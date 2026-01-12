<?php
// Include database connection
include 'db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    echo "You must be logged in to use this feature.";
    exit;
}

$user_id = $_SESSION["user_id"];
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id > 0) {
    // Fetch doctor details from database
    $query = "SELECT * FROM DOCTORS d INNER JOIN USERS u ON d.user_id = u.id WHERE d.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();

    if (!$doctor) {
        echo "Doctor not found.";
        exit;
    }

    // Check if the doctor is already in favorites
    $fav_query = "SELECT * FROM FAVOURITES WHERE user_id = ? AND doctor_id = ?";
    $stmt = $conn->prepare($fav_query);
    $stmt->bind_param("ii", $user_id, $doctor_id);
    $stmt->execute();
    $fav_result = $stmt->get_result();
    $is_favorited = $fav_result->num_rows > 0;
}

// Handle the favorite button action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["toggle_fav"])) {
    if ($doctor_id > 0) {
        if ($is_favorited) {
            // Remove from favorites
            $delete = "DELETE FROM FAVOURITES WHERE user_id = ? AND doctor_id = ?";
            $stmt = $conn->prepare($delete);
            $stmt->bind_param("ii", $user_id, $doctor_id);
            if ($stmt->execute()) {
                $_SESSION["fav_message"] = "Doctor removed from favorites!";
            } else {
                $_SESSION["fav_message"] = "Error removing from favorites.";
            }
        } else {
            // Add to favorites
            $insert = "INSERT INTO FAVOURITES (user_id, doctor_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("ii", $user_id, $doctor_id);
            if ($stmt->execute()) {
                $_SESSION["fav_message"] = "Doctor added to favorites!";
            } else {
                $_SESSION["fav_message"] = "Error adding to favorites.";
            }
        }
        // Redirect to refresh the page and show updated status
        header("Location: doctor-profile.php?doctor_id=$doctor_id");
        exit;
    }
}
?>




