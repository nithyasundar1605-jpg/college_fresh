<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/db_final.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->reg_id) && isset($data->status)) {
        try {
            $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE reg_id = ?");
            if ($stmt->execute([$data->status, $data->reg_id])) {
                // Create notification for the student about status change
                try {
                    $notif_query = "INSERT INTO notifications (user_id, message, type) 
                                    SELECT r.user_id, 
                                           CONCAT('Your registration for \"', e.event_name, '\" has been ', ?, '!'), 
                                           'registration'
                                    FROM registrations r
                                    JOIN events e ON r.event_id = e.event_id
                                    WHERE r.reg_id = ?";
                    $notif_stmt = $conn->prepare($notif_query);
                    $notif_stmt->execute([$data->status, $data->reg_id]);
                } catch (Exception $e) {
                    // Silently fail notification if it errors, main action still completes
                }
                
                echo json_encode(["message" => "Status updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Failed to update status"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Server error: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data for update"]);
    }
    exit;
}

if ($method === 'GET') {
    $event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

    $sql = "SELECT r.reg_id, r.status, r.user_id, r.event_id, u.name as student_name, u.profile_pic, e.event_name,
            (SELECT COUNT(*) FROM certificates c WHERE c.student_id = r.user_id AND c.event_id = r.event_id) as is_generated,
            (SELECT id FROM certificates c WHERE c.student_id = r.user_id AND c.event_id = r.event_id LIMIT 1) as certificate_id,
            (SELECT certificate_type FROM certificates c WHERE c.student_id = r.user_id AND c.event_id = r.event_id LIMIT 1) as certificate_type
            FROM registrations r
            JOIN users u ON r.user_id = u.id
            JOIN events e ON r.event_id = e.event_id";

    if ($event_id > 0) {
        $sql .= " WHERE r.event_id = :event_id";
    }

    $sql .= " ORDER BY r.reg_id DESC";

    $stmt = $conn->prepare($sql);
    if ($event_id > 0) {
        $stmt->bindParam(":event_id", $event_id);
    }
    $stmt->execute();
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($registrations);
}
?>
