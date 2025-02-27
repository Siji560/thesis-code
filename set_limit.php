<?php
// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "aquarium"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
$limitType = $_POST['type'] ?? '';
$newLimit = $_POST['value'] ?? '';

// Validate input
$allowedTypes = ['pHThreshold', 'TempThreshold', 'TurbidityThreshold'];
if (!in_array($limitType, $allowedTypes) || !is_numeric($newLimit)) {
    die("Invalid input.");
}

// Fetch the latest non-null values from the previous row
$latestQuery = "SELECT pHThreshold, TempThreshold, TurbidityThreshold FROM thresholds ORDER BY thresholdID DESC LIMIT 1";
$latestResult = $conn->query($latestQuery);
$latestRow = $latestResult->fetch_assoc();

$pH = $latestRow['pHThreshold'];
$temp = $latestRow['TempThreshold'];
$turbidity = $latestRow['TurbidityThreshold'];

// Update the new limit with posted value
if ($limitType == 'pHThreshold') {
    $pH = $newLimit;
} elseif ($limitType == 'TempThreshold') {
    $temp = $newLimit;
} elseif ($limitType == 'TurbidityThreshold') {
    $turbidity = $newLimit;
}

// Insert the new row with updated and copied values
$query = "INSERT INTO thresholds (pHThreshold, TempThreshold, TurbidityThreshold) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ddd", $pH, $temp, $turbidity);

if ($stmt->execute()) {
    echo "Limit inserted successfully!\n";
} else {
    echo "Error inserting limit: " . $stmt->error . "\n";
}

// Fetch and send current sensor values
$sensorQuery = "SELECT pHThreshold, TempThreshold, TurbidityThreshold FROM thresholds ORDER BY thresholdID DESC LIMIT 1";
$sensorResult = $conn->query($sensorQuery);

if ($sensorResult->num_rows > 0) {
    $sensorData = $sensorResult->fetch_assoc();
    echo "Current Sensor Values: \n";
    echo "pH: " . $sensorData['pHThreshold'] . "\n";
    echo "Temperature: " . $sensorData['TempThreshold'] . "\n";
    echo "Turbidity: " . $sensorData['TurbidityThreshold'] . "\n";
} else {
    echo "No sensor data available.\n";
}

$stmt->close();
$conn->close();
?>
