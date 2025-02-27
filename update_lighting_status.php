<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aquarium";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $conn->real_escape_string($_POST['status']); // Sanitize input

    $sql = "INSERT INTO lighting (Status) VALUES ('$status')";

    if ($conn->query($sql) === TRUE) {
        echo "Status updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
