SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `allowed_ips` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `admins` (`id`, `username`, `password_hash`, `allowed_ips`, `created_at`) VALUES
(1, 'admin', '$2y$10$fEwlFUmtJTB8E6r4vpTm4OmK6tJdQI69VfdO3NhtFcDmt.aAgqR5S', NULL, '2026-11-10 17:15:38');

CREATE TABLE IF NOT EXISTS `attribute_values` (
  `id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(100) NOT NULL,
  `price` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `attribute_values` (`id`, `attribute_id`, `value`, `price`) VALUES
(1, 2, '123312123', 123123123),
(2, 2, 'بیسبسیب', 324123123),
(3, 3, 'قرمز', 1000),
(4, 3, 'مشکی', 123123),
(9, 14, 'مشکی', 6),
(11, 16, 'سامسونگ', 50000),
(12, 16, 'شیامویی', 20000);

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `brands` (`id`, `name`, `slug`, `logo`, `sort_order`) VALUES
(1, 'اپل', 'apple', NULL, 0),
(2, 'سامسونگ', 'samsung', NULL, 0),
(3, 'سونی', 'sony', NULL, 0),
(4, 'شیائومی', 'xiaomi', NULL, 0);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(10) DEFAULT '?',
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `parent_id`, `icon`, `sort_order`) VALUES
(1, 'موبایل', 'mobile', NULL, '📱', 0),
(2, 'لپ تاپ', 'laptop', NULL, '💻', 0),
(3, 'هدفون', 'headphone', NULL, '🎧', 0),
(4, 'ساعت هوشمند', 'watch', NULL, '⌚', 0);

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `kind` varchar(20) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `success` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `login_attempts` (`id`, `identifier`, `kind`, `ip`, `success`, `created_at`) VALUES
(1, '::1', 'otp', '::1', 0, '2026-11-10 17:06:55'),
(2, '::1', 'otp', '::1', 0, '2026-11-10 17:07:58'),
(3, '::1', 'otp', '::1', 0, '2026-11-10 17:08:42'),
(4, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:15:55'),
(5, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:16:37'),
(6, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:02'),
(7, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:03'),
(8, 'admin', 'admin_login', '::1', 0, '2026-11-10 17:17:56'),
(9, '::1', 'otp', '::1', 0, '2026-11-10 18:24:52'),
(10, '::1', 'otp', '::1', 0, '2026-11-12 17:54:08'),
(11, '::1', 'otp', '::1', 0, '2026-11-13 09:08:41');

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
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

INSERT IGNORE INTO `orders` (`id`, `order_code`, `user_id`, `phone`, `address`, `lat`, `lng`, `notes`, `ship_method`, `pay_method`, `subtotal`, `discount`, `shipping`, `total`, `status`, `tracking_code`, `zarinpal_authority`, `created_at`) VALUES
(1, 'ORD-261110-53467', 2, NULL, 'سشیشسیسشیشسیسشیشسیشسیشسی', NULL, NULL, NULL, 'post', 'gateway', 45000000, 0, 0, 45000000, 'shipped', NULL, NULL, '2026-11-10 17:13:40'),
(2, 'ORD-261112-29A66', 1, NULL, 'sadasdasdasd', NULL, NULL, NULL, 'post', 'gateway', 123123, 0, 50000, 173123, 'paid', NULL, NULL, '2026-11-12 17:57:35'),
(3, 'ORD-261112-0158D', 1, NULL, ';kjtdrguhutftebnok0o', NULL, NULL, NULL, 'post', 'cod', 91000000, 0, 0, 91000000, 'shipped', '123123123123123123123123123123123123', NULL, '2026-11-12 17:59:27'),
(4, 'ORD-261113-21AAA', 1, '09120000000', 'dasdasdasdasdasdasdasd', NULL, NULL, NULL, 'post', 'gateway', 45000000, 0, 0, 45000000, 'paid', NULL, NULL, '2026-11-13 09:10:03'),
(5, 'ORD-261113-22801', 1, '09120000000', 'cfsdvsfvdsvfcdfvsdvsdvfvs', 29.5872283, 52.5892126, NULL, 'post', 'card', 35000000, 0, 0, 35000000, 'pending', NULL, NULL, '2026-11-13 09:10:22'),
(6, 'ORD-261113-97C31', 1, '09120000000', 'سیسیشیشسیشسیشسیشسیشسیشسیشسی', NULL, NULL, NULL, 'post', 'gateway', 325752492, 0, 0, 325752492, 'paid', NULL, NULL, '2026-11-13 11:23:13');

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `variant_info` varchar(255) DEFAULT '',
  `unit_price` bigint(20) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `variant_info`, `unit_price`, `qty`) VALUES
(1, 1, 1, 'گوشی آیفون 14', '', 45000000, 1),
(2, 2, 7, 'علیرضا', '', 123123, 1),
(3, 3, 1, 'گوشی آیفون 14', '', 45000000, 1),
(4, 3, 2, 'گوشی سامسونگ S23', '', 35000000, 1),
(5, 3, 4, 'هدفون سونی WH-1000XM5', '', 11000000, 1),
(6, 4, 1, 'گوشی آیفون 14', '', 45000000, 1),
(7, 5, 2, 'گوشی سامسونگ S23', '', 35000000, 1),
(8, 6, 7, 'علیرضا', '', 324369369, 1),
(9, 6, 7, 'علیرضا', '', 123123, 1),
(10, 6, 10, 'گلس', '', 150000, 2),
(11, 6, 10, 'گلس', '', 120000, 8);

CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id` int(11) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `attempts` tinyint(4) DEFAULT 0,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `otp_codes` (`id`, `phone`, `code`, `attempts`, `expires_at`, `used`, `created_at`) VALUES
(1, '09039112684', '921265', 0, '2026-11-10 20:41:55', 0, '2026-11-10 17:06:55'),
(2, '09039112684', '578183', 0, '2026-11-10 20:42:58', 0, '2026-11-10 17:07:58'),
(3, '09039112684', '150755', 0, '2026-11-10 20:43:42', 1, '2026-11-10 17:08:42'),
(4, '09120000000', '221770', 0, '2026-11-10 21:59:52', 1, '2026-11-10 18:24:52'),
(5, '09120000000', '405318', 0, '2026-11-12 21:29:08', 1, '2026-11-12 17:54:08'),
(6, '09120000000', '496240', 0, '2026-11-13 12:43:41', 1, '2026-11-13 09:08:41');

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` mediumtext DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `pages` (`id`, `slug`, `title`, `content`, `updated_at`) VALUES
(1, 'about', 'درباره ما', '<h2>به فروشگاه ما خوش آمدید</h2><p>ما با هدف ارائه بهترین خدمات و محصولات با کیفیت تاسیس شده‌ایم. رضایت شما بزرگترین افتخار ماست.</p>', '2026-11-10 16:47:47'),
(2, 'contact', 'تماس با ما', '<h2>ارتباط با ما</h2><p>برای ارتباط با ما می‌توانید از راه‌های زیر استفاده کنید:</p><ul><li>تلفن: 021-12345678</li><li>ایمیل: info@shop.com</li></ul>', '2026-11-10 16:47:47'),
(3, 'terms', 'قوانین و مقررات', '<h2>قوانین</h2><p>استفاده از این سایت به معنی پذیرش قوانین است.</p>', '2026-11-10 16:47:47');

CREATE TABLE IF NOT EXISTS `products` (
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

INSERT IGNORE INTO `products` (`id`, `name`, `price`, `discount_price`, `stock`, `category_id`, `brand_id`, `image`, `description`, `is_new`, `is_featured`, `active`, `created_at`) VALUES
(1, 'گوشی آیفون 14', 45000000, 0, 7, 1, 1, NULL, 'آیفون 14 با دوربین 12 مگاپیکسل', 1, 1, 1, '2026-11-10 16:47:47'),
(2, 'گوشی سامسونگ S23', 38000000, 35000000, 13, 1, 2, NULL, 'گلکسی S23 با نمایشگر 120 هرتز', 0, 1, 1, '2026-11-10 16:47:47'),
(3, 'لپ تاپ مک بوک پرو', 85000000, 0, 5, 2, 1, NULL, 'مک بوک پرو 14 اینچ M2', 1, 1, 1, '2026-11-10 16:47:47'),
(4, 'هدفون سونی WH-1000XM5', 12000000, 11000000, 19, 3, 3, NULL, 'بهترین هدفون نویز کنسلینگ', 1, 1, 1, '2026-11-10 16:47:47'),
(5, 'گوشی شیائومی 13', 25000000, 0, 30, 1, 4, NULL, 'شیائومی 13 با لنز لایکا', 0, 0, 1, '2026-11-10 16:47:47'),
(6, 'ساعت هوشمند اپل واچ 9', 18000000, 17000000, 12, 4, 1, NULL, 'اپل واچ سری 9 با صفحه AMOLED', 1, 0, 1, '2026-11-10 16:47:47'),
(7, 'علیرضا', 123123, 0, 120, 1, 2, 'uploads/img_1794331401_937.jpg', 'صیشی', 1, 1, 1, '2026-11-10 17:23:21'),
(8, 'شسیشسی', 21312, 3123123, 1231, 2, 2, 'uploads/img_1794565242_333.jpg', '213123123123', 1, 1, 1, '2026-11-13 10:20:42'),
(9, 'محصول جدید', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-11-13 11:06:41'),
(10, 'گلس', 100000, 0, 9990, NULL, NULL, 'uploads/img_1794568872_965.jpg', 'گلس', 1, 1, 1, '2026-11-13 11:19:49'),
(11, 'محصول جدید', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-11-13 11:30:18'),
(12, 'محصول جدید', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-11-13 11:45:57'),
(13, 'محصول جدید', 0, 0, 0, NULL, NULL, NULL, '', 0, 0, 1, '2026-11-13 11:47:03'),
(14, 'محصول جدید', 0, 0, 0, NULL, NULL, NULL, NULL, 0, 0, 1, '2026-11-13 11:48:29');

CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `product_attributes` (`id`, `product_id`, `name`, `created_at`) VALUES
(1, 7, 'سشیشسیسشی', '2026-11-13 10:20:19'),
(2, 7, 'سشیشسیشسی', '2026-11-13 10:24:37'),
(3, 7, 'رنگ', '2026-11-13 10:25:25'),
(14, 8, 'رنگ', '2026-11-13 10:48:50'),
(15, 9, 'شسیشسی', '2026-11-13 11:06:50'),
(16, 10, 'گوشی', '2026-11-13 11:20:19');

CREATE TABLE IF NOT EXISTS `settings` (
  `k` varchar(50) NOT NULL,
  `v` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `settings` (`k`, `v`) VALUES
('card_holder_name', '44060'),
('card_number', '6037-9959-9632-7415'),
('enamad_code', ''),
('footer_about', 'ما یک فروشگاه اینترنتی معتبر با بهترین قیمت‌ها و ارسال سریع هستیم.'),
('free_shipping_min', '2000000'),
('home_welcome_text', 'میو میو'),
('shipping_cost', '50000'),
('site_address', 'تهران، خیابان ولیعصر'),
('site_email', 'info@shop.com'),
('site_logo', 'uploads/img_1794506153_455.jpg'),
('site_name', 'فروشگاه من'),
('site_phone', '021-12345678');

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(11) NOT NULL,
  `national_code` varchar(10) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_verified` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `users` (`id`, `name`, `phone`, `national_code`, `postal_code`, `address`, `is_verified`, `created_at`) VALUES
(1, 'کاربر دمو', '09120000000', NULL, NULL, NULL, 1, '2026-11-10 17:02:49'),
(2, 'علیرضا', '09039112684', '2288278633', '7169721594', 'شسیشسیشسی', 1, '2026-11-10 17:08:46');

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 1, 1, '2026-11-12 17:54:16'),
(2, 1, 2, '2026-11-12 17:54:17'),
(3, 1, 4, '2026-11-12 17:54:20');

ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_id` (`attribute_id`);

ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `identifier` (`identifier`,`created_at`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone` (`phone`,`created_at`);

ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `active` (`active`,`is_featured`);

ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`k`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wish` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `attribute_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `product_attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `attribute_values`
  ADD CONSTRAINT `attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE;

ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

COMMIT; 