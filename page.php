<?php
require_once 'config.php';

$slug = $_GET['page'] ?? 'about';
$s = $pdo->prepare("SELECT * FROM pages WHERE slug=?");
$s->execute([$slug]);
$page = $s->fetch();

if (!$page) {
    $page_title = 'صفحه یافت نشد';
    include 'includes/header.php';
    echo '<div class="container" style="padding:50px;text-align:center;"><h2>صفحه یافت نشد</h2></div>';
    include 'includes/footer.php';
    exit;
}

$page_title = $page['title'];
include 'includes/header.php';
?>

<div class="container" style="max-width:800px; padding-top:40px;">
    <h1 style="color:var(--primary-d); margin-bottom:20px; text-align:center;"><?= e($page['title']) ?></h1>
    <div style="background:#fff; padding:30px; border-radius:16px; box-shadow:var(--shadow); line-height:2; font-size:15px;">
        <?= $page['content'] ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>