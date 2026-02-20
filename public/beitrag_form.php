<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/view.php';

$user = requireLogin();
$issues = getIssues();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$article = $id ? getArticleById($id) : null;

if ($article && $user['role'] !== 'admin' && (int) $article['user_id'] !== (int) $user['id']) {
    http_response_code(403);
    exit('Kein Zugriff auf diesen Beitrag.');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $formId = (int) ($_POST['id'] ?? 0);
    $existing = $formId ? getArticleById($formId) : null;
    if ($existing && $user['role'] !== 'admin' && (int) $existing['user_id'] !== (int) $user['id']) {
        http_response_code(403);
        exit('Kein Zugriff auf diesen Beitrag.');
    }

    $heftId = (int) ($_POST['heft_id'] ?? 0);
    $ueberschrift = trim((string) ($_POST['ueberschrift'] ?? ''));
    $subline = trim((string) ($_POST['subline'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $imageCount = max(0, (int) ($_POST['image_count'] ?? 0));
    $titelbild = isset($_POST['titelbild_flag']);
    $wordCount = calculateWordCount($content);
    $pages = pageCountFromMetrics($wordCount, $imageCount, $titelbild);

    $uploadError = validateUpload($_FILES['bild_upload'] ?? []);
    if ($uploadError) {
        $error = $uploadError;
    }

    if (!$error && $ueberschrift !== '' && $content !== '' && $heftId > 0) {
        if (!empty($_FILES['bild_upload']['tmp_name'])) {
            $ext = pathinfo((string) $_FILES['bild_upload']['name'], PATHINFO_EXTENSION);
            $filename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
            move_uploaded_file($_FILES['bild_upload']['tmp_name'], __DIR__ . '/uploads/' . $filename);
        }

        saveArticle([
            'id' => $formId ?: null,
            'heft_id' => $heftId,
            'user_id' => (int) ($existing['user_id'] ?? $user['id']),
            'ueberschrift' => $ueberschrift,
            'subline' => $subline,
            'content' => $content,
            'word_count' => $wordCount,
            'image_count' => $imageCount,
            'titelbild_flag' => $titelbild ? 1 : 0,
            'calculated_pages' => $pages,
        ]);
        redirect('/dashboard.php?heft_id=' . $heftId);
    }

    if (!$error) {
        $error = 'Bitte alle Pflichtfelder ausfüllen.';
    }
}

renderHeader($article ? 'Beitrag bearbeiten' : 'Beitrag anlegen', $user);
?>
<section class="card">
    <h1><?= $article ? 'Beitrag bearbeiten' : 'Neuen Beitrag anlegen' ?></h1>
    <?php if ($error): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data" data-live-pages>
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= (int) ($article['id'] ?? 0) ?>">
        <label>Heft
            <select name="heft_id" required>
                <option value="">Bitte wählen</option>
                <?php foreach ($issues as $issue): ?>
                    <option value="<?= (int) $issue['id'] ?>" <?= ((int) ($article['heft_id'] ?? 0) === (int) $issue['id']) ? 'selected' : '' ?>>
                        <?= h($issue['heftnummer']) ?> – <?= h($issue['titel']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Überschrift<input type="text" required name="ueberschrift" value="<?= h((string) ($article['ueberschrift'] ?? '')) ?>"></label>
        <label>Subline<input type="text" name="subline" value="<?= h((string) ($article['subline'] ?? '')) ?>"></label>
        <label>Inhalt<textarea required name="content" rows="14" data-word-source><?= h((string) ($article['content'] ?? '')) ?></textarea></label>
        <div class="split">
            <label>Kleine Bilder<input type="number" min="0" name="image_count" value="<?= (int) ($article['image_count'] ?? 0) ?>" data-image-source></label>
            <label class="checkbox"><input type="checkbox" name="titelbild_flag" data-title-source <?= !empty($article['titelbild_flag']) ? 'checked' : '' ?>> Titelbild</label>
        </div>
        <label>Optionaler Bildupload (validiert)<input type="file" name="bild_upload" accept="image/png,image/jpeg,image/webp"></label>
        <p class="muted">Wörter: <strong data-word-count><?= (int) ($article['word_count'] ?? 0) ?></strong> · Seiten: <strong data-page-count><?= (int) ($article['calculated_pages'] ?? 0) ?></strong></p>
        <button type="submit">Speichern</button>
    </form>
</section>
<?php renderFooter(); ?>
