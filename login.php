<?php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");

session_start();
// Initialize all session variables to prevent undefined index warnings
$_SESSION['pending_user_id'] = $_SESSION['pending_user_id'] ?? null;
$_SESSION['pending_username'] = $_SESSION['pending_username'] ?? null;
$_SESSION['pending_roll'] = $_SESSION['pending_roll'] ?? null;
$_SESSION['pending_profile_img'] = $_SESSION['pending_profile_img'] ?? null;
include 'db.php'; 
require_once 'csrf.php';
require_once 'Logger.php'; // Include Logger class
$logger = new Logger();
$error_message = "";
 $ip = $_SERVER['REMOTE_ADDR'];


if ($_SERVER["REQUEST_METHOD"] == "POST")
 {
    //checkCSRFToken($_POST['csrf_token']); // Check the token

    if (!isset($conn)) {
         $logger->log(null, "Database connection failed during login attempt", 'CRITICAL', ['ip' => $ip]);
        die("Database connection failed!");
    }
    // 2. Check if account is locked
    $lockout_check = $conn->prepare("SELECT * FROM account_lockouts 
                                   WHERE (username = ? OR ip_address = ?) 
                                   AND lockout_time > UNIX_TIMESTAMP()");
    $lockout_check->bind_param("ss", $_POST['email_or_username'], $ip);
    $lockout_check->execute();
    
    if ($lockout = $lockout_check->get_result()->fetch_assoc()) {
        $remaining = $lockout['lockout_time'] - time();
        $error_message = "Account locked. Try again in " . ceil($remaining / 60) . " minutes.";
        $logger->log(null, "Attempt to access locked account: " . $_POST['email_or_username'], 'WARNING', [
            'ip' => $ip,
            'remaining_time' => $remaining
        ]);
    } else {
    $email_or_username = trim($_POST['email_or_username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1";
$stmt = $conn->prepare($query);
    mysqli_stmt_bind_param($stmt, "ss", $email_or_username, $email_or_username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {

   // Verify the password
if (password_verify($password, $user['pass'])) {
    $logger->log($user['id'], "Successful login", 'INFO', [
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);

    // Successful login - clear attempts
    $conn->query("DELETE FROM login_attempts WHERE username = '".$conn->real_escape_string($email_or_username)."' OR ip_address = '$ip'");
    $conn->query("DELETE FROM account_lockouts WHERE username = '".$conn->real_escape_string($email_or_username)."' OR ip_address = '$ip'");

    // Check if 2FA is enabled for this user
    $stmt = $conn->prepare("SELECT otp_enabled FROM users WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userPref = $result->fetch_assoc();

    // Store user data in session
    $_SESSION['pending_user_id'] = $user['id'];
    $_SESSION['pending_username'] = $user['username'];
    $_SESSION['pending_roll'] = $user['ROLL'];
    $_SESSION['pending_profile_img'] = $user['profile_image'];

    if ($userPref['otp_enabled']) {
        // Redirect to OTP verification
        header("Location: auth/send_otp.php");
    } else {
        // Skip OTP and log in directly
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['roll'] = $user['ROLL'];
        $_SESSION['profile_image'] = $user['profile_image'];
        header('Location: patient-dashboard.php');
    }
    exit();
}
    else {
          // Log failed attempt
                $logger->log(null, "Failed login attempt for " . $email_or_username, 'WARNING', [
                    'ip' => $ip,
                    'user_id' => $user['id']
                ]);
        // Delete old login attempts (older than 1 hour)
        $conn->query("DELETE FROM login_attempts WHERE attempt_time < UNIX_TIMESTAMP() - 3600");
        // Failed attempt
        $conn->query("INSERT INTO login_attempts (username, ip_address, attempt_time) 
                     VALUES ('".$conn->real_escape_string($email_or_username)."', '$ip', UNIX_TIMESTAMP())");
        
        // Check attempts
        $attempts = $conn->query("SELECT COUNT(*) as count FROM login_attempts 
                                WHERE (username = '".$conn->real_escape_string($email_or_username)."' OR ip_address = '$ip') 
                                AND attempt_time > UNIX_TIMESTAMP() - 3600")
                  ->fetch_assoc()['count'];

        if ($attempts >= 5) {
            $lockout_time = time() + 900; // 15 minutes
            $check_existing_lock = $conn->query("SELECT id FROM account_lockouts WHERE username = '".$conn->real_escape_string($email_or_username)."' OR ip_address = '$ip'");
            if ($check_existing_lock->num_rows === 0) {
                $conn->query("INSERT INTO account_lockouts (username, ip_address, lockout_time) 
                            VALUES ('".$conn->real_escape_string($email_or_username)."', '$ip', $lockout_time)");
            }
            $error_message = "Too many failed attempts. Account locked for 15 minutes.";
            $logger->log(null, "Account locked for " . $email_or_username, 'SECURITY', [
                        'ip' => $ip,
                        'reason' => 'Too many failed attempts',
                        'lockout_time' => $lockout_time
                    ]);
        } else {
            $error_message = "🚨 Incorrect password. Attempts left: " . (5 - $attempts);
        }

    }
} else {
    $error_message = "🚨 Invalid email or username!";
      $logger->log(null, "Attempt to login with non-existent user: " . $email_or_username, 'WARNING', [
                'ip' => $ip
            ]);
}
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Mindspeak</title>
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
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
    
 
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">
					
					<div class="row">
						<div class="col-md-8 offset-md-2">
							
	

    <div class="account-content">
        <div class="row align-items-center justify-content-center">
            <div class="col-md-7 col-lg-6 login-left">
                <img src="assets/img/login-banner.png" class="img-fluid" alt="Login Image">    
            </div>
            <div class="col-md-12 col-lg-6 login-right">
                <div class="login-header">
                    <h3>Login</h3>
                </div>

                <!-- Display Error Message -->
                <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group form-focus">
                        <input type="text" name="email_or_username" class="form-control floating" required>
                        <label class="focus-label">Email or Username</label>
                    </div>
                    <div class="form-group form-focus">
                        <input type="password" name="password" class="form-control floating" required>
                        <label class="focus-label">Password</label>
                    </div>
                    <div class="text-right">
                        <a class="forgot-link" href="forgot-password.php">Forgot Password?</a>
                    </div>

                    <button class="btn btn-primary btn-block btn-lg login-btn" type="submit">Login</button>
                    <div class="login-or">
                        <span class="or-line"></span>
                    </div>
                    <div class="text-center dont-have">Don’t have an account? <a href="register.php">Register</a></div>
                </form>
            </div>
        </div>
    </div>
	</div>
    </div>
	</div>
    </div>

			<!-- /Page Content -->
   
			<!-- Footer -->
			<?php include "footer.php"?>
			<!-- /Footer -->
		   
		</div>
		<!-- /Main Wrapper -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

</html>