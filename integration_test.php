<?php
// Frontend-Backend Integration Test
// This script simulates how the React frontend communicates with the backend

echo "<h1>Frontend-Backend Integration Test</h1>";

// Test 1: Direct API call (simulating what happens in the browser)
echo "<h2>Test 1: Direct API Call</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/college_fresh/backend/auth/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'name' => 'nithya',
    'email' => 'nithyasundar1605@gmail.com',
    'password' => 'password123',
    'confirm_password' => 'password123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Origin: http://localhost:3000'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);

curl_close($ch);

echo "<h3>Response Status: $http_code</h3>";
echo "<h3>Headers:</h3><pre>" . htmlspecialchars($headers) . "</pre>";
echo "<h3>Body:</h3><pre>" . htmlspecialchars($body) . "</pre>";

// Test 2: Preflight request (CORS)
echo "<h2>Test 2: CORS Preflight Request</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/college_fresh/backend/auth/register.php');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: http://localhost:3000',
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Content-Type',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$preflight_response = curl_exec($ch);
$preflight_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Preflight Status: $preflight_code</h3>";
echo "<h3>Preflight Headers:</h3><pre>" . htmlspecialchars($preflight_response) . "</pre>";

// Test 3: Check if the database actually has the user
echo "<h2>Test 3: Database Verification</h2>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=college_events_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute(['nithyasundar1605@gmail.com']);
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ User found in database:</p>";
        echo "<pre>" . print_r($user, true) . "</pre>";
        
        // Clean up test user
        $delete_stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $delete_stmt->execute(['nithyasundar1605@gmail.com']);
        echo "<p>Test user cleaned up from database.</p>";
    } else {
        echo "<p style='color: orange;'>⚠ User not found in database (might not have been created due to CORS issue)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Conclusion:</h2>";
if ($http_code >= 200 && $http_code < 300) {
    echo "<p style='color: green;'>✓ Backend API is working correctly</p>";
    echo "<p>The issue is likely with frontend CORS configuration or React app not reflecting API changes</p>";
} else {
    echo "<p style='color: red;'>✗ Backend API has issues</p>";
}
?>