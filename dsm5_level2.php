<?php
session_start();
include "db.php"; 

if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in.");
}

$user_id = $_SESSION['user_id'];

// =============================================
// 1. Handle Retake Requests
// =============================================
if (isset($_GET['retake'])) {
    $test_to_retake = $_GET['retake'];
    
    // Verify test exists in level2Tests
    $stmt = $conn->prepare("SELECT level2_tests FROM dsm5_v1 WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $level2Tests = json_decode($stmt->get_result()->fetch_assoc()['level2_tests'], true);
    
    if (!in_array($test_to_retake, $level2Tests)) {
        die("Invalid test type");
    }
}

// =============================================
// 2. Get Completed Tests (within last 7 days)
// =============================================
$completed_tests = [];
$stmt = $conn->prepare("
    SELECT test_type, MAX(created_at) as last_completed 
    FROM dsm5_v2 
    WHERE user_id = ? 
    AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY test_type
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $completed_tests[$row['test_type']] = $row['last_completed'];
}

// =============================================
// 3. Get Required Level 2 Tests
// =============================================
$stmt = $conn->prepare("SELECT level2_tests FROM dsm5_v1 WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // No Level 1 results found for this user
    header("Location: dashboard.php?error=no_level1_results");
    exit();
}
$row = $result->fetch_assoc();
$level2Tests = json_decode($row['level2_tests'], true);
$stmt->close();

// Validate the decoded JSON
if (json_last_error() !== JSON_ERROR_NONE || !is_array($level2Tests)) {
    header("Location: dashboard.php?error=invalid_test_data");
    exit();
}

// =============================================
// 4. Handle Form Submission
// =============================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $test_type = $_POST['test_type'];
    $answers = [];

// Check if already completed within 7 days
    if (isset($completed_tests[$test_type])) {
        die("You've already completed this test within the last 7 days.");
    }
    // Capture responses and calculate total score
    for ($i = 1; isset($_POST["q$i"]); $i++) {
        $answers["q$i"] = (int)$_POST["q$i"];
    }

    $result = calculateTestScore($test_type, $answers);
    // Convert to JSON for database storage
    $answersJSON = json_encode($answers);
    $totalScore = $result['raw']; // Use the calculated raw score

    // Insert into database
    $sql = "INSERT INTO dsm5_v2 (user_id, test_type, score, answers) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $user_id, $test_type, $totalScore, $answersJSON);
        
    if ($stmt->execute()) {
        // Store results in session for display after redirect
            $_SESSION['test_results'] = [
                'type' => $test_type,
                'score' => $totalScore,
                'data' => $result
            ];

         // Redirect with completion message
         header("Location: dsm5_level2.php?completed=" . urlencode($test_type));
        exit();
    } else {
        die("Error saving Level 2 test results: " . $stmt->error);
    }
}

