<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal file cache (storage/cache).
 *
 * Used for data that changes rarely (settings, categories) so the
 * storefront does not hit MySQL for the same rows on every request.
 * Writes are atomic (temp file + rename).
 */
final class Cache
{
    public static function get(string $key): mixed
    {
        $file = self::path($key);

        if (!is_file($file)) {
            return null;
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return null;
        }

        $payload = json_decode($raw, true);

        if (!is_array($payload) || !isset($payload['expires'])) {
            return null;
        }

        if ((int) $payload['expires'] < time()) {
            @unlink($file);

            return null;
        }

        return $payload['value'] ?? null;
    }

    public static function put(string $key, mixed $value, int $ttl = 3600): void
    {
        $file = self::path($key);
        $temp = $file . '.' . bin2hex(random_bytes(4));

        $payload = json_encode([
            'expires' => time() + max(1, $ttl),
            'value'   => $value,
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return;
        }

        if (@file_put_contents($temp, $payload) === false) {
            return;
        }

        if (!@rename($temp, $file)) {
            @unlink($temp);
        }
    }

    public static function forget(string $key): void
    {
        @unlink(self::path($key));
    }

    /**
     * Return the cached value or compute and store it.
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();
        self::put($key, $value, $ttl);

        return $value;
    }

    private static function path(string $key): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key) ?? 'cache';

        return STORAGE_PATH . '/cache/' . $safe . '.json';
    }
}
