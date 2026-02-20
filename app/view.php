<?php

declare(strict_types=1);

function renderHeader(string $title, ?array $user): void
{
    ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?> · PHRYSO CMS</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php if ($user): ?>
    <header class="topbar">
        <div><strong>PHRYSO Produktions-CMS</strong></div>
        <nav>
            <a href="/dashboard.php">Dashboard</a>
            <a href="/beitrag_form.php">Neuer Beitrag</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="/hefte.php">Hefte</a>
                <a href="/user_management.php">Benutzer</a>
            <?php endif; ?>
            <a href="/logout.php">Logout</a>
        </nav>
    </header>
<?php endif; ?>
<main class="container">
    <?php
}

function renderFooter(): void
{
    ?>
</main>
<script src="/assets/js/app.js"></script>
</body>
</html>
    <?php
}
