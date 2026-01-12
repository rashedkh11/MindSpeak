
<?php
session_start();
// Database connection
$servername = "localhost"; // Your database host
$username = "root";        // Your database username
$password = "";            // Your database password
$dbname = "mindspeak1";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
// Initialize success message
$success_message = '';
$doctor_id = $session['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $session_name = $_POST['session_name'];
    $session_type = $_POST['session_type'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $clinic_name = $_POST['clinic_name'];
    $date_time = $_POST['date_time'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $doctor_id = $_SESSION['user_id'];

    // Handle image upload (optional)
    $image = '';
    if ($_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";  // Define the upload folder
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    // Prepare the SQL query to insert session data
    $sql = "INSERT INTO sessions (session_name, session_type, description, location, doctor_id, clinic_name, image, date_time, duration, price, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("ssssisssiss", $session_name, $session_type, $description, $location, $doctor_id, $clinic_name, $image, $date_time, $duration, $price, $status);

        // Execute the query
        if ($stmt->execute()) {
            $success_message = "Session added successfully!";
        } else {
            $success_message = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        $success_message = "Error: " . $conn->error;
    }

    // Close the connection
    $conn->close();
}
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
									<div class="appointment-tab">
									
										
										
										<div class="tab-content">
										
											<!-- Upcoming Appointment Tab -->
										
											<div class="tab-pane show active" >

                                            <h2>Session Information Form</h2>

<!-- Display success/error message -->
<?php if ($success_message): ?>
    <div class="message"><?php echo $success_message; ?></div>
<?php endif; ?>
<style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
            display: inline-block;
        }
        input[type="text"], input[type="number"], input[type="datetime-local"], select, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
        }
        input[type="file"] {
            padding: 10px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #4CAF50;
            margin-bottom: 15px;
        }
    </style>
<!-- Form for inserting session data -->
<div class="container">
    <h2>Create Session</h2>

    <!-- Success/Error Message -->
    <?php if ($success_message): ?>
        <div class="message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form action="create_session.php" method="POST" enctype="multipart/form-data">
        <label for="session_name">Session Name:</label>
        <input type="text" id="session_name" name="session_name" required><br><br>

        <label for="session_type">Session Type:</label>
        <input type="text" id="session_type" name="session_type" required><br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" required></textarea><br><br>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" required><br><br>

        <input type="number" id="doctor_id" name="doctor_id" value="<?php echo $doctor_id; ?>" hidden ><br><br>

        <label for="clinic_name">Clinic Name:</label>
        <input type="text" id="clinic_name" name="clinic_name" required><br><br>

        <label for="image">Image (Optional):</label>
        <input type="file" id="image" name="image"><br><br>

        <label for="date_time">Date and Time:</label>
        <input type="datetime-local" id="date_time" name="date_time" required><br><br>

        <label for="duration">Duration (in minutes):</label>
        <input type="number" id="duration" name="duration" required><br><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required><br><br>
    <input type="hidden" name="csrf_token" value="<?php require_once 'csrf_helper.php'; echo getCSRFToken(); ?>">

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select><br><br>

        <input type="submit" value="Submit">
    </form>
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