<?php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
//require_once 'csrf_helper.php';
//checkCSRFToken($_POST['csrf_token']); // Check the token// Database connection settings
$servername = "localhost";
$username = "root";  
$password = "";  
$dbname = "MINDSPEAK1"; 
require_once 'Logger.php';
$logger = new Logger();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture form data
    $username = $_POST['username']; 
    $phone = $_POST['phone']; 
    $email = $_POST['email']; 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format");
}
$password = $_POST['password'];
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    die("Password must be at least 8 characters with uppercase, lowercase and number");
}
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $gender = $_POST['gender']; 
    $age = $_POST['age'];
    $profile_pic = 'default.png'; // Default profile picture

    // Profile picture upload handling (if any file uploaded)
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Verify file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['profile_pic']['tmp_name']);
    
    if (!in_array($mime, $allowed_types)) {
        die("Only JPG, PNG, and GIF images are allowed");
    }
    
    // Verify file size
    if ($_FILES['profile_pic']['size'] > $max_size) {
        die("File too large. Maximum 2MB allowed");
    }
    
    // Generate unique filename
    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $profile_pic = 'uploads/' . $filename;
    
    if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic)) {
        die("Failed to upload profile picture");
    }
}

    // Check if fields are not empty
    if (!empty($username) && !empty($phone) && !empty($email) && !empty($gender) && !empty($age)) {
    $role = 'patient'; // Create a variable for the role
    $stmt = $conn->prepare("INSERT INTO users (username, pass, phone, email, gender, age, ROLL) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $username, $pass, $phone, $email, $gender, $age, $role); 

        if ($stmt->execute()) {
           // successful registration
                $_SESSION['pending_user_id'] = $conn->insert_id;
                $_SESSION['pending_username'] = $username;
                $logger->log($conn->insert_id, "User registered", 'INFO', ['ip' => $_SERVER['REMOTE_ADDR']]);

                // Generate and send OTP
                require_once 'auth/send_otp.php'; 
                header('Location: auth/verify_otp.php?setup=1');
                exit();
        } else {
                 echo "Error: " . $stmt->error;
        }
    } else {
        echo "Please fill all required fields.";  // Handle empty fields
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Mindspeak</title>
    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">
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
								
							<!-- Register Content -->
							<div class="account-content">
								<div class="row align-items-center justify-content-center">
									<div class="col-md-7 col-lg-6 login-left">
										<img src="assets/img/login-banner.png" class="img-fluid" alt="Doccure Register">	
									</div>
									<div class="col-md-12 col-lg-6 login-right">
										<div class="login-header">
											<h3>Patient Register <a href="doctor-register.php">Are you a Doctor?</a></h3>
										</div>
										
										<!-- Register Form -->
										<div class="form-container">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
								<label>User Name</label>

                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="form-group">
								<label>Phone Number</label>

                                    <input type="text" class="form-control" name="phone" required>
                                </div>
                                <div class="form-group">
								<label>Email Address</label>

                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Create Password</label>
                                    <input type="password" class="form-control" name="password" required 
                                        autocomplete="new-password" id="password">
                                    <div class="password-strength">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar"></div>
                                        </div>
                                        <small class="text-muted">Password strength: <span class="strength-text">Weak</span></small>
                                    </div>
                                </div>


                                <div class="form-group">
								<label>gender</label>
                                    <select name="gender" required>
										<option value="Select gender">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="number" class="form-control" name="age" required>
                                    <label>Age</label>
                                </div>
                                <!--input type="hidden" name="csrf_token" value="</?php require_once 'csrf_helper.php'; echo getCSRFToken(); ?>">
-->
                                <button class="btn btn-primary btn-block btn-lg login-btn" type="submit">Signup</button>
                            </form>
                        </div>
										<!-- /Register Form -->
										
									</div>
								</div>
							</div>
							<!-- /Register Content -->
								
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
	  <script>
document.querySelector("form").addEventListener("submit", function (e) {
  const password = document.getElementById("password").value;
  const complexityRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

  if (!complexityRegex.test(password)) {
    alert("Password must be at least 8 characters and include uppercase, lowercase, and a number.");
    e.preventDefault(); // Stop form submission
  }
});
</script>

<script>
document.getElementById("password").addEventListener("input", function() {
    const strength = {
        0: "Very Weak",
        1: "Weak",
        2: "Medium", 
        3: "Strong",
        4: "Very Strong"
    };
    
    const val = this.value;
    let score = 0;
    
    // Length
    if (val.length >= 8) score++;
    if (val.length >= 12) score++;
    
    // Complexity
    if (/[A-Z]/.test(val)) score++;
    if (/\d/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    
    // Update UI
    const progress = document.querySelector(".progress-bar");
    progress.style.width = `${(score/4)*100}%`;
    progress.className = `progress-bar bg-${score < 2 ? "danger" : score < 4 ? "warning" : "success"}`;
    document.querySelector(".strength-text").textContent = strength[Math.min(score, 4)];
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