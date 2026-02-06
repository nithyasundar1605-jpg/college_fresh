<?php
/**
 * Quick Database Migration - Run this file directly
 * Access via: http://localhost/college_fresh/quick_setup.php
 */

include_once 'backend/config/db_final.php';

header("Content-Type: text/plain");

echo "==============================================\n";
echo "  NOTIFICATION SYSTEM SETUP\n";
echo "==============================================\n\n";

try {
    // Check if table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'notifications'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "[INFO] Notifications table already exists.\n";
    } else {
        echo "[CREATING] Creating notifications table...\n";
        
        $sql = "CREATE TABLE notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'general',
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $conn->exec($sql);
        echo "[SUCCESS] Notifications table created!\n";
    }
    
    // Create a test notification
    $stmt = $conn->query("SELECT id, name FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $msg = "Notification system is now active! 🎉";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'system')");
        $stmt->execute([$admin['id'], $msg]);
        echo "[SUCCESS] Test notification created for admin: {$admin['name']}\n";
    }
    
    // Count notifications
    $stmt = $conn->query("SELECT COUNT(*) as cnt FROM notifications");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "[INFO] Total notifications: {$count['cnt']}\n";
    
    echo "\n==============================================\n";
    echo "  SETUP COMPLETE! ✓\n";
    echo "==============================================\n";
    echo "\nWhat to do next:\n";
    echo "1. Login to your application\n";
    echo "2. Check the notification bell icon\n";
    echo "3. Test by registering for an event\n";
    echo "4. Test by approving/rejecting registrations\n";
    
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
?>
