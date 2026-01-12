<?php
// Include database connection
include 'db.php';

// Start session
session_start();
$user_id = $_SESSION['user_id'] ; // Use logged-in user ID, fallback to 1



// Fetch user data from USERS table
$query_user = "SELECT * FROM USERS WHERE id = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

// Check if doctor exists in DOCTORS table
$query_doctor = "SELECT * FROM DOCTORS WHERE user_id = ?";
$stmt_doctor = $conn->prepare($query_doctor);
$stmt_doctor->bind_param("i", $user_id);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();
$doctor_data = $result_doctor->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User details
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
	$email = $_POST['email'];
    $gender = $_POST['gender'];
	$age = (int) $_POST['age'];
	$phone = (int)$_POST['phone'];

    // Doctor details
    $biography = $_POST['biography'];
    $clinic_name = $_POST['clinic_name'];
    $clinic_address = $_POST['clinic_address'];
    $services = $_POST['services'];
    $specialization = $_POST['specialization'];
    $pricing = (int)$_POST['pricing'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $date_of_birth = $_POST['date_of_birth'];

    // Update USERS table
    $query_update_user = "UPDATE USERS SET username = ?, fname = ?, mname = ?, lname = ?, email = ?, gender = ?, age = ?, phone = ? WHERE id = ?";
    $stmt_update_user = $conn->prepare($query_update_user);
	$stmt_update_user->bind_param("ssssssiss", $username, $fname, $mname, $lname, $email, $gender, $age, $phone, $user_id);
    $stmt_update_user->execute();

    if ($doctor_data) {
		// Update DOCTORS table
		$query_update_doctor = "UPDATE DOCTORS SET date_of_birth = ?, biography = ?, clinic_name = ?, clinic_address = ?, services = ?, specialization = ?, pricing = ?, city = ?, country = ?, address_line_1 = ?, address_line_2 = ? WHERE user_id = ?";
		$stmt_update_doctor = $conn->prepare($query_update_doctor);
		$stmt_update_doctor->bind_param("ssssssissssi", $date_of_birth, $biography, $clinic_name, $clinic_address, $services, $specialization, $pricing, $city, $country, $address1, $address2, $user_id);
		$stmt_update_doctor->execute();
	} else {
		// Insert into DOCTORS table
		$query_insert_doctor = "INSERT INTO DOCTORS (user_id, biography, clinic_name, clinic_address, services, specialization, pricing, city, country, address_line_1, address_line_2,date_of_birth) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt_insert_doctor = $conn->prepare($query_insert_doctor);
		$stmt_insert_doctor->bind_param("isssssisssss", $user_id, $biography, $clinic_name, $clinic_address, $services, $specialization, $pricing, $city, $country, $address1, $address2,$date_of_birth);
		$stmt_insert_doctor->execute();
	}

    // Redirect to refresh page
    header("Location: doctor_profile.php");
    exit();
}
?>



   

<?php include('get_user_doc.php'); ///////////////////////////////////////////////?>
			<!-- Header -->
<?php include('headerd.php'); ///////////////////////////////////////////////?>
			<!-- /Header -->
		
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">

					<div class="row">
							
							<!-- Profile Sidebar -->
							<?php include('profilesidebard.php'); ///////////////////////////////////////////////?>
						<!-- / Profile Sidebar -->
							<!-- /Profile Sidebar -->
							
						<div class="col-md-7 col-lg-8 col-xl-9">
						<form method="POST">
        <div class="col-md-7 col-lg-8 col-xl-9">

            <!-- Basic Information -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Basic Information</h4>
                    <div class="row form-row">
                        <div class="col-md-6">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" value="<?= $user_data['username'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?= $user_data['email'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>First Name</label>
                            <input type="text" class="form-control" name="fname" value="<?= $user_data['fname'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Middle Name</label>
                            <input type="text" class="form-control" name="mname" value="<?= $user_data['mname'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Last Name</label>
                            <input type="text" class="form-control" name="lname" value="<?= $user_data['lname'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= $user_data['phone'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?= $doctor_data['date_of_birth'] ?? '' ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Gender</label>
                            <select class="form-control" name="gender">
                                <option <?= ($user_data['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                                <option <?= ($user_data['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Age</label>
                            <input type="text" class="form-control" name="age" value="<?= $user_data['age'] ?? '' ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Doctor Information</h4>
                    <div class="row">
                        <div class="col-md-6"><label>Biography</label><textarea class="form-control" name="biography"><?= $doctor_data['biography'] ?? '' ?></textarea></div>
                        <div class="col-md-6"><label>Services</label><input type="text" class="form-control" name="services" value="<?= $doctor_data['services'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Specialization</label><input type="text" class="form-control" name="specialization" value="<?= $doctor_data['specialization'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Pricing</label><input type="text" class="form-control" name="pricing" value="<?= $doctor_data['pricing'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Clinic Name</label><input type="text" class="form-control" name="clinic_name" value="<?= $doctor_data['clinic_name'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Clinic Address</label><input type="text" class="form-control" name="clinic_address" value="<?= $doctor_data['clinic_address'] ?? '' ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Address Details -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Address Details</h4>
                    <div class="row">
                        <div class="col-md-6"><label>City</label><input type="text" class="form-control" name="city" value="<?= $doctor_data['city'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Country</label><input type="text" class="form-control" name="country" value="<?= $doctor_data['country'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Address Line 1</label><input type="text" class="form-control" name="address1" value="<?= $doctor_data['address_line_1'] ?? '' ?>"></div>
                        <div class="col-md-6"><label>Address Line 2</label><input type="text" class="form-control" name="address2" value="<?= $doctor_data['address_line_2'] ?? '' ?>"></div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="submit-section">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </form>

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
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Select2 JS -->
		<script src="assets/plugins/select2/js/select2.min.js"></script>
		
		<!-- Dropzone JS -->
		<script src="assets/plugins/dropzone/dropzone.min.js"></script>
		
		<!-- Bootstrap Tagsinput JS -->
		<script src="assets/plugins/bootstrap-tagsinput/js/bootstrap-tagsinput.js"></script>
		
		<!-- Profile Settings JS -->
		<script src="assets/js/profile-settings.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/doctor-profile-settings.html  30 Nov 2019 04:12:15 GMT -->
</html>