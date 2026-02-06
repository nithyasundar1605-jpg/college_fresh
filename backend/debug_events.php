<?php
include_once 'config/db_final.php';
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT event_id, event_name, coordinator_signature, management_signature FROM events ORDER BY event_id DESC LIMIT 5");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
