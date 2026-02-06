<?php
include_once 'backend/config/db_final.php';
try {
    echo "--- REGISTRATIONS TABLE ---\n";
    $stmt = $conn->query("DESCRIBE registrations");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
