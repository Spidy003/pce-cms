<?php
include '../config/db_connect.php';
$query = $_GET['q'] ?? '';

if(strlen($query) >= 3) {
    $stmt = $conn->prepare("SELECT full_name, roll_no, admission_no FROM users WHERE (full_name LIKE ? OR roll_no LIKE ?) AND role='student'");
    $term = "%$query%";
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<div class='admin-card' style='position:absolute; width:100%; z-index:500;'>";
    while($row = $result->fetch_assoc()) {
        echo "<div style='padding:10px; border-bottom:1px solid #ddd;'>
                <strong>{$row['full_name']}</strong> | {$row['roll_no']} 
              </div>";
    }
    echo "</div>";
}
?>
