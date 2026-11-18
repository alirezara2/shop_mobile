<?php
$user = current_user();
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?><?= e(setting('site_name', 'فروشگاه من')) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-base="<?= BASE ?>" data-csrf="<?= csrf() ?>">

<header class="site-header" id="header">
    <div class="topbar">
        <button class="burger" id="burgerBtn">☰</button>
        
        <a href="index.php" class="brand"><span>🛍️</span><?= e(setting('site_name')) ?></a>
        
        <nav class="main-nav" id="mainNav">
            <!-- دکمه بستن منو (فقط در موبایل دیده می‌شود) -->
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
        </nav>
        
        <div class="header-actions">
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