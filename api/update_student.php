<?php
include '../config/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $profile_pic = null;

    // Handle File Upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "uploads/profile/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $file_name = "user_" . $id . "." . $file_ext;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $file_name;
        }
    }

    // Update Query
    if ($profile_pic) {
        $stmt = $conn->prepare("UPDATE users SET phone = ?, address = ?, profile_pic = ? WHERE id = ?");
        $stmt->bind_param("sssi", $phone, $address, $profile_pic, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssi", $phone, $address, $id);
    }

    $stmt->execute();
    header("Location: ../student_dashboard.php?status=success");
    exit();
}
?>
