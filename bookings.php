<?php
session_start();
include 'db.php'; // Database connection


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $session_id = (int)$_POST['session_id']; // Corrected variable name
    $patient_id = (int)$_SESSION['user_id']; // Get patient ID from session

    // Ensure patient ID is set
    if (!$patient_id) {
        die("Error: Patient ID not found in session.");
    }

    // Define status
    $status = 'pending';

    // Insert into sessions table
    $insert_query = "INSERT INTO patient_sessions (patient_id, session_id, status) 
                     VALUES (?, ?, ?)";
    $stmt = $conn->prepare(query: $insert_query);
    
    $stmt->bind_param("iis", $patient_id, $session_id, $status);

    if ($stmt->execute()) {
        echo "Session booked successfully!";
    } else {
        echo "maybe u booked BEFORE : or there " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<?php include('header.php'); ///////////////////////////////////////////////?>

			
			<!-- Page Content -->
			

<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-12">

<?php
include 'db.php'; // Database connection

// Ensure patient is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get session ID from URL
$session_id = $_GET['session_id']; 

// Fetch session details from patient_sessions table
$query = "SELECT * FROM sessions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();
$session = $result->fetch_assoc();

if (!$session) {
    die("Session not found.");
}

$doctor_id = $session['doctor_id']; // Get doctor ID from session

// Fetch doctor details from DOCTORS table
$query = "SELECT * FROM DOCTORS d INNER JOIN USERS u ON d.user_id = u.id WHERE d.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    die("Doctor not found.");
}
?>




<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="booking-doc-info">
                    <a href="singles.php?id=<?= $session_id ?>" class="booking-doc-img">
                        <img src="<?=$session['image']; ?>" alt="Doctor Image">
                    </a>
                    <div class="booking-info">
                        <h4><a href="doctor-profile.php?doctor_id=<?= $doctor_id ?>">
                             <?= $session['session_name'] ; ?>
                        </a></h4>
                        <div class="rating">
                        
                        </div>
                        <p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> <?= $session['location']; ?></p>
                    </div>
                </div>
                <hr>
                <div class="doctor-bio">
                    <h5>details</h5>
                    <p><?= $session['description']; ?></p>
                </div>
                <div class="doctor-services">
                    <h5>type</h5>
                    <p><?= $session['session_type']; ?></p>
                </div>
                <div class="doctor-specialization">
                    <h5>Doctor</h5>
                    <p>dr . <?= $doctor['fname']; ?>  <?=$doctor['lname'];?></p>
                </div>
                <div class="doctor-pricing">
                    <h5>Pricing</h5>
                    <p> $<?= $session['price']; ?> $</p>


                </div>
                <form action="" method="POST">


<!-- Hidden inputs to pass date, time, and patient_id -->
<input type="hidden" name="session_id" value="<?= $session_id; ?>">
<input type="hidden" name="patient_id" value="<?= $_SESSION['user_id']; ?>"> <!-- Using patient_id from session -->
<button type="submit"  class="btn btn-primary submit-btn" id="submit-btn" >Book session</button>
</form>
            </div>
            
        </div>
    </div>
</div>

       



<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-12">

          
                            </div>
                        </div>
                    </div>
                    <!-- /Schedule Content -->

                </div>
                <!-- /Schedule Widget -->

            </div>
        </div>
    </div>
</div>

<script>
    // Function to set the selected date and time
    function selectSlot(date, time, element) {
        // Check if slot is unavailable
        if (element.classList.contains('booked')) {
            alert('This slot is not available!');
            return;
        }

        // Set the hidden input values
        document.getElementById("appointment_date").value = date;
        document.getElementById("slot_time").value = time;

        // Highlight the selected slot
        document.querySelectorAll('.timing').forEach(function (slot) {
            slot.classList.remove('selected');
        });
        element.classList.add('selected');

        // Enable the submit button
        document.getElementById("submit-btn").disabled = false;
    }
</script>

                <!-- /Submit Section -->

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
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/booking.html  30 Nov 2019 04:12:16 GMT -->
</html>