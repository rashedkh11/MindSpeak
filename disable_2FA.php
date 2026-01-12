<?php
session_start();
require 'db.php';
define('SECURE_ACCESS', true);
require 'config.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enable_2fa = isset($_POST['enable_2fa']) ? 1 : 0;
    
    // Update user preference
    $stmt = $conn->prepare("UPDATE users SET otp_enabled = ? WHERE id = ?");
    $stmt->bind_param("ii", $enable_2fa, $user_id);
    
    if ($stmt->execute()) {
        $success = "2FA settings updated successfully!";
    } else {
        $error = "Failed to update settings. Please try again.";
    }
}

// Get current 2FA status
$stmt = $conn->prepare("SELECT otp_enabled FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$otp_enabled = $user['otp_enabled'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Two-Factor Authentication</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-wrapper">
        <div class="content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Two-Factor Authentication</h4>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="enable2fa" 
                                               name="enable_2fa" <?= $otp_enabled ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="enable2fa">
                                            Enable Two-Factor Authentication
                                        </label>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">Save Settings</button>
                                    </div>
                                </form>
                                
                                <div class="alert alert-info mt-3">
                                    <p><strong>What is Two-Factor Authentication?</strong></p>
                                    <p>When enabled, you'll need to enter a verification code sent to your email in addition to your password when logging in.</p>
                                    <p class="mb-0"><strong>Note:</strong> For security reasons, you may be required to verify your identity when changing this setting.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>