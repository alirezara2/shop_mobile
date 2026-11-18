<?php
require_once 'config.php';
$page_title = 'سبد خرید';
include 'includes/header.php';
?>

<div class="container" style="padding-top:30px; max-width:900px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px;">🛒 سبد خرید</h1>
    
    <div id="cartItems" style="background:#fff; padding:20px; border-radius:16px; box-shadow:var(--shadow);">
        <div style="text-align:center; padding:30px; color:var(--muted);">در حال بارگذاری...</div>
    </div>
    
    <div id="cartFooter" style="margin-top:20px; display:none;"></div>
</div>

<script>
async function renderCart() {
    const box = document.getElementById('cartItems');
    const footer = document.getElementById('cartFooter');

    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'get_cart',
                csrf: '<?= csrf() ?>'
            })
        });

        const text = await response.text();
        let res;
        try {
            res = JSON.parse(text);
        } catch (e) {
            console.error('پاسخ نامعتبر از سرور:', text);
            box.innerHTML = `<div style="text-align:center; padding:30px; color:#ef4444;">
                خطا در دریافت اطلاعات از سرور.<br>
                <small style="color:#64748b; font-size:12px;">(پاسخ سرور: ${text.substring(0, 200)}...)</small>
            </div>`;
            return;
        }

        if (!res.items || !Array.isArray(res.items)) {
            box.innerHTML = '<div style="text-align:center; padding:30px; color:#ef4444;">خطا در پردازش داده‌ها.</div>';
            return;
        }

        if (res.items.length === 0) {
            box.innerHTML = '<div style="text-align:center; padding:30px; color:var(--muted);">سبد خرید شما خالی است.</div>';
            footer.style.display = 'none';
            return;
        }

        box.innerHTML = res.items.map(i => `
            <div style="display:flex; align-items:center; gap:15px; border-bottom:1px solid #eee; padding:15px 0;">
                <div style="width:70px; height:70px; background:#f5f3ff; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:30px; overflow:hidden;">
                    ${i.image ? '<img src="'+i.image+'" style="width:100%;height:100%;object-fit:cover">' : '📦'}
                </div>
                <div style="flex:1;">
                    <h4 style="margin:0;">${i.name}</h4>
                    <div style="font-size:13px; color:var(--muted); margin-top:5px;">قیمت واحد: ${Number(i.unit_price).toLocaleString('fa-IR')} تومان</div>
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
                <span>جمع کل:</span>
                <span style="color:var(--primary-d);">${Number(res.total).toLocaleString('fa-IR')} تومان</span>
            </div>
            <a href="checkout.php" class="btn btn-primary" style="display:block; text-align:center; margin-top:15px; text-decoration:none; padding:15px;">ادامه فرآیند خرید ←</a>
        `;

        // به‌روزرسانی تعداد سبد در هدر
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            cartCount.textContent = res.items.reduce((sum, item) => sum + item.qty, 0).toLocaleString('fa-IR');
        }

    } catch (err) {
        console.error('خطای شبکه:', err);
        box.innerHTML = '<div style="text-align:center; padding:30px; color:#ef4444;">خطا در ارتباط با سرور. لطفاً کنسول مرورگر (F12) را بررسی کنید.</div>';
    }
}

async function changeQty(key, act) {
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'cart_update',
                key: key,
                act: act,
                csrf: '<?= csrf() ?>'
            })
        });
        const res = await response.json();
        
        if (res.ok) {
            renderCart();
        } else {
            alert(res.error || 'خطا در تغییر تعداد');
        }
    } catch (err) {
        alert('خطا در ارتباط با سرور');
    }
}

renderCart();
</script>

<?php include 'includes/footer.php'; ?>