function calculateTestScore($testType, $answers) {
    $totalItems = count($answers);
    $answeredItems = count(array_filter($answers, function($a) { return $a !== null; }));
    $rawSum = array_sum(array_filter($answers));
    
    // Check minimum answered items requirement (75% for most tests, 80% for mania)
    $minRequired = ($testType === 'mania') ? 0.8 : 0.75;
    if ($answeredItems < ceil($totalItems * $minRequired)) {
        return [
            'error' => "At least " . ceil($totalItems * $minRequired) . " items must be answered for valid scoring"
        ];
    }
    // Calculate prorated score if needed
    $proratedRaw = ($answeredItems < $totalItems) 
        ? round(($rawSum * $totalItems) / $answeredItems)
        : $rawSum;
    switch ($testType) {
        case 'depression':
        case 'sleep':
        case 'anger':
        case 'anxiety':
            $tScore = calculateTScore($testType, $proratedRaw);
            return [
                'raw' => $proratedRaw,
                't_score' => $tScore,
                'interpretation' => interpretTScore($tScore)
            ];
        case 'ocd':
            $averageScore = $proratedRaw / 5;
            $interpretation = match(true) {
                $averageScore < 1 => 'None',
                $averageScore < 2 => 'Mild',
                $averageScore < 3 => 'Moderate',
                $averageScore < 4 => 'Severe',
                default => 'Extreme'
            };
            return [
                'raw' => $proratedRaw,
                'average' => round($averageScore, 1),
                'interpretation' => $interpretation,
                'flag' => ($proratedRaw >= 8) ? 'Consider detailed OCD assessment' : null
            ];

        case 'somatic':
            $interpretation = match(true) {
                $proratedRaw <= 4 => 'Minimal',
                $proratedRaw <= 9 => 'Low',
                $proratedRaw <= 14 => 'Medium',
                default => 'High'
            };
            return [
                'raw' => $proratedRaw,
                'interpretation' => $interpretation
            ];

        case 'substance':
            $flaggedSubstances = [];
            foreach ($answers as $substance => $score) {
                if ($score > 0) {
                    $flaggedSubstances[] = [
                        'substance' => $substance,
                        'score' => $score,
                        'frequency' => match($score) {
                            1 => '1-2 days',
                            2 => 'Several days',
                            3 => 'More than half the days',
                            4 => 'Nearly every day',
                            default => 'Not at all'
                        }
                    ];
                }
            }
            return [
                'items' => $flaggedSubstances,
                'interpretation' => count($flaggedSubstances) > 1 
                    ? 'Complex substance use pattern' 
                    : (count($flaggedSubstances) ? 'Single substance use' : 'No substance use')
            ];

        case 'mania':
            $interpretation = ($proratedRaw >= 6) 
                ? 'High probability of manic/hypomanic condition (consider treatment/diagnostic workup)'
                : 'Unlikely significant manic symptoms';
            return [
                'raw' => $proratedRaw,
                'interpretation' => $interpretation,
                'clinical_flag' => ($proratedRaw >= 6) 
                    ? 'Score ≥6 indicates possible manic/hypomanic condition'
                    : null
            ];

        default:
            return ['error' => 'Invalid test type'];
    }
}

function calculateTScore($testType, $rawScore) {
    // These would normally come from official conversion tables
    $tScoreTables = [
        'depression' => [8=>20, 12=>30, 16=>40, 20=>50, 24=>60, 28=>70, 32=>80, 36=>90, 40=>100],
        'sleep' => [8=>20, 12=>30, 16=>40, 20=>50, 24=>60, 28=>70, 32=>80, 36=>90, 40=>100],
        'anger' => [5=>20, 8=>30, 11=>40, 14=>50, 17=>60, 20=>70, 23=>80, 25=>90],
        'anxiety' => [7=>20, 11=>30, 15=>40, 19=>50, 23=>60, 27=>70, 31=>80, 35=>90]
    ];
    $table = $tScoreTables[$testType];
    $closest = null;
    
    foreach ($table as $raw => $t) {
        if ($raw <= $rawScore) {
            $closest = $t;
        } else {
            // Linear interpolation between points
            $prevRaw = array_search($closest, $table);
            $nextRaw = $raw;
            $nextT = $t;
            
            if ($prevRaw !== false) {
                $ratio = ($rawScore - $prevRaw) / ($nextRaw - $prevRaw);
                return round($closest + ($nextT - $closest) * $ratio, 1);
            }
            return $t;
        }
    }
    
    return $closest ?? 50; // Default if not found
}

function interpretTScore($tScore) {
    return match(true) {
        $tScore < 55 => 'None to slight',
        $tScore < 60 => 'Mild',
        $tScore < 70 => 'Moderate',
        default => 'Severe'
    };
}

