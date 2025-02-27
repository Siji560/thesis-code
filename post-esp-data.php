<?php
$servername = "localhost"; // Replace with your server address
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "aquarium"; // Replace with your database name

$api_key_value = "1";

$api_key = $pHValue = $TemperatureValue = $TurbidityValue = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);

    if ($api_key == $api_key_value) {
        $pHValue = test_input($_POST["pHValue"]);
        $TemperatureValue = test_input($_POST["TemperatureValue"]);
        $TurbidityValue = test_input($_POST["TurbidityValue"]);

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Insert data into database
        $sql = "INSERT INTO sensordata (pHValue, TemperatureValue, TurbidityValue)
                VALUES ('$pHValue', '$TemperatureValue', '$TurbidityValue')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $conn->close();
    } else {
        echo "Wrong API Key provided.";
    }
} else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
