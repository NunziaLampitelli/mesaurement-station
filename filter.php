<?php
include 'db.php';

$minHumidity = isset($_GET['minHumidity']) ? (float)$_GET['minHumidity'] : 0;
$maxHumidity = isset($_GET['maxHumidity']) ? (float)$_GET['maxHumidity'] : 100;

$sql = "
    SELECT 
        DeviceId,
        DATE_FORMAT(Log_time, '%Y-%m-%d %H:%i:00') - INTERVAL (MINUTE(Log_time) % 10) MINUTE AS TimeSlot,
        AVG(Temperature) AS AvgTemperature,
        AVG(Humidity) AS AvgHumidity
    FROM measurements
    WHERE Log_time >= '2024-12-01 00:00:00'
    GROUP BY DeviceId, TimeSlot
    HAVING AvgHumidity BETWEEN $minHumidity AND $maxHumidity
    ORDER BY TimeSlot, DeviceId;
";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $timeSlot = $row['TimeSlot'];
    $data[$timeSlot][$row['DeviceId']] = $row;
}

function findSimilarDevices($devices, $threshold = 1.5) {
    $similar = [];
    foreach ($devices as $deviceId => $row) {
        foreach ($devices as $compareId => $compareRow) {
            if ($deviceId !== $compareId) {
                $diffTemp = abs($row['AvgTemperature'] - $compareRow['AvgTemperature']);
                $diffHum = abs($row['AvgHumidity'] - $compareRow['AvgHumidity']);

                if ($diffTemp <= $threshold && $diffHum <= $threshold) {
                    $similar[$deviceId][] = $compareId;
                }
            }
        }
    }
    return $similar;
}

foreach ($data as $timeSlot => $devices) {
    foreach ($devices as $row) {
        echo "<tr>
                <td>{$row['DeviceId']}</td>
                <td>{$row['TimeSlot']}</td>
                <td>" . number_format($row['AvgTemperature'], 2) . "°C</td>
                <td>" . number_format($row['AvgHumidity'], 2) . "%</td>
              </tr>";
    }
    
    $similar = findSimilarDevices($devices);
    
    if (!empty($similar)) {
        echo "<tr class='highlight'>
                <td colspan='4'>Similar Devices for $timeSlot: ";
        foreach ($similar as $deviceId => $matches) {
            echo "Device $deviceId similar to: " . implode(', ', $matches) . " | ";
        }
        echo "</td>
              </tr>";
    }
}

$conn->close();
?>
