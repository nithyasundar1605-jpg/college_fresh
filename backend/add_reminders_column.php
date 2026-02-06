<?php
include_once 'config/db_final.php';

try {
    $sql = "ALTER TABLE events ADD COLUMN reminders_sent TINYINT(1) DEFAULT 0";
    $conn->exec($sql);
    echo "Column reminders_sent added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
