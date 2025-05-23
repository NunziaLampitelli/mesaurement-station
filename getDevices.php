<?php
include 'db.php';

$sql = "SELECT DISTINCT DeviceId FROM measurements";
$result = $conn->query($sql);

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row['DeviceId'];
}

echo json_encode($devices);
$conn->close();
?>
