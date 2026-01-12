
<div class="col-md-5 col-lg-4 col-xl-3 theiaStickySidebar">

<div class="profile-sidebar">
    <div class="widget-profile pro-widget-content">
            <div class="profile-info-widget">
                <a href="#" class="booking-doc-img">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="User Image">
                </a>
                <div class="profile-det-info">
                    <h3>DR . <?php echo htmlspecialchars($full_name); ?></h3>
                    <div class="patient-details">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> <?php echo ($country !== "N/A")? "$country": "country not available";?></h5>
                    </div>
                </div>
                </div>
        </div>

    <div class="dashboard-widget">
    <nav class="dashboard-menu">
										<ul>
											<li >
												<a href="doctor-dashboard.php">
													<i class="fas fa-columns"></i>
													<span>Dashboard</span>
												</a>
											</li>
											<li>
												<a href="appointments.php">
													<i class="fas fa-calendar-check"></i>
													<span>REQ to be my Patients</span>
												</a>
											</li>
											<li>
												<a href="my-patients.php">
													<i class="fas fa-user-injured"></i>
													<span>My Patients</span>
												</a>
											</li>
											
											<li>
												<a href="create_session.php">
													<i class="fas fa-file-invoice"></i>
													<span>create new session</span>
												</a>
											</li>
											<li>
												<a href="reviews.php">
													<i class="fas fa-star"></i>
													<span>Reviews</span>
												</a>
											</li>
											<li>
												<a href="chat-doctor.soooooon">
													<i class="fas fa-comments"></i>
													<span>Message</span>
													<small class="unread-msg">23</small>
												</a>
											</li>
											<li>
												<a href="doctor-profile-settings.php">
													<i class="fas fa-user-cog"></i>
													<span>Profile Settings</span>
												</a>
											</li>
											<li>
												<a href="social-media.php">
													<i class="fas fa-share-alt"></i>
													<span>Social Media</span>
												</a>
											</li>
											<li>
												<a href="doctor-change-password.php">
													<i class="fas fa-lock"></i>
													<span>Change Password</span>
												</a>
											</li>
											<li>
												<a href="setup_2fa.php">
													<i class="fas fa-lock"></i>
													<span>Enable 2FA</span>
												</a>
											</li>
											<li>
												<a href="logout.php">
													<i class="fas fa-sign-out-alt"></i>
													<span>Logout</span>
												</a>
											</li>
										</ul>
									</nav>
    </div>

    </div>



    </div>
