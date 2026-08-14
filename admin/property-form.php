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
        flash('error', 'Không tìm thấy bất động sản.');
        redirect(BASE_URL . '/admin/properties.php');
    }
    if (!isAdmin() && (int)$property['employee_id'] !== (int)$user['id']) {
        http_response_code(403);
        die('Bạn không có quyền sửa bất động sản này.');
    }
}

$types = $pdo->query('SELECT * FROM property_types ORDER BY id')->fetchAll();
$cities = $pdo->query('SELECT * FROM cities ORDER BY name')->fetchAll();
$districts = $pdo->query('SELECT * FROM districts ORDER BY name')->fetchAll();
$employees = isAdmin() ? $pdo->query("SELECT id, full_name FROM users WHERE role = 'employee' ORDER BY full_name")->fetchAll() : [];
$amenitiesList = $pdo->query('SELECT id, name FROM amenities ORDER BY name')->fetchAll();

$existingAmenityIds = [];
$existingImages = [];
if ($property) {
    $a = $pdo->prepare('SELECT amenity_id FROM property_amenities WHERE property_id = ?');
    $a->execute([$id]);
    $existingAmenityIds = array_map('intval', array_column($a->fetchAll(), 'amenity_id'));

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
    $selectedAmenityIds = array_map('intval', $_POST['amenities'] ?? []);

    if (isAdmin()) {
        $form['employee_id'] = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
    } else {
        $form['employee_id'] = $user['id'];
    }

    if ($form['title'] === '') $errors[] = 'Vui lòng nhập tiêu đề.';
    if (!$form['property_type_id']) $errors[] = 'Vui lòng chọn loại bất động sản.';
    if (!$form['city_id']) $errors[] = 'Vui lòng chọn thành phố.';
    if (!$form['district_id']) $errors[] = 'Vui lòng chọn quận/huyện.';
    if ($form['price'] <= 0) $errors[] = 'Vui lòng nhập giá hợp lệ.';

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
        if ($selectedAmenityIds) {
            $insA = $pdo->prepare('INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)');
            foreach ($selectedAmenityIds as $amenityId) {
                $insA->execute([$id, $amenityId]);
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

        // Upload anh moi len ImgBB
        if (!empty($_FILES['images']['name'][0])) {
            $hasPrimaryStmt = $pdo->prepare('SELECT 1 FROM property_images WHERE property_id = ? AND is_primary = 1');
            $hasPrimaryStmt->execute([$id]);
            $hasPrimary = (bool)$hasPrimaryStmt->fetchColumn();
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $failedUploads = [];

            foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                if (!is_uploaded_file($tmpName)) continue;
                $originalName = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) continue;
                $hostedUrl = uploadImageToImgbb($tmpName);
                if ($hostedUrl !== false) {
                    $isPrimary = !$hasPrimary ? 1 : 0;
                    $hasPrimary = true;
                    $pdo->prepare('INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?, ?, ?)')
                        ->execute([$id, $hostedUrl, $isPrimary]);
                } else {
                    $failedUploads[] = $originalName;
                }
            }
            if ($failedUploads) {
                flash('error', 'Không thể upload lên ImgBB: ' . implode(', ', $failedUploads));
            }
        }

        flash('success', $property ? 'Đã cập nhật bất động sản.' : 'Đã đăng tin bất động sản mới.');
        redirect(BASE_URL . '/admin/properties.php');
    }
}

