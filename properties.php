<?php
require_once __DIR__ . '/config/config.php';

$cities = $pdo->query('SELECT * FROM cities ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT * FROM districts ORDER BY name')->fetchAll();
$types = $pdo->query('SELECT * FROM property_types ORDER BY id')->fetchAll();

$cityId = isset($_GET['city_id']) && $_GET['city_id'] !== '' ? (int)$_GET['city_id'] : null;
$districtId = isset($_GET['district_id']) && $_GET['district_id'] !== '' ? (int)$_GET['district_id'] : null;
$typeSlug = trim($_GET['property_type'] ?? '');
$transactionType = in_array($_GET['transaction_type'] ?? '', ['sale', 'rent'], true) ? $_GET['transaction_type'] : '';
$bedrooms = isset($_GET['bedrooms']) && $_GET['bedrooms'] !== '' ? (int)$_GET['bedrooms'] : null;
$keyword = trim($_GET['q'] ?? '');
$priceRange = trim($_GET['price_range'] ?? '');
$priceMin = null;
$priceMax = null;
if ($priceRange !== '' && strpos($priceRange, '-') !== false) {
    [$minStr, $maxStr] = explode('-', $priceRange, 2);
    $priceMin = $minStr !== '' ? (float)$minStr : null;
    $priceMax = $maxStr !== '' ? (float)$maxStr : null;
}

$where = ["p.status = 'available'"];
$params = [];

if ($cityId) { $where[] = 'p.city_id = ?'; $params[] = $cityId; }
if ($districtId) { $where[] = 'p.district_id = ?'; $params[] = $districtId; }
if ($typeSlug !== '') { $where[] = 'pt.slug = ?'; $params[] = $typeSlug; }
if ($transactionType !== '') { $where[] = 'p.transaction_type = ?'; $params[] = $transactionType; }
if ($bedrooms) { $where[] = 'p.bedrooms >= ?'; $params[] = $bedrooms; }
if ($priceMin !== null) { $where[] = 'p.price >= ?'; $params[] = $priceMin; }
if ($priceMax !== null) { $where[] = 'p.price <= ?'; $params[] = $priceMax; }
if ($keyword !== '') {
    $where[] = '(p.title LIKE ? OR p.address LIKE ?)';
    $params[] = "%{$keyword}%";
    $params[] = "%{$keyword}%";
}

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("
    SELECT COUNT(*) FROM properties p
    JOIN property_types pt ON pt.id = p.property_type_id
    WHERE {$whereSql}
");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$perPage = 9;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$pg = paginate($total, $page, $perPage);

$stmt = $pdo->prepare("
    SELECT p.*, pt.name AS type_name, c.name AS city_name, d.name AS district_name,
           (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC LIMIT 1) AS image
    FROM properties p
    JOIN property_types pt ON pt.id = p.property_type_id
    JOIN cities c ON c.id = p.city_id
    JOIN districts d ON d.id = p.district_id
    WHERE {$whereSql}
    ORDER BY p.created_at DESC
    LIMIT {$perPage} OFFSET {$pg['offset']}
");
$stmt->execute($params);
$properties = $stmt->fetchAll();

$pageTitle = 'Tim kiem bat dong san - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';

function qs(array $overrides): string
{
    $params = array_merge($_GET, $overrides);
    foreach ($params as $k => $v) {
        if ($v === '' || $v === null) unset($params[$k]);
    }
    return '?' . http_build_query($params);
}
?>
<div class="page-header">
  <div class="container">
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Trang chu</a> / Bat dong san</div>
    <h1>Tim kiem bat dong san</h1>
  </div>
</div>

<div class="container">
  <div class="listing-layout">
    <aside class="filter-panel">
      <h3>Bo loc tim kiem</h3>
      <form method="get" action="<?= BASE_URL ?>/properties.php">
        <div class="filter-group">
          <label>Tu khoa</label>
          <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Ten duong, du an...">
        </div>
        <div class="filter-group">
          <label>Loai giao dich</label>
          <select name="transaction_type">
            <option value="">Tat ca</option>
            <option value="sale" <?= $transactionType === 'sale' ? 'selected' : '' ?>>Mua ban</option>
            <option value="rent" <?= $transactionType === 'rent' ? 'selected' : '' ?>>Cho thue</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Tinh / Thanh pho</label>
          <select name="city_id">
            <option value="">Tat ca</option>
            <?php foreach ($cities as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= $cityId === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Quan / Huyen</label>
          <select name="district_id">
            <option value="">Tat ca</option>
            <?php foreach ($districts as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= $districtId === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Loai bat dong san</label>
          <select name="property_type">
            <option value="">Tat ca</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= e($t['slug']) ?>" <?= $typeSlug === $t['slug'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>Muc gia</label>
          <select name="price_range">
            <option value="">Khong gioi han</option>
            <option value="0-1000000000" <?= $priceRange === '0-1000000000' ? 'selected' : '' ?>>Duoi 1 ty</option>
            <option value="1000000000-3000000000" <?= $priceRange === '1000000000-3000000000' ? 'selected' : '' ?>>1 - 3 ty</option>
            <option value="3000000000-6000000000" <?= $priceRange === '3000000000-6000000000' ? 'selected' : '' ?>>3 - 6 ty</option>
            <option value="6000000000-" <?= $priceRange === '6000000000-' ? 'selected' : '' ?>>Tren 6 ty</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Phong ngu toi thieu</label>
          <select name="bedrooms">
            <option value="">Bat ky</option>
            <?php foreach ([1, 2, 3, 4, 5] as $b): ?>
              <option value="<?= $b ?>" <?= $bedrooms === $b ? 'selected' : '' ?>><?= $b ?>+ phong</option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Ap dung bo loc</button>
      </form>
    </aside>

    <div>
      <div class="listing-results-head">
        <span>Tim thay <strong><?= $total ?></strong> bat dong san</span>
      </div>

      <?php if (empty($properties)): ?>
        <p class="empty-state">Khong tim thay bat dong san phu hop. Vui long thu bo loc khac.</p>
      <?php else: ?>
        <div class="property-grid">
          <?php foreach ($properties as $p): include __DIR__ . '/includes/property-card.php'; endforeach; ?>
        </div>

        <?php if ($pg['totalPages'] > 1): ?>
          <div class="pagination">
            <?php if ($pg['page'] > 1): ?>
              <a href="<?= qs(['page' => $pg['page'] - 1]) ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pg['totalPages']; $i++): ?>
              <?php if ($i === $pg['page']): ?>
                <span class="active"><?= $i ?></span>
              <?php else: ?>
                <a href="<?= qs(['page' => $i]) ?>"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($pg['page'] < $pg['totalPages']): ?>
              <a href="<?= qs(['page' => $pg['page'] + 1]) ?>">&raquo;</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
