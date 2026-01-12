<?php
session_start();
include 'db.php'; // Database connection

// Ensure patient is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$doctor_id = $_GET['doctor_id']; // Get doctor ID dynamically

// Define available slots (10 AM - 4 PM)
$available_slots = [
    "10:00 AM", "11:00 AM", "12:00 PM",
    "1:00 PM", "2:00 PM", "3:00 PM", "4:00 PM"
];

// Fetch booked slots from the database
$unavailable_slots = [];
$query = "SELECT appointment_date, time FROM APPOINTMENTS WHERE doctor_id = ? AND status = 'confirmed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $unavailable_slots[$row["appointment_date"]][] = $row["time"];
}

// Generate upcoming week dates
$week_dates = [];
for ($i = 0; $i < 7; $i++) {
    $week_dates[] = date("Y-m-d", strtotime("+$i days"));
}

// Handle form submission to book the appointment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $appointment_date = $_POST['appointment_date'];
    $slot_time = $_POST['slot_time'];
    $doctor_id = $_POST['doctor_id'];
    $patient_id = $_SESSION['user_id']; // Get patient_id from session

    // Check if the slot is already booked and confirmed (unavailable)
    $query = "SELECT * FROM APPOINTMENTS WHERE appointment_date = ? AND time = ? AND doctor_id = ? AND status = 'confirmed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $appointment_date, $slot_time, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the slot is unavailable (confirmed), display a message
        echo "Sorry, this slot is already confirmed and unavailable!";
    } else {
        // Insert the appointment into the APPOINTMENTS table with pending status
        $status = 'pending'; // Appointment status
        $amount = 100; // Set the amount (you may calculate this based on pricing)
        
        $insert_query = "INSERT INTO APPOINTMENTS (patient_id, doctor_id, appointment_date, time, status, amount) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisssi", $patient_id, $doctor_id, $appointment_date, $slot_time, $status, $amount);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Appointment booked successfully!";
        } else {
            echo "Failed to book the appointment!";
        }
    }
}
?>
<?php include('header.php'); ///////////////////////////////////////////////?>

			
			<!-- Page Content -->
			<style>
    /* Style for booked slots (red) */
    .timing.booked {
        background-color: red !important;
        color: white !important;
        pointer-events: none;
    }
</style>

<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-12">

<?php
$doctor_id = $_GET['doctor_id']; // Get doctor ID from URL

// Fetch doctor details from DOCTORS table
$query = "SELECT * FROM DOCTORS d INNER JOIN USERS u ON d.user_id = u.id WHERE d.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="booking-doc-info">
                    <a href="doctor-profile.php?doctor_id=<?= $doctor_id ?>" class="booking-doc-img">
                        <img src="<?=$doctor['profile_image']; ?>" alt="Doctor Image">
                    </a>
                    <div class="booking-info">
                        <h4><a href="doctor-profile.php?doctor_id=<?= $doctor_id ?>">
                            Dr. <?= $doctor['fname'] . ' ' . $doctor['lname']; ?>
                        </a></h4>
                        <div class="rating">
                            <?php 
                            // Display dynamic stars based on rating
                            $rating = round($doctor['rating']);
                            for ($i = 0; $i < 5; $i++) {
                                echo $i < $rating ? '<i class="fas fa-star filled"></i>' : '<i class="fas fa-star"></i>';
                            }
                            ?>
                            <span class="d-inline-block average-rating"><?= $doctor['reviews']; ?> Reviews</span>
                        </div>
                        <p class="text-muted mb-0"><i class="fas fa-map-marker-alt"></i> <?= $doctor['clinic_address']; ?>, <?= $doctor['city']; ?>, <?= $doctor['country']; ?></p>
                    </div>
                </div>
                <hr>
                <div class="doctor-bio">
                    <h5>Biography</h5>
                    <p><?= $doctor['biography']; ?></p>
                </div>
                <div class="doctor-services">
                    <h5>Services</h5>
                    <p><?= $doctor['services']; ?></p>
                </div>
                <div class="doctor-specialization">
                    <h5>Specialization</h5>
                    <p><?= $doctor['specialization']; ?></p>
                </div>
                <div class="doctor-pricing">
                    <h5>Pricing</h5>
                    <p>Min: $<?= $doctor['pricing_min']; ?> - Max: $<?= $doctor['pricing_max']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

                <!-- Schedule Widget -->
       

<style>
    /* Style for unavailable slots (red) */
    .timing.booked {
        background-color: red !important;
        color: white !important;
        pointer-events: none;
    }

    .timing.selected {
        background-color: #0d6efd !important;
        color: white !important;
    }
</style>

<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-12">

                <!-- Schedule Widget -->
                <div class="card booking-schedule schedule-widget">
                    
                    <!-- Schedule Header -->
                    <div class="schedule-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="day-slot">
                                    <ul>
                                    <?php foreach ($week_dates as $date): ?>
    <li>
        <span><?= date("D", strtotime($date)); ?></span>
        <span class="slot-date"><?= date("d M", strtotime($date)); ?></span>
    </li>
<?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Schedule Header -->

                    <!-- Schedule Content -->
                    <div class="schedule-cont">
                        <div class="row">
                            <div class="col-md-12">
                                <form action="" method="POST">
                                    <div class="time-slot">
                                        <ul class="clearfix">
                                            <?php foreach ($week_dates as $date): ?>
                                                <li>
                                                    <?php foreach ($available_slots as $slot): ?>
                                                        <?php
                                                        // Check if this slot is unavailable
                                                        $is_unavailable = isset($unavailable_slots[$date]) && in_array($slot, $unavailable_slots[$date]);
                                                        ?>
                                                        <a class="timing <?= $is_unavailable ? 'booked' : ''; ?>" 
                                                           href="#" 
                                                           onclick="selectSlot('<?= $date; ?>', '<?= $slot; ?>', this)">
                                                           <span><?= $slot; ?></span>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
									</div>

                                    <!-- Hidden inputs to pass date, time, and patient_id -->
                                    <input type="hidden" name="appointment_date" id="appointment_date" value="">
                                    <input type="hidden" name="slot_time" id="slot_time" value="">
                                    <input type="hidden" name="doctor_id" value="<?= $doctor_id; ?>">
                                    <input type="hidden" name="patient_id" value="<?= $_SESSION['user_id']; ?>"> <!-- Using patient_id from session -->
                                    <button type="submit"  class="btn btn-primary submit-btn" id="submit-btn" disabled>Book Appointment</button>
                                </form>
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