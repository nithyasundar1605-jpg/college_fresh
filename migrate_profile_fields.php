<?php
/**
 * Run this file in browser to apply profile fields migration
 * http://localhost/college_fresh/migrate_profile_fields.php
 */
include_once 'backend/config/db_final.php';
header("Content-Type: text/plain");

echo "Applying Profile Fields Migration...\n";

try {
    // Check if column exists to avoid error
    $check = $conn->query("SHOW COLUMNS FROM users LIKE 'college_name'");
    if ($check->rowCount() == 0) {
        $sql = "ALTER TABLE users
                ADD COLUMN college_name VARCHAR(150),
                ADD COLUMN address TEXT,
                ADD COLUMN phone_number VARCHAR(20),
                ADD COLUMN course_name VARCHAR(100),
                ADD COLUMN year_of_study INT";
        
        $conn->exec($sql);
        echo "[SUCCESS] Columns added to users table.\n";
    } else {
        echo "[INFO] Columns already exist.\n";
    }
    
    echo "Migration Complete.";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage();
}
?>
