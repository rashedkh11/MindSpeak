<?php
// Start session
session_start();

// Check if doctor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id']; // Logged-in doctor ID

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mindspeak1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch patients assigned to the doctor with status "pending"
$sql = "SELECT u.id, u.fname, u.lname, u.profile_image, u.phone, u.age, u.gender, 
               p.city, p.country, p.blood_group
        FROM priv_doctors pd
        JOIN users u ON pd.patient_id = u.id
        JOIN patients p ON pd.patient_id = p.user_id
        WHERE pd.doctor_id = ? AND pd.status = 'accepted'";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL Error: " . $conn->error);
}
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
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
						<div class="col-md-7 col-lg-8 col-xl-9">
						
							<div class="row row-grid">
								<?php
								if ($result->num_rows > 0) {
									while ($row = $result->fetch_assoc()) {
										echo '
										<div class="col-md-6 col-lg-4 col-xl-3">
												<div class="card widget-profile pat-widget-profile">
													<div class="card-body">
														<div class="pro-widget-content">
															<div class="profile-info-widget">
																<a href="#" class="booking-doc-img">
																	<img src="' . $row['profile_image'] . '" alt="User Image">
																</a>
																<div class="profile-det-info">
																	<h3>' . $row['fname'] . ' ' . $row['lname'] . '</h3>
																	<div class="patient-details">
																		<h5><b>Patient ID :</b> PT00' . $row['id'] . '</h5>
																		<h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> ' . $row['city'] . ', ' . $row['country'] . '</h5>
																	</div>
																</div>
															</div>
														</div>
														<div class="patient-info">
															<ul>
																<li>Phone <span>' . $row['phone'] . '</span></li>
																<li>Age <span>' . $row['age'] . ' Years, ' . $row['gender'] . '</span></li>
																<li>Blood Group <span>' . $row['blood_group'] . '</span></li>
															</ul>
														</div>
													</div>
												</div>
												
											</div>';
									}
								} else {
									echo '<div class="col-12"><p>No patients assigned to you.</p></div>';
								}
							
								
								?>
							
								
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
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/my-patients.html  30 Nov 2019 04:12:09 GMT -->
</html>