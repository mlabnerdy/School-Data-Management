-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2026 at 09:27 AM
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
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `current_latest_appointment` varchar(255) DEFAULT NULL,
  `deped_email` varchar(150) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `degree_finished` varchar(255) DEFAULT NULL,
  `specialization_prc_eligibility` varchar(255) DEFAULT NULL,
  `tin_no` varchar(30) DEFAULT NULL,
  `birthdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `employee_id`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `email`, `position_department`, `other_info`, `created_at`, `updated_at`, `plantilla_no`, `first_day_of_service`, `current_latest_appointment`, `deped_email`, `personal_email`, `degree_finished`, `specialization_prc_eligibility`, `tin_no`, `birthdate`) VALUES
(1, '55-412341', 'uploads/staff/1e195c3d04de8f8f758627d7.jpg', 'Pony Plum', '1989-05-10', 'Male', 'P5 Brgy. Dulo', '09091005012', '', 'Registrar', '', '2026-08-08 06:40:37', '2026-08-08 06:43:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `emergency_contact` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `lrn`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `parent_guardian`, `grade_section`, `other_info`, `created_at`, `updated_at`, `school_id`, `school_year`, `emergency_name`, `emergency_address`, `emergency_contact`) VALUES
(2, '500223090129', 'uploads/student/9c8376caecfe8279f2b1de75.jpg', 'Juan Dela Cruz', '2018-08-14', 'Male', 'P-5 Brgy. V Doon St. Bacoor City', '09325010012', 'Maria Dela Cruz', 'Grade I - Wonderful', '', '2026-08-14 06:36:57', '2026-08-14 06:36:57', '500634', '2026-2027', 'Maria Dela Cruz', 'P-5 Brgy. V Doon St. Bacoor City', '09500093912');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `current_latest_appointment` varchar(255) DEFAULT NULL,
  `deped_email` varchar(150) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `degree_finished` varchar(255) DEFAULT NULL,
  `specialization_prc_eligibility` varchar(255) DEFAULT NULL,
  `tin_no` varchar(30) DEFAULT NULL,
  `birthdate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `employee_id`, `photo`, `full_name`, `date_of_birth`, `gender`, `address`, `contact_number`, `email`, `position_department`, `other_info`, `created_at`, `updated_at`, `plantilla_no`, `first_day_of_service`, `current_latest_appointment`, `deped_email`, `personal_email`, `degree_finished`, `specialization_prc_eligibility`, `tin_no`, `birthdate`) VALUES
(1, '55-412341', 'uploads/teacher/ffb35fb6ddec4c728573261f.webp', 'Mila Milenio', '1995-06-01', 'Female', 'P2 Brgy. 4, Somewhere Anywhere', '09325010012', 'pogiako123@gmail.com', 'Teacher II', '', '2026-08-08 06:25:02', '2026-08-11 14:26:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(2, 'John Doe', 'john', 'adminako123', 'Administrator', '2026-08-08 06:13:31'),
(3, 'System Administrator', 'admin', '$2y$10$Ac8nA3ihwRwYAD79aVa0Cek8PoEntBVi52MfUFgGM2Glpj692PJcC', 'Administrator', '2026-08-08 06:15:44'),
(4, 'Administrator 1', 'admin1', '$2y$10$81EtCRGSc.iPTmy8j./ddehsAVVV9gGqAdk3sfnL2T.TYjmSILf1O', 'Administrator', '2026-08-11 09:14:04'),
(5, 'Teacher Account', 'teacher1', '$2y$10$wG7z.C4jaU3sl7L1DyGQo.cSfGUw5.ipzN8zMiLSQ5e1rKMXniOl.', 'Teacher', '2026-08-14 06:46:24');

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
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

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
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
