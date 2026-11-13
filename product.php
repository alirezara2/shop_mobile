<?php
require_once 'config.php';
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1");
$s->execute([$id]); $p = $s->fetch();
if (!$p) { $page_title='یافت نشد'; include 'includes/header.php'; echo '<div class="container"><div class="empty">محصول یافت نشد</div></div>'; include 'includes/footer.php'; exit; }

$u = current_user();
$in_wish = false;
if ($u) { $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?"); $stmt->execute([$u['id'],$p['id']]); $in_wish = $stmt->fetch()?true:false; }

// دریافت ویژگی‌ها
$attrs = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id=?");
$attrs->execute([$id]); $attrs = $attrs->fetchAll();

// محصولات مرتبط (همان دسته‌بندی)
$related = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.category_id=? AND p.id!=? AND p.active=1 LIMIT 4");
$related->execute([$p['category_id'], $id]); 
$related = $related->fetchAll();

// محصولات تصادفی
$random = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.active=1 ORDER BY RAND() LIMIT 4")->fetchAll();

function check_wish($user, $pid, $pdo) {
    if (!$user) return false;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $pid]);
    return $stmt->fetch() ? true : false;
}

$page_title = $p['name'];
include 'includes/header.php';
?>

<style>
    /* استایل صفحه محصول */
    .product-detail {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        background: #fff;
        padding: 30px;
        border-radius: 20px;
        box-shadow: var(--shadow);
        margin-top: 30px;
    }
    .product-image-box {
        background: #f5f3ff;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 120px;
        min-height: 350px;
        overflow: hidden;
    }
    .product-image-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        max-height: 400px;
    }
    .product-info h1 {
        font-size: 24px;
        margin-bottom: 10px;
        line-height: 1.6;
    }
    .product-cat {
        color: var(--muted);
        font-size: 14px;
        margin-bottom: 20px;
        display: inline-block;
        background: var(--primary-l);
        padding: 4px 12px;
        border-radius: 20px;
    }
    .price-display {
        font-size: 28px;
        font-weight: 900;
        color: var(--primary-d);
        margin-bottom: 20px;
    }
    .product-desc {
        line-height: 2;
        margin-bottom: 20px;
        color: var(--text);
    }
    .attr-select {
        width: 100%;
        padding: 12px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        margin-bottom: 10px;
        background: #fafafa;
        cursor: pointer;
    }
    .attr-select:focus {
        border-color: var(--primary);
        outline: none;
    }
    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    .btn-add-cart {
        flex: 1;
        padding: 15px;
        font-size: 16px;
        font-weight: 900;
        background: var(--grad);
        color: #fff;
        border: none;
        border-radius: 14px;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-add-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(124,58,237,0.3);
    }

    /* بخش محصولات مرتبط و تصادفی */
    .section-related {
        margin-top: 50px;
        margin-bottom: 20px;
    }
    .section-related .section-title {
        font-size: 20px;
        font-weight: 900;
        margin-bottom: 20px;
        padding-inline-start: 12px;
        border-inline-start: 5px solid var(--primary);
    }

    /* استایل کارت محصول (یکسان) */
    .product-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        transition: 0.3s;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }
    .product-card .p-img {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f5f3ff, #fdf2f8);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .product-card .p-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-card .p-body {
        padding: 15px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .product-card .p-cat {
        font-size: 11px;
        color: var(--primary);
        background: var(--primary-l);
        padding: 3px 10px;
        border-radius: 20px;
        align-self: flex-start;
        margin-bottom: 8px;
        font-weight: 700;
    }
    .product-card .p-name {
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 8px;
        min-height: 42px;
        color: var(--text);
        text-decoration: none;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .product-card .p-price {
        font-weight: 900;
        color: var(--primary-d);
        font-size: 16px;
        margin-bottom: 12px;
        margin-top: auto;
    }
    .product-card .p-actions {
        display: flex;
        gap: 8px;
    }
    .product-card .btn-view {
        flex: 1;
        display: block;
        text-align: center;
        text-decoration: none;
        background: var(--grad);
        color: #fff;
        padding: 10px;
        border-radius: 12px;
        font-weight: bold;
        font-size: 13px;
        transition: 0.3s;
    }
    .product-card .btn-view:hover {
        opacity: 0.9;
    }

    /* ریسپانسیو موبایل */
    @media (max-width: 900px) {
        .product-detail {
            grid-template-columns: 1fr;
            padding: 20px;
            gap: 20px;
        }
        .product-image-box {
            min-height: 250px;
            max-height: 300px;
        }
        .product-info h1 {
            font-size: 20px;
        }
        .price-display {
            font-size: 22px;
        }
        
        /* گرید محصولات در موبایل */
        .products-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
        .product-card .p-img {
            height: 140px;
        }
        .product-card .p-name {
            font-size: 12px;
            min-height: 36px;
        }
        .product-card .p-price {
            font-size: 14px;
        }
        .product-card .btn-view {
            font-size: 11px;
            padding: 8px;
        }
    }
</style>

<div class="container">
    <!-- جزئیات محصول -->
    <div class="product-detail">
        <div class="product-image-box">
            <?php if($p['image']): ?>
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
            <?php else: ?>
                📦
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h1><?= e($p['name']) ?></h1>
            <span class="product-cat"><?= e($p['cat_name']??'—') ?></span>
            
            <div id="priceDisplay" class="price-display">
                <?= fmt($p['price']) ?> تومان
            </div>
            
            <p class="product-desc"><?= nl2br(e($p['description'])) ?></p>

            <?php if($attrs): ?>
                <div style="margin-bottom:20px; border:1px solid #eee; padding:15px; border-radius:12px; background:#fafafa;">
                    <h3 style="color:var(--primary-d); margin-bottom:10px; font-size:15px;">انتخاب ویژگی‌ها:</h3>
                    <?php foreach($attrs as $a): 
                        $vals = $pdo->prepare("SELECT * FROM attribute_values WHERE attribute_id=?");
                        $vals->execute([$a['id']]); $vals = $vals->fetchAll();
                    ?>
                        <div style="margin-bottom:10px;">
                            <label style="font-weight:bold; display:block; margin-bottom:5px; font-size:13px;"><?= e($a['name']) ?>:</label>
                            <select class="attr-select" data-attr-name="<?= e($a['name']) ?>">
                                <option value="" data-price="0" selected>— بدون انتخاب —</option>
                                <?php foreach($vals as $v): ?>
                                    <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?>"><?= e($v['value']) ?> (<?= fmt($v['price']) ?> ت)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="product-actions">
                <button onclick="addToCartWithAttrs(<?= $p['id'] ?>, <?= $p['price'] ?>)" class="btn-add-cart">🛒 افزودن به سبد خرید</button>
                <button class="wish-btn <?= $in_wish?'active':'' ?>" data-pid="<?= $p['id'] ?>" style="width:60px; border:1.5px solid var(--border); border-radius:14px; background:#fff; color:#ef4444; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><?= $in_wish?'♥':'♡' ?></button>
            </div>
        </div>
    </div>

    <!-- محصولات مرتبط -->
    <?php if($related): ?>
    <div class="section-related">
        <h2 class="section-title">محصولات مرتبط</h2>
        <div class="products-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px;">
            <?php foreach($related as $rp): 
                $in_wish_r = check_wish($user, $rp['id'], $pdo);
            ?>
                <article class="product-card">
                    <a href="product.php?id=<?= $rp['id'] ?>" class="p-img">
                        <?php if($rp['image']): ?><img src="<?= e($rp['image']) ?>" alt="<?= e($rp['name']) ?>">
                        <?php else: ?>📦<?php endif; ?>
                    </a>
                    <div class="p-body">
                        <span class="p-cat"><?= e($rp['cat_name']??'—') ?></span>
                        <a href="product.php?id=<?= $rp['id'] ?>" class="p-name"><?= e($rp['name']) ?></a>
                        <div class="p-price"><?= fmt($rp['price']) ?> تومان</div>
                        <div class="p-actions">
                            <a href="product.php?id=<?= $rp['id'] ?>" class="btn-view">مشاهده محصول</a>
                            <button class="wish-btn <?= $in_wish_r ? 'active' : '' ?>" data-pid="<?= $rp['id'] ?>" style="width:40px; border:1.5px solid var(--border); border-radius:10px; background:#fff; color:#ef4444; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><?= $in_wish_r ? '♥' : '♡' ?></button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- محصولات تصادفی -->
    <?php if($random): ?>
    <div class="section-related" style="margin-bottom:50px;">
        <h2 class="section-title">پیشنهاد شگفت‌انگیز</h2>
        <div class="products-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px;">
            <?php foreach($random as $rp): 
                $in_wish_r = check_wish($user, $rp['id'], $pdo);
            ?>
                <article class="product-card">
                    <a href="product.php?id=<?= $rp['id'] ?>" class="p-img">
                        <?php if($rp['image']): ?><img src="<?= e($rp['image']) ?>" alt="<?= e($rp['name']) ?>">
                        <?php else: ?>📦<?php endif; ?>
                    </a>
                    <div class="p-body">
                        <span class="p-cat"><?= e($rp['cat_name']??'—') ?></span>
                        <a href="product.php?id=<?= $rp['id'] ?>" class="p-name"><?= e($rp['name']) ?></a>
                        <div class="p-price"><?= fmt($rp['price']) ?> تومان</div>
                        <div class="p-actions">
                            <a href="product.php?id=<?= $rp['id'] ?>" class="btn-view">مشاهده محصول</a>
                            <button class="wish-btn <?= $in_wish_r ? 'active' : '' ?>" data-pid="<?= $rp['id'] ?>" style="width:40px; border:1.5px solid var(--border); border-radius:10px; background:#fff; color:#ef4444; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><?= $in_wish_r ? '♥' : '♡' ?></button>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    // به‌روزرسانی قیمت با تغییر ویژگی‌ها
    document.querySelectorAll('.attr-select').forEach(select => {
        select.addEventListener('change', () => {
            let totalPrice = <?= (int)$p['price'] ?>;
            document.querySelectorAll('.attr-select').forEach(s => {
                if (s.value) {
                    totalPrice += Number(s.options[s.selectedIndex].dataset.price || 0);
                }
            });
            document.getElementById('priceDisplay').textContent = totalPrice.toLocaleString('fa-IR') + ' تومان';
        });
    });

    // افزودن به سبد با ویژگی‌ها
    function addToCartWithAttrs(pid, basePrice) {
        const btn = event.target; 
        btn.textContent = 'در حال افزودن...'; 
        btn.disabled = true;
        
        const selects = document.querySelectorAll('.attr-select');
        const attrs = {};
        let totalPrice = basePrice;
        
        selects.forEach(s => {
            if (s.value) {
                attrs[s.dataset.attrName] = s.options[s.selectedIndex].text;
                totalPrice += Number(s.options[s.selectedIndex].dataset.price || 0);
            }
        });
        
        fetch('api.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'add_to_cart',
                pid: pid,
                attrs: JSON.stringify(attrs),
                total_price: totalPrice,
                csrf: '<?= csrf() ?>'
            })
        }).then(r => r.json()).then(res => {
            if (res.ok) {
                alert('✅ محصول به سبد اضافه شد');
                window.location.href = 'cart.php';
            } else {
                alert(res.error || 'خطا');
                btn.textContent = '🛒 افزودن به سبد خرید'; 
                btn.disabled = false;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>