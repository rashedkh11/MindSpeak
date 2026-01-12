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





<?php include('headerd.php'); ///////////////////////////////////////////////?>


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

<!-- Prediction Models Button List -->

<div class="profile-sidebar">
    

    <div class="dashboard-widget">
        <nav class="dashboard-menu">
            <ul>
			<li>
				<a href="overtime.php?id=<?php echo $patient_id; ?>">
					<span>OVER TIME</span>
				</a>
			</li>
              
             
            </ul>
        </nav>
    </div>

    </div>





							
						</div>

						<div class="col-md-7 col-lg-8 col-xl-9 dct-appoinment">
							<div class="card">
								<div class="card-body pt-0">
									<div class="user-tabs">
										<ul class="nav nav-tabs nav-tabs-bottom nav-justified flex-wrap">
										
											<li class="nav-item">
												<a class="nav-link active" href="#pat_appointments" data-toggle="tab">Dashboard</a>
											</li>
											
										
											
										</ul>
									</div>
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "mindspeak");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- model1 Mental Health ---
$sql1 = "SELECT 
            AVG(ADHD_prob) AS avg_ADHD,
            AVG(Aspergers_prob) AS avg_Aspergers,
            AVG(Depression_prob) AS avg_Depression,
            AVG(OCD_prob) AS avg_OCD,
            AVG(PTSD_prob) AS avg_PTSD
        FROM model1";
$result1 = $conn->query($sql1);
$row1 = $result1->fetch_assoc();
$model1_data = [
    'ADHD' => round($row1['avg_ADHD'], 3),
    'Aspergers' => round($row1['avg_Aspergers'], 3),
    'Depression' => round($row1['avg_Depression'], 3),
    'OCD' => round($row1['avg_OCD'], 3),
    'PTSD' => round($row1['avg_PTSD'], 3)
];

// --- facebookemo_model Emotions ---
$sql2 = "SELECT 
            AVG(sadness_prob) AS avg_sadness,
            AVG(joy_prob) AS avg_joy,
            AVG(love_prob) AS avg_love,
            AVG(anger_prob) AS avg_anger,
            AVG(fear_prob) AS avg_fear,
            AVG(surprise_prob) AS avg_surprise
        FROM facebookemo_model";
$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
$emotion_data = [
    'Sadness' => round($row2['avg_sadness'], 3),
    'Joy' => round($row2['avg_joy'], 3),
    'Love' => round($row2['avg_love'], 3),
    'Anger' => round($row2['avg_anger'], 3),
    'Fear' => round($row2['avg_fear'], 3),
    'Surprise' => round($row2['avg_surprise'], 3)
];

// --- suicide_model Suicide Detection ---
$sql3 = "SELECT 
            AVG(NonSuicide_prob) AS avg_non_suicide,
            AVG(Suicide_prob) AS avg_suicide
        FROM suicide_model";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();
$suicide_data = [
    'Non-Suicide' => round($row3['avg_non_suicide'], 3),
    'Suicide' => round($row3['avg_suicide'], 3)
];

// --- depression_model Full Texts Depression ---
$sql4 = "SELECT
            AVG(non_depression_prob) AS avg_non_depression,
            AVG(depression_prob) AS avg_depression
        FROM depression_model";
$result4 = $conn->query($sql4);
$row4 = $result4->fetch_assoc();
$fulltexts_data = [
    'Non-Depression' => round($row4['avg_non_depression'], 6),
    'Depression' => round($row4['avg_depression'], 6)
];

// --- posnegneu_model PosNegNeu Sentiment ---
$sql5 = "SELECT
            AVG(Positive_prob) AS avg_positive,
            AVG(Negative_prob) AS avg_negative,
            AVG(Neutral_prob) AS avg_neutral
         FROM posnegneu_model";
$result5 = $conn->query($sql5);
$row5 = $result5->fetch_assoc();
$posnegneu_data = [
    'Positive' => round($row5['avg_positive'], 3),
    'Negative' => round($row5['avg_negative'], 3),
    'Neutral' => round($row5['avg_neutral'], 3)
];

// --- NEW TABLE: mental_health_predictions ---
$sql6 = "SELECT 
            AVG(anxiety_prob) AS avg_anxiety,
            AVG(bipolar_prob) AS avg_bipolar,
            AVG(depression_prob) AS avg_depression,
            AVG(normal_prob) AS avg_normal,
            AVG(personality_disorder_prob) AS avg_personality_disorder,
            AVG(stress_prob) AS avg_stress,
            AVG(suicidal_prob) AS avg_suicidal
         FROM model2";
