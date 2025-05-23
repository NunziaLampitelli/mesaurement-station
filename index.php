<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['token'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$token = $_SESSION['token'];

$stmt = $conn->prepare("SELECT token FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_token);
$stmt->fetch();

if ($token !== $db_token) {
    echo "Token not valid";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Data Visualization</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        label { margin-right: 10px; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        #summary { margin-top: 20px; }
    </style>
</head>
<body>

<h2>Device Data Visualization</h2>

<form action="logout.php" method="post" style="text-align: right;">
    <button type="submit" name="logout">Logout</button>
</form>

<form id="dataForm">
    <div>
        <label for="datePicker">Date:</label>
        <input type="date" id="datePicker" name="date">

        <label for="timePicker">Time (every 30 min):</label>
        <select id="timePicker" name="time">
            <script>
                for (let h = 0; h < 24; h++) {
                    ['00', '30'].forEach(m => {
                        const hour = h.toString().padStart(2, '0');
                        document.write(`<option value="${hour}:${m}">${hour}:${m}</option>`);
                    });
                }
            </script>
        </select>

        <button type="button" id="loadButton">Load Data</button>
    </div>
</form>

<table id="dataTable">
    <thead>
        <tr>
            <th>Device</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
            <th>Reliability</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div id="summary">
    <p><strong>Average Temperature:</strong> <span id="avgTemperature">--</span> °C</p>
    <p><strong>Average Humidity:</strong> <span id="avgHumidity">--</span> %</p>
    <p><strong>Reliable Devices:</strong> <span id="reliableDevices">--</span></p>
</div>

<h2>Add a new device</h2>
<form action="add_device.php" method="post">
    <label for="mac">MAC Address:</label>
    <input type="text" name="mac" id="mac" required>

    <label for="project">Project:</label>
    <input type="text" name="project" id="project" required>

    <label for="location">Location:</label>
    <input type="text" name="location" id="location" required>

    <button type="submit">Add device</button>
</form>

<script src="app.js"></script>
</body>
</html>
