<?php
session_start();
$servername = "localhost"; 
$username = "root";        
$password = "";           
$dbname = "mindspeak1";     

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in");
}$doctor_id = $_SESSION['user_id']; 
$sql = "SELECT reviews.*, users.fname, users.lname, users.profile_image 
        FROM reviews 
        JOIN users ON reviews.patient_id = users.id 
        WHERE reviews.doctor_id = ? 
        ORDER BY reviews.created_at DESC";
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
	

<div class="col-md-7 col-lg-8 col-xl-9">
    <div class="doc-review review-listing">
        <!-- Review Listing -->
        <ul class="comments-list">
            <?php while ($review = $result->fetch_assoc()): ?>
                <li>
                    <div class="comment">
                        <!-- Display the user's profile image -->
                        <img class="avatar rounded-circle" alt="User Image" src="<?php echo $review['profile_image']; ?>" onerror="this.src='assets/img/patients/default.jpg';">
                        <div class="comment-body">
                            <div class="meta-data">
                                <!-- Display the patient's first and last name -->
                                <span class="comment-author"><?php echo $review['fname'] . ' ' . $review['lname']; ?></span>
                                <span class="comment-date"><?php echo $review['created_at']; ?></span> <!-- Display created_at timestamp -->
								</div>
                                <div class="review-count rating">
                                    <?php
                                    // Display star rating based on review rating
                                    for ($i = 0; $i < 5; $i++) {
                                        echo ($i < $review['rating']) ? '<i class="fas fa-star filled"></i>' : '<i class="fas fa-star"></i>';
                                    }
                                    ?>
                                </div>
                          
							
                            <p class="recommended"><i class=""></i> <?php echo $review['review_title']; ?></p>
                            <p class="comment-content">
                                <?php echo $review['review_text']; ?>
                            </p>
                        </div>
						
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
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

<!-- doccure/reviews.html  30 Nov 2019 04:12:15 GMT -->
</html>