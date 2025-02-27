<?php
$file = 'lighting_schedule.json';
if (file_exists($file)) {
    $data = json_decode(file_get_contents($file), true);
    if ($data) {
        $start = $data['start'];
        $end = $data['end'];
        echo "Current Limit: $start - $end";
    } else {
        echo "Current Limit: 18:00 - 06:00";  // Fallback if data is missing
    }
} else {
    echo "Current Limit: 18:00 - 06:00";  // Default if no file exists
}
?>
