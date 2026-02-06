<?php
header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=event_participation_report.csv");
header("Pragma: no-cache");
header("Expires: 0");

include_once '../config/db_final.php';

try {
    // Open output stream
    $output = fopen("php://output", "w");

    // CSV Headers
    fputcsv($output, array('Event Name', 'Student Name', 'Email', 'Registration Status'));

    // Query data
    $query = "SELECT e.event_name, u.name, u.email, r.status 
              FROM registrations r
              JOIN users u ON r.user_id = u.id
              JOIN events e ON r.event_id = e.event_id
              ORDER BY e.event_name ASC, u.name ASC";
    
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
