<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Quản lý tiện ích';
$activeNav = 'amenities';

$amenities = $pdo->query("
    SELECT a.*, (SELECT COUNT(*) FROM property_amenities pa WHERE pa.amenity_id = a.id) AS total_properties
    FROM amenities a ORDER BY a.name
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="panel">
  <div class="panel-head">
    <h2>Danh sách tiện ích / dịch vụ</h2>
    <a href="<?= BASE_URL ?>/admin/amenity-form.php" class="btn btn-primary btn-sm">+ Thêm tiện ích</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Tên tiện ích</th><th>Số BĐS đang dùng</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
        <?php if (empty($amenities)): ?>
          <tr><td colspan="3">Chưa có tiện ích nào.</td></tr>
        <?php endif; ?>
        <?php foreach ($amenities as $a): ?>
          <tr>
            <td><?= e($a['name']) ?></td>
            <td><?= (int)$a['total_properties'] ?></td>
            <td class="table-actions">
              <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/amenity-form.php?id=<?= (int)$a['id'] ?>">Sửa</a>
              <form method="post" action="<?= BASE_URL ?>/admin/amenity-delete.php" onsubmit="return confirm('Xóa tiện ích này? Sẽ bị gỡ khỏi mọi tin đăng đang dùng.');" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
