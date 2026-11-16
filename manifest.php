<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$icon192 = brand_url('apple_icon_path', 'images/icon-192.png');
$icon512 = brand_url('apple_icon_path', 'images/icon-512.png');

echo json_encode([
    'id' => '/',
    'name' => site_name(),
    'short_name' => site_short(),
    'description' => (string) setting('site_meta_description', 'Install the programme portal on your device.'),
    'start_url' => './',
    'scope' => './',
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'background_color' => theme_color(),
    'theme_color' => theme_color(),
    'lang' => 'en-NG',
    'dir' => 'ltr',
    'categories' => ['finance', 'government'],
    'icons' => [
        ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'maskable'],
        ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
