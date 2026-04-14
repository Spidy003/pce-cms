<?php
include 'config/db_connect.php';
session_start();

if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Quick Stats
$pending = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 0")->fetch_assoc()['total'];
$faculty = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'faculty'")->fetch_assoc()['total'];
$students = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'student' AND status = 1")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PCE CMS | COMMAND_CENTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f0f0f0; font-family: 'Space Grotesk', sans-serif; padding: 40px; overflow-x: hidden; }

        .header-area { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 50px; border-bottom: 5px solid black; padding-bottom: 20px; }
        .hero-title { font-size: 4rem; text-transform: uppercase; line-height: 0.9; }

        .command-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }

        .function-card {
            background: white; border: 5px solid black; padding: 30px; cursor: pointer;
            transition: 0.2s; position: relative; box-shadow: 10px 10px 0px black;
        }
        .function-card:hover { transform: translate(-5px, -5px); box-shadow: 15px 15px 0px black; }
        .function-card h2 { font-size: 2rem; margin-bottom: 10px; text-transform: uppercase; }
        .function-card p { font-family: 'JetBrains Mono'; color: #666; }
        .card-icon { font-size: 3rem; margin-bottom: 20px; display: block; }

        #work-area {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: white; z-index: 2000; padding: 60px; overflow-y: auto;
        }
        .close-btn { position: fixed; top: 20px; right: 20px; background: var(--neo-pink); color: white; padding: 15px 30px; border: 4px solid black; font-weight: 900; cursor: pointer; }

        .hidden-data { display: none; }

        .admin-card { background: white; border: 4px solid black; box-shadow: 10px 10px 0px black; padding: 30px; margin-top: 20px; }
        .neo-table { width: 100%; border-collapse: collapse; font-family: 'JetBrains Mono'; margin-top: 15px; }
        .neo-table th { background: black; color: white; padding: 15px; text-align: left; }
        .neo-table td { padding: 15px; border-bottom: 2px solid black; }

        .neo-input { width: 100%; padding: 15px; border: 4px solid black; font-family: 'JetBrains Mono'; margin: 10px 0; font-weight: 900; }
        .neo-btn { padding: 10px 20px; border: 4px solid black; font-weight: 900; text-transform: uppercase; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.1s; }
        .neo-btn:active { transform: translate(2px, 2px); }
    </style>
