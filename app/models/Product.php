<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Product catalogue queries.
 */
final class Product
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function featured(int $limit = 8): array
    {
        return Database::select(
            "SELECT p.id, p.title, p.slug, p.price, p.sale_price, p.rating, p.stock,
                    p.sizes, p.colors, p.is_flash, p.is_featured, p.is_active, p.created_at,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_active = 1
             ORDER BY p.is_flash DESC, p.is_featured DESC, p.created_at DESC, p.id ASC
             LIMIT :limit",
            ['limit' => $limit]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function list(?string $categorySlug = null, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $params  = ['offset' => $offset, 'perPage' => $perPage];

        $where = 'WHERE p.is_active = 1';
        if ($categorySlug !== null) {
            $where .= ' AND p.category_slug = :categorySlug';
            $params['categorySlug'] = $categorySlug;
        }

        $products = Database::select(
            "SELECT p.id, p.title, p.slug, p.price, p.sale_price, p.rating, p.stock,
                    p.sizes, p.colors, p.is_flash, p.is_featured, p.is_active, p.created_at,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
             {$where}
             ORDER BY p.is_flash DESC, p.created_at DESC, p.id ASC
             LIMIT :perPage OFFSET :offset",
            $params
        );

        $count = (int) Database::value(
            "SELECT COUNT(*) FROM products p {$where}",
            [],
            'COUNT(*)'
        );

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
            "SELECT p.id, p.title, p.slug, p.price, p.sale_price, p.rating, p.stock,
                    p.sizes, p.colors, p.is_flash, p.is_featured, p.is_active, p.created_at,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_active = 1 AND p.is_flash = 1
               AND (p.flash_sale_ends_at IS NULL OR p.flash_sale_ends_at > NOW())
             ORDER BY p.flash_sale_ends_at ASC, p.id ASC"
        );
    }

    public static function nextFlashEnd(): ?string
    {
        $row = Database::selectOne(
            "SELECT flash_sale_ends_at FROM products
             WHERE is_active = 1 AND is_flash = 1
               AND flash_sale_ends_at > NOW()
             ORDER BY flash_sale_ends_at ASC
             LIMIT 1"
        );

        return $row !== false && isset($row['flash_sale_ends_at'])
            ? (string) $row['flash_sale_ends_at']
            : null;
    }

    public static function flashAlertMessage(): ?string
    {
        $endAt = self::nextFlashEnd();

        if ($endAt === null) {
            return null;
        }

        try {
            $diff = time() - strtotime($endAt);

            return $diff > 0
                ? null
                : 'Promo flash active — jusqu\'à 40% de réduction. Se termine dans ' . self::timeUntil($endAt);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function findBySlug(string $slug): ?array
    {
        $rows = Database::select(
            "SELECT p.id, p.title, p.slug, p.description, p.price, p.sale_price, p.rating, p.stock,
                    p.sizes, p.colors, p.is_flash, p.is_featured, p.is_active, p.created_at, p.updated_at,
                    p.category_slug, p.category_id,
                    c.name AS category_name, c.slug AS category_slug_left,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.slug = :slug AND p.is_active = 1
             LIMIT 1",
            ['slug' => $slug]
        );

        $row = $rows !== [] ? reset($rows) : null;

        if ($row === false) {
            return null;
        }

        $row['category_slug'] = $row['category_slug_left'] ?? $row['category_slug'] ?? '';
        unset($row['category_slug_left']);

        return $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function related(int $productId, string $categorySlug, int $limit = 4): array
    {
        return Database::select(
            "SELECT p.id, p.title, p.slug, p.price, p.sale_price,
                    pi.image_path AS primary_image
             FROM products p
             LEFT JOIN product_images pi
                ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_active = 1
               AND p.category_slug = :categorySlug
               AND p.id != :productId
             ORDER BY p.created_at DESC
             LIMIT :limit",
            ['categorySlug' => $categorySlug, 'productId' => $productId, 'limit' => $limit]
        );
    }

    private static function timeUntil(string $endAt): string
    {
        $seconds = max(0, strtotime($endAt) - time());
        $hours   = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}min";
        }

        return "{$minutes}min";
    }
}
