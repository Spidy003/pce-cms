<?php
include '../config/db_connect.php';
session_start();

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'faculty') {
    die("UNAUTHORIZED_ACCESS");
}

$action = $_GET['action'] ?? '';



// --- POST NEW ASSIGNMENT ---
if ($action == 'post_assignment') {
    $faculty_id = $_SESSION['user_id'];
    $assign_mapping_id = intval($_POST['assign_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);

    // Get context (Div/Batch) from the mapping
    $mapping = $conn->query("SELECT * FROM subject_assignments WHERE id = $assign_mapping_id")->fetch_assoc();
    $sub_name = $mapping['subject_name'];
    $div = $mapping['division'];
    $batch = $mapping['batch_id'];

    $stmt = $conn->prepare("INSERT INTO assignments (faculty_id, subject_name, division, batch_id, title, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $faculty_id, $sub_name, $div, $batch, $title, $desc);

    if ($stmt->execute()) {
        header("Location: ../faculty/faculty_dashboard.php?status=post_success");
    }
    exit();
}

// --- VIEW SUBMISSIONS (AJAX CALL) ---
if ($action == 'view_submissions') {
    $assign_id = intval($_GET['as_id']);

    $query = "SELECT s.*, u.full_name, u.admission_no 
              FROM assignment_submissions s 
              JOIN users u ON s.student_id = u.id 
              WHERE s.assignment_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $assign_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='neo-table'>
                <tr><th>STUDENT</th><th>ADM_NO</th><th>FILE</th><th>ACTION</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['full_name']}</td>
                    <td>{$row['admission_no']}</td>
                    <td><a href='uploads/submissions/{$row['submission_file']}' target='_blank' style='color:var(--neo-blue); font-weight:bold;'>OPEN_DOC</a></td>
                    <td>
                        <a href='process_faculty.php?action=grade_sub&id={$row['id']}&status=Correct' class='neo-btn' style='background:var(--neo-green); font-size:0.6rem; padding:5px;'>CORRECT</a>
                        <a href='process_faculty.php?action=grade_sub&id={$row['id']}&status=Wrong' class='neo-btn' style='background:var(--neo-pink); font-size:0.6rem; padding:5px;'>WRONG</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='padding:20px;'>No submissions found for this assignment.</p>";
    }
    exit();
}

// --- GRADE SUBMISSION ---
if ($action == 'grade_sub') {
    $sub_id = intval($_GET['id']);
    $status = $_GET['status']; // 'Correct' or 'Wrong'

    $stmt = $conn->prepare("UPDATE assignment_submissions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $sub_id);
    $stmt->execute();

    header("Location: ../faculty/faculty_dashboard.php?status=submission_graded");
    exit();
}
// --- FETCH STUDENTS BY ASSIGNED BATCH ---
if ($action == 'fetch_students') {
    $type = $_GET['type'];
    $assign_id = intval($_GET['as_id']);

    $mapping = $conn->query("SELECT * FROM subject_assignments WHERE id = $assign_id")->fetch_assoc();
    if (!$mapping) die("MAPPING_NOT_FOUND");
    
    $div = $mapping['division'];
    $batch = $mapping['batch_id'];
    $sub_name = $mapping['subject_name'];

    // Complex query: get students who match the division exactly and batch (or ALL)
    $q = "SELECT u.id, u.full_name, u.admission_no 
          FROM users u 
          LEFT JOIN enrollments e ON u.id = e.student_id 
          LEFT JOIN classes c ON e.class_id = c.id 
          WHERE (c.class_name = '$div' OR u.division = '$div') 
          AND (e.batch_name = '$batch' OR u.lab_batch = '$batch' OR '$batch' = 'ALL' OR e.batch_name = 'ALL')
          AND u.role = 'student' AND u.status = 1";
    
    $students = $conn->query($q);

    if ($students->num_rows > 0) {
        if ($type == 'grader') {
            echo "<form action='core/process_faculty.php?action=push_marks' method='POST'>
                  <input type='hidden' name='subject_name' value='{$sub_name}'>
                  <table class='neo-table'><tr><th>STUDENT</th><th>IA 1 ( /40)</th><th>IA 2 ( /40)</th></tr>";
            while($s = $students->fetch_assoc()) {
                echo "<tr>
                        <td><strong>{$s['full_name']}</strong><br>{$s['admission_no']}</td>
                        <td><input type='number' name='ia1[{$s['id']}]' class='neo-input' max='40' min='0' placeholder='0' required></td>
                        <td><input type='number' name='ia2[{$s['id']}]' class='neo-input' max='40' min='0' placeholder='0' required></td>
                      </tr>";
            }
            echo "</table>
                  <button type='submit' class='neo-btn' style='background:black; color:white; width:100%; margin-top:20px;'>RECORD_SCORES</button>
                  </form>";
        } else if ($type == 'attendance') {
            echo "<form action='core/process_faculty.php?action=push_attendance' method='POST'>
            <input type='hidden' name='subject_name' value='{$sub_name}'>
            <label>> OVERALL TOTAL LECTURES CONDUCTED</label>
            <input type='number' name='total_lectures' class='neo-input' min='1' value='1' required style='margin-bottom:20px;'>
            <table class='neo-table'><tr><th>STUDENT</th><th>PRESENT LECTURES (Out of Total)</th></tr>";
            while($s = $students->fetch_assoc()) {
                echo "<tr>
                        <td><strong>{$s['full_name']}</strong><br>{$s['admission_no']}</td>
                        <td><input type='number' name='present[{$s['id']}]' class='neo-input' min='0' placeholder='e.g. 5' required></td>
                      </tr>";
            }
            echo "</table>
                  <button type='submit' class='neo-btn' style='background:black; color:var(--neo-green); width:100%; margin-top:20px;'>RECORD_ATTENDANCE_PULSE</button>
                  </form>";
        }
    } else {
        echo "<p style='padding:20px; font-family:JetBrains Mono;'>> SYSTEM: No students found matching this batch mapping.</p>";
    }
    exit();
}

// --- PUSH MARKS ---
if ($action == 'push_marks') {
    // Auto-create results table if missing
    $conn->query("CREATE TABLE IF NOT EXISTS results (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, subject_name VARCHAR(100), ia1 INT, ia2 INT, marks_obtained FLOAT, UNIQUE(student_id, subject_name))");

    $sub_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $ia1_arr = $_POST['ia1'];
    $ia2_arr = $_POST['ia2'];

    foreach($ia1_arr as $student_id => $m1) {
        $m1 = intval($m1);
        $m2 = intval($ia2_arr[$student_id]);
        $avg = round(($m1 + $m2) / 2, 1);
        $s_id = intval($student_id);

        $stmt = $conn->prepare("REPLACE INTO results (student_id, subject_name, ia1, ia2, marks_obtained) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiii", $s_id, $sub_name, $m1, $m2, $avg);
        $stmt->execute();
    }
    header("Location: ../faculty/faculty_dashboard.php?status=marks_pushed");
    exit();
}

// --- PUSH ATTENDANCE ---
if ($action == 'push_attendance') {
    // Auto-create attendance table if missing
    $conn->query("CREATE TABLE IF NOT EXISTS attendance (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT, subject_name VARCHAR(100), total_lectures INT, present_lectures INT, UNIQUE(student_id, subject_name))");

    $sub_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $total = intval($_POST['total_lectures']);
    $present_arr = $_POST['present'];

    foreach($present_arr as $student_id => $p) {
        $pr = intval($p);
        $s_id = intval($student_id);

        $stmt = $conn->prepare("REPLACE INTO attendance (student_id, subject_name, total_lectures, present_lectures) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $s_id, $sub_name, $total, $pr);
        $stmt->execute();
    }
    header("Location: ../faculty/faculty_dashboard.php?status=attendance_pushed");
    exit();
}
