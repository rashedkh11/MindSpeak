<?php
require __DIR__ . '/../db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/../config.php';

// Rate limit resends
$stmt = $pdo->prepare("SELECT COUNT(*) FROM otp_resends WHERE user_id = ? AND created_at > NOW() - INTERVAL 1 HOUR");
$stmt->execute([$_SESSION['pending_user_id']]);
if ($stmt->fetchColumn() >= RESEND_LIMIT) {
    die("Too many resend requests");
}

// Log resend attempt
$pdo->prepare("INSERT INTO otp_resends (user_id, ip_address) VALUES (?, ?)")
    ->execute([$_SESSION['pending_user_id'], $_SERVER['REMOTE_ADDR']]);

// Reuse send_otp.php logic
header('Location: send_otp.php');