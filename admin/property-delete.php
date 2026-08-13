<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/properties.php');
}
verifyCsrf();

$user = currentUser();
$id = (int)($_POST['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    $property = $stmt->fetch();

    if ($property && (isAdmin() || (int)$property['employee_id'] === (int)$user['id'])) {
        $imgs = $pdo->prepare('SELECT image_path FROM property_images WHERE property_id = ?');
        $imgs->execute([$id]);
        foreach ($imgs->fetchAll() as $img) {
            $fullPath = __DIR__ . '/../' . $img['image_path'];
            if (is_file($fullPath) && strpos(realpath($fullPath) ?: '', realpath(UPLOAD_DIR)) === 0) {
                @unlink($fullPath);
            }
        }
        $pdo->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);
        flash('success', 'Da xoa bat dong san.');
    } else {
        flash('error', 'Ban khong co quyen xoa bat dong san nay.');
    }
}

redirect(BASE_URL . '/admin/properties.php');
