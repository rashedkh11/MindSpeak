<?php
session_start();
// Include database connection
require 'db.php'; 

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if session ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Session ID is missing from the URL.");
}

// Get session ID from URL and ensure it's an integer
$session_id = intval($_GET['id']);

// Query to fetch session details along with doctor info
$query = "
    SELECT s.*, 
           u.fname AS doctor_name, 
           u.profile_image AS doctor_image 
    FROM SESSIONS s
    LEFT JOIN USERS u ON s.doctor_id = u.id
    WHERE s.id = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

// Bind session ID to query
$stmt->bind_param("i", $session_id);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

// Get the result set
$result = $stmt->get_result();
if (!$result) {
    die("Query result failed: " . $conn->error);
}

// Fetch session data
$session = $result->fetch_assoc();

// If no session found, show error
if (!$session) {
    die("Session not found.");
}

$stmt->close();
$session_date = date("d/m/Y", strtotime($session['date_time']));
$session_time = date("h:i A", strtotime($session['date_time']));
?>
   




	<?php include "header.php";?>
			
            
            <!-- Page Content -->
             
			<div class="content">
				<div class="container">

					<!-- Doctor Widget -->
					<div class="card">
        <div class="card-body">
            <div class="doctor-widget">
                <div class="doc-info-left">
                    <div class="doctor-img">
                        <img src="<?= htmlspecialchars($session['image'] ?? 'assets/img/groub.jpg') ?>" class="img-fluid" alt="Session Image">
                    </div>
                    <div class="doc-info-cont">
                        <h5 class="doc-name"><?= htmlspecialchars($session['session_name']) ?></h5>
                        <h3 class="doc-speciality"><?= nl2br(htmlspecialchars($session['description'])) ?></h3>
                        <div class="clinic-details">
                            <p class="doc-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($session['location'] ?? 'Location not available') ?></p>
                        </div>
                    </div>
                </div>
                <div class="doc-info-right">
                    <div class="clini-infos">
                        <ul>
                            <li><i class="far fa-user"></i> Dr. <?= htmlspecialchars($session['doctor_name'] ?? 'Unknown') ?></li>
                            <li><i class="far fa-building"></i> <?= htmlspecialchars($session['clinic_name'] ?? 'Unknown Clinic') ?></li>
                        </ul>
                    </div>
                    <div class="clinic-booking">
                        <a class="apt-btn" href="bookings.php?session_id=<?= $session_id ?>">Book Appointment</a>
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
                    <h2 class="widget-title">About</h2>
                    <p><?= nl2br(htmlspecialchars($session['description'])) ?></p>
                </div>

                <!-- Session Details -->
                <div class="widget education-widget">
                    <h4 class="widget-title">Session Details</h4>
                    <div class="experience-box">
                        <ul class="experience-list">

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#"><i class="far fa-user"></i> Dr. <?= htmlspecialchars($session['doctor_name'] ?? 'Unknown') ?></a>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#"><i class="far fa-building"></i> <?= htmlspecialchars($session['clinic_name'] ?? 'Unknown Clinic') ?></a>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#"><i class="far fa-hourglass"></i> <?= htmlspecialchars($session['duration'] ?? 'Duration not available') ?></a>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <h5><a href="#"><i class="far fa-clock"></i> <b><?= $session_time ?></b></a></h5>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <h5><a href="#"><i class="far fa-calendar"></i> <b><?= $session_date ?></b></a></h5>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#" class="fas fa-map-marker-alt text-danger"> <?= htmlspecialchars($session['location'] ?? 'Location not available') ?></a>
                                    </div>
                                </div>
                            </li>

                            <li>
                                <div class="experience-user"><div class="before-circle"></div></div>
                                <div class="experience-content">
                                    <div class="timeline-content">
                                        <a href="#" class="far fa-money-bill-alt text-success"> Pricing: $<?= htmlspecialchars($session['price'] ?? 'Price not available') ?> per session</a>
                                    </div>
                                </div>
                            </li>

                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>


								<!-- /Overview Content -->
                                 
								
								
								
								
								
								
								
							</div>
						</div>
					</div>
					<!-- /Doctor Details Tab -->

				</div>
			</div>		
			<!-- /Page Content -->
   
			<!-- Footer -->
			<?php include "footer.php"?>
			<!-- /Footer -->
		   
		</div>
		<!-- /Main Wrapper -->
		
		<!-- Voice Call Modal -->
		<div class="modal fade call-modal" id="voice_call">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<!-- Outgoing Call -->
						<div class="call-box incoming-box">
							<div class="call-wrapper">
								<div class="call-inner">
									<div class="call-user">
										<img alt="User Image" src="assets/img/doctors/doctor-thumb-02.jpg" class="call-avatar">
										<h4>Dr. Darren Elder</h4>
										<span>Connecting...</span>
									</div>							
									<div class="call-items">
										<a href="javascript:void(0);" class="btn call-item call-end" data-dismiss="modal" aria-label="Close"><i class="material-icons">call_end</i></a>
										<a href="voice-call.html" class="btn call-item call-start"><i class="material-icons">call</i></a>
									</div>
								</div>
							</div>
						</div>
						<!-- Outgoing Call -->

					</div>
				</div>
			</div>
		</div>
		<!-- /Voice Call Modal -->
		
		<!-- Video Call Modal -->
		<div class="modal fade call-modal" id="video_call">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body">
					
						<!-- Incoming Call -->
						<div class="call-box incoming-box">
							<div class="call-wrapper">
								<div class="call-inner">
									<div class="call-user">
										<img class="call-avatar" src="assets/img/doctors/doctor-thumb-02.jpg" alt="User Image">
										<h4>Dr. Darren Elder</h4>
										<span>Calling ...</span>
									</div>							
									<div class="call-items">
										<a href="javascript:void(0);" class="btn call-item call-end" data-dismiss="modal" aria-label="Close"><i class="material-icons">call_end</i></a>
										<a href="video-call.html" class="btn call-item call-start"><i class="material-icons">videocam</i></a>
									</div>
								</div>
							</div>
						</div>
						<!-- /Incoming Call -->
						
					</div>
				</div>
			</div>
		</div>
		<!-- Video Call Modal -->
		
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