<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

file_put_contents("../debug_test.log", "Test log entry\n", FILE_APPEND);

$input = file_get_contents("php://input");
echo json_encode([
    "message" => "Test successful", 
    "input_received" => $input,
    "log_written" => file_exists("../debug_test.log")
]);
?>
