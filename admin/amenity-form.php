<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$amenity = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM amenities WHERE id = ?');
    $stmt->execute([$id]);
    $amenity = $stmt->fetch();
    if (!$amenity) {
        flash('error', 'Không tìm thấy tiện ích.');
        redirect(BASE_URL . '/admin/amenities.php');
    }
}

$errors = [];
$name = $amenity['name'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $name = trim($_POST['name'] ?? '');

    if ($name === '') $errors[] = 'Vui lòng nhập tên tiện ích.';

    if (!$errors) {
        $checkStmt = $pdo->prepare('SELECT id FROM amenities WHERE name = ? AND id != ?');
        $checkStmt->execute([$name, $id]);
        if ($checkStmt->fetch()) {
            $errors[] = 'Tiện ích này đã tồn tại.';
        }
    }

    if (!$errors) {
        if ($amenity) {
            $pdo->prepare('UPDATE amenities SET name = ? WHERE id = ?')->execute([$name, $id]);
            flash('success', 'Đã cập nhật tiện ích.');
        } else {
            $pdo->prepare('INSERT INTO amenities (name) VALUES (?)')->execute([$name]);
            flash('success', 'Đã thêm tiện ích mới.');
        }
        redirect(BASE_URL . '/admin/amenities.php');
    }
}

$pageTitle = $amenity ? 'Sửa tiện ích' : 'Thêm tiện ích';
$activeNav = 'amenities';
require_once __DIR__ . '/includes/header.php';
?>
<div class="panel form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err) echo e($err) . '<br>'; ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="form-group">
      <label>Tên tiện ích</label>
      <input type="text" name="name" required value="<?= e($name) ?>" placeholder="Vd: Hồ bơi, Gym, Bảo vệ 24/7">
    </div>
    <button type="submit" class="btn btn-primary"><?= $amenity ? 'Cập nhật' : 'Thêm tiện ích' ?></button>
    <a href="<?= BASE_URL ?>/admin/amenities.php" class="btn btn-outline">Hủy</a>
  </form>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
