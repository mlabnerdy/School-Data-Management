-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 10:00 AM
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
-- Database: `school_data_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `owner_type` enum('student','teacher','staff') NOT NULL,
  `owner_id` int(10) UNSIGNED NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `information`
--

CREATE TABLE `information` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Published',
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `information`
--

INSERT INTO `information` (`id`, `title`, `category`, `description`, `school_year`, `date_posted`, `status`, `file_name`, `file_path`, `file_type`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Test', 'Inventory', 'Test Test', '2027-2028', '2026-08-26', 'Published', 'Personal Data Sheet (PDS) 2025 - CS Form 212 Revised 2025.pdf', 'uploads/information/info_6a8e96978da089.49788080_Personal_Data_Sheet__PDS__2025_-_CS_Form_212_Revised_2025.pdf', 'application/pdf', 3, 3, '2026-08-26 07:32:39', '2026-08-26 07:33:50');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(10) UNSIGNED NOT NULL,
  `school_year` varchar(9) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `school_year`, `is_active`, `created_at`) VALUES
(1, '2024-2025', 1, '2026-08-26 06:06:10'),
(2, '2025-2026', 1, '2026-08-26 06:06:10'),
(3, '2026-2027', 1, '2026-08-26 06:06:10'),
(4, '2027-2028', 1, '2026-08-26 06:14:19');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `position_department` varchar(150) DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plantilla_no` varchar(50) DEFAULT NULL,
  `first_day_of_service` date DEFAULT NULL,
  `current_latest_appointment` date DEFAULT NULL,
  `deped_email` varchar(150) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `degree_finished` varchar(255) DEFAULT NULL,
  `specialization_prc_eligibility` varchar(255) DEFAULT NULL,
  `tin_no` varchar(30) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `prc_eligibility` varchar(255) DEFAULT NULL,
  `gsis_no` varchar(50) DEFAULT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `salary_grade` tinyint(3) UNSIGNED DEFAULT NULL,
  `step` tinyint(3) UNSIGNED DEFAULT NULL,
  `training_accomplishments` text DEFAULT NULL
) ;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `user_id`, `created_by`, `updated_by`, `employee_id`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `email`, `position_department`, `other_info`, `created_at`, `updated_at`, `plantilla_no`, `first_day_of_service`, `current_latest_appointment`, `deped_email`, `personal_email`, `degree_finished`, `specialization_prc_eligibility`, `tin_no`, `birthdate`, `specialization`, `prc_eligibility`, `gsis_no`, `philhealth_no`, `salary_grade`, `step`, `training_accomplishments`) VALUES
(1, 3, NULL, 3, '55-412341', 'uploads/staff/f5cecdb776a993b3d6a68bce.webp', 'Pony Plum', '1989-05-10', 'Male', 'P5 Brgy. Dulo Test 2 test test testteststtest', '09091005012', '', 'Registrar', '', '2026-08-08 06:40:37', '2026-08-18 08:09:19', '', NULL, NULL, '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `lrn` varchar(12) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `parent_guardian` varchar(150) DEFAULT NULL,
  `grade_section` varchar(100) DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `school_id` varchar(6) NOT NULL DEFAULT '500634',
  `school_year` varchar(9) DEFAULT NULL,
  `emergency_name` varchar(100) DEFAULT NULL,
  `emergency_address` text DEFAULT NULL,
  `emergency_contact` varchar(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `parent_guardian`, `grade_section`, `other_info`, `created_at`, `updated_at`, `school_id`, `school_year`, `emergency_name`, `emergency_address`, `emergency_contact`, `created_by`, `updated_by`) VALUES
(2, '500223090129', 'uploads/student/9c8376caecfe8279f2b1de75.jpg', 'Juan Dela Cruz', '2018-08-14', 'Male', 'P-5 Brgy. V Doon St. Bacoor City', '09325010012', 'Maria Dela Cruz', 'Grade I - Wonderful', '', '2026-08-14 06:36:57', '2026-08-26 06:14:45', '500634', '2027-2028', 'Maria Dela Cruz', 'P-5 Brgy. V Doon St. Bacoor City', '09500093912', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `position_department` varchar(150) DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plantilla_no` varchar(50) DEFAULT NULL,
  `first_day_of_service` date DEFAULT NULL,
  `current_latest_appointment` date DEFAULT NULL,
  `deped_email` varchar(150) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `degree_finished` varchar(255) DEFAULT NULL,
  `specialization_prc_eligibility` varchar(255) DEFAULT NULL,
  `tin_no` varchar(30) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `prc_eligibility` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `gsis_no` varchar(50) DEFAULT NULL,
  `philhealth_no` varchar(50) DEFAULT NULL,
  `salary_grade` tinyint(3) UNSIGNED DEFAULT NULL,
  `step` tinyint(3) UNSIGNED DEFAULT NULL,
  `training_accomplishments` text DEFAULT NULL
) ;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `employee_id`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `email`, `position_department`, `other_info`, `created_at`, `updated_at`, `plantilla_no`, `first_day_of_service`, `current_latest_appointment`, `deped_email`, `personal_email`, `degree_finished`, `specialization_prc_eligibility`, `tin_no`, `birthdate`, `specialization`, `prc_eligibility`, `created_by`, `updated_by`, `gsis_no`, `philhealth_no`, `salary_grade`, `step`, `training_accomplishments`) VALUES
(4, 5, '55-412322', NULL, 'Maria Dela Cruz', NULL, '', 'Test test test', '09325010012', NULL, '', '', '2026-08-14 08:35:21', '2026-08-18 07:46:01', '3212331', '2026-08-09', '2026-08-04', 'sample@deped.gov.ph', 'sampleemail@email.com', 'Bachelor of Secondary Education', '2132158921839', '2348237482374', '2026-08-09', NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL),
(6, NULL, '5623626', NULL, 'Jane Doe', '2026-08-03', 'Female', 'Purok test', '09667183489', NULL, 'Teacher II', '', '2026-08-18 07:14:54', '2026-08-18 08:03:58', '84566356', '2026-08-03', '2026-08-11', 'sample2@deped.gov.ph', 'sample2email@email.com', 'Bachelor of Secondary Education', 'Mathematics', '63466345', NULL, NULL, NULL, NULL, 3, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transparency`
--

CREATE TABLE `transparency` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `date_posted` date DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `status` enum('Published','Draft','Archived') NOT NULL DEFAULT 'Published',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transparency`
--

INSERT INTO `transparency` (`id`, `title`, `category`, `description`, `school_year`, `date_posted`, `file_name`, `file_path`, `file_type`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 'Test', NULL, 'Test', NULL, NULL, 'PARQPlus2025ImageFile.pdf', 'uploads/transparency/dd5b6adf05f2e730830ce248.pdf', 'application/pdf', 'Published', 3, 3, '2026-08-26 07:57:14', '2026-08-26 07:58:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Administrator','Teacher','Staff') NOT NULL DEFAULT 'Staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `created_at`) VALUES
(3, 'Super Admin', 'admin', '$2y$10$Ac8nA3ihwRwYAD79aVa0Cek8PoEntBVi52MfUFgGM2Glpj692PJcC', 'Administrator', '2026-08-08 06:15:44'),
(4, 'Administrator', 'admin1', '$2y$10$G6yetdIXtHMhGqMg6wIP/.ye39JDvM2BD9bp1PVjoRxEMVibXoPJG', 'Administrator', '2026-08-11 09:14:04'),
(5, 'Maria Dela Cruz', 'teacher', '$2y$10$wG7z.C4jaU3sl7L1DyGQo.cSfGUw5.ipzN8zMiLSQ5e1rKMXniOl.', 'Teacher', '2026-08-14 06:46:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_owner` (`owner_type`,`owner_id`);

--
-- Indexes for table `information`
--
ALTER TABLE `information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_school_year` (`school_year`),
  ADD KEY `idx_school_year_active` (`is_active`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_staff_user_id` (`user_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_students_school_year` (`school_year`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `fk_teachers_user` (`user_id`);

--
-- Indexes for table `transparency`
--
ALTER TABLE `transparency`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transparency_category` (`category`),
  ADD KEY `idx_transparency_school_year` (`school_year`),
  ADD KEY `idx_transparency_status` (`status`),
  ADD KEY `idx_transparency_created_by` (`created_by`),
  ADD KEY `idx_transparency_updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `information`
--
ALTER TABLE `information`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transparency`
--
ALTER TABLE `transparency`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
