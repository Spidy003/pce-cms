<?php
include '../config/db_connect.php';
session_start();

// Security: Only students can upload
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'student') {
    die("UNAUTHORIZED_ACCESS");
}

$action = $_GET['action'] ?? '';
$student_id = $_SESSION['user_id'];

// --- UPLOAD ASSIGNMENT ---
if ($action == 'upload_assignment') {
    $assign_id = intval($_POST['assignment_id']);
    
    if (isset($_FILES['sub_file']) && $_FILES['sub_file']['error'] == 0) {
        $upload_dir = "uploads/submissions/";
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["sub_file"]["name"]);
        $target_file = $upload_dir . $file_name;

        // Move the file to the server folder
        if (move_uploaded_file($_FILES["sub_file"]["tmp_name"], $target_file)) {
            
            // Log submission in database
            // Using REPLACE INTO ensures that if a student re-uploads, it updates the record
            $stmt = $conn->prepare("REPLACE INTO assignment_submissions (assignment_id, student_id, submission_file, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("iis", $assign_id, $student_id, $file_name);
            
            if ($stmt->execute()) {
                header("Location: ../student/student_dashboard.php?status=upload_success");
            } else {
                echo "Database Error: " . $conn->error;
            }
        } else {
            echo "File Move Error. Check folder permissions.";
        }
    } else {
        header("Location: ../student/student_dashboard.php?status=upload_failed");
    }
    exit();
}

// Fallback
header("Location: ../student/student_dashboard.php");
exit();
