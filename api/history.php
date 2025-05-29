<?php
// api/history.php
require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('GET only', 405);
}

try {
    // 1. Sensor history (temp + hum)
    $stmt1 = $conn->prepare(
        "SELECT created_at, dht22_temp, dht22_hum
         FROM sensor_readings
         ORDER BY created_at DESC
         LIMIT 20"
    );
    $stmt1->execute();
    $res1 = $stmt1->get_result();
    $sensorRows = array_reverse($res1->fetch_all(MYSQLI_ASSOC));
    $stmt1->close();

    // 2. Device history (bulb + fan)
    $stmt2 = $conn->prepare(
        "SELECT updated_at AS timestamp, device_name, status
         FROM device_states
         ORDER BY updated_at DESC
         LIMIT 40"
    );
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    $rawDevices = $res2->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    // Combine bulb and fan into same row per timestamp
    $deviceMap = [];

    foreach ($rawDevices as $row) {
        $ts = $row['timestamp'];
        $device = $row['device_name'];
        $status = $row['status'] == 1 ? 'ON' : 'OFF';

        if (!isset($deviceMap[$ts])) {
            $deviceMap[$ts] = [
                'timestamp' => $ts,
                'bulb_status' => 'N/A',
                'fan_status' => 'N/A'
            ];
        }

        if ($device === 'bulb') {
            $deviceMap[$ts]['bulb_status'] = $status;
        } elseif ($device === 'fan') {
            $deviceMap[$ts]['fan_status'] = $status;
        }
    }

    // Sort timestamps ascending
    ksort($deviceMap);
    $deviceRows = array_values($deviceMap);

    // 3. Return both
    json_success([
        'sensors' => $sensorRows,
        'devices' => $deviceRows
    ]);

} catch (Exception $e) {
    json_error('DB error: ' . $e->getMessage(), 500);
}
