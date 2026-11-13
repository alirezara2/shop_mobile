// ==========================================
// تنظیمات اولیه
// ==========================================
const BASE = document.body.dataset.base || '/';
const CSRF = document.body.dataset.csrf || '';

document.addEventListener('DOMContentLoaded', () => {

    // ==========================================
    // ۰. حالت شب و روز (Dark Mode)
    // ==========================================
    const themeBtn = document.getElementById('themeBtn');

    // خواندن تم ذخیره شده از حافظه مرورگر
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);

    // تغییر آیکون دکمه بر اساس تم فعلی
    if (themeBtn) {
        themeBtn.textContent = currentTheme === 'dark' ? '☀️' : '🌙';

        themeBtn.addEventListener('click', () => {
            const now = document.documentElement.getAttribute('data-theme');
            const nextTheme = now === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', nextTheme);
            localStorage.setItem('theme', nextTheme);
            themeBtn.textContent = nextTheme === 'dark' ? '☀️' : '🌙';
        });
    }

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
            if (window.innerWidth <= 900 && closeMenuBtn) closeMenuBtn.style.display = 'block';
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
    // ۳. قابلیت نصب اپلیکیشن (Add to Home Screen)
    // ==========================================
    let deferredPrompt;
    const installBtn = document.getElementById('installAppBtn');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (installBtn) installBtn.style.display = 'block';
    });

    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                installBtn.style.display = 'none';
            } else {
                alert('برای نصب، از منوی مرورگر خود گزینه "Add to Home screen" را انتخاب کنید.');
            }
        });
    }

    // ==========================================
    // ۴. اسلایدر (با قابلیت کشیدن)
    // ==========================================
    const slider = document.getElementById('slider');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    let currentIndex = 0;
    let startX = 0;
    let isDragging = false;
    let autoPlayInterval;

    if (slider && slides.length > 0) {
        function goToSlide(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;

            currentIndex = index;
            slides.forEach((slide, i) => slide.classList.toggle('active', i === currentIndex));
            dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
        }

        function startAutoPlay() {
            clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(() => goToSlide(currentIndex + 1), 5000);
        }

        startAutoPlay();

        slider.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            clearInterval(autoPlayInterval);
        }, { passive: true });

        slider.addEventListener('touchend', (e) => {
            if (!isDragging) return;
            isDragging = false;
            const endX = e.changedTouches[0].clientX;
            const diff = startX - endX;
            if (diff > 50) goToSlide(currentIndex + 1);
            else if (diff < -50) goToSlide(currentIndex - 1);
            startAutoPlay();
        });

        slider.addEventListener('mousedown', (e) => {
            startX = e.clientX;
            isDragging = true;
            clearInterval(autoPlayInterval);
            e.preventDefault();
        });

        slider.addEventListener('mouseup', (e) => {
            if (!isDragging) return;
            isDragging = false;
            const endX = e.clientX;
            const diff = startX - endX;
            if (diff > 50) goToSlide(currentIndex + 1);
            else if (diff < -50) goToSlide(currentIndex - 1);
            startAutoPlay();
        });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
                startAutoPlay();
            });
        });
    }

    // ==========================================
    // ۵. دکمه افزودن به سبد خرید
    // ==========================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-cart');
        if (!btn) return;
        const pid = btn.dataset.pid;
        if (!pid) return;

        const card = btn.closest('.product-card');
        const attrs = {};
        if (card) {
            card.querySelectorAll('.attr-select').forEach(s => {
                if (s.value) attrs[s.dataset.attrName] = s.options[s.selectedIndex].text;
            });
        }

        const originalText = btn.textContent;
        btn.textContent = 'در حال افزودن...'; btn.disabled = true;

        try {
            const response = await fetch(BASE + 'api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'add_to_cart', pid: pid, attrs: JSON.stringify(attrs), csrf: CSRF })
            });
            const res = await response.json();
            if (res.ok) {
                alert('✅ محصول به سبد خرید اضافه شد');
                const countEl = document.getElementById('cartCount');
                if (countEl) countEl.textContent = Number(res.cart_qty).toLocaleString('fa-IR');
            } else { alert(res.error || 'خطا در افزودن به سبد'); }
        } catch (err) { alert('خطا در ارتباط با سرور'); }
        finally { btn.textContent = originalText; btn.disabled = false; }
    });

    // ==========================================
    // ۶. دکمه علاقه‌مندی‌ها (قلب)
    // ==========================================
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.wish-btn');
        if (!btn) return;

        e.preventDefault();
        const pid = btn.dataset.pid;
        if (!pid) return;

        try {
            const response = await fetch(BASE + 'api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'toggle_wishlist', pid: pid, csrf: CSRF })
            });

            const res = await response.json();

            if (res.ok) {
                if (res.in_wish) {
                    btn.classList.add('active');
                    btn.innerHTML = '♥';
                } else {
                    btn.classList.remove('active');
                    btn.innerHTML = '♡';
                }

                const wishCountEl = document.getElementById('wishCount');
                if (wishCountEl) wishCountEl.textContent = Number(res.wish_count).toLocaleString('fa-IR');

                if (window.location.search.includes('tab=wishlist')) {
                    setTimeout(() => window.location.reload(), 500);
                }
            } else {
                alert(res.error || 'ابتدا وارد حساب کاربری شوید');
                if (res.error && res.error.includes('وارد شوید')) {
                    window.location.href = 'login.php';
                }
            }
        } catch (err) {
            alert('خطا در ارتباط با سرور');
        }
    });

});
// ==========================================
// مدیریت Ajax برای ویژگی‌ها (در پنل ادمین)
// ==========================================
document.addEventListener('submit', async (e) => {
    // فقط فرم‌هایی که در بخش ویژگی‌ها هستند
    const form = e.target;
    const isAttrForm = form.querySelector('input[name="action"]')?.value.includes('attribute');
    if (!isAttrForm) return;

    e.preventDefault();

    // پیدا کردن ID محصول از والد
    const editRow = form.closest('tr[id^="editForm_"]');
    if (!editRow) return;
    const pid = editRow.id.replace('editForm_', '');

    const formData = new FormData(form);
    formData.append('update_attr_html', '1'); // برای تشخیص در API

    try {
        // 1. ارسال فرم برای ذخیره تغییرات
        const response = await fetch('../api.php', {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.ok) {
            // 2. دریافت HTML جدید بخش ویژگی‌ها
            const htmlRes = await fetch('../api.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'update_attribute_html',
                    pid: pid,
                    csrf: document.querySelector('input[name="csrf"]').value
                })
            }).then(r => r.json());

            if (htmlRes.ok) {
                // 3. جایگزینی HTML جدید در لیست ویژگی‌ها
                const attrList = document.getElementById('attr-list-' + pid);
                if (attrList) {
                    attrList.innerHTML = htmlRes.html;
                }
            } else {
                alert(htmlRes.error || 'خطا در دریافت لیست ویژگی‌ها');
            }
        } else {
            alert(res.error || 'خطا در ذخیره ویژگی');
        }
    } catch (err) {
        console.error(err);
        alert('خطا در ارتباط با سرور');
    }
});