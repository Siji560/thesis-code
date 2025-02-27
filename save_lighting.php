<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start'];
    $end = $_POST['end'];

    $data = [
        'start' => $start,
        'end' => $end
    ];

    file_put_contents('lighting_schedule.json', json_encode($data));

    echo "Lighting schedule saved successfully.";
}
?>
