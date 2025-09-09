-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 08, 2025 at 01:10 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u435643473_color`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$pc7lZaxsonSgnyVzVMs93uGZentyJb7A2VHAN7djtkuKJ3Le9AfIG');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `image_path`, `link_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(8, 'Join Now', 'uploads/banners/banner_1757328356_1825.jpg', '', 0, 1, '2025-09-08 10:45:56', '2025-09-08 10:45:56'),
(9, '.', 'uploads/banners/banner_1757328391_9036.jpg', '', 0, 1, '2025-09-08 10:46:31', '2025-09-08 10:46:31'),
(10, 'f', 'uploads/banners/banner_1757328400_6861.jpg', '', 0, 1, '2025-09-08 10:46:40', '2025-09-08 10:46:40'),
(11, 'welcome', 'uploads/banners/banner_1757328785_9671.jpg', '', 0, 1, '2025-09-08 10:53:05', '2025-09-08 11:31:00');

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `transaction_id`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 4, 125.00, '23432345644', 'Approved', 'esrfghmn', '2025-09-08 03:51:14', '2025-09-08 03:51:32'),
(2, 4, 1253.00, '255336636', 'Rejected', 'asc', '2025-09-08 03:52:03', '2025-09-08 03:52:09'),
(3, 10, 100.00, '1135054', 'Approved', 'esfdef', '2025-09-08 05:02:30', '2025-09-08 05:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `legal_pages`
--

CREATE TABLE `legal_pages` (
  `id` int(11) NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `legal_pages`
--

INSERT INTO `legal_pages` (`id`, `page_key`, `title`, `content`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'terms_of_service', 'Terms of Service', '<h2>Terms of Service</h2>\r\n</div>\r\n<p><strong>Last updated:</strong> September 8, 2025</p>\r\n\r\n<h3>1. Acceptance of Terms</h3>\r\n<p>By accessing and using Expo Tournament gaming platform, you accept and agree to be bound by the terms and provision of this agreement.</p>\r\n\r\n<h3>2. Use License</h3>\r\n<p>Permission is granted to temporarily download one copy of Expo Tournament materials for personal, non-commercial transitory viewing only.</p>\r\n\r\n<h3>3. Account Registration</h3>\r\n<p>Users must provide accurate and complete information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>\r\n\r\n<h3>4. Tournament Participation</h3>\r\n<p>Participation in tournaments requires entry fees as specified. Winners will be determined based on game performance and platform rules.</p>\r\n\r\n<h3>5. Payment and Refunds</h3>\r\n<p>All tournament entry fees are non-refundable unless the tournament is cancelled by Expo Tournament. Winnings will be credited to user wallets.</p>\r\n\r\n<h3>6. Prohibited Conduct</h3>\r\n<p>Users must not engage in cheating, fraud, or any behavior that violates fair play principles. Violation may result in account suspension.</p>\r\n\r\n<h3>7. Limitation of Liability</h3>\r\n<p>Expo Tournament shall not be liable for any damages arising from the use of this platform or participation in tournaments.</p>\r\n\r\n<h3>8. Contact Information</h3>\r\n<p>For questions about these Terms of Service, please contact us through our support channels.</p>\'', 1, '2025-09-08 10:34:23', '2025-09-08 11:15:31'),
(2, 'privacy_policy', 'Privacy Policy', '<h2>Privacy Policy</h2>\n<p><strong>Last updated:</strong> September 8, 2025</p>\n\n<h3>1. Information We Collect</h3>\n<p>We collect information you provide directly to us, such as when you create an account, participate in tournaments, or contact us for support.</p>\n\n<h4>Personal Information:</h4>\n<ul>\n<li>Username and email address</li>\n<li>Payment information (UPI ID)</li>\n<li>Tournament participation history</li>\n<li>Communication preferences</li>\n</ul>\n\n<h3>2. How We Use Your Information</h3>\n<p>We use the information we collect to:</p>\n<ul>\n<li>Provide and maintain our gaming platform</li>\n<li>Process tournament entries and payments</li>\n<li>Send you tournament updates and notifications</li>\n<li>Improve our services and user experience</li>\n<li>Comply with legal obligations</li>\n</ul>\n\n<h3>3. Information Sharing</h3>\n<p>We do not sell, trade, or rent your personal information to third parties.</p>\n\n<h3>4. Data Security</h3>\n<p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>\n\n<h3>5. Contact Us</h3>\n<p>If you have questions about this Privacy Policy, please contact our support team.</p>', 1, '2025-09-08 10:34:23', '2025-09-08 11:03:03'),
(3, 'refund_policy', 'Refund Policy', '<h2>Refund Policy</h2>\r\n<p><strong>Last updated:</strong> September 8, 2025</p>\r\n\r\n<h3>1. General Policy</h3>\r\n<p>All tournament entry fees are generally non-refundable once paid. This policy ensures fair competition and platform stability.</p>\r\n\r\n<h3>2. Eligible Refund Scenarios</h3>\r\n<p>Refunds may be issued in the following circumstances:</p>\r\n<ul>\r\n<li><strong>Tournament Cancellation:</strong> If we cancel a tournament, full refunds will be issued</li>\r\n<li><strong>Technical Issues:</strong> If technical problems prevent tournament participation</li>\r\n<li><strong>Duplicate Payments:</strong> If you accidentally make duplicate payments</li>\r\n</ul>\r\n\r\n<h3>3. Refund Process</h3>\r\n<p>To request a refund:</p>\r\n<ol>\r\n<li>Contact our support team within 24 hours of the issue</li>\r\n<li>Provide your username and transaction details</li>\r\n<li>Explain the reason for the refund request</li>\r\n<li>Allow 3-5 business days for processing</li>\r\n</ol>', 1, '2025-09-08 10:34:23', '2025-09-08 10:34:23'),
(4, 'responsible_gaming', 'Responsible Gaming', '<h2>Responsible Gaming</h2>\r\n<p><strong>Last updated:</strong> September 8, 2025</p>\r\n\r\n<h3>1. Our Commitment</h3>\r\n<p>Adept Play is committed to promoting responsible gaming and ensuring a safe, enjoyable experience for all users.</p>\r\n\r\n<h3>2. Age Verification</h3>\r\n<p>Our platform is intended for users aged 18 and above. We do not knowingly collect information from minors.</p>\r\n\r\n<h3>3. Setting Limits</h3>\r\n<p>We encourage users to set personal limits on:</p>\r\n<ul>\r\n<li>Daily/weekly spending on tournament entries</li>\r\n<li>Time spent on the platform</li>\r\n<li>Number of tournaments participated per day</li>\r\n</ul>\r\n\r\n<h3>4. Getting Help</h3>\r\n<p>If you or someone you know needs help with gaming problems, resources are available through our support channels.</p>', 1, '2025-09-08 10:34:23', '2025-09-08 10:34:23'),
(5, 'fair_play_policy', 'Fair Play Policy', '<h2>Fair Play Policy</h2>\r\n<p><strong>Last updated:</strong> September 8, 2025</p>\r\n\r\n<h3>1. Our Commitment to Fair Play</h3>\r\n<p>Adept Play is dedicated to maintaining a fair, transparent, and enjoyable gaming environment for all participants.</p>\r\n\r\n<h3>2. Prohibited Activities</h3>\r\n<p>The following activities are strictly prohibited:</p>\r\n<ul>\r\n<li><strong>Cheating:</strong> Using unauthorized software, exploits, or hacks</li>\r\n<li><strong>Collusion:</strong> Working with other players to gain unfair advantages</li>\r\n<li><strong>Account Sharing:</strong> Allowing others to play on your account</li>\r\n<li><strong>Multi-Accounting:</strong> Creating multiple accounts to circumvent limits</li>\r\n</ul>\r\n\r\n<h3>3. Consequences</h3>\r\n<p>Violations may result in warnings, suspensions, or permanent bans depending on severity.</p>', 1, '2025-09-08 10:34:23', '2025-09-08 10:34:23'),
(6, 'community_guidelines', 'Community Guidelines', '<h2>Community Guidelines</h2>\r\n<p><strong>Last updated:</strong> September 8, 2025</p>\r\n\r\n<h3>1. Building a Positive Community</h3>\r\n<p>Our community guidelines help create a welcoming environment where all players can enjoy competitive gaming.</p>\r\n\r\n<h3>2. Respectful Communication</h3>\r\n<p>All users must:</p>\r\n<ul>\r\n<li>Treat others with respect and courtesy</li>\r\n<li>Use appropriate language in all communications</li>\r\n<li>Avoid discriminatory or offensive content</li>\r\n<li>Respect different skill levels and backgrounds</li>\r\n</ul>\r\n\r\n<h3>3. Prohibited Content</h3>\r\n<p>The following content is not allowed:</p>\r\n<ul>\r\n<li>Hate speech or discriminatory language</li>\r\n<li>Harassment, bullying, or threats</li>\r\n<li>Spam or repetitive messages</li>\r\n<li>Adult or inappropriate content</li>\r\n</ul>', 1, '2025-09-08 10:34:23', '2025-09-08 10:34:23'),
(7, 'contact_us', 'Contact Us', '<h2>Contact Us</h2>\r\n<p><strong>We are here to help!</strong></p>\r\n\r\n<h3>📧 Email Support</h3>\r\n<p><strong>General Inquiries:</strong> support@adeptplay.com<br>\r\n<strong>Technical Issues:</strong> admin@adeptplay.com<br>\r\n<strong>Billing Questions:</strong> billing@adeptplay.com</p>\r\n\r\n<h3>📱 Support Hours</h3>\r\n<p><strong>Monday - Friday:</strong> 9:00 AM - 8:00 PM IST<br>\r\n<strong>Saturday:</strong> 10:00 AM - 6:00 PM IST<br>\r\n<strong>Sunday:</strong> 12:00 PM - 6:00 PM IST</p>\r\n\r\n<h3>💡 Feedback</h3>\r\n<p>We value your feedback! Help us improve by sharing your suggestions and experiences.</p>', 1, '2025-09-08 10:34:23', '2025-09-08 10:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `joined_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `user_id`, `tournament_id`, `joined_at`) VALUES
(1, 4, 1, '2025-09-08 03:25:27'),
(2, 1, 5, '2025-09-08 03:31:20'),
(3, 4, 5, '2025-09-08 03:31:25'),
(4, 1, 2, '2025-09-08 04:32:45'),
(5, 4, 3, '2025-09-08 05:01:18'),
(6, 4, 4, '2025-09-08 05:01:53'),
(7, 4, 2, '2025-09-08 05:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `referrer_reward` decimal(10,2) NOT NULL,
  `referred_reward` decimal(10,2) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referred_id`, `referrer_reward`, `referred_reward`, `status`, `created_at`) VALUES
(1, 4, 18, 50.00, 25.00, 'completed', '2025-09-08 10:03:42'),
(2, 4, 19, 50.00, 25.00, 'completed', '2025-09-08 10:04:10'),
(3, 1, 20, 50.00, 25.00, 'completed', '2025-09-08 10:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'admin_upi_id', 'piyush@ybl', '2025-09-08 03:47:34', '2025-09-08 03:50:19'),
(2, 'admin_qr_code', 'admin_qr_1757326473.png', '2025-09-08 03:47:34', '2025-09-08 10:14:33'),
(3, 'referral_reward_referrer', '50', '2025-09-08 09:50:50', '2025-09-08 10:07:18'),
(4, 'referral_reward_referred', '25', '2025-09-08 09:50:50', '2025-09-08 10:07:18'),
(5, 'admin_referral_code', 'ADM001', '2025-09-08 09:50:50', '2025-09-08 09:50:50'),
(6, 'legal_pages_enabled', '1', '2025-09-08 10:34:23', '2025-09-08 10:34:23'),
(7, 'legal_footer_text', 'All rights reserved. © 2025 Adept Play Gaming Platform.', '2025-09-08 10:34:23', '2025-09-08 10:34:23');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `entry_fee` decimal(10,2) NOT NULL,
  `prize_pool` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(5,2) DEFAULT 0.00,
  `match_time` datetime NOT NULL,
  `room_id` varchar(100) DEFAULT '',
  `room_password` varchar(100) DEFAULT '',
  `status` enum('Upcoming','Live','Completed') DEFAULT 'Upcoming',
  `winner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `title`, `game_name`, `entry_fee`, `prize_pool`, `commission_percentage`, `match_time`, `room_id`, `room_password`, `status`, `winner_id`, `created_at`) VALUES
(1, 'PUBG Mobile Championship', 'PUBG Mobile', 100.00, 800.00, 20.00, '2025-09-09 03:18:59', '1234323', 'Asdfr45', 'Completed', 4, '2025-09-08 03:18:59'),
(2, 'Free Fire Battle Royale', 'Free Fire', 50.00, 400.00, 20.00, '2025-09-10 03:18:59', '', '', 'Upcoming', NULL, '2025-09-08 03:18:59'),
(3, 'Call of Duty Tournament', 'Call of Duty Mobile', 150.00, 1200.00, 20.00, '2025-09-11 03:18:59', '', '', 'Upcoming', NULL, '2025-09-08 03:18:59'),
(4, 'Valorant Championship', 'Valorant', 200.00, 1600.00, 20.00, '2025-09-12 03:18:59', '2134567', 'sd34ff', 'Live', NULL, '2025-09-08 03:18:59'),
(5, 'PUBG GREATE', 'PUBG Mobile', 100.00, 170.00, 20.00, '2025-11-01 01:01:00', '43543T', 'DSGE545', 'Completed', 1, '2025-09-08 03:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `type`, `description`, `created_at`) VALUES
(1, 4, 1000.00, 'credit', 'Admin Credit: bonus', '2025-09-08 03:25:14'),
(2, 4, 100.00, 'debit', 'Tournament entry fee for: PUBG Mobile Championship', '2025-09-08 03:25:27'),
(3, 4, 800.00, 'credit', 'Tournament prize for: PUBG Mobile Championship', '2025-09-08 03:28:56'),
(4, 1, 100.00, 'debit', 'Tournament entry fee for: PUBG GREATE', '2025-09-08 03:31:20'),
(5, 4, 100.00, 'debit', 'Tournament entry fee for: PUBG GREATE', '2025-09-08 03:31:25'),
(6, 1, 170.00, 'credit', 'Tournament prize for: PUBG GREATE', '2025-09-08 03:33:18'),
(7, 4, 100.00, 'debit', 'UPI Withdrawal to: play@paytm.com', '2025-09-08 03:49:42'),
(8, 4, 125.00, 'credit', 'UPI Deposit - Transaction ID: 23432345644', '2025-09-08 03:51:32'),
(9, 1, 50.00, 'debit', 'Tournament entry fee for: Free Fire Battle Royale', '2025-09-08 04:32:45'),
(10, 4, 150.00, 'debit', 'Tournament entry fee for: Call of Duty Tournament', '2025-09-08 05:01:18'),
(11, 4, 200.00, 'debit', 'Tournament entry fee for: Valorant Championship', '2025-09-08 05:01:53'),
(12, 4, 50.00, 'debit', 'Tournament entry fee for: Free Fire Battle Royale', '2025-09-08 05:01:56'),
(13, 9, 123.00, 'credit', 'Admin Credit: dsvd', '2025-09-08 05:02:33'),
(14, 10, 100.00, 'credit', 'UPI Deposit - Transaction ID: 1135054', '2025-09-08 05:03:55'),
(15, 4, 50.00, 'credit', 'Referral reward - New user referred', '2025-09-08 10:03:42'),
(16, 18, 25.00, 'credit', 'Referral welcome bonus', '2025-09-08 10:03:42'),
(17, 4, 50.00, 'credit', 'Referral reward - New user referred', '2025-09-08 10:04:10'),
(18, 19, 25.00, 'credit', 'Referral welcome bonus', '2025-09-08 10:04:10'),
(19, 1, 50.00, 'credit', 'Referral reward - New user referred', '2025-09-08 10:09:47'),
(20, 20, 25.00, 'credit', 'Referral welcome bonus', '2025-09-08 10:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wallet_balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `upi_id` varchar(255) DEFAULT NULL,
  `referral_id` varchar(10) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `total_referrals` int(11) DEFAULT 0,
  `referral_earnings` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `wallet_balance`, `created_at`, `upi_id`, `referral_id`, `referred_by`, `total_referrals`, `referral_earnings`) VALUES
(1, 'testuser1', 'test1@example.com', '$2y$10$wLHn7u.RkpRjsgm7S721Ue1Jxn2MbyB3zjh6EyBe1bMckqxR4gOgO', 1070.00, '2025-09-08 03:18:59', NULL, 'ADM001', NULL, 1, 50.00),
(2, 'testuser2', 'test2@example.com', '$2y$10$5PaQ7KhYMvn4auX.wmZKZOpNXtkJ8YN.5LXmKSGWTw3sw8ZWuogu6', 1500.00, '2025-09-08 03:18:59', NULL, 'TES817', NULL, 0, 0.00),
(3, 'testuser3', 'test3@example.com', '$2y$10$14RHAulZFQh4y/tErdCSouZwesQDIFp5JSxsEg6R8jHFTJJ4nQcKC', 2000.00, '2025-09-08 03:18:59', NULL, 'TES173', NULL, 0, 0.00),
(4, 'play', 'piyushmaji524@gmail.com', '$2y$10$p7h.2qJzqMrz4/9OQAKpxOjeW98rsRi.SbOWELu70d4en4tGytE2y', 1325.00, '2025-09-08 03:22:11', 'play@paytm.com', 'PLA114', NULL, 2, 100.00),
(5, 'sorifulyt639', 'nazmunnessakhatun2006@gmail.com', '$2y$10$M1SIBF8SsJwPFClvXmZa1uinPJv2LXAr4OkSA9pK.od0TOEkzI7Dy', 0.00, '2025-09-08 04:25:14', NULL, 'SOR854', NULL, 0, 0.00),
(6, 'Abcd', 'abcd@gmail.com', '$2y$10$NGhIMVJR4XDdIoln.U8sO.Gq9Xrsj4DPs0JAPUwSvSm9V1Tcr6Jyi', 0.00, '2025-09-08 04:36:36', NULL, 'ABC226', NULL, 0, 0.00),
(7, 'Rathod', 'rathodrakeshj4@gmail.com', '$2y$10$61OfKh98IzTT63Mysahpn.j7FwCWGh0Rt47TM6RexW.v0tLro6JAy', 0.00, '2025-09-08 04:40:41', NULL, 'RAT270', NULL, 0, 0.00),
(8, 'Lol', 'lolid@gmail.com', '$2y$10$AG3u/ZqinuNxrwkGLgofjus36RQjxf6C2dIgJvFYbbFzNHLKAGx6i', 0.00, '2025-09-08 04:44:30', NULL, 'LOL572', NULL, 0, 0.00),
(9, 'Hacker', 'kushaltimsina110@gmail.com', '$2y$10$YYfOXZWuG/LK6dOVakci6.CL1d2LL2MCEMukGc/MQpBy2T5R.PSRq', 123.00, '2025-09-08 04:57:17', NULL, 'HAC151', NULL, 0, 0.00),
(10, 'Hamzigaming', 'muskeenshah6@gmail.com', '$2y$10$6XwN5VHnOoqCok2uHXWoQeZfsBu06Ped3HsbaubljZPAx99rVUWXK', 100.00, '2025-09-08 05:01:32', NULL, 'HAM741', NULL, 0, 0.00),
(11, 'PINUU', 'biswojitbarik2000@gmail.com', '$2y$10$BfH2dGvWuJh/imEgJRJct.7nHCPy25Qs6U3zHBWhimy1nbTKOC24q', 0.00, '2025-09-08 05:06:42', NULL, 'PIN451', NULL, 0, 0.00),
(12, 'aff', 'fdfhjbh@gmail.com', '$2y$10$BHjNkN3e28VnvZi6/LlKAOEhTDqedGsmZ4/mgkD8b17T1CTvgIX.W', 0.00, '2025-09-08 08:50:46', NULL, 'AFF833', NULL, 0, 0.00),
(18, 'checvk', 'sdjfhgh@gmail.com', '$2y$10$CH82Dt2vIiYxasdAktT.COP6k96t5yxYr211aNKjnVUI1mQ5tP1S.', 25.00, '2025-09-08 10:03:42', NULL, 'CHE410', 4, 0, 0.00),
(19, 'check123', 'dshfgh@gmail.com', '$2y$10$Q/YAjyVKpqpX5LCA0Mznd.QMa99FFBgt/lWrkCTmBgBdYz6FsOao.', 25.00, '2025-09-08 10:04:10', NULL, 'CHE997', 4, 0, 0.00),
(20, 'adref', 'bhdfhfc@gmail.com', '$2y$10$11rCWdacjbvf45yKSKqKqeZFMIA3QysEbx16UejU6EMrRXoR19IRK', 25.00, '2025-09-08 10:09:47', NULL, 'ADR306', 1, 0, 0.00),
(21, 'asdf456', 'dsfjhbsgh@gmail.com', '$2y$10$1RoEpj9G67Xy3ACaDCLmpuLgQOhr4zdByXMEh6d6cDd6HA5ZnBpyG', 0.00, '2025-09-08 10:11:07', NULL, 'ASD603', NULL, 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `upi_id` varchar(255) NOT NULL,
  `status` enum('Pending','Completed','Rejected') DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `upi_id`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 4, 100.00, 'play@paytm.com', 'Completed', 'rdg', '2025-09-08 03:49:09', '2025-09-08 03:49:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `legal_pages`
--
ALTER TABLE `legal_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_key` (`page_key`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`user_id`,`tournament_id`),
  ADD KEY `tournament_id` (`tournament_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referred_id` (`referred_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_id` (`referral_id`),
  ADD KEY `users_referred_by_fk` (`referred_by`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `legal_pages`
--
ALTER TABLE `legal_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
  ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `tournaments_ibfk_1` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_referred_by_fk` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