$pageTitle = $property ? 'Sửa bất động sản' : 'Đăng tin mới';
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
      <label>Tiêu đề tin đăng</label>
      <input type="text" name="title" required value="<?= e($form['title']) ?>">
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <textarea name="description" rows="4"><?= e($form['description']) ?></textarea>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Loại bất động sản</label>
        <select name="property_type_id" required>
          <option value="">-- Chọn --</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= (int)$form['property_type_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Loại giao dịch</label>
        <select name="transaction_type">
          <option value="sale" <?= $form['transaction_type'] === 'sale' ? 'selected' : '' ?>>Mua bán</option>
          <option value="rent" <?= $form['transaction_type'] === 'rent' ? 'selected' : '' ?>>Cho thuê</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Giá (VND)</label>
        <input type="text" inputmode="decimal" class="number-thousands" name="price" required value="<?= e((string)$form['price']) ?>">
      </div>
      <div class="form-group">
        <label>Đơn vị giá</label>
        <select name="price_unit">
          <option value="total" <?= $form['price_unit'] === 'total' ? 'selected' : '' ?>>Tổng giá</option>
          <option value="month" <?= $form['price_unit'] === 'month' ? 'selected' : '' ?>>Giá / tháng</option>
        </select>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Tỉnh / Thành phố</label>
        <select name="city_id" id="city_id" required>
          <option value="">-- Chọn --</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (int)$form['city_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Phường / Xã</label>
        <select name="district_id" id="district_id" required>
          <option value="">-- Chọn --</option>
          <?php foreach ($districts as $d): ?>
            <option value="<?= (int)$d['id'] ?>" data-city="<?= (int)$d['city_id'] ?>" <?= (int)$form['district_id'] === (int)$d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label>Địa chỉ cụ thể</label>
      <input type="text" name="address" value="<?= e($form['address']) ?>" placeholder="Số nhà, tên đường">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Diện tích (m²)</label>
        <input type="text" inputmode="decimal" class="number-thousands" name="area" value="<?= e((string)$form['area']) ?>">
      </div>
      <div class="form-group">
        <label>Số tầng</label>
        <input type="number" name="floors" value="<?= e((string)$form['floors']) ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Phòng ngủ</label>
        <input type="number" name="bedrooms" value="<?= e((string)$form['bedrooms']) ?>">
      </div>
      <div class="form-group">
        <label>Phòng tắm</label>
        <input type="number" name="bathrooms" value="<?= e((string)$form['bathrooms']) ?>">
      </div>
    </div>

    <div class="form-group">
      <label>Tiện ích / dịch vụ</label>
      <?php if (empty($amenitiesList)): ?>
        <p style="color:var(--text-muted);font-size:13px;">Chưa có tiện ích nào trong hệ thống. <a href="<?= BASE_URL ?>/admin/amenity-form.php">Thêm tiện ích</a>.</p>
      <?php else: ?>
        <div class="checkbox-grid">
          <?php foreach ($amenitiesList as $am): ?>
            <label>
              <input type="checkbox" name="amenities[]" value="<?= (int)$am['id'] ?>" <?= in_array((int)$am['id'], $existingAmenityIds, true) ? 'checked' : '' ?>>
              <?= e($am['name']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Trạng thái</label>
        <select name="status">
          <?php foreach (['available' => 'Còn hiệu lực', 'pending' => 'Chờ duyệt', 'sold' => 'Đã bán', 'rented' => 'Đã cho thuê'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $form['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (isAdmin()): ?>
        <div class="form-group">
          <label>Nhân viên phụ trách</label>
          <select name="employee_id">
            <option value="">-- Chưa phân công --</option>
            <?php foreach ($employees as $emp): ?>
              <option value="<?= (int)$emp['id'] ?>" <?= (int)$form['employee_id'] === (int)$emp['id'] ? 'selected' : '' ?>><?= e($emp['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($existingImages): ?>
      <div class="form-group">
        <label>Hình ảnh hiện tại (chọn để xóa)</label>
        <div class="current-images">
          <?php foreach ($existingImages as $img): ?>
            <label style="display:flex;flex-direction:column;align-items:center;gap:4px;font-size:11px;">
              <img src="<?= e(imageUrl($img['image_path'])) ?>" alt="">
              <span><input type="checkbox" name="delete_images[]" value="<?= (int)$img['id'] ?>" style="width:auto;"> Xóa</span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <label>Thêm hình ảnh mới</label>
      <input type="file" name="images[]" multiple accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary"><?= $property ? 'Cập nhật' : 'Đăng tin' ?></button>
    <a href="<?= BASE_URL ?>/admin/properties.php" class="btn btn-outline">Hủy</a>
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

  function formatThousands(input) {
    var digitsBeforeCursor = input.value.slice(0, input.selectionStart).replace(/\D/g, '').length;

    var parts = input.value.replace(/,/g, '').split('.');
    var intPart = parts[0].replace(/\D/g, '');
    var decPart = parts.length > 1 ? '.' + parts[1].replace(/\D/g, '').slice(0, 2) : '';
    var formatted = intPart === '' ? decPart : Number(intPart).toLocaleString('en-US') + decPart;
    input.value = formatted;

    var pos = 0, digitCount = 0;
    while (pos < formatted.length && digitCount < digitsBeforeCursor) {
      if (/\d/.test(formatted[pos])) digitCount++;
      pos++;
    }
    input.setSelectionRange(pos, pos);
  }

  document.querySelectorAll('.number-thousands').forEach(function (input) {
    formatThousands(input);
    input.addEventListener('input', function () { formatThousands(input); });
  });
});
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
