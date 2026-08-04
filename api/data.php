<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

const HISTORY_KEEP_ROWS = 200;

function prune_history(): void {
  db()->query(
    'DELETE FROM app_data_history WHERE id NOT IN ' .
    '(SELECT id FROM (SELECT id FROM app_data_history ORDER BY saved_at DESC LIMIT ' . HISTORY_KEEP_ROWS . ') t)'
  );
}

start_secure_session();
$email = require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = db()->prepare('SELECT data, version, updated_at, updated_by_email FROM app_data WHERE id = 1');
  $stmt->execute();
  $stmt->bind_result($dataRaw, $version, $updatedAt, $updatedBy);
  $stmt->fetch();
  $stmt->close();

  $data = $dataRaw === '' ? null : json_decode($dataRaw, true);
  json_out(['data' => $data, 'version' => $version, 'updatedAt' => $updatedAt, 'updatedBy' => $updatedBy, 'me' => $email]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_csrf_header();

  $body = json_input();
  if (!isset($body['data']) || !is_array($body['data'])) {
    json_out(['error' => 'missing_data'], 400);
  }
  if (!isset($body['baseVersion']) || !is_int($body['baseVersion'])) {
    json_out(['error' => 'missing_base_version'], 400);
  }
  $baseVersion = $body['baseVersion'];
  $newDataRaw = json_encode($body['data']);

  $conn = db();
  $conn->begin_transaction();
  try {
    $stmt = $conn->prepare('SELECT data, version FROM app_data WHERE id = 1 FOR UPDATE');
    $stmt->execute();
    $stmt->bind_result($currentDataRaw, $currentVersion);
    $stmt->fetch();
    $stmt->close();

    if ($currentVersion !== $baseVersion) {
      $conn->rollback();
      $currentData = $currentDataRaw === '' ? null : json_decode($currentDataRaw, true);
      json_out(['error' => 'conflict', 'data' => $currentData, 'version' => $currentVersion], 409);
    }

    $stmt = $conn->prepare('INSERT INTO app_data_history (data, version, saved_by_email) VALUES (?, ?, ?)');
    $stmt->bind_param('sis', $currentDataRaw, $currentVersion, $email);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare('UPDATE app_data SET data = ?, version = version + 1, updated_by_email = ? WHERE id = 1 AND version = ?');
    $stmt->bind_param('ssi', $newDataRaw, $email, $baseVersion);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected !== 1) {
      $conn->rollback();
      json_out(['error' => 'conflict'], 409);
    }

    $conn->commit();
  } catch (\Throwable $e) {
    $conn->rollback();
    json_out(['error' => 'server_error'], 500);
  }

  prune_history();

  $stmt = $conn->prepare('SELECT version, updated_at FROM app_data WHERE id = 1');
  $stmt->execute();
  $stmt->bind_result($newVersion, $updatedAt);
  $stmt->fetch();
  $stmt->close();

  json_out(['version' => $newVersion, 'updatedAt' => $updatedAt, 'updatedBy' => $email]);
}

json_out(['error' => 'method_not_allowed'], 405);
