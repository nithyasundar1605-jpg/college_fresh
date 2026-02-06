<?php
// backend/admin/students.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include_once '../config/db_final.php';

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    if ($student_id > 0) {
        // Fetch detailed info for a specific student
        $stmt = $conn->prepare("SELECT id, name, email, college_name, address, phone_number, course_name, year_of_study, profile_pic, created_at FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            http_response_code(404);
            echo json_encode(["message" => "Student not found."]);
            exit;
        }

        // Fetch registration history
        $reg_stmt = $conn->prepare("SELECT r.reg_id, e.event_name, e.event_date, r.status 
                                   FROM registrations r 
                                   JOIN events e ON r.event_id = e.event_id 
                                   WHERE r.user_id = ? 
                                   ORDER BY e.event_date DESC");
        $reg_stmt->execute([$student_id]);
        $student['registrations'] = $reg_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch certificates
        $cert_stmt = $conn->prepare("SELECT c.id, e.event_name, c.certificate_type, c.generated_at 
                                    FROM certificates c 
                                    JOIN events e ON c.event_id = e.event_id 
                                    WHERE c.student_id = ? 
                                    ORDER BY c.generated_at DESC");
        $cert_stmt->execute([$student_id]);
        $student['certificates'] = $cert_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate stats
        $student['stats'] = [
            'total_participations' => count($student['registrations']),
            'total_certificates' => count($student['certificates']),
            'approved_registrations' => count(array_filter($student['registrations'], function($r) { return $r['status'] === 'approved'; }))
        ];

        echo json_encode($student);
    } else {
        // Fetch all students
        $sql = "SELECT id, name, email, college_name, phone_number, profile_pic, created_at,
                (SELECT COUNT(*) FROM registrations WHERE user_id = users.id) as reg_count,
                (SELECT COUNT(*) FROM certificates WHERE student_id = users.id) as cert_count
                FROM users 
                WHERE role = 'student' 
                ORDER BY name ASC";
        $stmt = $conn->query($sql);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Server error: " . $e->getMessage()]);
}
?>
