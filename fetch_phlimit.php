<?php
// fetch_turbidity.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aquarium";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT pHThreshold FROM thresholds ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo htmlspecialchars ("Current Limit: ".$row["pHThreshold"]);
} else {
    echo "No data available";
}

$conn->close();
?>
