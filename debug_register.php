<?php
// Debug Registration API - Detailed Error Reporting
// File: debug_register.php

// Enable detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

// Log incoming request
file_put_contents('debug_log.txt', "=== REGISTRATION REQUEST ===\n" . date('Y-m-d H:i:s') . "\n" . file_get_contents('php://input') . "\n\n", FILE_APPEND);

try {
    // Test database connection
    $host = 'localhost';
    $db_name = 'college_events_db';
    $username = 'root';
    $password = '';
    
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Log successful connection
    file_put_contents('debug_log.txt', "DB Connection OK\n", FILE_APPEND);
    
    // Get request data
    $data = json_decode(file_get_contents("php://input"));
    
    if (!$data) {
        throw new Exception("Invalid JSON data received");
    }
    
    // Log received data
    file_put_contents('debug_log.txt', "Data received: " . print_r($data, true) . "\n", FILE_APPEND);
    
    $name = trim($data->name ?? '');
    $email = trim($data->email ?? '');
    $password = $data->password ?? '';
    $confirm_password = $data->confirm_password ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (!empty($errors)) {
        file_put_contents('debug_log.txt', "Validation errors: " . implode(', ', $errors) . "\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Validation failed",
            "errors" => $errors
        ]);
        exit();
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        file_put_contents('debug_log.txt', "Email already exists: $email\n", FILE_APPEND);
        http_response_code(409);
        echo json_encode([
            "status" => "error",
            "message" => "Email already registered"
        ]);
        exit();
    }
    
    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
    $result = $stmt->execute([$name, $email, $hashed_password]);
    
    if ($result) {
        file_put_contents('debug_log.txt', "Registration successful for: $email\n", FILE_APPEND);
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful"
        ]);
    } else {
        throw new Exception("Failed to insert user");
    }
    
} catch (Exception $e) {
    file_put_contents('debug_log.txt', "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed: " . $e->getMessage()
    ]);
}
?>