<?php
require_once '../config/db_connect.php';
header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];
    $stmt = $conn->prepare("SELECT full_name, roll_no, admission_no, role FROM users WHERE full_name LIKE ? OR roll_no LIKE ?");
    $searchTerm = "%" . $searchQuery . "%";
    
    if ($stmt) {
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $users]);
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database query failed.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No search query provided.']);
}
?>
