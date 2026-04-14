<?php
include '../config/db_connect.php';
session_start();

/**
 * 1. THE ULTIMATE SECURITY GATE
 * This must be at the top to prevent any unauthorized code execution.
 */
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    $current_role = $_SESSION['user_role'] ?? 'NONE';
    die("UNAUTHORIZED_ACCESS: Your role is '$current_role'. Admin privileges required.");
}

$action = $_GET['action'] ?? '';

// --- 2. APPROVE USER ---
if ($action == 'approve') {
    $id = intval($_GET['id']);
    $conn->query("UPDATE users SET status = 1 WHERE id = $id");
    header("Location: ../admin/admin_dashboard.php");
    exit();
}

// --- 3. ROLE SWAP ---
if ($action == 'roleswap') {
    $email = trim($_POST['email']);
    $role = $_POST['new_role'];
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE email = ?");
    $stmt->bind_param("ss", $role, $email);
    $stmt->execute();
    header("Location: ../admin/admin_dashboard.php#roles");
    exit();
}

// --- 4. POST GLOBAL NOTICE ---
if ($action == 'post_notice') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // Self-healing table check
    $conn->query("CREATE TABLE IF NOT EXISTS notices (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), content TEXT, date_posted TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

    $stmt = $conn->prepare("INSERT INTO notices (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();
    
    header("Location: ../admin/admin_dashboard.php?status=notice_posted");
    exit();
}

// --- 5. CSV BATCH ONBOARDING ---
if ($action == 'csv_upload' && isset($_FILES["csv_file"])) {
    $filename = $_FILES["csv_file"]["tmp_name"];
    if ($_FILES["csv_file"]["size"] > 0) {
        $file = fopen($filename, "r");
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            $roll_no = "PCE-BATCH-" . substr($data[3], -3);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, admission_no, roll_no, role, status) VALUES (?, ?, ?, ?, ?, 'student', 1)");
            $stmt->bind_param("sssss", $data[0], $data[1], $data[2], $data[3], $roll_no);
            $stmt->execute();
        }
        fclose($file);
    }
    header("Location: ../admin/admin_dashboard.php?status=upload_complete");
    exit();
}

// --- 6. SUBJECT MAPPING (FACULTY ASSIGNMENT) ---
if ($action == 'assign_subject') {
    $fac_id = $_POST['faculty_id'];
    $sub = $_POST['sub_name'];
    $div = $_POST['div'];
    $batch = $_POST['batch'];

    $conn->query("CREATE TABLE IF NOT EXISTS subject_assignments (id INT AUTO_INCREMENT PRIMARY KEY, faculty_id INT, subject_name VARCHAR(100), division VARCHAR(10), batch_id VARCHAR(10))");

    $stmt = $conn->prepare("INSERT INTO subject_assignments (faculty_id, subject_name, division, batch_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $fac_id, $sub, $div, $batch);
    $success = $stmt->execute();
    
    if (isset($_POST['ajax'])) {
        echo json_encode(['status' => $success ? 'success' : 'failed']);
        exit();
    }
    
    header("Location: ../admin/admin_dashboard.php?status=" . ($success ? "assigned" : "error"));
    exit();
}

// --- 7. TOGGLE FEE LOCK ---
if ($action == 'toggle_fee_lock') {
    $id = intval($_GET['id']);
    $conn->query("UPDATE fees SET is_locked = 1 - is_locked WHERE id = $id");
    header("Location: ../admin/admin_dashboard.php");
    exit();
}

// --- 8. CREATE CLASS (CLASS MASTER) ---
if ($action == 'create_class') {
    $c_name = trim($_POST['c_name']);
    if (!empty($c_name)) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name) VALUES (?)");
        $stmt->bind_param("s", $c_name);
        $stmt->execute();
        header("Location: ../admin/admin_dashboard.php?status=class_created");
    } else {
        header("Location: ../admin/admin_dashboard.php?status=error_empty");
    }
    exit();
}

// --- QUICK ENROLL LOGIC ---
if ($action == 'quick_enroll') {
    $s_id = intval($_POST['s_id']);
    $c_id = intval($_POST['c_id']);
    $batch = mysqli_real_escape_string($conn, $_POST['batch']);

    // Check if a class was actually selected
    if ($c_id == 0) {
        echo "<script>alert('ERROR: Please select a Class from the dropdown first!'); window.location='../admin_dashboard.php';</script>";
        exit();
    }

    // Attempt to enroll the student
    // REPLACE INTO is used so if they are already enrolled, it just updates their class/batch
    $sql = "REPLACE INTO enrollments (student_id, class_id, batch_name) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $s_id, $c_id, $batch);

    if ($stmt->execute()) {
        // This only runs if the database confirms the save was successful
        echo "<script>
                alert('SUCCESS: Student enrolled in class successfully!'); 
                window.location='../admin_dashboard.php';
              </script>";
    } else {
        // This runs if there is a database error
        echo "<script>
                alert('DATABASE_ERROR: Could not update enrollment. Check your table structure.'); 
                window.location='../admin_dashboard.php';
              </script>";
    }
    exit();
}

// --- 9. ADD TIMETABLE ---
if ($action == 'add_timetable') {
    $class_id = intval($_POST['class_id']);
    $batch = $_POST['batch_id'];
    $subject = $_POST['subject'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    
    $end_time = date('H:i', strtotime($start_time) + 3600);

    $stmt = $conn->prepare("INSERT INTO timetable (class_id, batch_id, subject_name, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $class_id, $batch, $subject, $day, $start_time, $end_time);
    $stmt->execute();
    
    header("Location: ../admin/admin_dashboard.php?status=timetable_added");
    exit();
}

// --- 10. FALLBACK REDIRECT ---
header("Location: ../admin/admin_dashboard.php");
exit();
?>
