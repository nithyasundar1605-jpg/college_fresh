<?php
include_once 'config/db_final.php';
$id = 9;
$stmt = $conn->prepare("SELECT id, name, profile_pic FROM users WHERE id = ?");
$stmt->execute([$id]);
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
