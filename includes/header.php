<?php
$user = current_user();
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

// دریافت تعداد علاقه‌مندی‌ها
$wish_count = 0;
if ($user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $wish_count = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?><?= e(setting('site_name', 'فروشگاه من')) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body data-base="<?= BASE ?>" data-csrf="<?= csrf() ?>">

<header class="site-header" id="header">
    <div class="topbar">
        <button class="burger" id="burgerBtn">☰</button>
        
        <!-- نمایش لوگو یا ایموجی -->
        <a href="index.php" class="brand" style="display:flex; align-items:center; gap:8px; text-decoration:none;">
            <?php if(setting('site_logo')): ?>
                <img src="<?= e(setting('site_logo')) ?>" alt="<?= e(setting('site_name')) ?>" style="max-height: 40px; max-width: 120px; object-fit: contain;">
            <?php else: ?>
                <span>🛍️</span>
            <?php endif; ?>
            <span><?= e(setting('site_name')) ?></span>
        </a>
        
        <nav class="main-nav" id="mainNav">
            <!-- دکمه بستن منو -->
            <button class="close-menu-btn" id="closeMenuBtn" style="display:none; position:absolute; top:20px; left:20px; background:none; border:none; font-size:24px; color:#ef4444; cursor:pointer;">✕</button>

            <a href="index.php">خانه</a>
            <a href="category.php">فروشگاه</a>
            <div class="dropdown">
                <a href="#" class="drop-toggle" id="dropToggle">دسته‌بندی ▾</a>
                <div class="dropdown-menu" id="dropMenu">
                    <div>
                        <?php foreach($cats as $c): ?>
                            <a href="category.php?cat=<?= urlencode($c['slug']) ?>"><?= e($c['icon']) ?> <?= e($c['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <a href="page.php?page=about">درباره ما</a>
            <a href="page.php?page=contact">تماس با ما</a>
            
            <!-- دکمه نصب اپلیکیشن (فقط در منوی موبایل دیده می‌شود) -->
            <button id="installAppBtn" style="display:none; background:#7c3aed; color:#fff; border:none; padding:12px 15px; border-radius:10px; font-weight:bold; margin-top:10px; cursor:pointer;">📲 نصب اپلیکیشن</button>
        </nav>
        
        <div class="header-actions">
            <!-- دکمه علاقه مندی ها -->
            <a href="profile.php?tab=wishlist" class="icon-btn" title="علاقه‌مندی‌ها" style="position:relative;">
                ♡ <span class="count" id="wishCount"><?= fmt($wish_count) ?></span>
            </a>
            
            <button class="theme-toggle icon-btn" id="themeBtn" title="حالت شب">🌙</button>
            <a href="cart.php" class="icon-btn">🛒 <span class="count" id="cartCount"><?= fmt(cart_qty()) ?></span></a>
            
            <?php if($user): ?>
                <a href="profile.php" class="user-chip">👤 <?= e($user['name']) ?></a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm">ورود / ثبت‌نام</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main>