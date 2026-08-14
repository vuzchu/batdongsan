<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/amenities.php');
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $pdo->prepare('DELETE FROM amenities WHERE id = ?')->execute([$id]);
    flash('success', 'Đã xóa tiện ích.');
}

redirect(BASE_URL . '/admin/amenities.php');
