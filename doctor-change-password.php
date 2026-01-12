<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}include('db.php'); 



$error = "";
$success = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; 
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $stmt = $conn->prepare("SELECT pass FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();
        if (!password_verify($old_password, $hashed_password)) {
            $error = "Old password is incorrect.";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET pass = ? WHERE id = ?");
            $stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($stmt->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>


<?php include('get_user_doc.php'); ///////////////////////////////////////////////?>
			<!-- Header -->
<?php include('headerd.php'); ///////////////////////////////////////////////?>
			<!-- /Header -->
		
			
			<!-- Page Content -->
			<div class="content">
				<div class="container-fluid">

					<div class="row">
							
							<!-- Profile Sidebar -->
							<?php include('profilesidebard.php'); ///////////////////////////////////////////////?>
						<!-- / Profile Sidebar -->
							
						<div class="col-md-7 col-lg-8 col-xl-9">
							<div class="card">
								<div class="card-body">
									<div class="row">
										<div class="col-md-12 col-lg-6">
										
											<!-- Change Password Form -->
											<?php if (!empty($error)): ?>
          									  <div class="alert alert-danger"><?php echo $error; ?></div>
														<?php endif; ?>

														<?php if (!empty($success)): ?>
															<div class="alert alert-success"><?php echo $success; ?></div>
														<?php endif; ?>

												<form method="POST" action="">
													<div class="form-group">
														<label for="old_password">Old Password</label>
														<input type="password" name="old_password" class="form-control" required>
													</div>
													<div class="form-group">
														<label for="new_password">New Password</label>
														<input type="password" name="new_password" class="form-control" required>
													</div>
													<div class="form-group">
														<label for="confirm_password">Confirm Password</label>
														<input type="password" name="confirm_password" class="form-control" required>
													</div>
													<div class="submit-section">
														<button type="submit" class="btn btn-primary">Save Changes</button>
													</div>
													    <input type="hidden" name="csrf_token" value="<?php require_once 'csrf_helper.php'; echo getCSRFToken(); ?>">

												</form>
											<!-- /Change Password Form -->
											
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
	  
		<!-- jQuery -->
		<script src="assets/js/jquery.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		
		<!-- Sticky Sidebar JS -->
        <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
        <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
		
		<!-- Custom JS -->
		<script src="assets/js/script.js"></script>
		
	</body>

<!-- doccure/doctor-change-password.html  30 Nov 2019 04:12:36 GMT -->
</html>