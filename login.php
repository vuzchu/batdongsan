<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui long nhap day du email va mat khau.';
    } elseif (attemptLogin($pdo, $email, $password)) {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        $error = 'Email hoac mat khau khong dung.';
    }
}

$pageTitle = 'Dang nhap - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Dang nhap he thong</h1>
    <p class="sub">Danh cho quan tri vien va nhan vien quan ly bat dong san.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="">
      <?= csrfField() ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Mat khau</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Dang nhap</button>
    </form>

    <div class="demo-hint">
      Tai khoan demo &mdash; Admin: <strong>admin@homeland.vn</strong> / <strong>Admin@123</strong><br>
      Nhan vien: <strong>an.nguyen@homeland.vn</strong> / <strong>Nhanvien@123</strong>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
