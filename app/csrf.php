<?php

declare(strict_types=1);

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf(): void
{
    $incoming = $_POST['csrf_token'] ?? '';
    $token = $_SESSION['csrf_token'] ?? '';
    if (!is_string($incoming) || !is_string($token) || !hash_equals($token, $incoming)) {
        http_response_code(419);
        exit('Ungültiges CSRF-Token.');
    }
}
