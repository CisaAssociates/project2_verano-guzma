<?php
// index.php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Incubator Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.2/dist/darkly/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <h1 class="text-center mb-4">Incubator Dashboard</h1>

  <div class="card mb-4">
    <div class="card-header">Latest Sensor Readings & Device Status</div>
    <div class="card-body">
      <ul class="list-group">
        <li class="list-group-item">DHT22 Temp: <span id="dht22_temp">-- °C</span></li>
        <li class="list-group-item">DHT22 Humidity: <span id="dht22_hum">-- %</span></li>
        <li class="list-group-item">Bulb Status: <span id="bulb-status">--</span></li>
        <li class="list-group-item">Fan Status: <span id="fan-status">--</span></li>
      </ul>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">Temperature Chart (DHT22)</div>
        <div class="card-body">
          <canvas id="tempChart" height="200"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6 mt-3 mt-md-0">
      <div class="card">
        <div class="card-header">Humidity Chart (DHT22)</div>
        <div class="card-body">
          <canvas id="humidityChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center mb-4">
    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#historyModal">
      View Temp & Humidity Log
    </button>
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#deviceHistoryModal">
      View Device Status Log
    </button>
  </div>

  <!-- Temp & Humidity History Modal -->
  <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="historyModalLabel">Temp & Humidity History (Last 20)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Timestamp</th>
                  <th class="text-end">DHT22 Temp (°C)</th>
                  <th class="text-end">DHT22 Hum (%)</th>
                </tr>
              </thead>
              <tbody id="history-body">
                <!-- Injected via JS -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ Device Status History Modal -->
  <div class="modal fade" id="deviceHistoryModal" tabindex="-1" aria-labelledby="deviceHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deviceHistoryModalLabel">Device Status History (Last 20)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-striped table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Timestamp</th>
                  <th class="text-end">Bulb</th>
                  <th class="text-end">Fan</th>
                </tr>
              </thead>
              <tbody id="device-history-body">
                <!-- Injected via JS -->
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/dashboard.js?v=5"></script>
</body>
</html>
