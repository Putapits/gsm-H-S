-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Oct 03, 2025 at 03:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone-hs`
CREATE DATABASE`capstone-hs`;
USE `capstone-hs`;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female','other','prefer-not-to-say') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed') NOT NULL,
  `address` text NOT NULL,
  `appointment_type` varchar(100) NOT NULL,
  `preferred_date` date NOT NULL,
  `health_concerns` text NOT NULL,
  `medical_history` text NOT NULL,
  `current_medications` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) NOT NULL,
  `emergency_contact_phone` varchar(20) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `birth_date`, `gender`, `civil_status`, `address`, `appointment_type`, `preferred_date`, `health_concerns`, `medical_history`, `current_medications`, `allergies`, `emergency_contact_name`, `emergency_contact_phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Lelouch', '', 'Lamperouge', 'xlelouchlamperouge2024@gmail.com', '123123132', '2001-12-01', 'male', 'single', 'qweqwe', 'general-checkup', '2025-10-03', 'Headache', 'Covid', 'Bioflu', 'Peanuts', 'Monkey D. Luffy', '123123123', 'cancelled', '2025-10-03 06:05:10', '2025-10-03 10:23:37'),
(2, 5, 'Edilyn', '', 'Alix', 'edilynalix@gmail.com', '12312312312', '2200-12-01', 'female', 'single', 'Manila, Manila', 'general-checkup', '2025-10-17', 'Trauma', 'None', 'None', 'Peanuts', 'Julia Desolo', '123123123', 'confirmed', '2025-10-03 12:50:56', '2025-10-03 12:51:45'),
(3, 2, 'Lelouch', '', 'Lamperouge', 'xlelouchlamperouge2024@gmail.com', '12312312312', '2001-12-01', 'male', 'single', 'Quezon City', 'follow-up', '2025-10-10', 'Nothing', 'Nothing', 'Nothing', 'Nothing', 'Monkey D. Luffy', '123123123123', 'pending', '2025-10-03 13:04:16', '2025-10-03 13:04:16');

-- --------------------------------------------------------

--
-- Table structure for table `health_center_requests`
--

CREATE TABLE `health_center_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sub_service_type` enum('medical-consultation','emergency-care','preventive-care') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `consultation_type` varchar(100) DEFAULT NULL,
  `emergency_type` varchar(100) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `preventive_service_type` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `service_details` text NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_surveillance_reports`
--

CREATE TABLE `health_surveillance_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `report_type` enum('disease','environmental','incident') NOT NULL,
  `disease_or_event` varchar(150) DEFAULT NULL,
  `symptoms` text DEFAULT NULL,
  `cases_count` int(11) DEFAULT NULL,
  `report_location` varchar(200) DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `service_details` text NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `immunization_nutrition_requests`
--

CREATE TABLE `immunization_nutrition_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `age` int(11) DEFAULT NULL,
  `vaccine_type` varchar(100) DEFAULT NULL,
  `dose_number` varchar(50) DEFAULT NULL,
  `nutrition_concern` varchar(150) DEFAULT NULL,
  `service_details` text NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sanitation_permit_requests`
--

