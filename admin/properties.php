<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$user = currentUser();
$pageTitle = isAdmin() ? 'Quản lý bất động sản' : 'Bất động sản của tôi';
$activeNav = 'properties';

$where = [];
$params = [];

if (!isAdmin()) {
    $where[] = 'p.employee_id = ?';
    $params[] = $user['id'];
} elseif (!empty($_GET['employee_id'])) {
    $where[] = 'p.employee_id = ?';
    $params[] = (int)$_GET['employee_id'];
}

if (!empty($_GET['status'])) {
    $where[] = 'p.status = ?';
    $params[] = $_GET['status'];
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("
    SELECT p.*, pt.name AS type_name, c.name AS city_name, d.name AS district_name, u.full_name AS agent_name,
           (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC LIMIT 1) AS image
    FROM properties p
    JOIN property_types pt ON pt.id = p.property_type_id
    JOIN cities c ON c.id = p.city_id
    JOIN districts d ON d.id = p.district_id
    LEFT JOIN users u ON u.id = p.employee_id
    {$whereSql}
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$properties = $stmt->fetchAll();

$employees = isAdmin() ? $pdo->query("SELECT id, full_name FROM users WHERE role = 'employee' ORDER BY full_name")->fetchAll() : [];

require_once __DIR__ . '/includes/header.php';
?>
<div class="panel">
  <div class="toolbar">
    <div class="toolbar-filters">
      <form method="get" style="display:flex; gap:10px; flex-wrap:wrap;">
        <?php if (isAdmin()): ?>
          <select name="employee_id" onchange="this.form.submit()">
            <option value="">Tất cả nhân viên</option>
            <?php foreach ($employees as $emp): ?>
              <option value="<?= (int)$emp['id'] ?>" <?= (isset($_GET['employee_id']) && (int)$_GET['employee_id'] === (int)$emp['id']) ? 'selected' : '' ?>><?= e($emp['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
        <select name="status" onchange="this.form.submit()">
          <option value="">Tất cả trạng thái</option>
          <?php foreach (['available' => 'Còn hiệu lực', 'pending' => 'Chờ duyệt', 'sold' => 'Đã bán', 'rented' => 'Đã cho thuê'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= (($_GET['status'] ?? '') === $val) ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <a href="<?= BASE_URL ?>/admin/property-form.php" class="btn btn-primary btn-sm">+ Đăng tin mới</a>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th></th><th>Tin đăng</th><th>Loại</th><th>Khu vực</th><th>Giá</th><th>Trạng thái</th>
          <?php if (isAdmin()): ?><th>Nhân viên</th><?php endif; ?><th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($properties)): ?>
          <tr><td colspan="8">Chưa có bất động sản nào.</td></tr>
        <?php endif; ?>
        <?php foreach ($properties as $p): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e(imageUrl($p['image'])) ?>" alt=""></td>
            <td><a href="<?= BASE_URL ?>/property-detail.php?slug=<?= urlencode($p['slug']) ?>" target="_blank"><?= e($p['title']) ?></a></td>
            <td><?= e($p['type_name']) ?></td>
            <td><?= e($p['district_name']) ?>, <?= e($p['city_name']) ?></td>
            <td><?= e(formatPrice((float)$p['price'], $p['price_unit'])) ?></td>
            <td><span class="status-pill status-<?= e($p['status']) ?>"><?= e(statusLabel($p['status'])) ?></span></td>
            <?php if (isAdmin()): ?><td><?= e($p['agent_name'] ?? 'Chưa phân công') ?></td><?php endif; ?>
            <td class="table-actions">
              <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/property-form.php?id=<?= (int)$p['id'] ?>">Sửa</a>
              <form method="post" action="<?= BASE_URL ?>/admin/property-delete.php" onsubmit="return confirm('Xóa bất động sản này?');" style="display:inline;">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
