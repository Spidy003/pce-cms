<?php
include 'config/db_connect.php';
$assignments = $conn->query("SELECT * FROM assignments")->fetch_all(MYSQLI_ASSOC);
$classes = $conn->query("SELECT * FROM classes")->fetch_all(MYSQLI_ASSOC);
$mappings = $conn->query("SELECT * FROM subject_assignments")->fetch_all(MYSQLI_ASSOC);
$timetable = $conn->query("SELECT * FROM timetable")->fetch_all(MYSQLI_ASSOC);
$enrollments = $conn->query("SELECT * FROM enrollments")->fetch_all(MYSQLI_ASSOC);
echo json_encode(['assignments'=>$assignments, 'classes'=>$classes, 'mappings'=>$mappings, 'timetable'=>$timetable, 'enrollments'=>$enrollments]);
?>
