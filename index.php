<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = SITE_NAME . ' - Tìm kiếm bất động sản';

$types = $pdo->query('SELECT * FROM property_types ORDER BY id')->fetchAll();
$cities = $pdo->query('SELECT * FROM cities ORDER BY name')->fetchAll();

$typeCounts = [];
foreach ($pdo->query('SELECT property_type_id, COUNT(*) c FROM properties GROUP BY property_type_id')->fetchAll() as $row) {
    $typeCounts[$row['property_type_id']] = (int)$row['c'];
}

$typeImages = [
    'chung-cu' => 'assets/images/type-apartments.svg',
    'nha-rieng' => 'assets/images/type-houses.svg',
    'van-phong' => 'assets/images/type-offices.svg',
    'mat-bang-kinh-doanh' => 'assets/images/type-retail.svg',
];

$featured = $pdo->query("
    SELECT p.*, pt.name AS type_name, c.name AS city_name, d.name AS district_name,
           (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC LIMIT 1) AS image
    FROM properties p
    JOIN property_types pt ON pt.id = p.property_type_id
    JOIN cities c ON c.id = p.city_id
    JOIN districts d ON d.id = p.district_id
    WHERE p.status = 'available'
    ORDER BY p.created_at DESC
    LIMIT 6
")->fetchAll();

$totalProperties = (int)$pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
$totalEmployees = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'employee'")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="hero-inner">
    <div>
      <span class="hero-eyebrow">Tìm kiếm không gian lý tưởng của bạn</span>
      <h1>Tìm ngôi nhà<br>mơ ước hôm nay</h1>
      <p>Khám phá chung cư, nhà riêng, văn phòng và mặt bằng kinh doanh khắp Việt Nam - mua bán hoặc cho thuê, kết nối trực tiếp với nhân viên phụ trách.</p>
      <a href="<?= BASE_URL ?>/properties.php" class="btn btn-primary">Xem bất động sản &rarr;</a>
    </div>
    <div class="hero-image">
      <img src="<?= BASE_URL ?>/assets/images/hero-house.svg" alt="Homeland Real Estate">
    </div>
  </div>
</section>

<div class="container">
  <form class="search-box" action="<?= BASE_URL ?>/properties.php" method="get">
    <div class="search-tabs">
      <button type="button" class="active" data-value="sale">Mua</button>
      <button type="button" data-value="rent">Thuê</button>
    </div>
    <input type="hidden" name="transaction_type" value="sale">
    <div class="search-fields">
      <div class="search-field">
        <label>Khu vực</label>
        <select name="city_id">
          <option value="">Tất cả khu vực</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="search-field">
        <label>Loại bất động sản</label>
        <select name="property_type">
          <option value="">Tất cả loại hình</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t['slug']) ?>"><?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="search-field">
        <label>Mức giá</label>
        <select name="price_range">
          <option value="">Không giới hạn</option>
          <option value="0-1000000000">Dưới 1 tỷ</option>
          <option value="1000000000-3000000000">1 - 3 tỷ</option>
          <option value="3000000000-6000000000">3 - 6 tỷ</option>
          <option value="6000000000-">Trên 6 tỷ</option>
        </select>
      </div>
      <div class="search-field">
        <label>Phòng ngủ</label>
        <select name="bedrooms">
          <option value="">Bất kỳ</option>
          <option value="1">1+ phòng</option>
          <option value="2">2+ phòng</option>
          <option value="3">3+ phòng</option>
          <option value="4">4+ phòng</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary search-submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
        Tìm kiếm
      </button>
    </div>
  </form>
</div>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Chọn theo loại hình</span>
        <h2>Tìm bất động sản phù hợp<br>với <span class="accent">phong cách của bạn</span></h2>
      </div>
      <a href="<?= BASE_URL ?>/properties.php" class="btn btn-outline">Xem tất cả &rarr;</a>
    </div>
    <div class="type-grid">
      <?php foreach ($types as $t): ?>
        <a class="type-card" href="<?= BASE_URL ?>/properties.php?property_type=<?= e($t['slug']) ?>">
          <img src="<?= BASE_URL ?>/<?= e($typeImages[$t['slug']] ?? 'assets/images/placeholder.svg') ?>" alt="<?= e($t['name']) ?>">
          <div class="type-card-body">
            <h3><?= e($t['name']) ?></h3>
            <p><?= (int)($typeCounts[$t['id']] ?? 0) ?> bất động sản</p>
            <span class="link">Xem ngay &rarr;</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:var(--bg-soft)">
  <div class="container">
    <div class="section-head">
      <div>
        <span class="eyebrow">Bất động sản nổi bật</span>
        <h2>Lựa chọn <span class="accent">dành riêng cho bạn</span></h2>
      </div>
      <a href="<?= BASE_URL ?>/properties.php" class="btn btn-outline">Xem tất cả &rarr;</a>
    </div>
    <?php if (empty($featured)): ?>
      <p class="empty-state">Chưa có bất động sản nào được đăng.</p>
    <?php else: ?>
      <div class="property-grid">
        <?php foreach ($featured as $p): include __DIR__ . '/includes/property-card.php'; endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="stats-bar">
  <div class="container stats-grid">
    <div>
      <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
      <strong>1200+</strong><span>Khách hàng hài lòng</span>
    </div>
    <div>
      <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"/><path d="M5 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6"/></svg></div>
      <strong><?= $totalProperties ?>+</strong><span>Bất động sản đăng tin</span>
    </div>
    <div>
      <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/></svg></div>
      <strong><?= $totalEmployees ?>+</strong><span>Nhân viên tư vấn</span>
    </div>
    <div>
      <div class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M9 14l-3 8 6-3 6 3-3-8"/></svg></div>
      <strong>98%</strong><span>Khách hàng hài lòng</span>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
