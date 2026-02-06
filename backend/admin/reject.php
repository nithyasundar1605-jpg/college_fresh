<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/db_final.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->reg_id)) {
    $stmt = $conn->prepare("UPDATE registrations SET status='rejected' WHERE reg_id=?");
    if ($stmt->execute([$data->reg_id])) {
        // Add notification for the student
        try {
            $notif_query = "INSERT INTO notifications (user_id, message, type) 
                            SELECT r.user_id, CONCAT('Your registration for \"', e.event_name, '\" has been rejected.'), 'registration'
                            FROM registrations r
                            JOIN events e ON r.event_id = e.event_id
                            WHERE r.reg_id = ?";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->execute([$data->reg_id]);
        } catch (Exception $e) {
            // Silently fail notification if it errors
        }

        echo json_encode(['message' => 'Registration rejected successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to reject registration']);
    }
} else {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid request']);
}
?>
