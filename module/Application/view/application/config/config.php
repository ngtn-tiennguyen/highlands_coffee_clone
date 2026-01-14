<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../../src/Helper/Constants.php';
$lang = 'en';
if (isset($_GET['lang'])) {
  $lang = $_GET['lang'];
  $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
  $lang = $_SESSION['lang'];
}

$langFile = dirname(__DIR__, 3) . '/models/Lang/' . $lang . '.php';
if (file_exists($langFile)) {
  $file = include $langFile;
} else {
  $file = include dirname(__DIR__, 3) . '/models/Lang/en.php';
}

if (is_callable($file)) {
  $appMultilang = $file;
} elseif (is_array($file)) {
  $appMultilang = function (string $key, string $default = '') use ($file) {
    return $file[$key] ?? $default;
  };
} else {
  $appMultilang = function (string $key, string $default = '') {
    return $default;
  };
}