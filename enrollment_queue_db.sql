-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2026 at 05:39 AM
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
-- Database: `enrollment_queue_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `office_name` varchar(150) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `office` varchar(100) DEFAULT 'Unknown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `office_name`, `username`, `password`, `office`) VALUES
(1, 'Registrar', 'registrar', '1234', 'Unknown'),
(2, 'Medical/Nurse', 'nurse', '1234', 'Unknown'),
(3, 'Library', 'library', '1234', 'Unknown'),
(4, 'MIS', 'mis', '1234', 'Unknown'),
(5, 'Student Affairs', 'student_affairs', '1234', 'Unknown'),
(6, 'DDRM Office', 'ddrm', '1234', 'Unknown');

-- --------------------------------------------------------

--
-- Table structure for table `eligible_students`
--

CREATE TABLE `eligible_students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eligible_students`
--

INSERT INTO `eligible_students` (`id`, `student_id`, `student_number`, `full_name`, `program`, `year_level`, `created_at`) VALUES
(1, NULL, '2024-0001', 'Juan Dela Cruz', 'BSIT', 1, '2026-04-26 15:23:56'),
(2, NULL, '2024-0002', 'Maria Santos', 'BSBA', 2, '2026-04-26 15:23:56'),
(3, NULL, '2024-0003', 'Pedro Reyes', 'BSED', 3, '2026-04-26 15:23:56'),
(4, NULL, '2025-9005', 'John Mark FLores', 'BSCE', 1, '2026-05-12 06:44:44'),
(5, NULL, '2025-0009', 'Benjamin Gardo', 'BSEE', NULL, '2026-05-12 06:44:44');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_steps`
--

CREATE TABLE `enrollment_steps` (
  `step_id` int(11) NOT NULL,
  `step_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `step_order` int(11) DEFAULT NULL,
  `assigned_admin_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_steps`
--

INSERT INTO `enrollment_steps` (`step_id`, `step_name`, `location`, `step_order`, `assigned_admin_id`, `is_active`) VALUES
(1, 'STEP 1: Enrollment Requirements & Registration', 'Registrar Office (Ground Floor, Bldg 1)', 1, 1, 1),
(2, 'STEP 2: Medical Requirements & School Uniform', 'Medical/Nurse Office (Seminar Room, Bldg 1)', 2, 2, 1),
(3, 'STEP 3: Library Card', 'Library (2nd Floor, Bldg 1)', 3, 3, 1),
(4, 'STEP 4: School ID & Student Accounts (Portal, Blackboard, Outlook)', 'MIS Office (4th Floor, Bldg 1)', 4, 4, 1),
(5, 'STEP 5: Facebook Group Enrollment (Batch 2024)', 'Office of Student Affairs (Ground Floor, Bldg 1)', 5, 5, 1),
(6, 'STEP 6: NSTP Component (CWTS / ROTC / DRRM)', 'DRRM Office (Ground Floor, Bldg 2)', 6, 6, 1),
(7, 'STEP 7: Certificate of Registration (COR) & Final Reminders', 'Registrar Office (Ground Floor, Bldg 1)', 7, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `access_code` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `birthdate` date NOT NULL DEFAULT '2000-01-01'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `program`, `access_code`, `created_at`, `birthdate`) VALUES
('10', 'Pedro Reyes', 'BSCE', 'CDM-2026-0004', '2026-04-26 16:12:26', '2004-08-17'),
('11', 'Maria Santos', 'BSCpE', 'CDM-2026-0005', '2026-05-08 08:33:55', '2005-01-30'),
('12', 'Juan Dela Cruz', 'BSCE', 'CDM-2026-0006', '2026-05-08 08:43:46', '2003-04-12'),
('13', 'Brandon Rull', 'BSCpE', 'CDM-2026-0007', '2026-05-12 06:51:16', '2004-10-05'),
('14', 'Benjamin Gardo', 'BSCE', 'CDM-2026-0008', '2026-05-12 06:51:45', '2005-07-19'),
('15', 'Desy Dally Collado', 'BSCpE', 'CDM-2026-0009', '2026-05-17 05:52:57', '2004-12-03'),
('20261020001', 'Hannah Sagario', 'BSCpE', 'CDM-2026-0010', '2026-05-21 12:39:48', '2003-09-25'),
('20261020002', 'Kent Manos', 'BSEcE', 'CDM-2026-0011', '2026-05-21 12:42:29', '2004-03-08'),
('20261020003', 'Anthony Galaban', 'BSAR', 'CDM-2026-0012', '2026-05-28 06:31:05', '2000-01-01'),
('20261020004', 'jffahbdahbd', 'BSAR', 'CDM-2026-0013', '2026-05-28 06:32:12', '2000-01-01'),
('7', 'James Cortez Sagario', 'BSCpE', 'CDM-2026-0001', '2026-04-26 16:11:54', '2005-08-15'),
('8', 'Quian Marcus Pluma', 'BSCpE', 'CDM-2026-0002', '2026-04-26 16:12:01', '2005-02-21'),
('9', 'Juan Dela Cruz', 'BSENSE', 'CDM-2026-0003', '2026-04-26 16:12:06', '2003-11-09');

-- --------------------------------------------------------

--
-- Table structure for table `student_steps`
--

CREATE TABLE `student_steps` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_steps`
--

INSERT INTO `student_steps` (`id`, `student_id`, `step_id`, `status`, `updated_at`) VALUES
(1, 10, 1, 'completed', '2026-05-08 08:38:51'),
(2, 7, 1, 'completed', '2026-05-12 05:55:43'),
(3, 8, 3, 'pending', '2026-05-12 05:56:26'),
(8, 8, 2, 'completed', '2026-04-26 18:11:16'),
(12, 7, 5, 'completed', '2026-04-26 18:11:31'),
(14, 7, 4, 'completed', '2026-05-12 05:55:43'),
(16, 11, 1, 'completed', '2026-05-08 08:36:29'),
(24, 11, 3, 'completed', '2026-05-08 08:39:43'),
(28, 7, 2, 'completed', '2026-05-12 05:55:53'),
(31, 7, 3, 'pending', '2026-05-12 05:56:50'),
(36, 14, 1, 'completed', '2026-05-12 06:52:59'),
(40, 14, 2, 'completed', '2026-05-12 06:54:54'),
(41, 15, 1, 'completed', '2026-05-17 05:54:35'),
(45, 15, 2, 'completed', '2026-05-17 05:54:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `eligible_students`
--
ALTER TABLE `eligible_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `enrollment_steps`
--
ALTER TABLE `enrollment_steps`
  ADD PRIMARY KEY (`step_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `access_code` (`access_code`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `student_id_2` (`student_id`);

--
-- Indexes for table `student_steps`
--
ALTER TABLE `student_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_step` (`student_id`,`step_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `eligible_students`
--
ALTER TABLE `eligible_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollment_steps`
--
ALTER TABLE `enrollment_steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_steps`
--
ALTER TABLE `student_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
