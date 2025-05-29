#include <WiFi.h>
#include <HTTPClient.h>
#include "DHT.h"

// ====== WIFI CONFIGURATION ======
const char* ssid     = "xample";
const char* password = "12345";

// ====== SERVER ENDPOINTS ======
const char* postServer    = "http://192.168.81.105/incubator/api/post-data.php";
const char* testGetServer = "http://192.168.81.105/incubator/api/get-latest-reading.php";

// ====== DEVICE PINS ======
#define RELAY_PIN     2    // Bulb/Fan Relay
#define MOTOR_RELAY   17   // Tilt Motor Relay
#define DHT_PIN       4
#define DHT_TYPE      DHT22

DHT dht(DHT_PIN, DHT_TYPE);

// ====== TIMING CONFIGURATION ======
//const unsigned long MOTOR_INTERVAL = 14400000;  // 4 hours = 4 * 60 * 60 * 1000
//const unsigned long MOTOR_ON_TIME  = 1000;      // 1 second is okay

const unsigned long MOTOR_INTERVAL = 20000;  // 20 sec
const unsigned long MOTOR_ON_TIME  = 1000;   // 1 sec
const unsigned long POST_INTERVAL  = 5000;   // 5 sec

// ====== STATE VARIABLES ======
bool bulbState        = false;
unsigned long lastPostTime    = 0;
unsigned long lastMotorToggle = 0;
unsigned long motorActivatedAt = 0;
bool motorActive = false;

void setup() {
  Serial.begin(115200);
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(MOTOR_RELAY, OUTPUT);

  // Ensure both devices start OFF
  digitalWrite(RELAY_PIN, HIGH);
  digitalWrite(MOTOR_RELAY, HIGH);

  dht.begin();
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\n✅ WiFi connected: " + WiFi.localIP().toString());

  lastPostTime = millis();
  lastMotorToggle = millis();
}

void loop() {
  unsigned long now = millis();

  // ---- SENSOR READ, BULB CONTROL, POST ----
  if (now - lastPostTime >= POST_INTERVAL) {
    lastPostTime = now;

    float temp = dht.readTemperature();
    float hum  = dht.readHumidity();

    if (isnan(temp) || isnan(hum)) {
      Serial.println("❌ DHT read failed, skipping control");
      return;
    }

    Serial.printf("🌡 Temp: %.2f °C   💧 Hum: %.2f %%\n", temp, hum);

    // ---- BULB/FAN CONTROL LOGIC ----
    if (temp < 37.5 && !bulbState) {
      bulbState = true;
      digitalWrite(RELAY_PIN, HIGH); // ON (active LOW)
      Serial.println("💡 Bulb/Fan turned ON (Temp < 37.5)");
    } else if (temp >= 37.8 && bulbState) {
      bulbState = false;
      digitalWrite(RELAY_PIN, LOW); // OFF
      Serial.println("💡 Bulb/Fan turned OFF (Temp ≥ 37.8)");
    }

    // ---- SEND DATA TO SERVER ----
    sendStatus(temp, hum);
  }

  // ---- MOTOR TILT SCHEDULER ----
  if (!motorActive && (now - lastMotorToggle >= MOTOR_INTERVAL)) {
    motorActive = true;
    motorActivatedAt = now;
    digitalWrite(MOTOR_RELAY, LOW); // start motor
    Serial.println("⏳ Motor tilt START");
  }

  if (motorActive && (now - motorActivatedAt >= MOTOR_ON_TIME)) {
    motorActive = false;
    lastMotorToggle = now;
    digitalWrite(MOTOR_RELAY, HIGH); // stop motor
    Serial.println("✅ Motor tilt END");
  }
}

// Sends the current bulbState (1=ON, 0=OFF), temp, hum via HTTP POST
void sendStatus(float temp, float hum) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi disconnected, skipping POST");
    return;
  }

  HTTPClient http;
  http.begin(testGetServer);
  int getCode = http.GET();
  Serial.printf("GET test returned: %d\n", getCode);
  http.end();

  String bulb = bulbState ? "1" : "0";  // LOW = ON = 0, so invert logic for API
  String fan  = bulbState ? "1" : "0";  // same logic

  String body = "bulb=" + bulb +
                "&fan=" + fan +
                "&dht22_temp=" + String(temp, 2) +
                "&dht22_hum="  + String(hum, 2);

  Serial.println("--- HTTP POST ---");
  Serial.println("POST body: " + body);

  http.begin(postServer);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  int postCode = http.POST(body);
  String resp = http.getString();
  Serial.printf("POST code: %d   resp: %s\n", postCode, resp.c_str());
  http.end();
}
