<?php

declare(strict_types=1);

namespace App\Middleware;

class Auth
{
    public static function user(): array
    {
        return require_user();
    }

    public static function admin(array $permissions = []): array
    {
        return require_admin($permissions);
    }
}
