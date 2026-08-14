<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$user = currentUser();
$pageTitle = 'Tổng quan';
$activeNav = 'dashboard';

if (isAdmin()) {
    $totalProperties = (int)$pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
    $totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();
    $totalSale = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE transaction_type = 'sale'")->fetchColumn();
    $totalRent = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE transaction_type = 'rent'")->fetchColumn();

    $recentStmt = $pdo->query("
        SELECT p.*, pt.name AS type_name, u.full_name AS agent_name
        FROM properties p
        JOIN property_types pt ON pt.id = p.property_type_id
        LEFT JOIN users u ON u.id = p.employee_id
        ORDER BY p.created_at DESC LIMIT 8
    ");
    $recent = $recentStmt->fetchAll();

    $byAgentStmt = $pdo->query("
        SELECT u.id, u.full_name, COUNT(p.id) AS total
        FROM users u
        LEFT JOIN properties p ON p.employee_id = u.id
        WHERE u.role = 'employee'
        GROUP BY u.id, u.full_name
        ORDER BY total DESC
    ");
    $byAgent = $byAgentStmt->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM properties WHERE employee_id = ?');
    $stmt->execute([$user['id']]);
    $myTotal = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE employee_id = ? AND status = 'available'");
    $stmt->execute([$user['id']]);
    $myAvailable = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE employee_id = ? AND transaction_type = 'sale'");
    $stmt->execute([$user['id']]);
    $mySale = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE employee_id = ? AND transaction_type = 'rent'");
    $stmt->execute([$user['id']]);
    $myRent = (int)$stmt->fetchColumn();

    $recentStmt = $pdo->prepare("
        SELECT p.*, pt.name AS type_name
        FROM properties p
        JOIN property_types pt ON pt.id = p.property_type_id
        WHERE p.employee_id = ?
        ORDER BY p.created_at DESC LIMIT 8
    ");
    $recentStmt->execute([$user['id']]);
    $recent = $recentStmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if (isAdmin()): ?>
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/></svg></div>
      <strong><?= $totalProperties ?></strong><span>Tổng bất động sản</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div>
      <strong><?= $totalEmployees ?></strong><span>Nhân viên</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div>
      <strong><?= $totalSale ?></strong><span>Tin mua bán</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
      <strong><?= $totalRent ?></strong><span>Tin cho thuê</span>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head"><h2>Bất động sản theo nhân viên</h2></div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Nhân viên</th><th>Số bất động sản phụ trách</th></tr></thead>
        <tbody>
        <?php foreach ($byAgent as $a): ?>
          <tr><td><?= e($a['full_name']) ?></td><td><?= (int)$a['total'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/></svg></div>
      <strong><?= $myTotal ?></strong><span>Bất động sản của tôi</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></div>
      <strong><?= $myAvailable ?></strong><span>Đang còn hiệu lực</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg></div>
      <strong><?= $mySale ?></strong><span>Tin mua bán</span>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
      <strong><?= $myRent ?></strong><span>Tin cho thuê</span>
    </div>
  </div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <h2><?= isAdmin() ? 'Bất động sản mới nhất' : 'Bất động sản của tôi' ?></h2>
    <a href="<?= BASE_URL ?>/admin/properties.php" class="btn btn-outline btn-sm">Xem tất cả</a>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Tin đăng</th><th>Loại</th><th>Giá</th><th>Trạng thái</th><?php if (isAdmin()): ?><th>Nhân viên</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent)): ?>
          <tr><td colspan="5">Chưa có bất động sản nào.</td></tr>
        <?php endif; ?>
        <?php foreach ($recent as $p): ?>
          <tr>
            <td><a href="<?= BASE_URL ?>/property-detail.php?slug=<?= urlencode($p['slug']) ?>" target="_blank"><?= e($p['title']) ?></a></td>
            <td><?= e($p['type_name']) ?></td>
            <td><?= e(formatPrice((float)$p['price'], $p['price_unit'])) ?></td>
            <td><span class="status-pill status-<?= e($p['status']) ?>"><?= e(statusLabel($p['status'])) ?></span></td>
            <?php if (isAdmin()): ?><td><?= e($p['agent_name'] ?? 'Chưa phân công') ?></td><?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
