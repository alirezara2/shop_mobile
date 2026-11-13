<?php
require_once 'config.php';

if (isset($_GET['logout'])) { session_destroy(); redirect('admin.php'); }

$login_err = '';
if (isset($_POST['login_submit'])) {
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    $s = $pdo->prepare("SELECT * FROM admins WHERE username=?"); $s->execute([$u]); $a = $s->fetch();
    if ($a && password_verify($p, $a['password_hash'])) {
        session_regenerate_id(true); $_SESSION['admin_id'] = $a['id']; $_SESSION['admin_user'] = $a['username']; redirect('admin.php');
    } else { $login_err = 'نام کاربری یا رمز اشتباه است'; }
}

if (!is_admin()) { ?>
    <!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><title>ورود مدیر</title><link rel="stylesheet" href="assets/admin.css"></head><body class="admin-body">
    <div class="login-wrap"><form class="login-box" method="post">
        <h1>🔐 ورود مدیر</h1><p>پنل مدیریت فروشگاه</p>
        <?php if($login_err): ?><div class="alert err"><?= e($login_err) ?></div><?php endif; ?>
        <label>نام کاربری</label><input name="username" required autofocus>
        <label>رمز عبور</label><input type="password" name="password" required>
        <button class="btn btn-primary" name="login_submit" value="1">ورود</button>
        <div style="margin-top:16px;text-align:center"><a href="index.php" style="font-size:13px;color:#7c3aed">← بازگشت به سایت</a></div>
    </form></div></body></html>
<?php exit; }

