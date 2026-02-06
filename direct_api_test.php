<?php
// Direct Registration API Test
// Bypasses all frontend and tests the backend directly

header('Content-Type: application/json');

// Test data matching what failed
$test_input = [
    'name' => 'nithya',
    'email' => 'nithya@gmail.com', 
    'password' => 'password123',
    'confirm_password' => 'password123'
];

echo "=== DIRECT REGISTRATION API TEST ===\n";
echo "Input data: " . json_encode($test_input) . "\n\n";

// Simulate the register.php logic directly
try {
    echo "1. Loading database configuration...\n";
    require_once 'backend/config/db.php';
    
    echo "2. Getting database connection...\n";
    $database = new Database();
    $db = $database->getConnection();
    echo "✓ Database connected successfully\n";
    
    echo "3. Validating input data...\n";
    $name = trim(htmlspecialchars($test_input['name']));
    $email = trim(htmlspecialchars($test_input['email']));
    $password = $test_input['password'];
    $confirm_password = $test_input['confirm_password'];
    
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long";
    if (empty($confirm_password)) $errors[] = "Confirm password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (!empty($errors)) {
        echo "✗ Validation errors found:\n";
        foreach($errors as $error) {
            echo "  - $error\n";
        }
        exit();
    }
    echo "✓ Input validation passed\n";
    
    echo "4. Checking if email already exists...\n";
    $check_query = "SELECT id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(":email", $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo "⚠ Email already registered\n";
        $existing = $check_stmt->fetch();
        echo "  Existing user ID: " . $existing['id'] . "\n";
    } else {
        echo "✓ Email is unique\n";
    }
    
    echo "5. Hashing password...\n";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    echo "✓ Password hashed\n";
    
    echo "6. Inserting new user...\n";
    $query = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'student')";
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $hashed_password);
    
    if ($stmt->execute()) {
        $user_id = $db->lastInsertId();
        echo "✓ User registered successfully!\n";
        echo "  User ID: $user_id\n";
        echo "  Name: $name\n";
        echo "  Email: $email\n";
        echo "  Role: student\n";
        
        // Return success response
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful. You can now login.",
            "user_id" => $user_id
        ]);
        
        // Clean up test user (optional)
        // $cleanup = $db->prepare("DELETE FROM users WHERE id = :id");
        // $cleanup->bindParam(":id", $user_id);
        // $cleanup->execute();
        // echo "✓ Test user cleaned up\n";
        
    } else {
        echo "✗ Failed to insert user\n";
        throw new Exception("Database insert failed");
    }
    
} catch(Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed: " . $e->getMessage()
    ]);
}
?>