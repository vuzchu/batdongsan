<?php
/**
 * Cac ham tien ich dung chung.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price, string $unit = 'total'): string
{
    if ($price >= 1000000000) {
        $formatted = rtrim(rtrim(number_format($price / 1000000000, 2, '.', ''), '0'), '.');
        $text = $formatted . ' ty';
    } elseif ($price >= 1000000) {
        $formatted = rtrim(rtrim(number_format($price / 1000000, 1, '.', ''), '0'), '.');
        $text = $formatted . ' trieu';
    } else {
        $text = number_format($price, 0, ',', '.') . ' d';
    }
    return $unit === 'month' ? $text . '/thang' : $text;
}

function slugify(string $text): string
{
    $text = trim($text);
    $unicode = [
        'a' => '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/u',
        'e' => '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/u',
        'i' => '/ì|í|ị|ỉ|ĩ/u',
        'o' => '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/u',
        'u' => '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/u',
        'y' => '/ỳ|ý|ỵ|ỷ|ỹ/u',
        'd' => '/đ/u',
    ];
    $text = mb_strtolower($text, 'UTF-8');
    foreach ($unicode as $latin => $pattern) {
        $text = preg_replace($pattern, $latin, $text);
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'bat-dong-san';
}

function uniqueSlug(PDO $pdo, string $table, string $base): string
{
    $slug = $base;
    $i = 1;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE slug = ?");
    while (true) {
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }
        $i++;
        $slug = $base . '-' . $i;
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Phien lam viec khong hop le. Vui long tai lai trang va thu lai.');
    }
}

function paginate(int $total, int $page, int $perPage): array
{
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    return [
        'page' => $page,
        'totalPages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
    ];
}
