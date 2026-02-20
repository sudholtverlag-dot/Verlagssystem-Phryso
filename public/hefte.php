<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view.php';

$user = requireAdmin();
$error = null;
$editIssue = null;

if (isset($_GET['edit'])) {
    $editIssue = getIssueById((int) $_GET['edit']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $heftnummer = trim((string) ($_POST['heftnummer'] ?? ''));
    $titel = trim((string) ($_POST['titel'] ?? ''));
    $status = (string) ($_POST['status'] ?? 'planung');

    if ($heftnummer === '' || $titel === '' || !in_array($status, ['planung', 'offen', 'geschlossen'], true)) {
        $error = 'Ungültige Eingabe.';
    } else {
        if ($id > 0) {
            updateIssue($id, $heftnummer, $titel, $status);
        } else {
            createIssue($heftnummer, $titel, $status);
        }
        redirect('/hefte.php');
    }
}

$issues = getIssues();
renderHeader('Heftverwaltung', $user);
?>
<div class="grid two-col">
    <section class="card">
        <h1><?= $editIssue ? 'Heft bearbeiten' : 'Neues Heft anlegen' ?></h1>
        <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) ($editIssue['id'] ?? 0) ?>">
            <label>Heftnummer<input required type="text" name="heftnummer" value="<?= h((string) ($editIssue['heftnummer'] ?? '')) ?>"></label>
            <label>Titel<input required type="text" name="titel" value="<?= h((string) ($editIssue['titel'] ?? '')) ?>"></label>
            <label>Status
                <select name="status">
                    <?php foreach (['planung', 'offen', 'geschlossen'] as $status): ?>
                        <option value="<?= h($status) ?>" <?= (($editIssue['status'] ?? 'planung') === $status) ? 'selected' : '' ?>><?= h($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Speichern</button>
        </form>
    </section>

    <section class="card">
        <h2>Alle Hefte</h2>
        <table>
            <thead><tr><th>Heft</th><th>Titel</th><th>Status</th><th>Seiten</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($issues as $issue): ?>
                <tr>
                    <td><?= h($issue['heftnummer']) ?></td>
                    <td><?= h($issue['titel']) ?></td>
                    <td><?= h($issue['status']) ?></td>
                    <td><?= h((string) issuePageSum((int) $issue['id'])) ?></td>
                    <td><a href="/hefte.php?edit=<?= (int) $issue['id'] ?>">Bearbeiten</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<?php renderFooter(); ?>
