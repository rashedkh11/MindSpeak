<?php
//session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User is not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch patient details from `patients` table
$sql = "SELECT date_of_birth, state, country FROM patients WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient_result = $stmt->get_result();
$patient = $patient_result->fetch_assoc();
$stmt->close();

$dob = !empty($patient['date_of_birth']) ? date('d M Y', strtotime($patient['date_of_birth'])) : "N/A";
$state = $patient['state'] ?? "N/A";
$country = $patient['country'] ?? "N/A";

// Fetch user details from `users` table
$sql = "SELECT fname, lname, username, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

$full_name = (!empty($user['fname']) && !empty($user['lname'])) ? $user['fname'] . ' ' . $user['lname'] : $user['username'];
$image = !empty($user['user_image']) ? "uploads/" . $user['user_image'] : "assets/img/random.png"; // Profile image

// Calculate age if DOB is available
$age = ($dob !== "N/A") ? date_diff(date_create($patient['date_of_birth']), date_create('today'))->y : "N/A";
?>
