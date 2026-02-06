<?php
// backend/cron/send_reminders.php
// Run this script daily via cron job or Windows Task Scheduler

include_once '../config/db_final.php';
include_once '../utils/mail_helper.php';

try {
    echo "Checking for upcoming events...\n";

    // Find events happening tomorrow that haven't had reminders sent
    $sql = "SELECT event_id, event_name, event_date, venue FROM events 
            WHERE event_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) 
            AND reminders_sent = 0";
    
    $stmt = $conn->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($events)) {
        echo "No upcoming events found for reminders.\n";
        exit;
    }

    foreach ($events as $event) {
        echo "Processing event: " . $event['event_name'] . "\n";

        // Get registered users
        $regSql = "SELECT u.name, u.email 
                   FROM registrations r
                   JOIN users u ON r.user_id = u.id
                   WHERE r.event_id = ?";
        $regStmt = $conn->prepare($regSql);
        $regStmt->execute([$event['event_id']]);
        $users = $regStmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;
        foreach ($users as $user) {
            if (!empty($user['email'])) {
                $subject = "Reminder: Upcoming Event - " . $event['event_name'];
                $body = "<h3>Hello " . htmlspecialchars($user['name']) . ",</h3>";
                $body .= "<p>This is a reminder that the event <strong>" . htmlspecialchars($event['event_name']) . "</strong> is happening tomorrow!</p>";
                $body .= "<p><strong>Date:</strong> " . $event['event_date'] . "<br>";
                $body .= "<strong>Venue:</strong> " . htmlspecialchars($event['venue']) . "</p>";
                $body .= "<p>Don't miss it!</p>";

                if (sendMail($user['email'], $user['name'], $subject, $body)) {
                    $count++;
                }
            }
        }

        // Mark event as reminder sent
        $updateSql = "UPDATE events SET reminders_sent = 1 WHERE event_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$event['event_id']]);

        echo "Sent $count reminders for " . $event['event_name'] . ".\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
