<?php
// Database connection settings
$servername = "localhost";
$username = "root";  // Your DB username
$password = "";  // Your DB password
$dbname = "MINDSPEAK1";  // Your DB name

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
    $pass = password_hash($_POST['pass'], algo: PASSWORD_BCRYPT);  // Hash password
    $gender = $_POST['gender']; 
    $age = $_POST['age'];
    $profile_pic = 'default.png'; // Default profile picture

    // Profile picture upload handling (if any file uploaded)
    if ($_FILES['profile_pic']['name']) {
        // Save uploaded file in 'uploads/' folder
        $profile_pic = 'uploads/' . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic)) {
            echo "Profile picture uploaded successfully.";
        } else {
            echo "Failed to upload profile picture.";
        }
    }

    // Check if fields are not empty
    if (!empty($username) && !empty($phone) && !empty($email) && !empty($gender) && !empty($age)) {
        // Prepare the SQL query to insert data into users table
        $sql = "INSERT INTO users (username, pass, phone, email, gender, age ,roll) 
                VALUES ('$username', '$pass', '$phone', '$email', '$gender', '$age','Doctor')";

        // Execute the query and check if it was successful
        if ($conn->query($sql) === TRUE) {
            echo "New user created successfully";
            header("Location: login.php");  // Redirect after successful registration
            exit;  // Make sure to call exit after header
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;  // Error message if insert fails
        }
    } else {
        echo "Please fill all required fields.";  // Handle empty fields
    }
}

// Close the database connection
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
                        <li class="active"><a href="index-2.php">Home</a></li>
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
    
 
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>	
			
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
											<h3>Patient Register <a href="register.php">Not a doctor?</a></h3>
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

                                    <input type="password" class="form-control" name="pass" required>
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
									<label>Age</label>
                                    <input type="number" class="form-control" name="age" required>
                                </div>
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
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/register.html  30 Nov 2019 04:12:20 GMT -->
</html>