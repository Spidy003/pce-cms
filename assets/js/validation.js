document.addEventListener('DOMContentLoaded', () => {
    // --- LOGIN FORM ELEMENTS ---
    const loginForm = document.getElementById('login-form');
    const loginEmail = document.getElementById('login_email');
    const loginPassword = document.getElementById('login_password');

    // --- REGISTRATION FORM ELEMENTS ---
    const regForm = document.getElementById('reg-form');
    const fullName = document.getElementById('full_name');
    const dob = document.getElementById('dob');
    const bloodGroup = document.getElementById('blood_group');
    const phoneNumber = document.getElementById('phone_number');
    const admissionNo = document.getElementById('admission_no');
    const admissionYear = document.getElementById('admission_year');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const role = document.getElementById('role');

    // --- HELPER FUNCTION: SHOW ERROR ---
    function showError(inputElement, errorMessage) {
        const errorSpan = document.getElementById('error_' + inputElement.id);
        if (errorSpan) {
            errorSpan.textContent = errorMessage;
            errorSpan.style.display = 'block';
        }
        inputElement.classList.add('error-border');
        inputElement.classList.remove('success-border');
    }

    // --- HELPER FUNCTION: CLEAR ERROR ---
    function clearError(inputElement) {
        const errorSpan = document.getElementById('error_' + inputElement.id);
        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.style.display = 'none';
        }
        inputElement.classList.remove('error-border');
        inputElement.classList.add('success-border');
    }

    // --- REGULAR EXPRESSIONS ---
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\d{10}$/;
    const admissionRegex = /^PCE\d{2}_\d{3}$/;

    // --- VALIDATION FUNCTIONS ---

    // Login Validations
    function validateLoginEmail() {
        if (loginEmail.value.trim() === '') {
            showError(loginEmail, 'Email cannot be blank.');
            return false;
        } else if (!emailRegex.test(loginEmail.value.trim())) {
            showError(loginEmail, 'Invalid email format.');
            return false;
        }
        clearError(loginEmail);
        return true;
    }

    function validateLoginPassword() {
        if (loginPassword.value === '') {
            showError(loginPassword, 'Password cannot be blank.');
            return false;
        }
        clearError(loginPassword);
        return true;
    }

    // Registration Validations
    function validateFullName() {
        const val = fullName.value.trim();
        if (val === '') {
            showError(fullName, 'Full name cannot be blank.');
            return false;
        } else if (/\d/.test(val)) {
            showError(fullName, 'Full name cannot contain numbers.');
            return false;
        }
        clearError(fullName);
        return true;
    }

    function validateDateOfBirth() {
        if (dob.value === '') {
            showError(dob, 'Date of Birth is required.');
            return false;
        }
        
        const selectedDate = new Date(dob.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Ignore time part

        if (selectedDate > today) {
            showError(dob, 'Date of Birth cannot be in the future.');
            return false;
        }

        // Calculate age
        let age = today.getFullYear() - selectedDate.getFullYear();
        const m = today.getMonth() - selectedDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < selectedDate.getDate())) {
            age--; // Subtract 1 if the birth month/day hasn't happened yet this year
        }

        if (age < 15) {
            showError(dob, 'Registration requires you to be at least 15 years old.');
            return false;
        }

        clearError(dob);
        return true;
    }

    function validateBloodGroup() {
        if (bloodGroup.value.trim() === '') {
            showError(bloodGroup, 'Blood group cannot be blank.');
            return false;
        }
        clearError(bloodGroup);
        return true;
    }

    function validatePhoneNumber() {
        if (phoneNumber.value.trim() === '') {
            showError(phoneNumber, 'Phone number is required.');
            return false;
        } else if (!phoneRegex.test(phoneNumber.value.trim())) {
            showError(phoneNumber, 'Phone number must be exactly 10 digits.');
            return false;
        }
        clearError(phoneNumber);
        return true;
    }

    function validateAdmissionNo() {
        if (admissionNo.value.trim() === '') {
            showError(admissionNo, 'Admission number is required.');
            return false;
        } else if (!admissionRegex.test(admissionNo.value.trim())) {
            showError(admissionNo, 'Format must be PCE##_### (e.g., PCE24_001).');
            return false;
        }
        clearError(admissionNo);
        return true;
    }

    function validateAdmissionYear() {
        const year = parseInt(admissionYear.value, 10);
        if (admissionYear.value.trim() === '') {
            showError(admissionYear, 'Admission year is required.');
            return false;
        } else if (isNaN(year) || year < 1990 || year > 2026) {
            showError(admissionYear, 'Year must be strictly between 1990 and 2026.');
            return false;
        }
        clearError(admissionYear);
        return true;
    }

    function validateRegEmail() {
        if (email.value.trim() === '') {
            showError(email, 'Email cannot be blank.');
            return false;
        } else if (!emailRegex.test(email.value.trim())) {
            showError(email, 'Invalid email format.');
            return false;
        }
        clearError(email);
        return true;
    }

    function validateRegPassword() {
        const val = password.value;
        const msg = [];
        
        if (val === '') {
            showError(password, 'Password cannot be blank.');
            return false;
        }

        if (val.length < 8) msg.push('min 8 chars');
        if (!/[a-z]/.test(val)) msg.push('1 lowercase');
        if (!/[A-Z]/.test(val)) msg.push('1 uppercase');
        if (!/[0-9]/.test(val)) msg.push('1 number');
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(val)) msg.push('1 special symbol');

        if (msg.length > 0) {
            showError(password, 'Password needs: ' + msg.join(', '));
            return false;
        }

        clearError(password);
        
        // Also trigger confirm password if it has some value
        if (confirmPassword.value !== '') {
            validateConfirmPassword();
        }
        
        return true;
    }

    function validateConfirmPassword() {
        if (confirmPassword.value === '') {
            showError(confirmPassword, 'Confirm password cannot be blank.');
            return false;
        } else if (confirmPassword.value !== password.value) {
            showError(confirmPassword, 'Passwords do not match.');
            return false;
        }
        clearError(confirmPassword);
        return true;
    }

    function validateRole() {
        if (role.value === '') {
            showError(role, 'Please select a role.');
            return false;
        }
        clearError(role);
        return true;
    }

    // --- EVENT LISTENERS FOR REAL-TIME VALIDATION (input & focusout) ---
    
    // Login
    if (loginEmail) {
        loginEmail.addEventListener('input', validateLoginEmail);
        loginEmail.addEventListener('focusout', validateLoginEmail);
    }
    if (loginPassword) {
        loginPassword.addEventListener('input', validateLoginPassword);
        loginPassword.addEventListener('focusout', validateLoginPassword);
    }

    // Registration
    if (fullName) {
        fullName.addEventListener('input', validateFullName);
        fullName.addEventListener('focusout', validateFullName);
    }
    if (dob) {
        dob.addEventListener('input', validateDateOfBirth);
        dob.addEventListener('focusout', validateDateOfBirth);
    }
    if (bloodGroup) {
        bloodGroup.addEventListener('input', validateBloodGroup);
        bloodGroup.addEventListener('focusout', validateBloodGroup);
    }
    if (phoneNumber) {
        phoneNumber.addEventListener('input', validatePhoneNumber);
        phoneNumber.addEventListener('focusout', validatePhoneNumber);
    }
    if (admissionNo) {
        admissionNo.addEventListener('input', validateAdmissionNo);
        admissionNo.addEventListener('focusout', validateAdmissionNo);
    }
    if (admissionYear) {
        admissionYear.addEventListener('input', validateAdmissionYear);
        admissionYear.addEventListener('focusout', validateAdmissionYear);
    }
    if (email) {
        email.addEventListener('input', validateRegEmail);
        email.addEventListener('focusout', validateRegEmail);
    }
    if (password) {
        password.addEventListener('input', validateRegPassword);
        password.addEventListener('focusout', validateRegPassword);
    }
    if (confirmPassword) {
        confirmPassword.addEventListener('input', validateConfirmPassword);
        confirmPassword.addEventListener('focusout', validateConfirmPassword);
    }
    if (role) {
        role.addEventListener('change', validateRole);
        role.addEventListener('focusout', validateRole);
    }

    // --- FORM SUBMISSION PREVENT DEFAULT ---

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const isEmailValid = validateLoginEmail();
            const isPasswordValid = validateLoginPassword();

            if (!isEmailValid || !isPasswordValid) {
                e.preventDefault(); // Stop form submission
            }
        });
    }

    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            const isFullNameValid = validateFullName();
            const isDobValid = validateDateOfBirth();
            const isBloodGroupValid = validateBloodGroup();
            const isPhoneNumberValid = validatePhoneNumber();
            const isAdmissionNoValid = validateAdmissionNo();
            const isAdmissionYearValid = validateAdmissionYear();
            const isEmailValid = validateRegEmail();
            const isPasswordValid = validateRegPassword();
            const isConfirmPasswordValid = validateConfirmPassword();
            const isRoleValid = validateRole();

            // Check if ANY validation failed
            if (!isFullNameValid || !isDobValid || !isBloodGroupValid || !isPhoneNumberValid || 
                !isAdmissionNoValid || !isAdmissionYearValid || !isEmailValid || !isPasswordValid || 
                !isConfirmPasswordValid || !isRoleValid) {
                e.preventDefault(); // Stop form submission
            }
        });
    }
});
