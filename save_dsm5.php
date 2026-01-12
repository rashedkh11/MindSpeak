<?php
session_start();
include "db.php"; 

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    die("Error: User is not logged in. Please log in before taking the test.");
}

$user_id = $_SESSION['user_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {

      $answers = [];
      for ($i =1; $i <= 13; $i++) {
          if (!isset($_POST["q$i"])) {
              die("Error: Missing response for question $i.");
          }
          $answers["q$i"] = $_POST["q$i"];
      }
      // Calculate category scores
      $categoryScores = [
          "depression" => max($answers["q1"], $answers["q2"]),
          "anger" => $answers["q3"],
          "mania" => max($answers["q4"], $answers["q5"]),
          "anxiety" => max($answers["q6"], $answers["q7"], $answers["q8"]),
          "somatic" => max($answers["q9"], $answers["q10"]),
          "sleep" => $answers["q11"],
          "ocd" => max($answers["q12"], $answers["q13"]),
      ];
      // Calculate total score
      $totalScore = array_sum($categoryScores);
  
      //  Determine Level 2 tests needed based on the DSM-5 rule
          $level2Tests = [];
  
          foreach ($categoryScores as $category => $score) 
          {
              if ($score >= 2) {
                  $level2Tests[] = $category; 
              }
          }

    $level2TestsJSON = json_encode($level2Tests);
    $answersJSON = json_encode($answers);

    $sql = "INSERT INTO dsm5_v1 
        (user_id, total_score, depression, anger, mania, anxiety, somatic ,sleep, ocd, level2_tests, answers) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("iiiiiiiiiss", 
        $user_id, $totalScore,
        $categoryScores['depression'], $categoryScores['anger'], 
        $categoryScores['mania'], $categoryScores['anxiety'], 
        $categoryScores['somatic'], $categoryScores['sleep'], 
        $categoryScores['ocd'], 
        $level2TestsJSON, $answersJSON
    );

   if ($stmt->execute()) {
    // If any test is needed, go to Level 2
    if (!empty($level2Tests)) {
        $_SESSION['level2_tests'] = $level2Tests;
        header("Location: dsm5_level2.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
} else {
    die("Error saving results: " . $stmt->error);
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>DSM-5 Self-Assessment</title>

 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"> 
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

        <!--POPBOX-->
        <style>
             body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
              margin: 0;
              display: flex;
              align-items: center;
              justify-content: center;
              height: 100vh;
              background-color: #e0f7f9;
        }
        
        .container {
            max-width: 900px;
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .instructions {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        label {
            font-weight: bold;
        }
        .small-text {
            font-size: 12px;
            color: #023438;
        }
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .fade-up.show {
            opacity: 1;
            transform: translateY(0);
         }
          
          .popup-container {
              position: fixed;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              background: rgba(0, 0, 0, 0.5);
              display: flex;
              align-items: center;
              justify-content: center;
          }
          .popup {
              background: #3fbbc0;
              color: white;
              padding: 40px;
              border-radius: 15px;
              text-align: center;
              box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
              width: 400px;
              font-size: 1.5em;
          }
          .close-btn {
              margin-top: 15px;
              background: white;
              color: #3fbbc0;
              border: none;
              padding: 15px;
              cursor: pointer;
              border-radius: 8px;
              font-size: 1em;
          }

          .starter-section {
                width: 100vw;  /* Full viewport width */
                height: 100vh; /* Full viewport height */
                padding: 20px;
                box-sizing: border-box;
                display: none;
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}
        .highlight-row {
            background-color: #e3eceb; /* Light yellow */
            text-align: left;
        }               
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #ffffff; }
            th, td { border: 1px solid #05878b; padding: 8px; text-align: center; }
            th { background-color: #a9dddf; color: #ffffff; }
            .question { text-align: left; color: #000000; }
            button { background-color: #3fbbc0; color: #ffffff; padding: 10px 15px; border: none; cursor: pointer; }
            button:hover { background-color: #3599a8; }
      </style>
  </head>

  <body>
      <div class="popup-container" id="popup">
          <div class="popup">
              <h2>You must answer this test !</h2>
              <p>it consists of two levels.</p>
              <button class="close-btn" onclick="closePopup()">Close</button>
          </div>
      </div>

      <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("starter-section").style.display = "none"; // Hide form initially
    });

    function closePopup() {
        document.getElementById("popup").style.display = "none";
        let form = document.getElementById("starter-section");
        
        form.style.display = "block"; // Show the form
        setTimeout(() => {
            form.style.opacity = "1";  // Fade in
            form.style.transform = "translateY(0)"; // Move up smoothly
        }, 100); }

      </script>

    <!-- Starter Section Section -->
    <section id="starter-section" class="starter-section section">

                <div class="container fade-up" data-aos="fade-up">
                    <h2 style="text-align: center;" >DSM-5 Questionnaire LEVEL ONE </h2>
                    <p class="instructions">Please rate the following items based on your experience over the past two weeks.</p>
                    
                        <form action="" method="post">
                            <table>
                                <tr>
                                    <th class="question"> </th>
                                    <th>None <br><span class="small-text">Not at all</span></th>
                                    <th>Slight <br><span class="small-text">less than a day or two</span></th>
                                    <th>Mild <br><span class="small-text">Several days</span></th>
                                    <th>Moderate<br><span class="small-text">More than half the days</span></th>
                                    <th>Severe <br><span class="small-text">Nearly every day</span></th>

                                </tr>
                                <tr>
                                    <td class="highlight-row">1. Little interest or pleasure in doing things?</td>
                                    <td><input type="radio" name="q1" value="1" required></td>
                                    <td><input type="radio" name="q1" value="2"></td>
                                    <td><input type="radio" name="q1" value="3"></td>
                                    <td><input type="radio" name="q1" value="3"></td>
                                    <td><input type="radio" name="q1" value="4"></td>
                                </tr>
                                
                                <tr>
                                    <td class="highlight-row">2. Feeling down, depressed, or hopeless?</td>
                                    <td><input type="radio" name="q2" value="1" required></td>
                                    <td><input type="radio" name="q2" value="2"></td>
                                    <td><input type="radio" name="q2" value="3"></td>
                                    <td><input type="radio" name="q2" value="4"></td>
                                    <td><input type="radio" name="q2" value="5"></td>
                                </tr>
                                <tr>
                                <td class="question">3. Feeling more irritated, grouchy, or angry than usual? </td>
                                <td><input type="radio" name="q3" value="1" required></td>
                                <td><input type="radio" name="q3" value="2"></td>
                                <td><input type="radio" name="q3" value="3"></td>
                                <td><input type="radio" name="q3" value="4"></td>
                                <td><input type="radio" name="q3" value="5"></td>
                            </tr> <tr>
                                <td class="highlight-row">4. Sleeping less than usual, but still have a lot of energy? </td>
                                <td><input type="radio" name="q4" value="1"required></td>
                                <td><input type="radio" name="q4" value="2"></td>
                                <td><input type="radio" name="q4" value="3"></td>
                                <td><input type="radio" name="q4" value="4"></td>
                                <td><input type="radio" name="q4" value="5"></td>
                            </tr> <tr>
                            <td class="highlight-row">5. Starting lots more projects than usual or doing more risky things than usual? </td>
                            <td><input type="radio" name="q5" value="1"required></td>
                            <td><input type="radio" name="q5" value="2"></td>
                            <td><input type="radio" name="q5" value="3"></td>
                            <td><input type="radio" name="q5" value="4"></td>
                            <td><input type="radio" name="q5" value="5"></td>
                        </tr> <tr>
                            <td class="question">6. Feeling nervous, anxious, frightened, worried, or on edge? </td>
                            <td><input type="radio" name="q6" value="1"required></td>
                            <td><input type="radio" name="q6" value="2"></td>
                            <td><input type="radio" name="q6" value="3"></td>
                            <td><input type="radio" name="q6" value="4"></td>
                            <td><input type="radio" name="q6" value="5"></td>
                        </tr> <tr>
                        <td class="question">7. Feeling panic or being frightened? </td>
                        <td><input type="radio" name="q7" value="1"required></td>
                        <td><input type="radio" name="q7" value="2"></td>
                        <td><input type="radio" name="q7" value="3"></td>
                        <td><input type="radio" name="q7" value="4"></td>
                        <td><input type="radio" name="q7" value="5"></td>
                    </tr> <tr>
                        <td class="question">8. Avoiding situations that make you anxious? </td>
                        <td><input type="radio" name="q8" value="1"required></td>
                        <td><input type="radio" name="q8" value="2"></td>
                        <td><input type="radio" name="q8" value="3"></td>
                        <td><input type="radio" name="q8" value="4"></td>
                        <td><input type="radio" name="q8" value="5"></td>
                    </tr> <tr>
                    <td class="highlight-row">9. Unexplained aches and pains (e.g., head, back, joints, abdomen, legs)? </td>
                    <td><input type="radio" name="q9" value="1"required></td>
                    <td><input type="radio" name="q9" value="2"></td>
                    <td><input type="radio" name="q9" value="3"></td>
                    <td><input type="radio" name="q9" value="4"></td>
                    <td><input type="radio" name="q9" value="5"></td>
                    </tr>

                    <tr>
                        <td class="highlight-row">10. Feeling that your illnesses are not being taken seriously enough? </td>
                        <td><input type="radio" name="q10" value="1"required></td>
                        <td><input type="radio" name="q10" value="2"></td>
                        <td><input type="radio" name="q10"value="3"></td>
                        <td><input type="radio" name="q10" value="4"></td>
                        <td><input type="radio" name="q10" value="5"></td>
                    </tr><tr>
                         <td class="question">11. Problems with sleep that affected your sleep quality over all? </td>
                        <td><input type="radio" name="q11" value="1"required></td>
                        <td><input type="radio" name="q11" value="2"></td>
                        <td><input type="radio" name="q11" value="3"></td>
                        <td><input type="radio" name="q11" value="4"></td>
                        <td><input type="radio" name="q11" value="5"></td>
                    </tr><tr>
                        <td class="highlight-row">12. Unpleasant thoughts, urges, or images that repeatedly enter your mind? </td>
                        <td><input type="radio" name="q12" value="1"required></td>
                        <td><input type="radio" name="q12" value="2"></td>
                        <td><input type="radio" name="q12" value="3"></td>
                        <td><input type="radio" name="q12" value="4"></td>
                        <td><input type="radio" name="q12" value="5"></td>
                    </tr>
                    <tr>
                        <td class="highlight-row">13. Feeling driven to perform certain behaviors or mental acts over and over again? </td>
                        <td><input type="radio" name="q13" value="1"required></td>
                        <td><input type="radio" name="q13" value="2"></td>
                        <td><input type="radio" name="q13" value="3"></td>
                        <td><input type="radio" name="q13" value="4"></td>
                        <td><input type="radio" name="q13" value="5"></td>
                    </tr><tr>
                        </table>
                            <br>
                            <button type="submit" style="display: block; margin: 15px auto; padding: 15px; border: none; border-radius: 8px; font-size: 1em; cursor: pointer;">Submit</button>
                        </form>
                </div>
</section>


    <script>
        $(document).ready(function() {
            AOS.init();
        });
    </script>




</body>

</html>