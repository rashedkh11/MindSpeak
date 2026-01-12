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
				<a href="MentalHealthDetection.php?id=<?php echo $patient_id; ?>">
					<span>Dashboard</span>
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
												<a class="nav-link active" href="#pat_appointments" data-toggle="tab">OVER TIME</a>
											</li>
											
										
											
										</ul>
									</div>
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "mindspeak");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper: function to fetch average probabilities grouped by day for a given table and columns
function fetch_avg_by_date($conn, $table, $columns) {
    // Prepare SQL: group by DATE(timestamp), average for each column
    // Assume table has a 'timestamp' column
    $cols_avg_sql = [];
    foreach ($columns as $col) {
        $cols_avg_sql[] = "AVG($col) AS avg_$col";
    }
    $cols_avg_sql_str = implode(", ", $cols_avg_sql);
    
    $sql = "SELECT DATE(timestamp) AS date, $cols_avg_sql_str
            FROM $table
            GROUP BY DATE(timestamp)
            ORDER BY DATE(timestamp)";
    $result = $conn->query($sql);

    $dates = [];
    $data = [];
    foreach ($columns as $col) {
        $data[$col] = [];
    }

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['date'];
            foreach ($columns as $col) {
                $data[$col][] = round($row["avg_$col"], 5);
            }
        }
    }
    return ['dates' => $dates, 'data' => $data];
}

// --- model1 Mental Health overall averages ---
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
    'ADHD_prob' => round($row1['avg_ADHD'], 5),
    'Aspergers_prob' => round($row1['avg_Aspergers'], 5),
    'Depression_prob' => round($row1['avg_Depression'], 5),
    'OCD_prob' => round($row1['avg_OCD'], 5),
    'PTSD_prob' => round($row1['avg_PTSD'], 5)
];

// --- model1 Mental Health overtime ---
$model1_overtime = fetch_avg_by_date($conn, 'model1', array_keys($model1_data));

// --- facebookemo_model Emotions overall averages ---
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
    'sadness_prob' => round($row2['avg_sadness'], 5),
    'joy_prob' => round($row2['avg_joy'], 5),
    'love_prob' => round($row2['avg_love'], 5),
    'anger_prob' => round($row2['avg_anger'], 5),
    'fear_prob' => round($row2['avg_fear'], 5),
    'surprise_prob' => round($row2['avg_surprise'], 5)
];

// --- facebookemo_model overtime ---
$emotion_overtime = fetch_avg_by_date($conn, 'facebookemo_model', array_keys($emotion_data));

// --- suicide_model Suicide Detection overall averages ---
$sql3 = "SELECT 
            AVG(NonSuicide_prob) AS avg_non_suicide,
            AVG(Suicide_prob) AS avg_suicide
        FROM suicide_model";
$result3 = $conn->query($sql3);
$row3 = $result3->fetch_assoc();
$suicide_data = [
    'NonSuicide_prob' => round($row3['avg_non_suicide'], 5),
    'Suicide_prob' => round($row3['avg_suicide'], 5)
];

// --- suicide_model overtime ---
$suicide_overtime = fetch_avg_by_date($conn, 'suicide_model', array_keys($suicide_data));

// --- depression_model Full Texts Depression overall averages ---
$sql4 = "SELECT
            AVG(non_depression_prob) AS avg_non_depression,
            AVG(depression_prob) AS avg_depression
        FROM depression_model";
$result4 = $conn->query($sql4);
$row4 = $result4->fetch_assoc();
$fulltexts_data = [
    'non_depression_prob' => round($row4['avg_non_depression'], 5),
    'depression_prob' => round($row4['avg_depression'], 5)
];

// --- depression_model overtime ---
$fulltexts_overtime = fetch_avg_by_date($conn, 'depression_model', array_keys($fulltexts_data));

// --- posnegneu_model PosNegNeu Sentiment overall averages ---
$sql5 = "SELECT
            AVG(Positive_prob) AS avg_positive,
            AVG(Negative_prob) AS avg_negative,
            AVG(Neutral_prob) AS avg_neutral
         FROM posnegneu_model";
$result5 = $conn->query($sql5);
$row5 = $result5->fetch_assoc();
$posnegneu_data = [
    'Positive_prob' => round($row5['avg_positive'], 5),
    'Negative_prob' => round($row5['avg_negative'], 5),
    'Neutral_prob' => round($row5['avg_neutral'], 5)
];

// --- posnegneu_model overtime ---
$posnegneu_overtime = fetch_avg_by_date($conn, 'posnegneu_model', array_keys($posnegneu_data));

// --- model2 NEW Mental Health Predictions overall averages ---
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
    'anxiety_prob' => round($row6['avg_anxiety'], 5),
    'bipolar_prob' => round($row6['avg_bipolar'], 5),
    'depression_prob' => round($row6['avg_depression'], 5),
    'normal_prob' => round($row6['avg_normal'], 5),
    'personality_disorder_prob' => round($row6['avg_personality_disorder'], 5),
    'stress_prob' => round($row6['avg_stress'], 5),
    'suicidal_prob' => round($row6['avg_suicidal'], 5)
];

// --- model2 overtime ---
$new_model_overtime = fetch_avg_by_date($conn, 'model2', array_keys($new_model_data));

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
        max-width: 600px;
        margin: auto;
        text-align: center;
    }
    .chart-container canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>

