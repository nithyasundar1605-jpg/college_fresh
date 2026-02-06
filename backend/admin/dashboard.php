<?php
include_once '../config/db_final.php';
// Auth check pending (e.g. check for admin session/token)

// Get counts
try {
    $stats = [];
    
    // Total Events
    $stmt = $conn->query("SELECT COUNT(*) as count FROM events");
    $stats['total_events'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total Students
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='student'");
    $stats['total_students'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total Registrations
    $stmt = $conn->query("SELECT COUNT(*) as count FROM registrations");
    $stats['total_registrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Certificates Issued
    $stmt = $conn->query("SELECT COUNT(*) as count FROM certificates");
    $stats['certificates_issued'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Recent events list (for the dashboard table)
    $stmt = $conn->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 5");
    $stats['recent_events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
