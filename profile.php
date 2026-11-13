<?php
require_once 'config.php';

$u = current_user();
if (!$u) redirect('login.php');

$tab = $_GET['tab'] ?? 'dashboard';

// پردازش احراز هویت
$verify_msg = '';
if (isset($_POST['verify_submit'])) {
    check_csrf();
    $national = trim($_POST['national_code']);
    $postal = trim($_POST['postal_code']);
    $address = trim($_POST['address']);
    
    if (strlen($national) !== 10 || strlen($postal) !== 10 || strlen($address) < 10) {
        $verify_msg = '<div class="alert" style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:10px;margin-bottom:20px;">اطلاعات وارد شده معتبر نیست.</div>';
    } else {
        $pdo->prepare("UPDATE users SET national_code=?, postal_code=?, address=?, is_verified=1 WHERE id=?")
            ->execute([$national, $postal, $address, $u['id']]);
        redirect('profile.php?ok=1');
    }
}

// دریافت سفارش‌ها
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
$orders->execute([$u['id']]); 
$orders = $orders->fetchAll();

// دریافت محصولات علاقه‌مندی
$wishlist_items = $pdo->prepare("SELECT p.* FROM wishlist w JOIN products p ON p.id = w.product_id WHERE w.user_id = ? ORDER BY w.id DESC");
$wishlist_items->execute([$u['id']]);
$wishlist = $wishlist_items->fetchAll();

$page_title = 'حساب کاربری';
include 'includes/header.php';
?>

