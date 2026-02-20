<?php

declare(strict_types=1);

function getIssues(): array
{
    return db()->query('SELECT * FROM hefte ORDER BY id DESC')->fetchAll();
}

function getIssueById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM hefte WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

function createIssue(string $heftnummer, string $titel, string $status): void
{
    $stmt = db()->prepare('INSERT INTO hefte (heftnummer, titel, status) VALUES (:heftnummer, :titel, :status)');
    $stmt->execute(compact('heftnummer', 'titel', 'status'));
}

function updateIssue(int $id, string $heftnummer, string $titel, string $status): void
{
    $stmt = db()->prepare('UPDATE hefte SET heftnummer = :heftnummer, titel = :titel, status = :status WHERE id = :id');
    $stmt->execute(compact('id', 'heftnummer', 'titel', 'status'));
}

function getArticlesForUser(array $user, ?int $issueId = null): array
{
    $where = [];
    $params = [];

    if ($issueId) {
        $where[] = 'b.heft_id = :heft_id';
        $params['heft_id'] = $issueId;
    }

    if ($user['role'] !== 'admin') {
        $where[] = 'b.user_id = :user_id';
        $params['user_id'] = (int) $user['id'];
    }

    $sql = 'SELECT b.*, h.heftnummer, h.titel AS heft_titel, u.username
            FROM beitraege b
            JOIN hefte h ON h.id = b.heft_id
            JOIN users u ON u.id = b.user_id';

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY b.updated_at DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getArticleById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM beitraege WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}

function saveArticle(array $data): void
{
    if (!empty($data['id'])) {
        $stmt = db()->prepare('UPDATE beitraege
            SET heft_id=:heft_id, user_id=:user_id, ueberschrift=:ueberschrift, subline=:subline,
                content=:content, word_count=:word_count, image_count=:image_count,
                titelbild_flag=:titelbild_flag, calculated_pages=:calculated_pages
            WHERE id=:id');
        $stmt->execute($data);
        return;
    }

    $stmt = db()->prepare('INSERT INTO beitraege
        (heft_id, user_id, ueberschrift, subline, content, word_count, image_count, titelbild_flag, calculated_pages)
        VALUES
        (:heft_id, :user_id, :ueberschrift, :subline, :content, :word_count, :image_count, :titelbild_flag, :calculated_pages)');
    $insertData = $data;
    unset($insertData['id']);
    $stmt->execute($insertData);
}

function deleteArticle(int $id): void
{
    $stmt = db()->prepare('DELETE FROM beitraege WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function issuePageSum(int $issueId): float
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(calculated_pages), 0) AS pages FROM beitraege WHERE heft_id = :id');
    $stmt->execute(['id' => $issueId]);
    return (float) ($stmt->fetch()['pages'] ?? 0);
}

function totalEditors(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM users WHERE role='redakteur'")->fetchColumn();
}
