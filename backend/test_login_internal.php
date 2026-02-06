<?php
// URL of the login endpoint
$url = 'http://localhost:8080/college_fresh/backend/auth/login_v2.php';

// Data to send
$data = array(
    'email' => 'admin@college.com',
    'password' => 'admin123'
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Fetch content even on failure status codes
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "--- RAW RESPONSE START ---\n";
echo $result;
echo "\n--- RAW RESPONSE END ---\n";

echo "HTTP_RESPONSE_HEADER:\n";
print_r($http_response_header);
?>
