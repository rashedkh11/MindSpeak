<?php
// Include database connection
include 'db.php';
session_start();


// Check if the patient is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // Logged-in patient's user ID

// Query to fetch favorite doctors with rating
$query = "
    SELECT d.user_id, u.profile_image, u.fname, u.lname, d.specialization, d.city, d.country, d.pricing_min, d.pricing_max, d.rating
    FROM FAVOURITES f
    JOIN DOCTORS d ON f.doctor_id = d.user_id
    JOIN USERS u ON d.user_id = u.id
    WHERE f.user_id = ?
";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    // Print the error if the prepare fails
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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

						<!-- /Profile Sidebar -->
						


					<div class="col-md-7 col-lg-8 col-xl-9">
						<div class="row row-grid">
							<?php while ($doctor = $result->fetch_assoc()) { ?>
								<div class="col-md-6 col-lg-4 col-xl-3">
									<div class="profile-widget">
										<div class="doc-img">
											<a href="doctor-profile.php?doctor_id=<?php echo $doctor['user_id']; ?>">
												<img class="img-fluid" alt="User Image" src="<?php echo htmlspecialchars($doctor['profile_image']); ?>">
											</a>
										</div>
										<div class="pro-content">
											<h3 class="title">
												<a href="doctor-profile.php?doctor_id=<?php echo $doctor['user_id']; ?>">
													<?php echo htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']); ?>
												</a>
												<i class="fas fa-check-circle verified"></i>
											</h3>
											<p class="speciality"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
											
											<!-- Rating Display -->
											<div class="rating">
												<?php 
													$rating = $doctor['rating'];
													for ($i = 0; $i < 5; $i++) {
														echo $i < $rating ? '<i class="fas fa-star filled"></i>' : '<i class="fas fa-star"></i>';
													}
												?>
												<span class="d-inline-block average-rating">(<?php echo $rating; ?>)</span>
											</div>

											<ul class="available-info">
												<li>
													<i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['city'] . ', ' . $doctor['country']); ?>
												</li>
												<li>
													<i class="far fa-money-bill-alt"></i> $<?php echo htmlspecialchars($doctor['pricing_min']); ?> - $<?php echo htmlspecialchars($doctor['pricing_max']); ?> per hour
												</li>
											</ul>
											<div class="row row-sm">
												<div class="col-6">
													<a href="doctor-profile.php?doctor_id=<?php echo $doctor['user_id']; ?>" class="btn view-btn">View Profile</a>
												</div>
												<div class="col-6">
													<a href="booking.php?doctor_id=<?php echo $doctor['user_id']; ?>" class="btn book-btn">Book Now</a>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php } ?>
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

<!-- doccure/favourites.html  30 Nov 2019 04:12:17 GMT -->
</html>