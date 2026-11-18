-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2026 at 07:33 PM
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
-- Database: `onsori_shop_mobile`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `allowed_ips` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `allowed_ips`, `created_at`) VALUES
(1, 'admin', '$2y$10$fEwlFUmtJTB8E6r4vpTm4OmK6tJdQI69VfdO3NhtFcDmt.aAgqR5S', NULL, '2026-11-10 17:15:38');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `sort_order`) VALUES
(1, 'اپل', 'apple', NULL, 0),
(2, 'سامسونگ', 'samsung', NULL, 0),
(3, 'سونی', 'sony', NULL, 0),
(4, 'شیائومی', 'xiaomi', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(10) DEFAULT '?',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`, `icon`, `sort_order`) VALUES
(1, 'موبایل', 'mobile', NULL, '📱', 0),
(2, 'لپ تاپ', 'laptop', NULL, '💻', 0),
(3, 'هدفون', 'headphone', NULL, '🎧', 0),
(4, 'ساعت هوشمند', 'watch', NULL, '⌚', 0);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `kind` varchar(20) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `success` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `kind`, `ip`, `success`, `created_at`) VALUES
(1, '::1', 'otp', '::1', 0, '2026-11-10 17:06:55'),
(2, '::1', 'otp', '::1', 0, '2026-11-10 17:07:58'),
(3, '::1', 'otp', '::1', 0, '2026-11-10 17:08:42'),
(4, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:15:55'),
(5, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:16:37'),
(6, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:02'),
(7, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:03'),
(8, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:56'),
(9, '::1', 'otp', '::1', 0, '2026-11-10 18:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ship_method` varchar(50) NOT NULL,
  `pay_method` varchar(50) NOT NULL,
  `subtotal` bigint(20) NOT NULL,
  `discount` bigint(20) DEFAULT 0,
  `shipping` bigint(20) DEFAULT 0,
  `total` bigint(20) NOT NULL,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `tracking_code` varchar(50) DEFAULT NULL,
  `zarinpal_authority` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `address`, `lat`, `lng`, `notes`, `ship_method`, `pay_method`, `subtotal`, `discount`, `shipping`, `total`, `status`, `tracking_code`, `zarinpal_authority`, `created_at`) VALUES
(1, 'ORD-261110-53467', 2, 'سشیشسیسشیشسیسشیشسیشسیشسی', NULL, NULL, NULL, 'post', 'gateway', 45000000, 0, 0, 45000000, 'shipped', NULL, NULL, '2026-11-10 17:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `variant_info` varchar(255) DEFAULT '',
  `unit_price` bigint(20) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `variant_info`, `unit_price`, `qty`) VALUES
(1, 1, 1, 'گوشی آیفون 14', '', 45000000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `attempts` tinyint(4) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `phone`, `code`, `attempts`, `expires_at`, `used`, `created_at`) VALUES
(1, '09039112684', '921265', 0, '2026-11-10 20:41:55', 0, '2026-11-10 17:06:55'),
(2, '09039112684', '578183', 0, '2026-11-10 20:42:58', 0, '2026-11-10 17:07:58'),
(3, '09039112684', '150755', 0, '2026-11-10 20:43:42', 1, '2026-11-10 17:08:42'),
(4, '09120000000', '221770', 0, '2026-11-10 21:59:52', 1, '2026-11-10 18:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `slug`, `title`, `content`, `updated_at`) VALUES
(1, 'about', 'درباره ما', '<h2>به فروشگاه ما خوش آمدید</h2><p>ما با هدف ارائه بهترین خدمات و محصولات با کیفیت تاسیس شده‌ایم. رضایت شما بزرگترین افتخار ماست.</p>', '2026-11-10 16:47:47'),
(2, 'contact', 'تماس با ما', '<h2>ارتباط با ما</h2><p>برای ارتباط با ما می‌توانید از راه‌های زیر استفاده کنید:</p><ul><li>تلفن: 021-12345678</li><li>ایمیل: info@shop.com</li></ul>', '2026-11-10 16:47:47'),
(3, 'terms', 'قوانین و مقررات', '<h2>قوانین</h2><p>استفاده از این سایت به معنی پذیرش قوانین است.</p>', '2026-11-10 16:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `price` bigint(20) NOT NULL DEFAULT 0,
  `discount_price` bigint(20) DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_new` tinyint(4) DEFAULT 0,
  `is_featured` tinyint(4) DEFAULT 0,
  `active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `discount_price`, `stock`, `category_id`, `brand_id`, `image`, `description`, `is_new`, `is_featured`, `active`, `created_at`) VALUES
(1, 'گوشی آیفون 14', 45000000, 0, 9, 1, 1, NULL, 'آیفون 14 با دوربین 12 مگاپیکسل', 1, 1, 1, '2026-11-10 16:47:47'),
(2, 'گوشی سامسونگ S23', 38000000, 35000000, 15, 1, 2, NULL, 'گلکسی S23 با نمایشگر 120 هرتز', 0, 1, 1, '2026-11-10 16:47:47'),
(3, 'لپ تاپ مک بوک پرو', 85000000, 0, 5, 2, 1, NULL, 'مک بوک پرو 14 اینچ M2', 1, 1, 1, '2026-11-10 16:47:47'),
(4, 'هدفون سونی WH-1000XM5', 12000000, 11000000, 20, 3, 3, NULL, 'بهترین هدفون نویز کنسلینگ', 1, 1, 1, '2026-11-10 16:47:47'),
(5, 'گوشی شیائومی 13', 25000000, 0, 30, 1, 4, NULL, 'شیائومی 13 با لنز لایکا', 0, 0, 1, '2026-11-10 16:47:47'),
(6, 'ساعت هوشمند اپل واچ 9', 18000000, 17000000, 12, 4, 1, NULL, 'اپل واچ سری 9 با صفحه AMOLED', 1, 0, 1, '2026-11-10 16:47:47'),
(7, 'علیرضا', 123123, 0, 123, 1, 2, 'uploads/img_1794331401_937.jpg', 'صیشی', 1, 1, 1, '2026-11-10 17:23:21');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `k` varchar(50) NOT NULL,
  `v` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`k`, `v`) VALUES
('enamad_code', ''),
('footer_about', 'ما یک فروشگاه اینترنتی معتبر با بهترین قیمت‌ها و ارسال سریع هستیم.'),
('free_shipping_min', '2000000'),
('shipping_cost', '50000'),
('site_address', 'تهران، خیابان ولیعصر'),
('site_email', 'info@shop.com'),
('site_name', 'فروشگاه من'),
('site_phone', '021-12345678');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `national_code` varchar(10) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `national_code`, `postal_code`, `address`, `is_verified`, `created_at`) VALUES
(1, 'کاربر دمو', '09120000000', NULL, NULL, NULL, 1, '2026-11-10 17:02:49'),
(2, 'علیرضا', '09039112684', '2288278633', '7169721594', 'شسیشسیشسی', 1, '2026-11-10 17:08:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `identifier` (`identifier`,`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone` (`phone`,`created_at`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `active` (`active`,`is_featured`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`k`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
