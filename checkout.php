<?php
require_once 'config.php';

if (!current_user()) redirect('login.php');
if (!is_user_verified()) {
    $page_title = 'نیاز به احراز هویت';
    include 'includes/header.php';
    echo '<div style="text-align:center; padding:50px;"><h2>برای خرید باید ابتدا <a href="profile.php?tab=verify" style="color:var(--primary);">احراز هویت</a> کنید.</h2></div>';
    include 'includes/footer.php';
    exit;
}
if (!cart()) redirect('cart.php');

$u = current_user();
$page_title = 'تکمیل خرید';
include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .checkout-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 20px;
        margin-top: 20px;
        margin-bottom: 50px;
    }
    .checkout-card {
        background: #fff;
        padding: 25px;
        border-radius: 16px;
        box-shadow: var(--shadow);
    }
    .checkout-card h3 { margin-bottom: 15px; color: var(--primary-d); }
    
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-weight: bold; margin-bottom: 8px; }
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-family: inherit;
        background: #fafafa;
    }
    .form-group input:focus, .form-group textarea:focus { border-color: var(--primary); background: #fff; outline: none; }

    #map { height: 250px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 10px; }

    .payment-methods {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }
    .payment-option {
        flex: 1;
        border: 2px solid #ddd;
        padding: 15px;
        border-radius: 12px;
        cursor: pointer;
        text-align: center;
        font-weight: bold;
        transition: 0.2s;
    }
    .payment-option:hover { border-color: #c7d2fe; }
    .payment-option.active { border-color: var(--primary); background: var(--primary-l); color: var(--primary-d); }
    .payment-option input { display: none; }

    .card-info {
        display: none;
        background: #fffbeb;
        border: 2px dashed #f59e0b;
        padding: 15px;
        border-radius: 12px;
        margin-top: 15px;
        text-align: center;
    }
    .card-info.show { display: block; }
    .card-info .num { font-size: 20px; font-family: monospace; font-weight: bold; letter-spacing: 2px; margin: 5px 0; color: #92400e; }
    .card-info .name { font-size: 14px; color: #92400e; }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 14px;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 2px solid #eee;
        font-weight: bold;
        font-size: 16px;
        color: var(--primary-d);
    }

    @media (max-width: 900px) {
        .checkout-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="container">
    <h1 style="color:var(--primary-d); margin-top:30px;">🧾 تکمیل خرید</h1>
    
    <div class="checkout-grid">
        <!-- فرم اطلاعات -->
        <div class="checkout-card">
            <form id="checkoutForm">
                <h3>اطلاعات تحویل</h3>
                
                <div class="form-group">
                    <label>شماره تماس *</label>
                    <input type="tel" id="phone" placeholder="09123456789" value="<?= e($u['phone']) ?>" required maxlength="11">
                </div>

                <div class="form-group">
                    <label>آدرس دقیق پستی *</label>
                    <textarea id="address" rows="3" placeholder="استان، شهر، خیابان، کوچه، پلاک، واحد" required><?= e($u['address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>انتخاب موقعیت روی نقشه (اختیاری)</label>
                    <div id="map"></div>
                    <small id="mapCoords" style="color:var(--muted); font-size:12px;">روی نقشه کلیک کنید تا موقعیت شما ثبت شود</small>
                </div>

                <h3 style="margin-top:25px;">روش پرداخت</h3>
                <div class="payment-methods">
                    <label class="payment-option active" id="payGatewayLabel">
                        <input type="radio" name="pay" value="gateway" checked>
                        🌐 پرداخت آنلاین
                    </label>
                    <label class="payment-option" id="payCardLabel">
                        <input type="radio" name="pay" value="card">
                        💳 کارت به کارت
                    </label>
                </div>

                <!-- نمایش شماره کارت -->
                <div class="card-info" id="cardInfo">
                    <div style="font-size:13px; color:#92400e; margin-bottom:5px;">لطفاً مبلغ را به کارت زیر واریز کنید:</div>
                    <div class="num"><?= e(setting('card_number', '6037-XXXX-XXXX-XXXX')) ?></div>
                    <div class="name">به نام: <?= e(setting('card_holder_name', 'فروشگاه من')) ?></div>
                </div>

                <button type="button" onclick="submitOrder()" class="btn btn-primary btn-block" style="margin-top:20px; padding:15px;">ثبت و پرداخت سفارش</button>
                <div id="msg" style="color:#ef4444; text-align:center; margin-top:10px; font-weight:bold;"></div>
            </form>
        </div>

        <!-- خلاصه سفارش -->
        <div class="checkout-card">
            <h3>خلاصه سفارش</h3>
            <?php 
                $subtotal = cart_total();
                $shipping = $subtotal >= (int)setting('free_shipping_min', 2000000) ? 0 : (int)setting('shipping_cost', 50000);
                $total = $subtotal + $shipping;
                $items = cart();
                foreach($items as $item):
            ?>
                <div class="summary-item">
                    <span><?= e($item['name']) ?> (×<?= $item['qty'] ?>)</span>
                    <span><?= fmt($item['unit_price'] * $item['qty']) ?> ت</span>
                </div>
            <?php endforeach; ?>
            
            <div class="summary-total">
                <span>هزینه ارسال:</span>
                <span><?= $shipping ? fmt($shipping).' ت' : 'رایگان' ?></span>
            </div>
            <div class="summary-total" style="border:none; padding-top:0; margin-top:5px;">
                <span>مبلغ کل:</span>
                <span><?= fmt($total) ?> تومان</span>
            </div>
        </div>
    </div>
</div>

<script>
    // تنظیمات نقشه
    let map = L.map('map').setView([29.5918, 52.5837], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 19}).addTo(map);
    let marker = null;
    let selectedLat = null;
    let selectedLng = null;

    map.on('click', function(e) {
        selectedLat = e.latlng.lat;
        selectedLng = e.latlng.lng;
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
        document.getElementById('mapCoords').innerHTML = `✅ موقعیت انتخاب شد: ${selectedLat.toFixed(5)}, ${selectedLng.toFixed(5)}`;
        document.getElementById('mapCoords').style.color = '#10b981';
    });

    // انتخاب روش پرداخت
    const payOptions = document.querySelectorAll('.payment-option');
    const cardInfo = document.getElementById('cardInfo');

    payOptions.forEach(option => {
        option.addEventListener('click', () => {
            payOptions.forEach(o => o.classList.remove('active'));
            option.classList.add('active');
            
            // اگر کارت به کارت انتخاب شد، اطلاعات کارت را نشان بده
            const selectedPay = option.querySelector('input').value;
            if (selectedPay === 'card') {
                cardInfo.classList.add('show');
            } else {
                cardInfo.classList.remove('show');
            }
        });
    });

    async function submitOrder() {
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        const payMethod = document.querySelector('input[name="pay"]:checked').value;
        const msg = document.getElementById('msg');
        const btn = event.target;

        if (phone.length !== 11) { msg.textContent = 'شماره تماس معتبر نیست'; return; }
        if (address.length < 10) { msg.textContent = 'آدرس را کامل وارد کنید'; return; }

        btn.textContent = 'در حال ثبت...';
        btn.disabled = true;

        try {
            const res = await fetch('api.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'place_order',
                    phone: phone,
                    address: address,
                    lat: selectedLat || '',
                    lng: selectedLng || '',
                    pay: payMethod,
                    csrf: '<?= csrf() ?>'
                })
            }).then(r => r.json());

            if (res.ok) {
                if (payMethod === 'gateway') {
                    alert('در حال انتقال به درگاه پرداخت... (در حالت تست به پروفایل هدایت می‌شوید)');
                    window.location.href = 'profile.php?tab=orders';
                } else {
                    alert('✅ سفارش شما ثبت شد.\nلطفاً مبلغ را به کارت زیر واریز کنید:\n\n<?= e(setting('card_number', '6037-XXXX-XXXX-XXXX')) ?>\nبه نام: <?= e(setting('card_holder_name', 'فروشگاه من')) ?>');
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