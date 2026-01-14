<?php
namespace Application\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ActionNormalizer
{
  public static function normalize($action)
  {
    $parts = preg_split('/[-_]/', $action);

    if (count($parts) > 0) {
      $first = array_shift($parts);
      $rest = array_map('ucfirst', $parts);
      return $first . implode('', $rest);
    }

    return $action;
  }
}
