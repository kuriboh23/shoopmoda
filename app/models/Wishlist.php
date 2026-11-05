<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Session;

/**
 * Wishlist stored in the session (no customer account, per the PRD).
 */
final class Wishlist
{
    private const KEY = 'wishlist';

    /** @var array<int, string> */
    private static array $memory = [];

    private static bool $loaded = false;

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            $stored       = Session::get(self::KEY, []);
            self::$memory = is_array($stored)
                ? array_values(array_filter(array_map('strval', $stored)))
                : [];
            self::$loaded = true;
        }

        return self::$memory;
    }

    public static function has(string $slug): bool
    {
        return in_array($slug, self::all(), true);
    }

    /**
     * Add the product if missing, remove it otherwise.
     *
     * @return bool True when the product is now in the wishlist.
     */
    public static function toggle(string $slug): bool
    {
        $slug = trim($slug);

        if ($slug === '') {
            return false;
        }

        $slugs = self::all();

        if (in_array($slug, $slugs, true)) {
            $slugs = array_values(array_filter($slugs, static fn (string $item): bool => $item !== $slug));
            $active = false;
        } else {
            array_unshift($slugs, $slug);
            $active = true;
        }

        self::save($slugs);

        return $active;
    }

    public static function remove(string $slug): void
    {
        self::save(array_values(array_filter(
            self::all(),
            static fn (string $item): bool => $item !== $slug
        )));
    }

    public static function count(): int
    {
        return count(self::all());
    }

    /**
     * Full product rows for the saved slugs.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function products(): array
    {
        return Product::bySlugs(self::all());
    }

    /**
     * @param array<int, string> $slugs
     */
    private static function save(array $slugs): void
    {
        self::$memory = array_values(array_unique($slugs));
        self::$loaded = true;
        Session::set(self::KEY, self::$memory);
    }
}
