<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once '../config/db_final.php';

// Accept user_id from query params to check registration status
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

try {
    // Fetch all open events and check if the current user is registered
    $query = "SELECT e.*, 
              (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.event_id AND r.user_id = :user_id) as is_registered
              FROM events e 
              ORDER BY e.event_date ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error fetching events: " . $e->getMessage()]);
}
?>
