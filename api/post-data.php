<?php
// File: api/post-data.php

require_once '../db.php';
header('Content-Type: application/json');

// 1) Grab POST values
$bulb_status_val = isset($_POST['bulb']) ? (int)$_POST['bulb'] : null;
$fan_status_val  = isset($_POST['fan'])  ? (int)$_POST['fan']  : null;
$dht22_temp      = isset($_POST['dht22_temp']) ? floatval($_POST['dht22_temp']) : null;
$dht22_hum       = isset($_POST['dht22_hum'])  ? floatval($_POST['dht22_hum'])  : null;

// 2) Validate inputs
if ($bulb_status_val === null || $fan_status_val === null || $dht22_temp === null || $dht22_hum === null) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Missing bulb, fan, dht22_temp, or dht22_hum'
    ]);
    exit;
}

try {
    // 3) Insert temperature/humidity reading
    $stmt1 = $conn->prepare("INSERT INTO sensor_readings (dht22_temp, dht22_hum, created_at) VALUES (?, ?, NOW())");
    if (!$stmt1) {
        throw new Exception("Prepare failed (sensor_readings): " . $conn->error);
    }
    $stmt1->bind_param("dd", $dht22_temp, $dht22_hum);
    $stmt1->execute();
    $stmt1->close();

    // 4) Insert states (bulb + fan) - using shared relay logic
    $stmt2 = $conn->prepare("INSERT INTO device_states (device_name, status, updated_at) VALUES (?, ?, NOW())");
    if (!$stmt2) {
        throw new Exception("Prepare failed (device_states): " . $conn->error);
    }

    // Devices are tied to same relay: use bulb status for both
    $sharedStatus = $bulb_status_val;

    $stmt2->bind_param("si", $deviceName, $sharedStatus);

    $deviceName = 'bulb';
    $stmt2->execute();

    $deviceName = 'fan';
    $stmt2->execute();

    $stmt2->close();

    // 5) Done
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
