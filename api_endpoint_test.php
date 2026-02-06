<?php
// API Endpoint Test
// Tests if the registration endpoint is accessible via HTTP

// Set proper headers for web requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test the actual registration endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "=== API ENDPOINT TEST ===\n";
    
    // Read raw POST data
    $raw_data = file_get_contents('php://input');
    echo "Received data: $raw_data\n";
    
    // Parse JSON
    $data = json_decode($raw_data, true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit();
    }
    
    echo "Parsed data:\n";
    print_r($data);
    
    // Test database connection
    try {
        require_once 'backend/config/db.php';
        $database = new Database();
        $db = $database->getConnection();
        echo "✓ Database connected\n";
        
        // Test registration logic
        $name = trim(htmlspecialchars($data['name']));
        $email = trim(htmlspecialchars($data['email']));
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];
        
        // Basic validation
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception("Missing required fields");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match");
        }
        
        echo "✓ Validation passed\n";
        
        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit();
        }
        
        echo "✓ Email is unique\n";
        
        // Register user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        
        if ($stmt->execute([$name, $email, $hashed_password])) {
            $user_id = $db->lastInsertId();
            http_response_code(201);
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful!',
                'user_id' => $user_id
            ]);
            echo "\n✓ User registered successfully (ID: $user_id)\n";
        } else {
            throw new Exception("Failed to insert user");
        }
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Registration failed: ' . $e->getMessage()
        ]);
        echo "\n✗ Error: " . $e->getMessage() . "\n";
    }
} else {
    // Display test form for manual testing
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>API Endpoint Test</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; }
            input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
            button { background: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; }
            .result { margin-top: 20px; padding: 15px; border-radius: 4px; }
            .success { background: #d4edda; color: #155724; }
            .error { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>
        <h1>🔧 API Endpoint Test</h1>
        
        <div class="form-group">
            <label>Name:</label>
            <input type="text" id="name" value="Test User">
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" id="email" value="test@example.com">
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" id="password" value="password123">
        </div>
        
        <div class="form-group">
            <label>Confirm Password:</label>
            <input type="password" id="confirm_password" value="password123">
        </div>
        
        <button onclick="testApi()">Test Registration API</button>
        
        <div id="result"></div>

        <script>
            async function testApi() {
                const data = {
                    name: document.getElementById('name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    confirm_password: document.getElementById('confirm_password').value
                };
                
                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    displayResult(result, response.status);
                    
                } catch (error) {
                    displayResult({message: 'Network error: ' + error.message}, 0);
                }
            }
            
            function displayResult(result, status) {
                const resultDiv = document.getElementById('result');
                const isSuccess = status >= 200 && status < 300;
                
                resultDiv.className = 'result ' + (isSuccess ? 'success' : 'error');
                resultDiv.innerHTML = `
                    <h3>Status: ${status} (${isSuccess ? 'SUCCESS' : 'ERROR'})</h3>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                `;
            }
        </script>
    </body>
    </html>
    <?php
}
?>