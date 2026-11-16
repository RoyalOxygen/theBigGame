<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = require BASE_PATH . '/app/config/database.php';
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']);

        try {
            self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $lock = BASE_PATH . '/storage/installed.lock';
            if (!is_file($lock) && is_file(BASE_PATH . '/install.php')) {
                $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
                $rel = ltrim(dirname($script), '/');
                $depth = ($rel === '' || $rel === '.') ? 0 : substr_count($rel, '/') + 1;
                $prefix = $depth > 0 ? str_repeat('../', $depth) : '';
                header('Location: ' . $prefix . 'install.php', true, 302);
                exit;
            }
            throw new RuntimeException('Database connection failed. Check DB settings in .env.');
        }

        return self::$pdo;
    }
}
