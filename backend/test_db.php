<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once 'config/db.php';

if ($conn) {
    echo json_encode(["status" => "success", "message" => "Database connected successfully"]);
} else {
    http_response_code(200); // Changed to 200 to see error in frontend/tools
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
}
?>
