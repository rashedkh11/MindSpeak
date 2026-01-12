<?php
require __DIR__ . '/db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/config.php';
require_once 'Logger.php'; // Include Logger class
$logger = new Logger();
$ip = $_SERVER['REMOTE_ADDR'];

// Verify OTP or backup code was successful
if (!isset($_SESSION['pending_user_id']) || $_SESSION['pending_action'] !== 'password_reset') {
    header("Location: ../login.php");
    exit();
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if ($new_password !== $confirm_password) {
        $error = "Passwords don't match";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
        $error = "Password must be at least 8 characters with uppercase, lowercase and number";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['pending_user_id']);
        
        if ($stmt->execute()) {
            // Log the password change
            $logger->log($_SESSION['pending_user_id'], "Password reset successful", 'INFO', ['ip' => $ip]);
            
            // Clear pending session
            unset($_SESSION['pending_user_id']);
            unset($_SESSION['pending_action']);
            
            // Set success message
            $_SESSION['success_message'] = "Password updated successfully. Please login with your new password.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
            $logger->log($_SESSION['pending_user_id'], "Password reset failed", 'ERROR', ['ip' => $ip, 'error' => $stmt->error]);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <!-- Same header as forgot-password.php -->
</head>
<body>
    <div class="main-wrapper">
        <!-- Same header/navigation -->
        
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="account-content">
                            <div class="row align-items-center justify-content-center">
                                <div class="col-md-12 col-lg-6 login-right">
                                    <div class="login-header">
                                        <h3>Reset Your Password</h3>
                                    </div>
                                    
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                    <?php endif; ?>
                                    
                                    <form method="POST">
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" name="new_password" class="form-control" required 
                                                   autocomplete="new-password">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input type="password" name="confirm_password" class="form-control" required 
                                                   autocomplete="new-password">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">Update Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
    </div>
</body>
</html>