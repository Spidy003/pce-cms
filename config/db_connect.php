<?php
// db_connect.php — Auto-detects Local vs Production environment

$isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1'])
           || str_contains(strtolower($_SERVER['HTTP_HOST'] ?? ''), 'localhost');

if ($isLocal) {
    // ============================================================
    // LOCAL XAMPP SETTINGS — used when running on your computer
    // ============================================================
    $host   = "localhost";
    $user   = "root";       // XAMPP default username
    $pass   = "";           // XAMPP default password (empty)
    $dbname = "pce_college_db"; // Your local database name
    $port   = 3306;
} else {
    // ============================================================
    // PRODUCTION INFINITYFREE SETTINGS — used on live server
    // ============================================================
    $host   = "sql207.infinityfree.com";
    $user   = "if0_41660314";
    $pass   = "h9ELE7cxjpsJ8g";
    $dbname = "if0_41660314_college_db";
    $port   = 3306;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("CRITICAL_CONNECTION_ERROR: " . $e->getMessage() . " on " . ($isLocal ? "localhost" : "production"));
}
?>