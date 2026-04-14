<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCE CMS | Gateway</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Space+Grotesk:wght@800&display=swap" rel="stylesheet">
    <style>
        .error-msg {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
            display: none; /* Hidden by default */
        }
        .error-border {
            border-color: red !important;
        }
    </style>
</head>
<body class="grid-wrapper" style="display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 40px 0;">

    <div class="contact-container" style="max-width: 550px; width: 95%;">
        <div id="auth-badge" class="contact-badge" style="background: var(--neo-yellow); color: black; transform: rotate(-1deg);">
            SYSTEM_ACCESS_v1.0
        </div>

        <div class="contact-card" style="grid-template-columns: 1fr; padding: 2.5rem;">
            
            <div style="display: flex; gap: 10px; margin-bottom: 2rem; border-bottom: 4px solid black; padding-bottom: 1.5rem;">
                <button onclick="toggleAuth('login')" id="tab-login" class="neo-btn btn-black" style="padding: 10px 20px; font-size: 0.8rem; cursor: pointer;">LOGIN</button>
                <button onclick="toggleAuth('register')" id="tab-reg" class="neo-btn btn-white" style="padding: 10px 20px; font-size: 0.8rem; cursor: pointer;">REGISTER</button>
            </div>

            <div id="login-form-area">
                <h1 class="contact-title" style="font-size: 3rem;">LOGIN.</h1>
                <form id="login-form" action="core/auth_logic.php?action=login" method="POST">
                    <div class="input-group">
                        <label>> EMAIL_ADDRESS</label>
                        <input type="email" id="login_email" name="email" placeholder="shravan@pillai.edu">
                        <span class="error-msg" id="error_login_email"></span>
                    </div>
                    <div class="input-group">
                        <label>> PASSWORD</label>
                        <input type="password" id="login_password" name="password" placeholder="********">
                        <span class="error-msg" id="error_login_password"></span>
                    </div>
                    <button type="submit" id="login_submit" class="transmit-btn">START YOUR JOURNEY</button>
                </form>
            </div>

            <div id="register-form-area" style="display: none;">
                <h1 class="contact-title" style="font-size: 3rem;">JOIN_PCE.</h1>
                <form id="reg-form" action="core/auth_logic.php?action=register" method="POST">
                    <div class="input-group">
                        <label>> FULL NAME</label>
                        <input type="text" id="full_name" name="full_name" placeholder="John Doe">
                        <span class="error-msg" id="error_full_name"></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group">
                            <label>> D.O.B</label>
                            <input type="date" id="dob" name="dob">
                            <span class="error-msg" id="error_dob"></span>
                        </div>
                        <div class="input-group">
                            <label>> BLOOD GRP</label>
                            <input type="text" id="blood_group" name="blood_group" placeholder="B+ve">
                            <span class="error-msg" id="error_blood_group"></span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group">
                            <label>> PHONE NUMBER</label>
                            <input type="text" id="phone_number" name="phone_number" placeholder="9876543210">
                            <span class="error-msg" id="error_phone_number"></span>
                        </div>
                        <div class="input-group">
                            <label>> ADMISSION_NO</label>
                            <input type="text" id="admission_no" name="admission_no" placeholder="PCE24_001">
                            <span class="error-msg" id="error_admission_no"></span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group">
                            <label>> YEAR</label>
                            <input type="number" id="admission_year" name="admission_year" value="2026">
                            <span class="error-msg" id="error_admission_year"></span>
                        </div>
                        <div class="input-group">
                            <label>> EMAIL_ID</label>
                            <input type="email" id="email" name="email">
                            <span class="error-msg" id="error_email"></span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group">
                            <label>> SET PASSWORD</label>
                            <input type="password" id="password" name="password">
                            <span class="error-msg" id="error_password"></span>
                        </div>
                        <div class="input-group">
                            <label>> CONFIRM PASS</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                            <span class="error-msg" id="error_confirm_password"></span>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>> REGISTER AS</label>
                        <select id="role" name="role" style="width:100%; padding:12px; border:4px solid black; font-family:'JetBrains Mono'; font-weight:900; background: white;">
                            <option value="">SELECT ROLE</option>
                            <option value="student">STUDENT</option>
                            <option value="faculty">FACULTY</option>
                        </select>
                        <span class="error-msg" id="error_role"></span>
                    </div>

                    <button type="submit" id="reg_submit" class="transmit-btn" style="background: var(--neo-pink); color: white;">SUBMIT FOR APPROVAL</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAuth(view) {
            const loginArea = document.getElementById('login-form-area');
            const regArea = document.getElementById('register-form-area');
            const tabLogin = document.getElementById('tab-login');
            const tabReg = document.getElementById('tab-reg');
            const badge = document.getElementById('auth-badge');

            if (view === 'register') {
                loginArea.style.display = 'none';
                regArea.style.display = 'block';
                tabReg.className = 'neo-btn btn-black';
                tabLogin.className = 'neo-btn btn-white';
                badge.innerText = 'PCE';
                badge.style.background = 'var(--neo-pink)';
                badge.style.color = 'white';
            } else {
                loginArea.style.display = 'block';
                regArea.style.display = 'none';
                tabLogin.className = 'neo-btn btn-black';
                tabReg.className = 'neo-btn btn-white';
                badge.innerText = 'SYSTEM_ACCESS_v1.0';
                badge.style.background = 'var(--neo-yellow)';
                badge.style.color = 'black';
            }
        }

    </script>
    <script src="assets/js/validation.js"></script>
</body>
</html>
