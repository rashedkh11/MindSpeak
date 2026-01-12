<?php
session_start();

require 'db.php';

include('header.php'); ///////////////////////////////////////////////?>

			<!-- Page Content -->
			
				   <!-- SERVICES Start -->
                   <div class="container-fluid">
                    <div class="container p-3">
                      <h2 class="text-center"><br>Therapies</h2><br>
                      <h6 class="text-center">Discover our expert-led therapy sessions tailored to support your growth</h6>
              
                      <!-- Therapies Row 1 -->
                      <?php


// Fetch therapy sessions from the database
$sql = "SELECT * FROM SESSIONS WHERE status = 'active' ORDER BY date_time ASC";
$result = $conn->query($sql);
?>

<div class="row mt-4">
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="col-lg-4">
            <div class="rounded-lg service-card-2">
                <a href="singles.php?id=<?= $row['id'] ?>" class="text-decoration-none text-reset">
                    <!-- Image -->
                    <img src="<?= htmlspecialchars($row['image']) ?>" class="w-100 rounded-lg shadow-sm">
                    
                    <!-- Title & Description -->
                    <div class="service-content p-3">
                        <h4 class="font-weight-bold text-primary"><?= htmlspecialchars($row['session_name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($row['description']) ?></p>
                    </div>

                    <!-- Location & Price -->
                    <div class="clini-infos px-3 pb-3">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-map-marker-alt text-danger"></i> <?= htmlspecialchars($row['location']) ?></li>
                            </li>
                        </ul>
                    </div>
                </a>
            </div>
        </div>
    <?php } ?>
</div>



                    </div>
                  </div>
                  <!-- SERVICES End -->

			</div>		
			<!-- /Page Content -->
   
			<!-- Footer -->
			<?PHP include('footer.php'); /////////////////////////////////////////////// ?>
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

<!-- doccure/blank-page.html  30 Nov 2019 04:12:20 GMT -->
</html>