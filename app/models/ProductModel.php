<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Product catalogue queries.
 */
final class ProductModel
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function featured(int $limit = 8): array
    {
        return Database::select(
            "SELECT p.id, p.name, p.slug, p.price, p.secondary_price,
                    p.images, p.is_new, p.is_featured, p.is_flash_sale,
                    p.flash_sale_ends_at, p.created_at, COALESCE(p.sort_order, 0) AS sort_order
             FROM products p
             WHERE p.is_active = 1
             ORDER BY p.is_flash_sale DESC, p.is_new DESC, p.sort_order ASC, p.created_at DESC,
                      p.id ASC
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
        $params = ['offset' => $offset, 'perPage' => $perPage];

        $where = 'p.is_active = 1';
        if ($categorySlug !== null) {
            $where .= ' AND p.category_slug = :categorySlug';
            $params['categorySlug'] = $categorySlug;
        }

        $products = Database::select(
            "SELECT p.id, p.name, p.slug, p.price, p.secondary_price,
                    p.images, p.is_new, p.is_featured, p.is_flash_sale,
                    p.flash_sale_ends_at, p.created_at, COALESCE(p.sort_order, 0) AS sort_order
             FROM products p
             WHERE {$where}
             ORDER BY p.is_flash_sale DESC, p.sort_order ASC, p.created_at DESC, p.id ASC
             LIMIT :perPage OFFSET :offset",
            $params
        );

        $count = (int) Database::value(
            "SELECT COUNT(*) FROM products p WHERE {$where}",
            [],
            'COUNT(*)'
        );

        return [
            'products'    => $products,
            'total'       => $count,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($count / $perPage),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function flashSales(): array
    {
        return Database::select(
            "SELECT p.id, p.name, p.slug, p.price, p.secondary_price,
                    p.images, p.is_new, p.is_featured, p.is_flash_sale,
                    p.flash_sale_ends_at, p.created_at, COALESCE(p.sort_order, 0) AS sort_order
             FROM products p
             WHERE p.is_active = 1 AND p.is_flash_sale = 1
               AND (p.flash_sale_ends_at IS NULL OR p.flash_sale_ends_at > NOW())
             ORDER BY p.flash_sale_ends_at ASC, p.id ASC"
        );
    }

    public static function nextFlashEnd(): ?string
    {
        $row = Database::selectOne(
            "SELECT flash_sale_ends_at FROM products
             WHERE is_active = 1 AND is_flash_sale = 1
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
                : 'Promotions en cours — fin dans ' . self::timeUntil($endAt);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function timeUntil(string $endAt): string
    {
        $seconds = max(0, strtotime($endAt) - time());
        $hours   = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
