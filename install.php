<?php
define('INSTALLING', true);
require_once 'config.php';

if (isset($_GET['run'])) {
    $sql = file_get_contents(__DIR__.'/database.sql');
    
    // اجرای کوئری‌ها
    try {
        $pdo->exec($sql);
        
        // ساخت ادمین پیش‌فرض
        $hash = password_hash(ADMIN_PASS, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?,?) ON DUPLICATE KEY UPDATE password_hash=?")
            ->execute([ADMIN_USER, $hash, $hash]);
        
        // ساخت پوشه آپلود
        if (!is_dir(__DIR__.'/uploads')) mkdir(__DIR__.'/uploads', 0755, true);
        
        echo '<!DOCTYPE html><html lang="fa" dir="rtl"><meta charset="utf-8"><body style="font-family:Tahoma;padding:40px;text-align:center;background:#faf5ff;line-height:2">';
        echo '<h2 style="color:#10b981">✅ نصب با موفقیت انجام شد</h2>';
        echo '<p>نام کاربری ادمین: <b>'.ADMIN_USER.'</b></p>';
        echo '<p>رمز عبور ادمین: <b>'.ADMIN_PASS.'</b></p>';
        echo '<p style="color:#dc2626;font-weight:bold">⚠️ همین الان فایل install.php را از هاست حذف کنید.</p>';
        echo '<div style="margin-top:20px">';
        echo '<a href="'.BASE.'admin.php" style="background:#7c3aed;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;margin:5px">ورود به پنل ادمین</a>';
        echo '<a href="'.BASE.'" style="background:#ec4899;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;margin:5px">مشاهده سایت</a>';
        echo '</div></body></html>';
        
    } catch (Exception $e) {
        die('خطا در نصب: ' . $e->getMessage());
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نصب فروشگاه</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #faf5ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(124,58,237,0.1); text-align: center; max-width: 500px; }
        h1 { color: #7c3aed; }
        p { color: #64748b; line-height: 1.8; }
        .btn { display: inline-block; background: linear-gradient(135deg, #7c3aed, #ec4899); color: #fff; padding: 14px 30px; border-radius: 12px; text-decoration: none; font-weight: bold; margin-top: 20px; transition: 0.3s; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(124,58,237,0.3); }
    </style>
</head>
<body>
    <div class="box">
        <h1>🚀 نصب فروشگاه</h1>
        <p>این عملیات جداول دیتابیس را می‌سازد و تنظیمات اولیه را انجام می‌دهد.</p>
        <p style="color: #ef4444; font-weight: bold;">توجه: قبل از نصب مطمئن شوید اطلاعات دیتابیس را در config.php وارد کرده‌اید.</p>
        <a href="?run=1" class="btn">شروع نصب</a>
    </div>
</body>
</html>