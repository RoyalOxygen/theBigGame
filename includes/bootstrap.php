<?php

declare(strict_types=1);

if (PHP_VERSION_ID < 80000) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'NARFCARD requires PHP 8.0 or later. This server is running PHP ' . PHP_VERSION . '.';
    exit;
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

$vendorAutoload = BASE_PATH . '/vendor/autoload.php';
$fallbackAutoload = BASE_PATH . '/includes/autoload.php';
if (is_file($vendorAutoload)) {
    require_once $vendorAutoload;
    $helpers = BASE_PATH . '/app/helpers/functions.php';
    if (is_file($helpers) && !function_exists('config')) {
        require_once $helpers;
    }
} elseif (is_file($fallbackAutoload)) {
    require_once $fallbackAutoload;
} else {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Application autoload files are missing. Re-upload the complete narfcard folder, including vendor/ or includes/autoload.php.';
    exit;
}

$envFile = BASE_PATH . '/.env';
if (is_file($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $value = trim($value, "\"'");
            }
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
}

$scriptName = basename(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')));
if (PHP_SAPI !== 'cli'
    && !is_file(BASE_PATH . '/storage/installed.lock')
    && !in_array($scriptName, ['install.php', 'health.php'], true)
) {
    if (!headers_sent()) {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $rel = ltrim(dirname($script), '/');
        $depth = ($rel === '' || $rel === '.') ? 0 : substr_count($rel, '/') + 1;
        $prefix = $depth > 0 ? str_repeat('../', $depth) : '';
        header('Location: ' . $prefix . 'install.php', true, 302);
    }
    exit;
}

$config = require BASE_PATH . '/app/config/app.php';

if (!defined('BASE_URL')) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
        || (strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host !== '') {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/.');
        foreach (['/admin', '/user', '/api'] as $sub) {
            if ($dir === $sub || str_ends_with($dir, $sub)) {
                $dir = substr($dir, 0, -strlen($sub));
                break;
            }
        }
        if ($dir === '/' || $dir === '\\' || $dir === '.') {
            $dir = '';
        }
        define('BASE_URL', ($https ? 'https' : 'http') . '://' . $host . $dir);
    } else {
        define('BASE_URL', rtrim((string) $config['url'], '/'));
    }
}

date_default_timezone_set($config['timezone'] ?? 'Africa/Lagos');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($config['https_only']) || (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
    );
    session_name('NARFCARDSESSID');
    session_set_cookie_params([
        'lifetime' => (int) ($config['session_lifetime'] ?? 7200),
        'path' => '/',
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);
    @session_start();
}

if (empty($_SESSION['_init'])) {
    @session_regenerate_id(true);
    $_SESSION['_init'] = time();
}

$idle = (int) ($config['session_lifetime'] ?? 7200);
if (!empty($_SESSION['_last_activity']) && (time() - (int) $_SESSION['_last_activity']) > $idle) {
    session_unset();
    session_destroy();
    @session_start();
}
$_SESSION['_last_activity'] = time();

if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}
