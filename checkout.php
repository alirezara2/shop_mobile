<?php
require_once 'config.php';

// بررسی لاگین بودن کاربر
if (!current_user()) redirect('login.php');

// بررسی احراز هویت
if (!is_user_verified()) {
    $page_title = 'نیاز به احراز هویت';
    include 'includes/header.php';
    echo '<div style="text-align:center; padding:50px; font-family:Tahoma;">
            <h2>برای خرید باید ابتدا <a href="profile.php?tab=verify" style="color:var(--primary); font-weight:bold;">احراز هویت</a> کنید.</h2>
            <a href="index.php" class="btn btn-primary" style="margin-top:20px; display:inline-block; text-decoration:none; padding:10px 20px;">بازگشت به فروشگاه</a>
          </div>';
    include 'includes/footer.php';
    exit;
}

// بررسی خالی نبودن سبد
if (!cart()) redirect('cart.php');

$u = current_user();
$page_title = 'تکمیل خرید';
include 'includes/header.php';
?>

<div class="container" style="max-width:800px; padding-top:30px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px;">🧾 تکمیل خرید</h1>
    
    <div style="background:#fff; padding:25px; border-radius:16px; box-shadow:var(--shadow);">
        
        <label style="font-weight:bold; display:block; margin-bottom:8px;">آدرس تحویل</label>
        <textarea id="address" style="width:100%; padding:12px; border:1px solid #ddd; border-radius:10px; min-height:80px; margin-bottom:20px; font-family:inherit;"><?= e($u['address'] ?? '') ?></textarea>

        <label style="font-weight:bold; display:block; margin-bottom:8px;">روش پرداخت</label>
        <div style="display:flex; gap:10px; margin-bottom:20px; flex-direction:column;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="radio" name="pay" value="gateway" checked> درگاه پرداخت زرین‌پال
            </label>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="radio" name="pay" value="cod"> پرداخت در محل (فقط شیراز)
            </label>
        </div>

        <button onclick="placeOrder()" class="btn btn-primary" id="submitBtn" style="width:100%; padding:15px; font-size:16px;">ثبت و پرداخت سفارش</button>
        <div id="msg" style="color:#ef4444; margin-top:15px; text-align:center; font-weight:bold;"></div>
    </div>
</div>

<script>
async function placeOrder() {
    const address = document.getElementById('address').value.trim();
    const payMethod = document.querySelector('input[name="pay"]:checked').value;
    const msg = document.getElementById('msg');
    const btn = document.getElementById('submitBtn');

    if (address.length < 10) {
        msg.textContent = 'لطفاً آدرس دقیق را وارد کنید (حداقل ۱۰ کاراکتر)';
        return;
    }

    btn.textContent = 'در حال ثبت سفارش...';
    btn.disabled = true;

    try {
        const res = await fetch('api.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'place_order',
                address: address,
                pay: payMethod,
                csrf: '<?= csrf() ?>'
            })
        }).then(r => r.json());

        if (res.ok) {
            // اگر پرداخت درگاه بود، به صفحه پرداخت می‌رود
            if (payMethod === 'gateway' && res.redirect) {
                window.location.href = res.redirect;
            } else {
                // اگر پرداخت در محل بود، به داشبورد می‌رود
                window.location.href = 'profile.php?tab=orders';
            }
        } else {
            msg.textContent = res.error || 'خطا در ثبت سفارش';
            btn.textContent = 'ثبت و پرداخت سفارش';
            btn.disabled = false;
        }
    } catch (err) {
        msg.textContent = 'خطا در ارتباط با سرور';
        btn.textContent = 'ثبت و پرداخت سفارش';
        btn.disabled = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>