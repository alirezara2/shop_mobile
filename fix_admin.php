<?php
require_once 'config.php';

// تنظیمات ادمین جدید
$username = 'admin';
$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

// آپدیت دیتابیس (حذف محدودیت IP و ست کردن رمز جدید)
$stmt = $pdo->prepare("UPDATE `admins` SET `password_hash` = ?, `allowed_ips` = NULL WHERE `username` = ?");
$stmt->execute([$hash, $username]);

if ($stmt->rowCount() > 0) {
    echo "<h2 style='color:green'>✅ رمز عبور با موفقیت تغییر کرد!</h2>";
} else {
    // اگر کاربری پیدا نشد، از اول می‌سازیم
    $stmt2 = $pdo->prepare("INSERT INTO `admins` (`username`, `password_hash`, `allowed_ips`) VALUES (?, ?, NULL)");
    $stmt2->execute([$username, $hash]);
    echo "<h2 style='color:green'>✅ ادمین جدید ساخته شد!</h2>";
}

echo "<p>نام کاربری: <b>$username</b></p>";
echo "<p>رمز عبور: <b>$password</b></p>";
echo "<p><a href='admin.php'>برو به صفحه ورود ادمین</a></p>";