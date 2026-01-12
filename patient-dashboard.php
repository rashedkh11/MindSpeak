<?php
session_start();
include('db.php'); 
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
			<!-- Header -->
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
								<div class="card-body pt-0">
								
									<!-- Tab Menu -->
									<nav class="user-tabs mb-4">
										<ul class="nav nav-tabs nav-tabs-bottom nav-justified">
											<li class="nav-item">
												<a class="nav-link active" href="#pat-appointments" data-toggle="tab">Appointments</a>
											</li>
											<li class="nav-item">
												<a class="nav-link" href="#patient-sessions" data-toggle="tab">sessions</a>
											</li>
										
										</ul>
									</nav>
									<!-- /Tab Menu -->
									
									<!-- Tab Content -->
									<div class="tab-content pt-0">
										
									<div id="pat-appointments" class="tab-pane fade show active">
    <div class="card card-table mb-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Appt Date</th>
                            <th>Booking Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <h2 class="table-avatar">
                                    <!-- Display Doctor Image -->
                                    <a href="doctor-profile.php?doctor_id=<?php echo $row['doctor_id']; ?>" class="avatar avatar-sm mr-2">
                                        <?php
                                            // Check if user has an image stored in user_image column
                                            if (!empty($image)) {
                                                // If image path is found and file exists
                                                echo '<img class="avatar-img rounded-circle" src="' . $image. '" alt="Doctor Image">';
                                            } else {
                                                // Fallback image if user doesn't have one
                                                echo '<img class="avatar-img rounded-circle" src="profile" alt="Doctor Image">';
                                            }
                                        ?>
                                    </a>
                                    <a href="doctor-profile.php?doctor_id=<?php echo $row['doctor_id']; ?>"><?php echo $row['doctor_name']; ?></a>
                                </h2>
                            </td>
                            <td><?php echo $row['appointment_date']; ?> <span class="d-block text-info"><?php echo date('h:i A', strtotime($row['appointment_date'])); ?></span></td>
                            <td><?php echo $row['booking_date']; ?></td>
                            <td><?php echo isset($row['amount']) ? '$' . number_format($row['amount'], 2) : 'N/A'; ?></td>
                            <td><span class="badge badge-pill <?php echo getStatusClass($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                            <td class="text-right">
                                <div class="table-action">
                                    <a href="javascript:void(0);" class="btn btn-sm bg-primary-light">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                    <a href="appointment-details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm bg-info-light">
                                        <i class="far fa-eye"></i> View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<?php
// Function to determine the CSS class for appointment status
function getStatusClass($status) {
    switch ($status) {
        case 'Confirmed':
            return 'bg-success-light';
        case 'Cancelled':
            return 'bg-danger-light';
        case 'Pending':
            return 'bg-warning-light';
        default:
            return 'bg-secondary-light';
    }
}

?>







										
										<!-- patient_sessions Tab -->
										<div id="patient-sessions" class="tab-pane fade ">
    <div class="card card-table mb-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-center mb-0">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Session Name</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result2->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <h2 class="table-avatar">
                                    <a href="doctor-profile.php?id=<?php echo $row['doctor_id']; ?>" class="avatar avatar-sm mr-2">
                                        <?php
                                            if (!empty($row['image']) && file_exists($row['image'])) {
                                                echo '<img class="avatar-img rounded-circle" src="' . $row['image'] . '" alt="Doctor Image">';
                                            } else {
                                                echo '<img class="avatar-img rounded-circle" src="' . $row['profile_image'] . '" alt="Doctor Image">';
                                            }
                                        ?>
                                    </a>
                                    <a href="doctor-profile.php?id=<?php echo $row['doctor_id']; ?>">
                                        Dr. <?php echo $row['fname'] . ' ' . $row['lname']; ?>
                                    </a>
                                </h2>
                            </td>
                            <td> <a href="singles.php?id=<?php echo $row['session_id']; ?>">
                                        <?php echo $row['session_name'] ?>
                                    </a>
								</td>
                            <td><?php echo $row['session_type']; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['date_time'])); ?> 
                                <span class="d-block text-info"><?php echo date('h:i A', strtotime($row['date_time'])); ?></span>
                            </td>
                            <td><?php echo $row['duration']; ?> mins</td>
                            <td><?php echo isset($row['price']) ? '$' . number_format($row['price'], 2) : 'N/A'; ?></td>
                            <td><span class="badge badge-pill <?php echo getStatusClass2($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                          
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
function getStatusClass2($status) {
	switch ($status) {
        case 'accepted':
            return 'bg-success-light';
        case 'cancelled':
            return 'bg-danger-light';
        case 'pending':
            return 'bg-warning-light';
        default:
            return 'bg-secondary-light';
    }
    
}
?>


										<!-- /patient_sessions Tab -->
											
									
										
										
									</div>
									<!-- Tab Content -->
									
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

<!-- doccure/patient-dashboard.html  30 Nov 2019 04:12:16 GMT -->
</html>