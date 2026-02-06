<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

include_once '../config/db_final.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit;
}

try {
    $query = "SELECT id, message, type, is_read, created_at 
              FROM notifications 
              WHERE user_id = ? 
              ORDER BY created_at DESC 
              LIMIT 50";
              
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert is_read to boolean for frontend consistency if needed
    foreach ($notifications as &$n) {
        $n['is_read'] = (bool)$n['is_read'];
    }
    
    echo json_encode($notifications);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error fetching notifications: " . $e->getMessage()]);
}
?>
