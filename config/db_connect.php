<?php
// db_connect.php
$host = "127.0.0.1"; // Try '127.0.0.1' first; if it fails, try 'localhost'
$user = "root";
$pass = ""; 
$dbname = "pce_college_db";
$port = 3307; // Default XAMPP port

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