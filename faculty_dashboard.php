<?php
include 'config/db_connect.php';
session_start();

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'faculty') {
    header("Location: login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

// Quick Stats for the Faculty
$my_subjects = $conn->query("SELECT COUNT(*) as total FROM subject_assignments WHERE faculty_id = $faculty_id")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PCE | FACULTY_TERMINAL</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap" rel="stylesheet">
    <style>
        /* --- CORE UI --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { 
            background: #e0e0e0; 
            font-family: 'Space Grotesk', sans-serif; 
            padding: 40px; 
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(black 1px, transparent 1px), radial-gradient(black 1px, transparent 1px);
            background-size: 30px 30px; background-position: 0 0, 15px 15px; opacity: 0.1; z-index: -1; pointer-events: none;
        }

        .header-area { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 50px; border-bottom: 5px solid black; padding-bottom: 20px; position: relative; z-index: 1; }
        .hero-title { font-size: 4rem; text-transform: uppercase; line-height: 0.9; }

        .search-container { margin-bottom: 40px; position: relative; z-index: 1000; }
        .neo-search { width: 100%; padding: 20px; border: 5px solid black; font-family: 'JetBrains Mono'; font-size: 1.2rem; box-shadow: 10px 10px 0px black; outline: none; background: white; }

        .command-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; position: relative; z-index: 1; }

        .function-card { background: white; border: 5px solid black; padding: 30px; cursor: pointer; transition: 0.2s; position: relative; box-shadow: 10px 10px 0px black; }
        .function-card:hover { transform: translate(-5px, -5px); box-shadow: 15px 15px 0px var(--neo-pink); }
        .function-card h2 { font-size: 2rem; margin-bottom: 10px; text-transform: uppercase; }
        .card-icon { font-size: 3rem; margin-bottom: 20px; display: block; }

        #work-area { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 2000; padding: 60px; overflow-y: auto; }
        .close-btn { position: fixed; top: 20px; right: 20px; background: var(--neo-pink); color: white; padding: 15px 30px; border: 4px solid black; font-weight: 900; cursor: pointer; }

        .admin-card { background: white; border: 4px solid black; box-shadow: 10px 10px 0px black; padding: 30px; margin-top: 20px; }
        .neo-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-family: 'JetBrains Mono'; }
        .neo-table th { background: black; color: white; padding: 15px; text-align: left; }
        .neo-table td { padding: 15px; border-bottom: 2px solid black; }

        .neo-input { width: 100%; padding: 15px; border: 4px solid black; font-family: 'JetBrains Mono'; margin: 10px 0; font-weight: 900; }
        .neo-btn { padding: 10px 20px; border: 4px solid black; font-weight: 900; text-transform: uppercase; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.1s; }
        .hidden-tpl { display: none; }

        /* --- Live Search Stylings --- */
        #searchResults { margin-top: 5px; width: 100%; position: absolute; top: 100%; left: 0; z-index: 10000; display:flex; flex-direction: column; gap: 10px; }
        .user-card { display: flex; justify-content: space-between; align-items: center; padding: 20px; border: 4px solid black; background: white; box-shadow: 6px 6px 0px black; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .user-card:hover { transform: translate(-4px, -4px); box-shadow: 10px 10px 0px var(--neo-pink); }
        .user-info h3 { margin: 0 0 5px 0; color: black; font-size: 22px; text-transform: uppercase; }
        .user-info p { margin: 0; color: #333; font-size: 14px; font-family: 'JetBrains Mono', monospace; }
        .user-role { background: var(--neo-yellow); color: black; padding: 5px 12px; border: 3px solid black; font-size: 14px; font-family: 'JetBrains Mono', monospace; text-transform: uppercase; }
        .status-msg { text-align: center; padding: 20px; font-family: 'JetBrains Mono', monospace; font-weight: 700; background: white; border: 4px solid black; box-shadow: 8px 8px 0px black; text-transform: uppercase; width: 100%; }
    </style>
</head>
<body>

    <header class="header-area">
        <div>
            <h1 class="hero-title">FACULTY<br>COMMAND.</h1>
            <p style="font-family: 'JetBrains Mono'; margin-top: 10px;">> ID: <?php echo $faculty_id; ?> | SUBJECTS: <?php echo $my_subjects; ?></p>
        </div>
        <a href="logout.php" class="neo-btn" style="background: black; color: white; padding: 15px 30px;">LOGOUT_</a>
    </header>

    <div class="search-container">
        <input type="text" id="studentSearch" class="neo-search" placeholder="SEARCH_STUDENT_BY_NAME_OR_ROLL...">
        <div id="searchResults"></div>
    </div>

    <div class="command-grid">
        <div class="function-card" style="border-top: 15px solid var(--neo-green);" onclick="openWork('attendance')">
            <span class="card-icon">⏱️</span>
            <h2>Attendance</h2>
            <p>> Batch Pulse Register</p>
        </div>

        <div class="function-card" style="border-top: 15px solid var(--neo-blue);" onclick="openWork('marks')">
            <span class="card-icon">📊</span>
            <h2>Grader</h2>
            <p>> Update Internal Scores</p>
        </div>

        <div class="function-card" style="border-top: 15px solid var(--neo-yellow);" onclick="openWork('assignments')">
            <span class="card-icon">📂</span>
            <h2>Assignments</h2>
            <p>> Post & Check Submissions</p>
        </div>

        <div class="function-card" style="border-top: 15px solid var(--neo-pink);" onclick="openWork('notice')">
            <span class="card-icon">🔔</span>
            <h2>Class Alert</h2>
            <p>> Subject-Specific Broadcast</p>
        </div>
    </div>

    <div id="work-area">
        <button class="close-btn" onclick="closeWork()">CLOSE [ESC]</button>
        <div id="work-content"></div>
    </div>

    <div class="hidden-tpl">
        <div id="tpl-attendance">
            <h1>/ ATTENDANCE_TERMINAL</h1>
            <div class="admin-card" style="text-align: center; padding: 60px;">
                <span class="card-icon" style="font-size: 5rem; margin: 0 auto 20px;">🚧</span>
                <h2>COMING SOON</h2>
                <p style="margin-top: 15px; font-family: 'JetBrains Mono', monospace; color: var(--neo-pink); font-weight: bold; font-size: 1.2rem;">
                    > This module is currently undergoing system upgrades.
                </p>
                <p style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; color: #555;">
                    Phase II Deployment Scheduled.
                </p>
            </div>
        </div>

        <div id="tpl-marks">
            <h1>/ GRADER_PORTAL</h1>
            <div class="admin-card" style="text-align: center; padding: 60px;">
                <span class="card-icon" style="font-size: 5rem; margin: 0 auto 20px;">🚧</span>
                <h2>COMING SOON</h2>
                <p style="margin-top: 15px; font-family: 'JetBrains Mono', monospace; color: var(--neo-pink); font-weight: bold; font-size: 1.2rem;">
                    > The Grading module is under active development.
                </p>
                <p style="margin-top: 5px; font-family: 'JetBrains Mono', monospace; color: #555;">
                    Phase II Deployment Scheduled.
                </p>
            </div>
        </div>

        <div id="tpl-assignments">
            <h1>/ ASSIGNMENT_CONTROL</h1>
            <div class="command-grid" style="grid-template-columns: 1fr 1fr; margin-top:20px;">
                <div class="admin-card">
                    <h3>POST NEW ASSIGNMENT</h3>
                    <form action="core/process_faculty.php?action=post_assignment" method="POST" enctype="multipart/form-data">
                        <select name="assign_id" class="neo-input">
                            <?php 
                            $maps = $conn->query("SELECT * FROM subject_assignments WHERE faculty_id = $faculty_id");
                            while($m = $maps->fetch_assoc()) echo "<option value='{$m['id']}'>{$m['subject_name']} ({$m['division']} - {$m['batch_id']})</option>";
                            ?>
                        </select>
                        <input type="text" name="title" placeholder="Assignment Title" class="neo-input" required>
                        <textarea name="desc" placeholder="Instructions..." class="neo-input" style="height:80px;"></textarea>
                        <input type="file" name="assignment_file" class="neo-input">
                        <button type="submit" class="neo-btn" style="background:var(--neo-green); width:100%;">PUSH_TO_BATCH</button>
                    </form>
                </div>

                <div class="admin-card">
                    <h3>CHECK SUBMISSIONS</h3>
                    <div style="max-height: 400px; overflow-y:auto; font-family:'JetBrains Mono';">
                        <?php
                        $my_posts = $conn->query("SELECT * FROM assignments WHERE faculty_id = $faculty_id ORDER BY id DESC");
                        if($my_posts->num_rows > 0){
                            while($post = $my_posts->fetch_assoc()): ?>
                                <div style="border-bottom:2px solid black; padding:15px 0; display:flex; justify-content:space-between; align-items:center;">
                                    <div><strong><?php echo $post['title']; ?></strong><br><small><?php echo $post['division']; ?> - <?php echo $post['batch_id']; ?></small></div>
                                    <button onclick="loadSubmissions(<?php echo $post['id']; ?>)" class="neo-btn" style="font-size:0.6rem; padding:5px; background:var(--neo-yellow);">VIEW_UPLOADS</button>
                                </div>
                            <?php endwhile;
                        } else { echo "<p>> No assignments posted yet.</p>"; } ?>
                    </div>
                </div>
            </div>
            <div id="submission-viewer" style="margin-top:30px;"></div>
        </div>

        <div id="tpl-notice">
            <h1>/ SUBJECT_BROADCAST</h1>
            <div class="admin-card">
                <form action="core/process_faculty.php?action=post_alert" method="POST">
                    <label>> ALERT_HEADING</label><input type="text" name="title" class="neo-input">
                    <label>> MESSAGE_BODY</label><textarea name="msg" class="neo-input" style="height:100px;"></textarea>
                    <button type="submit" class="neo-btn" style="background:var(--neo-green); color:black; width:100%; box-shadow:5px 5px 0px black;">PUSH_TO_STUDENTS</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openWork(type) {
            document.getElementById('searchResults').innerHTML = '';
            const area = document.getElementById('work-area');
            const content = document.getElementById('work-content');
            const template = document.getElementById('tpl-' + type);
            content.innerHTML = template.innerHTML;
            area.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeWork() {
            document.getElementById('work-area').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // AJAX TO LOAD STUDENT UPLOADS
        function loadSubmissions(assignId) {
            fetch('process_faculty.php?action=view_submissions&as_id=' + assignId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('submission-viewer').innerHTML = "<div class='admin-card'><h3>STUDENT_SUBMISSIONS</h3>" + data + "</div>";
                });
        }

        // LIVE SEARCH LOGIC
        let searchTimeout = null;
        document.getElementById('studentSearch').addEventListener('input', function(e) {
            const query = e.target.value.trim();
            const resultsContainer = document.getElementById('searchResults');
            
            clearTimeout(searchTimeout);
            
            if (query.length === 0) {
                resultsContainer.innerHTML = '';
                return;
            }
            
            resultsContainer.innerHTML = '<div class="status-msg" style="background:var(--neo-green);">FETCHING_DATA...</div>';
            
            searchTimeout = setTimeout(() => {
                fetch(`experiment7_action.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultsContainer.innerHTML = '';
                        if (data.status === 'success') {
                            if (data.data.length > 0) {
                                data.data.forEach(user => {
                                    const card = document.createElement('div');
                                    card.className = 'user-card';
                                    const roleColors = { 'student': 'var(--neo-yellow)', 'faculty': 'var(--neo-pink)', 'admin': 'var(--neo-blue)' };
                                    const bgRole = roleColors[user.role] || 'var(--neo-yellow)';
                                    const textRole = user.role === 'admin' ? 'white' : 'black';
                                    
                                    card.innerHTML = `
                                        <div class="user-info">
                                            <h3>> ${user.full_name}</h3>
                                            <p>ROLL: ${user.roll_no ? user.roll_no : 'N/A'} ${user.admission_no ? ' | ADM: ' + user.admission_no : ''}</p>
                                        </div>
                                        <div class="user-role" style="background:${bgRole}; color:${textRole};">${user.role ? user.role : 'STUDENT'}</div>
                                    `;
                                    resultsContainer.appendChild(card);
                                });
                            } else {
                                resultsContainer.innerHTML = '<div class="status-msg" style="background:var(--neo-pink); color:white;">NO_RECORDS_FOUND.</div>';
                            }
                        } else {
                            resultsContainer.innerHTML = `<div class="status-msg">ERROR: ${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        resultsContainer.innerHTML = '<div class="status-msg" style="background:var(--neo-pink); color:white;">CONNECTION_FAILED.</div>';
                    });
            }, 300);
        });

        // Auto-close search results when cursor leaves the search area or clicking outside
        document.querySelector('.search-container').addEventListener('mouseleave', function() {
            document.getElementById('searchResults').innerHTML = '';
        });
        
        document.addEventListener('click', function(e) {
            if (!document.querySelector('.search-container').contains(e.target)) {
                document.getElementById('searchResults').innerHTML = '';
            }
        });

        document.onkeydown = function(evt) { if (evt.keyCode == 27) closeWork(); };
    </script>
</body>
</html>
