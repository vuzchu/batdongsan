<?php
/**
 * Header dung chung cho trang public. Bien $pageTitle co the duoc dat truoc khi include.
 */
$pageTitle = $pageTitle ?? SITE_NAME;
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?= BASE_URL ?>/index.php" class="logo">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
      <span class="logo-text">HOMELAND<br><small>REAL ESTATE</small></span>
    </a>
    <nav class="main-nav">
      <a href="<?= BASE_URL ?>/index.php">Trang chu</a>
      <a href="<?= BASE_URL ?>/properties.php">Bat dong san</a>
      <a href="<?= BASE_URL ?>/properties.php?transaction_type=sale">Mua ban</a>
      <a href="<?= BASE_URL ?>/properties.php?transaction_type=rent">Cho thue</a>
    </nav>
    <div class="header-actions">
      <?php if ($user): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-outline">Bang dieu khien</a>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-primary">Dang xuat</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary">Dang nhap</a>
      <?php endif; ?>
    </div>
    <button class="nav-toggle" type="button" aria-label="Menu">&#9776;</button>
  </div>
</header>
<main>
