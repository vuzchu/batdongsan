<?php
require_once __DIR__ . '/../config/config.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/employees.php');
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $pdo->prepare('UPDATE properties SET employee_id = NULL WHERE employee_id = ?')->execute([$id]);
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
    $stmt->execute([$id]);
    flash('success', 'Đã xóa nhân viên.');
}

redirect(BASE_URL . '/admin/employees.php');
