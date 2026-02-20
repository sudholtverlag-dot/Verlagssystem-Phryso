<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view.php';

if (currentUser()) {
    redirect('/dashboard.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attemptLogin($username, $password)) {
        redirect('/dashboard.php');
    }
    $error = 'Login fehlgeschlagen.';
}

renderHeader('Login', null);
?>
<section class="auth-card">
    <h1>PHRYSO Login</h1>
    <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
    <form method="post">
        <?= csrfField() ?>
        <label>Benutzername<input required name="username" type="text"></label>
        <label>Passwort<input required name="password" type="password"></label>
        <button type="submit">Anmelden</button>
    </form>
</section>
<?php renderFooter(); ?>
