<?php
require_once 'config.php';

if (isset($_GET['logout'])) {
    session_destroy();
    redirect('admin.php');
}

$login_err = '';
if (isset($_POST['login_submit'])) {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    
    $s = $pdo->prepare("SELECT * FROM admins WHERE username=?"); 
    $s->execute([$u]);
    $a = $s->fetch();
    if ($a && password_verify($p, $a['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $a['id'];
        $_SESSION['admin_user'] = $a['username'];
        redirect('admin.php');
    } else {
        $login_err = 'نام کاربری یا رمز اشتباه است';
    }
}

if (!is_admin()) {
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ورود مدیر</title>
        <link rel="stylesheet" href="assets/admin.css">
    </head>
    <body class="admin-body">
        <div class="login-wrap">
            <form class="login-box" method="post">
                <h1>🔐 ورود مدیر</h1>
                <p>پنل مدیریت فروشگاه</p>
                <?php if($login_err): ?><div class="alert err"><?= e($login_err) ?></div><?php endif; ?>
                <label>نام کاربری</label>
                <input name="username" required autofocus>
                <label>رمز عبور</label>
                <input type="password" name="password" required>
                <button class="btn btn-primary" name="login_submit" value="1">ورود</button>
                <div style="margin-top:16px;text-align:center"><a href="index.php" style="font-size:13px;color:#7c3aed">← بازگشت به سایت</a></div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_POST['action'])) {
    check_csrf();
    $action = $_POST['action'];

    switch($action){
        case 'add_product': {
            $name = trim($_POST['name']);
            $price = (int)$_POST['price'];
            $discount_price = (int)$_POST['discount_price'];
            $stock = (int)$_POST['stock'];
            $cat_id = (int)$_POST['category_id'] ?: null;
            $brand_id = (int)$_POST['brand_id'] ?: null;
            $desc = trim($_POST['description']);
            $is_new = isset($_POST['is_new']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $image = upload_image('image');

            $pdo->prepare("INSERT INTO products (name, price, discount_price, stock, category_id, brand_id, description, image, is_new, is_featured) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name, $price, $discount_price, $stock, $cat_id, $brand_id, $desc, $image, $is_new, $is_featured]);
            redirect('admin.php?tab=products&ok=1');
        }

        case 'delete_product': {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            redirect('admin.php?tab=products&ok=1');
        }

        case 'update_order_status': {
            $id = (int)$_POST['id'];
            $status = $_POST['status'];
            $tracking = trim($_POST['tracking_code'] ?? '');
            $allowed = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (in_array($status, $allowed)) {
                $pdo->prepare("UPDATE orders SET status=?, tracking_code=? WHERE id=?")
                    ->execute([$status, $tracking ?: null, $id]);
            }
            redirect('admin.php?tab=orders&ok=1');
        }

        case 'update_settings': {
            $fields = ['site_name','site_phone','site_email','site_address','shipping_cost','free_shipping_min'];
            foreach($fields as $f) {
                if(isset($_POST[$f])){
                    $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=?")->execute([$f, $_POST[$f], $_POST[$f]]);
                }
            }
            redirect('admin.php?tab=settings&ok=1');
        }

        case 'update_page': {
            $slug = $_POST['slug'];
            $title = trim($_POST['title']);
            $content = $_POST['content'];
            $pdo->prepare("INSERT INTO pages (slug, title, content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE title=?, content=?")
                ->execute([$slug, $title, $content, $title, $content]);
            redirect('admin.php?tab=pages&ok=1');
        }

        case 'add_brand': {
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            if ($name && $slug) {
                $pdo->prepare("INSERT IGNORE INTO brands (name, slug) VALUES (?,?)")->execute([$name, $slug]);
            }
            redirect('admin.php?tab=brands&ok=1');
        }

        case 'delete_brand': {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM brands WHERE id=?")->execute([$id]);
            redirect('admin.php?tab=brands&ok=1');
        }

        case 'add_category': {
            $name = trim($_POST['name']);
            $slug = trim($_POST['slug']);
            $icon = trim($_POST['icon'] ?? '📦');
            if ($name && $slug) {
                $pdo->prepare("INSERT IGNORE INTO categories (name, slug, icon) VALUES (?,?,?)")->execute([$name, $slug, $icon]);
            }
            redirect('admin.php?tab=categories&ok=1');
        }

        case 'delete_category': {
            $id = (int)$_POST['id'];
            $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
            redirect('admin.php?tab=categories&ok=1');
        }
    }
}

$tab = $_GET['tab'] ?? 'dashboard';
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY sort_order")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as cat_name, b.name as brand_name FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id ORDER BY p.id DESC")->fetchAll();
$orders = $pdo->query("SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$pages = $pdo->query("SELECT * FROM pages")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">

    <div class="admin-overlay" id="adminOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <h2>⚙️ پنل مدیریت</h2>
        <div class="sub">👤 <?= e($_SESSION['admin_user']) ?></div>
        
        <a href="?tab=dashboard" class="nav-item <?= $tab=='dashboard'?'active':'' ?>">📊 داشبورد</a>
        <a href="?tab=products" class="nav-item <?= $tab=='products'?'active':'' ?>">📦 محصولات</a>
        <a href="?tab=orders" class="nav-item <?= $tab=='orders'?'active':'' ?>">🛒 سفارش‌ها</a>
        <a href="?tab=categories" class="nav-item <?= $tab=='categories'?'active':'' ?>">🗂️ دسته‌بندی‌ها</a>
        <a href="?tab=brands" class="nav-item <?= $tab=='brands'?'active':'' ?>">🏢 برندها</a>
        <a href="?tab=pages" class="nav-item <?= $tab=='pages'?'active':'' ?>">📄 صفحات (درباره ما)</a>
        <a href="?tab=users" class="nav-item <?= $tab=='users'?'active':'' ?>">👥 کاربران</a>
        <a href="?tab=settings" class="nav-item <?= $tab=='settings'?'active':'' ?>">⚙️ تنظیمات سایت</a>

        <div class="sidebar-foot">
            <a href="index.php" target="_blank">🌐 مشاهده سایت</a>
            <a href="?logout=1">🚪 خروج</a>
        </div>
    </aside>

    <main class="main">
        <div class="top-bar">
            <h1>
                <?php 
                    $titles = ['dashboard'=>'📊 داشبورد','products'=>'📦 محصولات','orders'=>'🛒 سفارش‌ها','categories'=>'🗂️ دسته‌بندی‌ها','brands'=>'🏢 برندها','pages'=>'📄 صفحات','users'=>'👥 کاربران','settings'=>'⚙️ تنظیمات'];
                    echo $titles[$tab] ?? 'پنل مدیریت'; 
                ?>
            </h1>
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
        </div>

        <?php if(isset($_GET['ok'])): ?><div class="alert ok">✅ عملیات با موفقیت انجام شد.</div><?php endif; ?>

        <?php if($tab == 'dashboard'): ?>
            <div class="stats">
                <div class="stat"><div class="label">محصولات</div><div class="val"><?= count($products) ?></div></div>
                <div class="stat"><div class="label">سفارش‌ها</div><div class="val"><?= count($orders) ?></div></div>
                <div class="stat"><div class="label">کاربران</div><div class="val"><?= count($users) ?></div></div>
            </div>
            <div class="card">
                <h3>📋 آخرین سفارش‌ها</h3>
                <table>
                    <tr><th>کد</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th></tr>
                    <?php foreach(array_slice($orders, 0, 5) as $o): ?>
                        <tr><td><?= e($o['order_code']) ?></td><td><?= e($o['user_name']) ?></td><td><?= fmt($o['total']) ?> ت</td><td><span class="badge <?= $o['status'] ?>"><?= $o['status'] ?></span></td></tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'products'): ?>
            <div class="card">
                <h3>➕ افزودن محصول جدید</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <div class="row3">
                        <div><label>نام محصول</label><input name="name" required></div>
                        <div><label>قیمت اصلی (تومان)</label><input name="price" type="number" required></div>
                        <div><label>قیمت با تخفیف (۰=بدون تخفیف)</label><input name="discount_price" type="number" value="0"></div>
                        <div><label>موجودی</label><input name="stock" type="number" value="0"></div>
                        <div><label>دسته‌بندی</label><select name="category_id"><option value="">—</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>برند</label><select name="brand_id"><option value="">—</option><?php foreach($brands as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>عکس</label><input name="image" type="file"></div>
                    </div>
                    <label><input type="checkbox" name="is_new" value="1"> محصول جدید</label>
                    <label><input type="checkbox" name="is_featured" value="1"> محصول ویژه</label>
                    <label>توضیحات</label>
                    <textarea name="description"></textarea>
                    <button class="btn btn-primary" style="margin-top:16px">ذخیره محصول</button>
                </form>
            </div>

            <div class="card">
                <h3>📋 لیست محصولات</h3>
                <table>
                    <tr><th>عکس</th><th>نام</th><th>قیمت</th><th>موجودی</th><th>عملیات</th></tr>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td><?= $p['image'] ? '<img src="'.e($p['image']).'" style="width:40px;height:40px;border-radius:8px;object-fit:cover">' : '📦' ?></td>
                        <td><?= e($p['name']) ?></td>
                        <td><?= fmt($p['price']) ?> ت</td>
                        <td><?= $p['stock'] ?></td>
                        <td>
                            <form method="post" style="display:inline" onsubmit="return confirm('حذف شود؟')"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-danger btn-sm">حذف</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'orders'): ?>
            <?php foreach($orders as $o): ?>
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:10px;">
                        <b style="color:var(--primary-d); font-size:15px;"><?= e($o['order_code']) ?></b>
                        <span class="badge <?= $o['status'] ?>"><?= $o['status'] ?></span>
                    </div>
                    <p><b>مشتری:</b> <?= e($o['user_name']) ?> (<?= e($o['user_phone']) ?>)</p>
                    <p style="font-size:13px; color:var(--muted); margin:5px 0;"><b>آدرس:</b> <?= e($o['address']) ?></p>
                    <p style="font-weight:bold; margin-top:10px;">مبلغ: <?= fmt($o['total']) ?> تومان</p>
                    
                    <form method="post" style="display:flex; gap:8px; margin-top:15px; flex-wrap:wrap; align-items:center;">
                        <input type="hidden" name="action" value="update_order_status">
                        <input type="hidden" name="id" value="<?= $o['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= csrf() ?>">
                        
                        <select name="status" style="padding:8px; border-radius:8px; border:1px solid #ddd; flex:1; min-width:150px;">
                            <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>در انتظار پرداخت</option>
                            <option value="paid" <?= $o['status']=='paid'?'selected':'' ?>>پرداخت شده</option>
                            <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>در حال آماده‌سازی</option>
                            <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?>>ارسال شده</option>
                            <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>تحویل داده شده</option>
                            <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>لغو شده</option>
                        </select>
                        
                        <input name="tracking_code" placeholder="کد رهگیری پستی" value="<?= e($o['tracking_code'] ?? '') ?>" style="padding:8px; border-radius:8px; border:1px solid #ddd; flex:1; min-width:150px;">
                        
                        <button class="btn btn-primary btn-sm">💾 ذخیره</button>
                    </form>
                </div>
            <?php endforeach; ?>

        <?php elseif($tab == 'categories'): ?>
            <div class="card">
                <h3>➕ افزودن دسته‌بندی</h3>
                <form method="post" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_category">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input name="name" placeholder="نام دسته" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <input name="slug" placeholder="slug (انگلیسی)" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <input name="icon" placeholder="آیکون" value="📦" style="width:80px; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <button class="btn btn-primary">افزودن</button>
                </form>
            </div>
            <div class="card">
                <h3>📋 لیست دسته‌بندی‌ها</h3>
                <table>
                    <tr><th>آیکون</th><th>نام</th><th>slug</th><th>عملیات</th></tr>
                    <?php foreach($cats as $c): ?>
                    <tr>
                        <td><?= e($c['icon']) ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td><?= e($c['slug']) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('حذف شود؟')"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-danger btn-sm">حذف</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'brands'): ?>
            <div class="card">
                <h3>➕ افزودن برند</h3>
                <form method="post" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="add_brand">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input name="name" placeholder="نام برند" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <input name="slug" placeholder="slug (انگلیسی)" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <button class="btn btn-primary">افزودن</button>
                </form>
            </div>
            <div class="card">
                <h3>📋 لیست برندها</h3>
                <table>
                    <tr><th>نام</th><th>slug</th><th>عملیات</th></tr>
                    <?php foreach($brands as $b): ?>
                    <tr>
                        <td><?= e($b['name']) ?></td>
                        <td><?= e($b['slug']) ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('حذف شود؟')"><input type="hidden" name="action" value="delete_brand"><input type="hidden" name="id" value="<?= $b['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-danger btn-sm">حذف</button></form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php elseif($tab == 'pages'): ?>
            <?php foreach($pages as $p): ?>
                <div class="card" style="margin-bottom:20px">
                    <form method="post">
                        <input type="hidden" name="action" value="update_page">
                        <input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
                        <input type="hidden" name="csrf" value="<?= csrf() ?>">
                        <label>عنوان (<?= e($p['slug']) ?>)</label>
                        <input name="title" value="<?= e($p['title']) ?>">
                        <label>متن</label>
                        <textarea name="content" style="min-height:150px;direction:rtl"><?= e($p['content']) ?></textarea>
                        <button class="btn btn-primary" style="margin-top:10px">ذخیره تغییرات</button>
                    </form>
                </div>
            <?php endforeach; ?>

        <?php elseif($tab == 'settings'): ?>
            <div class="card">
                <form method="post">
                    <input type="hidden" name="action" value="update_settings">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <div class="row3">
                        <div><label>نام سایت</label><input name="site_name" value="<?= e(setting('site_name')) ?>"></div>
                        <div><label>تلفن</label><input name="site_phone" value="<?= e(setting('site_phone')) ?>"></div>
                        <div><label>ایمیل</label><input name="site_email" value="<?= e(setting('site_email')) ?>"></div>
                        <div><label>آدرس</label><input name="site_address" value="<?= e(setting('site_address')) ?>"></div>
                        <div><label>هزینه ارسال (تومان)</label><input name="shipping_cost" type="number" value="<?= e(setting('shipping_cost')) ?>"></div>
                        <div><label>حداقل ارسال رایگان</label><input name="free_shipping_min" type="number" value="<?= e(setting('free_shipping_min')) ?>"></div>
                    </div>
                    <button class="btn btn-primary" style="margin-top:16px">ذخیره تنظیمات</button>
                </form>
            </div>

        <?php elseif($tab == 'users'): ?>
            <div class="card">
                <table>
                    <tr><th>نام</th><th>شماره</th><th>کد ملی</th><th>وضعیت</th><th>تاریخ عضویت</th></tr>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['phone']) ?></td>
                        <td><?= e($u['national_code'] ?? '—') ?></td>
                        <td><?= $u['is_verified'] ? '<span class="badge paid">تایید شده</span>' : '<span class="badge cancelled">تایید نشده</span>' ?></td>
                        <td><?= date('Y/m/d', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const menuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('adminOverlay');

    if (menuBtn) {
        menuBtn.addEventListener('click', () => {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
});
</script>
</body>
</html>