<?php
include_once 'config/db_final.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT event_id, event_name, coordinator_signature, management_signature FROM events WHERE event_name LIKE '%inter college event%' ORDER BY event_id DESC LIMIT 1");
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($event, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
