<?php
header("Content-Type: application/json");
$host = "127.0.0.1";
$username = "root";
$password = "";
$db_name = "college_event_management";

$result = [];

try {
    // 1. Connect NO DB
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result['step1_nodb'] = "Success";

    // 2. Check Directories (if possible) - skipped as we can't access filesystem easily from PHP web
    
    // 3. SHOW DATABASES again
    $stmt = $conn->query("SHOW DATABASES LIKE '$db_name'");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $result['step2_check_exists'] = $dbs;

    if (empty($dbs)) {
        $result['error'] = "Database not found in SHOW DATABASES";
    } else {
        // 4. Try USE
        try {
            $conn->exec("USE $db_name");
            $result['step3_use'] = "Success";
        } catch(Exception $e) {
            $result['step3_use'] = "Failed: " . $e->getMessage();
        }
    }

    // 5. Connect WITH DB
    try {
        $conn2 = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $result['step4_connect_with_dsn'] = "Success";
    } catch(Exception $e) {
        $result['step4_connect_with_dsn'] = "Failed: " . $e->getMessage();
    }

} catch(Exception $e) {
    $result['fatal_error'] = $e->getMessage();
}

echo json_encode($result);
?>
