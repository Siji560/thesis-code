<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true, 512, JSON_BIGINT_AS_STRING); // Ensure proper float parsing

    if (!isset($input['status']) || !isset($input['grams'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $status = $input['status'];
    $grams = floatval($input['grams']); // Ensure the grams value is treated as a float

    // Database connection
    $conn = new mysqli("localhost", "root", "", "aquarium");

    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Insert data into the database
    $stmt = $conn->prepare("INSERT INTO feeder (Status, feedamount) VALUES (?, ?)");
    $stmt->bind_param('sd', $status, $grams); // Change 'si' to 'sd' (s = string, d = double for decimals)

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Data recorded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record data']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
