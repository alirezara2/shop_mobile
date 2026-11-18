</main>

<!-- =========================================
     منوی پایین صفحه (مخصوص موبایل و اندروید)
     ========================================= -->
<div class="bottom-nav">
    <a href="index.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <span>🏠</span> خانه
    </a>
    <a href="category.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'category.php' ? 'active' : '' ?>">
        <span>🛍️</span> فروشگاه
    </a>
    <a href="cart.php" class="nav-btn <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">
        <span>🛒</span> سبد خرید
        <span class="cart-badge" id="mobileCartCount"><?= fmt(cart_qty()) ?></span>
    </a>
    <a href="<?= $user ? 'profile.php' : 'login.php' ?>" class="nav-btn <?= in_array(basename($_SERVER['PHP_SELF']), ['profile.php', 'login.php']) ? 'active' : '' ?>">
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
            <a href="page.php?page=about">درباره ما</a>
            <a href="page.php?page=contact">تماس با ما</a>
        </div>
        <div class="footer-col">
            <h4>تماس با ما</h4>
            <a href="tel:<?= e(setting('site_phone')) ?>">📞 <?= e(setting('site_phone')) ?></a>
            <span style="display:block;padding:5px 0;color:#a5b4fc">📍 <?= e(setting('site_address')) ?></span>
        </div>
    </div>
    <div class="footer-bottom">© ۱۴۰۴ <?= e(setting('site_name')) ?></div>
</footer>

<script src="assets/app.js"></script>
</body>
</html>