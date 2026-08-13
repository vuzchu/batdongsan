<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

$pageTitle = 'Quan ly nhan vien';
$activeNav = 'employees';

$employees = $pdo->query("
    SELECT u.*, (SELECT COUNT(*) FROM properties p WHERE p.employee_id = u.id) AS total_properties
    FROM users u WHERE u.role = 'employee' ORDER BY u.created_at DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="panel">
  <div class="panel-head">
    <h2>Danh sach nhan vien</h2>
    <a href="<?= BASE_URL ?>/admin/employee-form.php" class="btn btn-primary btn-sm">+ Them nhan vien</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr><th>Ho ten</th><th>Email</th><th>Dien thoai</th><th>Zalo</th><th>Facebook</th><th>BDS phu trach</th><th>Trang thai</th><th>Thao tac</th></tr>
      </thead>
      <tbody>
        <?php if (empty($employees)): ?>
          <tr><td colspan="8">Chua co nhan vien nao.</td></tr>
        <?php endif; ?>
        <?php foreach ($employees as $emp): ?>
          <tr>
            <td><?= e($emp['full_name']) ?></td>
            <td><?= e($emp['email']) ?></td>
            <td><?= e($emp['phone'] ?: '-') ?></td>
            <td><?= e($emp['zalo'] ?: '-') ?></td>
            <td><?= $emp['facebook'] ? '<a href="' . e($emp['facebook']) . '" target="_blank">Link</a>' : '-' ?></td>
            <td><?= (int)$emp['total_properties'] ?></td>
            <td><span class="status-pill <?= $emp['is_active'] ? 'status-available' : 'status-sold' ?>"><?= $emp['is_active'] ? 'Dang lam viec' : 'Ngung' ?></span></td>
            <td class="table-actions">
              <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/employee-form.php?id=<?= (int)$emp['id'] ?>">Sua</a>
              <form method="post" action="<?= BASE_URL ?>/admin/employee-delete.php" onsubmit="return confirm('Xoa nhan vien nay? Cac bat dong san dang phu trach se ve trang thai chua phan cong.');" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$emp['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Xoa</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