$result6 = $conn->query($sql6);
$row6 = $result6->fetch_assoc();
$new_model_data = [
    'Anxiety' => round($row6['avg_anxiety'], 5),
    'Bipolar' => round($row6['avg_bipolar'], 5),
    'Depression' => round($row6['avg_depression'], 5),
    'Normal' => round($row6['avg_normal'], 5),
    'Personality Disorder' => round($row6['avg_personality_disorder'], 5),
    'Stress' => round($row6['avg_stress'], 5),
    'Suicidal' => round($row6['avg_suicidal'], 5)
];

$conn->close();
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-bottom: 50px;
    }
    .chart-container {
        width: 100%;
        max-width: 450px;
        margin: auto;
        text-align: center;
    }
    .chart-container canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>

<!-- Mental Health Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">🧠 Mental Health Dashboard</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="mhBarChart"></canvas></div>
            <div class="chart-container"><h6>Horizontal Bar</h6><canvas id="mhHBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="mhPieChart"></canvas></div>
            <div class="chart-container"><h6>Line Chart</h6><canvas id="mhLineChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Emotion Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">📱 Emotions</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="emBarChart"></canvas></div>
            <div class="chart-container"><h6>Horizontal Bar</h6><canvas id="emHBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="emPieChart"></canvas></div>
            <div class="chart-container"><h6>Line Chart</h6><canvas id="emLineChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Suicide Detection Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">⚠️ Suicide Detection</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="sdBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="sdPieChart"></canvas></div>
        </div>
    </div>
</div>

<!-- Full Texts Depression Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">📝 Depression Probabilities</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="ftBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="ftPieChart"></canvas></div>
            <div class="chart-container"><h6>Doughnut Chart</h6><canvas id="ftDoughnutChart"></canvas></div>
            <div class="chart-container"><h6>Horizontal Bar Chart</h6><canvas id="ftHBarChart"></canvas></div>
        </div>
    </div>
</div>

<!-- PosNegNeu Sentiment Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">😊 PosNegNeu Sentiment</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="pnBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="pnPieChart"></canvas></div>
            <div class="chart-container"><h6>Doughnut Chart</h6><canvas id="pnDoughnutChart"></canvas></div>
            <div class="chart-container"><h6>Horizontal Bar Chart</h6><canvas id="pnHBarChart"></canvas></div>
        </div>
    </div>
</div>

<!-- New Mental Health Predictions Dashboard -->
<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">🆕 Mental Health Predictions</h2>
        <div class="chart-grid">
            <div class="chart-container"><h6>Bar Chart</h6><canvas id="newBarChart"></canvas></div>
            <div class="chart-container"><h6>Pie Chart</h6><canvas id="newPieChart"></canvas></div>
            <div class="chart-container"><h6>Doughnut Chart</h6><canvas id="newDoughnutChart"></canvas></div>
            <div class="chart-container"><h6>Horizontal Bar Chart</h6><canvas id="newHBarChart"></canvas></div>
        </div>
    </div>
</div>

<script>
const mhLabels = <?php echo json_encode(array_keys($model1_data)); ?>;
const mhValues = <?php echo json_encode(array_values($model1_data)); ?>;
const mhColors = [
    'rgba(255, 99, 132, 0.6)',
    'rgba(54, 162, 235, 0.6)',
    'rgba(255, 206, 86, 0.6)',
    'rgba(75, 192, 192, 0.6)',
    'rgba(153, 102, 255, 0.6)'
];

const emLabels = <?php echo json_encode(array_keys($emotion_data)); ?>;
const emValues = <?php echo json_encode(array_values($emotion_data)); ?>;
const emColors = [
    'rgba(244, 67, 54, 0.6)',
    'rgba(33, 150, 243, 0.6)',
    'rgba(233, 30, 99, 0.6)',
    'rgba(255, 152, 0, 0.6)',
    'rgba(103, 58, 183, 0.6)',
    'rgba(0, 188, 212, 0.6)'
];

const sdLabels = <?php echo json_encode(array_keys($suicide_data)); ?>;
const sdValues = <?php echo json_encode(array_values($suicide_data)); ?>;
const sdColors = [
    'rgba(244, 67, 54, 0.6)',
    'rgba(54, 162, 235, 0.6)'
];

const ftLabels = <?php echo json_encode(array_keys($fulltexts_data)); ?>;
const ftValues = <?php echo json_encode(array_values($fulltexts_data)); ?>;
const ftColors = [
    'rgba(100, 181, 246, 0.6)',
    'rgba(255, 87, 34, 0.6)'
];

