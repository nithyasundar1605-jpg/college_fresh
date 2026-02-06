<?php
include_once 'config/db_final.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET') {
        // GET /backend/gallery_api.php?event_id=123
        $eventId = $_GET['event_id'] ?? null;
        
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['message' => 'Event ID is required']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM event_gallery WHERE event_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$eventId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($images);
    } 
    elseif ($method === 'POST') {
        // POST /backend/gallery_api.php (Upload)
        // Body: form-data with 'event_id' and 'images[]'
        
        $eventId = $_POST['event_id'] ?? null;
        
        if (!$eventId || !isset($_FILES['images'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Event ID and Images are required']);
            exit;
        }

        $uploadDir = 'uploads/gallery/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uploadedImages = [];
        $files = $_FILES['images'];
        $count = count($files['name']);

        $stmt = $conn->prepare("INSERT INTO event_gallery (event_id, image_path) VALUES (?, ?)");

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid('gallery_') . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                    $dbPath = 'uploads/gallery/' . $filename;
                    $stmt->execute([$eventId, $dbPath]);
                    $uploadedImages[] = [
                        'id' => $conn->lastInsertId(),
                        'image_path' => $dbPath,
                        'event_id' => $eventId
                    ];
                }
            }
        }

        echo json_encode(['message' => 'Upload successful', 'images' => $uploadedImages]);
    }
    elseif ($method === 'DELETE') {
        // DELETE /backend/gallery_api.php?id=123 (Delete specific image)
        
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => 'Image ID is required']);
            exit;
        }

        // Get image path first to unlink file
        $stmt = $conn->prepare("SELECT image_path FROM event_gallery WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            // Remove from DB
            $delStmt = $conn->prepare("DELETE FROM event_gallery WHERE id = ?");
            if ($delStmt->execute([$id])) {
                // Remove from Filesystem
                $filePath = '../' . $image['image_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                echo json_encode(['message' => 'Image deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to delete from database']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Image not found']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server Error: ' . $e->getMessage()]);
}
?>
