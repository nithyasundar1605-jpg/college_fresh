<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once '../config/db_final.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "User ID is required."]);
    exit;
}

try {
    $stats = [];

    // Total Registrations
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['total_registrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Total Approved (Participated)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$user_id]);
    $stats['total_participations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Prizes Breakdown
    $stmt = $conn->prepare("SELECT certificate_type, COUNT(*) as count FROM certificates WHERE student_id = ? GROUP BY certificate_type");
    $stmt->execute([$user_id]);
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats['first_prizes'] = 0;
    $stats['second_prizes'] = 0;
    $stats['third_prizes'] = 0;
    $stats['participation_certificates'] = 0;

    foreach ($prizes as $prize) {
        if ($prize['certificate_type'] === 'First Prize') $stats['first_prizes'] = $prize['count'];
        elseif ($prize['certificate_type'] === 'Second Prize') $stats['second_prizes'] = $prize['count'];
        elseif ($prize['certificate_type'] === 'Third Prize') $stats['third_prizes'] = $prize['count'];
        elseif ($prize['certificate_type'] === 'Participation') $stats['participation_certificates'] = $prize['count'];
    }

    // Recent Activity (Laste 5 registrations)
    $stmt = $conn->prepare("SELECT r.status, r.event_id, e.event_name, e.event_date 
                           FROM registrations r 
                           JOIN events e ON r.event_id = e.event_id 
                           WHERE r.user_id = ? 
                           ORDER BY r.reg_id DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $stats['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error fetching profile stats: " . $e->getMessage()]);
}
?>