function displayTestResults($testType, $results) { ?>
    <div class="test-results <?= $testType ?>">
        <h4><?= ucfirst($testType) ?> Results</h4>
        
        <?php if (isset($results['error'])): ?>
            <div class="alert alert-danger"><?= $results['error'] ?></div>
        <?php else: ?>
            <div class="score-summary">
                <p>Raw Score: <strong><?= $results['raw'] ?></strong></p>
                
                <?php if (isset($results['t_score'])): ?>
                    <p>T-Score: <?= $results['t_score'] ?></p>
                <?php endif; ?>
                
                <?php if (isset($results['average'])): ?>
                    <p>Average Score: <?= $results['average'] ?></p>
                <?php endif; ?>
                
                <p class="interpretation">
                    Interpretation: <strong><?= $results['interpretation'] ?></strong>
                </p>
            </div>
            
            <?php if (isset($results['clinical_flag']) || isset($results['flag'])): ?>
                <div class="clinical-alert alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= $results['clinical_flag'] ?? $results['flag'] ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($results['items'])): ?>
                <div class="substance-details">
                    <h5>Substance Use Details:</h5>
                    <ul>
                        <?php foreach ($results['items'] as $item): ?>
                            <li>
                                <?= ucfirst($item['substance']) ?>: 
                                <?= $item['frequency'] ?> (Score: <?= $item['score'] ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DSM-5 Level 2 Questionnaire</title>
    <style>
    body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: rgb(235, 250, 247);
    padding: 20px;
    margin: 0;
}

/* General form style */
form {
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    max-width: 850px;
    margin: auto;
}

/* Specific class for mania form if needed */
form.mania-form {
    padding: 20px;
    margin-top: 30px; /* or adjust as needed */
}



/* Headings */
h3 {
    color: #007bff;
    text-align: center;
    margin-bottom: 25px;
}

/* Instruction Box */
.instructions {
    background-color: #f1f8ff;
    padding: 15px;
    border-left: 5px solid #007bff;
    margin-bottom: 25px;
    border-radius: 5px;
}

.instructions ol {
    margin: 0;
    padding-left: 20px;
}

/* Question Blocks */
.question-block {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: #333;
}

/* Alternate background colors */
.question-block:nth-child(odd) {
    background-color: #f8f9fa;
}

.question-block:nth-child(even) {
    background-color: #e9f7ef;
}

.question-block p,
.question {
    font-weight: bold;
    margin-bottom: 10px;
}

/* Labels */
label {
    display: block;
    margin: 6px 0 6px 20px;
}

/* Buttons (Unified) */
button,
.btn-submit {
    background-color: #009879;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 20px;
    display: inline-block;
}

button:hover,
.btn-submit:hover {
    background-color: #007f67;
}

/* Tables */
.test-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 0.9em;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}

.test-table th,
.test-table td {
    padding: 12px 15px;
    text-align: center;
    border: 1px solid #ddd;
}

.test-table th {
    background-color: #009879;
    color: white;
}

.test-table tr {
    border-bottom: 1px solid #dddddd;
}

.test-table tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

.test-table tr:last-of-type {
    border-bottom: 2px solid #009879;
}

