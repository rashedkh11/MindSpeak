<?php
// Security headers
//header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

require 'db.php';
if (!defined('SECURE_ACCESS')) die("Direct access denied.");

// Security Settings
define('OTP_EXPIRY_MINUTES', 5);
define('MAX_ATTEMPTS', 5);
define('BACKUP_CODE_COUNT', 5);
define('RESEND_LIMIT', 3); // Max OTP resends per hour
define('IP_BAN_ATTEMPTS', 10); // Max global attempts per IP

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);
define('SMTP_USERNAME', 'ehdaajaafreh@gmail.com');  
define('SMTP_PASSWORD', 'rppa fhhl erep hnlg');    
define('SMTP_FROM_EMAIL', 'mindspeak@gmail.com');
define('SMTP_FROM_NAME', 'MindSpeak');

// Secure Session Settings - Only start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,    // Ensure you're using HTTPS
        'cookie_samesite' => 'Strict',
        'gc_maxlifetime' => 1800
    ]);
    session_regenerate_id(true);
}