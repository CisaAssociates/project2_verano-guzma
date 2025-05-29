<?php
require_once '../db.php';

$sql = "SELECT device_name, status FROM device_states";
$result = $conn->query($sql);

$states = [];
while ($row = $result->fetch_assoc()) {
    $states[$row['device_name']] = (bool)$row['status'];
}

echo json_encode($states);
$conn->close();
?>
