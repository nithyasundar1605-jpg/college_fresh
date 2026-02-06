<?php
// Test Script for Backend APIs
// File: test_backend.php

echo "<h1>Backend API Test</h1>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
require_once 'backend/config/db.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test if tables exist
echo "<h2>2. Database Tables Test</h2>";
$tables = ['users', 'events', 'registrations', 'certificates'];
foreach($tables as $table) {
    try {
        $query = "SHOW TABLES LIKE '$table'";
        $stmt = $db->prepare($query);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' not found</p>";
        }
    } catch(Exception $e) {
        echo "<p style='color: red;'>✗ Error checking table '$table': " . $e->getMessage() . "</p>";
    }
}

// Test if admin user exists
echo "<h2>3. Admin User Test</h2>";
try {
    $query = "SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    if($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Admin user found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $admin['id'] . "</li>";
        echo "<li>Name: " . $admin['name'] . "</li>";
        echo "<li>Email: " . $admin['email'] . "</li>";
        echo "<li>Role: " . $admin['role'] . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Admin user not found</p>";
    }
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ Error checking admin user: " . $e->getMessage() . "</p>";
}

echo "<h2>4. API Endpoints Location</h2>";
$api_endpoints = [
    'backend/auth/register.php',
    'backend/auth/login.php',
    'backend/auth/logout.php',
    'backend/config/db.php'
];

foreach($api_endpoints as $endpoint) {
    if(file_exists($endpoint)) {
        echo "<p style='color: green;'>✓ $endpoint exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $endpoint not found</p>";
    }
}

echo "<h2>5. CORS Headers Test</h2>";
$headers = getallheaders();
echo "<p>Current headers:</p>";
echo "<pre>" . print_r($headers, true) . "</pre>";

echo "<p><strong>Note:</strong> For full API testing, you need to:</p>";
echo "<ol>";
echo "<li>Start XAMPP Apache server</li>";
echo "<li>Import the database_schema.sql file</li>";
echo "<li>Test APIs using Postman or the frontend application</li>";
echo "</ol>";
?>