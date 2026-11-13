</main>

<!-- دکمه شناور تماس -->
<?php if(setting('site_phone')): ?>
<a href="tel:<?= e(setting('site_phone')) ?>" class="floating-call-btn" title="تماس با ما">
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
    </svg>
</a>
<?php endif; ?>

<!-- منوی پایین صفحه (مخصوص موبایل) -->
<div class="bottom-nav">
    <a href="<?= BASE ?>index.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <span>🏠</span> خانه
    </a>
    <a href="<?= BASE ?>category.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : '' ?>">
        <span>🛍️</span> فروشگاه
    </a>
    <a href="<?= BASE ?>cart.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">
        <span>🛒</span> سبد خرید
        <span class="cart-badge" id="mobileCartCount"><?= fmt(cart_qty()) ?></span>
    </a>
    <a href="<?= isset($user) && $user ? BASE.'profile.php' : BASE.'login.php' ?>" class="nav-btn <?= in_array(basename($_SERVER['PHP_SELF']), ['profile.php', 'login.php']) ? 'active' : '' ?>">
        <span>👤</span> حساب کاربری
    </a>
</div>

<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-col">
            <h4>🛍️ <?= e(setting('site_name')) ?></h4>
            <p style="font-size:13px"><?= e(setting('footer_about')) ?></p>
        </div>
        <div class="footer-col">
            <h4>لینک‌ها</h4>
            <a href="<?= BASE ?>page.php?page=about">درباره ما</a>
            <a href="<?= BASE ?>page.php?page=contact">تماس با ما</a>
        </div>
        <div class="footer-col">
            <h4>تماس با ما</h4>
            <a href="tel:<?= e(setting('site_phone')) ?>">📞 <?= e(setting('site_phone')) ?></a>
            <span style="display:block;padding:5px 0;color:#a5b4fc">📍 <?= e(setting('site_address')) ?></span>
        </div>
    </div>
    <div class="footer-bottom">© ۱۴۰۴ <?= e(setting('site_name')) ?></div>
</footer>

<script src="<?= BASE ?>assets/app.js"></script>
</body>
</html>