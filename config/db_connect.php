<?php
// db_connect.php
$host = getenv('DB_HOST') ?: "sql207.infinityfree.com";
$user = getenv('DB_USER') ?: "if0_41660314";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "h9ELE7cxjpsJ8g"; 
$dbname = getenv('DB_NAME') ?: "if0_41660314_college_db";
$port = getenv('DB_PORT') ?: 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Adding the port explicitly helps bypass most "Access Denied" socket errors
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // If 127.0.0.1 fails, try the alternative socket connection
    try {
        $conn = new mysqli("localhost", $user, $pass, $dbname);
    } catch (mysqli_sql_exception $e2) {
        die("CRITICAL_CONNECTION_ERROR: " . $e2->getMessage());
    }
}
?>