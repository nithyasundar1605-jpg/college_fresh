<?php
include_once 'config/db_final.php';
// If we are here, connection success (db_final exits on error)
header("Content-Type: application/json");
echo json_encode(["status" => "success", "message" => "Connected via db_final.php"]);
?>
