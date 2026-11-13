<?php
require_once 'config.php';
$page_title = 'سبد خرید';
include 'includes/header.php';
?>

<div class="container" style="padding-top:30px; max-width:900px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px;">🛒 سبد خرید</h1>
    <div id="cartItems" style="background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow);">
        <div style="text-align:center; padding:30px;">در حال بارگذاری...</div>
    </div>
    <div id="cartFooter" style="margin-top:20px; display:none;"></div>
</div>

<script>
async function renderCart() {
    const res = await fetch('api.php', { method: 'POST', body: new URLSearchParams({action: 'get_cart', csrf: '<?= csrf() ?>'}) }).then(r => r.json());
    const box = document.getElementById('cartItems');
    const footer = document.getElementById('cartFooter');
    if (!res.items.length) { box.innerHTML = '<div style="text-align:center; padding:30px;">سبد خرید خالی است.</div>'; footer.style.display='none'; return; }
    
    box.innerHTML = res.items.map(i => `
        <div style="display:flex; align-items:center; gap:15px; border-bottom:1px solid #eee; padding:15px 0;">
            <div style="width:70px; height:70px; background:#f5f3ff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:30px; overflow:hidden;">
                ${i.image ? '<img src="'+i.image+'" style="width:100%;height:100%;object-fit:cover">' : '📦'}
            </div>
            <div style="flex:1;">
                <h4 style="margin:0;">${i.name}</h4>
                <div style="font-size:13px; color:var(--muted); margin-top:5px;">قیمت واحد: ${Number(i.unit_price).toLocaleString('fa-IR')} تومان</div>
                <div style="font-size:12px; color:#ef4444; margin-top:5px;">${i.attrs_text ? 'ویژگی‌ها: ' + i.attrs_text : ''}</div>
            </div>
            <div style="display:flex; align-items:center; gap:10px; background:#f1f5f9; padding:5px 10px; border-radius:10px;">
                <button onclick="changeQty('${i.key}','inc')" style="border:none; background:#fff; width:30px; height:30px; border-radius:8px; cursor:pointer; font-weight:bold;">+</button>
                <span style="font-weight:bold;">${Number(i.qty).toLocaleString('fa-IR')}</span>
                <button onclick="changeQty('${i.key}','dec')" style="border:none; background:#fff; width:30px; height:30px; border-radius:8px; cursor:pointer; font-weight:bold;">−</button>
            </div>
            <div style="font-weight:bold; width:100px; text-align:left;">${Number(i.unit_price * i.qty).toLocaleString('fa-IR')} ت</div>
            <button onclick="changeQty('${i.key}','del')" style="border:none; background:none; color:#ef4444; cursor:pointer; font-size:18px;">🗑</button>
        </div>
    `).join('');
    footer.style.display = 'block';
    footer.innerHTML = `
        <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:bold; background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow);">
            <span>جمع کل:</span><span style="color:var(--primary-d);">${Number(res.total).toLocaleString('fa-IR')} تومان</span>
        </div>
        <a href="checkout.php" class="btn btn-primary" style="display:block; text-align:center; margin-top:15px; text-decoration:none; padding:15px;">ادامه فرآیند خرید ←</a>
    `;
}

async function changeQty(key, act) {
    const res = await fetch('api.php', { method: 'POST', body: new URLSearchParams({action: 'cart_update', key, act, csrf: '<?= csrf() ?>'}) }).then(r => r.json());
    if (res.ok) renderCart(); else alert(res.error || 'خطا');
}
renderCart();
</script>

<?php include 'includes/footer.php'; ?>