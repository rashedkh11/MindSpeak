<?php
include('header.php'); 


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

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $role = isset($_SESSION['roll']) ? $_SESSION['roll'] : 'User';
    $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/img/random.png';
	echo '<P>' . 'welcome ' . $username ." : ". $role . '</P>';
} else {
    header("Location: login.php");
    exit();
}

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

$stmt = $pdo->prepare($query);
$stmt->execute($params); 
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html> 
<html lang="en">

<head>
   <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>MindSpeak </title>
    
    <!-- Favicons -->
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Owl Carousel CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
		
		<!-- Fontawesome CSS -->
		<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
		<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
		
		
		<!-- index CSS -->
		<link rel="stylesheet" href="assets/css/indexst.css">

        <!-- Owl Carousel JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
		
  <style>
        /* Custom styles for the page */
        .hero-section {
            background: linear-gradient(rgba(15, 114, 114, 0.7), rgba(186, 215, 219, 0.7)), url('assets/img/home1.jpg');
            background-size: cover;
            background-position: center;
            padding: 120px 0;
            color: white;
            text-align: center;
        }
        
        .therapist-card .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .therapist-card .card:hover {
            transform: translateY(-10px);
        }
        
        .therapist-card img {
            height: 250px;
            object-fit: cover;
        }
        
        .rating {
            color: #FFC107;
        }
        /* How It Works Section */
.how-it-works-section {
  padding: 5rem 0;
  background-color: #f8f9fa;
  position: relative;
}

.how-it-works-section h2 {
  color: var(--heading-color);
  font-size: 2.5rem;
  margin-bottom: 1rem;
  position: relative;
  text-align: center;
}

.how-it-works-section h2:after {
  content: '';
  display: block;
  width: 80px;
  height: 4px;
  background: var(--accent-color);
  margin: 15px auto;
}

.how-it-works-section p.text-center {
  color: color-mix(in srgb, var(--default-color), transparent 40%);
  margin-bottom: 3rem;
}

/* Timeline */
.vertical-timeline {
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 20px 0;
}