<div class="card card-table mb-4">
    <div class="card-body">
        <h2 class="text-center mb-4">📈 Mental Health Prediction Trends Over Time (Example Charts)</h2>
        <div class="chart-grid">

            <!-- Example: model1 line chart -->
            <div class="chart-container">
                <h6>Model1 Mental Health Probabilities Over Time</h6>
                <canvas id="model1LineChart"></canvas>
            </div>

            <!-- Example: facebookemo_model line chart -->
            <div class="chart-container">
                <h6>Facebook Emo Emotions Over Time</h6>
                <canvas id="emotionLineChart"></canvas>
            </div>

            <!-- Example: suicide_model line chart -->
            <div class="chart-container">
                <h6>Suicide Detection Over Time</h6>
                <canvas id="suicideLineChart"></canvas>
            </div>

            <!-- Example: depression_model line chart -->
            <div class="chart-container">
                <h6>Depression Model Over Time</h6>
                <canvas id="depressionLineChart"></canvas>
            </div>

            <!-- Example: posnegneu_model line chart -->
            <div class="chart-container">
                <h6>PosNegNeu Sentiment Over Time</h6>
                <canvas id="posnegneuLineChart"></canvas>
            </div>

            <!-- Example: new model2 line chart -->
            <div class="chart-container">
                <h6>New Mental Health Predictions Over Time</h6>
                <canvas id="newModelLineChart"></canvas>
            </div>

        </div>
    </div>
</div>

<script>
// Data passed from PHP
const model1Dates = <?php echo json_encode($model1_overtime['dates']); ?>;
const model1Data = <?php echo json_encode($model1_overtime['data']); ?>;

const emotionDates = <?php echo json_encode($emotion_overtime['dates']); ?>;
const emotionData = <?php echo json_encode($emotion_overtime['data']); ?>;

const suicideDates = <?php echo json_encode($suicide_overtime['dates']); ?>;
const suicideData = <?php echo json_encode($suicide_overtime['data']); ?>;

const depressionDates = <?php echo json_encode($fulltexts_overtime['dates']); ?>;
const depressionData = <?php echo json_encode($fulltexts_overtime['data']); ?>;

const posnegneuDates = <?php echo json_encode($posnegneu_overtime['dates']); ?>;
const posnegneuData = <?php echo json_encode($posnegneu_overtime['data']); ?>;

const newModelDates = <?php echo json_encode($new_model_overtime['dates']); ?>;
const newModelData = <?php echo json_encode($new_model_overtime['data']); ?>;

// Helper to generate line chart datasets from data object and colors
function createDatasets(dataObj, colors) {
    const labels = Object.keys(dataObj);
    return labels.map((key, idx) => ({
        label: key.replace(/_prob$/i, '').replace(/_/g, ' ').toUpperCase(),
        data: dataObj[key],
        borderColor: colors[idx],
        backgroundColor: colors[idx].replace('1)', '0.2)'),
        fill: false,
        tension: 0.2
    }));
}

function createChart(ctxId, labels, dataObj, colors, title) {
    const ctx = document.getElementById(ctxId).getContext('2d');
    const datasets = createDatasets(dataObj, colors);

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            stacked: false,
            plugins: {
                title: {
                    display: true,
                    text: title
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    title: {
                        display: true,
                        text: 'Average Probability'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
}

// Colors arrays for each model's datasets (you can customize)
const colorsModel1 = [
    'rgba(255, 99, 132, 1)',     // ADHD
    'rgba(54, 162, 235, 1)',     // Aspergers
    'rgba(255, 206, 86, 1)',     // Depression
    'rgba(75, 192, 192, 1)',     // OCD
    'rgba(153, 102, 255, 1)'     // PTSD
];
const colorsEmotions = [
    'rgba(153, 0, 0, 1)',        // sadness
    'rgba(255, 165, 0, 1)',      // joy
    'rgba(255, 20, 147, 1)',     // love
    'rgba(255, 0, 0, 1)',        // anger
    'rgba(128, 0, 128, 1)',      // fear
    'rgba(255, 215, 0, 1)'       // surprise
];
const colorsSuicide = [
    'rgba(54, 162, 235, 1)',     // NonSuicide
    'rgba(255, 99, 132, 1)'      // Suicide
];
const colorsDepression = [
    'rgba(75, 192, 192, 1)',     // non_depression
    'rgba(255, 206, 86, 1)'      // depression
];
const colorsPosNegNeu = [
    'rgba(255, 99, 132, 1)',     // Positive
    'rgba(54, 162, 235, 1)',     // Negative
    'rgba(255, 206, 86, 1)'      // Neutral
];
const colorsNewModel = [
    'rgba(255, 99, 132, 1)',     // anxiety
    'rgba(54, 162, 235, 1)',     // bipolar
    'rgba(255, 206, 86, 1)',     // depression
    'rgba(75, 192, 192, 1)',     // normal
    'rgba(153, 102, 255, 1)',    // personality disorder
    'rgba(255, 159, 64, 1)',     // stress
    'rgba(255, 0, 255, 1)'       // suicidal
];

// Create charts
createChart('model1LineChart', model1Dates, model1Data, colorsModel1, 'Model1 Mental Health Probabilities Over Time');
createChart('emotionLineChart', emotionDates, emotionData, colorsEmotions, 'Facebook Emo Emotions Over Time');
createChart('suicideLineChart', suicideDates, suicideData, colorsSuicide, 'Suicide Detection Over Time');
createChart('depressionLineChart', depressionDates, depressionData, colorsDepression, 'Depression Model Over Time');
createChart('posnegneuLineChart', posnegneuDates, posnegneuData, colorsPosNegNeu, 'PosNegNeu Sentiment Over Time');
createChart('newModelLineChart', newModelDates, newModelData, colorsNewModel, 'New Mental Health Predictions Over Time');

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