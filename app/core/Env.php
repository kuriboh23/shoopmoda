<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal .env reader (no Composer dependency).
 *
 * Supports KEY=value, comments (#), quoted values and empty values.
 * Real environment variables always win over the .env file.
 */
final class Env
{
    private static bool $loaded = false;

    /** @var array<string, string> */
    private static array $vars = [];

    public static function load(string $path): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key   = trim($key);
            $value = trim($value);

            // Strip matching surrounding quotes.
            $first = $value[0] ?? '';
            $last  = substr($value, -1);

            if (strlen($value) >= 2 && ($first === '"' || $first === "'") && $last === $first) {
                $value = substr($value, 1, -1);
            }

            if ($key === '') {
                continue;
            }

            self::$vars[$key] = $value;

            // Do not override variables already provided by the server.
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (array_key_exists($key, self::$vars)) {
            return self::$vars[$key];
        }

        $value = getenv($key);

        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return ($value !== null && preg_match('/^-?\d+$/', $value) === 1) ? (int) $value : $default;
    }
}
