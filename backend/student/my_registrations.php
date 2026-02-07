<?php
include_once '../config/db_final.php';

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if ($user_id) {
    $sql = "SELECT r.reg_id, r.status, e.event_name, e.event_date, e.venue, e.event_id, c.id as certificate_id
            FROM registrations r
            JOIN events e ON r.event_id = e.event_id
            LEFT JOIN certificates c ON r.user_id = c.student_id AND r.event_id = c.event_id
            WHERE r.user_id = ?
            ORDER BY r.reg_id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($registrations);
} else {
    http_response_code(400);
    echo json_encode(['message' => 'User ID required']);

    
}
?>
