<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;

class Customers
{
  private Connection $conn;

  public function __construct(Connection $conn)
  {
    $this->conn = $conn;
  }

  public function createCustomer(array $data): int
  {
    $insertData = [
      'first_name' => $data['first_name'] ?? '',
      'last_name' => $data['last_name'] ?? '',
      'email' => $data['email'] ?? '',
      'phone' => $data['phone'] ?? '',
      'address' => $data['address'] ?? '',
      'state' => $data['state'] ?? '',
      'city' => $data['city'] ?? '',
      'created_at' => date('Y-m-d H:i:s')
    ];

    $this->conn->insert('customers', $insertData);

    return (int) $this->conn->lastInsertId();
  }

  public function findById(int $id): ?array
  {
    $qb = $this->conn->createQueryBuilder();
    $customer = $qb->select('*')
      ->from('customers')
      ->where('id = :id')
      ->setParameter('id', $id)
      ->fetchAssociative();

    return $customer ? $customer : null;
  }

  public function findByEmail(string $email): ?array
  {
    $qb = $this->conn->createQueryBuilder();
    $customer = $qb->select('*')
      ->from('customers')
      ->where('email = :email')
      ->setParameter('email', $email)
      ->fetchAssociative();

    return $customer ? $customer : null;
  }

  public function findAll(): array
  {
    $qb = $this->conn->createQueryBuilder();
    return $qb->select('*')
      ->from('customers')
      ->orderBy('created_at', 'DESC')
      ->fetchAllAssociative();
  }

  public function update(int $id, array $data): void
  {
    $updateData = [];

    if (isset($data['first_name']))
      $updateData['first_name'] = $data['first_name'];
    if (isset($data['last_name']))
      $updateData['last_name'] = $data['last_name'];
    if (isset($data['email']))
      $updateData['email'] = $data['email'];
    if (isset($data['phone']))
      $updateData['phone'] = $data['phone'] ?? '';
    if (isset($data['address']))
      $updateData['address'] = $data['address'] ?? '';
    if (isset($data['state']))
      $updateData['state'] = $data['state'] ?? '';
    if (isset($data['city']))
      $updateData['city'] = $data['city'] ?? '';

    if (!empty($updateData)) {
      $this->conn->update('customers', $updateData, ['id' => $id]);
    }
  }

  public function create(array $data): int
  {
    return $this->createCustomer($data);
  }

  public function find(int $id): ?array
  {
    return $this->findById($id);
  }

  public function delete(int $id): void
  {
    $this->conn->delete('customers', ['id' => $id]);
  }
}

