<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include 'config/db_connect.php';

// Fetch current holiday to show in the form
$res = mysqli_query($conn, "SELECT * FROM holidays LIMIT 1");
$current = mysqli_fetch_assoc($res);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Holiday Control</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap" rel="stylesheet">
</head>
<body class="flex-center">

    <div class="editor-card reveal">
        <div class="badge-pink">SYSTEM_CALENDAR</div>
        
        <h2 style="font-size: 2.5rem; font-weight: 900; text-transform: uppercase; font-style: italic; margin-bottom: 0.5rem; letter-spacing: -1px;">Holiday_Editor</h2>
        <p style="font-family: 'JetBrains Mono'; font-size: 0.875rem; color: #666; border-bottom: 2px solid black; padding-bottom: 1rem; margin-bottom: 2rem;">
            Update the countdown for all students.
        </p>

        <form action="holiday_logic.php" method="POST">
            <div class="form-group">
                <label class="form-label">> HOLIDAY_NAME</label>
                <input type="text" name="h_name" value="<?php echo htmlspecialchars($current['holiday_name']); ?>" required 
                    class="input-field" style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label class="form-label">> TARGET_DATE</label>
                <input type="date" name="h_date" value="<?php echo $current['holiday_date']; ?>" required 
                    class="input-field">
            </div>

            <button type="submit" class="btn-login" style="background: var(--neo-black);">
                UPDATE_NETWORK ->
            </button>
        </form>
        
        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="admin_dashboard.php" style="font-family: 'JetBrains Mono'; font-size: 0.75rem; font-weight: bold; text-decoration: underline; color: black;">
                <- BACK_TO_DASHBOARD
            </a>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
