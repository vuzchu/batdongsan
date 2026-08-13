<?php
/**
 * Header dung chung cho khu vuc quan tri (dashboard). Yeu cau requireLogin() da duoc goi truoc.
 * Bien $pageTitle, $activeNav co the duoc dat truoc khi include.
 */
$pageTitle = $pageTitle ?? 'Bang dieu khien';
$activeNav = $activeNav ?? '';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>/index.php" class="admin-logo">
      <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
      <span>HOMELAND</span>
    </a>
    <nav class="admin-nav">
      <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
        Tong quan
      </a>
      <?php if (isAdmin()): ?>
      <a href="<?= BASE_URL ?>/admin/employees.php" class="<?= $activeNav === 'employees' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Nhan vien
      </a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/admin/properties.php" class="<?= $activeNav === 'properties' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6"/></svg>
        <?= isAdmin() ? 'Bat dong san' : 'Bat dong san cua toi' ?>
      </a>
      <a href="<?= BASE_URL ?>/admin/profile.php" class="<?= $activeNav === 'profile' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
        Ho so ca nhan
      </a>
      <a href="<?= BASE_URL ?>/index.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
        Xem trang web
      </a>
      <a href="<?= BASE_URL ?>/logout.php">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Dang xuat
      </a>
    </nav>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <h1><?= e($pageTitle) ?></h1>
      <div class="admin-user">
        <span class="role-badge role-<?= e($user['role']) ?>"><?= $user['role'] === 'admin' ? 'Quan tri vien' : 'Nhan vien' ?></span>
        <div class="admin-avatar"><?= e(mb_strtoupper(mb_substr($user['full_name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></div>
        <span><?= e($user['full_name']) ?></span>
      </div>
    </header>
    <div class="admin-content">
      <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