if (isset($_POST['action'])) {
    check_csrf(); $action = $_POST['action'];
    switch($action){
        case 'add_product': {
            $name = trim($_POST['name']); $price = (int)$_POST['price']; $discount_price = (int)$_POST['discount_price'];
            $stock = (int)$_POST['stock']; $cat_id = (int)$_POST['category_id'] ?: null; $brand_id = (int)$_POST['brand_id'] ?: null;
            $desc = trim($_POST['description']); $is_new = isset($_POST['is_new'])?1:0; $is_featured = isset($_POST['is_featured'])?1:0;
            $image = upload_image('image');
            $pdo->prepare("INSERT INTO products (name,price,discount_price,stock,category_id,brand_id,description,image,is_new,is_featured) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$name,$price,$discount_price,$stock,$cat_id,$brand_id,$desc,$image,$is_new,$is_featured]);
            redirect('admin.php?tab=products&ok=1');
        }
        case 'edit_product': {
            $id = (int)$_POST['id']; $name = trim($_POST['name']); $price = (int)$_POST['price']; $discount_price = (int)$_POST['discount_price'];
            $stock = (int)$_POST['stock']; $cat_id = (int)$_POST['category_id'] ?: null; $brand_id = (int)$_POST['brand_id'] ?: null;
            $desc = trim($_POST['description']); $is_new = isset($_POST['is_new'])?1:0; $is_featured = isset($_POST['is_featured'])?1:0;
            $image = upload_image('image');
            if ($image) {
                $pdo->prepare("UPDATE products SET name=?,price=?,discount_price=?,stock=?,category_id=?,brand_id=?,description=?,image=?,is_new=?,is_featured=? WHERE id=?")
                    ->execute([$name,$price,$discount_price,$stock,$cat_id,$brand_id,$desc,$image,$is_new,$is_featured,$id]);
            } else {
                $pdo->prepare("UPDATE products SET name=?,price=?,discount_price=?,stock=?,category_id=?,brand_id=?,description=?,is_new=?,is_featured=? WHERE id=?")
                    ->execute([$name,$price,$discount_price,$stock,$cat_id,$brand_id,$desc,$is_new,$is_featured,$id]);
            }
            redirect('admin.php?tab=products&ok=1');
        }
        case 'delete_product': { $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]); redirect('admin.php?tab=products&ok=1'); }
        case 'toggle_product': { $pdo->prepare("UPDATE products SET active=1-active WHERE id=?")->execute([(int)$_POST['id']]); redirect('admin.php?tab=products&ok=1'); }
        case 'update_order_status': {
            $id = (int)$_POST['id']; $status = $_POST['status']; $tracking = trim($_POST['tracking_code'] ?? '');
            if (in_array($status, ['pending','paid','processing','shipped','delivered','cancelled'])) {
                $pdo->prepare("UPDATE orders SET status=?, tracking_code=? WHERE id=?")->execute([$status, $tracking ?: null, $id]);
            }
            redirect('admin.php?tab=orders&ok=1');
        }
        case 'update_settings': {
            $fields = ['site_name','site_phone','site_email','site_address','shipping_cost','free_shipping_min','home_welcome_text','card_number','card_holder_name'];
            foreach($fields as $f) { if(isset($_POST[$f])){ $pdo->prepare("INSERT INTO settings (k,v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=?")->execute([$f,$_POST[$f],$_POST[$f]]); } }
            if (!empty($_FILES['site_logo']['tmp_name'])) { $logo_path = upload_image('site_logo'); if ($logo_path) { $pdo->prepare("INSERT INTO settings (k,v) VALUES ('site_logo',?) ON DUPLICATE KEY UPDATE v=?")->execute([$logo_path,$logo_path]); } }
            redirect('admin.php?tab=settings&ok=1');
        }
        case 'update_page': {
            $slug = $_POST['slug']; $title = trim($_POST['title']); $content = $_POST['content'];
            $pdo->prepare("INSERT INTO pages (slug,title,content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE title=?,content=?")->execute([$slug,$title,$content,$title,$content]);
            redirect('admin.php?tab=pages&ok=1');
        }
        case 'add_brand': { $name=trim($_POST['name']); $slug=trim($_POST['slug']); if($name&&$slug){ $pdo->prepare("INSERT IGNORE INTO brands (name,slug) VALUES (?,?)")->execute([$name,$slug]); } redirect('admin.php?tab=brands&ok=1'); }
        case 'delete_brand': { $pdo->prepare("DELETE FROM brands WHERE id=?")->execute([(int)$_POST['id']]); redirect('admin.php?tab=brands&ok=1'); }
        case 'add_category': { $name=trim($_POST['name']); $slug=trim($_POST['slug']); $icon=trim($_POST['icon']??'📦'); if($name&&$slug){ $pdo->prepare("INSERT IGNORE INTO categories (name,slug,icon) VALUES (?,?,?)")->execute([$name,$slug,$icon]); } redirect('admin.php?tab=categories&ok=1'); }
        case 'delete_category': { $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_POST['id']]); redirect('admin.php?tab=categories&ok=1'); }
        
        // ویژگی‌ها
        case 'add_attribute': {
            $pid = (int)$_POST['product_id']; $name = trim($_POST['attr_name']);
            if ($name && $pid) { $pdo->prepare("INSERT INTO product_attributes (product_id, name) VALUES (?,?)")->execute([$pid, $name]); }
            redirect('admin.php?tab=edit_product&id=' . $pid . '&ok=1');
        }
        case 'delete_attribute': {
            $pid = (int)$_POST['pid'];
            $pdo->prepare("DELETE FROM product_attributes WHERE id=?")->execute([(int)$_POST['id']]);
            redirect('admin.php?tab=edit_product&id=' . $pid . '&ok=1');
        }
        case 'add_attribute_value': {
            $aid = (int)$_POST['attribute_id']; $val = trim($_POST['value']); $price = (int)$_POST['value_price'];
            $pid = (int)$_POST['pid'];
            if ($val && $aid) { $pdo->prepare("INSERT INTO attribute_values (attribute_id, value, price) VALUES (?,?,?)")->execute([$aid, $val, $price]); }
            redirect('admin.php?tab=edit_product&id=' . $pid . '&ok=1');
        }
        case 'delete_attribute_value': {
            $pid = (int)$_POST['pid'];
            $pdo->prepare("DELETE FROM attribute_values WHERE id=?")->execute([(int)$_POST['id']]);
            redirect('admin.php?tab=edit_product&id=' . $pid . '&ok=1');
        }
    }
}

$tab = $_GET['tab'] ?? 'dashboard';
$product_id = (int)($_GET['id'] ?? 0);
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY sort_order")->fetchAll();
$products = $pdo->query("SELECT p.*, c.name as cat_name, b.name as brand_name FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN brands b ON b.id=p.brand_id ORDER BY p.id DESC")->fetchAll();
$orders = $pdo->query("SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o LEFT JOIN users u ON u.id=o.user_id ORDER BY o.id DESC")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$pages = $pdo->query("SELECT * FROM pages")->fetchAll();

