<?php
// db_connect.php
$host = getenv('DB_HOST') ?: "127.0.0.1";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ""; 
$dbname = getenv('DB_NAME') ?: "pce_college_db";
$port = getenv('DB_PORT') ?: 3307;

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