CREATE TABLE `sanitation_permit_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `business_name` varchar(200) NOT NULL,
  `business_type` enum('Restaurant','Food Service','Commercial Facility','Other') NOT NULL,
  `permit_type` enum('New Application','Renewal','Update') NOT NULL,
  `service_details` text NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `service_details` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `user_id`, `service_type`, `full_name`, `email`, `phone`, `address`, `service_details`, `preferred_date`, `urgency`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'medical-consultation', 'Lelouch Lamperouge', 'xlelouchlamperouge2024@gmail.com', '12312312312', 'Manila', 'Help\n\nAdditional Information:\n- Consultation Type: General Practice', '2001-12-01', 'high', 'in_progress', '2025-10-03 06:50:42', '2025-10-03 10:34:43'),
(2, 2, 'emergency-care', 'Hisoka Morrow', 'hisokamorrow01192000@gmail.com', '12312312312', 'Manila', 'Help\n\nAdditional Information:\n- Emergency Type: Trauma\n- Symptoms: Trauma', '2000-12-01', 'high', 'in_progress', '2025-10-03 06:55:33', '2025-10-03 10:34:19'),
(3, 2, 'business-permit', 'Chrollo Lucilfer', 'chrollolucifer01192000@gmail.com', '12312312312', 'manila', 'Help\n\nAdditional Information:\n- Business Name: Index Philippines\n- Business Type: Restaurant\n- Permit Type: Renewal', '2001-12-01', 'high', 'pending', '2025-10-03 06:56:36', '2025-10-03 06:56:36'),
(4, 2, 'preventive-care', 'Chrollo Lucilfer', 'chrollolucifer01192000@gmail.com', '12312312312', 'Manila', 'Help\n\nAdditional Information:\n- Preventive Service Type: Wellness Check\n- Age: 25', '2000-12-01', 'high', 'completed', '2025-10-03 09:11:23', '2025-10-03 10:23:27'),
(5, 4, 'medical-consultation', 'jr bustalinio', 'jrbustalinio@gmail.com', '12312312312', 'Manila, Manila', 'Help\n\nAdditional Information:\n- Consultation Type: Medical Certificate\n- Consultation Urgency: Urgent', NULL, 'medium', 'pending', '2025-10-03 12:00:12', '2025-10-03 12:00:12'),
(6, 4, 'emergency-care', 'kelvin bustalinio', 'kelvinbustalinio@gmail.com', '12312312312', 'Manila, Manila', 'I want a solo room\n\nAdditional Information:\n- Emergency Type: Accident\n- Symptoms: Trauma', '2000-12-01', 'high', 'pending', '2025-10-03 12:02:59', '2025-10-03 12:02:59'),
(7, 5, 'medical-consultation', 'Peter Santos', 'petersantos@gmail.com', '12312312312', 'Quezon City', 'Help\n\nAdditional Information:\n- Consultation Type: Specialist Referral\n- Consultation Urgency: Urgent', '2000-12-01', 'medium', 'in_progress', '2025-10-03 12:52:39', '2025-10-03 12:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','nurse','citizen','inspector') DEFAULT 'citizen',
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `verification_status` enum('unverified','pending','verified','rejected') DEFAULT 'unverified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `status`, `profile_picture`, `phone`, `address`, `date_of_birth`, `gender`, `verification_status`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'User', 'admin@healthsanitation.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, 'unverified', '2025-10-02 07:59:07', '2025-10-02 07:59:07'),
(2, 'Lelouch', 'Lamperouge', 'xlelouchlamperouge2024@gmail.com', '$2y$10$YSNKtpSH7vf/LgTqyPQW6.UA4snGOiXnfxW5b6cOIeYtDP0CAkOp2', 'citizen', 'active', NULL, NULL, NULL, NULL, NULL, 'verified', '2025-10-03 05:31:52', '2025-10-03 13:03:29'),
(3, 'Gon', 'Freecs', 'gonfreecs01192000@gmail.com', '$2y$10$HR3E5h/sO3I.7XFJ5d5Z/.Xqu6X0zSZM5/CN6ic35vZJfwO6Ubl4.', 'doctor', 'active', NULL, NULL, NULL, NULL, NULL, 'unverified', '2025-10-03 10:14:21', '2025-10-03 10:14:21'),
(4, 'Peter', 'Santos', 'petersantos@gmail.com', '$2y$10$06Wa5HKSHGWZpy89nM3fze2dzdTDoO.KDxxWeAK8KiPANUr7KcYwa', 'citizen', 'active', NULL, NULL, NULL, NULL, NULL, 'verified', '2025-10-03 11:26:53', '2025-10-03 11:35:22'),
(5, 'Julia', 'Desolo', 'juliadesolo@gmail.com', '$2y$10$7uQMnqCRbN8zpUG33/N9/u/ZHyEcKOqG7UAO/xsNT2046qNeMpYci', 'citizen', 'active', 'uploads/profile_pictures/u5_20251003_145358_fa7cdd.jpg', NULL, NULL, NULL, NULL, 'verified', '2025-10-03 12:35:08', '2025-10-03 12:53:58');

-- --------------------------------------------------------

--
-- Table structure for table `user_verifications`
--

CREATE TABLE `user_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_verifications`
--

INSERT INTO `user_verifications` (`id`, `user_id`, `document_type`, `file_path`, `status`, `notes`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'national_id', 'uploads/verifications/uid4_20251003_132735_5953ca01.pdf', 'verified', 'Verified by admin', 1, '2025-10-03 11:35:22', '2025-10-03 11:27:35', '2025-10-03 11:35:22'),
(2, 2, 'national_id', 'uploads/verifications/uid2_20251003_133506_05e84780.pdf', 'rejected', 'Rejected by admin', 1, '2025-10-03 12:54:55', '2025-10-03 11:35:06', '2025-10-03 12:54:55'),
(3, 5, 'national_id', 'uploads/verifications/uid5_20251003_144848_00228ae9.pdf', 'verified', 'Verified by admin', 1, '2025-10-03 12:49:36', '2025-10-03 12:48:48', '2025-10-03 12:49:36'),
(4, 2, 'national_id', 'uploads/verifications/uid2_20251003_150321_67935990.pdf', 'verified', 'Verified by admin', 1, '2025-10-03 13:03:29', '2025-10-03 13:03:21', '2025-10-03 13:03:29');

-- --------------------------------------------------------

--
-- Table structure for table `wastewater_septic_requests`
--

CREATE TABLE `wastewater_septic_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `sub_service_type` enum('septic-pumping','inspection','repair','environmental-assessment') NOT NULL,
  `assessment_type` enum('Air Quality','Water Safety','Environmental Hazard','Pollution Control') DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `service_details` text NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `health_center_requests`
