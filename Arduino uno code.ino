#include "HX711.h"
#include <OneWire.h>
#include <DallasTemperature.h>
#include <EEPROM.h>
#include <Wire.h>


// Pin definitions
#define ONE_WIRE_BUS 3   // Data wire for temperature sensor on digital pin 2
#define TURBIDITY_PIN A0    // Pin for turbidity sensor
#define pHSensorPin A2      // Pin for pH sensor
#define LOAD_CELL_DT_PIN 5  // Data pin for load cell
#define LOAD_CELL_SCK_PIN 6 // Clock pin for load cell

// Create HX711 load cell object
HX711 scale(LOAD_CELL_DT_PIN, LOAD_CELL_SCK_PIN);


OneWire oneWire(ONE_WIRE_BUS);
DallasTemperature sensors(&oneWire);

float calibration_value = 21.34 + 6.24;

void setup() {    
  Serial.begin(9600);           // Start serial communication to send data to ESP8266
  sensors.begin();              // Start up the temperature sensor library

  // Initialize the load cell
  scale.set_scale(2560); // Set fixed calibration factor
  scale.tare();  // Reset the scale to 0

  long zero_factor = scale.read_average(); // Get a baseline reading
  Serial.print("Zero factor: "); 
  Serial.println(zero_factor);
}

void sendSensorData() {
  // Get temperature
  sensors.requestTemperatures();
  float tempC = sensors.getTempCByIndex(0);

  // Get turbidity
  int turbidity = analogRead(TURBIDITY_PIN);

  // Get pH value
  int buffer_arr[10];
  unsigned long int avgval = 0;
  for (int i = 0; i < 10; i++) {
    buffer_arr[i] = analogRead(pHSensorPin);
    delay(0);
  }
  for (int i = 0; i < 9; i++) {
    for (int j = i + 1; j < 10; j++) {
      if (buffer_arr[i] > buffer_arr[j]) {
        int temp = buffer_arr[i];
        buffer_arr[i] = buffer_arr[j];
        buffer_arr[j] = temp;
      }
    }
  }
  for (int i = 2; i < 8; i++) {
    avgval += buffer_arr[i];
  }
  float volt = (float)avgval * 5.0 / 1024 / 6;
  float phValue = -5.70 * volt + calibration_value;

  // Get load cell data (weight)
  float units = scale.get_units(10);  // Get the output value from the load cell
  if (units < 0) {
    units = 0.00;
  }
  float ounces = units * 0.035274;

  // Send all sensor data as CSV: tempC, turbidity, pH, loadCellValue (weight)
  Serial.print(tempC); Serial.print(",");
   Serial.print(phValue); Serial.print(",");
  Serial.print(turbidity); Serial.print(",");
  Serial.print(units); Serial.println(" grams,");
}

void loop() {
  sendSensorData();
  delay(1000); // Delay to control data transmission rate
}
