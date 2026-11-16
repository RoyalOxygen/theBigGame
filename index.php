<?php

if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 80000) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif">';
    echo '<h1>NARFCARD needs PHP 8</h1>';
    echo '<p>This server is running PHP ' . htmlspecialchars(PHP_VERSION) . '. In cPanel open <strong>Select PHP Version</strong> for this subdomain and choose 8.0, 8.1 or 8.2.</p>';
    echo '<p><a href="health.php">Server check</a> &middot; <a href="install.php">Installer</a></p>';
    echo '</body></html>';
    exit;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) {
        return;
    }
    $fatal = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR);
    if (!in_array($err['type'], $fatal, true)) {
        return;
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $msg = $err['message'] . ' in ' . $err['file'] . ' on line ' . $err['line'];
    $logDir = __DIR__ . '/storage/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents($logDir . '/php-fatal.log', date('c') . ' ' . $msg . "\n", FILE_APPEND);
    }
    echo '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:720px;margin:40px auto">';
    echo '<h1>NARFCARD could not start</h1>';
    echo '<p>PHP ' . htmlspecialchars(PHP_VERSION) . '</p>';
    echo '<pre style="white-space:pre-wrap;background:#f6f7f4;padding:12px">' . htmlspecialchars($msg) . '</pre>';
    echo '<p><a href="health.php">Server check</a> &middot; <a href="install.php">Installer</a></p>';
    echo '</body></html>';
});

require __DIR__ . '/includes/bootstrap.php';
view('public/home', array('title' => 'Home'));
