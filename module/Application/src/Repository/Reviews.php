<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;

class Reviews
{
  private Connection $conn;

  public function __construct(Connection $conn)
  {
    $this->conn = $conn;
  }

  public function createReview(array $data): int
  {
    $this->conn->insert('reviews', [
      'customer_id' => $data['customer_id'] ?? null,
      'name' => $data['name'],
      'email' => $data['email'],
      'rating' => $data['rating'],
      'comment' => $data['comment'],
      'created_at' => date('Y-m-d H:i:s'),
      'status' => 'pending'
    ]);

    return (int) $this->conn->lastInsertId();
  }

  public function findAll(string $status = 'approved'): array
  {
    $qb = $this->conn->createQueryBuilder();
    $query = $qb->select('r.*')
      ->from('reviews', 'r')
      ->orderBy('r.created_at', 'DESC');

    if ($status !== 'all') {
      $query->where('r.status = :status')
        ->setParameter('status', $status);
    }

    $reviews = $query->fetchAllAssociative();

    foreach ($reviews as &$review) {
      if (isset($review['customer_id']) && $review['customer_id']) {
        $productQb = $this->conn->createQueryBuilder();
        $products = $productQb->select('DISTINCT p.name', 'p.image')
          ->from('orders', 'o')
          ->join('o', 'order_items', 'oi', 'o.id = oi.order_id')
          ->join('oi', 'products', 'p', 'oi.product_id = p.id')
          ->where('o.customer_id = :customerId')
          ->setParameter('customerId', $review['customer_id'])
          ->setMaxResults(4)
          ->fetchAllAssociative();

        $review['products'] = $products;
      } else {
        $review['products'] = [];
      }
    }

    return $reviews;
  }

  public function findById(int $id): ?array
  {
    $qb = $this->conn->createQueryBuilder();
    $review = $qb->select('*')
      ->from('reviews')
      ->where('id = :id')
      ->setParameter('id', $id)
      ->fetchAssociative();

    $result = null;
    if ($review) {
      $result = $review;
    }

    return $result;
  }

  public function updateStatus(int $id, string $status): void
  {
    $this->conn->update('reviews', ['status' => $status], ['id' => $id]);
  }

  public function findByEmail(string $email): array
  {
    $qb = $this->conn->createQueryBuilder();
    return $qb->select('*')
      ->from('reviews')
      ->where('email = :email')
      ->setParameter('email', $email)
      ->orderBy('created_at', 'DESC')
      ->fetchAllAssociative();
  }
}
