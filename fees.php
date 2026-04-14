<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
include 'config/db_connect.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM fees WHERE student_id = '$user_id' ORDER BY due_date ASC";
$result = mysqli_query($conn, $query);

$total_pending = 0;
$fee_data = [];
while($row = mysqli_fetch_assoc($result)) {
    if($row['status'] == 'Pending') $total_pending += $row['amount'];
    $fee_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Financial Ledger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-neo-white">

    <div class="dashboard-container" style="max-width: 900px;">
        <header class="fee-header">
            <h1 class="reveal" style="font-size: 4rem; font-weight: 900; margin: 0; text-transform: uppercase; line-height: 0.8; letter-spacing: -3px;">Fee_Vault.</h1>
            <a href="student_dashboard.php" class="neo-btn" style="background: var(--neo-black);">CLOSE_X</a>
        </header>

        <section class="stats-grid grid-2">
            <div class="card reveal" style="background: var(--neo-red); color: white;">
                <span class="card-title" style="color: white; opacity: 0.8;">Total_Due</span>
                <p class="card-value">₹<?php echo number_format($total_pending, 2); ?></p>
            </div>
            <div class="card reveal" style="background: var(--neo-green);">
                <span class="card-title">Account_Status</span>
                <p class="card-value" style="font-size: 1.8rem;"><?php echo ($total_pending > 0) ? 'Action Required' : 'Cleared'; ?></p>
            </div>
        </section>

        <section class="fee-card-container">
            <?php foreach($fee_data as $fee): ?>
            <div class="card reveal" style="display: flex; justify-content: space-between; align-items: center; background: white; margin-bottom: 1.5rem;">
                <div>
                    <h4 style="font-size: 1.5rem; font-weight: 900; text-transform: uppercase; font-style: italic; margin-bottom: 4px;"><?php echo htmlspecialchars($fee['fee_type']); ?></h4>
                    <p style="font-family: 'JetBrains Mono'; font-size: 0.875rem; color: #666;">Due Date: <?php echo date("d M, Y", strtotime($fee['due_date'])); ?></p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 900; margin-bottom: 8px;">₹<?php echo number_format($fee['amount'], 2); ?></div>
                    <span class="status-badge-small <?php echo ($fee['status'] == 'Paid') ? 'bg-paid' : 'bg-pending'; ?>">
                        <?php echo $fee['status']; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </section>

        <footer class="ledger-footer reveal">
            <p>> Please visit the PCE Admin Office for offline payments or use the UPI portal for digital clearance.</p>
        </footer>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
