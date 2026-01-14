<?php

require __DIR__ . '/../vendor/autoload.php';

$container = require __DIR__ . '/../config/container.php';

/** @var \Doctrine\DBAL\Connection $conn */
$conn = $container->get(\Doctrine\DBAL\Connection::class);

try {
    $result = $conn->executeQuery('SELECT 1')->fetchOne();
    echo "DB connected, test query result: " . $result . PHP_EOL;
} catch (\Throwable $e) {
    echo "DB connection failed: " . $e->getMessage() . PHP_EOL;
}
