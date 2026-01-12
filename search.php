<?php
session_start();

// Database connection
$host = 'localhost'; 
$dbname = 'mindspeak1';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
// Fetch available cities and specializations for filter dropdowns
$cities = $pdo->query("SELECT DISTINCT city FROM doctors ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
$specializations = $pdo->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization")->fetchAll(PDO::FETCH_COLUMN);

// Get filter values from GET parameters
$selectedCity = $_GET['city'] ?? '';
$selectedSpecializations = $_GET['specializations'] ?? [];

// Base query
$query = "
SELECT 
    u.fname, u.lname, u.profile_image, 
    d.specialization, d.services, d.clinic_address, 
    d.city, d.country, d.rating, d.reviews, 
    d.pricing_min, d.pricing_max, d.user_id as doctor_id
FROM USERS u
INNER JOIN DOCTORS d ON u.id = d.user_id 
WHERE 1=1
";

$params = [];

// Add city filter if selected
if (!empty($selectedCity)) {
    $query .= " AND d.city = ?";
    $params[] = $selectedCity;
}

// Add specialization filter if selected
if (!empty($selectedSpecializations)) {
    $placeholders = implode(',', array_fill(0, count($selectedSpecializations), '?'));
    $query .= " AND d.specialization IN ($placeholders)";
    $params = array_merge($params, $selectedSpecializations);
}

// Prepare and execute query with parameters
$stmt = $pdo->prepare($query);
$stmt->execute($params); // THIS WAS MISSING THE PARAMETERS
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('header.php');
?>

<!-- Page Content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar (Search Filter) -->
            <div class="col-md-12 col-lg-4 col-xl-3 theiaStickySidebar">
                <form method="GET" action="">    
                    <div class="card search-filter">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Search Filter</h4>
                        </div>
                    <div class="card-body">
                            <!-- City Filter Dropdown -->
                            <div class="filter-widget">
                    <h4>Select City</h4>
                    <select class="form-control" name="city">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= htmlspecialchars($city) ?>"
                                <?= ($selectedCity == $city) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Specialization Checkboxes -->
                <div class="filter-widget">
                    <h4>Select Specialist</h4>
                    <?php foreach ($specializations as $spec): ?>
                        <div>
                            <label class="custom_check">
                                <input type="checkbox" name="specializations[]" 
                                       value="<?= htmlspecialchars($spec) ?>"
                                       <?= in_array($spec, $selectedSpecializations) ? 'checked' : '' ?>>
                                <span class="checkmark"></span> <?= htmlspecialchars($spec) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Search Button -->
                <div class="btn-search">
                    <button type="submit" class="btn btn-block">Search</button>
                </div>
            </div>
        </div>
    </form> <!-- CLOSING FORM TAG WAS MISSING -->
</div>
            <!-- End Sidebar -->

            <!-- Doctors List -->
            <div class="col-md-12 col-lg-8 col-xl-9">
                <div class="row">
                <?php if (empty($doctors)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">No doctors found matching your criteria.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="col-md-12"> <!-- Each doctor takes half width on large screens -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="doctor-widget">
                                        <div class="doc-info-left">
                                            <div class="doctor-img">
                                                <a href="doctor-profile.php?doctor_id=<?php echo $doctor['doctor_id']; ?>">
                                                    <img src="<?php echo $doctor['profile_image']; ?>" class="img-fluid" alt="Doctor Image">
                                                </a>
                                            </div>
                                            <div class="doc-info-cont">
                                                <h4 class="doc-name">
                                                    <a href="doctor-profile.php?doctor_id=<?php echo $doctor['doctor_id']; ?>">
                                                        Dr. <?php echo htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']); ?>
                                                    </a>
                                                </h4>
                                                <p class="doc-speciality"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                                                <div class="rating">
                                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo ($i < $doctor['rating']) ? 'filled' : ''; ?>"></i>
                                                    <?php endfor; ?>
                                                    <span class="d-inline-block average-rating">(<?php echo $doctor['reviews']; ?>)</span>
                                                </div>
                                                <div class="clinic-details">
                                                    <p class="doc-location">
                                                        <i class="fas fa-map-marker-alt"></i> 
                                                        <?php echo htmlspecialchars($doctor['clinic_address'] . ', ' . $doctor['city'] . ', ' . $doctor['country']); ?>
                                                    </p>
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
                                                    <li><i class="far fa-money-bill-alt"></i> 
                                                        $<?php echo $doctor['pricing_min']; ?> - $<?php echo $doctor['pricing_max']; ?> 
                                                        <i class="fas fa-info-circle" data-toggle="tooltip" title="Consultation fees"></i>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="clinic-booking">
                                                <a class="view-pro-btn" href="doctor-profile.php?doctor_id=<?php echo $doctor['doctor_id']; ?>">View Profile</a>
                                                <a class="apt-btn" href="booking.php?doctor_id=<?php echo $doctor['doctor_id']; ?>">Book Appointment</a>
                                            </div>
                                        </div>
                                    </div> <!-- End of doctor-widget -->
                                </div> <!-- End of card-body -->
                            </div> <!-- End of card -->
                        </div> <!-- End of col-md-6 -->
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div> <!-- End of row -->
            </div> <!-- End of col-md-8 -->
        </div> <!-- End of row -->
    </div> <!-- End of container-fluid -->
</div> <!-- End of content -->
	


				
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
		
		<!-- Datetimepicker JS -->
		<script src="assets/js/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Fancybox JS -->
		<script src="assets/plugins/fancybox/jquery.fancybox.min.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/search.html  30 Nov 2019 04:12:16 GMT -->
</html>