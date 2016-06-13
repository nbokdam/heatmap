/**
 * HeatmapSensor.ino
 * 
 * This sketch is intended for ESP8266 (ESP-12).
 * First it connects to WiFi. Afterwards it measures
 * the number of times the sound is above threshold 
 * as configured on the soundsensor.
 * 
 * When the measuring is done, the data is sent to
 * a webserver. The server needs to have the provided 
 * PHP-scripts installed. At least it needs to accept
 * HTTP-requests in the following format:
 * "http://www.example.com/measure.php?sensor=SENSOR_ID&value=VALUE"
 * 
 * The measured value is between and including 0 and 100. 0 means 
 * no sound detected; 100 means the whole measurement was above 
 * threshold.
 * 
 * Note: About the calibration-jumper
 * On the CALSW pin has to be a jumper. Under normal circumstances CALSW
 * should be connected to CALPULLDOWN, unless the soundsensor needs
 * to be calibrated, in which case it should be connected to CALPULLUP.
 * See below for pin-definitions.
 * 
 * Author: Nils Bokdam
 * Created on 12 june 2016
 * https://bokd.am/
 * 
 */

// Libraries:
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <Hash.h>

// Settings
// WiFi:
#define SSID "ssid"
#define WPAPSK "psk"

// Heatmap-server:
#define SERVER "http://example.com/heatmap"
#define SENSOR_ID 1
#define SECRET "changeme" //Secret needed for storing data

// Sleeping time after measurement (for power-saving)
const int sleepTimeS = 240;

// Number of measurements (has to do with accuracy)
const double MEASUREMENTS = 2000;

// Pin-definitions:
#define SOUNDSENSOR 4 //GPIO5 on ESP-12 board | to soundsensor digital out
#define SENSORPWR 5 //GPIO4 on ESP-12 board | to soundsensor powercircuit

// Calibrationpin-definitions:
#define CALSW 12
#define CALPULLUP 14
#define CALPULLDOWN 13

void setup() {
    Serial.begin(74880);
    Serial.println("");
    
    // Initialize pins
    Serial.println("Initializing pins...");
    pinMode(SOUNDSENSOR, INPUT);
    pinMode(CALSW, INPUT);
    pinMode(SENSORPWR, OUTPUT);
    pinMode(CALPULLUP, OUTPUT);
    pinMode(CALPULLDOWN, OUTPUT);
    digitalWrite(CALPULLUP, HIGH);
    digitalWrite(CALPULLDOWN, LOW);

    // Start calibration-routine if necessary
    if(digitalRead(CALSW)) {
      Serial.println("Calibration jumper detected. Please remove when ready.");
      digitalWrite(SENSORPWR, LOW);
      delay(200);
      while(digitalRead(CALSW))
        delay(200);
      Serial.println("Calibration jumper removed. Continuing...");
    } else
      Serial.println("No calibration jumper detected. Continuing...");
    
    // Turn off sound-sensor
    digitalWrite(SENSORPWR, HIGH);

    // Start WiFi
    Serial.println("Connecting to network with SSID: " + (String) SSID);
    WiFi.mode(WIFI_STA);
    WiFi.begin(SSID, WPAPSK);

    // Start measurement
    Serial.println("Please wait while the buzz is being measured...");
    String measurement = (String) measure();
    Serial.println("Done. Detected buzz-level: " + measurement);

    // WiFi should be started by now, but to be sure:    
    if (WiFi.status() != WL_CONNECTED){
      Serial.print("Waiting for WiFi-connection...");
      while (WiFi.status() != WL_CONNECTED) {
        Serial.print(".");
        delay(500);
      }   
    } 

    // Get challenge from Heatmap-server:
    HTTPClient http;
    String reqStr = (String) SERVER + (String) "/challenge.php?sensor=" + (String) SENSOR_ID;
    http.begin(reqStr);
    Serial.println("HTTP-responsecode: " + (String) http.GET());
    String challenge = http.getString();
    Serial.println("Challenge: " + challenge);
    http.end();    

    // Generate signature:
    String signature = sha1(challenge + (String) measurement + (String) SENSOR_ID + SECRET);
    Serial.println("Signature: " + signature);
    
    // Send measurement to server
    reqStr = (String) SERVER + (String) "/measurement.php?sensor=" + (String) SENSOR_ID + (String) "&value=" + measurement + "&signature=" + signature;
    http.begin(reqStr);
    Serial.println("HTTP-responsecode: " + (String) http.GET());
    Serial.println(http.getString());
    http.end();

    // Go to sleep...
    double time = millis() / 1000;
    Serial.println("Going to sleep after " + (String) time + " seconds.");
    Serial.println("");
    ESP.deepSleep(sleepTimeS * 1000000);
}

double measure() {
  // Turn on sensor and wait for warming-up:
  digitalWrite(SENSORPWR, LOW);
  delay(1000);

  // Initialize return-value:
  double retvalue = 0;

  // Now measure. This can take a while
  for(int i = 0; i < MEASUREMENTS; i++) {
    if(digitalRead(SOUNDSENSOR)==HIGH)
    retvalue += 1;
    delay(10);
  }

  // Invert measurement:
  retvalue = 100 - ((retvalue / MEASUREMENTS) * 100);

  // Turn off sensor:
  digitalWrite(SENSORPWR, HIGH);

  // Done! Returning value.
  return retvalue;
}

void loop() {
  // ESP resets after deepsleep, so no code here
}
