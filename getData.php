<?php
include 'db.php';

$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;

if (!$date || !$time || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date) || !preg_match("/^\d{2}:\d{2}$/", $time)) {
    echo json_encode(['error' => 'Invalid parameters!']);
    exit;
}

$startTime = "$date $time:00";
$endTime = date("Y-m-d H:i:s", strtotime($startTime) + 29 * 60 + 59);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=reserve', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Media dal sensore di riferimento (tabella maj)
    $stmtRef = $pdo->prepare("SELECT AVG(Temperature) AS refTemp, AVG(Humidity) AS refHum 
                              FROM maj
                              WHERE Log_time BETWEEN :start AND :end");
    $stmtRef->execute([':start' => $startTime, ':end' => $endTime]);
    $reference = $stmtRef->fetch(PDO::FETCH_ASSOC);

    if (!$reference || is_null($reference['refTemp']) || is_null($reference['refHum'])) {
        echo json_encode(['error' => 'no reference data available.']);
        exit;
    }

    $refTemp = round($reference['refTemp'], 1);
    $refHum = round($reference['refHum'], 1);

    // Dati da tutti i dispositivi
    $stmt = $pdo->prepare("SELECT DeviceId, AVG(Temperature) AS temperature, AVG(Humidity) AS humidity 
                           FROM measurements 
                           WHERE Log_time BETWEEN :start AND :end 
                           GROUP BY DeviceId");
    $stmt->execute([':start' => $startTime, ':end' => $endTime]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo json_encode(['error' => 'no data available for date and time']);
        exit;
    }

    $tempTolerance = 1.0;
    $humTolerance = 5.0;

    $reliableTempDevices = [];
    $reliableHumDevices = [];
    $dataMap = [];

    foreach ($rows as $row) {
        $deviceId = $row['DeviceId'];
        $temperature = round($row['temperature'], 1);
        $humidity = round($row['humidity'], 1);

        if (abs($temperature - $refTemp) <= $tempTolerance) {
            $reliableTempDevices[] = $deviceId;
        }

        if (abs($humidity - $refHum) <= $humTolerance) {
            $reliableHumDevices[] = $deviceId;
        }

        $dataMap[$deviceId] = [
            'DeviceId' => $deviceId,
            'temperature' => $temperature,
            'humidity' => $humidity
        ];
    }

    echo json_encode([
        'data' => array_values($dataMap),
        'refTemperature' => $refTemp,
        'refHumidity' => $refHum,
        'reliableTemperatureDevices' => $reliableTempDevices,
        'reliableHumidityDevices' => $reliableHumDevices
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>

