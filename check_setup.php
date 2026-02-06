<?php
// Quick Database Setup Checker
// File: check_setup.php

echo "<h1>🔧 Setup Checker</h1>";

// Check 1: XAMPP Apache running
echo "<h2>1. Server Status</h2>";
$apache_running = false;
if (function_exists('apache_get_version')) {
    echo "<p style='color: green;'>✓ Apache is running</p>";
    $apache_running = true;
} else {
    echo "<p style='color: orange;'>⚠ Apache status unknown - make sure XAMPP Apache is running</p>";
}

// Check 2: Database connection
echo "<h2>2. Database Connection</h2>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=college_events_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    // Check if tables exist
    echo "<h2>3. Database Tables</h2>";
    $tables = ['users', 'events', 'registrations', 'certificates'];
    foreach($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }
    
    // Check admin user
    echo "<h2>4. Admin User</h2>";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@college.edu'");
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $admin = $stmt->fetch();
        echo "<p style='color: green;'>✓ Admin user exists:</p>";
        echo "<ul><li>Name: {$admin['name']}</li><li>Email: {$admin['email']}</li><li>Role: {$admin['role']}</li></ul>";
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Solution:</strong> Import database_schema.sql in phpMyAdmin</p>";
}

// Check 3: File permissions
echo "<h2>5. File Permissions</h2>";
$required_files = [
    'backend/config/db.php',
    'backend/auth/register.php', 
    'backend/auth/login.php'
];

foreach($required_files as $file) {
    if(file_exists($file)) {
        echo "<p style='color: green;'>✓ $file exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $file missing</p>";
    }
}

echo "<h2>📋 Setup Instructions</h2>";
echo "<ol>";
echo "<li><strong>Start XAMPP:</strong> Make sure Apache and MySQL are running</li>";
echo "<li><strong>Import Database:</strong> Go to phpMyAdmin and import database_schema.sql</li>";
echo "<li><strong>Test Registration:</strong> Try registering a new student account</li>";
echo "<li><strong>Login Test:</strong> Use admin@college.edu / Admin@123 to test admin login</li>";
echo "</ol>";

echo "<h2>📞 Need Help?</h2>";
echo "<p>If registration still fails:</p>";
echo "<ol>";
echo "<li>Check that XAMPP Apache is running on port 80</li>";
echo "<li>Verify the database 'college_events_db' exists in phpMyAdmin</li>";
echo "<li>Make sure all backend files are in the correct location</li>";
echo "<li>Check browser console for network errors</li>";
echo "</ol>";
?>