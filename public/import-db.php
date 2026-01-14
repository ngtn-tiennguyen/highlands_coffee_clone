<?php
// public/import-db.php

// if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
//     die('Access denied');
// }

// script import-db.php
$output = shell_exec('php ../import-db.php 2>&1');
echo "<pre>" . htmlspecialchars($output) . "</pre>";
