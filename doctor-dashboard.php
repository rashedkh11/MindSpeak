<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the logged-in doctor's ID
$doctor_id = $_SESSION['user_id'];

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

// Handle appointment status updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id'], $_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = ($_POST['action'] === "accept") ? "confirmed" : "cancelled";

    // Update appointment status
    $sql = "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $appointment_id, $doctor_id);

    if ($stmt->execute()) {
        $message = "Appointment " . ucfirst($new_status) . ".";
    } else {
        $message = "Error updating status: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch upcoming appointments
$sql = "SELECT a.id, p.fname, p.lname, a.appointment_date, a.amount, a.purpose, a.type, a.status ,p.profile_image ,a.patient_id
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ? AND a.status = 'pending'
        ORDER BY a.appointment_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$sql1 = "SELECT a.id, p.fname, p.lname, a.appointment_date, a.amount, a.purpose, a.type, a.status ,p.profile_image, a.patient_id
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        WHERE a.doctor_id = ? AND a.status = 'confirmed'
        ORDER BY a.appointment_date ASC";

$stmt = $conn->prepare($sql1);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result2 = $stmt->get_result();
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

							
							
							<div class="row">
								<div class="col-md-12">
									<h4 class="mb-4">Patient Appoinment</h4>
									<div class="appointment-tab">
									
										<!-- Appointment Tab -->
										<ul class="nav nav-tabs nav-tabs-solid nav-tabs-rounded">
											<li class="nav-item">
												<a class="nav-link active" href="#request-appointments" data-toggle="tab">requests</a>
											</li>
											<li class="nav-item">
												<a class="nav-link" href="#my-appointments" data-toggle="tab">My appointments</a>
											</li> 
										</ul>
										<!-- /Appointment Tab -->
										
										<div class="tab-content">
										
											<!-- Upcoming Appointment Tab -->
										
											<div class="tab-pane show active" id="request-appointments">

											<?php if (isset($message)) { ?>
        									<p style="color: green;"><?php echo $message; ?></p>
    												<?php } ?>

											<div class="card card-table mb-0">
												<div class="card-body">
													<div class="table-responsive">
														<table class="table table-hover table-center mb-0">
															<thead>
																<tr>
																	<th>Patient Name</th>
																	<th>Appt Date</th>
																	<th>Purpose</th>
																	<th>Type</th>
																	<th class="text-center">Paid Amount</th>
																</tr>
															</thead>
															<tbody>
																<?php
																if ($result->num_rows > 0) {
																	while ($row = $result->fetch_assoc()) {
																		echo '<tr>';
																		echo '<td><h2 class="table-avatar">
																				<a href="patient-profile.php?id=' . $row['patient_id'] . '" class="avatar avatar-sm mr-2">
																					<img class="avatar-img rounded-circle" src='.$row["profile_image"]. '  alt="User Image">
																				</a>
																				<a href="patient-profile.php?id=' . $row['patient_id'] . '">' . $row['fname'] . ' ' . $row['lname'] . '</a>
																			</h2></td>';
																		echo '<td>' . date('d M Y', strtotime($row['appointment_date'])) . 
																			' <span class="d-block text-info">' . date('h:i A', strtotime($row['appointment_date'])) . '</span></td>';
																		echo '<td>' . $row['purpose'] . '</td>';
																		echo '<td>' . $row['type'] . '</td>';
																		echo '<td class="text-center">$' . $row['amount'] . '</td>';
																		
																		echo '<td class="text-right">
																		<form method="POST" style="display: inline;">
																		<button type="submit" name="action" value="View" class="btn btn-sm bg-info-light"><i class="far fa-eye"></i> View</button>
																		</form>
																				<form method="POST" style="display: inline;">
																					<input type="hidden" name="appointment_id" value="' . $row['id'] . '">
																					<button type="submit" name="action" value="accept" class="btn btn-sm bg-success-light">
																						<i class="fas fa-check"></i> Accept
																					</button>
																				</form>

																				<form method="POST" style="display: inline;">
																					<input type="hidden" name="appointment_id" value="' . $row['id'] . '">
																					<button type="submit" name="action" value="cancel" class="btn btn-sm bg-danger-light">
																						<i class="fas fa-times"></i> Cancel
																					</button>
																				</form>
																			</td>';
																		echo '</tr>';
																	}
																} else {
																	echo '<tr><td colspan="7">No appointments request</td></tr>';
																}
																?>
															</tbody>
														</table>
													</div>
												</div>
											</div>
										</div>

										
                                            
                                        


												
									   
											<!-- My Appointments (Confirmed) Tab -->
												<div class="tab-pane" id="my-appointments">
													<div class="card card-table mb-0">
														<div class="card-body">
															<div class="table-responsive">
																<table class="table table-hover table-center mb-0">
																	<thead>
																		<tr>
																			<th>Patient Name</th>
																			<th>Appt Date</th>
																			<th>Purpose</th>
																			<th>Type</th>
																			<th class="text-center">Paid Amount</th>
																		</tr>
																	</thead>
																	<tbody>
																		<?php
																		if ($result2->num_rows > 0) {
																			while ($row = $result2->fetch_assoc()) {
																				echo '<tr>';
																				echo '<td><h2 class="table-avatar">
																						<a href="patient-profile.php?id=' . $row['patient_id'] . '" class="avatar avatar-sm mr-2">
																							<img class="avatar-img rounded-circle" src="' . $row["profile_image"] . '" alt="User Image">
																						</a>
																						<a href="patient-profile.php?id=' . $row['patient_id'] . '">' . $row['fname'] . ' ' . $row['lname'] . '</a>
																					</h2></td>';
																				echo '<td>' . date('d M Y', strtotime($row['appointment_date'])) . '</td>';
																				echo '<td>' . $row['purpose'] . '</td>';
																				echo '<td>' . $row['type'] . '</td>';
																				echo '<td class="text-center">$' . $row['amount'] . '</td>';
																				echo '<td class="text-right">
																		<form method="POST" style="display: inline;">
																		<button type="submit" name="action" value="View" class="btn btn-sm bg-info-light"><i class="far fa-eye"></i> View</button>
																		</form>
																				

																				<form method="POST" style="display: inline;">
																					<input type="hidden" name="appointment_id" value="' . $row['id'] . '">
																					<button type="submit" name="action" value="cancel" class="btn btn-sm bg-danger-light">
																						<i class="fas fa-times"></i> Cancel
																					</button>
																				</form>
																			</td>';
																				echo '</tr>';
																				
																			}
																		} else {
																			echo '<tr><td colspan="6">No confirmed appointments</td></tr>';
																		}
																		?>
																	</tbody>
																</table>
															</div>
														</div>
													</div>
												</div>
														
													</div>
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
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Circle Progress JS -->
		<script src="assets/js/circle-progress.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/doctor-dashboard.html  30 Nov 2019 04:12:09 GMT -->
</html>