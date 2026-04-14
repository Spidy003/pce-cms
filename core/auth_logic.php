<?php
include '../config/db_connect.php';
session_start();

$action = $_GET['action'] ?? '';

// --- REGISTRATION LOGIC ---
if ($action == 'register') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 
    $dob = $_POST['dob'];
    $blood_group = $_POST['blood_group'];
    $ad_no = trim($_POST['admission_no']);
    $year = $_POST['admission_year'];
    $role = $_POST['role'];
    
    $roll_no = "PCE-" . $year . "-" . substr($ad_no, -3);

    $sql = "INSERT INTO users (full_name, email, password, dob, admission_year, admission_no, roll_no, blood_group, role, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $full_name, $email, $password, $dob, $year, $ad_no, $roll_no, $blood_group, $role);
    
    if ($stmt->execute()) {
        echo "<script>alert('Registration Successful! Wait for Admin approval.'); window.location='../login.php';</script>";
    } else {
        echo "<script>alert('Error: Email or Admission No already exists.'); window.location='../login.php';</script>";
    }
}

// --- LOGIN LOGIC ---
if ($action == 'login') {
    $email = trim($_POST['email']); 
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $pass === $user['password']) {
        
        if ($user['status'] == 0) {
            die("<div style='font-family:sans-serif; padding:50px; text-align:center;'>
                    <h1 style='color:red; border:5px solid black; display:inline-block; padding:20px;'>ACCESS_LOCKED</h1>
                    <p style='margin-top:20px;'>Your account is pending administrator approval. Please contact the IT department.</p>
                    <a href='login.php' style='color:blue; font-weight:bold;'>Return to Login</a>
                 </div>");
        }
        
        // --- ADDED FIX: Setting Session properly ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = strtolower($user['role']); // Force lowercase to match admin_dashboard check
        $_SESSION['user_name'] = $user['full_name'];

        // ROLE-BASED REDIRECT
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } elseif ($_SESSION['user_role'] === 'faculty') {
            header("Location: ../faculty/faculty_dashboard.php");
        } elseif ($_SESSION['user_role'] === 'student') {
            header("Location: ../student/student_dashboard.php");
        } else {
            header("Location: ../auth/login.php?error=unknown_role");
        }
        exit(); 
        
    } else {
        echo "<script>alert('Invalid Email or Password'); window.location='../login.php';</script>";
    }
}
?>
