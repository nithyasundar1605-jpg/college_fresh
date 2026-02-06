<?php
// Simplified Database Import Script
// Creates only the tables without database creation

echo "🚀 Starting simplified database import...\n";

try {
    echo "1. Connecting to MySQL server...";
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo " ✓ Connected\n";
    
    echo "2. Creating/Using database...";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS college_events_db");
    $pdo->exec("USE college_events_db");
    echo " ✓ Database ready\n";
    
    echo "3. Creating tables...\n";
    
    // Drop tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS certificates");
    $pdo->exec("DROP TABLE IF EXISTS registrations");  
    $pdo->exec("DROP TABLE IF EXISTS events");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    echo "   - Creating users table...";
    $pdo->exec("
        CREATE TABLE `users` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `email` VARCHAR(150) NOT NULL,
          `password` VARCHAR(255) NOT NULL,
          `role` ENUM('admin','student') NOT NULL DEFAULT 'student',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo " ✓\n";
    
    echo "   - Creating events table...";
    $pdo->exec("
        CREATE TABLE `events` (
          `event_id` INT(11) NOT NULL AUTO_INCREMENT,
          `event_name` VARCHAR(200) NOT NULL,
          `description` TEXT NOT NULL,
          `event_date` DATE NOT NULL,
          `venue` VARCHAR(200) NOT NULL,
          `created_by` INT(11) NOT NULL,
          `status` ENUM('open','closed') NOT NULL DEFAULT 'open',
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`event_id`),
          KEY `created_by` (`created_by`),
          CONSTRAINT `events_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo " ✓\n";
    
    echo "   - Creating registrations table...";
    $pdo->exec("
        CREATE TABLE `registrations` (
          `reg_id` INT(11) NOT NULL AUTO_INCREMENT,
          `user_id` INT(11) NOT NULL,
          `event_id` INT(11) NOT NULL,
          `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
          `registered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`reg_id`),
          KEY `user_id` (`user_id`),
          KEY `event_id` (`event_id`),
          CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
          UNIQUE KEY `unique_registration` (`user_id`, `event_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo " ✓\n";
    
    echo "   - Creating certificates table...";
    $pdo->exec("
        CREATE TABLE `certificates` (
          `cert_id` INT(11) NOT NULL AUTO_INCREMENT,
          `user_id` INT(11) NOT NULL,
          `event_id` INT(11) NOT NULL,
          `issue_date` DATE NOT NULL,
          `certificate_path` VARCHAR(255) NOT NULL,
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`cert_id`),
          KEY `user_id` (`user_id`),
          KEY `event_id` (`event_id`),
          CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
          CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo " ✓\n";
    
    echo "4. Inserting default admin user...";
    $pdo->exec("
        INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
        ('Administrator', 'admin@college.edu', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
    ");
    echo " ✓\n";
    
    echo "5. Verifying installation...";
    $stmt = $pdo->prepare("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'college_events_db'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['table_count'] >= 4) {
        echo " ✓ All tables created successfully\n";
        
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu'");
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            echo " ✓ Admin user verified: {$admin['name']} ({$admin['email']})\n";
        }
        
        echo "\n🎉 Database setup completed successfully!\n";
        echo "\n📋 Ready to use:";
        echo "\n- Main Application: http://localhost/college_fresh/";
        echo "\n- Admin Login: admin@college.edu / Admin@123";
        echo "\n- Test Student Registration: Use the registration form\n";
        
    } else {
        echo " ⚠ Some tables may be missing\n";
    }
    
} catch(Exception $e) {
    echo "\n❌ Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>