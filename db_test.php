<?php
// Web-based Database Connection Tester
// Save this as db_test.php and access via http://localhost/college_fresh/db_test.php

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f4f6f9; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        h1, h2 { color: #333; }
        ul { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Database Connection Test</h1>";

// Test 1: Check if we can connect to MySQL
echo "<h2>1. MySQL Connection Test</h2>";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='status success'>✓ Successfully connected to MySQL server</div>";
    
    // Test 2: Check if our specific database exists
    echo "<h2>2. Database Existence Test</h2>";
    try {
        $db_pdo = new PDO('mysql:host=localhost;dbname=college_events_db', 'root', '');
        $db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='status success'>✓ Database 'college_events_db' exists and is accessible</div>";
        
        // Test 3: Check required tables
        echo "<h2>3. Required Tables Test</h2>";
        $required_tables = ['users', 'events', 'registrations', 'certificates'];
        $missing_tables = [];
        
        foreach($required_tables as $table) {
            $stmt = $db_pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if($stmt->rowCount() > 0) {
                echo "<div class='status success'>✓ Table '$table' exists</div>";
            } else {
                echo "<div class='status error'>✗ Table '$table' is missing</div>";
                $missing_tables[] = $table;
            }
        }
        
        // Test 4: Check admin user
        echo "<h2>4. Admin User Verification</h2>";
        $stmt = $db_pdo->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu'");
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='status success'>✓ Admin user found:</div>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
            echo "<li><strong>Name:</strong> " . $admin['name'] . "</li>";
            echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
            echo "<li><strong>Role:</strong> " . $admin['role'] . "</li>";
            echo "</ul>";
        } else {
            echo "<div class='status error'>✗ Admin user (admin@college.edu) not found</div>";
        }
        
        // Summary
        echo "<h2>📋 Summary</h2>";
        if(empty($missing_tables)) {
            echo "<div class='status success'>
                <h3>🎉 All Systems Operational!</h3>
                <p>Your database is properly configured and ready for the College Event Management System.</p>
                <p>You can now test the registration and login functionality.</p>
            </div>";
        } else {
            echo "<div class='status warning'>
                <h3>⚠️ Partial Setup Detected</h3>
                <p>Missing tables: " . implode(', ', $missing_tables) . "</p>
                <p>Please re-import the database_schema.sql file in phpMyAdmin.</p>
            </div>";
        }
        
    } catch(PDOException $e) {
        echo "<div class='status error'>✗ Database 'college_events_db' not found: " . $e->getMessage() . "</div>";
        echo "<div class='status warning'>
            <h3>🔧 Solution Required:</h3>
            <ol>
                <li>Open phpMyAdmin at <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>
                <li>Create a new database named 'college_events_db'</li>
                <li>Import the 'database_schema.sql' file</li>
                <li>Refresh this page to verify</li>
            </ol>
        </div>";
    }
    
} catch(PDOException $e) {
    echo "<div class='status error'>✗ Cannot connect to MySQL: " . $e->getMessage() . "</div>";
    echo "<div class='status warning'>
        <h3>🔧 Troubleshooting Steps:</h3>
        <ol>
            <li>Make sure XAMPP is running (Apache and MySQL services)</li>
            <li>Check if MySQL is running on port 3306</li>
            <li>Verify XAMPP installation path</li>
            <li>Try restarting XAMPP services</li>
        </ol>
    </div>";
}

echo "<h2>🔗 Quick Links</h2>";
echo "<ul>
    <li><a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a> - Database management</li>
    <li><a href='http://localhost/college_fresh/' target='_blank'>Main Application</a> - College Event Management</li>
    <li><a href='http://localhost/college_fresh/api_test.html' target='_blank'>API Tester</a> - Direct backend testing</li>
</ul>";

echo "</div></body></html>";
?>