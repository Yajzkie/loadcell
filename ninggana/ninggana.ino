#include <WiFi.h>
#include <HTTPClient.h>
#include <HX711.h>
#include <ESP32Servo.h>

// HX711 circuit wiring
const int loadCellDoutPin = 16; // Data pin
const int loadCellSckPin = 4;   // Clock pin

// Servo pins
const int servo1Pin = 13;
const int servo2Pin = 12;
const int servo3Pin = 14;

HX711 scale;
Servo servo1;
Servo servo2;
Servo servo3;

// Define URL and WiFi credentials
String URL = "htthp";const char* ssid = "SLSU-BC WiFi"; const char* password = ""; 

void setup() {
  Serial.begin(115200);
  connectWiFi();
  
  // Initialize HX711
  scale.begin(loadCellDoutPin, loadCellSckPin);
  scale.set_scale(-465.00); // Adjust the scale factor as needed
  scale.tare();             // Reset the scale to 0

  // Initialize Servos
  servo1.attach(servo1Pin);
  servo2.attach(servoPin);
  servo3.attach(servo3in);

  // Initial servo positions
  servo1.write(0);
  servo2.write(180);
  servo3.write(0);
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  float weight_data = getWeightData();
  String name = getName(weight_data); // Get the name based on weight
  String postData = "weight_data=" + String(weight_data) + "&name=" + name;

  HTTPClient http;
  http.begin(URL);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpCode = http.POST(postData);
  String payload = http.getString();

  Serial.print("URL : "); Serial.println(URL); 
  Serial.print("Data: "); Serial.println(postData);
  Serial.print("httpCode: "); Serial.println(httpCode);
  Serial.print("payload : "); Serial.println(payload);
  Serial.println("--------------------------------------------------");

  // Servo control based on weight
  if (weight_data >= 40 && weight_data < 47) {
    servo1.write(90); // Move servo 1
    servo2.write(180);
    servo3.write(0);
    delay(4000);
    servo1.write(0); // Move servo 1
    servo2.write(180);
    servo3.write(0);
  } else if (weight_data >= 47 && weight_data < 54) {
    servo1.write(0);
    servo2.write(90); // Move servo 2
    servo3.write(0);  
    delay(4000);
    servo1.write(0);
    servo2.write(180);
    servo3.write(0);
  } else if (weight_data >= 54 && weight_data < 60) {
    servo1.write(0);
    servo2.write(180);
    servo3.write(90); // Move servo 3
    delay(4000);
    servo1.write(0);
    servo2.write(180);
    servo3.write(0);
  } else {
    servo1.write(0);
    servo2.write(180);
    servo3.write(0); // No move
  }

  delay(5000); // Adjust delay as needed
}

void connectWiFi() {
  WiFi.mode(WIFI_OFF);
  delay(1000);
  WiFi.mode(WIFI_STA);

  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
    
  Serial.print("connected to : "); Serial.println(ssid);
  Serial.print("IP address: "); Serial.println(WiFi.localIP());
}

float getWeightData() {
  // Get weight data from HX711
  float weight = scale.get_units(10); // Average of 10 readings
  return weight;
}

String getName(float weight) {
  if (weight >= 40 && weight < 47) {
    return "small";
  } else if (weight >= 47 && weight < 54) {
    return "medium";
  } else if (weight >= 54 && weight < 60) {
    return "large";
    } else if (weight >= -999 && weight < 40) {
    return "";
  } else {
    return "xl";
  }
}
