<?php

declare(strict_types=1);

use Laminas\Mvc\Application;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    if (is_string($path) && __FILE__ !== $path && is_file($path)) {
        // Set aggressive cache headers for static assets
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Images - cache for 1 year
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'])) {
            header('Cache-Control: public, max-age=31536000, immutable');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
        // CSS/JS - cache for 1 week
        elseif (in_array($extension, ['css', 'js'])) {
            header('Cache-Control: public, max-age=604800');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 604800) . ' GMT');
        }
        // Fonts - cache for 1 year
        elseif (in_array($extension, ['woff', 'woff2', 'ttf', 'eot'])) {
            header('Cache-Control: public, max-age=31536000, immutable');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }

        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `docker-compose run laminas composer install` if you are using Docker.\n"
    );
}

$container = require __DIR__ . '/../config/container.php';
// Run the application!
/** @var Application $app */
$app = $container->get('Application');
$app->run();
