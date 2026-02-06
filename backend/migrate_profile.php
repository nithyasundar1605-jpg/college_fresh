<?php
/**
 * Run this file in browser to apply profile fields migration
 * Access via: .../backend/migrate_profile.php
 */
include_once 'config/db_final.php';
header("Content-Type: text/html");
?>
<!DOCTYPE html>
<html>
<head><title>Migration</title></head>
<body style="font-family: sans-serif; padding: 20px;">
<h2>Applying Profile Fields Migration</h2>
<pre>
<?php
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
        echo "<span style='color:green'>[SUCCESS] Columns added to users table.</span>\n";
    } else {
        echo "<span style='color:blue'>[INFO] Columns already exist.</span>\n";
    }
    
    echo "\n<strong>Migration Complete.</strong>";
} catch (Exception $e) {
    echo "<span style='color:red'>[ERROR] " . $e->getMessage() . "</span>";
}
?>
</pre>
<button onclick="window.history.back()">Go Back</button>
</body>
</html>
