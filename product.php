<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.active=1");
$s->execute([$id]);
$p = $s->fetch();

if (!$p) {
    $page_title = 'محصول یافت نشد';
    include 'includes/header.php';
    echo '<div class="container" style="padding:50px;text-align:center;"><h2>محصول یافت نشد</h2><a href="index.php" class="btn btn-primary">بازگشت به خانه</a></div>';
    include 'includes/footer.php';
    exit;
}

$page_title = $p['name'];
include 'includes/header.php';
?>

<div class="container" style="padding-top:30px;">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px; background:#fff; padding:30px; border-radius:20px; box-shadow:var(--shadow);">
        <div style="background:#f5f3ff; border-radius:15px; display:flex; align-items:center; justify-content:center; font-size:120px; min-height:300px; overflow:hidden;">
            <?php if($p['image']): ?>
                <img src="<?= e($p['image']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                📦
            <?php endif; ?>
        </div>
        
        <div>
            <h1 style="font-size:24px; margin-bottom:10px;"><?= e($p['name']) ?></h1>
            <div style="color:var(--muted); font-size:14px; margin-bottom:20px;">دسته: <?= e($p['cat_name'] ?? '—') ?></div>
            
            <div style="font-size:28px; font-weight:bold; color:var(--primary-d); margin-bottom:20px;">
                <?php if($p['discount_price'] > 0): ?>
                    <?= fmt($p['discount_price']) ?> تومان
                    <span style="font-size:16px; text-decoration:line-through; color:#999; margin-right:10px;"><?= fmt($p['price']) ?></span>
                <?php else: ?>
                    <?= fmt($p['price']) ?> تومان
                <?php endif; ?>
            </div>

            <p style="line-height:2; margin-bottom:30px; color:var(--text);"><?= nl2br(e($p['description'])) ?></p>

            <button onclick="addToCart(<?= $p['id'] ?>)" class="btn btn-primary" style="width:100%; padding:15px; font-size:16px;">🛒 افزودن به سبد خرید</button>
        </div>
    </div>
</div>

<script>
function addToCart(pid) {
    const btn = event.target;
    btn.textContent = 'در حال افزودن...';
    btn.disabled = true;

    fetch('api.php', {
        method: 'POST',
        body: new URLSearchParams({action: 'add_to_cart', pid, csrf: CSRF})
    }).then(r => r.json()).then(res => {
        if (res.ok) {
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