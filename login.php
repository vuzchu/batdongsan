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
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } elseif (attemptLogin($pdo, $email, $password)) {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        $error = 'Email hoặc mật khẩu không đúng.';
    }
}

$pageTitle = 'Đăng nhập - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Đăng nhập hệ thống</h1>
    <p class="sub">Dành cho quản trị viên và nhân viên quản lý bất động sản.</p>

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
        <label for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
    </form>

    <div class="demo-hint">
      Tài khoản demo &mdash; Admin: <strong>admin@homeland.vn</strong> / <strong>Admin@123</strong><br>
      Nhân viên: <strong>an.nguyen@homeland.vn</strong> / <strong>Nhanvien@123</strong>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
