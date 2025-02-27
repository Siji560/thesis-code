<?php
$conn = new mysqli("localhost", "username", "password", "aquarium");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pH = $_POST['pHThreshold'];
$temp = $_POST['TempThreshold'];
$turbidity = $_POST['TurbidityThreshold'];

$query = "UPDATE sensordata 
          SET pHThreshold = ?, TempThreshold = ?, TurbidityThreshold = ? 
          WHERE pHThreshold != ? OR TempThreshold != ? OR TurbidityThreshold != ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("dddddd", $pH, $temp, $turbidity, $pH, $temp, $turbidity);

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
