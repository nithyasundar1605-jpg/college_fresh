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

if (!empty($data->user_id) && !empty($data->event_id)) {
    // 1. Check if event is open - Fetch more details for email
    $eventCheck = $conn->prepare("SELECT event_name, event_date, status, venue FROM events WHERE event_id = ?");
    $eventCheck->execute([$data->event_id]);
    $event = $eventCheck->fetch(PDO::FETCH_ASSOC);

    if (!$event || $event['status'] !== 'open') {
        http_response_code(400);
        echo json_encode(['message' => 'Event is closed or does not exist']);
        exit;
    }

    // 2. Check if already registered
    $dupCheck = $conn->prepare("SELECT reg_id FROM registrations WHERE user_id = ? AND event_id = ?");
    $dupCheck->execute([$data->user_id, $data->event_id]);
    
    if ($dupCheck->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['message' => 'You have already registered for this event']);
        exit;
    }

    // 3. Register
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, status) VALUES (?, ?, 'pending')");
    if ($stmt->execute([$data->user_id, $data->event_id])) {
        // Notify all admins about the new registration
        try {
            $notif_query = "INSERT INTO notifications (user_id, message, type)
                            SELECT u.id, 
                                   CONCAT((SELECT name FROM users WHERE id = ?), ' has registered for \"', e.event_name, '\"'),
                                   'registration'
                            FROM users u
                            CROSS JOIN events e
                            WHERE u.role = 'admin' AND e.event_id = ?";
            $notif_stmt = $conn->prepare($notif_query);
            $notif_stmt->execute([$data->user_id, $data->event_id]);
        } catch (Exception $e) { }

        // --- NEW: Send Email Confirmation ---
        include_once '../utils/mail_helper.php';
        try {
            // Fetch User Email
            $userQ = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
            $userQ->execute([$data->user_id]);
            $userRes = $userQ->fetch(PDO::FETCH_ASSOC);

            if ($userRes && !empty($userRes['email'])) {
                $subject = "Registration Confirmed: " . $event['event_name'];
                $body = "<h3>Hello " . htmlspecialchars($userRes['name']) . ",</h3>";
                $body .= "<p>You have successfully registered for the event <strong>" . htmlspecialchars($event['event_name']) . "</strong>.</p>";
                $body .= "<p><strong>Date:</strong> " . $event['event_date'] . "<br>";
                $body .= "<strong>Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>";
                $body .= "<p>We look forward to seeing you there!</p>";
                
                // Fire and forget (or log error) - don't block response
                sendMail($userRes['email'], $userRes['name'], $subject, $body);
            }
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
        }
        
        echo json_encode(['message' => 'Registration successful']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Registration failed']);
    }

} else {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete data']);
}
?>
