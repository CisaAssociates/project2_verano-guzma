<?php
// File: api/get-latest-reading.php

require_once '../db.php';
header('Content-Type: application/json');

try {
    // 1) Fetch the very latest sensor reading
    $sqlR = "
      SELECT 
        id,
        dht22_temp,
        dht22_hum,
        created_at
      FROM sensor_readings
      ORDER BY id DESC
      LIMIT 1
    ";
    $resultR = $conn->query($sqlR);
    $rowR    = $resultR ? $resultR->fetch_assoc() : null;

    // If no sensor row found, initialize defaults
    if (!$rowR) {
        $rowR = [
            'id'         => null,
            'dht22_temp' => null,
            'dht22_hum'  => null,
            'created_at' => null
        ];
    }

    // 2) Fetch the most recent relay state for bulb & fan
    $sqlS = "
      SELECT ds.device_name, ds.status
      FROM device_states ds
      INNER JOIN (
         SELECT device_name, MAX(updated_at) AS max_upd
         FROM device_states
         GROUP BY device_name
      ) latest
        ON ds.device_name = latest.device_name
       AND ds.updated_at  = latest.max_upd
    ";
    $resS = $conn->query($sqlS);

    // 3) Default to “off” then overwrite
    $states = ['bulb' => 'off', 'fan' => 'off'];
    if ($resS) {
        while ($s = $resS->fetch_assoc()) {
            $states[$s['device_name']] = $s['status'] ? 'on' : 'off';
        }
    }

    // 4) Merge sensor & relay into one JSON payload
    echo json_encode([
        "status" => "success",
        "data"   => array_merge(
            $rowR,
            ['bulb' => $states['bulb'], 'fan' => $states['fan']]
        )
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}

$conn->close();
