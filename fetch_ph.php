<?php
// fetch_ph.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aquarium";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT pHValue FROM sensordata ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo htmlspecialchars($row["pHValue"]);
} else {
    echo "No data available";
}

$conn->close();
?>