.timeline-item {
  position: relative;
  margin-bottom: 40px;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.timeline-dot {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: white;
  border: 3px solid var(--accent-color);
  color: var(--accent-color);
  font-weight: bold;
  font-size: 1.2rem;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  z-index: 2;
}

.timeline-item.active .timeline-dot {
  background: var(--accent-color);
  color: white;
}

.timeline-item:not(:last-child):after {
  content: '';
  position: absolute;
  left: 50%;
  top: 50px;
  bottom: -30px;
  width: 2px;
  background: var(--accent-color);
  transform: translateX(-50%);
  z-index: 1;
}

/* How It Works Boxes */
.how-it-works-box {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  margin-bottom: 30px;
  display: flex;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-left: 4px solid var(--accent-color);
}

.how-it-works-box:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.how-it-works-box-detail {
  flex: 1;
  padding-right: 30px;
}

.how-it-works-box-detail h3 {
  color: var(--heading-color);
  font-weight: 700;
  margin-bottom: 15px;
  font-size: 1.5rem;
}

.how-it-works-box-detail p {
  color: color-mix(in srgb, var(--default-color), transparent 30%);
  line-height: 1.7;
  margin-bottom: 15px;
}

.how-it-works-box-detail ul {
  padding-left: 20px;
  color: color-mix(in srgb, var(--default-color), transparent 20%);
}

.how-it-works-box-detail ul li {
  margin-bottom: 8px;
}

.how-it-works-sequence img {
  border-radius: 8px;
  max-width: 250px;
  height: auto;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

/* Final CTA Box */
.how-it-works-box-final {
  background: linear-gradient(135deg, var(--accent-color), #1977cc);
  color: white;
  text-align: center;
  flex-direction: column;
  border-left: none !important;
  padding: 50px 30px !important;
}

.how-it-works-box-final h3 {
  color: white !important;
  font-size: 2rem;
  margin-bottom: 1.5rem;
}

.how-it-works-box-final p {
  color: rgba(255,255,255,0.9) !important;
  font-size: 1.1rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

/* Hero Button */
.hero-btn {
  background: white;
  color: var(--accent-color);
  border: none;
  padding: 12px 30px;
  font-weight: 700;
  border-radius: 50px;
  transition: all 0.3s ease;
  display: inline-block;
  margin-top: 20px;
  text-decoration: none;
}

.hero-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.2);
  color: var(--accent-color);
}

/* Responsive Adjustments */
@media (max-width: 992px) {
  .how-it-works-box {
    flex-direction: column;
    text-align: center;
  }
  
  .how-it-works-box-detail {
    padding-right: 0;
    margin-bottom: 20px;
  }
  
  .how-it-works-sequence img {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .vertical-timeline {
    display: none;
  }
  
  .how-it-works-section h2 {
    font-size: 2rem;
  }
  
  .how-it-works-box {
    padding: 20px;
  }
  
  .how-it-works-box-final {
    padding: 30px 20px !important;
  }
  
  .how-it-works-box-final h3 {
    font-size: 1.5rem;
  }
}
   
    </style>
      <?php include('chat.php'); ///////////////////////////////////////////////?>

</head>
 
<body>
    <!-- Main Wrapper -->
    <div class="main-wrapper">

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <h1 class="display-4 mb-4">Your Mental Wellness Journey Starts Here</h1>
                        <p class="lead mb-5">Analyze. Understand. Heal. Start your journey toward better mental health today.</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <?php if(!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" class="btn btn-light btn-lg px-4">Get Started</a>
                                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Login</a>
                            <?php else: ?>
                                <a href="patient-dashboard.php" class="btn btn-light btn-lg px-4">My Dashboard</a>
                                <a href="app0but.php" class="btn btn-outline-light btn-lg px-4">Book Session</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
<section id="features" class="features section py-5">>
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Why Do Clients Choose MindSpeak ?</h2>
            <p>Experience mental health support that understands you</p>
        </div>

        <div class="features-container">            
            <div class="feature professional" data-aos="zoom-in" data-aos-delay="100">
                <div class="feature-icon">
                    <img src="assets/img/int.png" alt="AI Powered" loading="lazy">
                </div>
                <h2>It's <span>intelligent</span></h2>
                <p>Our advanced AI analyzes emotions and tracks mood patterns using NLP, making self-awareness and early intervention effortless.</p>
                <div class="feature-wave"></div>
            </div>


            <div class="feature inclusive" data-aos="zoom-in" data-aos-delay="200">
                <div class="feature-icon">
                    <img src="assets/img/inc.png" alt="Inclusive" loading="lazy">
                </div>
                <h2>It's <span>inclusive</span></h2>
                <p>A safe space for all individuals and professionals, enhancing mental health care with personalized, AI-driven insights.</p>
                <div class="feature-wave"></div>
            </div>


            <div class="feature supportive" data-aos="zoom-in" data-aos-delay="300">
                <div class="feature-icon">
                    <img src="assets/img/supp.png" alt="Supportive" loading="lazy">
                </div>
                <h2>It's <span>supportive</span></h2>
                <p>24/7 emotional guidance with mood tracking and personalized recommendations for continuous care.</p>
                <div class="feature-wave"></div>
            </div>


            <div class="feature professional" data-aos="zoom-in" data-aos-delay="400">
                <div class="feature-icon">
                    <img src="assets/img/pro.png" alt="Professional" loading="lazy">
                </div>
                <h2>It's <span>professional</span></h2>
                <p>Connect with licensed specialists who provide expert-backed insights in a completely secure environment.</p>
                <div class="feature-wave"></div>
            </div>
        </div>
    </div>
</section>


<!-- clients Section -->
<section id="process" class="process section">
    <div class="container">
        <div class="section-title" data-aos="fade-up">
            <h2>Who We Help</h2>
            <p>Three simple steps to better mental health</p>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="process-step text-center">
                    <div class="step-icon-wrapper">
                        <div class="step-number">1</div>
                        <div class="step-icon">
                            <i class="fas fa-clipboard-user"></i>
                        </div>
                    </div>
                    <h3>Individuals & Students</h3>
                    <p>Whether you're navigating life changes, stress, or emotional struggles — MindSpeak offers tools to understand and improve your mental health journey</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="process-step text-center">
                    <div class="step-icon-wrapper">
                        <div class="step-number">2</div>
                        <div class="step-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <h3>Professionals</h3>
                    <p>Therapists and clinicians can use our platform to access assessments, monitor client progress, and connect with those in need of care..</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="process-step text-center">
                    <div class="step-icon-wrapper">
                        <div class="step-number">3</div>
                        <div class="step-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <h3>Therapists</h3>
                    <p>Enhance client care with AI screening and access anonymized trend analytics.</p>
                </div>
            </div>
        </div>
    </div>
</section>

    <section id="how-it-works" class="how-it-works-section">
        <div class="container">
            <div class="row">
                <div class="col animate__animated animate__fadeInUp">
                    <h2 class="text-center">
                        How MindSpeak Works
                    </h2>
                    <p class="text-center text-muted mb-5">Simple steps to start your mental wellness journey</p>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="d-none d-sm-flex col-sm-2 justify-content-center">
                    <div class="vertical-timeline">
                        <div class="timeline-item active" data-step="1">
                            <span class="timeline-dot">1</span>
                        </div>
                        <div class="timeline-item" data-step="2">
                            <span class="timeline-dot">2</span>
                        </div>
                        <div class="timeline-item" data-step="3">
                            <span class="timeline-dot">3</span>
                        </div>
                        <div class="timeline-item timeline-dot-hiwell" data-step="4">
                            <span class="timeline-dot">
                                <i class="fas fa-heart"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-sm-10">
                    <div class="row">
                        <div class="col-md-12 mb-4 animate__animated animate__fadeInRight">
                            <div class="how-it-works-box">
                                <div class="how-it-works-box-detail">
                                    <h3> Take the Initial Assessment</h3>
                                    <p>Start by answering a few simple questions to help us understand your mental wellness needs.</p>
                                    <ul class="mt-3">
                                        <li>Answer a few simple questions</li>
                                        <li>Our AI matches you with compatible therapists</li>
                                        <li>Review therapist profiles and specialties</li>
                                    </ul>
                                </div>
                                <div class="how-it-works-sequence">
                                    <img loading="lazy" src="assets/img/dsmcta.jpeg" 
                                         class="img-fluid" 
                                         alt="Therapist matching process" 
                                         width="250" 
                                         height="200">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-4 animate__animated animate__fadeInRight animate__delay-1s">
                            <div class="how-it-works-box">
                                <div class="how-it-works-box-detail">
                                    <h3>Get Matched with a Therapist</h3>
                                    <p>Our AI recommends licensed therapists tailored to you. Enjoy a free 15-minute consultation to find the right fit.</p>
                                    <ul class="mt-3">
                                        <li>Free introductory session</li>
                                        <li>Discuss your goals and expectations</li>
                                        <li>Assess personal connection and comfort level</li>
                                    </ul>
                                </div>
                                <div class="how-it-works-sequence">
                                    <img loading="lazy" src="assets/img/dsmcta.jpeg" 
                                         class="img-fluid" 
                                         alt="Video therapy session" 
                                         width="250" 
                                         height="200">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-4 animate__animated animate__fadeInRight animate__delay-2s">
                            <div class="how-it-works-box">
                                <div class="how-it-works-box-detail">
                                    <h3>Start Your Therapy Journey</h3>
                                    <p>Begin your personalized therapy process with the licensed psychologist you select, through secure video, chat, or voice sessions.</p>
                                    <ul class="mt-3">
                                        <li>Schedule sessions at your convenience</li>
                                        <li>Access therapy from anywhere</li>
                                        <li>24/7 AI support between sessions</li>
                                    </ul>
                                </div>
                                <div class="how-it-works-sequence">
                                    <img loading="lazy" src="assets/img/dsmcta.jpeg" 
                                         class="img-fluid" 
                                         alt="Happy person after therapy" 
                                         width="250" 
                                         height="200">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-4 animate__animated animate__fadeInUp animate__delay-3s">
                            <div class="how-it-works-box how-it-works-box-final">
                                <div class="how-it-works-box-detail py-3">
                                    <h3>Take the first step toward better mental health</h3>
                                    <p class="mb-4">Your journey to wellness starts here</p>
                                    <a href="app0but.php" 
                                       class="btn hero-btn"
                                       data-event-name="sign_up_button_cta">
                                        Start Your Free Consultation
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<!-- DSM-5 Test Section -->
<section id="dsm5-test" class="section bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                <img src="assets\img\why ms.svg" style="width: 450px; height: auto;" alt="DSM-5 Test" class="img-fluid">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="ps-lg-5">
                    <h2 class="mb-4">Take Our DSM-5 Based Assessment</h2>
                    <p class="lead mb-4">Quickly identify your psychological and emotional needs with our clinically validated test.</p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Based on DSM-5 criteria</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Confidential and secure</li>
                        <li class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i> Personalized results</li>
                    </ul>
                    <a href="save_dsm5.php" class="btn btn-primary btn-lg px-4">Start Assessment Now</a>
                </div>
            </div>
        </div>
    </div>


<!-- Therapists Section -->
<section class="py-5 bg-light">
    <div class="container">
        <!-- Section Title -->
        <div class="section-title" data-aos="fade-up">
            <h2>Our Licensed Professionals</h2>
            <p>Connect with experienced mental health specialists</p>
        </div>
        
        
        <?php if(!empty($doctors)): ?>
        <div class="owl-carousel owl-theme therapist-carousel">
            <?php foreach ($doctors as $doctor): 
                $full_name = htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']);
                $profile_image = !empty($doctor['profile_image']) ? htmlspecialchars($doctor['profile_image']) : 'assets/img/random.png';
                $rating = isset($doctor['rating']) ? (float)$doctor['rating'] : 0;
                $reviews = isset($doctor['reviews']) ? (int)$doctor['reviews'] : 0;
            ?>
            <div class="therapist-card">
                <div class="card h-100">
                    <img src="<?= $profile_image ?>" 
                         class="card-img-top" 
                         alt="<?= $full_name ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $full_name ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($doctor['specialization']) ?></p>
                        <div class="rating mb-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?= $i <= $rating ? '' : '-o' ?>"></i>
                            <?php endfor; ?>
                            <span class="small text-muted">(<?= $reviews ?>)</span>
                        </div>
                        <a href="doctor-profile.php?doctor_id=<?= $doctor['doctor_id'] ?>" class="btn btn-sm btn-outline-primary w-100">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="search.php" class="btn btn-primary">Browse All Professionals</a>
        </div>
        <?php else: ?>
        <div class="alert alert-info text-center">No featured professionals available at the moment.</div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section id="cta" class="cta section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start" data-aos="fade-right">
                <h3>Ready to Take Control of Your Mental Health?</h3>
                <p class="mb-lg-0">Join thousands who've found support through MindSpeak. Your journey starts here.</p>
            </div>
            <div class="col-lg-4 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'register.php' ?>" 
                   class="cta-btn">
                   <?= isset($_SESSION['user_id']) ? 'Go to Dashboard' : 'Get Started Now' ?>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- Blog Section -->

<section id="blog" class="blog section">
    <div class="container">
        <!-- Section Title -->
        <div class="section-title" data-aos="fade-up">
            <h2>Mental Health Resources</h2>
            <p>Latest Articles &amp; Self-Care Tips</p>
        </div>

        <div class="row gy-4">
            <?php
            // Fetch latest blog posts from database using your suggested query structure
            $blog_query = "SELECT id, title, image, created_at 
                          FROM blog_posts 
                          ORDER BY created_at DESC 
                          LIMIT 3";
            $blog_stmt = $pdo->prepare($blog_query);
            $blog_stmt->execute();
            $blog_posts = $blog_stmt->fetchAll();

            if (!empty($blog_posts)) {
                foreach ($blog_posts as $post) {
                    // Format date
                    $publish_date = new DateTime($post['created_at']);
                    $day = $publish_date->format('d');
                    $month_year = $publish_date->format('M Y');
                    
                    // Default image if none provided
                    $image_path = !empty($post['image']) 
                                ? htmlspecialchars($post['image'])
                                : 'assets/img/blog/default.jpg';
                    ?>
                    <div class="col-lg-4 col-md-6" data-aos="fade-up">
                        <div class="blog-card">
                            <div class="blog-img">
                                <img src="<?= $image_path ?>" class="img-fluid" alt="<?= htmlspecialchars($post['title']) ?>">
                                <div class="blog-date">
                                    <span><?= $day ?></span>
                                    <span><?= $month_year ?></span>
                                </div>
                            </div>
                            <div class="blog-content">
                                <h3>
                                    <a href="blog-single.php?id=<?= $post['id'] ?>">
                                        <?= htmlspecialchars($post['title']) ?>
                                    </a>
                                </h3>
                                <a href="blog-single.php?id=<?= $post['id'] ?>" class="read-more">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center"><p>No blog posts available at the moment.</p></div>';
            }
            ?>
        </div>

        <div class="text-center mt-4">
            <a href="blog.php" class="btn btn-primary">View All Articles</a>
        </div>
    </div>
</section><!-- End Blog Section -->


<!-- FAQ Section -->
<section id="faq" class="section bg-light">
    <div class="container">
        <div class="section-title text-center mb-5">
            <h2>Frequently Asked Questions</h2>
            <p class="lead">Find answers to common questions about MindSpeak</p>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <!-- FAQ Item 1 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">How does MindSpeak's AI chatbot work?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>Our AI chatbot uses natural language processing to understand your emotions and provide supportive responses. It analyzes your mood patterns over time and can suggest helpful resources or recommend when to consult a professional.</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">Is my personal information kept confidential?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>Absolutely. We use end-to-end encryption for all conversations and adhere to strict privacy policies. Your data is never shared with third parties without your explicit consent.</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">How do I book a session with a therapist?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>You can browse our licensed professionals, view their profiles and availability, and book sessions directly through the platform. We'll send you reminders and provide a secure video conferencing link for your appointment.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <!-- FAQ Item 4 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">What's the cost of using MindSpeak?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>Basic features including the AI chatbot and mood tracking are free. Professional therapy sessions are priced individually by each practitioner, typically ranging from $50-$150 per session.</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">Can I use MindSpeak in an emergency?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>While we provide mental health support, we're not an emergency service. In crisis situations, please contact your local emergency services or crisis hotline immediately.</p>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="faq-item mb-3">
                    <div class="faq-question d-flex justify-content-between align-items-center p-3 bg-white rounded">
                        <h5 class="mb-0">How accurate is the DSM-5 test?</h5>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer p-3 bg-white rounded mt-1">
                        <p>Our test is based on DSM-5 criteria but is not a diagnostic tool. It helps identify areas of concern that you may want to discuss with a professional for proper evaluation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="300">
            <p>Still have questions? <a href="contact.php" class="text-primary">Contact our support team</a></p>
        </div>
</section>

<!-- Contact Us CTA Section -->
<section id="contact-cta" class="cta section" style="background: linear-gradient(135deg, rgba(44, 73, 100, 0.9) 0%, rgba(23, 54, 81, 0.9) 100%), url('assets/img/contact-bg.jpg') center/cover no-repeat fixed;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-center text-lg-start" data-aos="fade-right">
                <h3>Have Questions? We're Here to Help</h3>
                <p class="mb-lg-0">Our support team is available to answer your questions and help you get started on your mental wellness journey.</p>
            </div>
            <div class="col-lg-4 text-center text-lg-end" data-aos="fade-left" data-aos-delay="200">
                <a href="contact.php" class="cta-btn">Contact Us Now</a>
            </div>
        </div>
    </div>
</section>
			
    <!-- /Main Wrapper -->
  
    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    
    <!-- Owl Carousel JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <script>
    $(document).ready(function(){
        // Initialize therapist carousel
        $('.therapist-carousel').owlCarousel({
            loop: true,
            margin: 20,
            nav: true,
            dots: true,
            autoplay: true,
            responsive: {
                0: { items: 1 },
                600: { items: 2 },
                1000: { items: 3 }
            }
        });
        
        // FAQ functionality
        $('.faq-question').click(function(){
            $(this).parent().toggleClass('active').siblings().removeClass('active');
        });
    });
    </script>
</body>
</html>
	



<style>
.faq-question {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid rgba(26, 156, 161, 0.2);
}

.faq-question:hover {
    background-color: rgba(26, 156, 161, 0.05);
}

.faq-question i {
    transition: transform 0.3s ease;
}

.faq-item.active .faq-question i {
    transform: rotate(180deg);
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
    border: 1px solid transparent;
}

.faq-item.active .faq-answer {
    max-height: 300px;
    border-color: rgba(26, 156, 161, 0.2);
}
</style>
			</div>
						</div>
				   </div>
				</div>
			</section>
			<!-- /Popular Section -->

                </div>
        
              </div>
        
            </section><!-- /Faq Section -->
        
            
        
                      </div>
                      <div class="swiper-pagination"></div>
                    </div>
        
                  </div>
        
                </div>
        
              </div>
          </main>
        
      <!-- Footer -->
      <?php include "footer.php"?>
			<!-- /Footer -->
		   
	   </div>
       <!-- Scroll Top -->
          <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
        
          <!-- Preloader -->
          <div id="preloader"></div>
        
          <!-- Vendor JS Files -->
          <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
          <script src="assets/vendor/php-email-form/validate.js"></script>
          <script src="assets/vendor/aos/aos.js"></script>
          <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
          <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
          <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
        
          <!-- Main JS File -->
          <script src="assets/js/main.js"></script>
          <script src="assets/js/counter.js"></script>
	   <!-- /Main Wrapper -->
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
    <!-- Then Owl Carousel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Slick JS -->
		<script src="assets/js/slick.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
		<script>/*
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Close all other FAQ items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
});*/

$(document).ready(function(){
    // Initialize therapist carousel
    $('.therapist-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        responsive: {
            0: { items: 1 },
            600: { items: 2 },
            1000: { items: 3 }
        }
    });
    
    // FAQ functionality - single implementation
    $('.faq-question').click(function(){
        $(this).parent().toggleClass('active').siblings().removeClass('active');
    });
});
/*
$(document).ready(function(){
    // Initialize therapist carousel
    $('.therapist-carousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        responsive: {
            0: { items: 1 },
            600: { items: 2 },
            1000: { items: 3 }
        }
    });
    
    // Remove or fix conflicting carousel initializations
    $(".hero-slider").owlCarousel({
        loop: true,
        autoplay: true,
        smartSpeed: 500,
        autoplayTimeout: 3500,
        items: 1,
        nav: true,
        navText: ['<i class="fa fa-angle-left" aria-hidden="true"></i>', '<i class="fa fa-angle-right" aria-hidden="true"></i>'],
        dots: false
    });
});*/
		/* Template Name: Medilab
            */
            
            (function() {
              "use strict";
            
              /**
               * Apply .scrolled class to the body as the page is scrolled down
               */
              function toggleScrolled() {
                const selectBody = document.querySelector('body');
                const selectHeader = document.querySelector('#header');
                if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
                window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
              }
            
              document.addEventListener('scroll', toggleScrolled);
              window.addEventListener('load', toggleScrolled);
            
              
            
              /**
               * Scroll top button
               */
              let scrollTop = document.querySelector('.scroll-top');
            
              function toggleScrollTop() {
                if (scrollTop) {
                  window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
                }
              }
              scrollTop.addEventListener('click', (e) => {
                e.preventDefault();
                window.scrollTo({
                  top: 0,
                  behavior: 'smooth'
                });
              });
            
              window.addEventListener('load', toggleScrollTop);
              document.addEventListener('scroll', toggleScrollTop);
            
              /**
               * Animation on scroll function and init
               */
              function aosInit() {
                AOS.init({
                  duration: 600,
                  easing: 'ease-in-out',
                  once: true,
                  mirror: false
                });
              }
              window.addEventListener('load', aosInit);
            
              /**
               * Initiate glightbox
               */
              const glightbox = GLightbox({
                selector: '.glightbox'
              });
            
         

        // Select the answer content inside the clicked faq-item
        let answer = faqItem.querySelector('.faq-content');
        
        if (faqItem.classList.contains('faq-active')) {
            answer.style.maxHeight = answer.scrollHeight + "px"; // Expand
            answer.style.opacity = "1"; 
        } else {
            answer.style.maxHeight = "0"; // Collapse
            answer.style.opacity = "0"; 
        }
    });


            
              /**
               * Init swiper sliders
               */
              function initSwiper() {
                document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
                  let config = JSON.parse(
                    swiperElement.querySelector(".swiper-config").innerHTML.trim()
                  );
            
                  if (swiperElement.classList.contains("swiper-tab")) {
                    initSwiperWithCustomPagination(swiperElement, config);
                  } else {
                    new Swiper(swiperElement, config);
                  }
                });
              }
            
              window.addEventListener("load", initSwiper);
            
              /**
               * Correct scrolling position upon page load for URLs containing hash links.
               */
              window.addEventListener('load', function(e) {
                if (window.location.hash) {
                  if (document.querySelector(window.location.hash)) {
                    setTimeout(() => {
                      let section = document.querySelector(window.location.hash);
                      let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
                      window.scrollTo({
                        top: section.offsetTop - parseInt(scrollMarginTop),
                        behavior: 'smooth'
                      });
                    }, 100);
                  }
                }
              });
            
              /**
               * Navmenu Scrollspy
               */
              let navmenulinks = document.querySelectorAll('.navmenu a');
            
              function navmenuScrollspy() {
                navmenulinks.forEach(navmenulink => {
                  if (!navmenulink.hash) return;
                  let section = document.querySelector(navmenulink.hash);
                  if (!section) return;
                  let position = window.scrollY + 200;
                  if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
                    document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));
                    navmenulink.classList.add('active');
                  } else {
                    navmenulink.classList.remove('active');
                  }
                })
              }
              window.addEventListener('load', navmenuScrollspy);
              document.addEventListener('scroll', navmenuScrollspy);
            
            })();

            // Initialize AOS animations for these sections
document.addEventListener('DOMContentLoaded', function() {
    // Process step animations
    const processSteps = document.querySelectorAll('.process-step');
    processSteps.forEach(step => {
        step.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
        });
        step.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });

    // CTA button animation
    const ctaBtn = document.querySelector('.cta-btn');
    if(ctaBtn) {
        ctaBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        ctaBtn.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.faq-item h3').forEach(faqQuestion => {
        faqQuestion.addEventListener('click', function() {
            const faqItem = this.parentElement;
            const isActive = faqItem.classList.contains('active');
            
            // Close all FAQ items first
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open the clicked one if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
            }
        });
    });
});
             

		</script>
          
        </body>
        
        </html>