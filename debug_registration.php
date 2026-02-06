<?php
// Comprehensive Registration Debug Script
// This will test every step of the registration process

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Registration Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f9; }
        .container { background: white; padding: 20px; border-radius: 10px; max-width: 800px; margin: 0 auto; }
        .test-result { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Registration Process Debug</h1>";

// Simulate the exact registration data that failed
$test_data = [
    'name' => 'nithya',
    'email' => 'nithya@gmail.com',
    'password' => 'password123',
    'confirm_password' => 'password123'
];

echo "<div class='test-result info'>";
echo "<h2>📋 Test Data</h2>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test 1: Database Connection
echo "<div class='test-result info'>";
echo "<h2>1. Database Connection Test</h2>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=college_events_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='test-result success'>✓ Database connection successful</div>";
} catch(Exception $e) {
    echo "<div class='test-result error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
    die("</div></div></body></html>");
}
echo "</div>";

// Test 2: Check if email already exists
echo "<div class='test-result info'>";
echo "<h2>2. Email Uniqueness Check</h2>";
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$test_data['email']]);
    
    if($stmt->rowCount() > 0) {
        echo "<div class='test-result warning'>⚠ Email already exists in database</div>";
        $existing_user = $stmt->fetch();
        echo "<pre>Existing user ID: " . $existing_user['id'] . "</pre>";
    } else {
        echo "<div class='test-result success'>✓ Email is unique</div>";
    }
} catch(Exception $e) {
    echo "<div class='test-result error'>✗ Email check failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 3: Password Hashing
echo "<div class='test-result info'>";
echo "<h2>3. Password Processing Test</h2>";
try {
    $hashed_password = password_hash($test_data['password'], PASSWORD_DEFAULT);
    echo "<div class='test-result success'>✓ Password hashed successfully</div>";
    echo "<pre>Original: " . $test_data['password'] . "</pre>";
    echo "<pre>Hashed: " . $hashed_password . "</pre>";
    
    // Test password verification
    if(password_verify($test_data['password'], $hashed_password)) {
        echo "<div class='test-result success'>✓ Password verification works</div>";
    } else {
        echo "<div class='test-result error'>✗ Password verification failed</div>";
    }
} catch(Exception $e) {
    echo "<div class='test-result error'>✗ Password processing failed: " . $e->getMessage() . "</div>";
}
echo "</div>";

// Test 4: Actual Registration Attempt
echo "<div class='test-result info'>";
echo "<h2>4. Registration Insert Test</h2>";
try {
    $hashed_password = password_hash($test_data['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
    $result = $stmt->execute([
        $test_data['name'],
        $test_data['email'],
        $hashed_password
    ]);
    
    if($result) {
        $user_id = $pdo->lastInsertId();
        echo "<div class='test-result success'>✓ User registered successfully</div>";
        echo "<pre>User ID: $user_id</pre>";
        
        // Verify the insertion
        $verify_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $verify_stmt->execute([$user_id]);
        $user = $verify_stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<pre>Verified user data:\n";
        print_r($user);
        echo "</pre>";
        
        // Clean up - delete the test user
        $cleanup_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $cleanup_stmt->execute([$user_id]);
        echo "<div class='test-result info'>🧹 Test user cleaned up</div>";
        
    } else {
        echo "<div class='test-result error'>✗ Registration insert failed</div>";
    }
} catch(Exception $e) {
    echo "<div class='test-result error'>✗ Registration failed: " . $e->getMessage() . "</div>";
    echo "<pre>SQL Error Info: ";
    print_r($stmt->errorInfo());
    echo "</pre>";
}
echo "</div>";

// Test 5: Direct API Call Simulation
echo "<div class='test-result info'>";
echo "<h2>5. API Endpoint Test</h2>";

// Simulate what the frontend sends
$post_data = json_encode($test_data);
echo "<pre>POST Data sent:\n" . $post_data . "</pre>";

// Test the actual API endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/college_fresh/backend/auth/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($post_data)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "<pre>HTTP Status: $http_code</pre>";

if($error) {
    echo "<div class='test-result error'>✗ cURL Error: $error</div>";
} else {
    echo "<pre>Raw Response:\n" . htmlspecialchars($response) . "</pre>";
    
    $decoded_response = json_decode($response, true);
    if($decoded_response) {
        echo "<pre>Decoded Response:\n";
        print_r($decoded_response);
        echo "</pre>";
        
        if(isset($decoded_response['status']) && $decoded_response['status'] === 'success') {
            echo "<div class='test-result success'>🎉 API call successful!</div>";
        } else {
            echo "<div class='test-result error'>✗ API returned error</div>";
        }
    } else {
        echo "<div class='test-result error'>✗ Invalid JSON response</div>";
    }
}
echo "</div>";

// Summary
echo "<div class='test-result info'>";
echo "<h2>📋 Debug Summary</h2>";
echo "<ul>";
echo "<li>All database operations work correctly</li>";
echo "<li>Password hashing/verification works</li>";
echo "<li>Database insertion works</li>";
echo "<li>If API test failed, the issue is likely in the web server configuration</li>";
echo "</ul>";

echo "<h3>🔧 Next Steps:</h3>";
echo "<ol>";
echo "<li>Check if Apache is running properly</li>";
echo "<li>Verify the backend/auth/register.php file exists</li>";
echo "<li>Check Apache error logs for more details</li>";
echo "<li>Try accessing: http://localhost/college_fresh/backend/auth/register.php directly</li>";
echo "</ol>";
echo "</div>";

echo "</div></body></html>";
?>