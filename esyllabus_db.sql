-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 06:13 PM
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
-- Database: `esyllabus_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `student_number` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `log_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `student_number`, `student_name`, `action`, `details`, `log_time`) VALUES
(1, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: CWTS', '2026-05-08 23:16:36'),
(2, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-08 23:19:10'),
(3, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-08 23:19:26'),
(4, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-08 23:19:38'),
(5, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-08 23:45:26'),
(6, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'Viewed Syllabus', 'Opened syllabus for: CWTS', '2026-05-08 23:45:28'),
(7, '2023-1234-SJ-0', 'Jannie Pardilla', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-10 12:51:12'),
(8, '2023-1234-SJ-0', 'Jannie Pardilla', 'Viewed Syllabus', 'Opened syllabus for: Computer Programming 1', '2026-05-10 13:08:53');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `username`, `password`) VALUES
(3, NULL, 'Jannie', '12345'),
(4, NULL, 'Jean', '$2y$10$DNrwGcpTSAWgTgpUj4zSauHa0ofihoiMfloCw1dmaDv97if9JVhBC'),
(5, 'NR-Heart Aman R Cequena', 'Heart', 'PUP@123');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `course_category` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `course_category`, `file_path`, `uploaded_at`) VALUES
(2, 'Fundamentals of Information Technology', 'N', 'BSIT', 'books_library/1778251417_FIT_Comp_1sem.pdf', '2026-05-08 14:43:37');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `section` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `birthday` date NOT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_number`, `full_name`, `course`, `section`, `created_at`, `birthday`, `status`, `password`) VALUES
(8, '2023-00549-SJ-0', 'NR-Heart Aman R Cequena', 'BSIT', '', '2026-05-08 11:46:15', '2005-02-14', 'Archived', 'PUP@12345'),
(9, '2023-12345-SJ-0', 'Andre Malapaya', 'BSIT', '', '2026-05-08 15:44:52', '2002-09-17', 'Archived', 'PUP@12345'),
(10, '2023-1234-SJ-0', 'Jannie Pardilla', 'BSHM', '', '2026-05-10 04:30:41', '2000-01-01', 'Active', 'PUP@12345');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `year_level` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_code` varchar(20) NOT NULL,
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `subject_code`, `year_level`, `semester`, `file_path`, `date_added`, `course_code`, `views`) VALUES
(21, 'Computer Programming 1', 'COMP002', 1, 1, 'uploads/1778388657_CWTS10013_Civic_Welfare_Training_Service_1(1).pdf', '2026-05-10 04:50:57', 'BSHM', 2),
(22, 'CWTS', 'CWTS1', 2, 1, 'uploads/1778389582_CWTS10013_Civic_Welfare_Training_Service_1(1).pdf', '2026-05-10 05:06:22', 'BSED', 0),
(23, 'CWTS1', 'CWTS1', 2, 1, 'uploads/1778389700_CWTS10013_Civic_Welfare_Training_Service_1(1).pdf', '2026-05-10 05:08:20', 'BSPSYCH', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
