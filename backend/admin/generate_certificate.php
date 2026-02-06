<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

include_once '../config/db_final.php';
require_once('../libs/certificate_pdf_lib.php');

$data = json_decode(file_get_contents("php://input"));

if (isset($data->student_id) && isset($data->event_id)) {
    $student_id = intval($data->student_id);
    $event_id = intval($data->event_id);
    $certificate_type = isset($data->certificate_type) ? $data->certificate_type : 'Participation';

    if ($student_id > 0 && $event_id > 0) {
        try {
            // 1. Fetch Student and Event Details (Added u.email)
            $queryDetails = "SELECT u.name as student_name, u.email as student_email, e.college_name as event_college, e.event_name, e.description, e.event_date, e.coordinator_signature, e.management_signature
                            FROM users u, events e 
                            WHERE u.id = ? AND e.event_id = ?";
            $stmtDetails = $conn->prepare($queryDetails);
            $stmtDetails->execute([$student_id, $event_id]);
            $details = $stmtDetails->fetch(PDO::FETCH_ASSOC);

            if (!$details) {
                http_response_code(404);
                echo json_encode(["message" => "Student or Event not found."]);
                exit;
            }

            // 2. Check if already generated (Check for specific type)
            $check = $conn->prepare("SELECT id FROM certificates WHERE student_id = ? AND event_id = ? AND certificate_type = ?");
            $check->execute([$student_id, $event_id, $certificate_type]);
            
            if ($check->rowCount() > 0) {
                http_response_code(400);
                echo json_encode(["message" => "Certificate already generated for this student, event and type."]);
                exit;
            }

            // 3. Generate Certificate UID
            $certificate_uid = "CERT-" . strtoupper(substr(md5($event_id . $student_id . $certificate_type . time()), 0, 10));
            
            // 4. Define Path
            $safe_type = str_replace(' ', '_', strtolower($certificate_type));
            $filename = "certificate_" . $student_id . "_" . $event_id . "_" . $safe_type . ".pdf";
            $certificate_path = "certificates/" . $filename;
            $project_root = dirname(dirname(__DIR__));
            $dest_folder = $project_root . DIRECTORY_SEPARATOR . "certificates";
            
            if (!is_dir($dest_folder)) {
                mkdir($dest_folder, 0777, true);
            }
            $full_path = $dest_folder . DIRECTORY_SEPARATOR . $filename;

            // 5. Generate PDF - STRICT SINGLE PAGE, NO MARGINS
            $pdf = new PDF_Certificate('L', 'pt', [1200, 850]);
            $pdf->SetAutoPageBreak(false); // CRITICAL: Disable page breaks
            $pdf->SetMargins(0, 0, 0);    // CRITICAL: Zero margins
            $pdf->AddPage();
            
            // Draw Designs
            $pdf->HeaderDesign();
            $pdf->FooterDesign();

            // --- CENTERED CONTENT (Landscape 1200x850) ---
            
            // Logo / Organization - NOW DYNAMIC
            $college_header = !empty($details['event_college']) ? strtoupper($details['event_college']) : 'COLLEGE EVENT ORGANIZATION';
            
            $pdf->SetY(130);
            $pdf->SetFont('Arial', 'B', 32);
            $pdf->SetTextColor(0, 51, 102); // Dark Blue
            $pdf->Cell(0, 40, $college_header, 0, 1, 'C');
            
            // "CERTIFICATE"
            $pdf->SetY(220);
            $pdf->SetFont('Arial', 'B', 90);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 100, 'CERTIFICATE', 0, 1, 'C');
            
            // Dynamic Title based on Type
            $title_sub = "OF " . strtoupper($certificate_type);
            if ($certificate_type === 'First Prize' || $certificate_type === 'Second Prize' || $certificate_type === 'Third Prize') {
                $title_sub = "FOR " . strtoupper($certificate_type);
            }
            
            $pdf->SetY(310);
            $pdf->SetFont('Arial', 'B', 40);
            $pdf->SetTextColor(184, 134, 11); // Dark Golden Rod
            $pdf->Cell(0, 50, $title_sub, 0, 1, 'C');

            // "This is to certify that"
            $pdf->SetY(410);
            $pdf->SetFont('Arial', '', 28);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 40, 'This is to certify that', 0, 1, 'C');
            
            // Student Name
            $pdf->SetY(470);
            $pdf->SetFont('Arial', 'B', 64);
            $pdf->SetTextColor(0, 51, 102);
            $pdf->Cell(0, 80, strtoupper($details['student_name']), 0, 1, 'C');
            
            // Event Details
            $pdf->SetY(580);
            $pdf->SetFont('Arial', '', 24);
            $pdf->SetTextColor(0, 0, 0);
            
            if ($certificate_type === 'Participation') {
                $text = "has successfully participated in the event\n\"" . strtoupper($details['event_name']) . "\"\nheld on " . $details['event_date'] . ".";
            } else {
                $text = "has secured " . strtoupper($certificate_type) . " in the event\n\"" . strtoupper($details['event_name']) . "\"\nheld on " . $details['event_date'] . ".";
            }
            $pdf->MultiCell(0, 40, $text, 0, 'C');

            // Signatures
            $backend_root = dirname(__DIR__);
            $coordY = 700;
            $sigY = 660;
            
            // Coordinator Signature (Left)
            $pdf->SetXY(200, $coordY);
            $pdf->SetFont('Arial', 'B', 18);
            if (!empty($details['coordinator_signature'])) {
                $sigPath = $backend_root . DIRECTORY_SEPARATOR . $details['coordinator_signature'];
                if (file_exists($sigPath)) {
                    $pdf->Image($sigPath, 250, $sigY, 150, 0); // Approx width 150pt
                }
            }
            $pdf->Cell(250, 30, 'EVENT COORDINATOR', 0, 0, 'C');

            // College Management Signature (Right)
            $pdf->SetXY(750, $coordY);
            if (!empty($details['management_signature'])) {
                $sigPath = $backend_root . DIRECTORY_SEPARATOR . $details['management_signature'];
                if (file_exists($sigPath)) {
                    $pdf->Image($sigPath, 800, $sigY, 150, 0);
                }
            }
            $pdf->Cell(250, 30, 'COLLEGE MANAGEMENT', 0, 0, 'C');

            // Certificate ID (Bottom Center)
            $pdf->SetY(780);
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 20, 'Certificate ID: ' . $certificate_uid, 0, 1, 'C');

            // Save PDF
            $pdf->Output('F', $full_path);

            // 6. Save to database
            $stmt = $conn->prepare("INSERT INTO certificates (student_id, event_id, certificate_type, certificate_path, certificate_uid) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$student_id, $event_id, $certificate_type, $certificate_path, $certificate_uid])) {
                // Add notification for the student
                try {
                    $notif_msg = "A new certificate (\"" . $certificate_type . "\") has been generated for event \"" . $details['event_name'] . "\".";
                    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'certificate')");
                    $notif_stmt->execute([$student_id, $notif_msg]);
                    
                    // --- NEW: Send Email Notification with Link ---
                    include_once '../utils/mail_helper.php';
                    if (!empty($details['student_email'])) {
                        // Construct download link (adjust base URL as needed)
                        $download_link = "http://localhost:8080/college_fresh/backend/certificates/" . $filename;
                        
                        $subject = "Certificate Generated: " . $details['event_name'];
                        $body = "<h3>Congratulations " . htmlspecialchars($details['student_name']) . "!</h3>";
                        $body .= "<p>Your certificate for the event <strong>" . htmlspecialchars($details['event_name']) . "</strong> has been generated.</p>";
                        $body .= "<p>You can download it using the link below:</p>";
                        $body .= "<p><a href='" . $download_link . "'>Download Certificate</a></p>";
                        
                        sendMail($details['student_email'], $details['student_name'], $subject, $body);
                    }

                } catch (Exception $e) {
                    // Silently fail notification
                    error_log("Notification/Email error: " . $e->getMessage());
                }
                
                echo json_encode(["message" => "Certificate generated successfully.", "uid" => $certificate_uid]);
            } else {
                if (file_exists($full_path)) unlink($full_path);
                http_response_code(500);
                echo json_encode(["message" => "Failed to save certificate record."]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Server error: " . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Invalid numeric data provided."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete request parameters."]);
}
?>
