<?php
$conn = new mysqli("localhost", "username", "password", "aquarium");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT pHThreshold, TempThreshold, TurbidityThreshold FROM sensordata LIMIT 1";
$result = $conn->query($query);

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(["pHThreshold" => 0, "TempThreshold" => 0, "TurbidityThreshold" => 0]);
}

$conn->close();
?>
