#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

const char* ssid = "ZTE_2.4G_H77f5z";
const char* password = "PccAdXC2";

const char* serverName = "http://192.168.1.7/sensordata/post-esp-data.php";
const char* serverName1 = "http://192.168.1.7/sensordata/get_lighting_status.php";
const char* serverName2 = "http://192.168.1.7/sensordata/get_feeder_status.php";

int dataCounter = 7;
String lastFeederId = "";
float previousWeight = 0;

unsigned long previousMillis = 0;
const long interval = 1000;

// Feeder control variables
bool feederActive = false;
unsigned long feederStartTime = 0;
unsigned long feederDuration = 0;

void setup() {
  Serial.begin(9600);
  pinMode(D1, OUTPUT);
  pinMode(D8, OUTPUT);
  digitalWrite(D8, LOW);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  unsigned long currentMillis = millis();

  // If feeder is active, check if it's time to stop
  if (feederActive) {
    if (currentMillis - feederStartTime >= feederDuration) {
      Serial.println("Feeding complete. Turning D8 LOW.");
      digitalWrite(D8, LOW);
      feederActive = false;
    }
    return;  // Skip the rest of the loop while feeding
  }

  // Periodically check lighting and feeder status
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    fetchLightingStatus();
    fetchFeederStatus();
  }

  // Read Serial data only when feeder is NOT active
  if (!feederActive && Serial.available() > 0) {
    String data = Serial.readStringUntil('\n');
    Serial.println("Received Data: " + data);

    int index1 = data.indexOf(',');
    int index2 = data.indexOf(',', index1 + 1);
    int index3 = data.indexOf(',', index2 + 1);

    if (index1 != -1 && index2 != -1 && index3 != -1) {
      float phValue = data.substring(0, index1).toFloat();
      float temperatureValue = data.substring(index1 + 1, index2).toFloat();
      float turbidityValue = data.substring(index2 + 1, index3).toFloat();
      float weightValue = data.substring(index3 + 1).toFloat();

      dataCounter++;
      if (dataCounter > 7) {
        sendDataToServer(phValue, temperatureValue, turbidityValue, weightValue);
      } else {
        Serial.println("Skipping initial data. Counter: " + String(dataCounter));
      }
    } else {
      Serial.println("Invalid data format received!");
    }
  }
}

void fetchLightingStatus() {
  WiFiClient client;
  HTTPClient http;
  http.begin(client, serverName1);

  int httpResponseCode = http.GET();
  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.print("Lighting Status: ");
    Serial.println(response);
    response == "ON" ? digitalWrite(D1, HIGH) : digitalWrite(D1, LOW);
  } else {
    Serial.println("Error in HTTP request: " + String(httpResponseCode));
  }

  http.end();
}

void fetchFeederStatus() {
  WiFiClient client;
  HTTPClient http;
  http.begin(client, serverName2);

  int httpResponseCode = http.GET();
  if (httpResponseCode > 0) {
    String response = http.getString();

    int statusIndex = response.indexOf("Status: ");
    int gramsIndex = response.indexOf("Grams: ");
    int idIndex = response.indexOf("Id: ");

    if (statusIndex != -1 && gramsIndex != -1 && idIndex != -1) {
      String status = response.substring(statusIndex + 8, response.indexOf("<br>", statusIndex));
      String grams = response.substring(gramsIndex + 7, response.indexOf("<br>", gramsIndex));
      String id = response.substring(idIndex + 4);

      Serial.print("Feeder Status: ");
      Serial.println(status);
      Serial.print("Grams: ");
      Serial.println(grams);
      Serial.print("Id: ");
      Serial.println(id);

      if (id != lastFeederId) {
        Serial.println("New Feeder ID detected. Starting feeding process.");
        digitalWrite(D8, HIGH);

        feederDuration = grams.toFloat() * 1000;  // Convert grams to milliseconds
        feederStartTime = millis();
        feederActive = true;  // Set feeder active, skipping Serial reads until done

        lastFeederId = id;
      }
    } else {
      Serial.println("Invalid feeder response format!");
    }
  } else {
    Serial.println("Error in HTTP request: " + String(httpResponseCode));
  }

  http.end();
}

void sendDataToServer(float phValue, float temperatureValue, float turbidityValue, float weightValue) {
  WiFiClient client;
  HTTPClient http;

  String apiKeyValue = "1";
  String postData = "api_key=" + apiKeyValue +
                    "&TemperatureValue=" + String(phValue) +
                    "&TurbidityValue=" + String(temperatureValue) +
                    "&pHValue=" + String(turbidityValue) +
                    "&WeightValue=" + String(weightValue);

  Serial.println("POST Data: " + postData);

  http.begin(client, serverName);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int httpResponseCode = http.POST(postData);

  if (httpResponseCode > 0) {
    String response = http.getString();
    Serial.println("Response from server: " + response);
  } else {
    Serial.println("Error in HTTP request: " + String(httpResponseCode));
  }

  http.end();
}
