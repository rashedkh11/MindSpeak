-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 12, 2025 at 06:24 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `MINDSPEAK`
--

-- --------------------------------------------------------

--
-- Table structure for table `APPOINTMENTS`
--

CREATE TABLE `APPOINTMENTS` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `status` enum('Confirmed','Cancelled','Pending') DEFAULT 'Pending',
  `purpose` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `time` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `APPOINTMENTS`
--

INSERT INTO `APPOINTMENTS` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `booking_date`, `amount`, `follow_up_date`, `status`, `purpose`, `type`, `time`) VALUES
(3, 6, 13, '2025-02-15 00:00:00', '2025-02-05 11:00:00', 160.00, NULL, 'Pending', 'general', 'idk', '00:00:00'),
(4, 6, 13, '2025-02-17 08:00:00', '2025-02-06 11:30:00', 250.00, '2025-02-18', 'Confirmed', 'general', 'shutup', '00:00:00'),
(5, 6, 13, '2025-02-09 00:00:00', '2025-02-07 12:22:20', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(6, 6, 13, '2025-02-13 00:00:00', '2025-02-07 12:22:32', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(7, 6, 13, '2025-02-08 00:00:00', '2025-02-07 12:22:47', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(8, 6, 13, '2025-02-08 00:00:00', '2025-02-07 12:23:17', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(21, 6, 14, '2025-02-07 00:00:00', '2025-02-07 12:42:35', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(22, 6, 14, '2025-02-09 00:00:00', '2025-02-07 12:51:34', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(23, 6, 14, '2025-02-09 00:00:00', '2025-02-07 13:00:30', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(24, 6, 14, '2025-02-09 00:00:00', '2025-02-07 13:01:36', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(25, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:01:41', 100.00, NULL, 'Pending', NULL, NULL, '00:00:00'),
(26, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:01:45', 100.00, NULL, 'Confirmed', NULL, NULL, '00:00:00'),
(27, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:09:54', 100.00, NULL, 'Pending', NULL, NULL, '10:00 AM'),
(28, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:13:17', 100.00, NULL, 'Pending', NULL, NULL, '10:00 AM'),
(29, 6, 14, '2025-02-08 00:00:00', '2025-02-07 13:13:58', 100.00, NULL, 'Pending', NULL, NULL, '12:00 PM'),
(30, 6, 14, '2025-02-08 00:00:00', '2025-02-07 13:14:01', 100.00, NULL, 'Pending', NULL, NULL, '2:00 PM'),
(31, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:14:04', 100.00, NULL, 'Pending', NULL, NULL, '10:00 AM'),
(32, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:15:14', 100.00, NULL, 'Pending', NULL, NULL, '10:00 AM'),
(33, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:21:38', 100.00, NULL, 'Pending', NULL, NULL, '10:00 AM'),
(34, 6, 14, '2025-02-07 00:00:00', '2025-02-07 13:22:07', 100.00, NULL, 'Pending', NULL, NULL, '11:00 AM');

-- --------------------------------------------------------

--
-- Table structure for table `DOCTORS`
--

CREATE TABLE `DOCTORS` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `services` text DEFAULT NULL,
  `specialization` text DEFAULT NULL,
  `pricing` decimal(10,2) DEFAULT NULL,
  `clinic_name` varchar(255) DEFAULT NULL,
  `clinic_address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `rating` int(11) DEFAULT 0,
  `reviews` int(11) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `pricing_min` decimal(10,2) DEFAULT NULL,
  `pricing_max` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `DOCTORS`
--

INSERT INTO `DOCTORS` (`id`, `user_id`, `biography`, `services`, `specialization`, `pricing`, `clinic_name`, `clinic_address`, `city`, `country`, `address_line_1`, `address_line_2`, `date_of_birth`, `rating`, `reviews`, `profile_image`, `pricing_min`, `pricing_max`) VALUES
(14, 13, 'sd', 'sd mm , kk', 'doctor', 8.00, 'sd', 'ds', 'alkarak', 'jordan', 'sd', 's', '2025-02-06', 4, 98, NULL, 200.00, 400.00),
(20, 14, 'sd', 'sd', 'sd', 8.00, 'sd', 'ds', 'y', 'sd', 'sd', 's', '2025-02-06', 0, 0, NULL, 200.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `FAVOURITES`
--

CREATE TABLE `FAVOURITES` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `FAVOURITES`
--

INSERT INTO `FAVOURITES` (`id`, `user_id`, `doctor_id`, `created_at`) VALUES
(2, 6, 14, '2025-02-08 11:27:23'),
(9, 6, 13, '2025-02-11 19:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` enum('A-','A+','B-','B+','AB-','AB+','O-','O+') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `user_id`, `date_of_birth`, `blood_group`, `address`, `city`, `state`, `zip_code`, `country`) VALUES
(1, 6, '2002-04-11', 'O+', 'alkarak', 'alkarak', 'alkarak', '61610', 'Jordan'),
(5, 5, '2002-04-11', 'O+', 'alkarak', 'alkarak', 'alkarak', '61610', 'Jordan'),
(6, 33, '1990-05-15', 'O+', NULL, 'New York', NULL, NULL, 'USA');

-- --------------------------------------------------------

--
-- Table structure for table `patient_sessions`
--

CREATE TABLE `patient_sessions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `status` enum('accepted','pending','canceled') DEFAULT 'accepted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_sessions`
--

INSERT INTO `patient_sessions` (`id`, `patient_id`, `session_id`, `status`) VALUES
(1, 6, 1, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `priv_doctors`
--

CREATE TABLE `priv_doctors` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `status` enum('pending','accepted','cancelled') DEFAULT 'pending',
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `priv_doctors`
--

INSERT INTO `priv_doctors` (`id`, `patient_id`, `doctor_id`, `status`, `assigned_date`) VALUES
(5, 6, 13, 'accepted', '2025-02-05 19:11:55'),
(6, 5, 13, 'accepted', '2025-02-05 19:11:55');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `review_title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `doctor_id`, `patient_id`, `rating`, `review_title`, `review_text`, `created_at`, `updated_at`) VALUES
(2, 13, 14, 1, 'd', 'sd', '2025-02-07 15:16:31', '2025-02-07 15:16:31'),
(3, 13, 14, 3, '12', '1221', '2025-02-07 15:16:42', '2025-02-07 15:16:42'),
(4, 13, 14, 4, 'wq', 'wq', '2025-02-07 16:02:43', '2025-02-07 16:02:43'),
(5, 13, 14, 2, 's', 'ds', '2025-02-07 16:05:34', '2025-02-07 16:05:34'),
(6, 13, 14, 1, 'asasasasas', 'a', '2025-02-07 16:05:56', '2025-02-07 16:05:56'),
(7, 13, 14, 1, '111', '111', '2025-02-07 16:10:29', '2025-02-07 16:10:29'),
(10, 13, 6, 1, '111', '111', '2025-02-07 16:10:29', '2025-02-07 16:10:29'),
(11, 13, 6, 3, '1', '1', '2025-02-07 16:34:47', '2025-02-07 16:34:47'),
(12, 13, 6, 1, 'a', 'a', '2025-02-07 17:24:15', '2025-02-07 17:24:15'),
(13, 14, 6, 3, 'hi', 'hhhh', '2025-02-07 20:34:18', '2025-02-07 20:34:18'),
(14, 14, 6, 1, '1', '12', '2025-02-07 20:36:21', '2025-02-07 20:36:21'),
(15, 14, 6, 1, 'a', 'a', '2025-02-07 20:37:07', '2025-02-07 20:37:07'),
(16, 14, 6, 5, 'x', 'x', '2025-02-07 20:37:52', '2025-02-07 20:37:52'),
(17, 14, 6, 2, 'a', 'a', '2025-02-07 20:39:40', '2025-02-07 20:39:40'),
(18, 14, 6, 2, 'a', 'a', '2025-02-07 20:40:10', '2025-02-07 20:40:10'),
(19, 13, 6, 1, 'asasasasas', 'asasa', '2025-02-07 20:45:13', '2025-02-07 20:45:13'),
(20, 14, 6, 2, 'a', 'a', '2025-02-07 20:46:53', '2025-02-07 20:46:53'),
(21, 14, 6, 4, 'sdsddsds', 'dsdsdsds', '2025-02-07 20:47:16', '2025-02-07 20:47:16'),
(22, 14, 6, 4, 'sdsddsds', 'dsdsdsds', '2025-02-07 20:47:22', '2025-02-07 20:47:22'),
(23, 14, 6, 4, 'sdsddsds', 'dsdsdsds', '2025-02-07 20:47:48', '2025-02-07 20:47:48'),
(24, 14, 6, 4, 'sdsddsds', 'dsdsdsds', '2025-02-07 20:47:56', '2025-02-07 20:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `SESSIONS`
--

CREATE TABLE `SESSIONS` (
  `id` int(11) NOT NULL,
  `session_name` varchar(255) NOT NULL,
  `session_type` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `clinic_name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `date_time` datetime NOT NULL,
  `duration` varchar(50) NOT NULL DEFAULT '60 minutes',
  `price` decimal(10,2) NOT NULL DEFAULT 50.00,
  `status` enum('DONE','active') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SESSIONS`
--

INSERT INTO `SESSIONS` (`id`, `session_name`, `session_type`, `description`, `location`, `doctor_id`, `clinic_name`, `image`, `date_time`, `duration`, `price`, `status`) VALUES
(1, 'Group Therapy', 'Therapy', 'Supportive therapy for stress management', 'New York, USA', 13, 'UKM Clinic', 'assets/img/groub.png', '2025-02-15 14:00:00', '60 minutes', 50.00, 'active'),
(2, 'Group Therapy', 'Therapy', 'Healing together, growing stronger', 'Florida, USA', 13, 'UKM Clinic', 'assets/img/groub.png', '2025-02-10 14:00:00', '60', 300.00, 'active'),
(20, 'Group Therapy', 'Therapy', 'ee', 'almazar, jor', 13, '121', 'assets/img/groub.png', '2025-02-10 14:11:00', '12', 12.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fname` varchar(100) DEFAULT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `age` int(11) DEFAULT NULL CHECK (`age` >= 0),
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_status` enum('Active','Inactive','Banned') DEFAULT 'Active',
  `ROLL` varchar(10) NOT NULL CHECK (`ROLL` in ('PATIENT','DOCTOR')),
  `profile_image` varchar(255) DEFAULT 'assets/img/random.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `pass`, `username`, `fname`, `mname`, `lname`, `phone`, `email`, `gender`, `age`, `last_login`, `account_status`, `ROLL`, `profile_image`) VALUES
(5, '$2y$10$lvz/Jm/ez8LhUTxX0f3BsursI1WIFri2X2YXo5Sxq3Th5Oavnr8qm', 'rashed', 'mohammad', NULL, 'ahmad', '+962797499942', 'rashedfahad849@gmail.com', 'Male', 22, '2025-02-05 23:37:15', 'Active', 'Patient', 'assets/img/random.png'),
(6, '$2y$10$LK7CliBBYJTzz.fXzE0EduSiiPlyGA0eOYAtf97DsZfN8c9DRWkEC', 'rashed1', 'rashed', NULL, 'fahad', '0797499942', 'rashedfahad89@gmail.com', 'Male', 12, '2025-02-04 14:19:26', 'Active', 'Patient', 'assets/img/random.png'),
(12, '$2y$10$WTuYwWEPwEXWGVTc/Dk3gu9Bc.Bx74xUfC/tUaNfCDyWbyKghlRt2', 'mohammad', NULL, NULL, NULL, '12918928', 'rashedfahad8419@gmail.com', 'Male', 22, '2025-02-04 08:29:57', 'Active', 'Doctor', 'assets/img/random.png'),
(13, '$2y$10$JYzyNT8KqwotHhQtLWgkXetV.GqSVm9P/QgSJ6ykTuP/7FzjvUUoK', 'Dr. Ruby Perrin', 'Ruby', 'Ruby', 'Perrin', '766767', 'dskdsjjds@jakak.d', 'Female', 23, '2025-02-05 23:34:45', 'Active', 'doctor', 'assets/img/random.png'),
(14, '2', 'Dr. Darren Elder', 'Darren', 'Ruby', 'Elder', '2345678901', 'darren@example.com', 'Male', 40, '2025-02-06 20:18:55', 'Active', 'doctor', 'assets/img/random.png'),
(15, '3', 'John Doe', NULL, NULL, NULL, '3456789012', 'john@example.com', 'Male', 30, '2025-02-04 09:39:52', 'Active', 'patient', 'assets/img/random.png'),
(33, 'rr', 'u', 'John', NULL, 'Doe', '+1 234 567 890', 'johndoe@example.com', 'Male', NULL, '2025-02-09 08:39:17', 'Active', 'patient', 'assets/img/patients/patient.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `APPOINTMENTS`
--
ALTER TABLE `APPOINTMENTS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `DOCTORS`
--
ALTER TABLE `DOCTORS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `FAVOURITES`
--
ALTER TABLE `FAVOURITES`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`doctor_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `patient_sessions`
--
ALTER TABLE `patient_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`,`session_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `priv_doctors`
--
ALTER TABLE `priv_doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `SESSIONS`
--
ALTER TABLE `SESSIONS`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `APPOINTMENTS`
--
ALTER TABLE `APPOINTMENTS`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `DOCTORS`
--
ALTER TABLE `DOCTORS`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `FAVOURITES`
--
ALTER TABLE `FAVOURITES`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient_sessions`
--
ALTER TABLE `patient_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `priv_doctors`
--
ALTER TABLE `priv_doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `SESSIONS`
--
ALTER TABLE `SESSIONS`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `APPOINTMENTS`
--
ALTER TABLE `APPOINTMENTS`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `DOCTORS`
--
ALTER TABLE `DOCTORS`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `USERS` (`id`);

--
-- Constraints for table `FAVOURITES`
--
ALTER TABLE `FAVOURITES`
  ADD CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `USERS` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `USERS` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_sessions`
--
ALTER TABLE `patient_sessions`
  ADD CONSTRAINT `patient_sessions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `PATIENTS` (`user_id`),
  ADD CONSTRAINT `patient_sessions_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `SESSIONS` (`id`);

--
-- Constraints for table `priv_doctors`
--
ALTER TABLE `priv_doctors`
  ADD CONSTRAINT `priv_doctors_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `priv_doctors_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `SESSIONS`
--
ALTER TABLE `SESSIONS`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `DOCTORS` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
