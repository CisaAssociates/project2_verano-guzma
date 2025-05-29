<?php
require_once '../db.php';

$device = $_GET['device'] ?? null;

if ($device && in_array($device, ['bulb', 'fan'])) {
    $sql = "SELECT status FROM device_states WHERE device_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $device);
    $stmt->execute();
    $stmt->bind_result($currentStatus);
    $stmt->fetch();
    $stmt->close();

    $newStatus = $currentStatus ? 0 : 1;

    $stmt = $conn->prepare("UPDATE device_states SET status = ?, updated_at = NOW() WHERE device_name = ?");
    $stmt->bind_param("is", $newStatus, $device);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["device" => $device, "new_status" => $newStatus]);
} else {
    echo json_encode(["error" => "Invalid device"]);
}

$conn->close();
?>