// اگر در صفحه ویرایش هستیم، محصول را دریافت کن
$edit_product = null;
if ($tab === 'edit_product' && $product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>پنل مدیریت</title><link rel="stylesheet" href="assets/admin.css"></head>
<body class="admin-body">
<div class="admin-layout">
    <div class="admin-overlay" id="adminOverlay"></div>
    <aside class="sidebar" id="sidebar">
        <h2>⚙️ پنل مدیریت</h2><div class="sub">👤 <?= e($_SESSION['admin_user']) ?></div>
        <a href="?tab=dashboard" class="nav-item <?= $tab=='dashboard'?'active':'' ?>">📊 داشبورد</a>
        <a href="?tab=products" class="nav-item <?= in_array($tab, ['products','add_product','edit_product'])?'active':'' ?>">📦 محصولات</a>
        <a href="?tab=orders" class="nav-item <?= $tab=='orders'?'active':'' ?>">🛒 سفارش‌ها</a>
        <a href="?tab=categories" class="nav-item <?= $tab=='categories'?'active':'' ?>">🗂️ دسته‌بندی‌ها</a>
        <a href="?tab=brands" class="nav-item <?= $tab=='brands'?'active':'' ?>">🏢 برندها</a>
        <a href="?tab=pages" class="nav-item <?= $tab=='pages'?'active':'' ?>">📄 صفحات</a>
        <a href="?tab=users" class="nav-item <?= $tab=='users'?'active':'' ?>">👥 کاربران</a>
        <a href="?tab=settings" class="nav-item <?= $tab=='settings'?'active':'' ?>">⚙️ تنظیمات</a>
        <div class="sidebar-foot"><a href="../index.php" target="_blank">🌐 مشاهده سایت</a><a href="?logout=1">🚪 خروج</a></div>
    </aside>

    <main class="main">
        <div class="top-bar"><h1>
            <?php 
                if ($tab === 'add_product') echo '➕ افزودن محصول';
                elseif ($tab === 'edit_product') echo '✏️ ویرایش محصول';
                else {
                    $titles=['dashboard'=>'📊 داشبورد','products'=>'📦 محصولات','orders'=>'🛒 سفارش‌ها','categories'=>'🗂️ دسته‌بندی‌ها','brands'=>'🏢 برندها','pages'=>'📄 صفحات','users'=>'👥 کاربران','settings'=>'⚙️ تنظیمات'];
                    echo $titles[$tab] ?? 'پنل';
                }
            ?>
        </h1><button class="mobile-menu-btn" id="mobileMenuBtn">☰</button></div>
        <?php if(isset($_GET['ok'])): ?><div class="alert ok">✅ عملیات موفق.</div><?php endif; ?>

        <?php if($tab == 'dashboard'): ?>
            <div class="stats">
                <div class="stat"><div class="label">محصولات</div><div class="val"><?= count($products) ?></div></div>
                <div class="stat"><div class="label">سفارش‌ها</div><div class="val"><?= count($orders) ?></div></div>
                <div class="stat"><div class="label">کاربران</div><div class="val"><?= count($users) ?></div></div>
            </div>
            <div class="card"><h3>📋 آخرین سفارش‌ها</h3>
                <div class="table-wrapper"><table><tr><th>کد</th><th>مشتری</th><th>مبلغ</th><th>وضعیت</th></tr>
                <?php foreach(array_slice($orders,0,5) as $o): ?><tr><td><?= e($o['order_code']) ?></td><td><?= e($o['user_name']) ?></td><td><?= fmt($o['total']) ?> ت</td><td><span class="badge <?= $o['status'] ?>"><?= $o['status'] ?></span></td></tr><?php endforeach; ?></table></div>
            </div>

        <?php elseif($tab == 'products'): ?>
            <div style="margin-bottom:20px"><a href="?tab=add_product" class="btn btn-primary" style="text-decoration:none;">➕ افزودن محصول جدید</a></div>
            
            <div class="card">
                <h3>📋 لیست محصولات</h3>
                <div class="products-admin-grid">
                    <?php foreach($products as $p): ?>
                    <div class="admin-product-card">
                        <div class="apc-img">
                            <?php if($p['image']): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                            <?php else: ?>📦<?php endif; ?>
                        </div>
                        <div class="apc-body">
                            <div class="apc-name"><?= e($p['name']) ?></div>
                            <div class="apc-price"><?= fmt($p['price']) ?> تومان</div>
                            <div class="apc-status">
                                <?= $p['active'] ? '<span class="badge paid">فعال</span>' : '<span class="badge cancelled">غیرفعال</span>' ?>
                            </div>
                            <div class="apc-actions">
                                <a href="?tab=edit_product&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline" style="text-decoration:none;">✏️ ویرایش</a>
                                <form method="post" style="display:inline;"><input type="hidden" name="action" value="toggle_product"><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-sm <?= $p['active']?'btn-outline':'btn-primary' ?>"><?= $p['active']?'غیرفعال':'فعال' ?></button></form>
                                <form method="post" style="display:inline;" onsubmit="return confirm('حذف شود؟');"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-sm btn-danger">🗑</button></form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif($tab == 'add_product'): ?>
            <!-- صفحه افزودن محصول -->
            <div class="card" style="border: 2px solid var(--primary);">
                <h3>➕ افزودن محصول جدید</h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_product">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <div class="row3">
                        <div><label>نام محصول</label><input name="name" required></div>
                        <div><label>قیمت اصلی</label><input name="price" type="number" required></div>
                        <div><label>قیمت با تخفیف</label><input name="discount_price" type="number" value="0"></div>
                        <div><label>موجودی</label><input name="stock" type="number" value="0"></div>
                        <div><label>دسته‌بندی</label><select name="category_id"><option value="">—</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>برند</label><select name="brand_id"><option value="">—</option><?php foreach($brands as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>عکس</label><input name="image" type="file"></div>
                    </div>
                    <label><input type="checkbox" name="is_new" value="1"> محصول جدید</label>
                    <label><input type="checkbox" name="is_featured" value="1"> محصول ویژه</label>
                    <label>توضیحات</label>
                    <textarea name="description"></textarea>
                    <button class="btn btn-primary" style="margin-top:20px">💾 ذخیره محصول</button>
                    <a href="?tab=products" class="btn btn-outline" style="text-decoration:none; margin-top:20px;">لغو</a>
                </form>
            </div>

        <?php elseif($tab == 'edit_product' && $edit_product): ?>
            <!-- صفحه ویرایش محصول -->
            <div class="card" style="border: 2px solid var(--primary);">
                <h3>✏️ ویرایش محصول: <?= e($edit_product['name']) ?></h3>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit_product">
                    <input type="hidden" name="id" value="<?= $edit_product['id'] ?>">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <div class="row3">
                        <div><label>نام محصول</label><input name="name" value="<?= e($edit_product['name']) ?>" required></div>
                        <div><label>قیمت اصلی</label><input name="price" type="number" value="<?= $edit_product['price'] ?>" required></div>
                        <div><label>قیمت با تخفیف</label><input name="discount_price" type="number" value="<?= $edit_product['discount_price'] ?>"></div>
                        <div><label>موجودی</label><input name="stock" type="number" value="<?= $edit_product['stock'] ?>"></div>
                        <div><label>دسته‌بندی</label><select name="category_id"><option value="">—</option><?php foreach($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$edit_product['category_id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>برند</label><select name="brand_id"><option value="">—</option><?php foreach($brands as $b): ?><option value="<?= $b['id'] ?>" <?= $b['id']==$edit_product['brand_id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
                        <div><label>عکس</label><input name="image" type="file"></div>
                    </div>
                    <label><input type="checkbox" name="is_new" value="1" <?= $edit_product['is_new']?'checked':'' ?>> محصول جدید</label>
                    <label><input type="checkbox" name="is_featured" value="1" <?= $edit_product['is_featured']?'checked':'' ?>> محصول ویژه</label>
                    <label>توضیحات</label>
                    <textarea name="description"><?= e($edit_product['description']) ?></textarea>
                    <button class="btn btn-primary" style="margin-top:20px">💾 ذخیره تغییرات</button>
                    <a href="?tab=products" class="btn btn-outline" style="text-decoration:none; margin-top:20px;">بازگشت به لیست محصولات</a>
                </form>
            </div>

            <!-- بخش ویژگی‌ها (در همان صفحه) -->
            <div class="card" style="margin-top:20px;">
                <h3>🎨 ویژگی‌ها (متغیرها)</h3>
                <ul id="attr-list-<?= $edit_product['id'] ?>" style="list-style:none; padding:0;">
                    <?php 
                        $attrs = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id=?");
                        $attrs->execute([$edit_product['id']]);
                        $attrs = $attrs->fetchAll();
                    ?>
                    <?php foreach($attrs as $a): ?>
                        <li style="background:#f8fafc; border:1px solid var(--border); padding:15px; border-radius:10px; margin-bottom:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <b><?= e($a['name']) ?></b>
                                <form method="post" onsubmit="return confirm('حذف کل ویژگی؟');" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_attribute"><input type="hidden" name="id" value="<?= $a['id'] ?>"><input type="hidden" name="pid" value="<?= $edit_product['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>">
                                    <button class="btn btn-sm btn-danger">حذف ویژگی</button>
                                </form>
                            </div>
                            <div style="margin-top:10px;">
                                <ul style="list-style:none; padding:0; display:flex; flex-wrap:wrap; gap:5px;">
                                    <?php 
                                        $vals = $pdo->prepare("SELECT * FROM attribute_values WHERE attribute_id=?");
                                        $vals->execute([$a['id']]);
                                        $vals = $vals->fetchAll();
                                    ?>
                                    <?php foreach($vals as $v): ?>
                                        <li style="background:var(--primary-l); color:var(--primary-d); padding:5px 12px; border-radius:20px; font-size:12px; display:flex; align-items:center; gap:5px;">
                                            <?= e($v['value']) ?> (<?= fmt($v['price']) ?> ت)
                                            <form method="post" style="display:inline;"><input type="hidden" name="action" value="delete_attribute_value"><input type="hidden" name="id" value="<?= $v['id'] ?>"><input type="hidden" name="pid" value="<?= $edit_product['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button style="background:none;border:none;color:#ef4444;cursor:pointer;">×</button></form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <form method="post" style="display:flex; gap:5px; margin-top:10px; flex-wrap:wrap;">
                                    <input type="hidden" name="action" value="add_attribute_value"><input type="hidden" name="attribute_id" value="<?= $a['id'] ?>"><input type="hidden" name="pid" value="<?= $edit_product['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>">
                                    <input name="value" placeholder="مثلاً قرمز" required style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                    <input name="value_price" placeholder="قیمت (تومان)" type="number" value="0" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                                    <button class="btn btn-sm btn-primary">+ افزودن مقدار</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" style="display:flex; gap:5px; margin-top:15px; border-top:1px solid #eee; padding-top:15px;">
                    <input type="hidden" name="action" value="add_attribute"><input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input name="attr_name" placeholder="نام ویژگی (مثلاً رنگ)" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;">
                    <button class="btn btn-primary">+ افزودن ویژگی</button>
                </form>
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
                        <input type="hidden" name="action" value="update_order_status"><input type="hidden" name="id" value="<?= $o['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>">
                        <select name="status" style="padding:8px; border-radius:8px; border:1px solid #ddd; flex:1; min-width:150px;">
                            <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>در انتظار</option>
                            <option value="paid" <?= $o['status']=='paid'?'selected':'' ?>>پرداخت شده</option>
                            <option value="processing" <?= $o['status']=='processing'?'selected':'' ?>>در حال آماده‌سازی</option>
                            <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?>>ارسال شده</option>
                            <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>تحویل شده</option>
                            <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>لغو شده</option>
                        </select>
                        <input name="tracking_code" placeholder="کد رهگیری" value="<?= e($o['tracking_code']??'') ?>" style="padding:8px; border-radius:8px; border:1px solid #ddd; flex:1; min-width:150px;">
                        <button class="btn btn-primary btn-sm">💾 ذخیره</button>
                    </form>
                </div>
            <?php endforeach; ?>

        <?php elseif($tab == 'categories'): ?>
            <div class="card"><h3>➕ افزودن دسته</h3><form method="post" style="display:flex; gap:10px; flex-wrap:wrap;"><input type="hidden" name="action" value="add_category"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input name="name" placeholder="نام" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;"><input name="slug" placeholder="slug" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;"><input name="icon" placeholder="آیکون" value="📦" style="width:80px; padding:10px; border:1px solid #ddd; border-radius:8px;"><button class="btn btn-primary">افزودن</button></form></div>
            <div class="card"><h3>📋 لیست</h3><div class="table-wrapper"><table><tr><th>آیکون</th><th>نام</th><th>slug</th><th>عملیات</th></tr><?php foreach($cats as $c): ?><tr><td><?= e($c['icon']) ?></td><td><?= e($c['name']) ?></td><td><?= e($c['slug']) ?></td><td><form method="post" onsubmit="return confirm('حذف؟')"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-danger btn-sm">حذف</button></form></td></tr><?php endforeach; ?></table></div></div>

        <?php elseif($tab == 'brands'): ?>
            <div class="card"><h3>➕ افزودن برند</h3><form method="post" style="display:flex; gap:10px; flex-wrap:wrap;"><input type="hidden" name="action" value="add_brand"><input type="hidden" name="csrf" value="<?= csrf() ?>"><input name="name" placeholder="نام" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;"><input name="slug" placeholder="slug" required style="flex:1; padding:10px; border:1px solid #ddd; border-radius:8px;"><button class="btn btn-primary">افزودن</button></form></div>
            <div class="card"><h3>📋 لیست</h3><div class="table-wrapper"><table><tr><th>نام</th><th>slug</th><th>عملیات</th></tr><?php foreach($brands as $b): ?><tr><td><?= e($b['name']) ?></td><td><?= e($b['slug']) ?></td><td><form method="post" onsubmit="return confirm('حذف؟')"><input type="hidden" name="action" value="delete_brand"><input type="hidden" name="id" value="<?= $b['id'] ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><button class="btn btn-danger btn-sm">حذف</button></form></td></tr><?php endforeach; ?></table></div></div>

        <?php elseif($tab == 'pages'): ?>
            <?php foreach($pages as $p): ?>
                <div class="card" style="margin-bottom:20px"><form method="post"><input type="hidden" name="action" value="update_page"><input type="hidden" name="slug" value="<?= e($p['slug']) ?>"><input type="hidden" name="csrf" value="<?= csrf() ?>"><label>عنوان</label><input name="title" value="<?= e($p['title']) ?>"><label>متن</label><textarea name="content" style="min-height:150px;"><?= e($p['content']) ?></textarea><button class="btn btn-primary" style="margin-top:10px">ذخیره</button></form></div>
            <?php endforeach; ?>

        <?php elseif($tab == 'settings'): ?>
            <div class="card"><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf" value="<?= csrf() ?>">
                <div style="margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #eee;"><label style="color:var(--primary-d); font-weight:bold; font-size:15px;">متن خوش‌آمدگویی</label><input name="home_welcome_text" value="<?= e(setting('home_welcome_text','✨ به فروشگاه ما خوش آمدید')) ?>"></div>
                <div style="margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #eee;"><label style="color:var(--primary-d); font-weight:bold; font-size:15px;">اطلاعات کارت به کارت</label><div class="row3"><div><label>شماره کارت</label><input name="card_number" value="<?= e(setting('card_number','6037-XXXX-XXXX-XXXX')) ?>"></div><div><label>نام صاحب حساب</label><input name="card_holder_name" value="<?= e(setting('card_holder_name','فروشگاه من')) ?>"></div></div></div>
                <div class="row3"><div><label>نام سایت</label><input name="site_name" value="<?= e(setting('site_name')) ?>"></div><div><label>تلفن</label><input name="site_phone" value="<?= e(setting('site_phone')) ?>"></div><div><label>ایمیل</label><input name="site_email" value="<?= e(setting('site_email')) ?>"></div><div><label>آدرس</label><input name="site_address" value="<?= e(setting('site_address')) ?>"></div><div><label>هزینه ارسال</label><input name="shipping_cost" type="number" value="<?= e(setting('shipping_cost')) ?>"></div><div><label>حداقل ارسال رایگان</label><input name="free_shipping_min" type="number" value="<?= e(setting('free_shipping_min')) ?>"></div><div style="grid-column:1/-1; margin-top:15px; border-top:1px solid #eee; padding-top:15px;"><label style="color:var(--primary-d); font-weight:bold;">لوگو</label><input name="site_logo" type="file" accept="image/*"><?php if(setting('site_logo')): ?><div style="margin-top:15px; display:flex; align-items:center; gap:15px;"><span style="font-size:12px; color:var(--muted);">لوگوی فعلی:</span><img src="<?= e(setting('site_logo')) ?>" style="max-height:60px; border-radius:8px; border:1px solid #ddd; padding:5px;"></div><?php endif; ?></div></div>
                <button class="btn btn-primary" style="margin-top:16px">ذخیره</button>
            </form></div>

        <?php elseif($tab == 'users'): ?>
            <div class="card"><div class="table-wrapper"><table><tr><th>نام</th><th>شماره</th><th>کد ملی</th><th>وضعیت</th><th>تاریخ</th></tr><?php foreach($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['national_code']??'—') ?></td><td><?= $u['is_verified']?'<span class="badge paid">تایید شده</span>':'<span class="badge cancelled">تایید نشده</span>' ?></td><td><?= date('Y/m/d', strtotime($u['created_at'])) ?></td></tr><?php endforeach; ?></table></div></div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded',()=>{
    const b=document.getElementById('mobileMenuBtn'),s=document.getElementById('sidebar'),o=document.getElementById('adminOverlay');
    if(b){b.addEventListener('click',()=>{s.classList.add('open');o.classList.add('open');});}
    if(o){o.addEventListener('click',()=>{s.classList.remove('open');o.classList.remove('open');});}
});
</script>
</body>
</html>