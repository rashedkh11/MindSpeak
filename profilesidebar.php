
<div class="col-md-5 col-lg-4 col-xl-3 theiaStickySidebar">

<div class="profile-sidebar">
    <div class="widget-profile pro-widget-content">
            <div class="profile-info-widget">
                <a href="#" class="booking-doc-img">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="User Image">
                </a>
                <div class="profile-det-info">
                    <h3><?php echo htmlspecialchars($full_name); ?></h3>
                    <div class="patient-details">
                        <h5><i class="fas fa-birthday-cake"></i> <?php echo ($dob !== "N/A") ? "$dob, $age years" : "DOB not available"; ?></h5>
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> <?php echo ($country !== "N/A")? "$country": "country not available";?></h5>
                    </div>
                </div>
                </div>
        </div>

    <div class="dashboard-widget">
        <nav class="dashboard-menu">
            <ul>
                <li >
                    <a href="patient-dashboard.php">
                        <i class="fas fa-columns"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="favourites.php">
                        <i class="fas fa-bookmark"></i>
                        <span>Favourites</span>
                    </a>
                </li>
                <li>
                    <a href="chat.php">
                        <i class="fas fa-comments"></i>
                        <span>Message</span>
                        <small class="unread-msg">23</small>
                    </a>
                </li>
                <li>
                    <a href="profile-settings.php">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Settings</span>
                    </a>
                </li>
                <li>
                    <a href="social.php">
                    <i class="fas fa-share-alt"></i>
                        <span>social media</span>
                    </a>
                </li>
                <li>
                    <a href="change-password.php">
                        <i class="fas fa-lock"></i>
                        <span>Change Password</span>
                    </a>
                </li>
                <li>
                    <a href="disable_2FA.php">
                    <i class="fas fa-lock"></i>
                    <span>Two-Factor Authentication</span>
                    </a>
				</li>
                <li>
                    <a href="index-2.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    </div>



    </div>
