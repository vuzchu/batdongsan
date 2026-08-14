<?php
/**
 * Ket noi database dung PDO. Cau hinh qua bien moi truong, co gia tri mac dinh cho moi truong local.
 */

// Gia tri mac dinh khop voi cau hinh MySQL cua XAMPP (host localhost, user root, khong mat khau).
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'batdongsan';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra lại cấu hình trong config/database.php');
}
