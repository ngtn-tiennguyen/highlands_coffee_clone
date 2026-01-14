<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;

class Users
{
  private Connection $conn;

  public function __construct(Connection $conn)
  {
    $this->conn = $conn;
  }

  public function findByUsername(string $username): ?array
  {
    $row = $this->conn->fetchAssociative(
      'SELECT * FROM users WHERE username = ?',
      [$username]
    );
    return $row === false ? null : $row;
  }

  public function findById(int $id): ?array
  {
    $row = $this->conn->fetchAssociative(
      'SELECT * FROM users WHERE id = ?',
      [$id]
    );
    return $row === false ? null : $row;
  }

  public function findAll(): array
  {
    return $this->conn->fetchAllAssociative('SELECT * FROM users ORDER BY created_at DESC');
  }

  public function create(array $data): int
  {
    $this->conn->insert('users', [
      'username' => $data['username'],
      'password' => password_hash($data['password'], PASSWORD_DEFAULT),
      'email' => $data['email'],
      'full_name' => $data['full_name'],
      'type' => $data['type'] ?? 0,
      'created_at' => date('Y-m-d H:i:s')
    ]);

    return (int) $this->conn->lastInsertId();
  }

  public function update(int $id, array $data): void
  {
    $updateData = [
      'email' => $data['email'],
      'full_name' => $data['full_name'],
      'type' => $data['type'] ?? 0
    ];

    if (!empty($data['password'])) {
      $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $this->conn->update('users', $updateData, ['id' => $id]);
  }

  public function delete(int $id): void
  {
    $this->conn->delete('users', ['id' => $id]);
  }

  public function verifyPassword(string $password, string $hash): bool
  {
    return password_verify($password, $hash);
  }
}
