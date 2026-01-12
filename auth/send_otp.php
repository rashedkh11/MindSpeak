<?php
session_start(); // Add this at the top
require __DIR__ . '/../db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/../config.php';
require __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['pending_user_id'])) {
    die("Invalid request - No pending user session");
}
$action = $_GET['action'] ?? 'login'; // Default to 'login' if not specified
$_SESSION['pending_action'] = $action;

// Customize email based on action
if ($action === 'password_reset') {
    $subject = "Password Reset Verification Code";
    $message = "Your password reset code is: $code (Valid for ".OTP_EXPIRY_MINUTES." minutes)";
} else {
    $subject = "Login Verification Code";
    $message = "Your login verification code is: $code (Valid for ".OTP_EXPIRY_MINUTES." minutes)";
}

// Get user email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['pending_user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userEmail = $user['email'];

// Generate 6-digit code
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store in DB 
$stmt = $conn->prepare("UPDATE users SET 
    verification_code = ?,
    code_expires_at = ?,
    verification_attempts = 0
    WHERE id = ?");
$expiry = time() + (OTP_EXPIRY_MINUTES * 60);
$stmt->bind_param("sii", $code, $expiry, $_SESSION['pending_user_id']);
$stmt->execute();

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = SMTP_AUTH;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($userEmail);

    // Content
    $mail->isHTML(false); // Set to true if you want HTML content
    $mail->Subject = 'Your OTP Code';
    $mail->Body    = "Your verification code is: $code (Valid for ".OTP_EXPIRY_MINUTES." minutes)";

    $mail->send();
    header('Location: verify_otp.php');
    exit();
} catch (Exception $e) {
    die("Failed to send OTP. Error: {$mail->ErrorInfo}");
}