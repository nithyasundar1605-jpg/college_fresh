<?php
include_once 'backend/config/db_final.php';

try {
    $sql = file_get_contents('add_gallery_table.sql');
    $conn->exec($sql);
    echo "Table 'event_gallery' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
