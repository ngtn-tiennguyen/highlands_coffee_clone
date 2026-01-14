<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;

class Categories
{
  private Connection $conn;

  public function __construct(Connection $conn)
  {
    $this->conn = $conn;
  }

  public function findAll(): array
  {
    return $this->conn->fetchAllAssociative('SELECT * FROM categories ORDER BY name');
  }

  public function find(int $id): ?array
  {
    $row = $this->conn->fetchAssociative('SELECT * FROM categories WHERE id = ?', [$id]);
    return $row === false ? null : $row;
  }

  public function create(array $data): int
  {
    $this->conn->insert('categories', [
      'name' => $data['name'] ?? ''
    ]);
    return (int) $this->conn->lastInsertId();
  }

  public function update(int $id, array $data): void
  {
    $payload = [];
    if (isset($data['name']))
      $payload['name'] = $data['name'];
    if (!empty($payload)) {
      $this->conn->update('categories', $payload, ['id' => $id]);
    }
  }

  public function delete(int $id): void
  {
    $this->conn->delete('categories', ['id' => $id]);
  }
}
