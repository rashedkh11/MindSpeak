<?php
require __DIR__ . '/../db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/../config.php';

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Require login
if (empty($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// 1. Generate 5 codes (8 characters each)
$codes = array_map(fn() => bin2hex(random_bytes(4)), range(1, 5));

// 2. Store hashed versions in DB
$hashedCodes = array_map(fn($code) => [
    'code' => $code,
    'hash' => password_hash($code, PASSWORD_DEFAULT)
], $codes);

$stmt = $pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
$stmt->execute([
    json_encode($hashedCodes),
    $_SESSION['user_id']
]);

// 3. Store plain codes in session for one-time display
$_SESSION['backup_codes_display'] = $codes;

// 4. Redirect to the display page
header("Location:display_backup.php");
exit;
