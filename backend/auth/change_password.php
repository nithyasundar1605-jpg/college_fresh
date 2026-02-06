<?php
include_once '../config/db_final.php';

$data = json_decode(file_get_contents("php://input"));

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (
    !empty($data->user_id) &&
    !empty($data->current_password) &&
    !empty($data->new_password)
) {
    try {
        // 1. Fetch current password hash
        $query = "SELECT password FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $data->user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hash = $row['password'];

            // 2. Verify current password
            if (password_verify($data->current_password, $hash)) {
                
                // 3. Update with new password
                $new_hash = password_hash($data->new_password, PASSWORD_BCRYPT);
                $update_query = "UPDATE users SET password = :password WHERE id = :id";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bindParam(":password", $new_hash);
                $update_stmt->bindParam(":id", $data->user_id);
                
                if ($update_stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Password updated successfully."));
                } else {
                    http_response_code(503);
                    echo json_encode(array("message" => "Unable to update password."));
                }

            } else {
                http_response_code(400); // Bad Request (Wrong password)
                echo json_encode(array("message" => "Incorrect current password."));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}
?>