--
ALTER TABLE `health_center_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hc_user` (`user_id`),
  ADD KEY `idx_hc_status` (`status`);

--
-- Indexes for table `health_surveillance_reports`
--
ALTER TABLE `health_surveillance_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hs_user` (`user_id`),
  ADD KEY `idx_hs_status` (`status`);

--
-- Indexes for table `immunization_nutrition_requests`
--
ALTER TABLE `immunization_nutrition_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_in_user` (`user_id`),
  ADD KEY `idx_in_status` (`status`);

--
-- Indexes for table `sanitation_permit_requests`
--
ALTER TABLE `sanitation_permit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sp_user` (`user_id`),
  ADD KEY `idx_sp_status` (`status`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wastewater_septic_requests`
--
ALTER TABLE `wastewater_septic_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ws_user` (`user_id`),
  ADD KEY `idx_ws_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `health_center_requests`
--
ALTER TABLE `health_center_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_surveillance_reports`
--
ALTER TABLE `health_surveillance_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `immunization_nutrition_requests`
--
ALTER TABLE `immunization_nutrition_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sanitation_permit_requests`
--
ALTER TABLE `sanitation_permit_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_verifications`
--
ALTER TABLE `user_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wastewater_septic_requests`
--
ALTER TABLE `wastewater_septic_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_center_requests`
--
ALTER TABLE `health_center_requests`
  ADD CONSTRAINT `health_center_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_surveillance_reports`
--
ALTER TABLE `health_surveillance_reports`
  ADD CONSTRAINT `health_surveillance_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `immunization_nutrition_requests`
--
ALTER TABLE `immunization_nutrition_requests`
  ADD CONSTRAINT `immunization_nutrition_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sanitation_permit_requests`
--
ALTER TABLE `sanitation_permit_requests`
  ADD CONSTRAINT `sanitation_permit_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD CONSTRAINT `user_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wastewater_septic_requests`
--
ALTER TABLE `wastewater_septic_requests`
  ADD CONSTRAINT `wastewater_septic_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
