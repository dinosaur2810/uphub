-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2026 at 05:15 AM
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
-- Database: `uphub`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `item_type` enum('job','financial_aid','social_service') NOT NULL DEFAULT 'job',
  `job_id` int(10) UNSIGNED DEFAULT NULL,
  `job_seeker_id` int(10) UNSIGNED DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'submitted',
  `cover_letter` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `item_type`, `job_id`, `job_seeker_id`, `guest_name`, `guest_email`, `status`, `cover_letter`, `applied_at`) VALUES
(1, 'job', 1, 1, NULL, NULL, 'submitted', 'hello', '2026-04-07 16:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `financial_aid_programs`
--

CREATE TABLE `financial_aid_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `eligibility` text DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `exact_address` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `posted_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `moderation_status` enum('pending_approval','published','rejected') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_aid_programs`
--

INSERT INTO `financial_aid_programs` (`id`, `title`, `description`, `eligibility`, `contact_info`, `exact_address`, `status`, `created_by`, `posted_by_user_id`, `moderation_status`, `created_at`) VALUES
(1, 'Emergency Rent Assistance', 'Short-term help for households facing eviction risk.', 'Income below area median; documentation required.', 'aid@uplifthub.local', 'Tondo, Manila', 'active', 3, NULL, 'published', '2026-04-07 15:04:56'),
(2, 'Utility Relief Fund', 'Help with heating and electric bills in winter months.', 'Past-due notice required.', 'utilities@uplifthub.local', 'Tondo, Manila', 'active', 3, NULL, 'published', '2026-04-07 15:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `recruiter_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `exact_address` varchar(500) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `status` enum('draft','pending_approval','published','closed') NOT NULL DEFAULT 'pending_approval',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `recruiter_id`, `title`, `description`, `location`, `exact_address`, `salary_range`, `status`, `created_at`) VALUES
(1, 2, 'Part-Time Community Outreach Assistant', 'Support local outreach events and data collection.', 'Chicago, IL', 'Tondo, Manila', '$18/hr', 'published', '2026-04-07 15:04:56'),
(2, 2, 'Warehouse Associate', 'Light packing and inventory. Training provided.', 'Chicago, IL', 'Tondo, Manila', '$16-17/hr', 'published', '2026-04-07 15:04:56'),
(3, 2, 'Administrative Intern', 'Office support; pending admin approval.', 'Remote', NULL, 'Stipend', 'pending_approval', '2026-04-07 15:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `job_seeker_profiles`
--

CREATE TABLE `job_seeker_profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `income_bracket` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_seeker_profiles`
--

INSERT INTO `job_seeker_profiles` (`user_id`, `contact`, `education`, `skills`, `location`, `income_bracket`, `profile_picture`) VALUES
(1, '555-0100', 'High school diploma; community college courses', 'Customer service, data entry', 'Springfield, IL', '$15,000 - $25,000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(500) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1, 'Welcome to UpLiftHub. Browse jobs and programs to get started.', 'success', 0, '2026-04-07 15:04:56'),
(2, 2, 'Your company profile is approved. You can post jobs.', 'info', 0, '2026-04-07 15:04:56'),
(3, 3, 'Admin dashboard ready. Review pending recruiters and jobs.', 'info', 0, '2026-04-07 15:04:56'),
(4, 2, 'New application for: Part-Time Community Outreach Assistant', 'info', 0, '2026-04-07 16:01:32'),
(5, 1, 'Application submitted for Part-Time Community Outreach Assistant.', 'success', 0, '2026-04-07 16:01:32');

-- --------------------------------------------------------

--
-- Table structure for table `recruiter_profiles`
--

CREATE TABLE `recruiter_profiles` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recruiter_profiles`
--

INSERT INTO `recruiter_profiles` (`user_id`, `company_name`, `contact`, `location`, `industry`, `company_logo`) VALUES
(2, 'Community Works Nonprofit', '555-0200', 'Chicago, IL', 'Nonprofit / Workforce', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `social_services`
--

CREATE TABLE `social_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `exact_address` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `posted_by_user_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('pending_approval','published','rejected','active') NOT NULL DEFAULT 'active',
  `moderation_status` enum('pending_approval','published','rejected') NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `social_services`
--

INSERT INTO `social_services` (`id`, `name`, `description`, `exact_address`, `category`, `phone`, `latitude`, `longitude`, `posted_by_user_id`, `status`, `moderation_status`, `created_at`) VALUES
(1, 'Northside Food Pantry', 'Weekly groceries and nutrition counseling.', 'Tondo, Manila', 'Food', '555-0300', 41.8781000, -87.6298000, NULL, 'active', 'published', '2026-04-07 15:04:56'),
(2, 'Hope Career Center', 'Resume help, job fairs, and training referrals.', 'Tondo, Manila', 'Employment', '555-0400', 41.8810000, -87.6230000, NULL, 'active', 'published', '2026-04-07 15:04:56'),
(3, 'Family Health Clinic', 'Sliding-scale medical and mental health services.', 'Tondo, Manila', 'Health', '555-0500', NULL, NULL, NULL, 'active', 'published', '2026-04-07 15:04:56'),
(4, 'Verification Service 1775575372', 'Test description', '123 Test St', 'Testing', '555-1234', NULL, NULL, NULL, 'active', 'published', '2026-04-07 15:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('job_seeker','recruiter','admin') NOT NULL,
  `name` varchar(255) NOT NULL,
  `recruiter_status` enum('n/a','pending','approved','rejected') NOT NULL DEFAULT 'n/a',
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `name`, `recruiter_status`, `reset_token`, `reset_expires`, `created_at`) VALUES
(1, 'test@test.com', '$2y$10$q.JVobFNrRI0Vv.QscmiB.52nV5k5CvM62BrsH.BZD8yKYTKWJqI2', 'job_seeker', 'Demo Job Seeker', 'n/a', NULL, NULL, '2026-04-07 15:04:56'),
(2, 'recruiter@test.com', '$2y$10$q.JVobFNrRI0Vv.QscmiB.52nV5k5CvM62BrsH.BZD8yKYTKWJqI2', 'recruiter', 'Demo Recruiter', 'approved', NULL, NULL, '2026-04-07 15:04:56'),
(3, 'admin@test.com', '$2y$10$q.JVobFNrRI0Vv.QscmiB.52nV5k5CvM62BrsH.BZD8yKYTKWJqI2', 'admin', 'Demo Admin', 'n/a', NULL, NULL, '2026-04-07 15:04:56');

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `communication_logs`
--

CREATE TABLE `communication_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `receiver_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_app_job` (`job_id`),
  ADD KEY `idx_app_seeker` (`job_seeker_id`);

--
-- Indexes for table `financial_aid_programs`
--
ALTER TABLE `financial_aid_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fa_moderation` (`moderation_status`),
  ADD KEY `fk_aid_admin` (`created_by`),
  ADD KEY `fk_fa_poster` (`posted_by_user_id`),
  ADD KEY `idx_fa_exact_address` (`exact_address`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jobs_status` (`status`),
  ADD KEY `fk_jobs_recruiter` (`recruiter_id`),
  ADD KEY `idx_jobs_exact_address` (`exact_address`);

--
-- Indexes for table `job_seeker_profiles`
--
ALTER TABLE `job_seeker_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`);

--
-- Indexes for table `recruiter_profiles`
--
ALTER TABLE `recruiter_profiles`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `social_services`
--
ALTER TABLE `social_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ss_moderation` (`status`),
  ADD KEY `fk_ss_poster` (`posted_by_user_id`);

--
-- Indexes for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comm_app` (`application_id`),
  ADD KEY `idx_comm_sender` (`sender_id`),
  ADD KEY `idx_comm_receiver` (`receiver_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_recruiter_status` (`recruiter_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `financial_aid_programs`
--
ALTER TABLE `financial_aid_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `social_services`
--
ALTER TABLE `social_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `communication_logs`
--
ALTER TABLE `communication_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_app_seeker` FOREIGN KEY (`job_seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_aid_programs`
--
ALTER TABLE `financial_aid_programs`
  ADD CONSTRAINT `fk_aid_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fa_poster` FOREIGN KEY (`posted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_recruiter` FOREIGN KEY (`recruiter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_seeker_profiles`
--
ALTER TABLE `job_seeker_profiles`
  ADD CONSTRAINT `fk_js_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recruiter_profiles`
--
ALTER TABLE `recruiter_profiles`
  ADD CONSTRAINT `fk_rec_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `social_services`
--
ALTER TABLE `social_services`
  ADD CONSTRAINT `fk_ss_poster` FOREIGN KEY (`posted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `communication_logs`
--
ALTER TABLE `communication_logs`
  ADD CONSTRAINT `fk_comm_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comm_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
