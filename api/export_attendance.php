<?php
include '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class = $_POST['class_name'];
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$class.'_Attendance.csv"');

    $output = fopen('php://output', 'w');
    // Header Row
    fputcsv($output, array('Roll No', 'Full Name', 'Admission No', 'Attendance %'));

    // Fetch students in that class (assuming you have a 'class' column or linking table)
    $query = "SELECT roll_no, full_name, admission_no FROM users WHERE role = 'student' AND status = 1";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        // Here we add a placeholder for attendance percentage (Logic to be updated by Faculty)
        $row['attendance'] = "85%"; 
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}
?>
