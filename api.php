<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// به جز get_cart و logout، بقیه باید CSRF داشته باشند
if (!in_array($action, ['get_cart', 'logout'])) {
    check_csrf();
}

switch($action) {

    // ==========================================
    // خروج از حساب
    // ==========================================
    case 'logout': {
        // اگر توکن در URL هست، چک کن
        if (isset($_GET['csrf'])) {
            if (!hash_equals($_SESSION['csrf'] ?? '', $_GET['csrf'])) {
                die('توکن امنیتی نامعتبر است.');
            }
        }
        session_destroy();
        // هدایت به صفحه اصلی
        header('Location: index.php');
        exit;
    }

    // 1. ارسال کد OTP
    case 'otp_request': {
        if (!rate_limit_ok(ip(), MAX_OTP_PER_HOUR, 60, 'otp'))
            j(['ok'=>false, 'error'=>'تعداد درخواست بیش از حد. کمی بعد امتحان کنید.']);
            
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (strlen($name) < 3) j(['ok'=>false, 'error'=>'نام معتبر نیست']);
        if (!preg_match('/^09\d{9}$/', $phone)) j(['ok'=>false, 'error'=>'شماره معتبر نیست']);

        $code = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
        $pdo->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL ".OTP_EXPIRE_MINUTES." MINUTE))")
            ->execute([$phone, $code]);
            
        $sms = send_otp_sms($phone, $code);
        rate_limit_log(ip(), 'otp', false);
        
        $_SESSION['pending_name'] = $name;
        
        j(['ok'=>true, 'demo_code'=> $sms['demo'] ? $code : null]);
    }

    // 2. تایید کد OTP و ورود
    case 'otp_verify': {
        $phone = trim($_POST['phone'] ?? '');
        $code  = trim($_POST['code'] ?? '');
        
        $s = $pdo->prepare("SELECT * FROM otp_codes WHERE phone=? AND used=0 AND expires_at>NOW() ORDER BY id DESC LIMIT 1");
        $s->execute([$phone]); 
        $otp = $s->fetch();
        
        if (!$otp) j(['ok'=>false, 'error'=>'کد منقضی شده است']);
        if ($otp['attempts'] >= OTP_MAX_TRIES) j(['ok'=>false, 'error'=>'تلاش بیش از حد. کد جدید بگیرید.']);
        
        if ($otp['code'] !== $code) {
            $pdo->prepare("UPDATE otp_codes SET attempts=attempts+1 WHERE id=?")->execute([$otp['id']]);
            j(['ok'=>false, 'error'=>'کد صحیح نیست']);
        }
        
        $pdo->prepare("UPDATE otp_codes SET used=1 WHERE id=?")->execute([$otp['id']]);
        
        $name = $_SESSION['pending_name'] ?? 'کاربر';
        $s = $pdo->prepare("SELECT id FROM users WHERE phone=?"); 
        $s->execute([$phone]);
        
        if ($u = $s->fetch()) {
            $uid = $u['id'];
        } else {
            $pdo->prepare("INSERT INTO users (name,phone) VALUES (?,?)")->execute([$name,$phone]);
            $uid = $pdo->lastInsertId();
        }
        
        $_SESSION['user_id'] = $uid;
        unset($_SESSION['pending_name']);
        
        j(['ok'=>true, 'success'=>true]);
    }

    // 3. تکمیل احراز هویت
    case 'complete_verification': {
        if (empty($_SESSION['user_id'])) j(['ok'=>false, 'error'=>'ابتدا وارد شوید']);
        
        $national = trim($_POST['national_code'] ?? '');
        $postal = trim($_POST['postal_code'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if (strlen($national) !== 10) j(['ok'=>false, 'error'=>'کد ملی باید ۱۰ رقم باشد']);
        if (strlen($postal) !== 10) j(['ok'=>false, 'error'=>'کد پستی باید ۱۰ رقم باشد']);
        if (strlen($address) < 10) j(['ok'=>false, 'error'=>'آدرس را کامل وارد کنید']);
        
        $pdo->prepare("UPDATE users SET national_code=?, postal_code=?, address=?, is_verified=1 WHERE id=?")
            ->execute([$national, $postal, $address, $_SESSION['user_id']]);
            
        j(['ok'=>true]);
    }

    // 4. افزودن به سبد
    case 'add_to_cart': {
        $pid = (int)($_POST['pid'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        
        $s = $pdo->prepare("SELECT * FROM products WHERE id=? AND active=1"); 
        $s->execute([$pid]); 
        $p = $s->fetch();
        
        if (!$p) j(['ok'=>false, 'error'=>'محصول یافت نشد']);
        if ($p['stock'] <= 0) j(['ok'=>false, 'error'=>'این محصول موجود نیست']);

        $price = (int)$p['discount_price'] > 0 ? (int)$p['discount_price'] : (int)$p['price'];
        $key = $p['id'];
        $cart = cart();
        
        $curQty = isset($cart[$key]) ? $cart[$key]['qty'] : 0;
        if ($curQty + $qty > $p['stock']) j(['ok'=>false, 'error'=>'موجودی کافی نیست']);
        
        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'key' => $key,
                'product_id' => $p['id'],
                'unit_price' => $price,
                'qty' => $qty,
                'name' => $p['name'],
                'image' => $p['image']
            ];
        }
        
        set_cart($cart);
        j(['ok'=>true, 'cart_qty'=>cart_qty()]);
    }

    // 5. دریافت سبد
    case 'get_cart': {
        $items = []; 
        $total = 0;
        foreach(cart() as $i){
            $items[] = $i;
            $total += $i['unit_price'] * $i['qty'];
        }
        j(['ok'=>true, 'items'=>$items, 'total'=>$total]);
    }

    // 6. تغییر تعداد
    case 'cart_update': {
        $key = $_POST['key'] ?? ''; 
        $act = $_POST['act'] ?? '';
        $cart = cart();
        
        if (!isset($cart[$key])) j(['ok'=>false]);
        
        $s = $pdo->prepare("SELECT stock FROM products WHERE id=?"); 
        $s->execute([$cart[$key]['product_id']]); 
        $stock = $s->fetchColumn();
        
        if ($act === 'inc') {
            if ($cart[$key]['qty'] + 1 > $stock) j(['ok'=>false, 'error'=>'موجودی کافی نیست']);
            $cart[$key]['qty']++;
        }
        if ($act === 'dec') $cart[$key]['qty']--;
        if ($act === 'del' || $cart[$key]['qty'] <= 0) unset($cart[$key]);
        
        set_cart($cart);
        j(['ok'=>true, 'cart_qty'=>cart_qty()]);
    }

    // 7. ثبت سفارش نهایی
    case 'place_order': {
        if (empty($_SESSION['user_id'])) j(['ok'=>false,'error'=>'ابتدا وارد شوید']);
        if (!is_user_verified()) j(['ok'=>false,'error'=>'برای خرید باید احراز هویت کنید']);
        
        $cart = cart(); 
        if (!$cart) j(['ok'=>false,'error'=>'سبد خالی است']);
        
        $address = trim($_POST['address'] ?? '');
        if (strlen($address) < 10) j(['ok'=>false,'error'=>'آدرس کامل نیست']);
        
        $pay = $_POST['pay'] ?? 'gateway';
        $ship = $_POST['ship'] ?? 'post';
        
        $subtotal = cart_total();
        $shipping = $subtotal >= (int)setting('free_shipping_min', 2000000) ? 0 : (int)setting('shipping_cost', 50000);
        $total = $subtotal + $shipping;
        $code = 'ORD-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid()),0,5));

        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO orders (order_code, user_id, address, ship_method, pay_method, subtotal, shipping, total) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$code, $_SESSION['user_id'], $address, $ship, $pay, $subtotal, $shipping, $total]);
            $oid = $pdo->lastInsertId();
            
            foreach($cart as $i){
                $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty) VALUES (?,?,?,?,?)")
                    ->execute([$oid, $i['product_id'], $i['name'], $i['unit_price'], $i['qty']]);
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=?")->execute([$i['qty'], $i['product_id']]);
            }
            $pdo->commit();
        } catch(Exception $e){ 
            $pdo->rollBack(); 
            j(['ok'=>false, 'error'=>'خطا در ثبت سفارش']); 
        }

        set_cart([]);
        
        // برای تست، بدون درگاه، سفارش را پرداخت شده در نظر بگیر
        if ($pay === 'gateway') {
            $pdo->prepare("UPDATE orders SET status='paid' WHERE order_code=?")->execute([$code]);
            j(['ok'=>true, 'code'=>$code, 'redirect'=>url('profile.php?tab=orders')]);
        } else {
            j(['ok'=>true, 'code'=>$code, 'redirect'=>url('profile.php?tab=orders')]);
        }
    }

    default: j(['ok'=>false, 'error'=>'action نامعتبر']);
}