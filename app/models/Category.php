<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Category catalogue queries.
 */
final class Category
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function list(bool $isMenu = false): array
    {
        $rows = Database::select(
            "SELECT c.id, c.name, c.slug, c.icon, COALESCE(c.menu_label, c.name) AS menu_label,
                    c.sort_order, c.is_active, c.created_at,
                    (SELECT COUNT(*) FROM products p
                     WHERE p.is_active = 1 AND p.category_slug = c.slug) AS product_count
             FROM categories c
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
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $row = Database::selectOne(
            "SELECT id, name, slug, icon, menu_label,
                    COALESCE(menu_label, name) AS display_label,
                    sort_order, is_active, created_at
             FROM categories
             WHERE slug = :slug AND is_active = 1",
            ['slug' => $slug]
        );

        return $row !== false ? $row : null;
    }
}
