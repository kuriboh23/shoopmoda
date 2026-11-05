<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal session helper — thin wrapper around $_SESSION.
 *
 * Keeps controllers and models from touching $_SESSION directly.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start([
                'cookie_httponly' => true,
                'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();

        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();

        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }
}
