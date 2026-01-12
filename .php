<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get Patient ID from URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id == 0) {
    die("Patient ID is missing.");
}

// Fetch Patient Details
$query = "
    SELECT u.fname, u.lname, u.profile_image, u.phone, u.gender, p.date_of_birth, 
           p.city, p.country, p.blood_group
    FROM USERS u
    LEFT JOIN PATIENTS p ON u.id = p.user_id
    WHERE u.id = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error (Patient Query): " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Patient not found.");
}
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Doctor not logged in.");
}

$doctor_id = $_SESSION['user_id'];
// Calculate Age
$dob = new DateTime($patient['date_of_birth']);
$today = new DateTime();
$age = $today->diff($dob)->y;

// Fetch Last Booking
$query = "
    SELECT d.id AS doctor_id, d.fname AS doctor_name, d.profile_image AS doctor_image, 
           a.appointment_date, a.type , a.booking_date , a.amount , a.follow_up_date, a.status	, a.purpose	,a.time
    FROM APPOINTMENTS a
    JOIN USERS d ON a.doctor_id = d.id
    WHERE a.patient_id = ? AND a.doctor_id = ?
    ORDER BY a.appointment_date DESC
    LIMIT 2
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error (Booking Query): " . $conn->error);
}
$stmt->bind_param("ii", $patient_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$appointments = $bookings;

$stmt->close();
$query = "
    SELECT d.id AS doctor_id, d.fname AS doctor_name, d.profile_image AS doctor_image, 
           a.appointment_date, a.type , a.booking_date , a.amount , a.follow_up_date, a.status	, a.purpose	,a.time
    FROM APPOINTMENTS a
    JOIN USERS d ON a.doctor_id = d.id
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error (Booking Query): " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments2 = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();


?>

<?php include('header.php'); ///////////////////////////////////////////////?>


			<!-- /Header -->
			
		
			<div class="content">
				<div class="container-fluid">

					<div class="row">
						<div class="col-md-5 col-lg-4 col-xl-3 theiaStickySidebar dct-dashbd-lft">
						
							<!-- Profile Widget -->
							
<!-- HTML Output -->
<div class="card widget-profile pat-widget-profile">
    <div class="card-body">
        <div class="pro-widget-content">
            <div class="profile-info-widget">
                <a href="#" class="booking-doc-img">
                    <img src="<?= htmlspecialchars($patient['profile_image']) ?>" alt="User Image">
                </a>
                <div class="profile-det-info">
                    <h3><?= htmlspecialchars($patient['fname'] . " " . $patient['lname']) ?></h3>
                    <div class="patient-details">
                        <h5><b>Patient ID :</b> PT<?= str_pad($patient_id, 4, "0", STR_PAD_LEFT) ?></h5>
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($patient['city']) ?>, <?= htmlspecialchars($patient['country']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="patient-info">
            <ul>
                <li>Phone <span><?= htmlspecialchars($patient['phone']) ?></span></li>
                <li>Age <span><?= $age ?> Years, <?= ucfirst(htmlspecialchars($patient['gender'])) ?></span></li>
                <li>Blood Group <span><?= htmlspecialchars($patient['blood_group']) ?></span></li>
            </ul>
        </div>
    </div>
</div>

<!-- Last Booking -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Last Booking</h4>
    </div>
    <ul class="list-group list-group-flush">
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $booking): ?>
                <li class="list-group-item">
                    <div class="media align-items-center">
                        <div class="mr-3">
                            <img alt="Image placeholder" src="<?= htmlspecialchars($booking['doctor_image']) ?>" class="avatar rounded-circle">
                        </div>
                        <div class="media-body">
                            <h5 class="d-block mb-0">Dr. <?= htmlspecialchars($booking['doctor_name']) ?></h5>
                            <span class="d-block text-sm text-muted"><?= date("d M Y h:i A", strtotime($booking['appointment_date'])) ?></span>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item text-center">No recent bookings found.</li>
        <?php endif; ?>
    </ul>
</div>

							
						</div>

						<div class="col-md-7 col-lg-8 col-xl-9 dct-appoinment">
							<div class="card">
								<div class="card-body pt-0">
									<div class="user-tabs">
										<ul class="nav nav-tabs nav-tabs-bottom nav-justified flex-wrap">
										
											<li class="nav-item">
												<a class="nav-link active" href="#pat_appointments" data-toggle="tab">Appointments</a>
											</li>
											<li class="nav-item">
												<a class="nav-link" href="#all" data-toggle="tab"><span>All Appointments</span></a>
											</li>
										
											
										</ul>
									</div>
									<div class="tab-content">
										
										<!-- Appointment Tab -->
										<div id="pat_appointments" class="tab-pane fade show active">
    <div class="card card-table mb-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Appt Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Purpose</th>
                            <th>Booking Date</th>
                            <th>Amount</th>
                            <th>Follow Up</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($appointments) > 0) { ?>
                            <?php foreach ($appointments as $appt) { ?>
                                <tr>
                                    <td>
									<h2 class="table-avatar">
										<a href="doctor-profile.php?id=<?= $appt['doctor_id'] ?>" class="avatar avatar-sm mr-2">
											<img alt="Image placeholder" src="<?=htmlspecialchars($appt['doctor_image']) ?>" class="avatar rounded-circle">
										</a>
										<a href="doctor-profile.php?id=<?= $appt['doctor_id'] ?>">
											Dr. <?= htmlspecialchars($appt['doctor_name']) ?>
										</a>
									</h2>
                                    </td>
                                    <td><?= date("d M Y", strtotime($appt['appointment_date'])) ?></td>
                                    <td><span class="d-block text-info"><?= date("h:i A", strtotime($appt['time'])) ?></span></td>
                                    <td><?= htmlspecialchars($appt['type']) ?></td>
                                    <td><?= htmlspecialchars($appt['purpose']) ?></td>
                                    <td><?= date("d M Y", strtotime($appt['booking_date'])) ?></td>
                                    <td>$<?= htmlspecialchars($appt['amount']) ?></td>
                                    <td><?= date("d M Y", strtotime($appt['follow_up_date']??  ' ')) ?></td>
                                    <td>
                                        <span class="badge badge-pill bg-<?= ($appt['status'] == 'Confirmed') ? 'success' : 'danger' ?>-light">
                                            <?= htmlspecialchars($appt['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <!-- Add actions if needed -->
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="10" class="text-center">No Appointments Found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

										<!-- /Appointment Tab -->
										
										<!--  Tab -->
										<div class="tab-pane fade" id="all">
											
											<div class="card card-table mb-0">
												<div class="card-body">
													<div class="table-responsive">
														<table class="table table-hover table-center mb-0">
															<thead>
																<tr>
																	<th>Doctor</th>
																	<th>Appt Date</th>
																	<th>Time</th>
																	<th>Type</th>
																	<th>Purpose</th>
																	<th>Booking Date</th>
																	<th>Amount</th>
																	<th>Follow Up</th>
																	<th>Status</th>
																	<th></th>
																</tr>
															</thead>
															<tbody>
																<?php if (count($appointments2) > 0) { ?>
																	<?php foreach ($appointments2 as $appt2) { ?>
																		<tr>
																			<td>
																			<h2 class="table-avatar">
																				<a href="doctor-profile.php?id=<?= $appt2['doctor_id'] ?>" class="avatar avatar-sm mr-2">
																					<img alt="Image placeholder" src="<?=htmlspecialchars($appt2['doctor_image']) ?>" class="avatar rounded-circle">
																				</a>
																				<a href="doctor-profile.php?id=<?= $appt2['doctor_id'] ?>">
																					Dr. <?= htmlspecialchars($appt2['doctor_name']) ?>
																				</a>
																			</h2>
																			</td>
																			<td><?= date("d M Y", strtotime($appt2['appointment_date'])) ?></td>
																			<td><span class="d-block text-info"><?= date("h:i A", strtotime($appt['time'])) ?></span></td>
                                                                            <td><?= htmlspecialchars($appt2['type'] ?? '') ?></td>
																			<td><?= htmlspecialchars($appt2['purpose'] ?? ' ') ?></td>
																			<td><?= date("d M Y", strtotime($appt2['booking_date'])) ?></td>
																			<td>$<?= htmlspecialchars($appt2['amount']) ?></td>
																			<td><?= date("d M Y", strtotime($appt2['follow_up_date'] ?? ' ')) ?></td>
																			<td>
																				<span class="badge badge-pill bg-<?= ($appt2['status'] == 'Confirmed') ? 'success' : 'danger' ?>-light">
																					<?= htmlspecialchars($appt2['status']) ?>
																				</span>
																			</td>
																			<td class="text-right">
																				<!-- Add actions if needed -->
																			</td>
																		</tr>
																	<?php } ?>
																<?php } else { ?>
																	<tr>
																		<td colspan="10" class="text-center">No Appointments Found</td>
																	</tr>
																<?php } ?>
															</tbody>
														</table>
													</div>
												</div>
    										</div>
										</div>
										<!-- /Prescription Tab -->

										<!-- Medical Records Tab -->
										<?php
// Fetch patient's prediction data
$query = "
    SELECT ADHD_prob, Aspergers_prob, Depression_prob, OCD_prob, PTSD_prob, timestamp
    FROM model1
    WHERE user_id = ?
    ORDER BY timestamp DESC
    LIMIT 5
";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("SQL Error (Prediction Query): " . $conn->error);
}
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$predictions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

                                     
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
		
		<!-- Add Medical Records Modal -->
		<div class="modal fade custom-modal" id="add_medical_records">
			<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h3 class="modal-title">Medical Records</h3>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<form>					
						<div class="modal-body">
							<div class="form-group">
								<label>Date</label>
								<input type="text" class="form-control datetimepicker" value="31-10-2019">
							</div>
							<div class="form-group">
								<label>Description ( Optional )</label>
								<textarea class="form-control"></textarea>
							</div>
							<div class="form-group">
								<label>Upload File</label> 
								<input type="file" class="form-control">
							</div>	
							<div class="submit-section text-center">
								<button type="submit" class="btn btn-primary submit-btn">Submit</button>
								<button type="button" class="btn btn-secondary submit-btn" data-dismiss="modal">Cancel</button>							
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!-- /Add Medical Records Modal -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Datetimepicker JS -->
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/patient-profile.html  30 Nov 2019 04:12:13 GMT -->
</html>