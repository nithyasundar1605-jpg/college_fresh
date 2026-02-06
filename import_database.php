<?php
// Direct Database Import Script
// Run this script to automatically import the database

echo "🚀 Starting automatic database import...\n";

try {
    echo "1. Connecting to MySQL server...";
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo " ✓ Connected\n";
    
    echo "2. Dropping existing database (if any)...";
    $pdo->exec("DROP DATABASE IF EXISTS college_events_db");
    echo " ✓ Done\n";
    
    echo "3. Creating fresh database...";
    $pdo->exec("CREATE DATABASE college_events_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo " ✓ Created\n";
    
    echo "4. Selecting database...";
    $pdo->exec("USE college_events_db");
    echo " ✓ Selected\n";
    
    echo "5. Reading SQL schema file...";
    $sql_file = __DIR__ . '/database_schema.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("Database schema file not found at: $sql_file");
    }
    $sql = file_get_contents($sql_file);
    echo " ✓ File loaded (" . strlen($sql) . " characters)\n";
    
    echo "6. Executing SQL statements...";
    $statements = explode(';', $sql);
    $executed = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && strlen($statement) > 10) {
            $pdo->exec($statement);
            $executed++;
        }
    }
    echo " ✓ Executed $executed statements\n";
    
    echo "7. Verifying tables...";
    $required_tables = ['users', 'events', 'registrations', 'certificates'];
    $found_tables = 0;
    
    foreach($required_tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if($stmt->rowCount() > 0) {
            $found_tables++;
        }
    }
    
    if ($found_tables === count($required_tables)) {
        echo " ✓ All $found_tables tables created successfully\n";
    } else {
        echo " ⚠ Only found $found_tables of " . count($required_tables) . " tables\n";
    }
    
    echo "8. Verifying admin user...";
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu'");
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        echo " ✓ Admin user found: {$admin['name']} ({$admin['email']})\n";
    } else {
        echo " ⚠ Admin user not found\n";
    }
    
    echo "\n🎉 Database import completed successfully!\n";
    echo "\n📋 Next steps:";
    echo "\n1. Open your browser";
    echo "\n2. Go to: http://localhost/college_fresh/";
    echo "\n3. Test registration with any student details";
    echo "\n4. Login as admin: admin@college.edu / Admin@123";
    
} catch(Exception $e) {
    echo "\n❌ Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>