<?php
// Quick Registration Test
// Tests if the registration API is working

echo "<!DOCTYPE html>
<html>
<head>
    <title>Registration Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .result { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .btn { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>";

echo "<h1>🧪 Registration API Test</h1>";

// Test data
$test_data = [
    'name' => 'Test Student',
    'email' => 'test_' . time() . '@example.com', // Unique email
    'password' => 'password123',
    'confirm_password' => 'password123'
];

echo "<div class='result info'>";
echo "<h3>Test Data:</h3>";
echo "<ul>";
echo "<li>Name: " . $test_data['name'] . "</li>";
echo "<li>Email: " . $test_data['email'] . "</li>";
echo "<li>Password: " . str_repeat('*', strlen($test_data['password'])) . "</li>";
echo "</ul>";
echo "</div>";

// Test registration
echo "<h2>Testing Registration...</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/college_fresh/backend/auth/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo "<div class='result error'>";
    echo "<h3>❌ Network Error</h3>";
    echo "<p>Could not connect to registration API</p>";
    echo "</div>";
} else {
    $result = json_decode($response, true);
    
    echo "<div class='result " . ($http_code >= 200 && $http_code < 300 ? 'success' : 'error') . "'>";
    echo "<h3>Status: $http_code</h3>";
    echo "<h4>Response:</h4>";
    echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre>";
    echo "</div>";
    
    if (isset($result['status']) && $result['status'] === 'success') {
        echo "<div class='result success'>";
        echo "<h3>🎉 Registration Successful!</h3>";
        echo "<p>The authentication system is working correctly.</p>";
        echo "<a href='http://localhost/college_fresh/' class='btn'>Go to Main Application</a>";
        echo "</div>";
    } else {
        echo "<div class='result error'>";
        echo "<h3>❌ Registration Failed</h3>";
        echo "<p>There may be an issue with the backend API.</p>";
        echo "</div>";
    }
}

echo "<div class='result info'>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='http://localhost/college_fresh/' class='btn'>Try Registration in App</a></li>";
echo "<li><a href='http://localhost/college_fresh/auto_setup.php' class='btn'>Run Full Setup</a></li>";
echo "<li><a href='http://localhost/phpmyadmin' class='btn'>Check Database</a></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>