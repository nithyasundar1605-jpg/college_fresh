<?php
/**
 * Database Migration Script - Add Notifications Table
 * Run this file in your browser: http://localhost/college_fresh/setup_notifications.php
 */

header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Notifications - College Event Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #1a202c;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .step {
            background: #f7fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .step-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .step-result {
            color: #4a5568;
            font-size: 13px;
        }
        .code {
            background: #2d3748;
            color: #68d391;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .button:hover {
            background: #5568d3;
        }
        .checkmark {
            color: #48bb78;
            font-weight: bold;
            margin-right: 8px;
        }
        .xmark {
            color: #f56565;
            font-weight: bold;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔔 Notification System Setup</h1>
        <p class="subtitle">Automatically configure the notifications table for your database</p>

<?php
include_once 'backend/config/db_final.php';

$steps = [];
$hasErrors = false;

try {
    // Step 1: Check database connection
    $steps[] = [
        'title' => 'Step 1: Database Connection',
        'result' => 'Connected successfully to database',
        'success' => true
    ];

    // Step 2: Check if notifications table already exists
    $stmt = $conn->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        $steps[] = [
            'title' => 'Step 2: Check Existing Table',
            'result' => 'Notifications table already exists. Verifying structure...',
            'success' => true
        ];
        
        // Verify structure
        $stmt = $conn->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'user_id', 'message', 'type', 'is_read', 'created_at'];
        $hasAllColumns = empty(array_diff($requiredColumns, $columns));
        
        if ($hasAllColumns) {
            $steps[] = [
                'title' => 'Step 3: Verify Table Structure',
                'result' => 'Table structure is correct. All required columns present.',
                'success' => true
            ];
        } else {
            $steps[] = [
                'title' => 'Step 3: Verify Table Structure',
                'result' => 'Table exists but structure is incorrect. Please drop the table and run this script again.',
                'success' => false
            ];
            $hasErrors = true;
        }
    } else {
        $steps[] = [
            'title' => 'Step 2: Check Existing Table',
            'result' => 'Notifications table does not exist. Creating now...',
            'success' => true
        ];
        
        // Create the notifications table
        $createTableSQL = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) DEFAULT 'general',
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        
        $conn->exec($createTableSQL);
        
        $steps[] = [
            'title' => 'Step 3: Create Notifications Table',
            'result' => 'Notifications table created successfully!',
            'success' => true
        ];
    }
    
    // Step 4: Test notification insertion
    $stmt = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminUser) {
        $testMessage = "Notification system is now active! This is a test notification.";
        $insertStmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'system')");
        $insertStmt->execute([$adminUser['id'], $testMessage]);
        
        $steps[] = [
            'title' => 'Step 4: Test Notification Creation',
            'result' => 'Test notification created successfully! Check your notification bell.',
            'success' => true
        ];
    } else {
        $steps[] = [
            'title' => 'Step 4: Test Notification Creation',
            'result' => 'No admin user found to test with. Skipping test notification.',
            'success' => true
        ];
    }
    
    // Step 5: Count notifications
    $stmt = $conn->query("SELECT COUNT(*) as count FROM notifications");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $steps[] = [
        'title' => 'Step 5: Verify Notifications',
        'result' => "Total notifications in database: {$count['count']}",
        'success' => true
    ];
    
} catch (PDOException $e) {
    $steps[] = [
        'title' => 'Error',
        'result' => 'Database error: ' . $e->getMessage(),
        'success' => false
    ];
    $hasErrors = true;
} catch (Exception $e) {
    $steps[] = [
        'title' => 'Error',
        'result' => 'Unexpected error: ' . $e->getMessage(),
        'success' => false
    ];
    $hasErrors = true;
}

// Display results
foreach ($steps as $step) {
    echo '<div class="step">';
    echo '<div class="step-title">';
    echo $step['success'] ? '<span class="checkmark">✓</span>' : '<span class="xmark">✗</span>';
    echo htmlspecialchars($step['title']);
    echo '</div>';
    echo '<div class="step-result">' . htmlspecialchars($step['result']) . '</div>';
    echo '</div>';
}

if (!$hasErrors) {
    echo '<div class="status success">';
    echo '<strong>✅ Success!</strong><br>';
    echo 'The notification system is now fully configured and ready to use.<br><br>';
    echo '<strong>What works now:</strong><br>';
    echo '• Students will be notified when their registration is approved/rejected<br>';
    echo '• Admins will be notified when students register for events<br>';
    echo '• Students will be notified when certificates are generated<br>';
    echo '• Notification bell will show unread count<br>';
    echo '• Click notifications to mark as read';
    echo '</div>';
    
    echo '<div class="status info">';
    echo '<strong>Next Steps:</strong><br>';
    echo '1. Login to your application<br>';
    echo '2. Check the notification bell icon in the navigation bar<br>';
    echo '3. Test by having a student register for an event<br>';
    echo '4. Test by approving/rejecting a registration as admin';
    echo '</div>';
} else {
    echo '<div class="status error">';
    echo '<strong>⚠️ Setup encountered errors</strong><br>';
    echo 'Please check the error messages above and try again.';
    echo '</div>';
}
?>

        <a href="index.html" class="button">← Back to Application</a>
        <a href="setup_notifications.php" class="button" style="background: #48bb78;">🔄 Run Again</a>
    </div>
</body>
</html>
