<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$path = (string) ($_GET['f'] ?? '');
$path = str_replace(["\0", '..'], '', $path);
$full = BASE_PATH . '/uploads/' . ltrim($path, '/');
$realBase = realpath(BASE_PATH . '/uploads/branding');
$realFile = realpath($full);
if (!$realBase || !$realFile || !str_starts_with($realFile, $realBase) || !is_file($realFile)) {
    http_response_code(404);
    exit;
}

$mime = mime_content_type($realFile) ?: 'application/octet-stream';
if (!str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml' && $mime !== 'text/plain') {
    http_response_code(403);
    exit;
}
header('Content-Type: ' . ($mime === 'text/plain' ? 'image/svg+xml' : $mime));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');
readfile($realFile);
