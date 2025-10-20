<?php
require_once __DIR__ . '/database.php';

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_user(): ?array
{
    ensure_session_started();
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || !$user['is_admin']) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Forbidden';
        exit;
    }
}

function authenticate(string $email, string $password): ?array
{
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        ensure_session_started();
        $_SESSION['user'] = $user;
        return $user;
    }
    return null;
}

function logout(): void
{
    ensure_session_started();
    $_SESSION = [];
    session_destroy();
}
