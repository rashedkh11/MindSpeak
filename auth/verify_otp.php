<?php
session_start();
require __DIR__ . '/../db.php';
define('SECURE_ACCESS', true);
require __DIR__ . '/../config.php';

// Secure session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Validate verification session
if (!isset($_SESSION['pending_user_id'])) {
    die("Verification session expired. Please login again.");
}
// At the beginning of the file, add this check:
$stmt = $conn->prepare("SELECT otp_enabled FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['pending_user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user['otp_enabled']) {
    completeLogin();
    exit();
}

// Initialize variables
$error = null;
$success = null;
$ip = $_SERVER['REMOTE_ADDR'];

// Rate limiting check
$stmt = $pdo->prepare("SELECT COUNT(*) FROM auth_attempts WHERE ip_address = ? AND attempted_at > NOW() - INTERVAL 1 HOUR");
$stmt->execute([$ip]);
if ($stmt->fetchColumn() > IP_BAN_ATTEMPTS) {
    die("Too many attempts from your IP. Please try again in 1 hour.");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Handle backup code submission
if (isset($_POST['backup_code'])) {
    $backupCode = trim($_POST['backup_code']);
    
    // Add format validation first
    if (!preg_match('/^[a-zA-Z0-9]{8}$/', $backupCode)) {
        $error = "Invalid backup code format. Please enter exactly 8 alphanumeric characters.";
    } else {
        // Check if code was already used
        $stmt = $pdo->prepare("SELECT id FROM used_backup_codes 
                             WHERE user_id = ? AND code_hash = ?");
        $stmt->execute([
            $_SESSION['pending_user_id'],
            hash('sha256', $backupCode)
        ]);
        
        if ($stmt->fetch()) {
            $error = "This backup code has already been used.";
        } else {
            // Verify against active backup codes
            $stmt = $pdo->prepare("SELECT backup_codes FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['pending_user_id']]);
            $user = $stmt->fetch();
            
            $backupCodes = json_decode($user['backup_codes'] ?? '[]', true);
            $validCode = false;
            
            foreach ($backupCodes as $key => $code) {
                if (password_verify($backupCode, $code['hash'])) {
                    // Mark code as used
                    $pdo->prepare("INSERT INTO used_backup_codes (user_id, code_hash, ip_address) 
                                 VALUES (?, ?, ?)")
                       ->execute([
                           $_SESSION['pending_user_id'],
                           hash('sha256', $backupCode), // Store SHA256 hash instead of password_hash
                           $ip
                       ]);
                    
                    // Remove from active codes
                    unset($backupCodes[$key]);
                    $pdo->prepare("UPDATE users SET backup_codes = ? WHERE id = ?")
                       ->execute([
                           json_encode(array_values($backupCodes), JSON_UNESCAPED_SLASHES),
                           $_SESSION['pending_user_id']
                       ]);
                    
                    // Grant access
                    completeLogin();
                    $validCode = true;
                    break;
                }
            }
            
            if (!$validCode) {
                $error = "Invalid backup code. Please try again.";
                logFailedAttempt();
            }
        }
    }
}
    // Handle OTP submission
    elseif (isset($_POST['code'])) {
        $otp = trim($_POST['code']);
        
        $stmt = $pdo->prepare("SELECT verification_code, code_expires_at, verification_attempts 
                             FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['pending_user_id']]);
        $user = $stmt->fetch();
        
        // Validate OTP
        if (time() > $user['code_expires_at']) {
            $error = "Your OTP has expired. Please request a new one.";
        } elseif ($user['verification_attempts'] >= MAX_ATTEMPTS) {
            $error = "Too many incorrect attempts. Please use a backup code or try again later.";
        } elseif ($otp !== $user['verification_code']) {
            $error = "The OTP you entered is incorrect. Please try again.";
            $pdo->prepare("UPDATE users SET verification_attempts = verification_attempts + 1 
                          WHERE id = ?")
               ->execute([$_SESSION['pending_user_id']]);
            logFailedAttempt();
        } else {
            // OTP valid - complete login
            if ($_SESSION['pending_action'] === 'password_reset') {
    header('Location:/reset-password.php');
    exit();
}
            completeLogin();
        }
    }
}

function completeLogin() {
    global $pdo;
    
    // Clear OTP data
    $pdo->prepare("UPDATE users SET 
                  verification_code = NULL,
                  code_expires_at = NULL,
                  verification_attempts = 0
                  WHERE id = ?")
       ->execute([$_SESSION['pending_user_id']]);
    

    // Only set user_id if this was a login (not password reset)
    if ($_SESSION['pending_action'] === 'login') {
        $_SESSION['user_id'] = $_SESSION['pending_user_id'];
          $_SESSION['username'] = $_SESSION['pending_username'];
    $_SESSION['roll'] = $_SESSION['pending_roll'];
    $_SESSION['profile_image'] = $_SESSION['pending_profile_img'];
    }
    
    unset($_SESSION['pending_user_id']);
    unset($_SESSION['pending_action']); // Clean up
    
    // Redirect based on action
    $redirect = ($_SESSION['pending_action'] === 'password_reset') 
        ? '../reset-password.php'
        : '../patient-dashboard.php';
    
    header("Location: $redirect");
    exit();
}

function logFailedAttempt() {
    global $pdo, $ip;
    $pdo->prepare("INSERT INTO auth_attempts (user_id, ip_address) VALUES (?, ?)")
       ->execute([$_SESSION['pending_user_id'] ?? null, $ip]);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Mindspeak</title>
    <link rel="icon" href="../assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css"></head>
    <style>/* Verification Page Specific Styles */
.verify-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 120px);
    padding: 2rem;
    background-color: #f8f9fa;
}

.verify-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    padding: 2.5rem;
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
    border: 1px solid #e0e0e0;
}

.verify-title {
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-align: center;
}

.verify-subtitle {
    color: #7f8c8d;
    font-size: 1rem;
    text-align: center;
    margin-bottom: 2rem;
}

.info-text {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
    border-left: 4px solid #3498db;
}

.info-text p {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.info-text small {
    color: #7f8c8d;
    font-size: 0.85rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-control {
    height: 48px;
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.btn-verify {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    background-color:rgb(26, 206, 197);
    color: white;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 1rem;
    margin-bottom: 1.5rem;
}

.btn-verify:hover {
    background-color:rgb(192, 69, 21);
    transform: translateY(-1px);
}

.btn-secondary {
    display: block;
    text-align: center;
    color:rgb(238, 185, 107);
    background: none;
    border: none;
    padding: 0.5rem;
    margin-bottom: 1.5rem;
    cursor: pointer;
    font-weight: 500;
    text-decoration: none;
}

.btn-secondary:hover {
    text-decoration: underline;
}

.resend-link {
    text-align: center;
    color: #7f8c8d;
    margin-top: 1rem;
}

.resend-link a {
    color:rgb(54, 181, 190);
    text-decoration: none;
}

.resend-link a:hover {
    text-decoration: underline;
}

.error-message {
    background-color: #fee;
    color: #e74c3c;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #e74c3c;
}

.backup-info {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #f39c12;
}

.backup-info p {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.backup-info small {
    color: #7f8c8d;
    font-size: 0.85rem;
}

.generate-new {
    text-align: center;
    margin-top: 1rem;
}

.generate-new a {
    color:rgb(85, 157, 175);
    text-decoration: none;
}

.generate-new a:hover {
    text-decoration: underline;
}

.hidden {
    display: none;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .verify-card {
        padding: 1.5rem;
    }
    
    .verify-title {
        font-size: 1.5rem;
    }
}</style>
    <body>
    <div class="main-wrapper">
        <header class="header">
            <nav class="navbar navbar-expand-lg header-nav">
                <div class="navbar-header">
                   
                    <a href="index-2.php" class="navbar-brand logo">
                        <img src="assets/img/ms-logo.png" class="img-fluid" alt="Logo">
                    </a>
                </div>
                <div class="main-menu-wrapper">
                    <div class="menu-header">
                        <a href="index-2.html" class="menu-logo">
                            <img src="assets/img/ms-logo.png" class="img-fluid" alt="Logo">
                        </a>
                        <a id="menu_close" class="menu-close" href="javascript:void(0);">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <ul class="main-nav">
                        <li ><a href="index-2.php">Home</a></li>
                        <li><a href="search.php">Doctors</a></li>
                        <li><a href="sessions.php">Therapies</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li class="login-link"><a href="login.php">Login / Signup</a></li>
                    </ul>
                </div>
                <ul class="nav header-navbar-rht">
                    <li class="nav-item contact-item">
                        <div class="clinic-booking">
                            <a class="apt-btn" href="app0but.php">Book Appointment</a>
                        </div>
                    </li>
                   
                </ul>
            </nav>
        </header>
    </div>
    

    
<main>
    <div class="verify-container">
        <div class="verify-card">
            <div class="verify-header">
                <h1 class="verify-title">Verify Your Identity</h1>
                <p class="verify-subtitle">For your security, please verify it's you</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="info-text">
                <p><i class="fas fa-envelope"></i> We've sent a 6-digit verification code to your email.</p>
                <small>The code will expire in <?= OTP_EXPIRY_MINUTES ?> minutes.</small>
            </div>
            
            <form method="POST" id="otpForm" class="verify-form">
                <div class="form-group">
                    <label for="otpCode" class="sr-only">OTP Code</label>
                    <input type="text" 
                           id="otpCode" 
                           name="code" 
                           placeholder="Enter 6-digit OTP" 
                           class="form-control" 
                           required 
                           pattern="\d{6}" 
                           title="Please enter exactly 6 digits"
                           inputmode="numeric"
                           autocomplete="one-time-code">
                </div>
                <button type="submit" class="btn-verify">
                    <i class="fas fa-shield-alt"></i> Verify & Continue
                </button>
            </form>
            
          <a href="backup_codes.php" class="btn-secondary">
    <i class="fas fa-key"></i> Use Backup Code Instead
</a>
            
            <form method="POST" id="backupForm" class="verify-form hidden">
                <div class="backup-info">
                    <p><i class="fas fa-exclamation-triangle"></i> If you don't have access to your OTP, you can use one of your 8-digit backup codes.</p>
                    <small>Each backup code can only be used once.</small>
                </div>
                <div class="form-group">
                    <label for="backupCode" class="sr-only">Backup Code</label>
                    <input type="text" 
                           id="backupCode" 
                           name="backup_code" 
                           placeholder="Enter 8-digit backup code" 
                           class="form-control" 
                           required
                           pattern="[A-Za-z0-9]{8}"
                           title="Please enter exactly 8 alphanumeric characters">
                </div>
                <button type="submit" class="btn-verify">
                    <i class="fas fa-unlock-alt"></i> Verify Backup Code
                </button>
                <div class="generate-new">
                    <a href="backup_codes.php"><i class="fas fa-sync-alt"></i> Generate new backup codes</a>
                </div>
            </form>
            
            <div class="resend-link">
                Didn't receive a code? <a href="resend_otp.php"><i class="fas fa-redo"></i> Resend OTP</a>
            </div>
        </div>
    </div>
</main>
    
    <script>
       document.getElementById("toggleBackup").addEventListener("click", function(e) {
    e.preventDefault();
    const otpForm = document.getElementById("otpForm");
    const backupForm = document.getElementById("backupForm");
    const toggleLink = document.getElementById("toggleBackup");
    
    // Toggle visibility
    if (backupForm.classList.contains("hidden")) {
        backupForm.classList.remove("hidden");
        otpForm.classList.add("hidden");
        toggleLink.textContent = "Use OTP Instead";
        // Focus on backup code input when shown
        document.querySelector("#backupForm input[name='backup_code']").focus();
    } else {
        backupForm.classList.add("hidden");
        otpForm.classList.remove("hidden");
        toggleLink.textContent = "Use Backup Code Instead";
        // Focus on OTP input when shown
        document.querySelector("#otpForm input[name='code']").focus();
    }
});
    </script>
<!-- Footer -->
<style>
    .footer {
        background: #289976;
    }
</style>

<footer class="footer">
    <!-- Footer Top -->
    <div class="footer-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-md-8">
                    <!-- Footer Widget -->
                    <div class="footer-widget footer-about">
                        <div class="footer-logo">
                            <img src="assets/img/whitelogo.png">
                        </div>
                        <div class="footer-about-content">
                            <p>Your journey to better mental health starts here—insightful, supportive, and always listening.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Are You a Doctor? Section -->
                    <div class="footer-widget footer-menu">
                        <h2 class="footer-title">Are You a Doctor?</h2>
                        <ul>
                            <li><a href="doctor-register.html"><i class="fas fa-angle-double-right"></i> Join MindSpeak Doctors</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- For Clients Section -->
                    <div class="footer-widget footer-menu">
                        <h2 class="footer-title">For Clients</h2>
                        <ul>
                            <li><a href="search.html"><i class="fas fa-angle-double-right"></i> Search for Doctors</a></li>
                            <li><a href="login.html"><i class="fas fa-angle-double-right"></i> Login</a></li>
                            <li><a href="register.html"><i class="fas fa-angle-double-right"></i> Register</a></li>
                            <li><a href="appoinmtment-button.html"><i class="fas fa-angle-double-right"></i> Booking</a></li>
                            <li><a href="patient-dashboard.html"><i class="fas fa-angle-double-right"></i> Patient Dashboard</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <!-- Need Help Section -->
                    <div class="footer-widget footer-menu">
                        <h2 class="footer-title">Need Help!</h2>
                        <ul>
                            <li><a href="contact.html"><i class="fas fa-angle-double-right"></i> Contact us</a></li>
                            <li><a href="blog.html"><i class="fas fa-angle-double-right"></i> Library</a></li>
                            <li><a href="aboutus.html"><i class="fas fa-angle-double-right"></i> About Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <div class="container-fluid">
            <!-- Copyright -->
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 col-lg-6">
                        <!-- Copyright Menu -->
                        <div class="copyright-menu">
                            <ul class="policy-menu">
                                <li><a href="term-condition.php">Terms and Conditions</a></li>
                                <li><a href="privacy-policy.php">Policy</a></li>
                            </ul>
                        </div>
                        <!-- /Copyright Menu -->
                    </div>
                </div>
            </div>
            <!-- /Copyright -->
        </div>
    </div>
    <!-- /Footer Bottom -->
</footer>			<!-- /Footer -->
		   
		</div>
		<!-- /Main Wrapper -->
	  <script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById("toggleBackup");
    const otpForm = document.getElementById("otpForm");
    const backupForm = document.getElementById("backupForm");
    
    if (toggleBtn && otpForm && backupForm) {
        toggleBtn.addEventListener("click", function(e) {
            e.preventDefault();
            
            // Toggle visibility
            if (backupForm.classList.contains("hidden")) {
                backupForm.classList.remove("hidden");
                otpForm.classList.add("hidden");
                toggleBtn.innerHTML = '<i class="fas fa-mobile-alt"></i> Use OTP Instead';
                document.getElementById("backupCode").focus();
            } else {
                backupForm.classList.add("hidden");
                otpForm.classList.remove("hidden");
                toggleBtn.innerHTML = '<i class="fas fa-key"></i> Use Backup Code Instead';
                document.getElementById("otpCode").focus();
            }
        });
        
        // Auto-advance OTP input
        const otpInput = document.getElementById("otpCode");
        if (otpInput) {
            otpInput.addEventListener("input", function() {
                if (this.value.length === 6) {
                    document.getElementById("otpForm").submit();
                }
            });
        }
    }
});
</script>
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

</html>