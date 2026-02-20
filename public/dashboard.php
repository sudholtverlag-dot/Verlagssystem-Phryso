<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view.php';

$user = requireLogin();
$issues = getIssues();
$selectedIssueId = isset($_GET['heft_id']) ? (int) $_GET['heft_id'] : ($issues[0]['id'] ?? 0);
$selectedIssue = $selectedIssueId ? getIssueById($selectedIssueId) : null;
$articles = getArticlesForUser($user, $selectedIssueId ?: null);
$totalPages = $selectedIssueId ? issuePageSum($selectedIssueId) : 0;
$statusClass = getStatusColor($totalPages);
$progress = min(100, ($totalPages / 72) * 100);

renderHeader('Dashboard', $user);
?>
<h1>Dashboard</h1>
<div class="grid">
    <section class="card">
        <h2>Heftauswahl</h2>
        <form method="get">
            <select name="heft_id" onchange="this.form.submit()">
                <?php foreach ($issues as $issue): ?>
                    <option value="<?= (int) $issue['id'] ?>" <?= $selectedIssueId === (int) $issue['id'] ? 'selected' : '' ?>>
                        <?= h($issue['heftnummer']) ?> – <?= h($issue['titel']) ?> (<?= h($issue['status']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </section>

    <section class="card <?= h($statusClass) ?>">
        <h2>Produktionsstatus</h2>
        <?php if ($selectedIssue): ?>
            <p><strong><?= h($selectedIssue['heftnummer']) ?></strong>: <?= h($selectedIssue['titel']) ?></p>
            <p>Gesamtseiten: <strong><?= h((string) $totalPages) ?></strong></p>
            <div class="progress"><span style="width: <?= h((string) $progress) ?>%"></span></div>
            <small>Zielbereich: 56 bis 72 Seiten</small>
        <?php else: ?>
            <p>Noch kein Heft vorhanden.</p>
        <?php endif; ?>
    </section>

    <section class="card full-width">
        <h2>Beiträge</h2>
        <table>
            <thead><tr><th>Titel</th><th>Heft</th><th>Autor</th><th>Wörter</th><th>Seiten</th><th>Aktion</th></tr></thead>
            <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= h($article['ueberschrift']) ?></td>
                    <td><?= h($article['heftnummer']) ?></td>
                    <td><?= h($article['username']) ?></td>
                    <td><?= (int) $article['word_count'] ?></td>
                    <td><?= h((string) $article['calculated_pages']) ?></td>
                    <td>
                        <a href="/beitrag_form.php?id=<?= (int) $article['id'] ?>">Bearbeiten</a>
                        <form method="post" action="/beitrag_delete.php" class="inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int) $article['id'] ?>">
                            <button type="submit" class="link danger">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <?php if ($user['role'] === 'admin'): ?>
    <section class="card">
        <h2>Benutzerbereich</h2>
        <p>Redakteure: <?= totalEditors() ?> / 4</p>
        <a class="button" href="/user_management.php">Benutzer verwalten</a>
    </section>
    <?php endif; ?>
</div>
<?php renderFooter(); ?>
