console.log("📦 dashboard.js is loaded");

let tempChart, humidityChart;

// Fetch latest readings (device status, temp, hum)
function fetchLatest() {
  console.log("🛰️ fetchLatest called");

  axios.get('api/get-latest-reading.php', {
    headers: { 'Cache-Control': 'no-cache' }
  })
    .then(response => {
      console.log("📥 Latest response:", response.data);
      const res = response.data;

      if (res.status === 'success') {
        const { dht22_temp, dht22_hum, bulb, fan } = res.data;

        document.getElementById('dht22_temp').textContent = dht22_temp !== null ? dht22_temp + ' °C' : 'N/A';
        document.getElementById('dht22_hum').textContent  = dht22_hum  !== null ? dht22_hum  + ' %' : 'N/A';
        document.getElementById('bulb-status').textContent = bulb === 'on' ? 'ON' : 'OFF';
        document.getElementById('fan-status').textContent  = fan  === 'on'  ? 'ON' : 'OFF';
      } else {
        console.error('Error in latest fetch:', res.message);
      }
    })
    .catch(err => console.error('Fetch latest error:', err));
}

// Fetch temperature and humidity history
function fetchHistory() {
  console.log("📊 fetchHistory called");

  axios.get('api/history.php', {
    headers: { 'Cache-Control': 'no-cache' }
  })
    .then(response => {
      console.log("📥 History response:", response.data);
      const res = response.data;

      if (res.status === 'success') {
        const history = res.sensors;  // ✅ Use 'sensors' instead of 'data'
        const labels = history.map(r => r.created_at);
        const temps  = history.map(r => r.dht22_temp);
        const hums   = history.map(r => r.dht22_hum);

        updateCharts(labels, temps, hums);

        const tbody = document.getElementById('history-body');
        tbody.innerHTML = '';
        history.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${r.created_at}</td>
            <td class="text-end">${r.dht22_temp ?? 'N/A'}</td>
            <td class="text-end">${r.dht22_hum ?? 'N/A'}</td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        console.error('Error in history fetch:', res.message);
      }
    })
    .catch(err => console.error('Fetch history error:', err));
}


// ✅ Fetch device history (bulb + fan)
function fetchDeviceHistory() {
  console.log("📟 fetchDeviceHistory called");

  axios.get('api/history.php', {
    headers: { 'Cache-Control': 'no-cache' }
  })
    .then(response => {
      const res = response.data;
      if (res.status === 'success') {
        const history = res.devices;  // ✅ Use 'devices'
        const tbody = document.getElementById('device-history-body');
        tbody.innerHTML = '';
        history.forEach(r => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${r.timestamp}</td>
            <td class="text-end">${r.bulb_status}</td>
            <td class="text-end">${r.fan_status}</td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        console.error('Error in device history fetch:', res.message);
      }
    })
    .catch(err => console.error('Device history error:', err));
}




// Update Chart.js charts
function updateCharts(labels, temps, hums) {
  if (tempChart) tempChart.destroy();
  if (humidityChart) humidityChart.destroy();

  const ctxT = document.getElementById('tempChart').getContext('2d');
  tempChart = new Chart(ctxT, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'DHT22 Temp (°C)',
        data: temps,
        tension: 0.3,
        borderColor: '#ffce56',
        fill: false
      }]
    },
    options: { responsive: true }
  });

  const ctxH = document.getElementById('humidityChart').getContext('2d');
  humidityChart = new Chart(ctxH, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'DHT22 Humidity (%)',
        data: hums,
        tension: 0.3,
        borderColor: '#36a2eb',
        fill: false
      }]
    },
    options: { responsive: true }
  });
}

// Initialize dashboard logic
function init() {
  console.log("🚀 init() has run");

  fetchLatest();
  fetchHistory();
  fetchDeviceHistory();

  // Update live readings fast
  setInterval(() => {
    console.log("🔁 fetchLatest() @ 300ms");
    fetchLatest();
  }, 300);

  // Update history charts & logs slower
  setInterval(() => {
    console.log("📈 fetchHistory() @ 5s");
    fetchHistory();
    fetchDeviceHistory();
  }, 5000);
}

document.addEventListener('DOMContentLoaded', () => {
  console.log("📅 DOMContentLoaded event fired");
  init();
});
