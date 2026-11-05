<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class CategoryModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return Database::select(
            "SELECT c.id, c.name, c.slug, c.icon, c.parent, c.sort_order,
                    COALESCE(c.menu_label, c.name) AS menu_label,
                    (SELECT COUNT(*) FROM products p
                     WHERE p.is_active = 1 AND p.category_slug = c.slug) AS product_count
             FROM categories c
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function forMenu(): array
    {
        return array_values(array_filter(self::all(), static fn (array $row): bool =>
            (int) ($row['product_count'] ?? 0) > 0
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function bySlug(string $slug, bool $withCount = true): ?array
    {
        $sql = "SELECT c.id, c.name, c.slug, c.icon, c.parent, c.sort_order, c.meta_title, c.meta_desc,
                       COALESCE(c.menu_label, c.name) AS menu_label
                FROM categories c
                WHERE c.slug = :slug AND c.is_active = 1
                LIMIT 1";

        $row = Database::selectOne($sql, ['slug' => $slug]);

        if ($row === false || $row === null) {
            return null;
        }

        if ($withCount) {
            $row['product_count'] = (int) Database::value(
                "SELECT COUNT(*) FROM products p WHERE p.is_active = 1 AND p.category_slug = :slug",
                ['slug' => $slug],
                'COUNT(*)'
            );
        }

        return $row;
    }
}
