<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Thin key/value settings reader.
 *
 * Settings can live in app/config/app.php for static content and in the
 * `settings` database table for runtime values the owner may change without
 * a deploy.
 */
final class Setting
{
    /**
     * @return array<string, mixed>
     */
    private static array $cache = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function set(array $data): void
    {
        self::$cache = array_merge(self::$cache, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$cache !== []) {
            return self::$cache;
        }

        $rows = Database::select('SELECT setting_key, setting_value FROM settings');

        self::$cache = [];
        foreach ($rows as $row) {
            self::$cache[(string) $row['setting_key']] = $row['setting_value'];
        }

        return self::$cache;
    }

    /**
     * @param string $key
     * @param mixed  $default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $values = self::all();

        if (!array_key_exists($key, $values)) {
            return $default;
        }

        $value = $values[$key];

        if ($value === null || $value === '') {
            return $default;
        }

        return $value;
    }

    /**
     * Create or update a setting (admin panel).
     */
    public static function put(string $key, string $value): void
    {
        Database::execute(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            ['setting_key' => $key, 'setting_value' => $value]
        );

        self::$cache[$key] = $value;
    }

    /**
     * @param string $key
     * @param float  $default
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return (float) $value;
    }

    /**
     * @param string $key
     * @param int    $default
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * @param string $key
     * @param bool   $default
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
