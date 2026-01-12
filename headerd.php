<?php
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if the user is logged in
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Mindspeak</title>
    <link rel="icon" href="assets/img/logo.png" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <header class="header">
            <nav class="navbar navbar-expand-lg header-nav">
                <div class="navbar-header">
                   
                    <a href="index-2.php" class="navbar-brand logo">
                        <img src="assets/img/ms-logo.png" class="img-fluid" alt="Logo">
                    </a>
                </div>
                <div class="main-menu-wrapper">
                    <div class="menu-header">
                        <a href="doctor-dashboard.php" class="menu-logo">
                            <img src="assets/img/ms-logo.png" class="img-fluid" alt="Logo">
                        </a>
                        <a id="menu_close" class="menu-close" href="javascript:void(0);">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                    <ul class="main-nav">
                        <li ><a href="doctor-dashboard.php">Home</a></li>
                        <li><a href="blog.php">Blog</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li class="login-link"><a href="login.php">Login / Signup</a></li>
                    </ul>
                </div>
                <ul class="nav header-navbar-rht">
                
                    <li class="nav-item dropdown has-arrow logged-item">
                        <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                            <span class="user-img">
                                <img class="rounded-circle" src="<?php echo $profile_image; ?>" width="31" alt="<?php echo htmlspecialchars($username); ?>">
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="user-header">
                                <div class="avatar avatar-sm">
                                    <img src="<?php echo $profile_image; ?>" alt="User Image" class="avatar-img rounded-circle">
                                </div>
                                <div class="user-text">
                                    <h6><?php echo htmlspecialchars($username); ?></h6>
                                    <p class="text-muted mb-2"> <?php echo htmlspecialchars($role); ?></p>
                                </div>
                            </div>
                            <?php if ($role=='doctor')  {
                                echo '<a class="dropdown-item" href="doctor-dashboard.php">Dashboard</a>';
                                echo'<a class="dropdown-item" href="doctor-profile-settings.php">Profile Settings</a>';

                            } else {
                                echo '<a class="dropdown-item" href="patient-dashboard.php">Dashboard</a>';
                                echo'<a class="dropdown-item" href="profile-settings.php">Profile Settings</a>';

                            } ?>  
                            <a class="dropdown-item" href="logout.php">Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>
        </header>
    </div>
    
 
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
