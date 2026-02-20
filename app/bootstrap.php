<?php

declare(strict_types=1);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';

$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo '<h1>Konfigurationsdatei fehlt</h1>';
    echo '<p>Bitte kopiere <code>/config/config.php.sample</code> nach <code>/config/config.php</code> und trage die Zugangsdaten ein.</p>';
    exit;
}

$config = require $configFile;

if (!isset($config['db'])) {
    throw new RuntimeException('Datenbankkonfiguration fehlt.');
}

function db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $config;
    $db = $config['db'];
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);

    $pdo = new PDO($dsn, $db['user'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/repositories.php';
