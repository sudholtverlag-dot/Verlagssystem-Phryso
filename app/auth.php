<?php

declare(strict_types=1);

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        redirect('/index.php');
    }

    return $user;
}

function requireAdmin(): array
{
    $user = requireLogin();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Kein Zugriff.');
    }

    return $user;
}

function attemptLogin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}
