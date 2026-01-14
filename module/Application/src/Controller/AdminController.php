<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Repository\Products;
use Application\Repository\Orders;
use Application\Repository\Customers;
use Application\Repository\Users;
use Application\Repository\Categories;

class AdminController extends AbstractActionController
{
  protected $productRepo;
  protected $orderRepo;
  protected $customerRepo;
  protected $userRepo;
  protected $categoryRepo;

  public function __construct(
    Products $productRepo,
    Orders $orderRepo,
    Customers $customerRepo,
    Users $userRepo,
    Categories $categoryRepo
  ) {
    $this->productRepo = $productRepo;
    $this->orderRepo = $orderRepo;
    $this->customerRepo = $customerRepo;
    $this->userRepo = $userRepo;
    $this->categoryRepo = $categoryRepo;
  }

  private function checkAdmin()
  {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 1) {
      return $this->redirect()->toRoute('login');
    }
    return null;
  }

  public function indexAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new ViewModel();
    $this->layout('layout/admin');

    try {
      $stats = [
        'total_products' => count($this->productRepo->findAll()),
        'total_orders' => count($this->orderRepo->findAll()),
        'total_customers' => count($this->customerRepo->findAll()),
        'total_categories' => count($this->categoryRepo->findAll()),
      ];
      $view->stats = $stats;
    } catch (\Throwable $e) {
      $view->stats = ['total_products' => 0, 'total_orders' => 0, 'total_customers' => 0, 'total_categories' => 0];
    }

    $view->setTemplate('application/admin/index');
    return $view;
  }

  public function productsAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new ViewModel();
    $this->layout('layout/admin');

    try {
      $products = $this->productRepo->findAll();
      $view->products = $products;
    } catch (\Throwable $e) {
      $view->products = [];
    }

    $view->setTemplate('application/admin/products');
    return $view;
  }

  public function productAddAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      try {
        $this->productRepo->create($data);
        $_SESSION['success_message'] = 'Product added successfully!';
        return $this->redirect()->toRoute('admin', ['action' => 'products']);
      } catch (\Throwable $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
      }
    }

    $view = new ViewModel();
    $this->layout('layout/admin');
    $view->setTemplate('application/admin/product-form');
    return $view;
  }

  public function productEditAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromRoute('id');

    if ($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();

      try {
        $this->productRepo->update($id, $data);
        $_SESSION['success_message'] = 'Product updated successfully!';
        return $this->redirect()->toRoute('admin', ['action' => 'products']);
      } catch (\Throwable $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
      }
    }

    $view = new ViewModel();
    $this->layout('layout/admin');

    try {
      $product = $this->productRepo->find($id);
      if (!$product) {
        $_SESSION['error_message'] = 'Product not found!';
        return $this->redirect()->toRoute('admin', ['action' => 'products']);
      }
      $view->product = $product;
    } catch (\Throwable $e) {
      $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
      return $this->redirect()->toRoute('admin', ['action' => 'products']);
    }

    $view->setTemplate('application/admin/product-form');
    return $view;
  }

  public function productDeleteAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromRoute('id');

    try {
      $this->productRepo->delete($id);
      $_SESSION['success_message'] = 'Product deleted successfully!';
    } catch (\Throwable $e) {
      $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }

    return $this->redirect()->toRoute('admin', ['action' => 'products']);
  }

  public function ordersAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new ViewModel();
    $this->layout('layout/admin');

    try {
      $orders = $this->orderRepo->findAll();
      $view->orders = $orders;
    } catch (\Throwable $e) {
      $view->orders = [];
    }

    $view->setTemplate('application/admin/orders');
    return $view;
  }

  public function customersAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new ViewModel();
    $this->layout('layout/admin');

    try {
      $customers = $this->customerRepo->findAll();
      $view->customers = $customers;
    } catch (\Throwable $e) {
      $view->customers = [];
    }

    $view->setTemplate('application/admin/customers');
    return $view;
  }

  public function getProductAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromQuery('id', 0);
    $view = new JsonModel();
    $view->setTerminal(true);

    try {
      if ($id > 0) {
        $product = $this->productRepo->find($id);
        if ($product) {
          $view->setVariables(['success' => true, 'product' => $product]);
        } else {
          $view->setVariables(['success' => false, 'message' => 'Product not found']);
        }
      } else {
        $view->setVariables(['success' => false, 'message' => 'Invalid product ID']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function saveProductAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $data = $this->params()->fromPost();
    $files = $this->params()->fromFiles();
    $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;

    $imageName = $data['existing_image'] ?? null;
    if (isset($files['image']) && isset($files['image']['tmp_name']) && $files['image']['tmp_name']) {
      $uploadDir = realpath(getcwd() . '/public/images/products');
      if ($uploadDir === false) {
        $uploadDir = getcwd() . '/public/images/products';
        @mkdir($uploadDir, 0777, true);
      }
      $ext = pathinfo($files['image']['name'], PATHINFO_EXTENSION);
      $safeName = time() . '_' . uniqid('prod_', true) . ($ext ? '.' . $ext : '');
      $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;
      if (move_uploaded_file($files['image']['tmp_name'], $destination)) {
        $imageName = 'products/' . $safeName;
        $imageName = ltrim($imageName, '/');
      }
    }

    if ($imageName) {
      $data['image'] = $imageName;
    }

    try {
      if ($id) {
        $this->productRepo->update($id, $data);
        $view->setVariables(['success' => true, 'message' => 'Product updated successfully']);
      } else {
        $this->productRepo->create($data);
        $view->setVariables(['success' => true, 'message' => 'Product created successfully']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function deleteProductAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request method']);
      return $view;
    }

    $id = (int) $this->params()->fromQuery('id', 0);

    try {
      if ($id > 0) {
        $this->productRepo->delete($id);
        $view->setVariables(['success' => true, 'message' => 'Product deleted successfully']);
      } else {
        $view->setVariables(['success' => false, 'message' => 'Invalid product ID']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function getContentAction()
  {
    $action = $this->params()->fromQuery('action', 'index');

    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new ViewModel();
    $view->setTerminal(true);

    if ($action === 'index') {
      try {
        $stats = [
          'total_products' => count($this->productRepo->findAll()),
          'total_orders' => count($this->orderRepo->findAll()),
          'total_customers' => count($this->customerRepo->findAll()),
          'total_categories' => count($this->categoryRepo->findAll()),
        ];
        $view->stats = $stats;
      } catch (\Throwable $e) {
        $view->stats = ['total_products' => 0, 'total_orders' => 0, 'total_customers' => 0, 'total_categories' => 0];
      }
      $view->setTemplate('application/admin/admin');
    } elseif ($action === 'products') {
      try {
        $products = $this->productRepo->findAll();
        $categories = $this->productRepo->fetchCategories();
        $view->products = $products;
        $view->categories = $categories;
      } catch (\Throwable $e) {
        $view->products = [];
        $view->categories = [];
      }
      $view->setTemplate('application/admin/products');
    } elseif ($action === 'orders') {
      try {
        $orders = $this->orderRepo->findAll();
        $customers = $this->customerRepo->findAll();
        $view->orders = $orders;
        $view->customers = $customers;
      } catch (\Throwable $e) {
        $view->orders = [];
        $view->customers = [];
      }
      $view->setTemplate('application/admin/orders');
    } elseif ($action === 'customers') {
      try {
        $customers = $this->customerRepo->findAll();
        $view->customers = $customers;
      } catch (\Throwable $e) {
        $view->customers = [];
      }
      $view->setTemplate('application/admin/customers');
    } elseif ($action === 'categories') {
      try {
        $categories = $this->categoryRepo->findAll();
        $view->categories = $categories;
      } catch (\Throwable $e) {
        $view->categories = [];
      }
      $view->setTemplate('application/admin/categories');
    }

    return $view;
  }

  public function updateOrderStatusAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $data = $this->params()->fromPost();
    $id = isset($data['id']) ? (int) $data['id'] : null;
    $status = isset($data['status']) ? $data['status'] : null;

    if (!$id || !$status) {
      $view->setVariables(['success' => false, 'message' => 'Missing order ID or status']);
      return $view;
    }

    try {
      $this->orderRepo->update($id, ['status' => $status]);
      $view->setVariables(['success' => true, 'message' => 'Order status updated successfully']);
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function getOrderAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromQuery('id', 0);
    $view = new JsonModel();
    $view->setTerminal(true);

    try {
      if ($id > 0) {
        $order = $this->orderRepo->find($id);
        if ($order) {
          $view->setVariables(['success' => true, 'order' => $order]);
        } else {
          $view->setVariables(['success' => false, 'message' => 'Order not found']);
        }
      } else {
        $view->setVariables(['success' => false, 'message' => 'Invalid order ID']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function saveOrderAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $data = $this->params()->fromPost();
    $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;
    $customerId = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
    $status = $data['status'] ?? 'Pending';

    if (!$customerId) {
      $view->setVariables(['success' => false, 'message' => 'Customer is required']);
      return $view;
    }

    try {
      if ($id) {
        $this->orderRepo->update($id, ['customer_id' => $customerId, 'status' => $status]);
        $view->setVariables(['success' => true, 'message' => 'Order updated successfully']);
      } else {
        $orderId = $this->orderRepo->createOrder($customerId, $status);
        $view->setVariables(['success' => true, 'message' => 'Order created successfully', 'id' => $orderId]);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function deleteOrderAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $id = (int) $this->params()->fromQuery('id', 0);
    if ($id <= 0) {
      $view->setVariables(['success' => false, 'message' => 'Invalid order ID']);
      return $view;
    }

    try {
      $this->orderRepo->delete($id);
      $view->setVariables(['success' => true, 'message' => 'Order deleted successfully']);
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function getCustomerAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromQuery('id', 0);
    $view = new JsonModel();
    $view->setTerminal(true);

    try {
      if ($id > 0) {
        $customer = $this->customerRepo->find($id);
        if ($customer) {
          $view->setVariables(['success' => true, 'customer' => $customer]);
        } else {
          $view->setVariables(['success' => false, 'message' => 'Customer not found']);
        }
      } else {
        $view->setVariables(['success' => false, 'message' => 'Invalid customer ID']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function saveCustomerAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $data = $this->params()->fromPost();
    $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;

    try {
      if ($id) {
        $this->customerRepo->update($id, $data);
        $view->setVariables(['success' => true, 'message' => 'Customer updated successfully']);
      } else {
        $this->customerRepo->create($data);
        $view->setVariables(['success' => true, 'message' => 'Customer created successfully']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function deleteCustomerAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $id = (int) $this->params()->fromQuery('id', 0);

    if ($id <= 0) {
      $view->setVariables(['success' => false, 'message' => 'Invalid customer ID']);
      return $view;
    }

    try {
      $this->customerRepo->delete($id);
      $view->setVariables(['success' => true, 'message' => 'Customer deleted successfully']);
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function getCategoryAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $id = (int) $this->params()->fromQuery('id', 0);
    $view = new JsonModel();
    $view->setTerminal(true);

    try {
      if ($id > 0) {
        $category = $this->categoryRepo->find($id);
        if ($category) {
          $view->setVariables(['success' => true, 'category' => $category]);
        } else {
          $view->setVariables(['success' => false, 'message' => 'Category not found']);
        }
      } else {
        $view->setVariables(['success' => false, 'message' => 'Invalid category ID']);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function saveCategoryAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $data = $this->params()->fromPost();
    $id = isset($data['id']) && $data['id'] ? (int) $data['id'] : null;

    try {
      if ($id) {
        $this->categoryRepo->update($id, $data);
        $view->setVariables(['success' => true, 'message' => 'Category updated successfully']);
      } else {
        $newId = $this->categoryRepo->create($data);
        $view->setVariables(['success' => true, 'message' => 'Category created successfully', 'id' => $newId]);
      }
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }

  public function deleteCategoryAction()
  {
    $redirect = $this->checkAdmin();
    if ($redirect)
      return $redirect;

    $view = new JsonModel();
    $view->setTerminal(true);

    if (!$this->getRequest()->isPost()) {
      $view->setVariables(['success' => false, 'message' => 'Invalid request']);
      return $view;
    }

    $id = (int) $this->params()->fromQuery('id', 0);
    if ($id <= 0) {
      $view->setVariables(['success' => false, 'message' => 'Invalid category ID']);
      return $view;
    }

    try {
      $this->categoryRepo->delete($id);
      $view->setVariables(['success' => true, 'message' => 'Category deleted successfully']);
    } catch (\Throwable $e) {
      $view->setVariables(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

    return $view;
  }
}
