<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); 

// FIX: Use absolute path for safety
include_once __DIR__ . '/../config/db_final.php';

header("Content-Type: application/json");

// DEBUG LOGGING
$logFile = __DIR__ . '/login_debug.log';
$debugInfo = "Timestamp: " . date('Y-m-d H:i:s') . "\n";
$debugInfo .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$debugInfo .= "Input: " . file_get_contents("php://input") . "\n";
file_put_contents($logFile, $debugInfo, FILE_APPEND);

// INLINE SIMPLE JWT to avoid dependency issues
if (!class_exists('SimpleJWT')) {
    class SimpleJWT {
        private static $secret = "college_event_secret_key_12345";
        
        public static function encode($payload) {
            $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
            $payload = json_encode($payload);
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        }
    }
}

$input = file_get_contents("php://input");
$data = json_decode($input);

// Debug Data
// file_put_contents("login_debug.txt", date('Y-m-d H:i:s') . " - Input: " . $input . "\n", FILE_APPEND);

if (!empty($data->email) && !empty($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // --- HARDCODED ADMIN CHECK ---
    if ($email === 'admin@college.com' && $password === 'admin123') {
        $token_payload = [
            "iss" => "college_event_system",
            "aud" => "college_users",
            "iat" => time(),
            "nbf" => time(),
            "data" => [
                "id" => 1,
                "name" => "System Admin",
                "email" => "admin@college.com",
                "role" => "admin",
                "profile_pic" => null
            ]
        ];
        $jwt = SimpleJWT::encode($token_payload);

        http_response_code(200);
        echo json_encode(array(
            "message" => "Login successful.",
            "jwt" => $jwt,
            "user" => $token_payload['data']
        ));
        exit;
    }
    // -----------------------------

    $query = "SELECT id, name, email, password, role, profile_pic FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($password, $row['password'])) {
            $token_payload = [
                "iss" => "college_event_system",
                "aud" => "college_users",
                "iat" => time(),
                "nbf" => time(),
                "data" => [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "role" => $row['role'],
                    "profile_pic" => $row['profile_pic']
                ]
            ];

            $jwt = SimpleJWT::encode($token_payload);

            http_response_code(200);
            echo json_encode(array(
                "message" => "Login successful.",
                "jwt" => $jwt,
                "user" => [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "role" => $row['role'],
                    "profile_pic" => $row['profile_pic']
                ]
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Invalid password."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "User not found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}