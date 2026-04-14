<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}
include '../config/db_connect.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT subject_name, marks_obtained, ia1, ia2 FROM results WHERE student_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$subjects = [];
$marks = [];
$ia_data = [];

while($row = $res->fetch_assoc()) {
    $subjects[] = $row['subject_name'];
    $marks[] = $row['marks_obtained'];
    $ia_data[] = $row;
}

// Handle empty data cases
$top_subject = !empty($marks) ? $subjects[array_search(max($marks), $marks)] : "N/A";
$average = !empty($marks) ? round(array_sum($marks)/count($marks), 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Performance Matrix</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-neo-white" style="padding: 2rem;">

    <div class="container-narrow">
        <header class="results-header reveal">
            <h1 style="font-size: 3rem; font-weight: 900; text-transform: uppercase; font-style: italic; letter-spacing: -2px;">Result_Stats</h1>
            <a href="../student/student_dashboard.php" class="neo-btn" style="background: var(--neo-black); color: white; text-decoration: none;">BACK_</a>
        </header>

        <div class="chart-container reveal">
            <canvas id="resultsChart"></canvas>
        </div>

        <div class="reveal" style="margin: 40px 0;">
            <table style="width:100%; border-collapse: collapse; font-family:'JetBrains Mono'; background:white; border:4px solid black; box-shadow:8px 8px 0px black;">
                <tr style="background:black; color:white;">
                    <th style="padding:15px; text-align:left;">SUBJECT</th>
                    <th style="padding:15px;">IA 1 (40)</th>
                    <th style="padding:15px;">IA 2 (40)</th>
                    <th style="padding:15px;">CALCULATED AVG</th>
                </tr>
                <?php foreach($ia_data as $data): ?>
                <tr>
                    <td style="padding:15px; border-bottom:2px solid black; font-weight:bold;"><?php echo $data['subject_name']; ?></td>
                    <td style="padding:15px; border-bottom:2px solid black; text-align:center;"><?php echo $data['ia1']; ?></td>
                    <td style="padding:15px; border-bottom:2px solid black; text-align:center;"><?php echo $data['ia2']; ?></td>
                    <td style="padding:15px; border-bottom:2px solid black; text-align:center; color:var(--neo-green); font-weight:bold; background:#111;"><?php echo $data['marks_obtained']; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($ia_data)): ?>
                <tr><td colspan="4" style="padding:20px; text-align:center;">> NO INTERNAL MARKS UPLOADED YET.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <section class="summary-grid grid-2">
            <div class="card reveal" style="background: var(--neo-green);">
                <span class="card-title">Top_Subject</span>
                <p class="card-value"><?php echo htmlspecialchars($top_subject); ?></p>
            </div>
            <div class="card reveal" style="background: var(--neo-pink);">
                <span class="card-title">Average_Score</span>
                <p class="card-value"><?php echo $average; ?>%</p>
            </div>
        </section>
    </div>

    

    <script src="../assets/js/script.js"></script>

    <script>
        const ctx = document.getElementById('resultsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($subjects); ?>,
                datasets: [{
                    label: 'MARKS_OBTAINED',
                    data: <?php echo json_encode($marks); ?>,
                    backgroundColor: ['#FBFF48', '#FF70A6', '#3B82F6', '#33FF57', '#A855F7'],
                    borderColor: '#121212',
                    borderWidth: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 100,
                        grid: { color: '#121212', lineWidth: 1 },
                        ticks: { font: { family: 'JetBrains Mono', weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'JetBrains Mono', weight: 'bold' } }
                    }
                }
            }
        });
    </script>
</body>
</html>
