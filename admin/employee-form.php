<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$employee = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'employee'");
    $stmt->execute([$id]);
    $employee = $stmt->fetch();
    if (!$employee) {
        flash('error', 'Không tìm thấy nhân viên.');
        redirect(BASE_URL . '/admin/employees.php');
    }
}

$errors = [];
$form = [
    'full_name' => $employee['full_name'] ?? '',
    'email' => $employee['email'] ?? '',
    'phone' => $employee['phone'] ?? '',
    'zalo' => $employee['zalo'] ?? '',
    'facebook' => $employee['facebook'] ?? '',
    'is_active' => $employee['is_active'] ?? 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['email'] = trim($_POST['email'] ?? '');
    $form['phone'] = trim($_POST['phone'] ?? '');
    $form['zalo'] = trim($_POST['zalo'] ?? '');
    $form['facebook'] = trim($_POST['facebook'] ?? '');
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($form['full_name'] === '') $errors[] = 'Vui lòng nhập họ tên.';
    if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
    if (!$employee && $password === '') $errors[] = 'Vui lòng nhập mật khẩu cho nhân viên mới.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';

    if (!$errors) {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $checkStmt->execute([$form['email'], $id]);
        if ($checkStmt->fetch()) {
            $errors[] = 'Email này đã được sử dụng.';
        }
    }

    if (!$errors) {
        if ($employee) {
            if ($password !== '') {
                $stmt = $pdo->prepare('UPDATE users SET full_name=?, email=?, phone=?, zalo=?, facebook=?, is_active=?, password_hash=? WHERE id=?');
                $stmt->execute([$form['full_name'], $form['email'], $form['phone'], $form['zalo'], $form['facebook'], $form['is_active'], password_hash($password, PASSWORD_BCRYPT), $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET full_name=?, email=?, phone=?, zalo=?, facebook=?, is_active=? WHERE id=?');
                $stmt->execute([$form['full_name'], $form['email'], $form['phone'], $form['zalo'], $form['facebook'], $form['is_active'], $id]);
            }
            flash('success', 'Đã cập nhật thông tin nhân viên.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role, phone, zalo, facebook, is_active) VALUES (?, ?, ?, 'employee', ?, ?, ?, ?)");
            $stmt->execute([$form['full_name'], $form['email'], password_hash($password, PASSWORD_BCRYPT), $form['phone'], $form['zalo'], $form['facebook'], $form['is_active']]);
            flash('success', 'Đã thêm nhân viên mới.');
        }
        redirect(BASE_URL . '/admin/employees.php');
    }
}

$pageTitle = $employee ? 'Sửa nhân viên' : 'Thêm nhân viên';
$activeNav = 'employees';
require_once __DIR__ . '/includes/header.php';
?>
<div class="panel form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err) echo e($err) . '<br>'; ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="form-row">
      <div class="form-group">
        <label>Họ tên</label>
        <input type="text" name="full_name" required value="<?= e($form['full_name']) ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($form['email']) ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="text" name="phone" value="<?= e($form['phone']) ?>" placeholder="09xxxxxxxx">
      </div>
      <div class="form-group">
        <label>Zalo (số điện thoại hoặc tên định danh)</label>
        <input type="text" name="zalo" value="<?= e($form['zalo']) ?>" placeholder="09xxxxxxxx">
      </div>
    </div>
    <div class="form-group">
      <label>Link Facebook</label>
      <input type="text" name="facebook" value="<?= e($form['facebook']) ?>" placeholder="https://facebook.com/...">
    </div>
    <div class="form-group">
      <label><?= $employee ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu' ?></label>
      <input type="password" name="password" <?= $employee ? '' : 'required' ?>>
    </div>
    <div class="form-group">
      <label><input type="checkbox" name="is_active" style="width:auto" <?= $form['is_active'] ? 'checked' : '' ?>> Đang làm việc</label>
    </div>
    <button type="submit" class="btn btn-primary"><?= $employee ? 'Cập nhật' : 'Thêm nhân viên' ?></button>
    <a href="<?= BASE_URL ?>/admin/employees.php" class="btn btn-outline">Hủy</a>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
