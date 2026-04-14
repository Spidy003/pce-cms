<?php
include '../config/db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marks'])) {
    $marks = $_POST['marks'];
    $assignment_id = $_POST['assignment_id'];

    // In a full system, we'd update a specific 'grades' table, 
    // but for now, we'll confirm the grade is received.
    echo "<script>alert('Marks Assigned: $marks'); window.location='../view_submissions.php';</script>";
}
?>