.test-table tr:hover {
    background-color: #f1f1f1;
}
.completed-test {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
        }
        .test-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .progress-info {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
.test-status {
    background: #f8f9fa;
    border-left: 4px solid #6c757d;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.retake-button {
    display: inline-block;
    background: #28a745;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    text-decoration: none;
    margin-top: 10px;
}

.retake-button:hover {
    background: #218838;
}
.test-results-container {
        background-color: #e6f7ff;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
    }
      </style>
</head>
<body>
    <h1>DSM-5 Level 2 Questionnaire</h1>
    <p>Please answer the following questions based on your experience.</p>

    <div class="progress-info">
        Completed <?php echo count($completed_tests); ?> of <?php echo count($level2Tests); ?> tests
    </div>

<?php

if (!empty($level2Tests)):?>
  <?php foreach ($level2Tests as $test):
    // Check if we should show results for this test
        $show_results = isset($_SESSION['test_results']) && $_SESSION['test_results']['type'] == $test; 
        if (isset($completed_tests[$test]) && (!$show_results)):
?>
             
        <div class="completed-test">
                <h3><?= ucfirst(str_replace('_', ' ', $test)) ?></h3>
                <p>✓ You have already completed this test on: <?= date('M j, Y', strtotime($completed_tests[$test])) ?></p>
                
                <?php if (strtotime($completed_tests[$test]) < time() - (7 * 24 * 60 * 60)): ?>
                    <form method="POST">
                        <input type="hidden" name="retake" value="<?= $test ?>">
                        <button type="submit" class="retake-btn">Retake Test</button>
                    </form>
                <?php else: ?>
                    <p>Available for retake on: <?= date('M j, Y', strtotime($completed_tests[$test]) + (7 * 24 * 60 * 60)) ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="test-form">
                <h3><?= ucfirst(str_replace('_', ' ', $test)) ?></h3>
                <?php if ($show_results): ?>
                <div class="test-results-container">
                    <h3>Test Results - <?= ucfirst($test) ?></h3>
                    <p>Your score for the <?= $test ?> test is: <?= $_SESSION['test_results']['score'] ?></p>
                    <?php displayTestResults($test, $_SESSION['test_results']['data']); ?>
                    <?php unset($_SESSION['test_results']); ?>
                </div>
            <?php else: ?>
            
    <?php if ($test == 'depression') 
    {
        $depression_questions = [
            "I felt worthless.",
            "I felt that I had nothing to look forward to.",
            "I felt helpless.", "I felt sad.", "I felt like a failure.",
            "I felt depressed.", "I felt unhappy.", "I felt hopeless."
        ];
               
        echo "<h3>Depression Test</h3>";
        echo "<form method='POST' action='dsm5_level2.php'>";
        echo "<input type='hidden' name='test_type' value='depression'>";

        echo "<table class='test-table'>";
        echo "<tr><th>Question</th><th>never</th><th>Slight</th><th>Mild</th><th>Moderate</th><th>Severe</th>during the past 7 days.Please respond to each item by marking ( or x) one box per row.  </tr>";

        foreach ($depression_questions as $index => $question) {
            echo "<tr>";
            echo "<td>$question</td>";
            for ($i = 0; $i <= 4; $i++) {
                echo "<td><input type='radio' name='q" . ($index + 1) . "' value='$i' required></td>";
            }
            echo "</tr>"; }

        echo "</table>";
        echo "<button type='submit'>Submit</button>";
        echo "</form>";
         } ?>
</div>
  <?php 

    if ($test == 'anxiety')
     { $anxiety_questions = [
        "I felt fearful.", "I felt anxious.", "I felt worried.",
        "I found it hard to focus on anything other than my anxiety.",
        "I felt nervous.", "I felt uneasy.", "I felt tense."
           ];
            echo "<h3>Anxiety Test</h3>";
            echo "<form method='POST' action='dsm5_level2.php'>";
            echo "<input type='hidden' name='test_type' value='anxiety'>";

            echo "<table class='test-table'>";
            echo "<tr><th>Question</th><th>None</th><th>Slight</th><th>Mild</th><th>Moderate</th><th>Severe</th>during the past 7 days. Please respond to each item by marking ( or x) one box per row.  </tr>";

            foreach ($anxiety_questions as $index => $question) {
                echo "<tr>";
                echo "<td>$question</td>";
                for ($i = 0; $i <= 4; $i++) {
                    echo "<td><input type='radio' name='q" . ($index + 1) . "' value='$i' required></td>";
                }
                echo "</tr>";
            }

            echo "</table>";
            echo "<button type='submit'>Submit</button>";
            echo "</form>";

        } if ($test == 'anger') 
        {
            //3. Display Anger questions and options
            $anger_questions = [
                "I was irritated more than people knew.",
                "I felt angry.",
                "I felt like I was ready to explode.", "I was grouchy.", "I felt annoyed."
            ];
            echo "<h3>Anger Test</h3>";
            echo "<form method='POST' action='dsm5_level2.php'>";
            echo "<input type='hidden' name='test_type' value='anger'>";

            echo "<table class='test-table'>";
            echo "<tr><th>Question</th><th>never</th><th>Slight</th><th>Mild</th><th>Moderate</th><th>Severe</th>during the past 7 days.Please respond to each item by marking ( or x) one box per row.  </tr>";

            foreach ($anger_questions as $index => $question) {
                echo "<tr>";
                echo "<td>$question</td>";
                for ($i = 0; $i <= 4; $i++) {
                    echo "<td><input type='radio' name='q" . ($index + 1) . "' value='$i' required></td>";
                }
                echo "</tr>";
            }

            echo "</table>";
            echo "<button type='submit'>Submit</button>";
            echo "</form>";

        }if ($test == 'somatic') 
        {
             //4. Display Somatic questions and options

             $somatic_questions = [
                "Stomach pain.",
                "Back pain.",
                "Pain in your arms, legs, or joints (knees, hips, etc.) ",
                "Menstrual cramps or other problems with your periods WOMEN ONLY ",
                "Headaches.","Chest pain.","Dizziness.","Fainting spells .","Feeling your heart pound or race",
                "Shortness of breath.","Pain or problems during sexual intercourse.",
                "Constipation, loose bowels, or diarrhea","Nausea, gas, or indigestion.",
                "Feeling tired or having low energy.","Trouble sleeping."
                ];

             echo "<h3>Somatic Test</h3>";
             echo "<form method='POST' action='dsm5_level2.php'>";
             echo "<input type='hidden' name='test_type' value='somatic'>";
 
             echo "<table class='test-table'>";
             echo "<tr><th>Question</th><th>Not bothered at all</th><th>Bothered a little</th><th> Bothered a lot</th>During the past 7 days, how much have you been bothered by any of the following problems?</tr>";
 
             foreach ($somatic_questions as $index => $question) {
                 echo "<tr>";
                 echo "<td>$question</td>";
                 for ($i = 0; $i <= 2; $i++) {
                     echo "<td><input type='radio' name='q" . ($index + 1) . "' value='$i' required></td>";
                 }
                 echo "</tr>";
             }
 
             echo "</table>";
             echo "<button type='submit'>Submit</button>";
             echo "</form>";}
if ($test == 'sleep')
            { 
                //7. Display sleep Test Use questions and options

            $sleep_questions = [
                "I had difficulty falling asleep.",
                    "I had difficulty staying asleep.",
                    "I woke up too early and could not get back to sleep.",
                    "My sleep was restless.",
                    "I was dissatisfied with my sleep.",
                    "My sleep problems interfered with my daily functioning.",
                    "My sleep problems made it hard to concentrate.",
                    "I was sleepy during the day."
                ];
                
            echo "<h3> Sleep Disturbance Test</h3>";
            echo "<form method='POST' action='dsm5_level2.php'>";
            echo "<input type='hidden' name='test_type' value='sleep'>";
                // Display questions and options
              
             echo "<table class='test-table'>";
             echo "<tr><th>Question</th><th>Not at all</th><th>One or two days </th><th>Several days</th><th>More than half the days</th><th>Nearly every day</th> during the past 7 days. Please respond to each item by marking ( or x) one box per row. </tr>";
 
             foreach ($sleep_questions as $index => $question) {
                echo "<tr>";
                 echo "<td>$question</td>";
                 for ($i = 0; $i <= 4; $i++) {
                     echo "<td><input type='radio' name='q" . ($index + 1) . "' value='$i' required></td>";
                 }
                 echo "</tr>";
             }
    
                echo "</table>";
                echo "<button type='submit'>Submit</button>";
                echo "</form>";

            }if ($test=='ocd')
            { 
            //6. Display Repetitive Thoughts and Behaviors Test Use questions and options

            $ocd_questions = [
                1 => [
                    "text" => "On average, how much time is occupied by these thoughts or behaviors each day?",
                    "options" => [
                        0 => "0 — None",
                        1 => "1 — Mild (Less than an hour a day)",
                        2 => "2 — Moderate (1 to 3 hours a day)",
                        3 => "3 — Severe (3 to 8 hours a day)",
                        4 => "4 — Extreme (More than 8 hours a day)"
                    ]
                ],
                2 => [
                    "text" => "How much distress do these thoughts or behaviors cause you?",
                    "options" => [
                        0 => "0 — None",
                        1 => "1 — Mild (Slightly disturbing)",
                        2 => "2 — Moderate (Disturbing but still manageable)",
                        3 => "3 — Severe (Very disturbing)",
                        4 => "4 — Extreme (Overwhelming distress)"
                    ]
                ],
                3 => [
                    "text" => "How hard is it for you to control these thoughts or behaviors?",
                    "options" => [
                        0 => "0 — Complete control",
                        1 => "1 — Much control (Usually able to control them)",
                        2 => "2 — Moderate control (Sometimes able to control them)",
                        3 => "3 — Little control (Infrequently able to control them)",
                        4 => "4 — No control (Unable to control them)"
                    ]
                ],
                4 => [
                    "text" => "How much do these thoughts or behaviors cause you to avoid doing anything, going anyplace, or being with anyone?",
                    "options" => [
                        0 => "0 — No avoidance",
                        1 => "1 — Mild (Occasional avoidance)",
                        2 => "2 — Moderate (Regularly avoid doing these things)",
                        3 => "3 — Severe (Frequent and extensive avoidance)",
                        4 => "4 — Extreme (Nearly complete avoidance; house-bound)"
                    ]
                ],
                5 => [
                    "text" => "How much do these thoughts or behaviors interfere with school, work, or your social or family life?",
                    "options" => [
                        0 => "0 — None",
                        1 => "1 — Mild (Slight interference)",
                        2 => "2 — Moderate (Definite interference, but manageable)",
                        3 => "3 — Severe (Substantial interference)",
                        4 => "4 — Extreme (Near-total interference; incapacitated)"
                    ]
                ],
            ];
            echo "<h3>Repetitive Thoughts and Behaviors Test</h3>";
            echo "<form method='POST' action='dsm5_level2.php'>";
            echo "<input type='hidden' name='test_type' value='ocd'>";

            echo "<table class='test-table'>";
            echo "<tr>
                    <th>Question</th>
                    <th>None (0)</th>
                    <th>Mild (1)</th>
                    <th>Moderate (2)</th>
                    <th>Severe (3)</th>
                    <th>Extreme (4)</th>
                    during the past 7 days.  Please respond to each item by marking ( or x) one box per row.   
                </tr>";

            foreach ($ocd_questions as $qNum => $data) {
                echo "<tr>";
                echo "<td>{$data['text']}</td>";
                foreach ($data['options'] as $value => $label) {
                    // Extract just the severity level for the column header
                    $shortLabel = explode("—", $label)[0]; // Gets "0", "1", etc.
                    echo "<td><input type='radio' name='q$qNum' value='$value' required></td>";
                }
                echo "</tr>";
            }

            echo "</table>";
            echo "<button type='submit' class='btn-submit'>Submit</button>";
            echo "</form>";
        
        }if ($test == 'mania')
        {
                        $mania_questions = [
                            // Question 1
                            "I do not feel happier or more cheerful than usual." => 0,
                            "I occasionally feel happier or more cheerful than usual." => 1,
                            "I often feel happier or more cheerful than usual." => 2,
                            "I feel happier or more cheerful than usual most of the time." => 3,
                            "I feel happier or more cheerful than usual all of the time." => 4,

                            // Question 2
                            "I do not feel more self-confident than usual." => 0,
                            "I occasionally feel more self-confident than usual." => 1,
                            "I often feel more self-confident than usual." => 2,
                            "I frequently feel more self-confident than usual." => 3,
                            "I feel extremely self-confident all of the time." => 4,

                            // Question 3
                            "I do not need less sleep than usual." => 0,
                            "I occasionally need less sleep than usual." => 1,
                            "I often need less sleep than usual." => 2,
                            "I frequently need less sleep than usual." => 3,
                            "I can go all day and all night without any sleep and still not feel tired." => 4,

                            // Question 4
                            "I do not talk more than usual." => 0,
                            "I occasionally talk more than usual." => 1,
                            "I often talk more than usual." => 2,
                            "I frequently talk more than usual." => 3,
                            "I talk constantly and cannot be interrupted." => 4,

                            // Question 5
                            "I have not been more active (either socially, sexually, at work, home, or school) than usual." => 0,
                            "I have occasionally been more active than usual." => 1,
                            "I have often been more active than usual." => 2,
                            "I have frequently been more active than usual." => 3,
                            "I am constantly more active or on the go all the time." => 4
                        ];
            // Group answers in sets of 5
            $question_groups = array_chunk($mania_questions, 5, true);
            ?>

            <!-- Mania Test Form -->
            <form method="POST" action="dsm5_level2.php" class="mania-form">
                <h3>Mania Test</h3>
                <!-- Instructional Text -->
                <div class="instructions" style="background-color:#f8f9fa; padding:5px; border:1px solid #ccc; margin-bottom:10px;">
                    <strong>Please read carefully:</strong>
                    <ol>
                        <li>Read each group of statements/question carefully.</li>
                        <li>Choose the one statement in each group that best describes the way you (the individual receiving care) have been feeling for the past week.</li>
                        <li>Check the box (✓ or x) next to the number/statement selected.</li>
                        <li><strong>Note:</strong> The word <em>“occasionally”</em> means once or twice; <em>“often”</em> means several times or more; and <em>“frequently”</em> means most of the time.</li>
                    </ol>
                </div>
                <input type="hidden" name="test_type" value="mania">

                <?php
                $qNum = 1;
                foreach ($question_groups as $options) {
                    echo "<div class='question-block'>";

                    echo "<p>Question $qNum:</p>";
                    foreach ($options as $text => $value) {
                        echo "<label><input type='radio' name='q$qNum' value='$value' required> $text</label>";
                    }
                    echo "</div>";

                    $qNum++;
                }
                echo "<button type='submit' class='btn-submit'>Submit</button>";
                        echo "</form>";
}?>
         </div>
                <?php endif; ?>
            
                <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No Level 2 tests required based on your Level 1 assessment.</p>
    <?php endif; ?>

</body>
</html>

