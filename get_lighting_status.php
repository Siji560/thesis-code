<?php
$servername = "localhost"; // Update with your database server details
$username = "root";
$password = "";
$dbname = "aquarium";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the latest lighting status
$sql = "SELECT Status FROM lighting ORDER BY Timestamp DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo $row['Status'];
} else {
    echo "No data";
}

$conn->close();
?>
