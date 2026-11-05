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

    /**
     * Categories for the admin panel (inactive ones included).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function adminAll(): array
    {
        return Database::select(
            "SELECT c.id, c.name, c.slug, c.sort_order, c.is_active,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
             FROM categories c
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    public static function create(string $name, string $slug, int $sortOrder = 0): void
    {
        Database::execute(
            'INSERT INTO categories (name, slug, sort_order, is_active) VALUES (:name, :slug, :sort_order, 1)',
            ['name' => $name, 'slug' => $slug, 'sort_order' => $sortOrder]
        );
    }

    public static function delete(int $id): void
    {
        // Products keep existing: the foreign key sets category_id to NULL.
        Database::execute('DELETE FROM categories WHERE id = :id', ['id' => $id]);
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql    = 'SELECT COUNT(*) AS total FROM categories WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }

        $row = Database::first($sql, $params);

        return (int) ($row['total'] ?? 0) > 0;
    }
}
