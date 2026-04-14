<?php
// process_contact.php
// A simple handler for the contact form in index.html

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        echo "<script>alert('Error: All fields are required.'); window.location.href = 'index.html#contact';</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Invalid email format.'); window.location.href = 'index.html#contact';</script>";
        exit;
    }

    // In a real application, you would connect to db_connect.php and insert into a table,
    // or send an email. For now, we simulate success for the user.
    
    echo "<script>
        alert('TRANSMISSION SUCCESSUL: Thank you for contacting Pillai College. Your message has been received by the system.'); 
        window.location.href = 'index.html';
    </script>";
} else {
    // If accessed directly without POST
    header("Location: ../index.html");
    exit;
}
?>