<?php include('header.php'); ///////////////////////////////////////////////?>

			<!-- /Header -->
			
			
			
			<!-- Page Content -->
			<div class="content">
				<div class="container">

					<!-- Doctor Widget -->

					<div class="card">
					<?php if (isset($_SESSION["fav_message"])): ?>
    <div class="alert alert-info">
        <?php 
            echo $_SESSION["fav_message"]; 
            unset($_SESSION["fav_message"]); // Clear message after showing
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="doctor-widget">
            <div class="doc-info-left">
                <div class="doctor-img">
                    <img src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" class="img-fluid" alt="Doctor Image">
                </div>
                <div class="doc-info-cont">
                    <h4 class="doc-name">Dr. <?php echo htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']); ?></h4>
                    <p class="doc-speciality"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                    <div class="rating">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i < $doctor['rating'] ? 'filled' : ''; ?>"></i>
                        <?php endfor; ?>
                        <span class="d-inline-block average-rating">(<?php echo $doctor['reviews']; ?>)</span>
                    </div>
                    <div class="clinic-details">
                        <p class="doc-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['clinic_address']); ?>, <?php echo htmlspecialchars($doctor['city']); ?>, <?php echo htmlspecialchars($doctor['country']); ?></p>
                    </div>
                    <div class="clinic-services">
                        <?php 
                        $services = explode(',', $doctor['services']); 
                        foreach ($services as $service): 
                        ?>
                            <span><?php echo htmlspecialchars(trim($service)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="doc-info-right">
                <div class="clini-infos">
                    <ul>
                        <li><i class="far fa-thumbs-up"></i> <?php echo $doctor['rating'] * 20; ?>%</li>
                        <li><i class="far fa-comment"></i> <?php echo $doctor['reviews']; ?> Feedback</li>
                        <li><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($doctor['city'] . ', ' . $doctor['country']); ?></li>
                        <li><i class="far fa-money-bill-alt"></i> $<?php echo $doctor['pricing_min']; ?> - $<?php echo $doctor['pricing_max']; ?> per hour</li>
                    </ul>
                </div>
                <div class="doctor-action">
                    <form method="POST" action="doctor-profile.php?doctor_id=<?php echo $doctor_id; ?>">
                        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                        <button type="submit" name="toggle_fav" class="btn btn-white fav-btn">
                            <i class="far fa-bookmark"></i> <?php echo $is_favorited ? "Unfavorite" : "Favorite"; ?>
                        </button>
                    </form>
                    <a href="chat.html" class="btn btn-white msg-btn"><i class="far fa-comment-alt"></i></a>
                </div>
                <div class="clinic-booking">
                    <a class="apt-btn" href="booking.php?doctor_id=<?php echo $doctor_id; ?>">Book Appointment</a>
                </div>
            </div>
        </div>
    </div>
</div>

					<!-- /Doctor Widget -->
					
					<!-- Doctor Details Tab -->
					<div class="card">
						<div class="card-body pt-0">
						
							<!-- Tab Menu -->
							<nav class="user-tabs mb-4">
								<ul class="nav nav-tabs nav-tabs-bottom nav-justified">
									<li class="nav-item">
										<a class="nav-link active" href="#doc_overview" data-toggle="tab">Overview</a>
									</li>
									
									<li class="nav-item">
										<a class="nav-link" href="#doc_reviews" data-toggle="tab">Reviews</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" href="#doc_business_hours" data-toggle="tab">Business Hours</a>
									</li>
								</ul>
							</nav>
							<!-- /Tab Menu -->
							
							<!-- Tab Content -->
							<div class="tab-content pt-0">
							
								<!-- Overview Content -->


<div role="tabpanel" id="doc_overview" class="tab-pane fade show active">
    <div class="row">
        <div class="col-md-12 col-lg-9">
        
            <!-- About Details -->
            <div class="widget about-widget">
                <h4 class="widget-title">About Me</h4>
                <p><?php echo !empty($doctor['biography']) ? htmlspecialchars($doctor['biography']) : "No biography available."; ?></p>
            </div>
            <!-- /About Details -->
        
            <!-- Services List -->
            <div class="service-list">
                <h4>Services</h4>
                <ul class="clearfix">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <li><?php echo htmlspecialchars(trim($service)); ?></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No services available.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <!-- /Services List -->
            
            <!-- Specializations List -->
            <div class="service-list">
                <h4>Specializations</h4>
                <ul class="clearfix">
                   
                            <p><?php echo !empty($doctor['specialization']) ? htmlspecialchars($doctor['specialization']) : "No biography available."; ?></p>
							
                     
                </ul>
            </div>
            <!-- /Specializations List -->

        </div>
    </div>
</div>

								<!-- /Overview Content -->
							
								
								<!-- Reviews Content -->
								<?php
// db.php (Ensure this file only contains the database connection)

try {
    $pdo = new PDO('mysql:host=localhost;dbname=mindspeak', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>

<?php
include('db.php');  // Include database connection

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

// Fetch all reviews for this doctor along with patient details
// Change the query to use a named parameter
$stmt = $pdo->prepare("
    SELECT r.*, u.fname, u.lname, u.profile_image 
    FROM reviews r JOIN users u ON r.patient_id = u.id
    WHERE r.doctor_id = :doctor_id
    ORDER BY r.created_at DESC
");
$stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'];
    $review_title = $_POST['review_title'];
    $review_text = $_POST['review_text'];
    $patient_id = $_SESSION["user_id"];  // Get logged-in patient ID

    // Insert review into the database
    $stmt = $pdo->prepare("INSERT INTO reviews (doctor_id, patient_id, rating, review_title, review_text) 
                           VALUES (:doctor_id, :patient_id, :rating, :review_title, :review_text)");
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':review_title', $review_title);
    $stmt->bindParam(':review_text', $review_text);

    if ($stmt->execute()) {
        // Redirect to refresh page and show new review
		header("Location: " . $_SERVER['PHP_SELF'] . "?doctor_id=" . $doctor_id . "#doc_reviews");
		exit;        
    } else {
        echo 'Error submitting review.';
    }
}
?>
<!-- Review Listing -->
<div role="tabpanel" id="doc_reviews" class="tab-pane fade">
    <div class="widget review-listing">
        <ul class="comments-list">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <li>
                        <div class="comment">
                            <img class="avatar avatar-sm rounded-circle" alt="User Image" src="<?= htmlspecialchars($review['profile_image']); ?>">
                            <div class="comment-body">
                                <div class="meta-data">
                                    <span class="comment-author">
                                        <?= htmlspecialchars($review['fname'] . ' ' . $review['lname']); ?>
                                    </span>
                                    <span class="comment-date"><?= htmlspecialchars($review['created_at']); ?></span>
                                </div>
                                <!-- Stars under name -->
                                <div class="review-count rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="comment-content"><?= htmlspecialchars($review['review_text']); ?></p>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>
                    <p class="text-muted text-center">No reviews yet for this doctor.</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>

	<div class="write-review">
        <h4>Write a review for this Doctor</h4>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Review</label>
                <div class="star-rating">
                    <input id="star-5" type="radio" name="rating" value="5">
                    <label for="star-5" title="5 stars"><i class="active fa fa-star"></i></label>
                    <input id="star-4" type="radio" name="rating" value="4">
                    <label for="star-4" title="4 stars"><i class="active fa fa-star"></i></label>
                    <input id="star-3" type="radio" name="rating" value="3">
                    <label for="star-3" title="3 stars"><i class="active fa fa-star"></i></label>
                    <input id="star-2" type="radio" name="rating" value="2">
                    <label for="star-2" title="2 stars"><i class="active fa fa-star"></i></label>
                    <input id="star-1" type="radio" name="rating" value="1">
                    <label for="star-1" title="1 star"><i class="active fa fa-star"></i></label>
                </div>
            </div>

            <div class="form-group">
                <label>Title of your review</label>
                <input class="form-control" type="text" name="review_title" placeholder="If you could say it in one sentence, what would you say?">
            </div>
            
            <div class="form-group">
                <label>Your review</label>
                <textarea id="review_desc" maxlength="100" class="form-control" name="review_text"></textarea>
                <div class="d-flex justify-content-between mt-3">
                    <small class="text-muted"><span id="chars">100</span> characters remaining</small>
                </div>
            </div>

            <hr>

            <div class="form-group">
                <div class="terms-accept">
                    <div class="custom-checkbox">
                        <input type="checkbox" id="terms_accept" name="terms_accept">
                        <label for="terms_accept">I have read and accept <a href="#">Terms &amp; Conditions</a></label>
                    </div>
                </div>
            </div>

            <div class="submit-section">
                <button type="submit" class="btn btn-primary submit-btn">Add Review</button>
            </div>
        </form>
    </div>
</div>





 


								<!-- /Reviews Content -->
								
								<!-- Business Hours Content -->
								<div role="tabpanel" id="doc_business_hours" class="tab-pane fade">
									<div class="row">
										<div class="col-md-6 offset-md-3">
										
											<!-- Business Hours Widget -->
											<div class="widget business-widget">
												<div class="widget-content">
													<div class="listing-hours">
														<div class="listing-day current">
															<div class="day">Today <span>5 Nov 2019</span></div>
															<div class="time-items">
																<span class="open-status"><span class="badge bg-success-light">Open Now</span></span>
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Monday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Tuesday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Wednesday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Thursday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Friday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day">
															<div class="day">Saturday</div>
															<div class="time-items">
																<span class="time">07:00 AM - 09:00 PM</span>
															</div>
														</div>
														<div class="listing-day closed">
															<div class="day">Sunday</div>
															<div class="time-items">
																<span class="time"><span class="badge bg-danger-light">Closed</span></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<!-- /Business Hours Widget -->
									
										</div>
									</div>
								</div>
								<!-- /Business Hours Content -->
								
							</div>
						</div>
					</div>
					<!-- /Doctor Details Tab -->

				</div>
			</div>		
			<!-- /Page Content -->
   
			<!-- Footer -->
			<footer class="footer">
				
				<!-- Footer Top -->
				<div class="footer-top">
					<div class="container-fluid">
						<div class="row">
							<div class="col-lg-3 col-md-6">
							
								<!-- Footer Widget -->
								<div class="footer-widget footer-about">
									<div class="footer-logo">
										<img src="assets/img/footer-logo.png" alt="logo">
									</div>
									<div class="footer-about-content">
										<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>
										<div class="social-icon">
											<ul>
												<li>
													<a href="#" target="_blank"><i class="fab fa-facebook-f"></i> </a>
												</li>
												<li>
													<a href="#" target="_blank"><i class="fab fa-twitter"></i> </a>
												</li>
												<li>
													<a href="#" target="_blank"><i class="fab fa-linkedin-in"></i></a>
												</li>
												<li>
													<a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
												</li>
												<li>
													<a href="#" target="_blank"><i class="fab fa-dribbble"></i> </a>
												</li>
											</ul>
										</div>
									</div>
								</div>
								<!-- /Footer Widget -->
								
							</div>
							
							<div class="col-lg-3 col-md-6">
							
								<!-- Footer Widget -->
								<div class="footer-widget footer-menu">
									<h2 class="footer-title">For Patients</h2>
									<ul>
										<li><a href="search.html"><i class="fas fa-angle-double-right"></i> Search for Doctors</a></li>
										<li><a href="login.html"><i class="fas fa-angle-double-right"></i> Login</a></li>
										<li><a href="register.html"><i class="fas fa-angle-double-right"></i> Register</a></li>
										<li><a href="booking.html"><i class="fas fa-angle-double-right"></i> Booking</a></li>
										<li><a href="patient-dashboard.html"><i class="fas fa-angle-double-right"></i> Patient Dashboard</a></li>
									</ul>
								</div>
								<!-- /Footer Widget -->
								
							</div>
							
							<div class="col-lg-3 col-md-6">
							
								<!-- Footer Widget -->
								<div class="footer-widget footer-menu">
									<h2 class="footer-title">For Doctors</h2>
									<ul>
										<li><a href="appointments.html"><i class="fas fa-angle-double-right"></i> Appointments</a></li>
										<li><a href="chat.html"><i class="fas fa-angle-double-right"></i> Chat</a></li>
										<li><a href="login.html"><i class="fas fa-angle-double-right"></i> Login</a></li>
										<li><a href="doctor-register.html"><i class="fas fa-angle-double-right"></i> Register</a></li>
										<li><a href="doctor-dashboard.html"><i class="fas fa-angle-double-right"></i> Doctor Dashboard</a></li>
									</ul>
								</div>
								<!-- /Footer Widget -->
								
							</div>
							
							<div class="col-lg-3 col-md-6">
							
								<!-- Footer Widget -->
								<div class="footer-widget footer-contact">
									<h2 class="footer-title">Contact Us</h2>
									<div class="footer-contact-info">
										<div class="footer-address">
											<span><i class="fas fa-map-marker-alt"></i></span>
											<p> 3556  Beech Street, San Francisco,<br> California, CA 94108 </p>
										</div>
										<p>
											<i class="fas fa-phone-alt"></i>
											+1 315 369 5943
										</p>
										<p class="mb-0">
											<i class="fas fa-envelope"></i>
											doccure@example.com
										</p>
									</div>
								</div>
								<!-- /Footer Widget -->
								
							</div>
							
						</div>
					</div>
				</div>
				<!-- /Footer Top -->
				
				<!-- Footer Bottom -->
                <div class="footer-bottom">
					<div class="container-fluid">
					
						<!-- Copyright -->
						<div class="copyright">
							<div class="row">
								<div class="col-md-6 col-lg-6">
									<div class="copyright-text">
										<p class="mb-0"><a href="templateshub.net">Templates Hub</a></p>
									</div>
								</div>
								<div class="col-md-6 col-lg-6">
								
									<!-- Copyright Menu -->
									<div class="copyright-menu">
										<ul class="policy-menu">
											<li><a href="term-condition.html">Terms and Conditions</a></li>
											<li><a href="privacy-policy.html">Policy</a></li>
										</ul>
									</div>
									<!-- /Copyright Menu -->
									
								</div>
							</div>
						</div>
						<!-- /Copyright -->
						
					</div>
				</div>
				<!-- /Footer Bottom -->
				
			</footer>
			<!-- /Footer -->
		   
		</div>
		<!-- /Main Wrapper -->
		
		
		
		
		
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Fancybox JS -->
		<script src="assets/plugins/fancybox/jquery.fancybox.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/doctor-profile.html  30 Nov 2019 04:12:16 GMT -->
</html>
