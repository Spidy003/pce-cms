<?php
include '../config/db_connect.php';
session_start();

// Security Gate
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'student') {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// 1. Fetch Student Bio and ENROLLMENT Data
// FIXED: This now pulls from the enrollments table to show the Admin-assigned Class/Batch
$stmt = $conn->prepare("
    SELECT u.*, c.class_name, e.batch_name 
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.student_id
    LEFT JOIN classes c ON e.class_id = c.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

// 2. Fetch Latest Notice
$notice_query = $conn->query("SELECT * FROM notices ORDER BY id DESC LIMIT 1");
$latest_notice = $notice_query->fetch_assoc();

// 3. Fetch Real Attendance
$att_stmt = $conn->prepare("SELECT SUM(present_lectures) as pr, SUM(total_lectures) as tot FROM attendance WHERE student_id = ?");
$att_stmt->bind_param("i", $student_id);
$att_stmt->execute();
$att_res = $att_stmt->get_result()->fetch_assoc();
if (!empty($att_res['tot']) && $att_res['tot'] > 0) {
    $attendance = round(($att_res['pr'] / $att_res['tot']) * 100);
} else {
    $attendance = 100; // default state
}

// 4. Warning Logic
$status_color = ($attendance >= 75) ? '#00ff00' : '#FF3131';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE | STUDENT_LOG</title>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* --- AI CHATBOT WIDGET INLINE --- */
        .pomo-toggle-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #ffff00;
            border: 4px solid black;
            padding: 15px 20px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 900;
            box-shadow: 6px 6px 0px black;
            cursor: pointer;
            z-index: 1000;
            transition: 0.2s;
            font-size: 1.1rem;
            color: black;
        }

        .pomo-toggle-btn:hover {
            transform: translate(-3px, -3px);
            box-shadow: 9px 9px 0px black;
        }

        .pomodoro-widget {
            position: fixed;
            bottom: 90px;
            right: 30px;
            width: 350px;
            background: white;
            border: 5px solid black;
            box-shadow: 10px 10px 0px black;
            z-index: 1100;
            font-family: 'Space Grotesk', sans-serif;
            display: flex;
            flex-direction: column;
        }

        .pomo-header {
            background: #0077ff;
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid black;
        }

        .pomo-header h3 {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 900;
            font-size: 1.1rem;
            margin: 0;
        }

        .pomo-close-btn {
            background: #ff007f;
            color: white;
            border: 3px solid black;
            width: 30px;
            height: 30px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 2px 2px 0px black;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pomo-close-btn:hover {
            background: white;
            color: black;
        }

        .chat-body {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: 300px;
            overflow-y: auto;
            background: #fdfdfd;
            border-bottom: 4px solid black;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
        }

        .chat-message {
            padding: 10px;
            border: 2px solid black;
            width: 85%;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .ai-message {
            background: #eeeeee;
            align-self: flex-start;
            box-shadow: 3px 3px 0px black;
        }

        .user-message {
            background: #ffff00;
            align-self: flex-end;
            box-shadow: -3px 3px 0px black;
        }

        .chat-input-area {
            display: flex;
            padding: 10px;
            background: #ffffff;
            gap: 10px;
        }

        .chat-input-area input {
            flex-grow: 1;
            border: 3px solid black;
            padding: 8px;
            font-family: 'JetBrains Mono', monospace;
            outline: none;
        }

        .chat-input-area input:focus {
            background: #ffff00;
        }

        .pomo-btn {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 900;
            border: 3px solid black;
            padding: 8px 15px;
            cursor: pointer;
            box-shadow: 3px 3px 0px black;
            transition: 0.1s;
            font-size: 1.1rem;
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pomo-btn:active {
            transform: translate(3px, 3px);
            box-shadow: 0px 0px 0px black;
        }

        .bg-green {
            background: #00ff00;
        }

        @keyframes blink {
            from {
                opacity: 1;
            }

            to {
                opacity: 0.2;
            }
        }

        @media (max-width: 480px) {
            .pomodoro-widget {
                width: 90vw;
                right: 5vw;
                bottom: 80px;
            }

            .pomo-toggle-btn {
                bottom: 20px;
                right: 20px;
                font-size: 0.9rem;
                padding: 10px 15px;
            }
        }

        /* --- CORE THEME --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sidebar-width: 300px;
            --neo-yellow: #ffff00;
            --neo-green: #00ff00;
            --neo-pink: #ff007f;
            --neo-blue: #0077ff;
        }

        body {
            display: flex;
            background: #e0e0e0;
            min-height: 100vh;
            font-family: 'Space Grotesk', sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(black 1px, transparent 1px), radial-gradient(black 1px, transparent 1px);
            background-size: 30px 30px;
            background-position: 0 0, 15px 15px;
            opacity: 0.1;
            z-index: -1;
            pointer-events: none;
        }

        .timetable-side {
            width: var(--sidebar-width);
            background: #111;
            color: white;
            padding: 2rem;
            border-right: 8px solid black;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .schedule-item {
            border-left: 4px solid var(--neo-yellow);
            background: #222;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #333;
        }

        .schedule-item h4 {
            font-family: 'JetBrains Mono';
            color: var(--neo-yellow);
            margin-bottom: 5px;
        }

        .dashboard-main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
        }

        .notice-banner {
            background: var(--neo-yellow);
            border: 5px solid black;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 10px 10px 0px black;
        }

        .meter-container {
            background: white;
            border: 6px solid black;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 15px 15px 0px black;
        }

        .meter-bar {
            height: 50px;
            background: #eee;
            border: 4px solid black;
            width: 100%;
            position: relative;
        }

        .meter-fill {
            height: 100%;
            transition: 1s ease-in-out;
            display: flex;
            align-items: center;
            padding-left: 15px;
            font-weight: 900;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 35px;
        }

        .neo-card {
            background: white;
            border: 5px solid black;
            padding: 25px;
            box-shadow: 12px 12px 0px black;
            transition: 0.2s;
        }

        .neo-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 18px 18px 0px var(--neo-pink);
        }

        .id-badge {
            background: white;
            border: 4px solid black;
            padding: 20px;
            text-align: center;
            transform: rotate(-1deg);
        }

        .profile-pic-mini {
            width: 100px;
            height: 100px;
            border: 4px solid black;
            object-fit: cover;
            margin-bottom: 10px;
            background: #eee;
        }

        .neo-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .neo-table th {
            background: black;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .neo-table td {
            border-bottom: 2px solid black;
            padding: 10px;
            font-family: 'JetBrains Mono';
        }

        .neo-btn {
            padding: 12px;
            border: 4px solid black;
            font-weight: 900;
            cursor: pointer;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-block;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            border: 4px solid black;
            margin-bottom: 15px;
            font-family: 'JetBrains Mono';
        }

        /* --- RESPONSIVE LOGIC --- */
        @media (max-width: 1024px) {
            body {
                flex-direction: column;
            }

            .timetable-side {
                position: relative;
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 8px solid black;
                display: flex;
                flex-direction: column;
            }

            .dashboard-main {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .student-grid {
                grid-template-columns: 1fr;
            }

            .neo-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .notice-banner {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .id-badge {
                transform: rotate(0deg);
            }
        }
    </style>
</head>

<body>

    <aside class="timetable-side">
        <h2 style="color: var(--neo-yellow); margin-bottom: 5px; font-size: 2rem;">/ TODAY_SCH</h2>
        <p style="font-family: 'JetBrains Mono'; font-size: 0.8rem; color: #888; margin-bottom: 20px;">
            > CLASS: <?php echo $me['class_name'] ?? 'UNASSIGNED'; ?><br>
            > BATCH: <?php echo $me['batch_name'] ?? 'N/A'; ?>
        </p>

        <?php
        $day = date('l');
        // PERSONALIZED TIMETABLE QUERY
        $tt_query = $conn->prepare("
            SELECT t.* FROM timetable t
            JOIN enrollments e ON t.class_id = e.class_id
            WHERE e.student_id = ? 
            AND t.day_of_week = ? 
            AND (t.batch_id = e.batch_name OR t.batch_id = 'ALL')
            ORDER BY t.start_time ASC
        ");
        $tt_query->bind_param("is", $student_id, $day);
        $tt_query->execute();
        $tt_res = $tt_query->get_result();

        if ($tt_res->num_rows > 0):
            while ($slot = $tt_res->fetch_assoc()): ?>
                <div class="schedule-item">
                    <h4><?php echo date('h:i A', strtotime($slot['start_time'])); ?></h4>
                    <p><?php echo strtoupper($slot['subject_name']); ?></p>
                    <span style="font-size: 0.6rem; color: var(--neo-yellow);">[<?php echo $slot['batch_id']; ?>]</span>
                </div>
            <?php endwhile;
        else: ?>
            <p style="color: #888; font-family: 'JetBrains Mono';">> STATUS: NO_ACTIVE_LECTURES</p>
        <?php endif; ?>

        <a href="../auth/logout.php" class="neo-btn"
            style="background: var(--neo-pink); color: white; margin-top: 50px; display: block; text-align: center;">LOGOUT_</a>
    </aside>

    <main class="dashboard-main">

        <?php if ($latest_notice): ?>
            <div class="notice-banner" id="js-notice">
                <div>
                    <strong
                        style="font-family: 'JetBrains Mono'; background:black; color:white; padding:2px 5px;">[ALERT]</strong>
                    <span
                        style="margin-left: 15px; font-weight: 900;"><?php echo strtoupper($latest_notice['title']); ?></span>
                </div>
                <button onclick="document.getElementById('js-notice').style.display='none'" class="neo-btn"
                    style="background: black; color: white; padding: 5px 15px;">ACK_</button>
            </div>
        <?php endif; ?>

        <div class="meter-container">
            <h2 style="margin: 0 0 15px 0; text-transform: uppercase;">/ ATTENDANCE_PULSE</h2>
            <div class="meter-bar">
                <div class="meter-fill"
                    style="width: <?php echo $attendance; ?>%; background: <?php echo $status_color; ?>;">
                    <?php echo $attendance; ?>%
                </div>
            </div>
            <p
                style="margin-top: 15px; font-family: 'JetBrains Mono'; font-weight: bold; color: <?php echo $status_color; ?>;">
                <?php echo ($attendance < 75) ? "> STATUS: DEFAULTER_ALERT (CRITICAL)" : "> STATUS: CLEAR"; ?>
            </p>
        </div>

        <div class="student-grid">

            <div class="neo-card">
                <h3>/ ASSIGNMENTS_SUBMISSION</h3>
                <div style="margin-top: 15px; max-height: 400px; overflow-y: auto;">
                    <?php
                    $my_div = !empty($me['class_name']) ? $me['class_name'] : ($me['division'] ?? '');
                    $my_batch = !empty($me['batch_name']) ? $me['batch_name'] : ($me['lab_batch'] ?? 'ALL');

                    $assigns = $conn->query("SELECT * FROM assignments WHERE (division = '$my_div' OR division = 'ALL') AND (batch_id = '$my_batch' OR batch_id = 'ALL') ORDER BY id DESC");

                    if ($assigns && $assigns->num_rows > 0):
                        while ($as = $assigns->fetch_assoc()):
                            $as_id = $as['id'];
                            $check_sub = $conn->query("SELECT status FROM assignment_submissions WHERE assignment_id = $as_id AND student_id = $student_id")->fetch_assoc();
                            ?>
                            <div
                                style="border:3px solid black; padding:15px; margin-bottom:15px; background:<?php echo $check_sub ? '#f9f9f9' : 'white'; ?>;">
                                <strong style="text-transform: uppercase;"><?php echo $as['title']; ?></strong>
                                <p style="font-size:0.7rem; margin: 5px 0; font-family: 'JetBrains Mono';">
                                    <?php echo $as['description']; ?></p>

                                <?php if (!$check_sub): ?>
                                    <form action="../core/process_student.php?action=upload_assignment" method="POST"
                                        enctype="multipart/form-data"
                                        style="margin-top:10px; border-top: 1px dashed black; padding-top: 10px;">
                                        <input type="hidden" name="assignment_id" value="<?php echo $as['id']; ?>">
                                        <input type="file" name="sub_file" required
                                            style="font-size: 0.7rem; border: none; padding: 0;">
                                        <button type="submit" class="neo-btn"
                                            style="background:var(--neo-blue); color:white; font-size:0.6rem; width: 100%; margin-top: 5px;">UPLOAD_SUBMISSION</button>
                                    </form>
                                <?php else:
                                    $s_color = ($check_sub['status'] == 'Correct') ? 'var(--neo-green)' : (($check_sub['status'] == 'Wrong') ? 'var(--neo-pink)' : 'black');
                                    ?>
                                    <div style="margin-top: 10px; border-top: 1px dashed black; padding-top: 5px;">
                                        <span style="font-weight:900; color:<?php echo $s_color; ?>;">STATUS:
                                            <?php echo strtoupper($check_sub['status']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile;
                    else:
                        echo "<p style='font-family:\"JetBrains Mono\"; font-size:0.8rem;'>No pending assignments for $my_div.</p>";
                    endif; ?>
                </div>
            </div>

            <div class="neo-card">
                <h3>/ INTERNAL_SCORES</h3>
                <table class="neo-table" style="font-size:0.8rem;">
                    <thead>
                        <tr>
                            <th>SUBJECT</th>
                            <th>IA 1</th>
                            <th>IA 2</th>
                            <th>AVG</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $marks_q = $conn->query("SELECT * FROM results WHERE student_id = $student_id");
                        if ($marks_q && $marks_q->num_rows > 0):
                            while ($m = $marks_q->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo strtoupper($m['subject_name']); ?></strong></td>
                                    <td><?php echo $m['ia1']; ?>/40</td>
                                    <td><?php echo $m['ia2']; ?>/40</td>
                                    <td style="color:var(--neo-green); font-weight:bold;"><?php echo $m['marks_obtained']; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else:
                            echo "<tr><td colspan='4' style='text-align:center;'>> NO_IA_DATA_FOUND.</td></tr>";
                        endif; ?>
                    </tbody>
                </table>
                <a href="results.php" class="neo-btn"
                    style="background:var(--neo-pink); color:white; width:100%; margin-top:15px; text-align:center;">DETAILED_MATRIX</a>
            </div>

            <div class="neo-card" style="background: var(--neo-blue);">
                <h3 style="background:white; display:inline-block; padding:0 10px;">/ VIRTUAL_ID</h3>
                <div class="id-badge">
                    <?php if (!empty($me['profile_pic'])): ?>
                        <img src="uploads/profile/<?php echo $me['profile_pic']; ?>" class="profile-pic-mini" alt="Profile">
                    <?php endif; ?>
                    <p style="font-size: 1.4rem; font-weight: 900; margin: 0;">
                        <?php echo strtoupper($me['full_name']); ?></p>
                    <p
                        style="font-family: 'JetBrains Mono'; border: 2px solid black; background: #ffff00; display: inline-block; padding: 2px 10px; margin: 10px 0;">
                        DIV: <?php echo $me['class_name'] ?? 'N/A'; ?> | BATCH:
                        <?php echo $me['batch_name'] ?? 'N/A'; ?>
                    </p>
                    <div style="margin: 15px auto;">
                        <?php
                        $baseUrl = "http://localhost/pce-cms/verify_student.php";
                        $qrData = $baseUrl . "?ad_no=" . urlencode($me['admission_no']);
                        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
                        ?>
                        <img src="<?php echo $qrUrl; ?>" alt="QR"
                            style="border: 4px solid black; background: white; padding: 5px; width: 130px;">
                    </div>
                    <p style="font-size: 0.7rem; font-weight: 900;">PILLAI COLLEGE OF ENGINEERING</p>
                </div>
            </div>

            <div class="neo-card" style="border-color: var(--neo-pink);">
                <h3>/ SYNC_PROFILE</h3>
                <form action="update_student.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <label style="font-family: 'JetBrains Mono'; font-size: 0.7rem;">[CONTACT_NUMBER]</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($me['phone'] ?? ''); ?>">

                    <label style="font-family: 'JetBrains Mono'; font-size: 0.7rem;">[ADDRESS]</label>
                    <textarea name="address"><?php echo htmlspecialchars($me['address'] ?? ''); ?></textarea>

                    <label style="font-family: 'JetBrains Mono'; font-size: 0.7rem;">[PROFILE_PICTURE]</label>
                    <input type="file" name="profile_pic" accept="image/*">

                    <button type="submit" class="neo-btn"
                        style="background: black; color: white; width: 100%; margin-top: 15px;">SYNC_CHANGES_</button>
                </form>
            </div>

        </div>
    </main>

    <!-- PCE Study Buddy AI Chatbot -->
    <button id="chat-toggle" class="pomo-toggle-btn">
        <i class="ri-robot-2-fill"></i> STUDY BUDDY AI
    </button>

    <div id="chat-widget" class="pomodoro-widget" style="display: none;">
        <div class="pomo-header">
            <h3><i class="ri-robot-line"></i> PCE_HELPER_AI</h3>
            <button id="chat-close" class="pomo-close-btn"><i class="ri-close-line"></i></button>
        </div>
        <div class="chat-body" id="chat-body">
            <div class="chat-message ai-message">
                <strong>SYSTEM:</strong> Hello! I am the PCE Study Buddy AI. How can I assist you with career guidance
                or student queries today? (e.g., 'bonafide certificate', 'concession', 'career')
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Enter query..." onkeypress="handleChatKeyPress(event)">
            <button id="chat-send" class="pomo-btn bg-green"><i class="ri-send-plane-fill"></i></button>
        </div>
    </div>

    <script>
        // === CHATBOT JS INLINE ===
        document.addEventListener('DOMContentLoaded', () => {
            const chatToggleBtn = document.getElementById('chat-toggle');
            const chatWidget = document.getElementById('chat-widget');
            const chatCloseBtn = document.getElementById('chat-close');
            const chatSendBtn = document.getElementById('chat-send');
            const chatInput = document.getElementById('chat-input');
            const chatBody = document.getElementById('chat-body');

            const GEMINI_API_KEY = "AIzaSyBV5jHjHRPE2I07V1Jd0lK9yTxc99qyr0I";

            setTimeout(() => {
                if (chatWidget) chatWidget.style.display = 'flex';
            }, 2000);

            if (chatToggleBtn) chatToggleBtn.addEventListener('click', () => chatWidget.style.display = 'flex');
            if (chatCloseBtn) chatCloseBtn.addEventListener('click', () => chatWidget.style.display = 'none');
            if (chatSendBtn) chatSendBtn.addEventListener('click', handleSend);

            window.handleChatKeyPress = function (e) {
                if (e.key === 'Enter') handleSend();
            }

            async function handleSend() {
                if (!chatInput || !chatBody) return;
                const text = chatInput.value.trim();
                if (text === '') return;

                appendMessage('USER', text, 'user-message');
                chatInput.value = '';

                const loadingDiv = document.createElement('div');
                loadingDiv.className = `chat-message ai-message`;
                loadingDiv.innerHTML = `<strong>GEMINI:</strong> <span style="font-family:'JetBrains Mono'; animation: blink 1s infinite alternate;">...processing...</span>`;
                chatBody.appendChild(loadingDiv);
                chatBody.scrollTop = chatBody.scrollHeight;

                try {
                    const aiText = await fetchGeminiResponse(text);
                    loadingDiv.innerHTML = `<strong>GEMINI:</strong> ` + formatResponse(aiText);
                } catch (error) {
                    loadingDiv.innerHTML = `<strong>SYSTEM_ERROR:</strong> ${error.message}`;
                    loadingDiv.style.color = "red";
                }
                chatBody.scrollTop = chatBody.scrollHeight;
            }

            async function fetchGeminiResponse(userQuery) {
                if (!GEMINI_API_KEY) throw new Error("Missing API Key.");

                const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_API_KEY}`;
                const contextData = `
            PCE College FAQ Database:
            1. Bonafide Certificate: Log in to the PCE Student Portal, go to "Student Services", fill request form. Collect physical copy at admin office.
            2. Railway Concession: On portal, select "Railway Concession", enter source/destination, submit.
            3. Wrong Personal Details: Submit "Profile Update Request" via CMS. For name/DOB, visit Student Section with SSC/HSC docs.
            4. Online Fee Payment: Go to "Fee Payment" module, select year, pay via UPI/Net Banking/Card.
            5. Attendance Update: Marked by faculty via AMS. If wrong, approach teacher within 3 days.
            6. Minimum Attendance: 75% min required. Less leads to terms not granted.
            7. Internal Marks: Check "Marks/Result" section after cycle.
            8. Hall Ticket: Go to "Exam" tab. Fees must be clear & attendance verified.
            9. B.Tech Admission: CAP rounds by DTE/CET. Register on CET portal, list PCE preferred.
            10. Computer Eng Cutoff: Gen category MHT-CET 93-96%, JEE Main ~73,000 rank.
            11. IT Cutoff: MHT-CET 91-93%.
            12. Direct Second Year (DSE): Yes, diploma holders apply via CAP based on diploma %.
            13. Placements: TCS, Infosys, Capgemini, Accenture, Reliance, Wipro, L&T, Jio.
            14. Packages: Avg 5 LPA, highest 15-18 LPA.
            15. Student Associations: MESA, CSI, IEEE organize workshops, hackathons, visits.
            16. Join Association: Register at campus desks during yearly drives.
            17. ASK Portal: Mentor-Mentee system for certificates, co-curricular tracking.
            18. Reset CMS Password: Click "Forgot Password", link sent to @student.mes.ac.in.
            19. Get @student Email: Apply via Google Services link on PCE website or Admin office.
            20. Tech Issues: Visit System Admin 3rd floor or email support@mes.ac.in.
            `;

                const payload = {
                    contents: [{ parts: [{ text: `You are the official PCE Study Buddy AI Chatbot. Answer strictly using this database: ${contextData} Query: "${userQuery}"` }] }]
                };

                const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const data = await response.json();

                if (!response.ok) throw new Error(data.error?.message || "Failed to fetch from Gemini API.");
                if (data.candidates && data.candidates[0].content) return data.candidates[0].content.parts[0].text;
                throw new Error("Received anomalous payload structure from Gemini.");
            }

            function formatResponse(text) { return text.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>'); }
            function appendMessage(sender, text, className) {
                const msgDiv = document.createElement('div');
                msgDiv.className = `chat-message ${className}`;
                msgDiv.innerHTML = `<strong>${sender}:</strong> ${text}`;
                chatBody.appendChild(msgDiv);
            }
        });
    </script>
</body>

</html>