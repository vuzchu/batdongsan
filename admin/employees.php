<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Quản lý nhân viên';
$activeNav = 'employees';

$employees = $pdo->query("
    SELECT u.*, (SELECT COUNT(*) FROM properties p WHERE p.employee_id = u.id) AS total_properties
    FROM users u WHERE u.role = 'employee' ORDER BY u.created_at DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="panel">
  <div class="panel-head">
    <h2>Danh sách nhân viên</h2>
    <a href="<?= BASE_URL ?>/admin/employee-form.php" class="btn btn-primary btn-sm">+ Thêm nhân viên</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Họ tên</th><th>Email</th><th>Điện thoại</th><th>Zalo</th><th>Facebook</th><th>BĐS phụ trách</th><th>Trạng thái</th><th>Thao tác</th></tr>
      </thead>
      <tbody>
        <?php if (empty($employees)): ?>
          <tr><td colspan="8">Chưa có nhân viên nào.</td></tr>
        <?php endif; ?>
        <?php foreach ($employees as $emp): ?>
          <tr>
            <td><?= e($emp['full_name']) ?></td>
            <td><?= e($emp['email']) ?></td>
            <td><?= e($emp['phone'] ?: '-') ?></td>
            <td><?= e($emp['zalo'] ?: '-') ?></td>
            <td><?= $emp['facebook'] ? '<a href="' . e($emp['facebook']) . '" target="_blank">Link</a>' : '-' ?></td>
            <td><?= (int)$emp['total_properties'] ?></td>
            <td><span class="status-pill <?= $emp['is_active'] ? 'status-available' : 'status-sold' ?>"><?= $emp['is_active'] ? 'Đang làm việc' : 'Ngừng' ?></span></td>
            <td class="table-actions">
              <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/employee-form.php?id=<?= (int)$emp['id'] ?>">Sửa</a>
              <form method="post" action="<?= BASE_URL ?>/admin/employee-delete.php" onsubmit="return confirm('Xóa nhân viên này? Các bất động sản đang phụ trách sẽ về trạng thái chưa phân công.');" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$emp['id'] ?>">
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
