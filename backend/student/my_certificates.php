<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

include_once '../config/db_final.php';
// In a real scenario, we'd validate the JWT here. 
// For this setup, we'll assume the client sends the User ID in a header or query param for simplicity, 
// OR we decode the Bearer token. 
// Given the constraints and previous files, I'll check for a 'user_id' parameter.

// Retrieve student_id from query parameter
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Student ID is required."]);
    exit;
}

try {
    // Fetch certificates
    // Join with events to get event name
    $query = "SELECT c.id, c.generated_at, c.certificate_path, c.certificate_uid, c.certificate_type, e.event_name, e.event_date 
              FROM certificates c
              JOIN events e ON c.event_id = e.event_id
              WHERE c.student_id = :student_id
              ORDER BY c.generated_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":student_id", $student_id);
    $stmt->execute();
    
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($certificates);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error fetching certificates: " . $e->getMessage()]);
}
?>
