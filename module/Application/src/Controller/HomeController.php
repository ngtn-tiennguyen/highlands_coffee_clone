<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Repository\Products;
use Application\Repository\Orders;
use Application\Repository\Customers;
use Application\Repository\Reviews;
use Application\Helper\EmailSender;
use Constants;

class HomeController extends AbstractActionController
{
  protected $productRepo;
  protected $orderRepo;
  protected $customerRepo;
  protected $reviewRepo;
  private $translations;

  public function __construct(Products $productRepo, Orders $orderRepo, Customers $customerRepo, Reviews $reviewRepo)
  {
    $this->productRepo = $productRepo;
    $this->orderRepo = $orderRepo;
    $this->customerRepo = $customerRepo;
    $this->reviewRepo = $reviewRepo;
    require_once __DIR__ . '/../Helper/Constants.php';
    $this->loadTranslations();
  }

  private function loadTranslations()
  {
    $lang = $_SESSION['lang'] ?? 'en';
    $langFile = __DIR__ . '/../../models/Lang/' . $lang . '.php';
    $this->translations = file_exists($langFile) ? include $langFile : [];
  }

  private function trans(string $key): string
  {
    return $this->translations[$key] ?? '';
  }

  public function indexAction()
  {
    $view = new ViewModel();

    $listThumbnails = $this->getListThumbnails();
    $view->listThumbnails = $listThumbnails;

    $listProducts = $this->getListProducts();
    $view->listProducts = $listProducts;

    $carouselImages = $this->getCarouselImages();
    $view->carouselImages = $carouselImages;

    $reviews = $this->getReviews();
    $view->reviews = $reviews;

    return $view;
  }

  public function getListThumbnails()
  {
    return [
      ['src' => 'PHIN_SUA_DA.png', 'color' => '#b2292e'],
      ['src' => 'FREEZE_TRA_XANH.png', 'color' => '#047143'],
      ['src' => 'TRA_SEN_VANG_CN.png', 'color' => '#b37c5e']
    ];
  }

  public function getListProducts()
  {
    try {
      $data = $this->productRepo->getAllListProducts();
    } catch (\Throwable $e) {
      $data = [];
    }

    return $data;
  }

  public function getCarouselImages()
  {
    try {
      $images = $this->productRepo->getAllProductImages();
      shuffle($images);
      return $images;
    } catch (\Throwable $e) {
      return [];
    }
  }

  public function getReviews()
  {
    try {
      $data = $this->reviewRepo->findAll('approved');
    } catch (\Throwable $e) {
      $data = [];
    }

    return $data;
  }

  private function jsonResponse(bool $success, string $message, array $data = []): JsonModel
  {
    return new JsonModel(array_merge(['success' => $success, 'message' => $message], $data));
  }

  public function orderAction()
  {
    if ($this->getRequest()->isPost()) {
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || !isset($data['items'], $data['total'], $data['customer'])) {
        $result = $this->jsonResponse(false, $this->trans('ORDER_ERROR'));
      } else {
        try {
          $customerId = $this->customerRepo->createCustomer($data['customer']);
          $orderId = $this->orderRepo->createOrder($customerId, Constants::ORDER_STATUS_PROCESSING);
          $this->orderRepo->addOrderItems($orderId, $data['items']);

          try {
            $emailStartTime = microtime(true);
            $emailSent = EmailSender::sendOrderConfirmation(
              $data['customer'],
              $orderId,
              $data['items'],
              $data['total']
            );
            $emailDuration = round((microtime(true) - $emailStartTime) * 1000);

            if ($emailSent) {
              error_log("Order #{$orderId} Brevo email sent in {$emailDuration}ms");
            } else {
              error_log("Order #{$orderId} Brevo email failed in {$emailDuration}ms");
            }
          } catch (\Throwable $emailError) {
            error_log("Order #{$orderId} email exception: " . $emailError->getMessage());
          }

          $result = $this->jsonResponse(true, $this->trans('ORDER_SUCCESS'), [
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'data' => ['items' => $data['items'], 'total' => $data['total'], 'status' => Constants::ORDER_STATUS_PROCESSING]
          ]);
        } catch (\Throwable $e) {
          error_log("Order creation failed: " . $e->getMessage());
          error_log("Stack trace: " . $e->getTraceAsString());
          $result = $this->jsonResponse(false, $this->trans('ORDER_ERROR'));
        }
      }
    } else {
      $result = $this->jsonResponse(false, 'Method not allowed');
    }

    return $result;
  }

  public function checkCustomerAction()
  {
    if ($this->getRequest()->isPost()) {
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || !isset($data['email'])) {
        $result = $this->jsonResponse(false, 'Invalid data');
      } else {
        $customer = $this->customerRepo->findByEmail($data['email']);

        if ($customer) {
          $result = $this->jsonResponse(true, 'Customer found', [
            'customer_id' => $customer['id']
          ]);
        } else {
          $result = $this->jsonResponse(false, $this->trans('CUSTOMER_NOT_FOUND'));
        }
      }
    } else {
      $result = $this->jsonResponse(false, 'Method not allowed');
    }

    return $result;
  }

  public function reviewAction()
  {
    if ($this->getRequest()->isPost()) {
      $data = json_decode(file_get_contents('php://input'), true);

      if (!$data || !isset($data['name'], $data['email'], $data['rating'], $data['comment'])) {
        $result = $this->jsonResponse(false, $this->trans('REVIEW_ERROR'));
      } else {
        try {
          $customer = $this->customerRepo->findByEmail($data['email']);
          $customerId = $customer ? $customer['id'] : null;

          $reviewId = $this->reviewRepo->createReview([
            'customer_id' => $customerId,
            'name' => $data['name'],
            'email' => $data['email'],
            'rating' => (int) $data['rating'],
            'comment' => $data['comment']
          ]);

          $result = $this->jsonResponse(true, $this->trans('REVIEW_SUCCESS'), [
            'review_id' => $reviewId
          ]);
        } catch (\Throwable $e) {
          $result = $this->jsonResponse(false, $this->trans('REVIEW_ERROR'));
        }
      }
    } else {
      $result = $this->jsonResponse(false, 'Method not allowed');
    }

    return $result;
  }
}