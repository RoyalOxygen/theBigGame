<?php
header('Content-Type: text/html; charset=utf-8');
$php = PHP_VERSION;
$ok8 = defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 80000;
$pdo = function_exists('extension_loaded') && extension_loaded('pdo');
$mysql = function_exists('extension_loaded') && extension_loaded('pdo_mysql');
$index = is_file(__DIR__ . '/index.php');
$env = is_file(__DIR__ . '/.env');
$lock = is_file(__DIR__ . '/storage/installed.lock');
$storage = is_dir(__DIR__ . '/storage') && is_writable(__DIR__ . '/storage');
function hrow($label, $ok, $detail)
{
    $color = $ok ? '#0b5c35' : '#a33';
    $state = $ok ? 'OK' : 'FIX';
    echo '<tr><td>' . htmlspecialchars($label) . '</td><td style="color:' . $color . '">' . $state . '</td><td>' . htmlspecialchars($detail) . '</td></tr>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NARFCARD server check</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f4; margin: 0; color: #1c2430; }
        .bar { background: #073b22; color: #fff; padding: 14px 20px; border-bottom: 3px solid #c9a227; }
        .wrap { max-width: 720px; margin: 28px auto; background: #fff; padding: 24px; border: 1px solid #dfe6dc; border-radius: 12px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px 0; border-bottom: 1px solid #eef2ee; font-size: 14px; }
        a { color: #0b5c35; }
    </style>
</head>
<body>
<div class="bar">NARFCARD server check</div>
<div class="wrap">
    <p>If this page opens, PHP is running and the 500 is not caused by this file. Use the rows below on <strong>nerf.oxygendata.com.ng</strong>.</p>
    <table>
        <?php
        hrow('PHP version', $ok8, $php . ($ok8 ? '' : ' — switch this subdomain to PHP 8.0, 8.1 or 8.2 in cPanel'));
        hrow('index.php in this folder', $index, $index ? 'Document root looks correct' : 'Point the subdomain document root at the folder that contains index.php');
        hrow('PDO', $pdo, $pdo ? 'enabled' : 'enable pdo in Select PHP Version > Extensions');
        hrow('pdo_mysql', $mysql, $mysql ? 'enabled' : 'enable pdo_mysql in Select PHP Version > Extensions');
        hrow('storage writable', $storage, $storage ? 'yes' : 'chmod storage and uploads to 0755 or 0775');
        hrow('.env present', $env, $env ? 'yes' : 'run install.php');
        hrow('installed.lock', $lock, $lock ? 'installer already completed' : 'open install.php and enter MySQL plus Super Administrator login');
        ?>
    </table>
    <p><a href="install.php">Open installer</a> &middot; <a href="index.php">Open homepage</a></p>
</div>
</body>
</html>
