<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/bootstrap.php';

$path = (string) ($_GET['f'] ?? '');
$path = str_replace(["\0", '..'], '', $path);
$full = BASE_PATH . '/uploads/' . ltrim($path, '/');
$realBase = realpath(BASE_PATH . '/uploads');
$realFile = realpath($full);
if (!$realBase || !$realFile || !str_starts_with($realFile, $realBase) || !is_file($realFile)) {
    http_response_code(404);
    exit;
}

$user = auth_user();
$admin = auth_admin();
if (!$user && !$admin) {
    http_response_code(403);
    exit;
}
if ($user && !$admin) {
    $stmt = db()->prepare('SELECT photo_path FROM user_profiles WHERE user_id = ?');
    $stmt->execute([$user['id']]);
    $own = (string) $stmt->fetchColumn();
    if ($own !== ltrim($path, '/')) {
        http_response_code(403);
        exit;
    }
}

$mime = mime_content_type($realFile) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
readfile($realFile);
