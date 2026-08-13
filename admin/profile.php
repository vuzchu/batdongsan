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

    if ($form['full_name'] === '') $errors[] = 'Vui long nhap ho ten.';
    if ($newPassword !== '' && strlen($newPassword) < 6) $errors[] = 'Mat khau moi phai co it nhat 6 ky tu.';

    if (!$errors) {
        if ($newPassword !== '') {
            $pdo->prepare('UPDATE users SET full_name=?, phone=?, zalo=?, facebook=?, password_hash=? WHERE id=?')
                ->execute([$form['full_name'], $form['phone'], $form['zalo'], $form['facebook'], password_hash($newPassword, PASSWORD_BCRYPT), $user['id']]);
        } else {
            $pdo->prepare('UPDATE users SET full_name=?, phone=?, zalo=?, facebook=? WHERE id=?')
                ->execute([$form['full_name'], $form['phone'], $form['zalo'], $form['facebook'], $user['id']]);
        }
        $_SESSION['user']['full_name'] = $form['full_name'];
        flash('success', 'Da cap nhat ho so ca nhan.');
        redirect(BASE_URL . '/admin/profile.php');
    }
}

$pageTitle = 'Ho so ca nhan';
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
      <label>Ho ten</label>
      <input type="text" name="full_name" required value="<?= e($form['full_name']) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" value="<?= e($me['email']) ?>" disabled>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>So dien thoai (hien thi o trang chi tiet BDS)</label>
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
      <label>Mat khau moi (de trong neu khong doi)</label>
      <input type="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Cap nhat ho so</button>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
