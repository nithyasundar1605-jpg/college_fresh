<?php
// Test script to verify notifications table and functionality
header("Content-Type: application/json");

include_once 'backend/config/db_final.php';

$results = [];

try {
    // Test 1: Check if notifications table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->rowCount() > 0;
    $results['table_exists'] = $tableExists;
    
    if ($tableExists) {
        // Test 2: Check table structure
        $stmt = $conn->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results['table_structure'] = $columns;
        
        // Test 3: Count existing notifications
        $stmt = $conn->query("SELECT COUNT(*) as count FROM notifications");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        $results['notification_count'] = $count['count'];
        
        // Test 4: Get sample notifications (if any)
        $stmt = $conn->query("SELECT n.*, u.name as user_name 
                              FROM notifications n 
                              JOIN users u ON n.user_id = u.id 
                              ORDER BY n.created_at DESC 
                              LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results['sample_notifications'] = $samples;
        
        // Test 5: Check for users (to create test notification)
        $stmt = $conn->query("SELECT id, name, role FROM users LIMIT 3");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results['sample_users'] = $users;
        
        $results['status'] = 'success';
        $results['message'] = 'Notifications table is properly configured!';
    } else {
        $results['status'] = 'error';
        $results['message'] = 'Notifications table does not exist. Please run add_notifications_table.sql';
    }
    
} catch (Exception $e) {
    $results['status'] = 'error';
    $results['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
