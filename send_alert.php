<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phValue = $_POST['phValue'];
    $phLimit = $_POST['phLimit'];

    $to = "ccjaycamposagrado@gmail.com";  // Change to your recipient email
    $subject = "Aquarium pH Alert";
    $message = "Warning! The current pH value is $phValue, which exceeds the safe limit of $phLimit.";
    $headers = "From: aquarium-monitor@example.com";

    if (mail($to, $subject, $message, $headers)) {
        echo "Alert email sent successfully.";
    } else {
        echo "Failed to send alert email.";
    }
}
?>
