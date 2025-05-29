<?php
// api/update-relay-status.php
require_once __DIR__ . '/init.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('POST only', 405);
    exit;
}

// Read and sanitize input
$relay_status = isset($_POST['relay_status']) ? (int) $_POST['relay_status'] : 0;

try {
    // Insert into the existing device_states table
    $stmt = $conn->prepare("
        INSERT INTO device_states (device_name, status)
        VALUES (?, ?)
    ");
    $device = 'system_relay';
    $stmt->bind_param("si", $device, $relay_status);
    $stmt->execute();

    json_success(['message' => 'Relay status updated.']);
} catch (Exception $e) {
    json_error('DB error: ' . $e->getMessage(), 500);
}
