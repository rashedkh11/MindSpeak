<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$doctor_id = $_SESSION['user_id']; // Get doctor ID from session

$conn = new mysqli("localhost", "root", "", "mindspeak1");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch assigned patients for this doctor
$sql = "SELECT u.id, u.fname, u.lname, u.email, u.phone, u.profile_image, pd.status 
        FROM priv_doctors pd
        JOIN users u ON pd.patient_id = u.id
        WHERE pd.doctor_id = ? AND pd.status = 'pending'";

$stmt = $conn->prepare($sql);
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
							<div class="appointments">
							
									
								<!-- /Appointment List -->
	

							<div class="appointment-lists">
								<?php if ($result->num_rows > 0) { 
								while ($row = $result->fetch_assoc()) { ?>
								<div class="appointment-list">
								<div class="profile-info-widget">
									<a href="patient-profile.php?id=<?= $row['id'] ?>" class="booking-doc-img">
										<img src="<?= $row['profile_image'] ?>" alt="User Image">
									</a>
									<div class="profile-det-info">
										<h3><a href="patient-profile.php?id=<?= $row['id'] ?>"><?= $row['fname'] . " " . $row['lname'] ?></a></h3>
										<div class="patient-details">
											<h5><i class="fas fa-envelope"></i> <?= $row['email'] ?></h5>
											<h5><i class="fas fa-phone"></i> <?= $row['phone'] ?></h5>
											<h5><i class="far fa-clock"></i> Assigned on: <?= date('d M Y', strtotime($row['assigned_date'])) ?></h5>
											<h5>Status: <span class="badge badge-<?php 
												echo ($row['status'] == 'accepted') ? 'success' : (($row['status'] == 'pending') ? 'warning' : 'danger'); ?>">
												<?= ucfirst($row['status']) ?>
											</span></h5>
										</div>
									</div>
								</div>
								<div class="appointment-action">
									<form method="POST">
										<input type="hidden" name="patient_id" value="<?= $row['id'] ?>">
										<input type="hidden" name="csrf_token" value="<?php require_once 'csrf_helper.php'; echo getCSRFToken(); ?>">

										<button type="submit" name="action" value="accept" class="btn btn-sm bg-success-light">
											<i class="fas fa-check"></i> Accept
										</button>
										<button type="submit" name="action" value="reject" class="btn btn-sm bg-danger-light">
											<i class="fas fa-times"></i> cancel
										</button>
									</form>
								</div>
							</div>
						        <?php 
} 
    } else {
        echo "<p>No assigned patients.</p>";
    } ?>
</div>
								<!-- Appointment List -->

<?php
// Handle Accept or Reject action
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'], $_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];
    $status = ($_POST['action'] == "accept") ? "accepted" : "cancelled";

    $update_sql = "UPDATE priv_doctors SET status = ? WHERE doctor_id = ? AND patient_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $status, $doctor_id, $patient_id);
    
    if ($update_stmt->execute()) {
        echo "";
    } else {
        echo "<script>alert('Error updating status');</script>";
    }
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
		
		<!-- Appointment Details Modal -->
		<div class="modal fade custom-modal" id="appt_details">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Appointment Details</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<ul class="info-details">
							<li>
								<div class="details-header">
									<div class="row">
										<div class="col-md-6">
											<span class="title">#APT0001</span>
											<span class="text">21 Oct 2019 10:00 AM</span>
										</div>
										<div class="col-md-6">
											<div class="text-right">
												<button type="button" class="btn bg-success-light btn-sm" id="topup_status">Completed</button>
											</div>
										</div>
									</div>
								</div>
							</li>
							<li>
								<span class="title">Status:</span>
								<span class="text">Completed</span>
							</li>
							<li>
								<span class="title">Confirm Date:</span>
								<span class="text">29 Jun 2019</span>
							</li>
							<li>
								<span class="title">Paid Amount</span>
								<span class="text">$450</span>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<!-- /Appointment Details Modal -->
	  
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

<!-- doccure/appointments.html  30 Nov 2019 04:12:09 GMT -->
</html>