<div class="container" style="max-width:900px; padding-top:30px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px;">👤 حساب کاربری</h1>
    
    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
        <a href="profile.php?tab=dashboard" class="btn <?= $tab=='dashboard'?'btn-primary':'btn-outline' ?>" style="text-decoration:none;">داشبورد</a>
        <a href="profile.php?tab=orders" class="btn <?= $tab=='orders'?'btn-primary':'btn-outline' ?>" style="text-decoration:none;">سفارش‌ها</a>
        <a href="profile.php?tab=wishlist" class="btn <?= $tab=='wishlist'?'btn-primary':'btn-outline' ?>" style="text-decoration:none;">❤️ علاقه‌مندی‌ها</a>
        <a href="profile.php?tab=verify" class="btn <?= $tab=='verify'?'btn-primary':'btn-outline' ?>" style="text-decoration:none;">احراز هویت</a>
        
        <a href="api.php?action=logout&csrf=<?= csrf() ?>" class="btn btn-outline" style="color:#ef4444; border-color:#ef4444; margin-inline-start:auto; text-decoration:none;">🚪 خروج از حساب</a>
    </div>

    <?php if(isset($_GET['ok'])): ?>
        <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:10px;margin-bottom:20px;">✅ اطلاعات با موفقیت ذخیره شد.</div>
    <?php endif; ?>
    <?= $verify_msg ?>

    <?php if($tab == 'dashboard'): ?>
        
        <!-- نمایش سبد خرید در داشبورد -->
        <div style="background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow); margin-bottom:20px;">
            <h3 style="margin-bottom:15px; color:var(--primary-d);">🛒 سبد خرید شما</h3>
            <?php if(cart()): ?>
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:15px;">
                    <?php foreach(cart() as $item): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <div>
                                <b><?= e($item['name']) ?></b>
                                <span style="font-size:12px; color:var(--muted);">(تعداد: <?= fmt($item['qty']) ?>)</span>
                            </div>
                            <span style="font-weight:bold;"><?= fmt($item['unit_price'] * $item['qty']) ?> تومان</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; border-top:2px solid #eee; padding-top:10px;">
                    <b style="font-size:16px;">جمع کل: <?= fmt(cart_total()) ?> تومان</b>
                    <a href="checkout.php" class="btn btn-primary" style="text-decoration:none;">💳 برای ادامه پرداخت کلیک کنید</a>
                </div>
            <?php else: ?>
                <p style="color:var(--muted); text-align:center; padding:20px;">سبد خرید شما خالی است.</p>
                <a href="category.php" class="btn btn-outline" style="display:block; text-align:center; text-decoration:none;">مشاهده محصولات</a>
            <?php endif; ?>
        </div>

        <!-- اطلاعات حساب -->
        <div style="background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow);">
            <h3>خوش آمدید، <?= e($u['name']) ?>!</h3>
            <p style="margin-top:10px;">وضعیت حساب شما: 
                <?php if($u['is_verified']): ?>
                    <span style="color:#10b981;font-weight:bold;">✅ تایید شده</span>
                <?php else: ?>
                    <span style="color:#ef4444;font-weight:bold;">❌ تایید نشده</span> - برای خرید باید <a href="profile.php?tab=verify" style="color:var(--primary)">احراز هویت</a> کنید.
                <?php endif; ?>
            </p>
            <p style="margin-top:10px;">تعداد سفارش‌های شما: <b><?= count($orders) ?></b></p>
        </div>

    <?php elseif($tab == 'orders'): ?>
        <?php if(!$orders): ?>
            <div style="background:#fff; padding:20px; border-radius:16px;">هنوز سفارشی ثبت نکرده‌اید.</div>
        <?php else: ?>
            <?php foreach($orders as $o): ?>
                <div style="background:#fff; padding:20px; border-radius:16px; margin-bottom:15px; box-shadow:var(--shadow);">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:10px;">
                        <b style="color:var(--primary-d); font-size:15px;">سفارش: <?= e($o['order_code']) ?></b>
                        
                        <span style="padding:5px 12px; border-radius:20px; font-size:12px; font-weight:bold;
                            <?php if($o['status'] == 'pending'): ?>background:#fef3c7;color:#92400e;
                            <?php elseif($o['status'] == 'paid'): ?>background:#d1fae5;color:#065f46;
                            <?php elseif($o['status'] == 'processing'): ?>background:#dbeafe;color:#1e40af;
                            <?php elseif($o['status'] == 'shipped'): ?>background:#e0e7ff;color:#3730a3;
                            <?php elseif($o['status'] == 'delivered'): ?>background:#ede9fe;color:#5b21b6;
                            <?php else: ?>background:#fee2e2;color:#991b1b;<?php endif; ?>">
                            <?php 
                                $labels = [
                                    'pending' => 'در انتظار پرداخت',
                                    'paid' => 'پرداخت شده',
                                    'processing' => 'در حال آماده‌سازی',
                                    'shipped' => 'ارسال شده',
                                    'delivered' => 'تحویل داده شده',
                                    'cancelled' => 'لغو شده'
                                ];
                                echo $labels[$o['status']] ?? $o['status'];
                            ?>
                        </span>
                    </div>

                    <p style="font-size:13px; color:var(--muted); margin-bottom:10px;">تاریخ ثبت: <?= date('Y/m/d H:i', strtotime($o['created_at'])) ?></p>
                    <p style="font-weight:bold; margin-bottom:10px;">مبلغ نهایی: <?= fmt($o['total']) ?> تومان</p>
                    <p style="font-size:13px; margin-bottom:10px;">آدرس تحویل: <?= e($o['address']) ?></p>

                    <?php if($o['status'] == 'shipped' && $o['tracking_code']): ?>
                        <div style="margin-top:15px; background:#e0e7ff; color:#3730a3; padding:12px; border-radius:10px; font-weight:bold; font-size:14px; text-align:center;">
                            📦 کد رهگیری مرسوله شما: <span style="font-family:monospace; font-size:18px;"><?= e($o['tracking_code']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php elseif($tab == 'wishlist'): ?>
        <?php if(!$wishlist): ?>
            <div style="background:#fff; padding:20px; border-radius:16px; text-align:center; color:var(--muted);">
                لیست علاقه‌مندی‌های شما خالی است. ♡
            </div>
        <?php else: ?>
            <div class="products" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                <?php foreach($wishlist as $p): ?>
                    <article class="product-card reveal">
                        <a href="product.php?id=<?= $p['id'] ?>" class="p-img">
                            <?php if($p['image']): ?>
                                <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </a>
                        <div class="p-body">
                            <a href="product.php?id=<?= $p['id'] ?>" class="p-name"><?= e($p['name']) ?></a>
                            <div class="p-price"><?= fmt($p['price']) ?> تومان</div>
                            <button class="btn-cart" data-pid="<?= $p['id'] ?>">افزودن به سبد</button>
                            <!-- دکمه حذف از علاقه‌مندی -->
                            <button class="wish-btn active" data-pid="<?= $p['id'] ?>" style="width:100%; margin-top:5px; border:none; background:none; color:#ef4444; cursor:pointer; font-size:12px;">❌ حذف از علاقه‌مندی</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php elseif($tab == 'verify'): ?>
        <?php if($u['is_verified']): ?>
            <div style="background:#fff; padding:20px; border-radius:16px; text-align:center; box-shadow:var(--shadow);">
                <h3 style="color:#10b981;">✅ حساب شما قبلاً احراز هویت شده است.</h3>
            </div>
        <?php else: ?>
            <div style="background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow);">
                <h3 style="margin-bottom:15px;">تکمیل احراز هویت</h3>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                    <input type="hidden" name="verify_submit" value="1">
                    <label>کد ملی (۱۰ رقم)</label>
                    <input name="national_code" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;">
                    <label>کد پستی (۱۰ رقم)</label>
                    <input name="postal_code" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px;">
                    <label>آدرس دقیق پستی</label>
                    <textarea name="address" required style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px; min-height:80px;"></textarea>
                    <button type="submit" class="btn btn-primary" style="width:100%;">ثبت اطلاعات</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>