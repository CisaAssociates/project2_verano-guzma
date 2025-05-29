<?php
require_once '../db.php';

$sql = "SELECT * FROM sensor_readings ORDER BY created_at DESC LIMIT 20";
$result = $conn->query($sql);

$labels = [];
$dht22_temp = [];
$sht31_temp = [];
$dht22_hum = [];
$sht31_hum = [];

while ($row = $result->fetch_assoc()) {
    // Format timestamps for labels
    $labels[] = date('H:i:s', strtotime($row['created_at']));
    $dht22_temp[] = floatval($row['dht22_temp']);
    $sht31_temp[] = $row['sht31_temp'] !== null ? floatval($row['sht31_temp']) : null;
    $dht22_hum[] = floatval($row['dht22_hum']);
    $sht31_hum[] = $row['sht31_hum'] !== null ? floatval($row['sht31_hum']) : null;
}

echo json_encode([
    "status" => "success",
    "labels" => array_reverse($labels),
    "dht22_temp" => array_reverse($dht22_temp),
    "sht31_temp" => array_reverse($sht31_temp),
    "dht22_hum" => array_reverse($dht22_hum),
    "sht31_hum" => array_reverse($sht31_hum)
]);

$conn->close();
?>
