<?php
// ============================================
// تنظیمات اصلی
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'onsori_shop_mobile');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE', '/onsori/shop_mobile/');
define('SITE_URL', 'https://yourdomain.com');

define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Admin@1404!');

define('ZARINPAL_MERCHANT', '');
define('ZARINPAL_SANDBOX', true);

define('KAVENEGAR_KEY', '');
define('KAVENEGAR_SENDER', '');
define('KAVENEGAR_OTP_TEMPLATE', '');

define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCK_MINUTES', 15);
define('MAX_OTP_PER_HOUR', 5);
define('OTP_EXPIRE_MINUTES', 5);
define('OTP_MAX_TRIES', 3);

// ============================================
// Session امن
// ============================================
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// ============================================
// اتصال دیتابیس
// ============================================
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (Exception $e) {
    if (!isset($GLOBALS['INSTALLING'])) {
        http_response_code(500);
        die('خطا در اتصال به دیتابیس. لطفاً ابتدا install.php را اجرا کنید.');
    }
}

// ============================================
// بارگذاری توابع
// ============================================
require_once __DIR__ . '/includes/functions.php';