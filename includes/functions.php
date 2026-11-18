<?php
if (basename($_SERVER['PHP_SELF']) == 'functions.php') {
    exit('دسترسی غیرمجاز');
}

// --- توابع کمکی ---
function fmt($n) { return number_format((int)$n); }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function j($d) { header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

// این خط مشکل را حل می‌کند
function ip() { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

function url($p='') { 
    return BASE . ltrim($p, '/'); 
}

function redirect($p) { 
    header('Location: ' . url($p)); 
    exit; 
}

// --- امنیت ---
function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function check_csrf() {
    $t = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $t)) j(['ok'=>false,'error'=>'توکن امنیتی نامعتبر']);
}

function rate_limit_ok($key, $max, $minutes, $kind='login') {
    global $pdo;
    $s = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE identifier=? AND kind=? AND ip=? AND success=0 AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $s->execute([$key, $kind, ip(), $minutes]);
    return $s->fetchColumn() < $max;
}

function rate_limit_log($key, $kind, $success) {
    global $pdo;
    $pdo->prepare("INSERT INTO login_attempts (identifier, kind, ip, success) VALUES (?,?,?,?)")
        ->execute([$key, $kind, ip(), $success?1:0]);
}

// --- کاربر و ادمین ---
function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    global $pdo;
    $s = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $s->execute([$_SESSION['user_id']]);
    return $s->fetch() ?: null;
}

function is_user_verified() {
    $u = current_user();
    return $u && !empty($u['is_verified']);
}

function is_admin() {
    return !empty($_SESSION['admin_id']);
}

function require_admin() {
    if (!is_admin()) redirect('admin.php');
}

// --- تنظیمات ---
function setting($k, $d=null) {
    global $pdo;
    static $cache = [];
    if (array_key_exists($k, $cache)) return $cache[$k];
    try {
        $s = $pdo->prepare("SELECT v FROM settings WHERE k=?");
        $s->execute([$k]);
        $v = $s->fetchColumn();
        return $cache[$k] = ($v === false ? $d : $v);
    } catch (Exception $e) { return $d; }
}

// --- سبد خرید ---
function cart() { return $_SESSION['cart'] ?? []; }
function set_cart($c) { $_SESSION['cart'] = $c; }
function cart_qty() { return array_sum(array_column(cart(), 'qty')); }
function cart_total() {
    $t = 0;
    foreach (cart() as $i) $t += $i['unit_price'] * $i['qty'];
    return $t;
}

// --- پیامک ---
function send_otp_sms($phone, $code) {
    if (empty(KAVENEGAR_KEY)) {
        return ['demo' => true, 'code' => $code];
    }
    $url = 'https://api.kavenegar.com/v1/'.KAVENEGAR_KEY.'/verify/lookup.json';
    $params = http_build_query([
        'receptor' => $phone,
        'token' => $code,
        'template' => KAVENEGAR_OTP_TEMPLATE
    ]);
    $ch = curl_init($url . '?' . $params);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
    $res = curl_exec($ch);
    curl_close($ch);
    return ['demo' => false, 'success' => $res !== false];
}

// --- آپلود عکس ---
function upload_image($file_input_name) {
    if (empty($_FILES[$file_input_name]['tmp_name'])) return null;
    $file = $_FILES[$file_input_name];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) return null;
    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $fname = 'img_' . time() . '_' . rand(100,999) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . $fname)) {
        return 'uploads/' . $fname;
    }
    return null;
}
?>