</head>
<body>

    <header class="header-area">
        <div>
            <h1 class="hero-title">ADMIN<br>TERMINAL.</h1>
            <p style="font-family: 'JetBrains Mono'; margin-top: 10px;">> USER: SHRAVAN | STATUS: ONLINE</p>
        </div>
        <a href="logout.php" class="neo-btn" style="background: var(--neo-pink); color: white; box-shadow: 5px 5px 0px black;">LOGOUT_</a>
    </header>

    <div class="command-grid">
        <div class="function-card" style="border-top: 15px solid var(--neo-yellow);" onclick="openWork('approval')">
            <span class="card-icon">🔐</span>
            <h2>Approval Queue</h2>
            <p>Pending: <?php echo $pending; ?> users</p>
        </div>

        <div class="function-card" style="border-top: 15px solid var(--neo-blue);" onclick="openWork('roles')">
            <span class="card-icon">🔄</span>
            <h2>Role Swapper</h2>
            <p>Update faculty/HOD access</p>
        </div>

        <div class="function-card" style="border-top: 15px solid #000;" onclick="openWork('class-mgmt')">
            <span class="card-icon">🏗️</span>
            <h2>Class Architect</h2>
            <p>Build Divisions & Batches</p>
        </div>

        <div class="function-card" style="border-top: 15px solid #ff4500;" onclick="openWork('timetable')">
            <span class="card-icon">📅</span>
            <h2>Timetable Architect</h2>
            <p>Push Schedules Batch-wise</p>
        </div>

        <div class="function-card" style="border-top: 15px solid var(--neo-green);" onclick="openWork('assign')">
            <span class="card-icon">🗺️</span>
            <h2>Subject Mapper</h2>
            <p>Assign Faculty to Batches</p>
        </div>

        <div class="function-card" style="border-top: 15px solid #ff00ff;" onclick="openWork('onboarding')">
            <span class="card-icon">📂</span>
            <h2>Batch Import</h2>
            <p>Bulk CSV Registration</p>
        </div>

        <div class="function-card" style="border-top: 15px solid black;" onclick="openWork('broadcast')">
            <span class="card-icon">📢</span>
            <h2>Global Notice</h2>
            <p>Push alerts to all users</p>
        </div>
    </div>

    <div id="work-area">
        <button class="close-btn" onclick="closeWork()">CLOSE [ESC]</button>
        <div id="work-content"></div>
    </div>

    <div class="hidden-data">
        
        <div id="tpl-timetable">
            <h1 style="font-size: 3rem;">/ TIMETABLE_ARCHITECT</h1>
            <div class="admin-card">
                <form action="core/process_admin.php?action=add_timetable" method="POST">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <label>> SELECT_DIVISION</label>
                            <select name="class_id" class="neo-input">
                                <?php 
                                $cls = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                while($c = $cls->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>> TARGET_BATCH</label>
                            <select name="batch_id" class="neo-input">
                                <option value="ALL">ALL (Full Lecture)</option>
                                <option value="B1">B1 (Lab)</option>
                                <option value="B2">B2 (Lab)</option>
                                <option value="B3">B3 (Lab)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:20px; margin-top:20px;">
                        <div><label>> SUBJECT_NAME</label><input type="text" name="subject" class="neo-input" placeholder="e.g. Data Structures" required></div>
                        <div><label>> DAY</label>
                            <select name="day" class="neo-input">
                                <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                                <option>Thursday</option><option>Friday</option><option>Saturday</option>
                            </select>
                        </div>
                        <div><label>> TIME</label><input type="time" name="start_time" class="neo-input" required></div>
                    </div>

                    <button type="submit" class="neo-btn" style="background:black; color:white; width:100%; margin-top:30px;">PUSH_TO_STUDENT_DASHBOARD</button>
                </form>
            </div>
        </div>

        <div id="tpl-class-mgmt">
            <h1 style="font-size: 3rem;">/ CLASS_ARCHITECT</h1>
            <div class="admin-card" style="border-color:var(--neo-green);">
                <form action="core/process_admin.php?action=create_class" method="POST" style="display:flex; gap:10px;">
                    <input type="text" name="c_name" placeholder="New Class (e.g. IT-A)" class="neo-input" style="margin:0;" required>
                    <button type="submit" class="neo-btn" style="background:var(--neo-green);">CREATE_</button>
                </form>
            </div>

            <div class="admin-card" style="background:var(--neo-yellow); margin-top:20px;">
                <label>> SELECT_TARGET_CLASS_FOR_ENROLLMENT</label>
                <select id="active-class" class="neo-input" onchange="updateContext(this.value)">
                    <option value="">-- SELECT --</option>
                    <?php 
                    $cl = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                    while($c = $cl->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['class_name']}</option>";
                    ?>
                </select>
            </div>

            <div class="admin-card">
                <table class="neo-table">
                    <thead><tr><th>STUDENT</th><th>ADMISSION</th><th>CURR_CLASS</th><th>BATCH</th><th>ACTION</th></tr></thead>
                    <tbody>
                    <?php 
                    $query = "SELECT u.*, e.class_id as enrolled_class_id, c.class_name, e.batch_name 
                              FROM users u 
                              LEFT JOIN enrollments e ON u.id = e.student_id 
                              LEFT JOIN classes c ON e.class_id = c.id 
                              WHERE u.role='student' AND u.status=1";
                    $st = $conn->query($query);
                    while($s = $st->fetch_assoc()): ?>
                    <tr>
                    <td colspan="5" style="padding:0;">
                    <form action="core/process_admin.php?action=quick_enroll" method="POST" style="display:flex; width:100%; padding:15px; align-items:center;">
                        <div style="flex:2;"><strong><?php echo $s['full_name']; ?></strong></div>
                        <div style="flex:1;"><?php echo $s['admission_no'] ? $s['admission_no'] : 'N/A'; ?></div>
                        <div style="flex:1; font-family:'JetBrains Mono'; font-size:0.9rem; color:var(--neo-blue);">
                            <strong><?php echo $s['class_name'] ? $s['class_name'] : 'NO_CLASS'; ?></strong>
                        </div>
                        <div style="flex:1;">
                            <select name="batch" class="neo-input" style="padding:5px; margin:0;">
                                <?php $b = $s['batch_name']; ?>
                                <option value="B1" <?php if($b=='B1') echo 'selected';?>>B1</option>
                                <option value="B2" <?php if($b=='B2') echo 'selected';?>>B2</option>
                                <option value="B3" <?php if($b=='B3') echo 'selected';?>>B3</option>
                                <option value="ALL" <?php if($b=='ALL') echo 'selected';?>>ALL</option>
                            </select>
                        </div>
                        <div style="flex:1; text-align:right;">
                            <input type="hidden" name="s_id" value="<?php echo $s['id']; ?>">
                            <input type="hidden" name="c_id" class="hidden-c-id" value="">
                            <button type="submit" class="neo-btn" style="background:black; color:white; font-size:0.7rem;">
                                <?php echo $s['class_name'] ? 'UPDATE_ASSIGNMENT' : 'ADD_STUDENT'; ?>
                            </button>
                        </div>
                    </form>
                    </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
        </div>

        <div id="tpl-approval">
            <h1 style="font-size: 3rem;">/ APPROVAL_QUEUE</h1>
            <div class="admin-card">
                <table class="neo-table">
                    <thead><tr><th>NAME</th><th>ROLE</th><th>ACTION</th></tr></thead>
                    <tbody>
                    <?php 
                    $p_users = $conn->query("SELECT * FROM users WHERE status = 0");
                    while($u = $p_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['full_name']; ?></td>
                            <td><?php echo $u['role']; ?></td>
                            <td><a href="process_admin.php?id=<?php echo $u['id']; ?>&action=approve" class="neo-btn" style="background:var(--neo-green);">GRANT</a></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tpl-roles">
            <h1 style="font-size: 3rem;">/ ROLE_SWAPPER</h1>
            <div class="admin-card">
                <form action="core/process_admin.php?action=roleswap" method="POST">
                    <label>> TARGET_USER_EMAIL</label>
                    <input type="email" name="email" class="neo-input" placeholder="user@pillai.edu" required>
                    
                    <label>> NEW_ROLE_ASSIGNMENT</label>
                    <select name="new_role" class="neo-input">
                        <option value="student">STUDENT</option>
                        <option value="faculty">FACULTY</option>
                        <option value="admin">SYSTEM_ADMIN</option>
                    </select>
                    
                    <button type="submit" class="neo-btn" style="background:var(--neo-blue); color:white; width:100%; margin-top:20px;">EXECUTE_SWAP</button>
                </form>
            </div>
        </div>

        <div id="tpl-assign">
            <h1 style="font-size: 3rem;">/ SUBJECT_MAPPER</h1>
            <div class="admin-card">
                <form action="core/process_admin.php?action=assign_subject" method="POST" onsubmit="submitMapping(event, this)">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div>
                            <label>> TARGET_FACULTY</label>
                            <select name="faculty_id" class="neo-input">
                                <?php 
                                $fac = $conn->query("SELECT * FROM users WHERE role='faculty' OR role='admin'");
                                while($f = $fac->fetch_assoc()) echo "<option value='{$f['id']}'>{$f['full_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>> SUBJECT_NAME</label>
                            <input type="text" name="sub_name" class="neo-input" placeholder="e.g. DBMS" required>
                        </div>
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
                        <div>
                            <label>> TARGET_DIVISION</label>
                            <select name="div" class="neo-input">
                                <?php 
                                $cls = $conn->query("SELECT * FROM classes ORDER BY class_name ASC");
                                while($c = $cls->fetch_assoc()) echo "<option value='{$c['class_name']}'>{$c['class_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>> TARGET_BATCH</label>
                            <select name="batch" class="neo-input">
                                <option value="ALL">ALL (Full Class)</option>
                                <option value="B1">B1</option>
                                <option value="B2">B2</option>
                                <option value="B3">B3</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="neo-btn" style="background:var(--neo-green); color:black; width:100%; margin-top:30px;">DEPLOY_MAPPING</button>
                    <div id="mapping-message" style="margin-top: 15px; font-weight: bold; font-family: 'JetBrains Mono'; text-align: center;"></div>
                </form>
            </div>
        </div>

        <div id="tpl-onboarding">
            <h1 style="font-size: 3rem;">/ BATCH_IMPORT</h1>
            <div class="admin-card">
                <form action="core/process_admin.php?action=csv_upload" method="POST" enctype="multipart/form-data">
                    <div style="border: 4px dashed black; padding: 40px; text-align: center; margin-bottom: 20px;">
                        <span class="card-icon" style="font-size: 2rem;">📁</span>
                        <h3 style="font-family: 'Space Grotesk'; font-size: 1.5rem;">UPLOAD CSV FILE</h3>
                        <p style="margin-top: 10px; font-family: 'JetBrains Mono';">Headers: name, email, password, admission_no</p>
                    </div>
                    <input type="file" name="csv_file" class="neo-input" accept=".csv" required>
                    
                    <button type="submit" class="neo-btn" style="background:#ff00ff; color:white; width:100%; margin-top:20px;">COMMENCE_IMPORT</button>
                </form>
            </div>
        </div>

        <div id="tpl-broadcast">
            <h1 style="font-size: 3rem;">/ GLOBAL_NOTICE</h1>
            <div class="admin-card">
                <form action="core/process_admin.php?action=post_notice" method="POST">
                    <label>> NOTICE_TITLE</label>
                    <input type="text" name="title" class="neo-input" placeholder="URGENT: Server Maintenance" required>
                    
                    <label>> BROADCAST_MESSAGE</label>
                    <textarea name="content" class="neo-input" rows="5" placeholder="Enter details here..." required></textarea>
                    
                    <button type="submit" class="neo-btn" style="background:black; color:white; width:100%; margin-top:20px;">TRANSMIT_TO_ALL</button>
                </form>
            </div>
        </div>

    </div>

    <script>
    // Store class ID globally so it survives modal re-renders
    let selectedClassId = "";

    function openWork(type) {
        const area = document.getElementById('work-area');
        const content = document.getElementById('work-content');
        const template = document.getElementById('tpl-' + type);
        
        if(template) {
            content.innerHTML = template.innerHTML;
            area.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Re-apply the class ID if we are opening class management
            if(type === 'class-mgmt' && selectedClassId !== "") {
                syncInputs(selectedClassId);
            }
        }
    }

    function updateContext(val) {
        selectedClassId = val; // Set global variable
        syncInputs(val);
    }

    function syncInputs(val) {
        // We look for inputs specifically inside the WORK AREA, not the hidden template
        const activeArea = document.getElementById('work-content');
        const hiddenInputs = activeArea.querySelectorAll('.hidden-c-id');
        hiddenInputs.forEach(input => {
            input.value = val;
        });
        
        // Update the dropdown visual if it exists in the new content
        const dropdown = document.getElementById('active-class');
        if(dropdown) dropdown.value = val;
        
        console.log("Context Synced: " + val);
    }

    function closeWork() {
        document.getElementById('work-area').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function submitMapping(e, form) {
        e.preventDefault();
        const msgBox = form.querySelector('#mapping-message');
        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = 'DEPLOYING...';
        msgBox.innerHTML = '';

        const formData = new FormData(form);
        formData.append('ajax', '1');

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                msgBox.innerHTML = '> SUCCESS: Faculty successfully assigned to batch.';
                msgBox.style.color = 'green';
                form.reset();
            } else {
                msgBox.innerHTML = '> FAILED: Unable to assign faculty.';
                msgBox.style.color = 'red';
            }
        })
        .catch(err => {
            msgBox.innerHTML = '> FAILED: Network error or server issue.';
            msgBox.style.color = 'red';
        })
        .finally(() => {
            btn.innerHTML = originalText;
        });
    }

    document.onkeydown = function(evt) { if (evt.keyCode == 27) closeWork(); };

    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('status')) {
            openWork('class-mgmt');
        }
    }
</script>
</body>
</html>
