<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!in_array($action, ['get_cart', 'logout', 'update_attribute_html'])) {
    check_csrf();
}

switch($action) {

    case 'update_attribute_html': {
        $pid = (int)($_POST['pid'] ?? 0);
        if (!$pid) j(['ok'=>false, 'error'=>'شناسه محصول نامعتبر']);
        
        $attrs = $pdo->prepare("SELECT * FROM product_attributes WHERE product_id=?");
        $attrs->execute([$pid]);
        $attrs = $attrs->fetchAll();
        
        ob_start();
        foreach($attrs as $a) {
            $vals = $pdo->prepare("SELECT * FROM attribute_values WHERE attribute_id=?");
            $vals->execute([$a['id']]);
            $vals = $vals->fetchAll();
            ?>
            <li style="background:#fff; border:1px solid #ddd; padding:10px; border-radius:8px; margin-bottom:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <b><?= e($a['name']) ?></b>
                    <form method="post" onsubmit="return confirm('حذف کل ویژگی؟');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_attribute">
                        <input type="hidden" name="id" value="<?= $a['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= csrf() ?>">
                        <button class="btn btn-sm btn-danger">حذف ویژگی</button>
                    </form>
                </div>
                <div style="margin-top:10px;">
                    <ul style="list-style:none; padding:0; display:flex; flex-wrap:wrap; gap:5px;">
                        <?php foreach($vals as $v): ?>
                            <li style="background:var(--primary-l); color:var(--primary-d); padding:5px 10px; border-radius:20px; font-size:12px; display:flex; align-items:center; gap:5px;">
                                <?= e($v['value']) ?> (<?= fmt($v['price']) ?> ت)
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_attribute_value">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <input type="hidden" name="csrf" value="<?= csrf() ?>">
                                    <button style="background:none;border:none;color:#ef4444;cursor:pointer;">×</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="post" style="display:flex; gap:5px; margin-top:10px;">
                        <input type="hidden" name="action" value="add_attribute_value">
                        <input type="hidden" name="attribute_id" value="<?= $a['id'] ?>">
                        <input type="hidden" name="csrf" value="<?= csrf() ?>">
                        <input name="value" placeholder="مثلاً قرمز" required style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                        <input name="value_price" placeholder="قیمت (تومان)" type="number" value="0" style="flex:1; padding:8px; border:1px solid #ddd; border-radius:6px;">
                        <button class="btn btn-sm btn-primary">+ افزودن مقدار</button>
                    </form>
                </div>
            </li>
            <?php
        }
        $html = ob_get_clean();
        j(['ok'=>true, 'html'=>$html]);
    }

    case 'add_attribute': {
        $pid = (int)$_POST['product_id']; $name = trim($_POST['attr_name']);
        if ($name && $pid) { $pdo->prepare("INSERT INTO product_attributes (product_id, name) VALUES (?,?)")->execute([$pid, $name]); }
        j(['ok'=>true, 'success'=>true]);
    }
    case 'delete_attribute': {
        $pdo->prepare("DELETE FROM product_attributes WHERE id=?")->execute([(int)$_POST['id']]);
        j(['ok'=>true, 'success'=>true]);
    }
    case 'add_attribute_value': {
        $aid = (int)$_POST['attribute_id']; $val = trim($_POST['value']); $price = (int)$_POST['value_price'];
        if ($val && $aid) { $pdo->prepare("INSERT INTO attribute_values (attribute_id, value, price) VALUES (?,?,?)")->execute([$aid, $val, $price]); }
        j(['ok'=>true, 'success'=>true]);
    }
    case 'delete_attribute_value': {
        $pdo->prepare("DELETE FROM attribute_values WHERE id=?")->execute([(int)$_POST['id']]);
        j(['ok'=>true, 'success'=>true]);
    }

    case 'logout': {
        if (isset($_GET['csrf'])) {
            if (!hash_equals($_SESSION['csrf'] ?? '', $_GET['csrf'])) {
                die('توکن امنیتی نامعتبر است.');
            }
        }
        session_destroy();
        header('Location: index.php');
        exit;
    }

    case 'otp_request': {
        if (!rate_limit_ok(ip(), MAX_OTP_PER_HOUR, 60, 'otp'))
            j(['ok'=>false, 'error'=>'تعداد درخواست بیش از حد. کمی بعد امتحان کنید.']);
        $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? '');
        if (strlen($name) < 3) j(['ok'=>false, 'error'=>'نام معتبر نیست']);
        if (!preg_match('/^09\d{9}$/', $phone)) j(['ok'=>false, 'error'=>'شماره معتبر نیست']);
        $code = str_pad((string)random_int(0,999999), 6, '0', STR_PAD_LEFT);
        $pdo->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?,?, DATE_ADD(NOW(), INTERVAL ".OTP_EXPIRE_MINUTES." MINUTE))")->execute([$phone, $code]);
        $sms = send_otp_sms($phone, $code);
        rate_limit_log(ip(), 'otp', false);
        $_SESSION['pending_name'] = $name; $_SESSION['login_phone'] = $phone;
        j(['ok'=>true, 'demo_code'=> $sms['demo'] ? $code : null]);
    }

    case 'otp_verify': {
        $phone = trim($_POST['phone'] ?? ''); $code  = trim($_POST['code'] ?? '');
        $s = $pdo->prepare("SELECT * FROM otp_codes WHERE phone=? AND used=0 AND expires_at>NOW() ORDER BY id DESC LIMIT 1");
        $s->execute([$phone]); $otp = $s->fetch();
        if (!$otp) j(['ok'=>false, 'error'=>'کد منقضی شده است']);
        if ($otp['attempts'] >= OTP_MAX_TRIES) j(['ok'=>false, 'error'=>'تلاش بیش از حد. کد جدید بگیرید.']);
        if ($otp['code'] !== $code) { $pdo->prepare("UPDATE otp_codes SET attempts=attempts+1 WHERE id=?")->execute([$otp['id']]); j(['ok'=>false, 'error'=>'کد صحیح نیست']); }
        $pdo->prepare("UPDATE otp_codes SET used=1 WHERE id=?")->execute([$otp['id']]);
        $name = $_SESSION['pending_name'] ?? 'کاربر';
        $s = $pdo->prepare("SELECT id FROM users WHERE phone=?"); $s->execute([$phone]);
        if ($u = $s->fetch()) $uid = $u['id'];
        else { $pdo->prepare("INSERT INTO users (name,phone) VALUES (?,?)")->execute([$name,$phone]); $uid = $pdo->lastInsertId(); }
        $_SESSION['user_id'] = $uid;
        unset($_SESSION['pending_name']); unset($_SESSION['login_phone']);
        j(['ok'=>true, 'success'=>true]);
    }

    case 'complete_verification': {
        if (empty($_SESSION['user_id'])) j(['ok'=>false, 'error'=>'ابتدا وارد شوید']);
        $national = trim($_POST['national_code'] ?? ''); $postal = trim($_POST['postal_code'] ?? ''); $address = trim($_POST['address'] ?? '');
        if (strlen($national) !== 10) j(['ok'=>false, 'error'=>'کد ملی باید ۱۰ رقم باشد']);
        if (strlen($postal) !== 10) j(['ok'=>false, 'error'=>'کد پستی باید ۱۰ رقم باشد']);
        if (strlen($address) < 10) j(['ok'=>false, 'error'=>'آدرس را کامل وارد کنید']);
        $pdo->prepare("UPDATE users SET national_code=?, postal_code=?, address=?, is_verified=1 WHERE id=?")->execute([$national, $postal, $address, $_SESSION['user_id']]);
        j(['ok'=>true]);
    }

    case 'add_to_cart': {
        $pid = (int)($_POST['pid'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $attrs = json_decode($_POST['attrs'] ?? '{}', true) ?: [];
        $total_price = (int)($_POST['total_price'] ?? 0);
        
        $s = $pdo->prepare("SELECT * FROM products WHERE id=? AND active=1"); 
        $s->execute([$pid]); $p = $s->fetch();
        if (!$p) j(['ok'=>false, 'error'=>'محصول یافت نشد']);
        if ($p['stock'] <= 0) j(['ok'=>false, 'error'=>'این محصول موجود نیست']);

        // اگر قیمت کل از سمت کاربر ارسال شده باشد، از آن استفاده کن
        if ($total_price > 0) {
            $price = $total_price;
        } else {
            // در غیر این صورت، قیمت پایه را بررسی کن
            $price = (int)$p['discount_price'] > 0 ? (int)$p['discount_price'] : (int)$p['price'];
        }

        $attr_text = [];
        foreach($attrs as $name => $val) {
            $attr_text[] = "$name: $val";
        }
        
        $key = $p['id'] . '|' . md5(json_encode($attrs));
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
                'image' => $p['image'],
                'attrs_text' => implode(', ', $attr_text)
            ];
        }
        
        set_cart($cart);
        j(['ok'=>true, 'cart_qty'=>cart_qty()]);
    }

    case 'get_cart': {
        $items = []; $total = 0;
        foreach(cart() as $i){ $items[] = $i; $total += $i['unit_price'] * $i['qty']; }
        j(['ok'=>true, 'items'=>$items, 'total'=>$total]);
    }

    case 'cart_update': {
        $key = $_POST['key'] ?? ''; $act = $_POST['act'] ?? ''; $cart = cart();
        if (!isset($cart[$key])) j(['ok'=>false]);
        $s = $pdo->prepare("SELECT stock FROM products WHERE id=?"); $s->execute([$cart[$key]['product_id']]); $stock = $s->fetchColumn();
        if ($act === 'inc') { if ($cart[$key]['qty'] + 1 > $stock) j(['ok'=>false, 'error'=>'موجودی کافی نیست']); $cart[$key]['qty']++; }
        if ($act === 'dec') $cart[$key]['qty']--;
        if ($act === 'del' || $cart[$key]['qty'] <= 0) unset($cart[$key]);
        set_cart($cart);
        j(['ok'=>true, 'cart_qty'=>cart_qty()]);
    }

    case 'toggle_wishlist': {
        if (empty($_SESSION['user_id'])) j(['ok'=>false, 'error'=>'ابتدا وارد شوید']);
        $pid = (int)($_POST['pid'] ?? 0); $uid = $_SESSION['user_id'];
        if (!$pid) j(['ok'=>false, 'error'=>'محصول نامعتبر']);
        $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?"); $stmt->execute([$uid, $pid]); $wish = $stmt->fetch();
        if ($wish) { $pdo->prepare("DELETE FROM wishlist WHERE id = ?")->execute([$wish['id']]); $in_wish = false; }
        else { $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)")->execute([$uid, $pid]); $in_wish = true; }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?"); $stmt->execute([$uid]); $wish_count = $stmt->fetchColumn();
        j(['ok'=>true, 'in_wish'=>$in_wish, 'wish_count'=>$wish_count]);
    }

    case 'place_order': {
        if (empty($_SESSION['user_id'])) j(['ok'=>false,'error'=>'ابتدا وارد شوید']);
        if (!is_user_verified()) j(['ok'=>false,'error'=>'برای خرید باید احراز هویت کنید']);
        $cart = cart(); if (!$cart) j(['ok'=>false,'error'=>'سبد خالی است']);
        $phone = trim($_POST['phone'] ?? ''); $address = trim($_POST['address'] ?? '');
        $lat = $_POST['lat'] ?: null; $lng = $_POST['lng'] ?: null; $pay = $_POST['pay'] ?? 'gateway'; $ship = $_POST['ship'] ?? 'post';
        if (strlen($phone) !== 11) j(['ok'=>false,'error'=>'شماره تماس معتبر نیست']);
        if (strlen($address) < 10) j(['ok'=>false,'error'=>'آدرس کامل نیست']);
        $subtotal = cart_total();
        $shipping = $subtotal >= (int)setting('free_shipping_min', 2000000) ? 0 : (int)setting('shipping_cost', 50000);
        $total = $subtotal + $shipping;
        $code = 'ORD-' . date('ymd') . '-' . strtoupper(substr(md5(uniqid()),0,5));
        try {
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO orders (order_code, user_id, phone, address, lat, lng, ship_method, pay_method, subtotal, shipping, total) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$code, $_SESSION['user_id'], $phone, $address, $lat, $lng, $ship, $pay, $subtotal, $shipping, $total]);
            $oid = $pdo->lastInsertId();
            foreach($cart as $i){
                $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, qty) VALUES (?,?,?,?,?)")->execute([$oid, $i['product_id'], $i['name'], $i['unit_price'], $i['qty']]);
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id=?")->execute([$i['qty'], $i['product_id']]);
            }
            $pdo->commit();
        } catch(Exception $e){ $pdo->rollBack(); j(['ok'=>false, 'error'=>'خطا در ثبت سفارش']); }
        set_cart([]);
        if ($pay === 'gateway') $pdo->prepare("UPDATE orders SET status='paid' WHERE order_code=?")->execute([$code]);
        j(['ok'=>true, 'code'=>$code, 'redirect'=>url('profile.php?tab=orders')]);
    }

    default: j(['ok'=>false, 'error'=>'action نامعتبر']);
}