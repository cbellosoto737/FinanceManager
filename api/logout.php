<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(['error' => 'method_not_allowed'], 405);
}
require_csrf_header();

clear_remember_cookie();
$_SESSION = [];
session_destroy();

json_out(['ok' => true]);
