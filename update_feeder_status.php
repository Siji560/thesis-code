<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status = $_POST["status"];
    $amount = $_POST["amount"];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "aquarium");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO feeder (Status, feedamount(grams), timestamp) VALUES ('$status', '$amount', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "Feeder status updated successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
