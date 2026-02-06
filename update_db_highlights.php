<?php
include_once 'backend/config/db_final.php';

try {
    $sql = file_get_contents('add_highlights_info_table.sql');
    $conn->exec($sql);
    echo "Table 'event_highlights_info' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
