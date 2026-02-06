<?php
include_once '../config/db_final.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show HTML errors
header('Content-Type: application/json');

try {
    switch($method) {
        case 'GET':
            // List all events
            $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date DESC");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($events);
            break;

        case 'POST':
            // Handle multipart/form-data (Supports both Add and Edit)
            $id = $_POST['event_id'] ?? null;
            $name = $_POST['event_name'] ?? '';
            $date = $_POST['event_date'] ?? '';
            $desc = $_POST['description'] ?? '';
            $venue = $_POST['venue'] ?? '';
            $college = $_POST['college_name'] ?? '';
            $status = $_POST['status'] ?? 'open';
            $createdBy = $_POST['created_by'] ?? 1;

            // Safety check: ensure createdBy is a valid user ID to avoid FK failure
            $userCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
            $userCheck->execute([$createdBy]);
            if ($userCheck->rowCount() === 0) {
                $createdBy = 1; // Fallback to first admin
            }

            if (!empty($name) && !empty($date)) {
                // Normalize date format for MySQL (expects YYYY-MM-DD)
                if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
                    $date = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
                }

                // If it's a new event, check for duplicates
                if (!$id) {
                    $check = $conn->prepare("SELECT event_id FROM events WHERE event_name = ? AND event_date = ?");
                    $check->execute([$name, $date]);
                    if ($check->rowCount() > 0) {
                        http_response_code(400);
                        echo json_encode(['message' => 'Event with this name already exists on this date.']);
                        exit;
                    }
                }

                $image_url = $_POST['existing_image_url'] ?? null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/events/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid('event_') . '.' . $file_ext;
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $image_url = 'uploads/events/' . $file_name;
                    }
                }

                // Handle Signature Uploads
                $coord_sig_url = $_POST['existing_coordinator_signature'] ?? null;
                $mgmt_sig_url = $_POST['existing_management_signature'] ?? null;
                $sig_dir = '../uploads/signatures/';
                if (!is_dir($sig_dir)) mkdir($sig_dir, 0777, true);

                if (isset($_FILES['coordinator_signature']) && $_FILES['coordinator_signature']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['coordinator_signature']['name'], PATHINFO_EXTENSION);
                    $fname = uniqid('coord_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['coordinator_signature']['tmp_name'], $sig_dir . $fname)) {
                        $coord_sig_url = 'uploads/signatures/' . $fname;
                    }
                }

                if (isset($_FILES['management_signature']) && $_FILES['management_signature']['error'] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['management_signature']['name'], PATHINFO_EXTENSION);
                    $fname = uniqid('mgmt_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['management_signature']['tmp_name'], $sig_dir . $fname)) {
                        $mgmt_sig_url = 'uploads/signatures/' . $fname;
                    }
                }

                if ($id) {
                    // Update existing event
                    $sql = "UPDATE events SET event_name=?, college_name=?, description=?, event_date=?, venue=?, status=?, image_url=?, coordinator_signature=?, management_signature=? WHERE event_id=?";
                    $params = [$name, $college, $desc, $date, $venue, $status, $image_url, $coord_sig_url, $mgmt_sig_url, $id];
                } else {
                    // Insert new event
                    $sql = "INSERT INTO events (event_name, college_name, description, event_date, venue, status, created_by, image_url, coordinator_signature, management_signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$name, $college, $desc, $date, $venue, $status, $createdBy, $image_url, $coord_sig_url, $mgmt_sig_url];
                }

                $stmt = $conn->prepare($sql);
                if ($stmt->execute($params)) {
                    // If this is a new event (not an update), notify all students
                    if (!$id) {
                        try {
                            // Get all student user IDs
                            $studentQuery = "SELECT id FROM users WHERE role = 'student'";
                            $studentStmt = $conn->prepare($studentQuery);
                            $studentStmt->execute();
                            $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Create notification for each student
                            $notifQuery = "INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'event')";
                            $notifStmt = $conn->prepare($notifQuery);
                            
                            $message = "New event created: \"$name\" on $date. Register now!";
                            foreach ($students as $student) {
                                $notifStmt->execute([$student['id'], $message]);
                            }
                        } catch (Exception $e) {
                            // Silently fail notification if it errors, main action still completes
                            error_log("Failed to create event notifications: " . $e->getMessage());
                        }
                    }
                    
                    echo json_encode(['message' => $id ? 'Event updated successfully' : 'Event created successfully']);
                } else {
                    http_response_code(500);
                    $errorInfo = $stmt->errorInfo();
                    echo json_encode(['message' => 'Database operation failed: ' . $errorInfo[2]]);
                }
            }
            break;

        case 'PUT':
            // Legacy JSON support (no image)
            $json = json_decode(file_get_contents("php://input"));
            if ($json && !empty($json->event_id)) {
                 $sql = "UPDATE events SET event_name=?, college_name=?, description=?, event_date=?, venue=?, status=? WHERE event_id=?";
                 $stmt = $conn->prepare($sql);
                 if ($stmt->execute([
                    $json->event_name, 
                    $json->college_name ?? '',
                    $json->description, 
                    $json->event_date, 
                    $json->venue,
                    $json->status,
                    $json->event_id
                 ])) {
                     echo json_encode(['message' => 'Event updated successfully']);
                 } else {
                     http_response_code(500);
                     echo json_encode(['message' => 'Failed to update event']);
                 }
            }
            break;

        case 'DELETE':
            if (!empty($_GET['id'])) {
                $id = $_GET['id'];
                $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
                if ($stmt->execute([$id])) {
                    echo json_encode(['message' => 'Event deleted successfully']);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Failed to delete event']);
                }
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server Error: ' . $e->getMessage()]);
}
?>
