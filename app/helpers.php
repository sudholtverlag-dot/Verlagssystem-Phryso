<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function pageCountFromMetrics(int $wordCount, int $imageCount, bool $titelbild): int
{
    $pages = ($wordCount / 900) + ($imageCount * 0.15) + ($titelbild ? 0.5 : 0);
    return (int) ceil($pages);
}

function calculateWordCount(string $content): int
{
    $trimmed = trim(strip_tags($content));
    if ($trimmed === '') {
        return 0;
    }

    preg_match_all('/\b\p{L}[\p{L}\p{N}_-]*\b/u', $trimmed, $matches);
    return count($matches[0]);
}

function getStatusColor(float $pages): string
{
    if ($pages < 56) {
        return 'status-red';
    }

    if ($pages > 72) {
        return 'status-orange';
    }

    return 'status-green';
}

function validateUpload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Datei konnte nicht hochgeladen werden.';
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        return 'Datei ist größer als 5 MB.';
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        return 'Nur JPG, PNG oder WEBP sind erlaubt.';
    }

    return null;
}
