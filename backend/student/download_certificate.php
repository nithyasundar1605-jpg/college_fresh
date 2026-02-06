<?php
header("Access-Control-Allow-Origin: *");
include_once '../config/db_final.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid Certificate ID");
}

try {
    $query = "SELECT c.*, u.name as student_name, e.event_name, e.event_date 
              FROM certificates c
              JOIN users u ON c.student_id = u.id
              JOIN events e ON c.event_id = e.event_id
              WHERE c.id = :id";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cert) {
        // Construct absolute path to the file
        $project_root = dirname(dirname(__DIR__));
        $file_path = $project_root . DIRECTORY_SEPARATOR . $cert['certificate_path'];
        
        if (file_exists($file_path)) {
            // Serve PDF file
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            die("Certificate file not found on server.");
        }
    } else {
        die("Certificate not found in database.");
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
