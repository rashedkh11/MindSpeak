<?php
session_start();
include ('db.php'); 
$patient_id = $_SESSION['user_id']; 

$sql = "SELECT appointments.id, doctors.username AS doctor_name, appointment_date, booking_date, amount, follow_up_date, status, doctors.id AS doctor_id, doctors.profile_image
        FROM appointments
        INNER JOIN users AS doctors ON appointments.doctor_id = doctors.id
        WHERE appointments.patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$sql2 = "SELECT 
            S.id AS session_id, 
            S.session_name, 
            S.session_type, 
            S.description, 
            S.location, 
            S.date_time, 
            S.duration, 
            S.price, 
            S.image, 
            PS.status, 
            D.id AS doctor_id, 
            D.fname, D.lname, 
            D.profile_image
        FROM patient_sessions PS
        JOIN SESSIONS S ON PS.session_id = S.id
        JOIN USERS D ON S.doctor_id = D.id
        WHERE PS.patient_id = ?";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $patient_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

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
						<!-- / Profile Sidebar -->
						
						<div class="col-md-7 col-lg-8 col-xl-9">
							<div class="card">
								<div class="card-body">
								
									<!-- Social Form -->
									<form>                                                                                           
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Facebook URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Twitter URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Instagram URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Pinterest URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Linkedin URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-md-12 col-lg-8">
												<div class="form-group">
													<label>Youtube URL</label>
													<input type="text" class="form-control">
												</div>
											</div>
										</div>
										<div class="submit-section">
											<button type="submit" class="btn btn-primary submit-btn">Save Changes</button>
										</div>
									</form>
									<!-- /Social Form -->
									
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
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

</html>