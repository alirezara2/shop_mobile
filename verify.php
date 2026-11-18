<?php
require_once 'config.php';

$order_code = $_GET['order'] ?? '';
$status = $_GET['Status'] ?? '';
$authority = $_GET['Authority'] ?? '';

$s = $pdo->prepare("SELECT * FROM orders WHERE order_code=?");
$s->execute([$order_code]);
$order = $s->fetch();

if (!$order) die('سفارش یافت نشد');

if ($status === 'OK' && !empty(ZARINPAL_MERCHANT)) {
    $data = [
        'merchant_id' => ZARINPAL_MERCHANT,
        'amount' => $order['total'] * 10,
        'authority' => $authority
    ];
    $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($data), CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
    $res = json_decode(curl_exec($ch), true);

    if (!empty($res['data']['ref_id'])) {
        $pdo->prepare("UPDATE orders SET status='paid' WHERE id=?")->execute([$order['id']]);
        set_cart([]);
        echo '<div style="text-align:center; padding:50px; font-family:Tahoma;"><h2 style="color:#10b981;">✅ پرداخت موفق</h2><p>کد پیگیری بانکی: '.$res['data']['ref_id'].'</p><a href="'.url('profile.php?tab=orders').'">مشاهده سفارش‌ها</a></div>';
        exit;
    }
}

// اگر درگاه تنظیم نشده باشد یا ناموفق باشد
$pdo->prepare("UPDATE orders SET status='cancelled' WHERE id=?")->execute([$order['id']]);
echo '<div style="text-align:center; padding:50px; font-family:Tahoma;"><h2 style="color:#ef4444;">❌ پرداخت ناموفق</h2><a href="'.url('cart.php').'">بازگشت به سبد</a></div>';