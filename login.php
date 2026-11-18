<?php
require_once 'config.php';

if (current_user()) redirect('profile.php');

$page_title = 'ورود / ثبت‌نام';
include 'includes/header.php';
?>

<style>
    .auth-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: linear-gradient(135deg, rgba(124,58,237,.05), rgba(236,72,153,.05)); }
    .auth-card { background: #fff; border-radius: 24px; padding: 40px 30px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(124,58,237,.15); border: 1px solid var(--border); position: relative; }
    .auth-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: var(--grad); }
    .auth-logo { text-align: center; margin-bottom: 24px; }
    .auth-logo a { font-size: 24px; font-weight: 900; color: var(--primary); text-decoration: none; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; }
    .form-group input { width: 100%; padding: 14px 16px; border-radius: 12px; border: 1.5px solid var(--border); background: #f8fafc; font-family: inherit; font-size: 15px; outline: none; }
    .btn-gold { background: var(--grad); color: #fff; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 15px; width: 100%; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(124,58,237,.3); }
    .demo-box-alert { background: #fffbeb; border: 2px dashed #f59e0b; color: #92400e; padding: 15px; border-radius: 12px; text-align: center; margin-bottom: 20px; font-weight: 800; font-size: 14px; }
    .demo-box-alert strong { font-size: 24px; letter-spacing: 6px; font-family: monospace; display: block; margin-top: 5px; }
    .otp-inputs { display: flex; gap: 8px; justify-content: center; direction: ltr; margin: 20px 0; }
    .otp-input { width: 50px; height: 60px; text-align: center; font-size: 24px; font-weight: 900; border: 2px solid var(--border); border-radius: 12px; background: #f8fafc; outline: none; }
    .demo-login-box { background: #f0f4f0; border-radius: 8px; padding: 16px; margin-top: 20px; text-align: center; }
    .btn-demo { background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 10px; width: 100%; cursor: pointer; font-weight: bold; }
</style>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="index.php">🛍️ <?= e(setting('site_name')) ?></a>
        </div>

        <!-- مرحله ۱: دریافت شماره -->
        <div id="step-phone">
            <h2 style="text-align:center; margin-bottom:20px;">ورود / ثبت‌نام</h2>
            <form id="phoneForm">
                <!-- این خط حیاتی است: action را به فرم اضافه کردیم -->
                <input type="hidden" name="action" value="otp_request">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" name="name" placeholder="مثلاً علی رضایی" required>
                </div>
                <div class="form-group">
                    <label>شماره موبایل</label>
                    <input type="tel" name="phone" placeholder="09121234567" maxlength="11" dir="ltr" required pattern="09[0-9]{9}">
                </div>
                <button type="submit" class="btn-gold" id="sendBtn">دریافت کد تایید</button>
            </form>

            <!-- باکس دمو -->
            <div class="demo-login-box">
                <h4 style="margin-bottom:10px;">🔑 ورود سریع (دمو)</h4>
                <button type="button" class="btn-demo" id="demoLoginBtn">ورود با حساب دمو</button>
            </div>
        </div>

        <!-- مرحله ۲: نمایش کد دمو و دریافت کد -->
        <div id="step-otp" style="display:none;">
            <h2 style="text-align:center; margin-bottom:10px;">کد تایید</h2>
            <p style="text-align:center; font-size:13px; margin-bottom:20px;">کد ارسال شده به <span id="showPhone" dir="ltr" style="font-weight:bold;"></span></p>
            
            <!-- باکس زرد کد دمو -->
            <div id="demoCodeBox" class="demo-box-alert" style="display:none;">
                ⚠️ حالت دمو - کد تایید شما:
                <strong id="demoCodeText"></strong>
            </div>

            <form id="otpForm">
                <!-- این خط حیاتی است: action را به فرم اضافه کردیم -->
                <input type="hidden" name="action" value="otp_verify">
                <input type="hidden" name="csrf" value="<?= csrf() ?>">
                <input type="hidden" name="phone" id="otpPhone">
                <input type="hidden" name="code" id="otpHidden">
                <div class="otp-inputs">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input class="otp-input" type="text" inputmode="numeric" maxlength="1" pattern="[0-9]">
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn-gold" id="verifyBtn">تایید و ورود</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('input[name="csrf"]').value;

    // --- ارسال شماره و دریافت کد ---
    document.getElementById('phoneForm').onsubmit = async (e) => {
        e.preventDefault();
        const btn = document.getElementById('sendBtn');
        btn.textContent = 'در حال ارسال...';
        btn.disabled = true;

        const formData = new FormData(e.target);

        try {
            const res = await fetch('api.php', {
                method: 'POST',
                body: formData
            }).then(r => r.json());

            if (res.ok) {
                document.getElementById('step-phone').style.display = 'none';
                document.getElementById('step-otp').style.display = 'block';
                document.getElementById('showPhone').textContent = formData.get('phone');
                document.getElementById('otpPhone').value = formData.get('phone');

                if (res.demo_code) {
                    document.getElementById('demoCodeBox').style.display = 'block';
                    document.getElementById('demoCodeText').textContent = res.demo_code;
                }
            } else {
                alert(res.error);
                btn.textContent = 'دریافت کد تایید';
                btn.disabled = false;
            }
        } catch (err) {
            alert('خطا در ارتباط');
            btn.textContent = 'دریافت کد تایید';
            btn.disabled = false;
        }
    };

    // --- دکمه دمو ---
    document.getElementById('demoLoginBtn').onclick = async () => {
        const btn = document.getElementById('demoLoginBtn');
        btn.textContent = 'در حال ورود...';
        btn.disabled = true;

        const demoPhone = '09120000000';
        const demoName = 'کاربر دمو';

        try {
            const res1 = await fetch('api.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'otp_request', phone: demoPhone, name: demoName, csrf })
            }).then(r => r.json());

            if (res1.ok && res1.demo_code) {
                const res2 = await fetch('api.php', {
                    method: 'POST',
                    body: new URLSearchParams({ action: 'otp_verify', phone: demoPhone, code: res1.demo_code, csrf })
                }).then(r => r.json());

                if (res2.ok) {
                    window.location.href = 'profile.php';
                } else {
                    alert(res2.error || 'خطا در تایید کد');
                    btn.textContent = 'ورود با حساب دمو';
                    btn.disabled = false;
                }
            } else {
                alert(res1.error || 'خطا در ارسال کد');
                btn.textContent = 'ورود با حساب دمو';
                btn.disabled = false;
            }
        } catch (err) {
            alert('خطا در ارتباط');
            btn.textContent = 'ورود با حساب دمو';
            btn.disabled = false;
        }
    };

    // --- فرم کد تایید ---
    document.getElementById('otpForm').onsubmit = async (e) => {
        e.preventDefault();
        const inputs = document.querySelectorAll('.otp-input');
        const hidden = document.getElementById('otpHidden');
        
        hidden.value = Array.from(inputs).map(i => i.value).join('');
        if (hidden.value.length < 6) {
            alert('کد ۶ رقمی را کامل وارد کنید');
            return;
        }

        const btn = document.getElementById('verifyBtn');
        btn.textContent = 'در حال بررسی...';
        btn.disabled = true;

        try {
            const res = await fetch('api.php', {
                method: 'POST',
                body: new FormData(e.target)
            }).then(r => r.json());

            if (res.ok) {
                window.location.href = 'profile.php';
            } else {
                alert(res.error || 'کد صحیح نیست');
                btn.textContent = 'تایید و ورود';
                btn.disabled = false;
            }
        } catch (err) {
            alert('خطا در ارتباط');
            btn.textContent = 'تایید و ورود';
            btn.disabled = false;
        }
    };

    // --- مدیریت ورودی‌های OTP ---
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((inp, i) => {
        inp.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 1);
            if (e.target.value && i < otpInputs.length - 1) otpInputs[i + 1].focus();
        });
        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !e.target.value && i > 0) otpInputs[i - 1].focus();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>