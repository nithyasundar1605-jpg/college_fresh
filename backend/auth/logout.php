<?php
// User Logout API
// File: backend/auth/logout.php

require_once '../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array(
        "status" => "error",
        "message" => "Method not allowed"
    ));
    exit();
}

try {
    // Destroy session
    session_destroy();
    
    // Return success response
    http_response_code(200);
    echo json_encode(array(
        "status" => "success",
        "message" => "Logout successful"
    ));
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        "status" => "error",
        "message" => "Logout failed: " . $e->getMessage()
    ));
}
?>