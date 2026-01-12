<?php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");

session_start();
require_once 'db.php';
require_once 'Logger.php';
$logger = new Logger();
$error_message = "";
$success_message = "";
$ip = $_SERVER['REMOTE_ADDR'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
        $logger->log(null, "Invalid email format in password reset: $email", 'WARNING', ['ip' => $ip]);
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Set pending session for password reset
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['pending_username'] = $user['username'];
            $_SESSION['pending_email'] = $email;
            $_SESSION['pending_action'] = 'password_reset';
            
            // Log the request
            $logger->log($user['id'], "Password reset requested", 'INFO', ['ip' => $ip]);
            
            // Redirect to OTP verification
            header("Location: auth/send_otp.php?action=password_reset");
            exit();
        } else {
            $error_message = "Email not found in our system";
            $logger->log(null, "Password reset attempt for unknown email: $email", 'WARNING', ['ip' => $ip]);
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
			<!-- /Header -->
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">
					
					<div class="row">
						<div class="col-md-8 offset-md-2">
							
							<!-- Account Content -->
							<div class="account-content">
								<div class="row align-items-center justify-content-center">
									<div class="col-md-7 col-lg-6 login-left">
										<img src="assets/img/login-banner.png" class="img-fluid" alt="Login Banner">	
									</div>
									<div class="col-md-12 col-lg-6 login-right">
										<div class="login-header">
											<h3>Forgot Password?</h3>
											<p class="small text-muted">Enter your email to get a password reset link</p>
										</div>
										
									<!-- Forgot Password Form -->
		<form action="forgot-password.php" method="POST">
			<div class="form-group form-focus">
				<input type="email" class="form-control floating" name="email" required>
				<label class="focus-label">Email</label>
			</div>
			
			<?php if ($error_message): ?>
				<div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
			<?php endif; ?>
			
			<?php if ($success_message): ?>
				<div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
			<?php endif; ?>
			
			<div class="text-right">
				<a class="forgot-link" href="login.php">Remember your password?</a>
			</div>
			<button class="btn btn-primary btn-block btn-lg login-btn" type="submit">Reset Password</button>
		</form>
										<!-- /Forgot Password Form -->
										
									</div>
								</div>
							</div>
							<!-- /Account Content -->
							
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