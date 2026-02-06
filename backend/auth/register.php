<?php
include_once '../config/db_final.php';

$data = json_decode(file_get_contents("php://input"));

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    try {
        // Check for duplicate email
        $check_query = "SELECT id FROM users WHERE email = :email";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bindParam(":email", $data->email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Email already exists."));
            exit;
        }

        $query = "INSERT INTO users SET 
                    name=:name, 
                    email=:email, 
                    password=:password, 
                    role=:role,
                    college_name=:college_name,
                    address=:address,
                    phone_number=:phone_number,
                    course_name=:course_name,
                    year_of_study=:year_of_study";
                    
        $stmt = $conn->prepare($query);

        $name = htmlspecialchars(strip_tags($data->name));
        $email = htmlspecialchars(strip_tags($data->email));
        $password = password_hash($data->password, PASSWORD_BCRYPT);
        $role = "student"; 
        
        // New fields (optional or required based on preference, making them optional to avoid breaking if not provided, but frontend will require them)
        $college_name = isset($data->college_name) ? htmlspecialchars(strip_tags($data->college_name)) : '';
        $address = isset($data->address) ? htmlspecialchars(strip_tags($data->address)) : '';
        $phone_number = isset($data->phone_number) ? htmlspecialchars(strip_tags($data->phone_number)) : '';
        $course_name = isset($data->course_name) ? htmlspecialchars(strip_tags($data->course_name)) : '';
        $year_of_study = isset($data->year_of_study) ? intval($data->year_of_study) : 0;

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":role", $role);
        $stmt->bindParam(":college_name", $college_name);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":phone_number", $phone_number);
        $stmt->bindParam(":course_name", $course_name);
        $stmt->bindParam(":year_of_study", $year_of_study);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "User registered successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to register user."));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data."));
}