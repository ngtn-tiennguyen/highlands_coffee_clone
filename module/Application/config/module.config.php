<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\HomeController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'home-actions' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/home[/:action]',
                    'defaults' => [
                        'controller' => Controller\HomeController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'application' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\HomeController::class,
                        'action' => 'index',
                    ],
                ],
            ],
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'public-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/public/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'highlands-public-login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/highlands/public/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'public-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/public/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'highlands-public-logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/highlands/public/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],
            'admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/admin[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'public-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/public/admin[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'highlands-public-admin' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/highlands/public/admin[/:action][/:id]',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'highlands-public-signup' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/highlands/public/signup',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action' => 'signup',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\HomeController::class => function ($container) {
                return new Controller\HomeController(
                    $container->get(\Application\Repository\Products::class),
                    $container->get(\Application\Repository\Orders::class),
                    $container->get(\Application\Repository\Customers::class),
                    $container->get(\Application\Repository\Reviews::class)
                );
            },
            Controller\AuthController::class => function ($container) {
                return new Controller\AuthController(
                    $container->get(\Application\Repository\Users::class)
                );
            },
            Controller\AdminController::class => function ($container) {
                return new Controller\AdminController(
                    $container->get(\Application\Repository\Products::class),
                    $container->get(\Application\Repository\Orders::class),
                    $container->get(\Application\Repository\Customers::class),
                    $container->get(\Application\Repository\Users::class),
                    $container->get(\Application\Repository\Categories::class)
                );
            },
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'factories' => [
            \Doctrine\DBAL\Connection::class => function ($container) {
                $config = $container->get('config') ?? [];
                $params = $config['db'] ?? [];
                return \Doctrine\DBAL\DriverManager::getConnection($params);
            },
            \Application\Repository\Products::class => function ($container) {
                return new \Application\Repository\Products(
                    $container->get(\Doctrine\DBAL\Connection::class)
                );
            },
            \Application\Repository\Orders::class => function ($container) {
                return new \Application\Repository\Orders(
                    $container->get(\Doctrine\DBAL\Connection::class),
                    $container->get(\Application\Repository\Products::class)
                );
            },
            \Application\Repository\Customers::class => function ($container) {
                return new \Application\Repository\Customers(
                    $container->get(\Doctrine\DBAL\Connection::class)
                );
            },
            \Application\Repository\Reviews::class => function ($container) {
                return new \Application\Repository\Reviews(
                    $container->get(\Doctrine\DBAL\Connection::class)
                );
            },
            \Application\Repository\Users::class => function ($container) {
                return new \Application\Repository\Users(
                    $container->get(\Doctrine\DBAL\Connection::class)
                );
            },
            \Application\Repository\Categories::class => function ($container) {
                return new \Application\Repository\Categories(
                    $container->get(\Doctrine\DBAL\Connection::class)
                );
            },
        ],
    ],
];
