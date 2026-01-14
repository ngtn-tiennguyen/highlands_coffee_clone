<?php

declare(strict_types=1);

namespace Application\Repository;

use Doctrine\DBAL\Connection;

class Products
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function findAll(): array
    {
        return $this->conn->fetchAllAssociative(
            'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id'
        );
    }

    public function find(int $id): ?array
    {
        $row = $this->conn->fetchAssociative(
            'SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?',
            [$id]
        );
        return $row === false ? null : $row;
    }

    public function fetchCategories(): array
    {
        return $this->conn->fetchAllAssociative('SELECT id, name FROM categories ORDER BY name');
    }

    public function findIdByName(string $name): ?int
    {
        $row = $this->conn->fetchAssociative('SELECT id FROM products WHERE name = ?', [$name]);
        return $row === false ? null : (int) $row['id'];
    }

    public function getAllListProducts(): array
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('p.*, c.id AS category_id, c.name AS category_name')
            ->from('products', 'p')
            ->join('p', 'categories', 'c', 'p.category_id = c.id');

        $results = $qb->fetchAllAssociative();
        $list = [];

        foreach ($results as $product) {
            $categoryId = $product['category_id'];
            if (!isset($list[$categoryId])) {
                $list[$categoryId] = [
                    'category_name' => $product['category_name'],
                    'products' => []
                ];
            }
            $list[$categoryId]['products'][] = $product;
        }

        $totalPage = [];
        foreach ($list as $key => $value) {
            $totalProducts = count($value['products']);
            $itemsPerPage = 3;
            $totalPage[$key] = (int) ceil($totalProducts / $itemsPerPage);
        }
        return ['data' => $list, 'totalPage' => $totalPage];
    }

    public function getAllProductImages(): array
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('p.image, p.name')
            ->from('products', 'p')
            ->where('p.image IS NOT NULL');

        $results = $qb->fetchAllAssociative();

        return $results;
    }

    public function create(array $data): int
    {
        $this->conn->insert('products', [
            'name' => $data['name'],
            'descriptions' => $data['descriptions'] ?? '',
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'image' => $data['image'] ?? null
        ]);

        return (int) $this->conn->lastInsertId();
    }


    public function update(int $id, array $data): void
    {
        $updateData = [
            'name' => $data['name'],
            'descriptions' => $data['descriptions'] ?? '',
            'price' => $data['price'],
            'category_id' => $data['category_id']
        ];

        if (isset($data['image'])) {
            $updateData['image'] = $data['image'];
        }

        $this->conn->update('products', $updateData, ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->conn->delete('products', ['id' => $id]);
    }
}

