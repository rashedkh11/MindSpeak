
<?php 
session_start(); // Start the session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $role = isset($_SESSION['roll']) ? $_SESSION['roll'] : 'User';
    $profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'assets/img/random.png';
} else {
    // Redirect to login if not logged in
    header("Location: login.php");
    exit();
}
if ($role == 'doctor') {
    include('headerd.php');    
}
else {
    include('header.php');}?>


     
    <div class="page-title  mt-5">
        <div class="heading">
          <div class="container">
            <div class="row d-flex justify-content-center text-center">
              <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">
                <h1 class="heading-title">Book Now!
                </h1>
                <p class="-0mb">
                  The best consultants and specialists in more than 30 specialties </p>
              </div>
            </div>
          </div>
        </div>

     <div class="row mt-4 justify-content-center">
						

        <div class="col-lg-4 justify-content-center">
            <div class="rounded-lg service-card-2">
                <a href="singlesession.html" class="text-decoration-none text-reset" data-aos="fade-up" data-aos-delay="400">
                    <!-- Image -->
                        <img src="assets/img/s4.jpg" class="w-100 rounded-lg shadow-sm">
                    <!-- Title & Description -->
                    <div class="service-content p-3 text-center">
                        <h3 class="font-weight-bold text-primary">Find a Therapist</h3>
                        <p class="text-muted">Expert guidance for your mental well-being.</p>
                    </div>
                    <!-- Location & Price -->
                    <div class="clini-infos px-3 pb-3 text-center">
                        <a class="btn btn-primary" href="search.php">View All</a>
                    </div>
                </a>
            </div>
        </div>
    
            <div class="col-lg-4 ">
                <div class="rounded-lg service-card-2">
                    <a href="singlesession.html" class="text-decoration-none text-reset" data-aos="fade-up" data-aos-delay="400">
                        <!-- Image -->
                            <img src="assets/img/s5.jpg" class="w-100 rounded-lg shadow-sm">
                        <!-- Title & Description -->
                        <div class="service-content p-3 text-center">
                            <h3 class="font-weight-bold text-primary ">Special Sessions</h3>
                            <p class="text-muted">Tailored therapy for unique needs</p>
                        </div>
                        <!-- Location & Price -->
                        <div class="clini-infos px-3 pb-3 text-center">
                            <a class="btn btn-primary" href="sessions.php">View All</a>

                        </div>
                    </a>
                </div>
            </div>
            </div>


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
		
		<!-- Slick JS -->
		<script src="assets/js/slick.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
    AOS.init(); // Initialize AOS
</script>

		
	</body>

<!-- doccure/index.html  30 Nov 2019 04:12:03 GMT -->
</html>