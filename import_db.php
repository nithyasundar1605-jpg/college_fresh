<?php
// Quick Database Import Script
// Access via: http://localhost/college_fresh/import_db.php

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Import Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn { background: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .status { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>📊 Database Import Tool</h1>";

if ($_GET['action'] === 'import') {
    try {
        // Connect to MySQL
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Read SQL file
        $sql_file = __DIR__ . '/database_schema.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("Database schema file not found at: $sql_file");
        }
        
        $sql = file_get_contents($sql_file);
        
        // Split by semicolon and execute each statement
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<div class='status success'>
            <h3>✅ Success!</h3>
            <p>Database 'college_events_db' has been created and populated successfully.</p>
        </div>";
        
        echo "<div class='status info'>
            <h3>Next Steps:</h3>
            <p>1. <a href='http://localhost/college_fresh/db_test.php' class='btn'>Test Database Connection</a></p>
            <p>2. <a href='http://localhost/college_fresh/' class='btn'>Go to Main Application</a></p>
            <p>3. <a href='http://localhost/phpmyadmin' class='btn'>View in phpMyAdmin</a></p>
        </div>";
        
    } catch(Exception $e) {
        echo "<div class='status error'>
            <h3>❌ Error:</h3>
            <p>" . $e->getMessage() . "</p>
        </div>";
    }
} else {
    echo "<div class='status info'>
        <h3>This tool will:</h3>
        <ul>
            <li>Create the 'college_events_db' database</li>
            <li>Create all required tables</li>
            <li>Insert the default admin user</li>
        </ul>
    </div>";
    
    echo "<a href='?action=import' class='btn' style='background: #28a745;'>🚀 Import Database Now</a>";
    echo "<a href='http://localhost/phpmyadmin' class='btn' style='background: #17a2b8;'>📊 Open phpMyAdmin</a>";
}

echo "</div></body></html>";
?>