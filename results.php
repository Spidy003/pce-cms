<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}
include 'config/db_connect.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT subject_name, marks_obtained FROM results WHERE student_id = '$user_id'";
$res = mysqli_query($conn, $query);

$subjects = [];
$marks = [];

while($row = mysqli_fetch_assoc($res)) {
    $subjects[] = $row['subject_name'];
    $marks[] = $row['marks_obtained'];
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
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
</head>
<body class="bg-neo-white" style="padding: 2rem;">

    <div class="container-narrow">
        <header class="results-header reveal">
            <h1 style="font-size: 3rem; font-weight: 900; text-transform: uppercase; font-style: italic; letter-spacing: -2px;">Result_Stats</h1>
            <a href="student_dashboard.php" class="neo-btn" style="background: var(--neo-black); color: white; text-decoration: none;">BACK_</a>
        </header>

        <div class="chart-container reveal">
            <canvas id="resultsChart"></canvas>
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

    

    <script src="assets/js/script.js"></script>

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
