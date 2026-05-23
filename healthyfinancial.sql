-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 03:46 PM
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
-- Database: `healthyfinancial`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('healthyfinancial-cache-ptptn-daily-note:9:2026-05-23', 's:55:\"Keep it steady lah, one careful day protects the month.\";', 1779580800),
('healthyfinancial-cache-ptptn-dashboard-intro:9:2026-05-23', 's:71:\"PTPTN runway active: RM5.56 safe daily spend, no surprise splurges wei.\";', 1779580800),
('healthyfinancial-cache-ptptn-dashboard-intro:9:2026-05-23-12', 's:59:\"PTPTN is covering the gap now; 9 days left, spend steadily.\";', 1779541200),
('healthyfinancial-cache-ptptn-hourly-note:9:2026-05-23-12', 's:56:\"Spend slowly this hour; your PTPTN runway still matters.\";', 1779541200);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `university_name` varchar(100) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaderboard`
--

INSERT INTO `leaderboard` (`user_id`, `university_name`, `points`, `created_at`, `updated_at`) VALUES
(4, 'pekan', 37, '2026-05-21 22:49:27', '2026-05-22 22:08:33'),
(6, 'UM', 13, '2026-05-22 03:40:11', '2026-05-22 03:47:42'),
(8, 'UMPSA', 0, '2026-05-23 04:50:22', '2026-05-23 04:50:22'),
(9, 'UTEM', 21, '2026-05-23 02:42:10', '2026-05-23 02:42:38');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2024_01_01_000001_add_columns_to_users_table', 1),
(3, '2024_01_01_000002_create_transactions_table', 1),
(4, '2024_01_01_000003_create_budgets_table', 1),
(5, '2024_01_01_000004_create_user_challenges_table', 1),
(6, '2024_01_01_000005_create_leaderboard_table', 1),
(7, '0001_01_01_000001_create_cache_table', 2),
(8, '0001_01_01_000002_create_jobs_table', 2),
(9, '2026_05_23_000001_drop_budgets_table', 3),
(10, '2026_05_23_000002_drop_user_challenges_table', 4),
(11, '2026_05_23_000003_rename_campus_to_university_name', 5);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('asd854924@gmail.com', '$2y$12$xJD9hP/rwzMnDCC6TepqROyfGXrHIKZvd.iy4M8CgGXX0FG.6eU8e', '2026-05-23 00:02:23'),
('asd@asd.com', '$2y$12$b.P8ymFrPpeg/NSK.vsIx.ehYsNpZvXwFhfS/mhwsep1g4jsQ/etu', '2026-05-22 23:36:22'),
('ninjaago4@gmail.com', '$2y$12$8qR71gZ4zOCjhOEdAZdUYO5B/izYH77z1yyafC3zDjYvFqU87BXX6', '2026-05-23 01:31:14');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('RzYoTCr7kNTdj57YudJ3VvqkyRGnEMMDTWmsVbD2', 8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWkdsbEk1VnVQR1lFa1J6cUdwOXo0MHEzeDY4RjR3SG51TkFTemNUaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6ODt9', 1779543172),
('wCV3HzSGgiJDwY5HRQCkPBWFclrnGCkAYg3TUDV9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTk1lV244aHZBSml0SWg0NklLY0pTckpNVW1yN1hubXVFSE1wUUVqbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO319', 1779523851),
('Xs3cK98LAy1ZttUhLPM3SpTjhpGzOFhLglxN87DI', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.121.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUXpQa29HQkNLOTRkeFQ1N0p5aUI4N1pXRUQxb1BUbXZ2cVpaUk5wbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6MzoidXJsIjthOjE6e3M6ODoiaW50ZW5kZWQiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO319', 1779534743);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'other',
  `is_auto_categorized` tinyint(1) NOT NULL DEFAULT 0,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `description`, `amount`, `category`, `is_auto_categorized`, `transaction_date`, `created_at`, `updated_at`) VALUES
(12, 4, 'Nasi Lemak', 5.50, 'food', 1, '2026-05-22', '2026-05-22 03:35:43', '2026-05-22 03:35:43'),
(13, 4, 'Teh O', 2.00, 'food', 1, '2026-05-22', '2026-05-22 03:35:43', '2026-05-22 03:35:43'),
(14, 4, 'Nasi Lemak', 5.50, 'food', 1, '2026-05-22', '2026-05-22 03:36:15', '2026-05-22 03:36:15'),
(15, 4, 'Teh O', 2.00, 'food', 1, '2026-05-22', '2026-05-22 03:36:15', '2026-05-22 03:36:15'),
(16, 4, 'Nasi Lemak', 5.50, 'food', 1, '2026-05-22', '2026-05-22 03:37:34', '2026-05-22 03:37:34'),
(17, 4, 'Teh O', 2.00, 'food', 1, '2026-05-22', '2026-05-22 03:37:34', '2026-05-22 03:37:34'),
(18, 6, 'duit', 19.00, 'Family care', 0, '2026-05-22', '2026-05-22 03:40:11', '2026-05-22 03:40:11'),
(19, 6, 'Nasi Lemak', 5.50, 'food', 1, '2026-05-22', '2026-05-22 03:47:42', '2026-05-22 03:47:42'),
(20, 6, 'Teh O', 2.00, 'food', 1, '2026-05-22', '2026-05-22 03:47:42', '2026-05-22 03:47:42'),
(21, 4, 'Set A', 27.80, 'food', 1, '2026-05-22', '2026-05-22 11:59:28', '2026-05-22 11:59:28'),
(22, 4, 'Lane 1', 4.08, 'other', 1, '2026-05-22', '2026-05-22 12:00:56', '2026-05-22 12:00:56'),
(23, 4, '= 4 2.00 it', 2.00, 'other', 1, '2026-05-22', '2026-05-22 12:01:50', '2026-05-22 12:01:50'),
(24, 4, '3.00 1 3:00 &', 3.00, 'other', 1, '2026-05-22', '2026-05-22 12:02:34', '2026-05-22 12:02:34'),
(25, 4, '5.00 1 5.00\" =', 5.00, 'other', 1, '2026-05-22', '2026-05-22 12:02:34', '2026-05-22 12:02:34'),
(26, 4, '4.00 1 400 &', 4.00, 'other', 1, '2026-05-22', '2026-05-22 12:02:34', '2026-05-22 12:02:34'),
(27, 4, 'Set A', 27.80, 'food', 1, '2026-05-22', '2026-05-22 12:03:42', '2026-05-22 12:03:42'),
(28, 4, 'Set A', 27.80, 'food', 1, '2026-05-22', '2026-05-22 12:04:06', '2026-05-22 12:04:06'),
(29, 4, 'Medicines', 500.00, 'health', 1, '2026-05-22', '2026-05-22 12:07:10', '2026-05-22 12:07:10'),
(30, 4, 'Peritoneal alysis', 4000.00, 'other', 1, '2026-05-22', '2026-05-22 12:07:10', '2026-05-22 12:07:10'),
(31, 4, 'ayam goreng', 6.00, 'Food', 0, '2026-05-23', '2026-05-22 20:13:37', '2026-05-22 20:13:37'),
(32, 9, 'ayam goreng', 50.00, 'Food', 0, '2026-05-23', '2026-05-23 02:42:10', '2026-05-23 02:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `monthly_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ptptn_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saving_streak` int(11) NOT NULL DEFAULT 0,
  `university_name` varchar(100) DEFAULT NULL,
  `ptptn_mode` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `monthly_allowance`, `ptptn_balance`, `saving_streak`, `university_name`, `ptptn_mode`, `created_at`, `updated_at`) VALUES
(4, 'asd', 'asd@asd.com', NULL, '$2y$12$tPyVK7zyKFx./1Hb4TztjOf27of6ajUEav2SZD9zi1S1GmFmfz2he', NULL, 10.00, 10000.00, 2, 'pekan', 0, '2026-05-21 22:26:39', '2026-05-22 22:08:33'),
(5, 'qwe', 'qwe@qwe.com', NULL, '$2y$12$FQiyHk0PmFuDtXHcQSxZt.2FKgaQL0qaw7Jvz4oc2ZcgMR0aaTrSq', NULL, 0.00, 0.00, 0, 'UMPSA', 0, '2026-05-21 22:57:15', '2026-05-21 22:57:15'),
(6, 'AAA', 'we@we.com', NULL, '$2y$12$wdHbL3svxgYf8nOtbmKPpOdIlzH0nKwPlLCPhdojZ1U/j.3NNLlTW', NULL, 100.00, 0.00, 1, 'UM', 0, '2026-05-22 03:39:48', '2026-05-22 03:40:20'),
(7, 'ash', 'ninjaago4@gmail.com', NULL, '$2y$12$urFI2F75wdoAGwSv5w525eBobQp.5L2KB2ZgHI9nVX8Xc2EGTUm6q', NULL, 0.00, 0.00, 0, 'UMPSA', 0, '2026-05-22 22:13:03', '2026-05-22 22:13:03'),
(8, 'Mimi', 'asd854924@gmail.com', NULL, '$2y$12$7DpXh1S0yI6//sKtM5ML6OIMVD/F5wxkMus2g.4TfX0MU4Ef7r02C', NULL, 1000.00, 0.00, 0, 'UMPSA', 0, '2026-05-22 23:40:18', '2026-05-23 04:50:22'),
(9, 'Gigi', 'gigi@gigi.com', NULL, '$2y$12$sIHPF5fjSHz9TLaFolN5Iub3vyYuDtNhHwXHOvXSs6ht1LCTNLZiC', NULL, 10.00, 100.00, 1, 'UTEM', 1, '2026-05-23 02:41:43', '2026-05-23 04:09:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `campus` (`university_name`),
  ADD KEY `points` (`points`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `last_activity` (`last_activity`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transaction_date` (`transaction_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
