<?php
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    redirect(BASE_URL . '/properties.php');
}

$stmt = $pdo->prepare("
    SELECT p.*, pt.name AS type_name, c.name AS city_name, d.name AS district_name,
           u.id AS agent_id, u.full_name AS agent_name, u.phone AS agent_phone, u.zalo AS agent_zalo,
           u.facebook AS agent_facebook, u.avatar AS agent_avatar
    FROM properties p
    JOIN property_types pt ON pt.id = p.property_type_id
    JOIN cities c ON c.id = p.city_id
    JOIN districts d ON d.id = p.district_id
    LEFT JOIN users u ON u.id = p.employee_id
    WHERE p.slug = ?
    LIMIT 1
");
$stmt->execute([$slug]);
$property = $stmt->fetch();

if (!$property) {
    http_response_code(404);
    $pageTitle = 'Khong tim thay bat dong san - ' . SITE_NAME;
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container"><p class="empty-state">Khong tim thay bat dong san ban dang tim.</p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pdo->prepare('UPDATE properties SET views = views + 1 WHERE id = ?')->execute([$property['id']]);

$imgStmt = $pdo->prepare('SELECT image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, id ASC');
$imgStmt->execute([$property['id']]);
$images = array_column($imgStmt->fetchAll(), 'image_path');
if (empty($images)) {
    $images = ['assets/images/placeholder.svg'];
}

$amenStmt = $pdo->prepare('SELECT name FROM property_amenities WHERE property_id = ? ORDER BY id');
$amenStmt->execute([$property['id']]);
$amenities = array_column($amenStmt->fetchAll(), 'name');

$pageTitle = $property['title'] . ' - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';

$zaloPhone = $property['agent_zalo'] ? preg_replace('/\D/', '', $property['agent_zalo']) : '';
$agentInitial = $property['agent_name'] ? mb_strtoupper(mb_substr($property['agent_name'], 0, 1, 'UTF-8'), 'UTF-8') : '?';
?>
<div class="page-header">
  <div class="container">
    <div class="breadcrumb">
      <a href="<?= BASE_URL ?>/index.php">Trang chu</a> /
      <a href="<?= BASE_URL ?>/properties.php">Bat dong san</a> /
      <?= e($property['title']) ?>
    </div>
    <h1><?= e($property['title']) ?></h1>
  </div>
</div>

<div class="container">
  <div class="detail-layout">
    <div>
      <div class="detail-gallery">
        <img src="<?= BASE_URL ?>/<?= e($images[0]) ?>" alt="<?= e($property['title']) ?>">
      </div>

      <div class="detail-title-row">
        <div>
          <span class="badge <?= $property['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-sale' ?>" style="position:static;display:inline-block;margin-bottom:8px;">
            <?= $property['transaction_type'] === 'rent' ? 'Cho thue' : 'Ban' ?>
          </span>
          <p class="property-location">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= e($property['address'] ? $property['address'] . ', ' : '') ?><?= e($property['district_name']) ?>, <?= e($property['city_name']) ?>
          </p>
        </div>
        <div class="detail-price"><?= e(formatPrice((float)$property['price'], $property['price_unit'])) ?></div>
      </div>

      <div class="spec-grid">
        <div class="spec-item">
          <div class="spec-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
          <strong><?= e(rtrim(rtrim(number_format((float)$property['area'], 1), '0'), '.')) ?> m2</strong>
          <span>Dien tich</span>
        </div>
        <div class="spec-item">
          <div class="spec-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v13h18V7"/><path d="M3 7l9-4 9 4"/></svg></div>
          <strong><?= (int)$property['bedrooms'] ?></strong>
          <span>Phong ngu</span>
        </div>
        <div class="spec-item">
          <div class="spec-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div>
          <strong><?= (int)$property['bathrooms'] ?></strong>
          <span>Phong tam</span>
        </div>
        <div class="spec-item">
          <div class="spec-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6"/></svg></div>
          <strong><?= (int)$property['floors'] ?></strong>
          <span>So tang</span>
        </div>
      </div>

      <div class="detail-section">
        <h2>Mo ta chi tiet</h2>
        <p><?= nl2br(e($property['description'] ?: 'Chua co mo ta cho bat dong san nay.')) ?></p>
        <p style="color:var(--text-muted);font-size:13.5px;margin-top:10px;">Loai bat dong san: <strong><?= e($property['type_name']) ?></strong> &nbsp;|&nbsp; Ma tin: #<?= (int)$property['id'] ?> &nbsp;|&nbsp; <?= (int)$property['views'] ?> luot xem</p>
      </div>

      <?php if (!empty($amenities)): ?>
      <div class="detail-section">
        <h2>Tien ich &amp; dich vu</h2>
        <ul class="amenity-list">
          <?php foreach ($amenities as $a): ?>
            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> <?= e($a) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>

    <aside class="agent-card">
      <?php if ($property['agent_id']): ?>
        <div class="agent-head">
          <?php if ($property['agent_avatar']): ?>
            <img class="agent-avatar" src="<?= BASE_URL ?>/<?= e($property['agent_avatar']) ?>" alt="<?= e($property['agent_name']) ?>" style="object-fit:cover;">
          <?php else: ?>
            <div class="agent-avatar"><?= e($agentInitial) ?></div>
          <?php endif; ?>
          <div>
            <h3><?= e($property['agent_name']) ?></h3>
            <div class="agent-role">Nhan vien phu trach</div>
          </div>
        </div>
        <p style="font-size:13.5px;color:var(--text-muted);margin:0 0 4px;">Lien he truc tiep de duoc tu van va xem nha:</p>
        <div class="contact-icons">
          <a class="ic-phone" href="tel:<?= e($property['agent_phone']) ?>" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Dien thoai
          </a>
          <a class="ic-zalo" href="https://zalo.me/<?= e($zaloPhone) ?>" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><text x="12" y="16" font-size="9" text-anchor="middle" fill="currentColor" stroke="none">Za</text></svg>
            Zalo
          </a>
          <a class="ic-fb" href="<?= e($property['agent_facebook'] ?: '#') ?>" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            Facebook
          </a>
        </div>
      <?php else: ?>
        <h3>Lien he tu van</h3>
        <p class="no-agent">Bat dong san nay chua duoc phan cong nhan vien phu trach. Vui long lien he hotline 0900 000 000.</p>
      <?php endif; ?>
    </aside>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
