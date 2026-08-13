<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$user = currentUser();
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$property = null;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    if (!$property) {
        flash('error', 'Khong tim thay bat dong san.');
        redirect(BASE_URL . '/admin/properties.php');
    }
    if (!isAdmin() && (int)$property['employee_id'] !== (int)$user['id']) {
        http_response_code(403);
        die('Ban khong co quyen sua bat dong san nay.');
    }
}

$types = $pdo->query('SELECT * FROM property_types ORDER BY id')->fetchAll();
$cities = $pdo->query('SELECT * FROM cities ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT * FROM districts ORDER BY name')->fetchAll();
$employees = isAdmin() ? $pdo->query("SELECT id, full_name FROM users WHERE role = 'employee' ORDER BY full_name")->fetchAll() : [];

$existingAmenities = [];
$existingImages = [];
if ($property) {
    $a = $pdo->prepare('SELECT name FROM property_amenities WHERE property_id = ?');
    $a->execute([$id]);
    $existingAmenities = array_column($a->fetchAll(), 'name');

    $im = $pdo->prepare('SELECT id, image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, id ASC');
    $im->execute([$id]);
    $existingImages = $im->fetchAll();
}

$form = [
    'title' => $property['title'] ?? '',
    'description' => $property['description'] ?? '',
    'property_type_id' => $property['property_type_id'] ?? '',
    'transaction_type' => $property['transaction_type'] ?? 'sale',
    'price' => $property['price'] ?? '',
    'price_unit' => $property['price_unit'] ?? 'total',
    'city_id' => $property['city_id'] ?? '',
    'district_id' => $property['district_id'] ?? '',
    'address' => $property['address'] ?? '',
    'area' => $property['area'] ?? '',
    'bedrooms' => $property['bedrooms'] ?? 0,
    'bathrooms' => $property['bathrooms'] ?? 0,
    'floors' => $property['floors'] ?? 1,
    'status' => $property['status'] ?? 'available',
    'employee_id' => $property['employee_id'] ?? (!isAdmin() ? $user['id'] : ''),
    'amenities' => implode(', ', $existingAmenities),
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach (['title', 'description', 'transaction_type', 'price_unit', 'address', 'status'] as $f) {
        $form[$f] = trim($_POST[$f] ?? '');
    }
    foreach (['property_type_id', 'city_id', 'district_id', 'bedrooms', 'bathrooms', 'floors'] as $f) {
        $form[$f] = (int)($_POST[$f] ?? 0);
    }
    $form['price'] = (float)str_replace(',', '', $_POST['price'] ?? '0');
    $form['area'] = (float)str_replace(',', '', $_POST['area'] ?? '0');
    $form['amenities'] = trim($_POST['amenities'] ?? '');

    if (isAdmin()) {
        $form['employee_id'] = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
    } else {
        $form['employee_id'] = $user['id'];
    }

    if ($form['title'] === '') $errors[] = 'Vui long nhap tieu de.';
    if (!$form['property_type_id']) $errors[] = 'Vui long chon loai bat dong san.';
    if (!$form['city_id']) $errors[] = 'Vui long chon thanh pho.';
    if (!$form['district_id']) $errors[] = 'Vui long chon quan/huyen.';
    if ($form['price'] <= 0) $errors[] = 'Vui long nhap gia hop le.';

    if (!$errors) {
        if ($property) {
            $slug = $property['slug'];
            $stmt = $pdo->prepare("
                UPDATE properties SET title=?, slug=?, description=?, property_type_id=?, transaction_type=?, price=?,
                price_unit=?, city_id=?, district_id=?, address=?, area=?, bedrooms=?, bathrooms=?, floors=?, status=?, employee_id=?
                WHERE id=?
            ");
            $stmt->execute([
                $form['title'], $slug, $form['description'], $form['property_type_id'], $form['transaction_type'], $form['price'],
                $form['price_unit'], $form['city_id'], $form['district_id'], $form['address'], $form['area'],
                $form['bedrooms'], $form['bathrooms'], $form['floors'], $form['status'], $form['employee_id'], $id,
            ]);
        } else {
            $slug = uniqueSlug($pdo, 'properties', slugify($form['title']));
            $stmt = $pdo->prepare("
                INSERT INTO properties (title, slug, description, property_type_id, transaction_type, price, price_unit,
                city_id, district_id, address, area, bedrooms, bathrooms, floors, status, employee_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $form['title'], $slug, $form['description'], $form['property_type_id'], $form['transaction_type'], $form['price'],
                $form['price_unit'], $form['city_id'], $form['district_id'], $form['address'], $form['area'],
                $form['bedrooms'], $form['bathrooms'], $form['floors'], $form['status'], $form['employee_id'],
            ]);
            $id = (int)$pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM property_amenities WHERE property_id = ?')->execute([$id]);
        $amenityNames = array_filter(array_map('trim', explode(',', $form['amenities'])));
        if ($amenityNames) {
            $insA = $pdo->prepare('INSERT INTO property_amenities (property_id, name) VALUES (?, ?)');
            foreach ($amenityNames as $name) {
                $insA->execute([$id, $name]);
            }
        }

        // Xoa anh duoc chon
        if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imgId) {
                $imgId = (int)$imgId;
                $fetch = $pdo->prepare('SELECT image_path FROM property_images WHERE id = ? AND property_id = ?');
                $fetch->execute([$imgId, $id]);
                if ($row = $fetch->fetch()) {
                    $fullPath = __DIR__ . '/../' . $row['image_path'];
                    if (is_file($fullPath) && strpos(realpath($fullPath) ?: '', realpath(UPLOAD_DIR)) === 0) {
                        @unlink($fullPath);
                    }
                    $pdo->prepare('DELETE FROM property_images WHERE id = ?')->execute([$imgId]);
                }
            }
        }

        // Upload anh moi
        if (!empty($_FILES['images']['name'][0])) {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            $hasPrimaryStmt = $pdo->prepare('SELECT 1 FROM property_images WHERE property_id = ? AND is_primary = 1');
            $hasPrimaryStmt->execute([$id]);
            $hasPrimary = (bool)$hasPrimaryStmt->fetchColumn();
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                if (!is_uploaded_file($tmpName)) continue;
                $originalName = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) continue;
                $newName = 'prop-' . $id . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destPath = UPLOAD_DIR . $newName;
                if (move_uploaded_file($tmpName, $destPath)) {
                    $isPrimary = !$hasPrimary ? 1 : 0;
                    $hasPrimary = true;
                    $pdo->prepare('INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)')
                        ->execute([$id, UPLOAD_URL . $newName, $isPrimary]);
                }
            }
        }

        flash('success', $property ? 'Da cap nhat bat dong san.' : 'Da dang tin bat dong san moi.');
        redirect(BASE_URL . '/admin/properties.php');
    }
}

