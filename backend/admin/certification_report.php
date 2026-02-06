<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=certification_summary_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

include_once '../config/db_final.php';

try {
    // Open output stream
    $output = fopen("php://output", "w");

    // CSV Headers
    fputcsv($output, array('Certificate ID', 'Student Name', 'Email', 'Event Name', 'Generated At'));

    // Query data
    $query = "SELECT c.id, u.name, u.email, e.event_name, c.generated_at 
              FROM certificates c
              JOIN users u ON c.student_id = u.id
              JOIN events e ON c.event_id = e.event_id
              ORDER BY c.generated_at DESC, u.name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    fclose($output);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