const pnLabels = <?php echo json_encode(array_keys($posnegneu_data)); ?>;
const pnValues = <?php echo json_encode(array_values($posnegneu_data)); ?>;
const pnColors = [
    'rgba(75, 192, 192, 0.6)',
    'rgba(255, 99, 132, 0.6)',
    'rgba(201, 203, 207, 0.6)'
];

const newLabels = <?php echo json_encode(array_keys($new_model_data)); ?>;
const newValues = <?php echo json_encode(array_values($new_model_data)); ?>;
const newColors = [
    'rgba(255, 99, 132, 0.6)',        // Anxiety - Red
    'rgba(54, 162, 235, 0.6)',        // Bipolar - Blue
    'rgba(255, 206, 86, 0.6)',        // Depression - Yellow
    'rgba(75, 192, 192, 0.6)',        // Normal - Teal
    'rgba(153, 102, 255, 0.6)',       // Personality Disorder - Purple
    'rgba(255, 159, 64, 0.6)',        // Stress - Orange
    'rgba(199, 199, 199, 0.6)'        // Suicidal - Gray
];

// --- Chart Creation Helper ---
function createChart(ctx, type, labels, data, colors, options={}) {
    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: [{
                label: 'Avg Probability',
                data: data,
                backgroundColor: colors,
                borderColor: colors.map(c => c.replace('0.6', '1')),
                borderWidth: 1,
                fill: type === 'line' || type === 'area' ? true : false,
                tension: 0.3
            }]
        },
        options: Object.assign({
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top' }
            },
            scales: (type === 'pie' || type === 'doughnut') ? {} : {
                y: { beginAtZero: true, max: 1 }
            }
        }, options)
    });
}

// --- Mental Health Charts ---
createChart(document.getElementById('mhBarChart'), 'bar', mhLabels, mhValues, mhColors);
createChart(document.getElementById('mhHBarChart'), 'bar', mhLabels, mhValues, mhColors, {indexAxis: 'y'},);
createChart(document.getElementById('mhPieChart'), 'pie', mhLabels, mhValues, mhColors);
createChart(document.getElementById('mhLineChart'), 'line', mhLabels, mhValues, mhColors);

// --- Emotion Charts ---
createChart(document.getElementById('emBarChart'), 'bar', emLabels, emValues, emColors);
createChart(document.getElementById('emHBarChart'), 'bar', emLabels, emValues, emColors, {indexAxis: 'y'});
createChart(document.getElementById('emPieChart'), 'pie', emLabels, emValues, emColors);
createChart(document.getElementById('emLineChart'), 'line', emLabels, emValues, emColors);

// --- Suicide Detection Charts ---
createChart(document.getElementById('sdBarChart'), 'bar', sdLabels, sdValues, sdColors);
createChart(document.getElementById('sdPieChart'), 'pie', sdLabels, sdValues, sdColors);

// --- Full Text Depression Charts ---
createChart(document.getElementById('ftBarChart'), 'bar', ftLabels, ftValues, ftColors);
createChart(document.getElementById('ftPieChart'), 'pie', ftLabels, ftValues, ftColors);
createChart(document.getElementById('ftDoughnutChart'), 'doughnut', ftLabels, ftValues, ftColors);
createChart(document.getElementById('ftHBarChart'), 'bar', ftLabels, ftValues, ftColors, {indexAxis: 'y'});

// --- PosNegNeu Sentiment Charts ---
createChart(document.getElementById('pnBarChart'), 'bar', pnLabels, pnValues, pnColors);
createChart(document.getElementById('pnPieChart'), 'pie', pnLabels, pnValues, pnColors);
createChart(document.getElementById('pnDoughnutChart'), 'doughnut', pnLabels, pnValues, pnColors);
createChart(document.getElementById('pnHBarChart'), 'bar', pnLabels, pnValues, pnColors, {indexAxis: 'y'});

// --- NEW Mental Health Predictions Charts ---
createChart(document.getElementById('newBarChart'), 'bar', newLabels, newValues, newColors);
createChart(document.getElementById('newPieChart'), 'pie', newLabels, newValues, newColors);
createChart(document.getElementById('newDoughnutChart'), 'doughnut', newLabels, newValues, newColors);
createChart(document.getElementById('newHBarChart'), 'bar', newLabels, newValues, newColors, {indexAxis: 'y'});

</script>



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