$pageTitle = $property ? 'Sua bat dong san' : 'Dang tin moi';
$activeNav = 'properties';
require_once __DIR__ . '/includes/header.php';
?>
<div class="panel form-card">
  <?php if ($errors): ?>
    <div class="alert alert-error"><?php foreach ($errors as $err) echo e($err) . '<br>'; ?></div>
  <?php endif; ?>
  <form method="post" action="" enctype="multipart/form-data">
    <?= csrfField() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="form-group">
      <label>Tieu de tin dang</label>
      <input type="text" name="title" required value="<?= e($form['title']) ?>">
    </div>

    <div class="form-group">
      <label>Mo ta</label>
      <textarea name="description" rows="4"><?= e($form['description']) ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Loai bat dong san</label>
        <select name="property_type_id" required>
          <option value="">-- Chon --</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)$form['property_type_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Loai giao dich</label>
        <select name="transaction_type">
          <option value="sale" <?= $form['transaction_type'] === 'sale' ? 'selected' : '' ?>>Mua ban</option>
          <option value="rent" <?= $form['transaction_type'] === 'rent' ? 'selected' : '' ?>>Cho thue</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Gia (VND)</label>
        <input type="number" step="0.01" name="price" required value="<?= e((string)$form['price']) ?>">
      </div>
      <div class="form-group">
        <label>Don vi gia</label>
        <select name="price_unit">
          <option value="total" <?= $form['price_unit'] === 'total' ? 'selected' : '' ?>>Tong gia</option>
          <option value="month" <?= $form['price_unit'] === 'month' ? 'selected' : '' ?>>Gia / thang</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tinh / Thanh pho</label>
        <select name="city_id" id="city_id" required>
          <option value="">-- Chon --</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$form['city_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Quan / Huyen</label>
        <select name="district_id" id="district_id" required>
          <option value="">-- Chon --</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= (int)$d['id'] ?>" data-city="<?= (int)$d['city_id'] ?>" <?= (int)$form['district_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Dia chi cu the</label>
      <input type="text" name="address" value="<?= e($form['address']) ?>" placeholder="So nha, ten duong">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Dien tich (m2)</label>
        <input type="number" step="0.1" name="area" value="<?= e((string)$form['area']) ?>">
      </div>
      <div class="form-group">
        <label>So tang</label>
        <input type="number" name="floors" value="<?= e((string)$form['floors']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Phong ngu</label>
        <input type="number" name="bedrooms" value="<?= e((string)$form['bedrooms']) ?>">
      </div>
      <div class="form-group">
        <label>Phong tam</label>
        <input type="number" name="bathrooms" value="<?= e((string)$form['bathrooms']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Tien ich / dich vu (cach nhau boi dau phay)</label>
      <input type="text" name="amenities" value="<?= e($form['amenities']) ?>" placeholder="Ho boi, Gym, Bao ve 24/7">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Trang thai</label>
        <select name="status">
          <?php foreach (['available' => 'Con hieu luc', 'pending' => 'Cho duyet', 'sold' => 'Da ban', 'rented' => 'Da cho thue'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $form['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (isAdmin()): ?>
        <div class="form-group">
          <label>Nhan vien phu trach</label>
          <select name="employee_id">
            <option value="">-- Chua phan cong --</option>
            <?php foreach ($employees as $emp): ?>
              <option value="<?= (int)$emp['id'] ?>" <?= (int)$form['employee_id'] === (int)$emp['id'] ? 'selected' : '' ?>><?= e($emp['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($existingImages): ?>
      <div class="form-group">
        <label>Hinh anh hien tai (chon de xoa)</label>
        <div class="current-images">
          <?php foreach ($existingImages as $img): ?>
            <label style="display:flex;flex-direction:column;align-items:center;gap:4px;font-size:11px;">
              <img src="<?= BASE_URL ?>/<?= e($img['image_path']) ?>" alt="">
              <span><input type="checkbox" name="delete_images[]" value="<?= (int)$img['id'] ?>" style="width:auto;"> Xoa</span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label>Them hinh anh moi</label>
      <input type="file" name="images[]" multiple accept="image/*,.svg">
    </div>

    <button type="submit" class="btn btn-primary"><?= $property ? 'Cap nhat' : 'Dang tin' ?></button>
    <a href="<?= BASE_URL ?>/admin/properties.php" class="btn btn-outline">Huy</a>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var citySelect = document.getElementById('city_id');
  var districtSelect = document.getElementById('district_id');
  var allDistricts = Array.prototype.slice.call(districtSelect.options);

  function filterDistricts() {
    var cityId = citySelect.value;
    var current = districtSelect.value;
    districtSelect.innerHTML = '';
    allDistricts.forEach(function (opt) {
      if (opt.value === '' || opt.dataset.city === cityId) {
        districtSelect.appendChild(opt.cloneNode(true));
      }
    });
    if ([...districtSelect.options].some(o => o.value === current)) {
      districtSelect.value = current;
    }
  }

  citySelect.addEventListener('change', filterDistricts);
  filterDistricts();
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
