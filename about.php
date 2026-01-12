<?php 
session_start(); 
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $role = isset($_SESSION['roll']) ? $_SESSION['roll'] : 'User';
    $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/img/random.png';
} else {
    header("Location: login.php");
    exit();
}
if ($role == 'doctor') {
    include('headerd.php');    
}
else {
    include('header.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About MindSpeak - AI-Powered Mental Health Support</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary:rgb(26, 106, 126);
            --secondary:rgb(80, 67, 150);
            --accent: #E53E3E;
            --dark: #2D3748;
            --light: #F7FAFC;
        }
        
        /* Full-width layout */
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container-fluid {
            width: 100%;
            padding: 0;
            margin: 0;
        }
        
        /* Hero Section */
        .about-hero {
            background: linear-gradient(rgba(152, 121, 226, 0.6), rgba(57, 176, 182, 0.6)), url('assets/img/about-hero.jpg');
            background-size: cover;
            background-position: center;
            padding: 100px 0;
            color: white;
            text-align: center;
        }
        
        /* Section Styling */
        .section {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            color: var(--primary);
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        .section-title h2:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 15px auto;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-card {
            border-top: 4px solid var(--primary);
        }
        
        .team-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid var(--primary);
            margin: 0 auto 20px;
        }
        
        /* Background Variations */
        .bg-light {
            background-color: #f8f9fa;
        }
        
        .bg-primary {
            background: linear-gradient(135deg, var(--primary), #6B46C1);
            color: white;
        }
        
        /* Button Styles */
        .btn-primary {
            background: var(--primary);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: #4a4cdf;
            transform: translateY(-3px);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .section {
                padding: 50px 0;
            }
            
            .section-title {
                margin-bottom: 30px;
            }
        }
    </style>
</head>
<body>

<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <h1 class="display-4 mb-3">MindSpeak</h1>
        <p class="lead">Where AI Meets Human Compassion in Mental Health</p>
    </div>
</section>

<!-- Mission Section -->
<section class="section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card p-4">
                    <h2>Our Mission</h2>
                    <p class="lead">At MindSpeak, we believe everyone deserves access to compassionate, evidence-based mental health care—anytime, anywhere.</p>
                    <p>Founded in 2025, we bridge the gap between cutting-edge AI support and licensed human therapists to create a complete emotional wellness ecosystem.</p>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/img/aboutus.png" alt="MindSpeak Team" class="img-fluid rounded-lg shadow">
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose MindSpeak</h2>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-robot fa-3x" style="color: var(--primary);"></i>
                    </div>
                    <h3 class="text-center">AI + Human Support</h3>
                    <p class="text-center">Intelligent chatbot meets professional guidance for complete 24/7 support.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-flask fa-3x" style="color: var(--primary);"></i>
                    </div>
                    <h3 class="text-center">Science-Based</h3>
                    <p class="text-center">Evidence-based therapies grounded in clinical research and DSM-5 standards.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card feature-card p-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-lock fa-3x" style="color: var(--primary);"></i>
                    </div>
                    <h3 class="text-center">Privacy First</h3>
                    <p class="text-center"></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Who We Help -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Who We Help</h2>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-graduation-cap fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Students</h3>
                    <p>Combat academic stress with AI-guided coping tools and connect to youth-specialized therapists.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-briefcase fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Professionals</h3>
                    <p>Beat burnout with data-driven insights and schedule sessions around work commitments.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-user-md fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Therapists</h3>
                    <p>Enhance client care with AI screening and access anonymized trend analytics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Our Services -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-brain fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Individual Therapy</h3>
                    <p>One-on-one sessions tailored to your specific needs and goals.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-users fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Couples Counseling</h3>
                    <p>Improve communication and strengthen your relationship.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-search fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>Cognitive Therapy</h3>
                    <p>Evidence-based approaches for lasting change.</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center">
                    <i class="fas fa-robot fa-3x mb-3" style="color: var(--primary);"></i>
                    <h3>AI Companion</h3>
                    <p>24/7 emotional support between sessions.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- CTA Section -->
<section class="section bg-primary">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Begin Your Healing Journey?</h2>
        <p class="mb-4">Take the first step toward better mental health today</p>
        <a href="register.php" class="btn btn-light btn-lg">Get Started</a>
    </div>
</section>


<!-- Team Section -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Meet Our Team</h2>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center team-card">
                    <img src="assets/img/team-1.jpg" alt="Dr. Polina Zimmerman">
                    <h4>Rashed Khresheh</h4>
                    <p class="text-muted">-</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center team-card">
                    <img src="assets/img/team-2.jpg" alt="Rodolfo Quiros">
                    <h4>Ehdaa Jaafreh</h4>
                    <p class="text-muted">-</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center team-card">
                    <img src="assets/img/team-3.jpg" alt="Elle Hughes">
                    <h4>Mohammed Majali</h4>
                    <p class="text-muted">-</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card p-4 text-center team-card">
                    <img src="assets/img/team-4.jpg" alt="Drew Williams">
                    <h4>Hala Tarawneh</h4>
                    <p class="text-muted"> -</p>
                </div>
            </div>
        </div>
    </div>
</section>



<?php include "footer.php"; ?>

<!-- Scripts -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script>
    new WOW().init();
</script>

</body>
</html>