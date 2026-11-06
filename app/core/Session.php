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
            $cookieSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

            $options = [
                'cookie_httponly' => true,
                'cookie_secure'   => $cookieSecure,
                'cookie_samesite' => 'Lax',
            ];

            try {
                session_start($options);
            } catch (\Throwable $exception) {
                // Some shared hosts disable session configuration or block
                // custom session handlers. Fall back to the default handler.
                @ini_set('session.use_strict_mode', '0');

                try {
                    session_start();
                } catch (\Throwable) {
                    // If sessions are not available at all, the app still
                    // works; it just loses the cart / wishlist / admin session.
                }
            }
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
