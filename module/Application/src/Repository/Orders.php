<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;
use Constants;

class Orders
{
  private Connection $conn;
  private Products $productsRepo;

  public function __construct(Connection $conn, Products $productsRepo)
  {
    $this->conn = $conn;
    $this->productsRepo = $productsRepo;
  }

  public function createOrder(int $customerId, string $status = Constants::ORDER_STATUS_PROCESSING): int
  {
    $this->conn->insert('orders', [
      'customer_id' => $customerId,
      'dt_order' => date('Y-m-d H:i:s'),
      'status' => $status
    ]);

    return (int) $this->conn->lastInsertId();
  }

  public function addOrderItems(int $orderId, array $items): void
  {
    foreach ($items as $item) {
      $productId = is_numeric($item['id'])
        ? (int) $item['id']
        : $this->productsRepo->findIdByName($item['id']);

      if ($productId === null) {
        continue;
      }

      $this->conn->insert('order_items', [
        'order_id' => $orderId,
        'product_id' => $productId,
        'quantity' => $item['quantity'],
        'total' => $item['price'] * $item['quantity']
      ]);
    }
  }

  public function findAll(): array
  {
    $qb = $this->conn->createQueryBuilder();
    return $qb->select('o.*', 'c.first_name', 'c.last_name')
      ->from('orders', 'o')
      ->leftJoin('o', 'customers', 'c', 'o.customer_id = c.id')
      ->orderBy('o.dt_order', 'DESC')
      ->fetchAllAssociative();
  }

  public function find(int $id): ?array
  {
    $row = $this->conn->fetchAssociative('SELECT o.*, c.first_name, c.last_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?', [$id]);
    return $row === false ? null : $row;
  }

  public function findById(int $id): ?array
  {
    $qb = $this->conn->createQueryBuilder();
    $order = $qb->select('*')
      ->from('orders')
      ->where('id = :id')
      ->setParameter('id', $id)
      ->fetchAssociative();

    $result = null;
    if ($order) {
      $qb = $this->conn->createQueryBuilder();
      $items = $qb->select('oi.*', 'p.name', 'p.image')
        ->from('order_items', 'oi')
        ->join('oi', 'products', 'p', 'oi.product_id = p.id')
        ->where('oi.order_id = :orderId')
        ->setParameter('orderId', $id)
        ->fetchAllAssociative();

      $order['items'] = $items;
      $result = $order;
    }

    return $result;
  }

  public function updateStatus(int $id, string $status): void
  {
    $this->conn->update('orders', ['status' => $status], ['id' => $id]);
  }

  public function update(int $id, array $data): void
  {
    $payload = [];
    if (isset($data['customer_id']))
      $payload['customer_id'] = (int) $data['customer_id'];
    if (isset($data['status']))
      $payload['status'] = $data['status'];
    if (!empty($payload)) {
      $this->conn->update('orders', $payload, ['id' => $id]);
    }
  }

  public function delete(int $id): void
  {
    $this->conn->delete('order_items', ['order_id' => $id]);
    $this->conn->delete('orders', ['id' => $id]);
  }
}
