<?php
// Automated Setup Script
// This script will automatically:
// 1. Check if XAMPP services are running
// 2. Create the database
// 3. Import the schema
// 4. Verify everything works

echo "<!DOCTYPE html>
<html>
<head>
    <title>Automated Setup - College Event Management</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f4f6f9; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .step { padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
        .success { background: #d4edda; border-left-color: #28a745; }
        .error { background: #f8d7da; border-left-color: #dc3545; }
        .warning { background: #fff3cd; border-left-color: #ffc107; }
        .info { background: #d1ecf1; border-left-color: #17a2b8; }
        .btn { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .progress { width: 100%; background: #e9ecef; border-radius: 5px; height: 20px; margin: 15px 0; }
        .progress-bar { height: 100%; background: #007bff; border-radius: 5px; width: 0%; transition: width 0.3s; }
        h1, h2, h3 { color: #333; }
        .log { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 15px 0; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🚀 Automated Setup Wizard</h1>";

$log = [];
$steps_completed = 0;
$total_steps = 5;

function log_message(&$log, $message, $type = 'info') {
    $log[] = ['message' => $message, 'type' => $type, 'time' => date('H:i:s')];
    echo "<div class='step $type'>$message</div>\n";
    flush();
}

// Step 1: Check XAMPP services
log_message($log, "🔍 Step 1: Checking XAMPP Services...");
$apache_running = false;
$mysql_running = false;

// Try to connect to Apache
$apache_context = stream_context_create([
    "http" => ["timeout" => 2]
]);
$apache_response = @file_get_contents('http://localhost', false, $apache_context);
if ($apache_response !== false) {
    log_message($log, "✓ Apache is running on port 80", 'success');
    $apache_running = true;
    $steps_completed++;
} else {
    log_message($log, "✗ Apache is NOT running - Please start XAMPP Apache service", 'error');
}

// Try to connect to MySQL
try {
    $mysql_pdo = new PDO('mysql:host=localhost', 'root', '');
    $mysql_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    log_message($log, "✓ MySQL is running on port 3306", 'success');
    $mysql_running = true;
    $steps_completed++;
} catch(Exception $e) {
    log_message($log, "✗ MySQL is NOT running - Please start XAMPP MySQL service", 'error');
}

// Update progress bar
echo "<div class='progress'><div class='progress-bar' style='width: " . ($steps_completed/$total_steps*100) . "%'></div></div>";

if (!$apache_running || !$mysql_running) {
    log_message($log, "⚠️ Setup cannot continue until both Apache and MySQL are running", 'warning');
    echo "<div class='step info'>
        <h3>📋 Manual Steps Required:</h3>
        <ol>
            <li>Open XAMPP Control Panel</li>
            <li>Click 'Start' for Apache service</li>
            <li>Click 'Start' for MySQL service</li>
            <li>Wait for green 'Running' status</li>
            <li>Refresh this page</li>
        </ol>
        <a href='.' class='btn'>🔄 Refresh Setup</a>
        <a href='http://localhost' class='btn' style='background: #28a745;'>🌐 Test Apache</a>
        <a href='http://localhost/phpmyadmin' class='btn' style='background: #17a2b8;'>📊 Open phpMyAdmin</a>
    </div>";
} else {
    // Step 2: Create database
    log_message($log, "📂 Step 2: Creating Database...");
    try {
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Drop database if exists (clean slate)
        $pdo->exec("DROP DATABASE IF EXISTS college_events_db");
        
        // Create fresh database
        $pdo->exec("CREATE DATABASE college_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        log_message($log, "✓ Database 'college_events_db' created successfully", 'success');
        $steps_completed++;
        
        // Step 3: Import schema
        log_message($log, "📥 Step 3: Importing Database Schema...");
        $sql_file = __DIR__ . '/database_schema.sql';
        
        if (!file_exists($sql_file)) {
            throw new Exception("Database schema file not found at: $sql_file");
        }
        
        $sql = file_get_contents($sql_file);
        $statements = explode(';', $sql);
        $import_count = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && strlen($statement) > 10) {
                $pdo->exec("USE college_events_db");
                $pdo->exec($statement);
                $import_count++;
            }
        }
        
        log_message($log, "✓ Imported $import_count SQL statements successfully", 'success');
        $steps_completed++;
        
        // Step 4: Verify database structure
        log_message($log, "✅ Step 4: Verifying Database Structure...");
        $db_pdo = new PDO('mysql:host=localhost;dbname=college_events_db', 'root', '');
        $db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $required_tables = ['users', 'events', 'registrations', 'certificates'];
        $found_tables = 0;
        
        foreach($required_tables as $table) {
            $stmt = $db_pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if($stmt->rowCount() > 0) {
                $found_tables++;
            }
        }
        
        if ($found_tables === count($required_tables)) {
            log_message($log, "✓ All $found_tables required tables found", 'success');
            $steps_completed++;
        } else {
            log_message($log, "✗ Only found $found_tables of " . count($required_tables) . " required tables", 'error');
        }
        
        // Step 5: Test admin user
        log_message($log, "👤 Step 5: Verifying Admin User...");
        $stmt = $db_pdo->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu'");
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            log_message($log, "✓ Admin user verified: {$admin['name']} ({$admin['email']})", 'success');
        } else {
            log_message($log, "✗ Admin user not found", 'error');
        }
        
        // Final progress
        echo "<div class='progress'><div class='progress-bar' style='width: " . ($steps_completed/$total_steps*100) . "%'></div></div>";
        
        if ($steps_completed === $total_steps) {
            log_message($log, "🎉 Setup Completed Successfully!", 'success');
            echo "<div class='step success'>
                <h3>🎊 Congratulations! Setup is Complete!</h3>
                <p>All systems are operational and ready for use.</p>
                <div style='margin: 20px 0;'>
                    <a href='http://localhost/college_fresh/' class='btn' style='background: #28a745; padding: 15px 30px; font-size: 18px;'>🚀 Launch Application</a>
                </div>
                <h4>Test Credentials:</h4>
                <ul>
                    <li><strong>Admin Login:</strong> admin@college.edu / Admin@123</li>
                    <li><strong>Student Registration:</strong> Use the registration form</li>
                </ul>
            </div>";
        } else {
            log_message($log, "⚠️ Setup completed with some issues", 'warning');
        }
        
    } catch(Exception $e) {
        log_message($log, "❌ Setup failed: " . $e->getMessage(), 'error');
        echo "<div class='step error'>
            <h3>🔧 Troubleshooting:</h3>
            <p>Error: " . $e->getMessage() . "</p>
            <a href='.' class='btn'>🔄 Retry Setup</a>
        </div>";
    }
}

// Display log
echo "<h3>📋 Setup Log:</h3>";
echo "<div class='log'>";
foreach($log as $entry) {
    $icon = ['success' => '✓', 'error' => '✗', 'warning' => '⚠️', 'info' => 'ℹ️'][$entry['type']] ?? '•';
    echo "[{$entry['time']}] $icon {$entry['message']}<br>";
}
echo "</div>";

echo "<div class='step info'>
    <h3>🔗 Quick Links:</h3>
    <a href='http://localhost/college_fresh/' class='btn'>🏠 Main Application</a>
    <a href='http://localhost/college_fresh/db_test.php' class='btn'>🔍 Database Test</a>
    <a href='http://localhost/college_fresh/api_test.html' class='btn'>🔧 API Tester</a>
    <a href='http://localhost/phpmyadmin' class='btn'>📊 phpMyAdmin</a>
</div>";

echo "</div></body></html>";
?>