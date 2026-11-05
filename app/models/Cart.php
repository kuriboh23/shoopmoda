<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Session;

final class Cart
{
    /** @var array<string, array<string, mixed>> */
    private static array $memory = [];

    public static function add(string $slug, array $item): void
    {
        self::all();

        if (isset(self::$memory[$slug])) {
            self::$memory[$slug]['quantity'] = min(
                99,
                self::$memory[$slug]['quantity'] + max(1, (int) ($item['quantity'] ?? 1))
            );
        } else {
            self::$memory[$slug] = [
                'name'     => $item['name'] ?? $slug,
                'price'    => (float) ($item['price'] ?? 0),
                'quantity' => min(99, max(1, (int) ($item['quantity'] ?? 1))),
                'size'     => $item['size'] ?? null,
                'color'    => $item['color'] ?? null,
                'added_at' => date('Y-m-d H:i:s'),
                '_img'     => $item['_img'] ?? null,
            ];
        }

        Session::set('cart', self::$memory);
    }

    public static function remove(string $slug): void
    {
        self::all();

        unset(self::$memory[$slug]);
        Session::set('cart', self::$memory);
    }

    public static function update(string $slug, int $quantity): void
    {
        self::all();

        if (!array_key_exists($slug, self::$memory)) {
            return;
        }

        if ($quantity <= 0) {
            unset(self::$memory[$slug]);
        } else {
            self::$memory[$slug]['quantity'] = min(99, max(1, $quantity));
        }

        Session::set('cart', self::$memory);
    }

    public static function clear(): void
    {
        self::$memory = [];
        Session::set('cart', []);
    }

    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        if (self::$memory === []) {
            self::ensure();
            self::$memory = Session::get('cart', []);
        }

        return self::$memory;
    }

    public static function count(): int
    {
        return array_reduce(
            self::all(),
            static fn (int $sum, array $item): int => $sum + ((int) ($item['quantity'] ?? 1)),
            0
        );
    }

    public static function subtotal(): float
    {
        return array_reduce(
            self::all(),
            static fn (float $sum, array $item): float => $sum + ((float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1)),
            0.0
        );
    }

    public static function deliveryFee(float $subtotal): float
    {
        if ($subtotal >= 500) {
            return 0;
        }

        return (float) Setting::getFloat('delivery_fee', 25);
    }

    private static function ensure(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start([
                'cookie_httponly' => true,
                'cookie_secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'cookie_samesite' => 'Lax',
            ]);
        }
    }
}
