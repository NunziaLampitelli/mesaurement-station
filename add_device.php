<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mac = $_POST['mac'] ?? '';
    $project = $_POST['project'] ?? '';
    $location = $_POST['location'] ?? '';

    if (empty($mac) || empty($project) || empty($location)) {
        echo "All data is mandatory";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO devices (mac_address, Project, Location) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $mac, $project, $location);

    if ($stmt->execute()) {
        echo "Device added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
