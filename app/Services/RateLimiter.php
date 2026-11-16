<?php

declare(strict_types=1);

namespace App\Services;

class RateLimiter
{
    public static function hit(string $key, int $max, int $seconds): bool
    {
        $file = BASE_PATH . '/storage/logs/rate_' . hash('sha256', $key) . '.json';
        $now = time();
        $data = ['hits' => [], 'blocked_until' => 0];
        if (is_file($file)) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (is_array($decoded)) {
                $data = array_merge($data, $decoded);
            }
        }
        if (($data['blocked_until'] ?? 0) > $now) {
            return false;
        }
        $hits = array_values(array_filter($data['hits'] ?? [], static fn ($t) => $t > ($now - $seconds)));
        $hits[] = $now;
        if (count($hits) > $max) {
            $data['blocked_until'] = $now + $seconds;
            $data['hits'] = $hits;
            file_put_contents($file, json_encode($data));
            return false;
        }
        $data['hits'] = $hits;
        file_put_contents($file, json_encode($data));
        return true;
    }
}
