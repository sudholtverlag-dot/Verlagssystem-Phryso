<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view.php';

$user = requireAdmin();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        if (totalEditors() >= 4) {
            $error = 'Maximum von 4 Redakteuren erreicht.';
        } else {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if ($username === '' || $password === '') {
                $error = 'Bitte Benutzername und Passwort angeben.';
            } else {
                $stmt = db()->prepare('INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)');
                try {
                    $stmt->execute([
                        'username' => $username,
                        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                        'role' => 'redakteur',
                    ]);
                    redirect('/user_management.php');
                } catch (PDOException) {
                    $error = 'Benutzername bereits vergeben.';
                }
            }
        }
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = db()->prepare("DELETE FROM users WHERE id = :id AND role = 'redakteur'");
            $stmt->execute(['id' => $id]);
            redirect('/user_management.php');
        }
    }
}

$editors = db()->query("SELECT id, username, created_at FROM users WHERE role='redakteur' ORDER BY created_at DESC")->fetchAll();
renderHeader('Benutzerverwaltung', $user);
?>
<div class="grid two-col">
    <section class="card">
        <h1>Redakteur anlegen</h1>
        <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create">
            <label>Benutzername<input required type="text" name="username"></label>
            <label>Passwort<input required type="password" name="password"></label>
            <p class="muted">Aktuell: <?= count($editors) ?> / 4</p>
            <button type="submit">Anlegen</button>
        </form>
    </section>
    <section class="card">
        <h2>Redakteure</h2>
        <table>
            <thead><tr><th>Username</th><th>Erstellt</th><th>Aktion</th></tr></thead>
            <tbody>
            <?php foreach ($editors as $editor): ?>
                <tr>
                    <td><?= h($editor['username']) ?></td>
                    <td><?= h($editor['created_at']) ?></td>
                    <td>
                        <form method="post" class="inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $editor['id'] ?>">
                            <button class="link danger" type="submit">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php renderFooter(); ?>
