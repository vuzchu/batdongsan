<?php
/**
 * Dong bo bang cities/districts tu provinces.open-api.vn theo cau truc hanh chinh
 * moi (Tinh/Thanh pho -> Phuong/Xa, khong con cap quan/huyen tu 1/7/2025).
 * Chay thu cong khi co thay doi dia gioi hanh chinh: php database/sync-provinces.php
 * An toan chay lai nhieu lan (upsert theo cot "code").
 */

require_once __DIR__ . '/../config/database.php';

function fetchJson(string $url): array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($curl);
    if ($body === false) {
        fwrite(STDERR, 'Loi ket noi API (' . $url . '): ' . curl_error($curl) . "\n");
        exit(1);
    }
    curl_close($curl);

    $data = json_decode($body, true);
    if (!is_array($data)) {
        fwrite(STDERR, "Phan hoi API khong hop le tu {$url}\n");
        exit(1);
    }
    return $data;
}

echo "Dang lay danh sach tinh/thanh tu provinces.open-api.vn...\n";
$provinces = fetchJson('https://provinces.open-api.vn/api/v2/p/');

$cityUpsert = $pdo->prepare('INSERT INTO cities (code, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
$cityIdByCode = $pdo->prepare('SELECT id FROM cities WHERE code = ?');
$wardUpsert = $pdo->prepare('INSERT INTO districts (code, city_id, name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), city_id = VALUES(city_id)');

$totalWards = 0;
foreach ($provinces as $p) {
    $cityUpsert->execute([$p['code'], $p['name']]);
    $cityIdByCode->execute([$p['code']]);
    $cityId = (int)$cityIdByCode->fetchColumn();

    echo "  - {$p['name']} (code {$p['code']})...\n";
    $detail = fetchJson("https://provinces.open-api.vn/api/v2/p/{$p['code']}?depth=2");
    foreach ($detail['wards'] ?? [] as $w) {
        $wardUpsert->execute([$w['code'], $cityId, $w['name']]);
        $totalWards++;
    }
}

echo "Hoan tat: " . count($provinces) . " tinh/thanh, {$totalWards} phuong/xa.\n";
