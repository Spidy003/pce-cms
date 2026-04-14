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
        header("Location: ../faculty_dashboard.php?status=post_success");
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

    header("Location: ../faculty_dashboard.php?status=submission_graded");
    exit();
}
