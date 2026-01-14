<?php

declare(strict_types=1);

namespace Application;

use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Application;

class Module
{
    public function getConfig(): array
    {
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'normalizeActionName'], 100);
    }

    public function normalizeActionName(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            return;
        }

        $action = $routeMatch->getParam('action');
        if (!$action) {
            return;
        }

        if (strpos($action, '-') !== false || strpos($action, '_') !== false) {
            $parts = preg_split('/[-_]/', $action);
            $camelCase = array_shift($parts);
            foreach ($parts as $part) {
                $camelCase .= ucfirst($part);
            }

            $routeMatch->setParam('action', $camelCase);
        }
    }
}
