<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
$s = $pdo->prepare("SELECT * FROM brands WHERE slug=?");
$s->execute([$slug]);
$brand = $s->fetch();

if (!$brand) redirect('index.php');

$s = $pdo->prepare("SELECT * FROM products WHERE brand_id=? AND active=1");
$s->execute([$brand['id']]);
$products = $s->fetchAll();

$page_title = 'برند ' . $brand['name'];
include 'includes/header.php';
?>

<div class="container" style="padding-top:30px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px;">🏢 محصولات برند <?= e($brand['name']) ?></h1>
    <div class="products">
        <?php foreach($products as $p): ?>
            <article class="product-card">
                <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                    <?= $p['image'] ? '<img src="'.e($p['image']).'">' : '📦' ?>
                </a>
                <div class="p-body">
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                    <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if(!$products): ?><p>محصولی برای این برند ثبت نشده است.</p><?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>