<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php';

// Fetch all submissions with student names
$query = "SELECT assignments.*, users.username 
          FROM assignments 
          JOIN users ON assignments.student_id = users.id 
          ORDER BY upload_time DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Review Submissions</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap" rel="stylesheet">
</head>
<body class="bg-neo-white" style="padding: 2rem;">

    <div class="timetable-container"> 
        <header class="review-header reveal">
            <div>
                <h2 style="font-size: 3.5rem; font-weight: 900; text-transform: uppercase; margin: 0; letter-spacing: -2px;">Review_Log</h2>
                <p style="font-family: 'JetBrains Mono'; color: #666; margin-top: 5px;">Grading terminal for submitted assignments.</p>
            </div>
            <a href="faculty_dashboard.php" class="neo-btn" style="text-decoration: none; background: var(--neo-black); color: white;"> <- BACK</a>
        </header>

        <div class="review-table-wrapper reveal">
            <table class="review-table">
                <thead>
                    <tr>
                        <th>Student_ID</th>
                        <th>File_Name</th>
                        <th>Timestamp</th>
                        <th style="border-right: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td style="font-weight: 900;"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td style="font-style: italic;"><?php echo htmlspecialchars($row['file_name']); ?></td>
                        <td style="font-size: 0.75rem;"><?php echo $row['upload_time']; ?></td>
                        <td style="border-right: none;">
                            <div class="action-flex">
                                <a href="<?php echo $row['file_path']; ?>" download class="btn-sm" style="background: var(--neo-blue); color: white;">
                                    DOWNLOAD
                                </a>
                                
                                <form action="grade_logic.php" method="POST" style="display: flex; gap: 8px;">
                                    <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                                    <input type="number" name="marks" placeholder="Grade" class="input-grade">
                                    <button type="submit" class="btn-sm" style="background: var(--neo-green);">
                                        SAVE
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="4" style="padding: 3rem; text-align: center; font-weight: 900; color: #aaa; border-right: none;">
                            NO_DATA_FOUND_IN_MATRIX
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
