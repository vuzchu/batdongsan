<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$user = currentUser();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$me = $stmt->fetch();

$errors = [];
$form = [
    'full_name' => $me['full_name'],
    'phone' => $me['phone'],
    'zalo' => $me['zalo'],
    'facebook' => $me['facebook'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['zalo'] = trim($_POST['zalo'] ?? '');
    $form['facebook'] = trim($_POST['facebook'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if ($form['full_name'] === '') $errors[] = 'Vui lòng nhập họ tên.';
    if ($newPassword !== '' && strlen($newPassword) < 6) $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự.';

    if (!$errors) {
        if ($newPassword !== '') {
            $pdo->prepare('UPDATE users SET full_name=?, phone=?, zalo=?, facebook=?, password_hash=? WHERE id=?')
                ->execute([$form['full_name'], $form['phone'], $form['zalo'], $form['facebook'], password_hash($newPassword, PASSWORD_BCRYPT), $user['id']]);
        } else {
            $pdo->prepare('UPDATE users SET full_name=?, phone=?, zalo=?, facebook=? WHERE id=?')
                ->execute([$form['full_name'], $form['phone'], $form['zalo'], $form['facebook'], $user['id']]);
        }
        $_SESSION['user']['full_name'] = $form['full_name'];
        flash('success', 'Đã cập nhật hồ sơ cá nhân.');
        redirect(BASE_URL . '/admin/profile.php');
    }
}

$pageTitle = 'Hồ sơ cá nhân';
$activeNav = 'profile';
require_once __DIR__ . '/includes/header.php';
?>
<div class="panel form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err) echo e($err) . '<br>'; ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <?= csrfField() ?>
    <div class="form-group">
      <label>Họ tên</label>
      <input type="text" name="full_name" required value="<?= e($form['full_name']) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" value="<?= e($me['email']) ?>" disabled>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Số điện thoại (hiển thị ở trang chi tiết BĐS)</label>
        <input type="text" name="phone" value="<?= e($form['phone']) ?>" placeholder="09xxxxxxxx">
      </div>
      <div class="form-group">
        <label>Zalo</label>
        <input type="text" name="zalo" value="<?= e($form['zalo']) ?>" placeholder="09xxxxxxxx">
      </div>
    </div>
    <div class="form-group">
      <label>Link Facebook</label>
      <input type="text" name="facebook" value="<?= e($form['facebook']) ?>" placeholder="https://facebook.com/...">
    </div>
    <div class="form-group">
      <label>Mật khẩu mới (để trống nếu không đổi)</label>
      <input type="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Cập nhật hồ sơ</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
