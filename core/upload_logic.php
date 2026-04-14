<?php
include '../config/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['assignment_file'])) {
    $student_id = $_SESSION['user_id'];
    $file_name = $_FILES['assignment_file']['name'];
    $file_tmp = $_FILES['assignment_file']['tmp_name'];
    
    // Create unique filename to prevent overwriting
    $unique_name = time() . "_" . $file_name;
    $upload_path = "uploads/" . $unique_name;

    if (move_uploaded_file($file_tmp, $upload_path)) {
        $sql = "INSERT INTO assignments (student_id, file_name, file_path) VALUES ('$student_id', '$unique_name', '$upload_path')";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Upload Successful!'); window.location='../student_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Upload Failed! Check folder permissions.'); window.location='../upload_assignment.php';</script>";
    }
}
?>
