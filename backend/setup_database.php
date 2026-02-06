<?php
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect without DB name
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server.<br>";

    // Create Database
    $conn->exec("CREATE DATABASE IF NOT EXISTS college_event_management");
    echo "Database 'college_event_management' created (or already exists).<br>";

    // Use Database
    $conn->exec("USE college_event_management");

    // Create Tables (users, events, registrations, certificates)
    $q1 = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'student') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($q1);
    echo "Table 'users' checked/created.<br>";

    $q2 = "CREATE TABLE IF NOT EXISTS events (
        event_id INT AUTO_INCREMENT PRIMARY KEY,
        event_name VARCHAR(150) NOT NULL,
        description TEXT,
        event_date DATE NOT NULL,
        venue VARCHAR(150) NOT NULL,
        status ENUM('open', 'closed') DEFAULT 'open',
        created_by INT,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($q2);
    $conn->exec($q2);
    echo "Table 'events' checked/created.<br>";

    // Add signature columns if they don't exist
    try {
        $conn->exec("ALTER TABLE events ADD COLUMN coordinator_signature VARCHAR(255) DEFAULT NULL");
        echo "Column 'coordinator_signature' added.<br>";
    } catch (PDOException $e) {
        // Ignore if column exists
    }
    try {
        $conn->exec("ALTER TABLE events ADD COLUMN management_signature VARCHAR(255) DEFAULT NULL");
        echo "Column 'management_signature' added.<br>";
    } catch (PDOException $e) {
        // Ignore if column exists
    }

    $q3 = "CREATE TABLE IF NOT EXISTS registrations (
        reg_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        event_id INT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        UNIQUE(user_id, event_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
    )";
    $conn->exec($q3);
    echo "Table 'registrations' checked/created.<br>";

    $q4 = "CREATE TABLE IF NOT EXISTS certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        event_id INT NOT NULL,
        certificate_path VARCHAR(255) NOT NULL,
        certificate_uid VARCHAR(100) NOT NULL UNIQUE,
        generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(student_id, event_id),
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
    )";
    $conn->exec($q4);
    echo "Table 'certificates' updated.<br>";

    // Admin User
    // Password: admin123 -> $2y$10$Xw6ox/6.r5G7.D5G7.D5yeW8.w8.w8.w8.w8.w8.w8.w8.w8.w8.w8 (This hash was placeholder, let's generate real one)
    $pass = password_hash('admin123', PASSWORD_BCRYPT);
    $q5 = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('System Admin', 'admin@college.com', '$pass', 'admin')";
    $conn->exec($q5);
    echo "Admin user checked/created.<br>";

    echo "<h1>Database Setup Complete!</h1>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
