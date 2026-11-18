<?php
require_once 'config.php';

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

$page_title = 'محصولات';
include 'includes/header.php';
?>

<style>
    /* استایل اختصاصی صفحه فروشگاه */
    .shop-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 20px;
        padding-top: 30px;
    }
    .filter-sidebar {
        background: #fff;
        padding: 20px;
        border-radius: 16px;
        box-shadow: var(--shadow);
        height: fit-content;
        position: sticky;
        top: 90px;
    }
    .filter-sidebar h3 {
        font-size: 14px;
        margin-bottom: 8px;
        margin-top: 15px;
        color: var(--primary-d);
    }
    .filter-sidebar h3:first-child { margin-top: 0; }
    .filter-sidebar select, .filter-sidebar input {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-family: inherit;
        background: #fafafa;
    }
    .filter-btn {
        width: 100%;
        padding: 12px;
        background: var(--grad);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-weight: bold;
        cursor: pointer;
    }
    
    /* دکمه فیلتر مخصوص موبایل (در دسکتاپ مخفی) */
    .mobile-filter-btn {
        display: none;
        width: 100%;
        padding: 12px;
        background: #fff;
        color: var(--primary);
        border: 2px solid var(--primary);
        border-radius: 12px;
        font-weight: bold;
        margin-bottom: 15px;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    /* ریسپانسیو صفحه فروشگاه */
    @media (max-width: 900px) {
        .shop-layout {
            grid-template-columns: 1fr;
            padding-top: 15px;
        }
        
        .mobile-filter-btn {
            display: flex;
        }

        .filter-sidebar {
            display: none; /* در موبایل مخفی است */
            position: fixed;
            top: 0;
            right: -100%;
            width: 85%;
            max-width: 300px;
            height: 100vh;
            z-index: 1000;
            border-radius: 0;
            overflow-y: auto;
            transition: right 0.3s ease;
            box-shadow: -10px 0 30px rgba(0,0,0,0.2);
        }

        .filter-sidebar.open {
            display: block;
            right: 0;
        }

        /* لایه تاریک پشت فیلتر در موبایل */
        .filter-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        .filter-overlay.open {
            display: block;
        }
        
        .products {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 12px !important;
        }
    }
</style>

<div class="container shop-layout">
    
    <!-- لایه تاریک برای موبایل -->
    <div class="filter-overlay" id="filterOverlay"></div>

    <!-- دکمه فیلتر موبایل -->
    <button class="mobile-filter-btn" id="mobileFilterBtn">
        <span>⚙️</span> فیلتر و مرتب‌سازی محصولات
    </button>

    <!-- سایدبار فیلترها -->
    <aside class="filter-sidebar" id="filterSidebar">
        <!-- دکمه بستن در موبایل -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <b style="color:var(--primary-d);">فیلترها</b>
            <button id="closeFilterBtn" style="background:none; border:none; font-size:20px; color:#ef4444; cursor:pointer; display:none;">✕</button>
        </div>

        <form method="get" action="category.php">
            <h3>دسته‌بندی</h3>
            <select name="cat">
                <option value="">همه دسته‌ها</option>
                <?php foreach($cats as $c): ?>
                    <option value="<?= $c['slug'] ?>" <?= $cat_slug==$c['slug']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <h3>برند</h3>
            <select name="brand">
                <option value="">همه برندها</option>
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

            <button type="submit" class="filter-btn">اعمال فیلتر</button>
            <?php if($cat_slug || $brand_slug || $min_price || $max_price || $search): ?>
                <a href="category.php" style="display:block; text-align:center; margin-top:10px; font-size:12px; color:#ef4444; text-decoration:none;">حذف همه فیلترها</a>
            <?php endif; ?>
        </form>
    </aside>

    <!-- لیست محصولات -->
    <section>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <span style="font-size:14px; color:var(--muted);"><?= count($products) ?> محصول یافت شد</span>
        </div>
        
        <div class="products" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
            <?php foreach($products as $p): ?>
                <article class="product-card reveal">
                    <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                        <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>📦<?php endif; ?>
                    </a>
                    <div class="p-body">
                        <span class="p-cat"><?= e($p['cat_name']??'—') ?></span>
                        <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                        <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                        <button class="btn-cart" data-pid="<?= $p['id'] ?>">افزودن به سبد</button>
                    </div>
                </article>
            <?php endforeach; ?>
            
            <?php if(!$products): ?>
                <div style="grid-column: 1/-1; text-align:center; padding:50px; color:var(--muted);">
                    <h3>محصولی با این فیلتر یافت نشد.</h3>
                    <a href="category.php" style="color:var(--primary);">مشاهده همه محصولات</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    // منطق باز و بسته کردن فیلتر در موبایل
    document.addEventListener('DOMContentLoaded', () => {
        const mobileFilterBtn = document.getElementById('mobileFilterBtn');
        const filterSidebar = document.getElementById('filterSidebar');
        const filterOverlay = document.getElementById('filterOverlay');
        const closeFilterBtn = document.getElementById('closeFilterBtn');

        // چک کردن اینکه آیا در موبایل هستیم یا نه
        const isMobile = () => window.innerWidth <= 900;

        if (mobileFilterBtn) {
            mobileFilterBtn.onclick = () => {
                filterSidebar.classList.add('open');
                filterOverlay.classList.add('open');
                if (isMobile()) closeFilterBtn.style.display = 'block';
            };
        }

        const closeFilter = () => {
            filterSidebar.classList.remove('open');
            filterOverlay.classList.remove('open');
        };

        if (closeFilterBtn) closeFilterBtn.onclick = closeFilter;
        if (filterOverlay) filterOverlay.onclick = closeFilter;
    });
</script>

<?php include 'includes/footer.php'; ?>