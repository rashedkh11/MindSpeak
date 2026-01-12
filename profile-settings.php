<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "mindspeak1"; // Change to your DB name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in");
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM users LEFT JOIN patients ON users.id = patients.user_id WHERE users.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user2 = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['date_of_birth'];
    $blood_group = $_POST['blood_group'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];

    // Profile Image Upload
    $profile_image = $user2['profile_image'] ?? "assets/img/random.png";
    if (!empty($_FILES["profile_image"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types) && $_FILES["profile_image"]["size"] <= 2097152) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = $target_file;
            }
        }
    }

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET fname=?, lname=?, email=?, phone=?, profile_image=? WHERE id=?");
    $stmt->bind_param("sssssi", $fname, $lname, $email, $phone, $profile_image, $user_id);
    $stmt->execute();
    $stmt->close();

    // Check if patient exists
    $stmt = $conn->prepare("SELECT * FROM patients WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $patient_result = $stmt->get_result();
    $stmt->close();

    if ($patient_result->num_rows > 0) {
        // Update existing patient record
        $stmt = $conn->prepare("UPDATE patients SET date_of_birth=?, blood_group=?, address=?, city=?, state=?, zip_code=?, country=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $dob, $blood_group, $address, $city, $state, $zip_code, $country, $user_id);
    } else {
        // Insert new patient record
        $stmt = $conn->prepare("INSERT INTO patients (user_id, date_of_birth, blood_group, address, city, state, zip_code, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $dob, $blood_group, $address, $city, $state, $zip_code, $country);
    }
    $stmt->execute();
    $stmt->close();
}
?>

<?php include('get_user_profile.php'); ///////////////////////////////////////////////?>
<?php include('header.php'); ///////////////////////////////////////////////?>
			<!-- /Header -->
			
			
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">
					<div class="row">
					
						<!-- Profile Sidebar -->
						<?php include('profilesidebar.php'); ///////////////////////////////////////////////?>

						<!-- /Profile Sidebar -->
						
						<div class="col-md-7 col-lg-8 col-xl-9">
							<div class="card">
								<div class="card-body">
									
									<!-- Profile Settings Form -->
			<form action="settings.php" method="POST" enctype="multipart/form-data">
				<div class="row form-row">
					<!-- Profile Image Upload Section -->
					<div class="col-12 col-md-12">
						<div class="form-group">
							<div class="change-avatar">
								<div class="profile-img">
									<img src="<?= $user2['profile_image']; ?>" alt="User Image">
								</div>
								<div class="upload-img">
									<div class="change-photo-btn">
										<span><i class="fa fa-upload"></i> Upload Photo</span>
										<input type="file" name="profile_image" class="upload">
									</div>
									<small class="form-text text-muted">Allowed JPG, GIF or PNG. Max size of 2MB</small>
								</div>
							</div>
						</div>
					</div>

									<!-- HTML Section -->
				<div class="security-card">
					<h3>Two-Factor Authentication</h3>
					<?php if ($user['email_verified']): ?>
						<form method="post" action="/auth/disable_2fa.php">
							<button type="submit">Disable 2FA</button>
						</form>
					<?php else: ?>
						<a href="/auth/send_otp.php?setup=1" class="btn">Enable 2FA</a>
					<?php endif; ?>
				</div>
									<!-- Personal Info Section -->
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>First Name</label>
							<input type="text" class="form-control" name="fname" value="<?= $user2['fname']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Last Name</label>
							<input type="text" class="form-control" name="lname" value="<?= $user2['lname']; ?>" required>
						</div>
					</div>

					<!-- Date of Birth, Blood Group -->
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Date of Birth</label>
							<input type="text" class="form-control datetimepicker" name="dob" value="<?= $user2['date_of_birth']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Blood Group</label>
							<select class="form-control" name="blood_group" required>
								<option <?= ($user2['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
								<option <?= ($user2['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
								<option <?= ($user2['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
								<option <?= ($user2['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
								<option <?= ($user2['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
								<option <?= ($user2['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
								<option <?= ($user2['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
								<option <?= ($user2['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
							</select>
						</div>
					</div>

					<!-- Contact Info Section -->
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Email ID</label>
							<input type="email" class="form-control" name="email" value="<?= $user2['email']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Mobile</label>
							<input type="text" class="form-control" name="phone" value="<?= $user2['phone']; ?>" required>
						</div>
					</div>

					<!-- Address Section -->
					<div class="col-12">
						<div class="form-group">
							<label>Address</label>
							<input type="text" class="form-control" name="address" value="<?= $user2['address']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>City</label>
							<input type="text" class="form-control" name="city" value="<?= $user2['city']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>State</label>
							<input type="text" class="form-control" name="state" value="<?= $user2['state']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Zip Code</label>
							<input type="text" class="form-control" name="zip_code" value="<?= $user2['zip_code']; ?>" required>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-group">
							<label>Country</label>
							<input type="text" class="form-control" name="country" value="<?= $user2['country']; ?>" required>
						</div>
					</div>

				</div>

				<div class="submit-section">
					<button type="submit" class="btn btn-primary submit-btn">Save Changes</button>
				</div>
			</form>
									<!-- /Profile Settings Form -->
									
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
		
		<!-- Select2 JS -->
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		
		<!-- Datetimepicker JS -->
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/profile-settings.html  30 Nov 2019 04:12:18 GMT -->
</html>