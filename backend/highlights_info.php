<?php
include_once 'config/db_final.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $eventId = $_GET['event_id'] ?? null;
        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['message' => 'Event ID is required']);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM event_highlights_info WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Decode JSON fields for frontend
            $data['winners'] = json_decode($data['winners']);
            $data['guests'] = json_decode($data['guests']);
            $data['statistics'] = json_decode($data['statistics']);
            $data['sponsors'] = json_decode($data['sponsors']);
            $data['resources'] = json_decode($data['resources']);
            echo json_encode($data);
        } else {
            echo json_encode(null); // No report yet
        }
    } 
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);
        $eventId = $input['event_id'] ?? null;

        if (!$eventId) {
            http_response_code(400);
            echo json_encode(['message' => 'Event ID is required']);
            exit;
        }

        // Prepare JSON data
        $summary = $input['summary'] ?? '';
        $winners = isset($input['winners']) ? json_encode($input['winners']) : null;
        $guests = isset($input['guests']) ? json_encode($input['guests']) : null;
        $statistics = isset($input['statistics']) ? json_encode($input['statistics']) : null;
        $sponsors = isset($input['sponsors']) ? json_encode($input['sponsors']) : null;
        $resources = isset($input['resources']) ? json_encode($input['resources']) : null;

        // Check if exists
        $check = $conn->prepare("SELECT id FROM event_highlights_info WHERE event_id = ?");
        $check->execute([$eventId]);
        
        if ($check->rowCount() > 0) {
            // Update
            $sql = "UPDATE event_highlights_info SET summary=?, winners=?, guests=?, statistics=?, sponsors=?, resources=? WHERE event_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$summary, $winners, $guests, $statistics, $sponsors, $resources, $eventId]);
        } else {
            // Insert
            $sql = "INSERT INTO event_highlights_info (event_id, summary, winners, guests, statistics, sponsors, resources) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$eventId, $summary, $winners, $guests, $statistics, $sponsors, $resources]);
        }

        echo json_encode(['message' => 'Highlights updated successfully']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Server Error: ' . $e->getMessage()]);
}
?>
