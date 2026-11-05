<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Category queries, aligned on database/schema.sql.
 */
final class Category
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(bool $isMenu = false): array
    {
        $rows = Database::select(
            "SELECT c.id, c.name, c.slug, c.icon, c.sort_order, c.is_active, c.created_at,
                    c.name AS menu_label,
                    (SELECT COUNT(*) FROM products p
                     WHERE p.is_active = 1 AND p.category_id = c.id) AS product_count
             FROM categories c
             WHERE c.is_active = 1
             ORDER BY c.sort_order ASC, c.name ASC"
        );

        if ($isMenu) {
            return array_values(array_filter($rows, static fn (array $row): bool =>
                (int) ($row['product_count'] ?? 0) > 0
            ));
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function list(bool $isMenu = false): array
    {
        return self::all($isMenu);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        return Database::first(
            "SELECT c.id, c.name, c.slug, c.icon, c.sort_order, c.is_active,
                    c.name AS menu_label,
                    (SELECT COUNT(*) FROM products p
                     WHERE p.is_active = 1 AND p.category_id = c.id) AS product_count
             FROM categories c
             WHERE c.slug = :slug AND c.is_active = 1
             LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function bySlug(string $slug): ?array
    {
        return self::findBySlug($slug);
    }
}
