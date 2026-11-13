<?php
require_once 'config.php';
$page_title = 'صفحه اصلی';

$user = current_user();

$featured = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.is_featured=1 AND p.active=1 LIMIT 4")->fetchAll();
$newest = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.is_new=1 AND p.active=1 LIMIT 4")->fetchAll();
$random = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.active=1 ORDER BY RAND() LIMIT 4")->fetchAll();
$discounted = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.discount_price>0 AND p.active=1 LIMIT 4")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY sort_order")->fetchAll();

function check_wish($user, $pid, $pdo) {
    if (!$user) return false;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $pid]);
    return $stmt->fetch() ? true : false;
}

include 'includes/header.php';
?>

<div class="container title-wrap">
    <div class="title-box reveal"><?= e(setting('home_welcome_text', '✨ به فروشگاه ما خوش آمدید')) ?></div>
</div>

<form class="search-wrap" method="get" action="category.php">
    <input name="q" type="text" placeholder="نام محصول را جستجو کنید..." value="<?= e($_GET['q']??'') ?>">
    <button class="btn btn-primary" type="submit">🔍 جستجو</button>
</form>

<!-- اسلایدر -->
<div class="container">
    <div class="slider" id="slider">
        <div class="slide active" style="background:linear-gradient(135deg,#7c3aed,#a855f7,#ec4899)">
            <div class="emoji">🎉</div>
            <h2>جشنواره تخفیف ویژه</h2>
            <p>تا ۵۰٪ تخفیف روی محصولات منتخب</p>
        </div>
        <div class="slide" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4,#22d3ee)">
            <div class="emoji">🚚</div>
            <h2>ارسال سریع و رایگان</h2>
            <p>برای خریدهای بالای <?= fmt(setting('free_shipping_min', 2000000)) ?> تومان</p>
        </div>
        <div class="slide" style="background:linear-gradient(135deg,#f43f5e,#f59e0b,#fbbf24)">
            <div class="emoji">🔒</div>
            <h2>خرید امن و مطمئن</h2>
            <p>با درگاه پرداخت زرین‌پال</p>
        </div>
        <div class="dots" id="dots"></div>
    </div>
</div>

<!-- برندها -->
<div class="container">
    <div class="brands">
        <?php foreach($brands as $b): ?>
            <a href="brand.php?slug=<?= urlencode($b['slug']) ?>" class="brand-box reveal"><?= e($b['name']) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ردیف ۱: محصولات ویژه -->
<?php if($featured): ?>
<div class="container" id="products">
    <h2 class="section-title"><span>🔥 محصولات ویژه</span></h2>
    <div class="products">
        <?php foreach($featured as $p): 
            $in_wish = check_wish($user, $p['id'], $pdo);
        ?>
            <article class="product-card reveal">
                <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                    <?php else: ?>📦<?php endif; ?>
                </a>
                <div class="p-body">
                    <span class="p-cat"><?= e($p['cat_name']??'—') ?></span>
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                    <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                    <div class="p-actions" style="display:flex; gap:8px; margin-top:auto;">
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1; display:block; text-align:center; text-decoration:none; background:var(--grad); color:#fff; padding:10px; border-radius:12px; font-weight:bold; font-size:13px;">مشاهده محصول</a>
                        <button class="wish-btn <?= $in_wish ? 'active' : '' ?>" data-pid="<?= $p['id'] ?>" style="width:42px; border:1.5px solid var(--border); border-radius:12px; background:#fff; color:#ef4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                            <?= $in_wish ? '♥' : '♡' ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ردیف ۲: جدیدترین‌ها -->
<?php if($newest): ?>
<div class="container">
    <h2 class="section-title"><span>✨ جدیدترین محصولات</span></h2>
    <div class="products">
        <?php foreach($newest as $p): 
            $in_wish = check_wish($user, $p['id'], $pdo);
        ?>
            <article class="product-card reveal">
                <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                    <?php else: ?>📦<?php endif; ?>
                </a>
                <div class="p-body">
                    <span class="p-cat"><?= e($p['cat_name']??'—') ?></span>
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                    <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                    <div class="p-actions" style="display:flex; gap:8px; margin-top:auto;">
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1; display:block; text-align:center; text-decoration:none; background:var(--grad); color:#fff; padding:10px; border-radius:12px; font-weight:bold; font-size:13px;">مشاهده محصول</a>
                        <button class="wish-btn <?= $in_wish ? 'active' : '' ?>" data-pid="<?= $p['id'] ?>" style="width:42px; border:1.5px solid var(--border); border-radius:12px; background:#fff; color:#ef4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                            <?= $in_wish ? '♥' : '♡' ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ردیف ۳: محصولات تخفیف‌دار -->
<?php if($discounted): ?>
<div class="container">
    <h2 class="section-title"><span>💸 تخفیف‌های شگفت‌انگیز</span></h2>
    <div class="products">
        <?php foreach($discounted as $p): 
            $in_wish = check_wish($user, $p['id'], $pdo);
        ?>
            <article class="product-card reveal">
                <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                    <?php else: ?>📦<?php endif; ?>
                </a>
                <div class="p-body">
                    <span class="p-cat"><?= e($p['cat_name']??'—') ?></span>
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                    <div class="p-price"><?= fmt($p['discount_price']) ?> تومان <small style="text-decoration:line-through;color:#999"><?= fmt($p['price']) ?></small></div>
                    <div class="p-actions" style="display:flex; gap:8px; margin-top:auto;">
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1; display:block; text-align:center; text-decoration:none; background:var(--grad); color:#fff; padding:10px; border-radius:12px; font-weight:bold; font-size:13px;">مشاهده محصول</a>
                        <button class="wish-btn <?= $in_wish ? 'active' : '' ?>" data-pid="<?= $p['id'] ?>" style="width:42px; border:1.5px solid var(--border); border-radius:12px; background:#fff; color:#ef4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                            <?= $in_wish ? '♥' : '♡' ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ردیف ۴: پیشنهاد تصادفی -->
<?php if($random): ?>
<div class="container">
    <h2 class="section-title"><span>🎲 پیشنهاد شگفت‌انگیز</span></h2>
    <div class="products">
        <?php foreach($random as $p): 
            $in_wish = check_wish($user, $p['id'], $pdo);
        ?>
            <article class="product-card reveal">
                <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                    <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                    <?php else: ?>📦<?php endif; ?>
                </a>
                <div class="p-body">
                    <span class="p-cat"><?= e($p['cat_name']??'—') ?></span>
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                    <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                    <div class="p-actions" style="display:flex; gap:8px; margin-top:auto;">
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn-view" style="flex:1; display:block; text-align:center; text-decoration:none; background:var(--grad); color:#fff; padding:10px; border-radius:12px; font-weight:bold; font-size:13px;">مشاهده محصول</a>
                        <button class="wish-btn <?= $in_wish ? 'active' : '' ?>" data-pid="<?= $p['id'] ?>" style="width:42px; border:1.5px solid var(--border); border-radius:12px; background:#fff; color:#ef4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                            <?= $in_wish ? '♥' : '♡' ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>