<?php
include_once '../config/db_final.php';

try {
    $columns = [
        "coordinator_signature" => "VARCHAR(255) DEFAULT NULL",
        "management_signature" => "VARCHAR(255) DEFAULT NULL"
    ];

    foreach ($columns as $column => $definition) {
        // Check if column exists
        $check = $conn->prepare("SHOW COLUMNS FROM events LIKE ?");
        $check->execute([$column]);
        
        if ($check->rowCount() == 0) {
            // Add column
            $sql = "ALTER TABLE events ADD COLUMN $column $definition";
            $conn->exec($sql);
            echo "Added column '$column' to 'events' table.<br>";
        } else {
            echo "Column '$column' already exists.<br>";
        }
    }
    echo "Migration completed successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
