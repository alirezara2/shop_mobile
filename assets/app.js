// ==========================================
// تنظیمات اولیه
// ==========================================
const BASE = document.body.dataset.base || '/';
const CSRF = document.body.dataset.csrf || '';

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // ۱. منوی همبرگری موبایل
    // ==========================================
    const burgerBtn = document.getElementById('burgerBtn');
    const mainNav = document.getElementById('mainNav');
    const closeMenuBtn = document.getElementById('closeMenuBtn');

    if (burgerBtn && mainNav) {
        burgerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            mainNav.classList.add('open');
            if (window.innerWidth <= 900) closeMenuBtn.style.display = 'block';
        });
    }

    if (closeMenuBtn) {
        closeMenuBtn.addEventListener('click', () => {
            mainNav.classList.remove('open');
        });
    }

    // ==========================================
    // ۲. منوی کشویی دسته‌بندی در موبایل
    // ==========================================
    const dropToggle = document.getElementById('dropToggle');
    if (dropToggle) {
        dropToggle.addEventListener('click', (e) => {
            if (window.innerWidth <= 900) {
                e.preventDefault();
                dropToggle.closest('.dropdown').classList.toggle('open');
            }
        });
    }

    // ==========================================
    // ۳. اسلایدر صفحه اصلی
    // ==========================================
    const slides = document.querySelectorAll('.slide');
    const dots = document.getElementById('dots');

    if (slides.length > 0 && dots) {
        let idx = 0;

        slides.forEach((_, i) => {
            const d = document.createElement('div');
            d.className = 'dot' + (i === 0 ? ' active' : '');
            d.onclick = () => goSlide(i);
            dots.appendChild(d);
        });

        function goSlide(i) {
            idx = i;
            slides.forEach((s, si) => s.classList.toggle('active', si === i));
            document.querySelectorAll('.dot').forEach((d, di) => d.classList.toggle('active', di === i));
        }

        setInterval(() => goSlide((idx + 1) % slides.length), 5000);
    }

    // ==========================================
    // ۴. دکمه افزودن به سبد خرید
    // ==========================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-cart');
        if (!btn) return;

        const pid = btn.dataset.pid;
        if (!pid) return;

        const originalText = btn.textContent;
        btn.textContent = 'در حال افزودن...';
        btn.disabled = true;

        try {
            const response = await fetch(BASE + 'api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'add_to_cart', pid: pid, csrf: CSRF })
            });

            const text = await response.text();
            let res;
            try { res = JSON.parse(text); } catch (parseError) { throw new Error('خطای سرور'); }

            if (res.ok) {
                alert('✅ محصول به سبد خرید اضافه شد');
                const countEl = document.getElementById('cartCount');
                const mobileCountEl = document.getElementById('mobileCartCount');
                if (countEl) countEl.textContent = Number(res.cart_qty).toLocaleString('fa-IR');
                if (mobileCountEl) mobileCountEl.textContent = Number(res.cart_qty).toLocaleString('fa-IR');
            } else {
                alert(res.error || 'خطا در افزودن به سبد');
            }
        } catch (err) {
            alert('خطا در ارتباط با سرور');
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    });

});