<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$user = requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/dashboard.php');
}

verifyCsrf();
$id = (int) ($_POST['id'] ?? 0);
$article = $id ? getArticleById($id) : null;

if (!$article) {
    redirect('/dashboard.php');
}

if ($user['role'] !== 'admin' && (int) $article['user_id'] !== (int) $user['id']) {
    http_response_code(403);
    exit('Kein Zugriff.');
}

deleteArticle($id);
redirect('/dashboard.php?heft_id=' . (int) $article['heft_id']);
