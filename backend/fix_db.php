<?php
// backend/fix_db.php
// URL: http://localhost/college_fresh/backend/fix_db.php

include_once 'config/db_final.php';
header("Content-Type: text/html");

echo "<h2>Database Repair Tool</h2>";

function addColumnIfNotExists($conn, $table, $colDef) {
    $colName = explode(' ', trim($colDef))[0];
    try {
        $check = $conn->query("SHOW COLUMNS FROM $table LIKE '$colName'");
        if ($check->rowCount() == 0) {
            $conn->exec("ALTER TABLE $table ADD COLUMN $colDef");
            echo "<div style='color:green'>[ADDED] Column '$colName' added to '$table'.</div>";
        } else {
            echo "<div style='color:gray'>[OK] Column '$colName' already exists in '$table'.</div>";
        }
    } catch (PDOException $e) {
        echo "<div style='color:red'>[ERROR] Failed to add '$colName': " . $e->getMessage() . "</div>";
    }
}

// 1. Ensure 'notifications' table exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) DEFAULT 'general',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<div style='color:green'>[CHECK] Notifications table check passed.</div>";
} catch (PDOException $e) {
    echo "<div style='color:red'>[ERROR] Notifications table error: " . $e->getMessage() . "</div>";
}

// 2. Add Profile columns to 'users'
echo "<h3>Checking 'users' table columns...</h3>";
addColumnIfNotExists($conn, 'users', "college_name VARCHAR(150)");
addColumnIfNotExists($conn, 'users', "address TEXT");
addColumnIfNotExists($conn, 'users', "phone_number VARCHAR(20)");
addColumnIfNotExists($conn, 'users', "course_name VARCHAR(100)");
addColumnIfNotExists($conn, 'users', "year_of_study INT");

echo "<hr><h3>✅ Repairs Complete.</h3>";
echo "<p>Please <a href='javascript:window.history.back()'>Go Back</a> and try registering again.</p>";
?>
