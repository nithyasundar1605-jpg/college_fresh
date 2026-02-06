<?php
// backend/student/update_profile.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/db_final.php';

// Debug logging
$log_file = "update_profile_debug.log";
$log_data = "Timestamp: " . date("Y-m-d H:i:s") . "\n";
$log_data .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log_data .= "POST: " . print_r($_POST, true) . "\n";
$log_data .= "FILES: " . print_r($_FILES, true) . "\n";



// Check if it's multipart (for file upload) or JSON
$is_multipart = isset($_POST['user_id']) || isset($_POST['id']);
$user_id = $is_multipart ? (isset($_POST['user_id']) ? intval($_POST['user_id']) : intval($_POST['id'])) : null;

if (!$is_multipart) {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = isset($data->user_id) ? intval($data->user_id) : (isset($data->id) ? intval($data->id) : null);
}

if (!$user_id) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit;
}

try {
    $fields = [];
    $params = [];
    
    // Handle File Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($ext, $allowed)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid file type. Allowed: jpg, jpeg, png, webp"]);
            exit;
        }

        $filename = "profile_" . $user_id . "_" . time() . "." . $ext;
        
        // Resolve absolute directory correctly
        $abs_dest_dir = realpath(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profile_pics";
        
        $log_data .= "Abs Dest Dir: $abs_dest_dir\n";
        $log_data .= "Dir writable: " . (is_writable($abs_dest_dir) ? 'YES' : 'NO') . "\n";
        
        if (!is_dir($abs_dest_dir)) {
            if (!mkdir($abs_dest_dir, 0777, true)) {
                $log_data .= "ERROR: Failed to create directory $abs_dest_dir\n";
                file_put_contents($log_file, $log_data, FILE_APPEND);
                http_response_code(500);
                echo json_encode(["message" => "Failed to create upload directory."]);
                exit;
            }
        }

        if (!is_writable($abs_dest_dir)) {
            $log_data .= "ERROR: Directory not writable $abs_dest_dir\n";
            file_put_contents($log_file, $log_data, FILE_APPEND);
            http_response_code(500);
            echo json_encode(["message" => "Upload directory is not writable."]);
            exit;
        }

        $abs_dest_path = $abs_dest_dir . DIRECTORY_SEPARATOR . $filename;
        $relative_path = "uploads/profile_pics/" . $filename;
        
        if (move_uploaded_with_fallback($file['tmp_name'], $abs_dest_path)) {
            $log_data .= "Move success: YES\n";
            $log_data .= "Dest path: $abs_dest_path\n";
            // Delete old profile pic if exists
            $stmt_old = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
            $stmt_old->execute([$user_id]);
            $old_path = $stmt_old->fetchColumn();
            if ($old_path && $old_path !== $relative_path) {
                $abs_old_path = realpath(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $old_path);
                if (file_exists($abs_old_path)) @unlink($abs_old_path);
            }
            
            $fields[] = "profile_pic = ?";
            $params[] = $relative_path;
        } else {
            $log_data .= "Move success: NO\n";
            file_put_contents($log_file, $log_data, FILE_APPEND);
            http_response_code(500);
            echo json_encode(["message" => "Failed to save the uploaded image."]);
            exit;
        }
    }

    // Handle other fields
    // Skip these keys as they are not columns or handled separately
    $skip_keys = ['user_id', 'id', 'profile_pic', 'created_at', 'role'];
    $source = $is_multipart ? $_POST : (array)$data;
    
    // We only want to update specific valid columns
    $valid_columns = ['name', 'email', 'college_name', 'address', 'phone_number', 'course_name', 'year_of_study'];

    foreach ($source as $key => $value) {
        if (in_array($key, $valid_columns)) {
            if ($key === 'email') {
                $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check->execute([$value, $user_id]);
                if ($check->rowCount() > 0) {
                    http_response_code(400);
                    echo json_encode(["message" => "Email already exists."]);
                    exit;
                }
            }
            if ($key === 'phone_number' && !empty($value)) {
                $check = $conn->prepare("SELECT id FROM users WHERE phone_number = ? AND id != ?");
                $check->execute([$value, $user_id]);
                if ($check->rowCount() > 0) {
                    http_response_code(400);
                    echo json_encode(["message" => "Phone number already exists."]);
                    exit;
                }
            }
            $fields[] = "$key = ?";
            $params[] = $value;
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["message" => "No fields to update."]);
        exit;
    }

    $params[] = $user_id;
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $log_data .= "SQL: $sql\n";
    $log_data .= "Params: " . print_r($params, true) . "\n";
    
    if ($stmt->execute($params)) {
        $rows_affected = $stmt->rowCount();
        $log_data .= "Rows affected: $rows_affected\n";
        file_put_contents($log_file, $log_data, FILE_APPEND);

        // Fetch FULL updated user to return
        $get_user = $conn->prepare("SELECT id, name, email, role, college_name, address, phone_number, course_name, year_of_study, profile_pic FROM users WHERE id = ?");
        $get_user->execute([$user_id]);
        $updated_user = $get_user->fetch(PDO::FETCH_ASSOC);
        
        $log_data .= "Updated User from DB: " . print_r($updated_user, true) . "\n";
        file_put_contents($log_file, $log_data, FILE_APPEND);
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        echo json_encode([
            "status" => "success",
            "message" => "Profile updated successfully.",
            "rows_affected" => $rows_affected,
            "user" => $updated_user
        ]);
    } else {
        $errorInfo = $stmt->errorInfo();
        http_response_code(500);
        echo json_encode(["message" => "Failed to update profile.", "error" => $errorInfo[2]]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $e->getMessage()]);
}

/**
 * Helper to handle move_uploaded_file with fallback for raw path if needed
 */
function move_uploaded_with_fallback($tmp, $dest) {
    if (move_uploaded_file($tmp, $dest)) return true;
    return copy($tmp, $dest); // Fallback for some local environments
}
?>
