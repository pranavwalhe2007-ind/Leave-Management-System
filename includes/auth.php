<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    static $user;
    if ($user !== null) {
        return $user;
    }
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $statement = db()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
    $statement->execute([$_SESSION['user_id']]);
    $user = $statement->fetch() ?: null;
    return $user;
}

function require_login(?string $role = null): void
{
    $user = current_user();
    if (!$user) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
    if ($role !== null && $user['role'] !== $role) {
        header('Location: ' . base_url($user['role'] === 'admin' ? 'admin/dashboard.php' : 'employee/dashboard.php'));
        exit;
    }
}

function login_user(string $email, string $password): bool
{
    $statement = db()->prepare('SELECT id, password_hash FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $statement->execute([$email]);
    $user = $statement->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function base_url(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    $root = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    while (in_array(basename($root), ['employee', 'admin', 'includes'], true)) {
        $root = rtrim(str_replace('\\', '/', dirname($root)), '/');
    }
    return ($root ?: '') . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function make_avatar_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $first = preg_replace('/[^A-Za-z]/', '', $parts[0] ?? '');
    $last = preg_replace('/[^A-Za-z]/', '', $parts[count($parts) - 1] ?? '');

    if (count($parts) < 2) {
        return strtoupper(substr((string) $first, 0, 2));
    }

    return strtoupper(substr((string) $first, 0, 1) . substr((string) $last, 0, 1));
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $postToken = $_POST['csrf_token'] ?? '';
    if (!is_string($sessionToken) || !is_string($postToken) || !hash_equals($sessionToken, $postToken)) {
        http_response_code(419);
        exit('Invalid form token. Please go back and try again.');
    }
}

function calculate_days(string $start, string $end): float
{
    $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $start);
    $startErrors = DateTimeImmutable::getLastErrors();
    $endDate = DateTimeImmutable::createFromFormat('!Y-m-d', $end);
    $endErrors = DateTimeImmutable::getLastErrors();

    if (
        !$startDate ||
        !$endDate ||
        ($startErrors !== false && ($startErrors['warning_count'] || $startErrors['error_count'])) ||
        ($endErrors !== false && ($endErrors['warning_count'] || $endErrors['error_count'])) ||
        $startDate->format('Y-m-d') !== $start ||
        $endDate->format('Y-m-d') !== $end
    ) {
        return 0;
    }

    if ($endDate < $startDate) {
        return 0;
    }
    return (float) $startDate->diff($endDate)->days + 1;
}
