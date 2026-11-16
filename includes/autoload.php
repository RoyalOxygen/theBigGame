<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $map = [
        'App\\Controllers\\' => BASE_PATH . '/app/controllers/',
        'App\\Middleware\\' => BASE_PATH . '/app/middleware/',
        'App\\Services\\' => BASE_PATH . '/app/Services/',
        'PHPMailer\\PHPMailer\\' => BASE_PATH . '/vendor/phpmailer/phpmailer/src/',
    ];
    foreach ($map as $prefix => $dir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $dir . $relative;
        if (is_file($file)) {
            require_once $file;
        }
        return;
    }
});

$helpers = BASE_PATH . '/app/helpers/functions.php';
if (is_file($helpers)) {
    require_once $helpers;
}
