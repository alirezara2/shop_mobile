<?php
require_once 'config.php';

$user = current_user();
$cat_slug = $_GET['cat'] ?? '';
$brand_slug = $_GET['brand'] ?? '';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$min_price = (int)($_GET['min_price'] ?? 0);
$max_price = (int)($_GET['max_price'] ?? 0);

$where = ["p.active=1"];
$params = [];
if ($cat_slug) { $where[] = "c.slug = ?"; $params[] = $cat_slug; }
if ($brand_slug) { $where[] = "b.slug = ?"; $params[] = $brand_slug; }
if ($search) { $where[] = "p.name LIKE ?"; $params[] = "%$search%"; }
if ($min_price > 0) { $where[] = "p.price >= ?"; $params[] = $min_price; }
if ($max_price > 0) { $where[] = "p.price <= ?"; $params[] = $max_price; }

$orderBy = "p.id DESC";
if ($sort === 'cheap') $orderBy = "p.price ASC";
if ($sort === 'expensive') $orderBy = "p.price DESC";

$sql = "SELECT p.*, c.name as cat_name FROM products p 
        LEFT JOIN categories c ON c.id=p.category_id 
        LEFT JOIN brands b ON b.id=p.brand_id 
        WHERE " . implode(' AND ', $where) . " ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands")->fetchAll();

function check_wish_cat($user, $pid, $pdo) {
    if (!$user) return false;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user['id'], $pid]);
    return $stmt->fetch() ? true : false;
}

$page_title = 'محصولات';
include 'includes/header.php';
?>

<style>
    .shop-layout { display: flex; gap: 24px; align-items: flex-start; margin-top: 20px; margin-bottom: 50px; }
    .sidebar { width: 250px; flex-shrink: 0; background: #fff; padding: 20px; border-radius: 16px; box-shadow: var(--shadow); position: sticky; top: 90px; }
    .sidebar h3 { font-size: 14px; margin-bottom: 8px; margin-top: 16px; color: var(--primary-d); }
    .sidebar h3:first-child { margin-top: 0; }
    .sidebar select, .sidebar input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; background: #fafafa; }
    .sidebar .btn-filter { width: 100%; padding: 12px; background: var(--grad); color: #fff; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 10px; }
    .products-section { flex: 1; min-width: 0; }
    .mobile-filter-btn { display: none; }
    .close-filter-btn { display: none; }
    .filter-overlay { display: none; }

    @media (max-width: 900px) {
        .shop-layout { display: block; }
        .mobile-filter-btn { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; background: var(--primary-l); color: var(--primary-d); border: 1.5px solid var(--primary); border-radius: 12px; font-weight: bold; margin-bottom: 20px; cursor: pointer; }
        .sidebar { position: fixed; top: 0; right: -100%; width: 280px; height: 100vh; z-index: 9999; border-radius: 0; overflow-y: auto; transition: right 0.3s ease; box-shadow: -5px 0 20px rgba(0,0,0,0.2); }
        .sidebar.open { right: 0; }
        .close-filter-btn { display: block; background: none; border: none; font-size: 24px; color: var(--danger); cursor: pointer; margin-right: auto; }
        .filter-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9998; display: none; }
        .filter-overlay.open { display: block; }
        .products-section .products { grid-template-columns: repeat(2, 1fr) !important; gap: 12px !important; }
    }
</style>

<div class="container">
    <button class="mobile-filter-btn" id="openFilterBtn"><span>⚙️</span> فیلتر و مرتب‌سازی</button>
    <div class="filter-overlay" id="filterOverlay"></div>

    <div class="shop-layout">
        <aside class="sidebar" id="filterSidebar">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <b style="color:var(--primary-d); font-size:16px;">فیلترها</b>
                <button class="close-filter-btn" id="closeFilterBtn">✕</button>
            </div>

            <form method="get" action="category.php">
                <h3>دسته‌بندی</h3>
                <select name="cat">
                    <option value="">همه</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c['slug'] ?>" <?= $cat_slug==$c['slug']?'selected':'' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <h3>برند</h3>
                <select name="brand">
                    <option value="">همه</option>
                    <?php foreach($brands as $b): ?>
                        <option value="<?= $b['slug'] ?>" <?= $brand_slug==$b['slug']?'selected':'' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <h3>محدوده قیمت (تومان)</h3>
                <input name="min_price" type="number" placeholder="از" value="<?= $min_price ?: '' ?>">
                <input name="max_price" type="number" placeholder="تا" value="<?= $max_price ?: '' ?>">

                <h3>مرتب‌سازی</h3>
                <select name="sort">
                    <option value="newest" <?= $sort=='newest'?'selected':'' ?>>جدیدترین</option>
                    <option value="cheap" <?= $sort=='cheap'?'selected':'' ?>>ارزان‌ترین</option>
                    <option value="expensive" <?= $sort=='expensive'?'selected':'' ?>>گران‌ترین</option>
                </select>

                <button type="submit" class="btn-filter">اعمال فیلتر</button>
                <?php if($cat_slug || $brand_slug || $min_price || $max_price || $search): ?>
                    <a href="category.php" style="display:block; text-align:center; margin-top:12px; font-size:12px; color:var(--danger); text-decoration:none;">حذف فیلترها</a>
                <?php endif; ?>
            </form>
        </aside>

        <section class="products-section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <span style="font-size:14px; color:var(--muted);"><?= count($products) ?> محصول یافت شد</span>
            </div>
            
            <div class="products">
                <?php foreach($products as $p): 
                    $in_wish = check_wish_cat($user, $p['id'], $pdo);
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
                
                <?php if(!$products): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--muted);">
                        <h3>محصولی با این فیلتر یافت نشد.</h3>
                        <a href="category.php" style="color:var(--primary);">مشاهده همه محصولات</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openBtn = document.getElementById('openFilterBtn');
        const closeBtn = document.getElementById('closeFilterBtn');
        const sidebar = document.getElementById('filterSidebar');
        const overlay = document.getElementById('filterOverlay');

        if (openBtn) openBtn.addEventListener('click', () => { sidebar.classList.add('open'); overlay.classList.add('open'); });
        if (closeBtn) closeBtn.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
        if (overlay) overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('open'); });
    });
</script>

<?php include 'includes/footer.php'; ?>