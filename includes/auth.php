<?php
/**
 * Xac thuc va phan quyen: admin (quan tri) / employee (nhan vien).
 */

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();
    return $user !== null && $user['role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/login.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('Bạn không có quyền truy cập trang này.');
    }
}

function attemptLogin(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
        ];
        return true;
    }
    return false;
}

function logoutUser(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}
