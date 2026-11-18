
# 📱 Mobile Shop | فروشگاه موبایل

A lightweight, custom-built e-commerce platform for mobile phones and accessories, developed with pure PHP (no frameworks) and MySQL. It features a complete shopping flow, a secure admin panel, OTP authentication, and a fully responsive design for both mobile and desktop.

یک پلتفرم فروشگاهی سبک و اختصاصی برای موبایل و لوازم جانبی، که با PHP خالص (بدون فریم‌ورک) و MySQL توسعه یافته است. این پروژه شامل فرآیند کامل خرید، پنل مدیریت امن، احراز هویت با کد یکبار مصرف (OTP) و طراحی کاملاً واکنش‌گرا (Responsive) برای موبایل و دسکتاپ است.

---

## ✨ Features | ویژگی‌ها

### 🛒 User Side | بخش کاربری
- **Product Browsing:** Dynamic homepage with featured, newest, and discounted mobile products.
  **مرور محصولات:** صفحه اصلی داینامیک با محصولات ویژه، جدیدترین‌ها و تخفیف‌دار.
- **Advanced Filtering:** Search, filter by category/brand, price range, and sorting.
  **فیلتر پیشرفته:** جستجو، فیلتر بر اساس دسته‌بندی، برند، محدوده قیمت و مرتب‌سازی.
- **OTP Authentication:** Passwordless login via a 6-digit SMS code (with a local testing/demo mode).
  **احراز هویت OTP:** ورود بدون رمز عبور با کد ۶ رقمی پیامکی (همراه با حالت تست/دمو برای لوکال).
- **User Dashboard:** View order history, track shipment status, and manage the shopping cart directly from the profile.
  **داشبورد کاربر:** مشاهده تاریخچه سفارش‌ها، پیگیری وضعیت مرسوله و مدیریت سبد خرید مستقیماً از پروفایل.
- **Identity Verification:** Users must submit national and postal codes before checkout.
  **احراز هویت:** کاربران باید قبل از پرداخت، کد ملی و کد پستی خود را ثبت کنند.
- **Responsive UI:** Mobile-first design with a hamburger menu and a sticky bottom navigation bar.
  **رابط کاربری واکنش‌گرا:** طراحی موبایل-محور همراه با منوی همبرگری و نوار ناوبری چسبان در پایین صفحه.

### 🛡️ Admin Panel | پنل مدیریت
- **Dashboard:** Quick overview of sales, orders, and registered users.
  **داشبورد:** نمای کلی سریع از فروش، سفارش‌ها و کاربران ثبت‌نام شده.
- **Product Management:** Add, edit, and delete products (with image uploads, stock, and pricing).
  **مدیریت محصولات:** افزودن، ویرایش و حذف محصولات (همراه با آپلود عکس، موجودی و قیمت).
- **Order Management:** Update order statuses (Pending, Paid, Processing, Shipped, Delivered) and insert tracking codes.
  **مدیریت سفارش‌ها:** تغییر وضعیت سفارش‌ها (در انتظار، پرداخت شده، در حال آماده‌سازی، ارسال شده، تحویل شده) و ثبت کد رهگیری پستی.
- **Category & Brand Management:** Organize the store's structure (e.g., Apple, Samsung).
  **مدیریت دسته‌بندی و برند:** سازماندهی ساختار فروشگاه (مثلاً اپل، سامسونگ).
- **CMS:** Edit static pages like "About Us" and "Contact Us" directly from the panel.
  **سیستم مدیریت محتوا:** ویرایش صفحات ثابت مانند «درباره ما» و «تماس با ما» مستقیماً از پنل.
- **Site Settings:** Update site name, contact info, shipping costs, and free shipping thresholds.
  **تنظیمات سایت:** به‌روزرسانی نام سایت، اطلاعات تماس، هزینه ارسال و حداقل مبلغ ارسال رایگان.

---

## 🔒 Security & Tech Stack | امنیت و تکنولوژی‌ها

- **Backend:** PHP 8.x (Native) | بک‌اند: PHP 8.x (خالص)
- **Database:** MySQL / MariaDB | دیتابیس: MySQL / MariaDB
- **CSRF Protection:** All forms are secured with tokens. | محافظت CSRF: تمامی فرم‌ها با توکن امن شده‌اند.
- **Rate Limiting:** Login and OTP requests are protected against brute-force attacks. | محدودیت نرخ درخواست: ورود و درخواست‌های OTP در برابر حملات Brute-force محافظت می‌شوند.
- **Secure Passwords:** Admin passwords use `bcrypt` hashing. | رمزهای عبور امن: رمز ادمین با الگوریتم `bcrypt` هش می‌شود.
- **Session Security:** Strict cookie policies and HTTP-only flags. | امنیت نشست: سیاست‌های سخت‌گیرانه کوکی و پرچم‌های HTTP-only.
- **Prepared Statements:** All database queries use PDO to prevent SQL injection. | دستورات آماده: تمامی کوئری‌های دیتابیس از PDO برای جلوگیری از SQL Injection استفاده می‌کنند.

---

## 🚀 Installation | راه‌اندازی

1. **Clone the repository** | کلون کردن مخزن:
   ```bash
   git clone https://github.com/alirezara2/mobile-shop.git
   ```
2. **Configure Database** | تنظیم دیتابیس:
   - Create a MySQL database. | یک دیتابیس MySQL بسازید.
   - Import the provided `database.sql` file. | فایل `database.sql` را ایمپورت کنید.
   - Update your credentials in `config.php`. | اطلاعات اتصال خود را در `config.php` به‌روزرسانی کنید.
3. **Run the Application** | اجرای برنامه:
   - Move the project folder to your local server (e.g., `htdocs` in XAMPP). | پوشه پروژه را به سرور لوکال خود (مثلاً `htdocs` در XAMPP) منتقل کنید.
   - Access the site via `http://localhost/shop_mobile/`. | از طریق `http://localhost/shop_mobile/` به سایت دسترسی پیدا کنید.

---

## 📂 Project Structure | ساختار پروژه


shop_mobile/
├── admin.php               # Admin control panel | پنل مدیریت
├── api.php                 # AJAX processing and endpoints | پردازش‌های ایجکس
├── cart.php                # Shopping cart page | صفحه سبد خرید
├── category.php            # Shop archive with filters | صفحه فروشگاه با فیلترها
├── checkout.php            # Checkout and address input | صفحه تسویه حساب
├── config.php              # Database and site configuration | تنظیمات سایت و دیتابیس
├── index.php               # Homepage | صفحه اصلی
├── login.php               # OTP Authentication | صفحه ورود و احراز هویت
├── profile.php             # User dashboard and order history | داشبورد کاربر
├── assets/                 # CSS, JS, and images | فایل‌های ظاهری و اسکریپت‌ها
├── includes/               # Reusable header, footer, and functions | فایل‌های مشترک و توابع
└── uploads/                # Product images | تصاویر محصولات



*This project is open-source and intended for educational and commercial use.*
*این پروژه متن‌باز است و برای استفاده‌های آموزشی و تجاری در نظر گرفته شده است.*
