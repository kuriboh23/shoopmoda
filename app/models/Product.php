<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Product catalogue queries.
 *
 * The queries follow database/schema.sql: a product points to its category
 * through `category_id` and its pictures live in `product_images`.
 */
final class Product
{
    /**
     * Columns shared by every card/listing query.
     */
    private const CARD_COLUMNS = "p.id, p.title, p.slug, p.description, p.price, p.sale_price,
            p.rating, p.stock, p.is_flash, p.is_featured, p.is_active, p.created_at,
            c.name AS category_name, c.slug AS category_slug,
            pi.image_path AS primary_image";

    private const CARD_JOINS = "FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1";

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function featured(int $limit = 8): array
    {
        return Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE p.is_active = 1
             ORDER BY p.is_flash DESC, p.is_featured DESC, p.created_at DESC, p.id ASC
             LIMIT :limit",
            ['limit' => $limit]
        );
    }

    /**
     * @return array{products: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int}
     */
    public static function list(?string $categorySlug = null, int $page = 1, int $perPage = 12): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $params = ['offset' => $offset, 'perPage' => $perPage];

        $where = 'p.is_active = 1';
        if ($categorySlug !== null && $categorySlug !== '') {
            $where .= ' AND c.slug = :categorySlug';
            $params['categorySlug'] = $categorySlug;
        }

        $products = Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE {$where}
             ORDER BY p.is_flash DESC, p.is_featured DESC, p.created_at DESC, p.id ASC
             LIMIT :perPage OFFSET :offset",
            $params
        );

        $countRow = Database::first(
            "SELECT COUNT(*) AS total
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE {$where}",
            array_diff_key($params, ['offset' => true, 'perPage' => true])
        );

        $count = (int) ($countRow['total'] ?? 0);

        return [
            'products'    => $products,
            'total'       => $count,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => max(1, (int) ceil($count / $perPage)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function flashSales(): array
    {
        return Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE p.is_active = 1 AND p.is_flash = 1
             ORDER BY p.created_at DESC, p.id ASC"
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function search(string $query, int $limit = 24): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $like = '%' . $query . '%';

        return Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE p.is_active = 1
               AND (p.title LIKE :title OR p.description LIKE :description)
             ORDER BY p.is_flash DESC, p.is_featured DESC, p.created_at DESC
             LIMIT :limit",
            ['title' => $like, 'description' => $like, 'limit' => $limit]
        );
    }

    /**
     * @param array<int, string> $slugs
     * @return array<int, array<string, mixed>>
     */
    public static function bySlugs(array $slugs): array
    {
        $slugs = array_values(array_filter(array_map(
            static fn (mixed $slug): string => trim((string) $slug),
            $slugs
        ), static fn (string $slug): bool => $slug !== ''));

        if ($slugs === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($slugs), '?'));

        return Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE p.is_active = 1 AND p.slug IN ({$placeholders})
             ORDER BY p.created_at DESC",
            $slugs
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        return Database::first(
            "SELECT p.id, p.title, p.slug, p.description, p.price, p.sale_price,
                    p.rating, p.stock, p.sizes, p.colors, p.is_flash, p.is_featured,
                    p.is_active, p.created_at, p.updated_at, p.category_id,
                    c.name AS category_name, c.slug AS category_slug,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.slug = :slug AND p.is_active = 1
             LIMIT 1",
            ['slug' => $slug]
        );
    }

    /**
     * Gallery pictures for a product, primary first.
     *
     * @return array<int, string>
     */
    public static function images(int $productId): array
    {
        $rows = Database::select(
            "SELECT image_path FROM product_images
             WHERE product_id = :id
             ORDER BY is_primary DESC, sort_order ASC, id ASC",
            ['id' => $productId]
        );

        return array_values(array_filter(array_map(
            static fn (array $row): string => (string) ($row['image_path'] ?? ''),
            $rows
        )));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function related(int $productId, string $categorySlug, int $limit = 4): array
    {
        return Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE p.is_active = 1
               AND c.slug = :categorySlug
               AND p.id != :productId
             ORDER BY p.created_at DESC, p.id ASC
             LIMIT :limit",
            ['categorySlug' => $categorySlug, 'productId' => $productId, 'limit' => $limit]
        );
    }

    public static function nextFlashEnd(): ?string
    {
        $endAt = Setting::get('flash_sale_end');

        if ($endAt === null || $endAt === '') {
            return null;
        }

        return strtotime($endAt) > time() ? (string) $endAt : null;
    }

    public static function flashAlertMessage(): ?string
    {
        $endAt = self::nextFlashEnd();

        if ($endAt === null) {
            return null;
        }

        return 'Promo flash active — jusqu’à 40% de réduction. Se termine dans ' . self::timeUntil($endAt);
    }

    private static function timeUntil(string $endAt): string
    {
        $seconds = max(0, (int) strtotime($endAt) - time());
        $days    = (int) floor($seconds / 86400);
        $hours   = (int) floor(($seconds % 86400) / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        if ($days > 0) {
            return "{$days}j {$hours}h";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }

        return "{$minutes}min";
    }
}
