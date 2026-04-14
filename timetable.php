<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: auth/login.php");
    exit();
}
include 'config/db_connect.php';

// Fetch Time Table
$query = "SELECT * FROM timetable";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Time Table Matrix</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-neo-white" style="padding: 2rem;">

    <div class="timetable-container">
        <header class="timetable-header reveal">
            <div>
                <h1 style="font-size: 4.5rem; font-weight: 900; text-transform: uppercase; letter-spacing: -3px; font-style: italic; margin: 0;">Weekly_Grid.</h1>
                <p style="font-family: 'JetBrains Mono'; font-size: 1.25rem; margin-top: 0.5rem; background: var(--neo-yellow); display: inline-block; padding: 0 8px; border: 2px solid black;">Batch: SE-IT-2026</p>
            </div>
            <a href="student/student_dashboard.php" class="neo-btn" style="text-decoration: none; background: var(--neo-black); color: white;">CLOSE_X</a>
        </header>

        <div class="timetable-wrapper reveal">
            <table class="neo-table">
                <thead>
                    <tr>
                        <th>Day / Period</th>
                        <th>09:00 - 11:00</th>
                        <th>11:15 - 01:15</th>
                        <th>02:00 - 04:00</th>
                        <th>04:00 - 05:00</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="day-column"><?php echo htmlspecialchars($row['day_name']); ?></td>
                        <td class="cell-yellow"><?php echo htmlspecialchars($row['subject_1']); ?></td>
                        <td class="cell-pink"><?php echo htmlspecialchars($row['subject_2']); ?></td>
                        <td class="cell-green"><?php echo htmlspecialchars($row['subject_3']); ?></td>
                        <td class="cell-blue"><?php echo htmlspecialchars($row['subject_4']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="print-btn-container reveal">
            <button onclick="window.print()" class="neo-btn" style="padding: 1.5rem 3rem; font-size: 1.5rem; background: var(--neo-black); color: white;">
                Download_PDF (Print)
            </button>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
