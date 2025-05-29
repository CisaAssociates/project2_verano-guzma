<?php
require_once('../db.php');

header('Content-Type: application/json');

try {
  $stmt = $conn->query("SELECT created_at, bulb, fan FROM readings ORDER BY created_at DESC LIMIT 100");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'status' => 'success',
    'data' => $rows
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Failed to fetch device history'
  ]);
}
?>
