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
     * Full catalogue listing: search + category + sort, paginated.
     *
     * @return array{products: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int}
     */
    public static function catalog(?string $query, ?string $categorySlug, string $sort, int $page = 1, int $perPage = 12): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where  = 'p.is_active = 1';
        $params = [];

        if ($query !== null && $query !== '') {
            // Two distinct placeholders: a named parameter cannot be reused
            // when PDO runs with native prepares.
            $where .= ' AND (p.title LIKE :queryTitle OR p.description LIKE :queryDescription)';
            $params['queryTitle']       = '%' . $query . '%';
            $params['queryDescription'] = '%' . $query . '%';
        }

        if ($categorySlug !== null && $categorySlug !== '') {
            $where .= ' AND c.slug = :categorySlug';
            $params['categorySlug'] = $categorySlug;
        }

        $orderBy = match ($sort) {
            'prix-asc'  => 'COALESCE(p.sale_price, p.price) ASC, p.id ASC',
            'prix-desc' => 'COALESCE(p.sale_price, p.price) DESC, p.id ASC',
            'promos'    => 'p.is_flash DESC, (p.sale_price IS NOT NULL) DESC, p.created_at DESC',
            default     => 'p.is_flash DESC, p.is_featured DESC, p.created_at DESC, p.id ASC',
        };

        $products = Database::select(
            "SELECT " . self::CARD_COLUMNS . "
             " . self::CARD_JOINS . "
             WHERE {$where}
             ORDER BY {$orderBy}
             LIMIT :perPage OFFSET :offset",
            $params + ['perPage' => $perPage, 'offset' => $offset]
        );

        $countRow = Database::first(
            "SELECT COUNT(*) AS total
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE {$where}",
            $params
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

    /**
     * Autocomplete suggestions for the search page.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function suggestions(string $query, int $limit = 6): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        return Database::select(
            "SELECT p.id, p.title, p.slug, p.price, p.sale_price,
                    pi.image_path AS primary_image, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.is_active = 1 AND p.title LIKE :query
             ORDER BY p.is_featured DESC, p.created_at DESC, p.id ASC
             LIMIT :limit",
            ['query' => '%' . $query . '%', 'limit' => $limit]
        );
    }

    /**
     * Words used in active product titles, keyed by their folded form.
     *
     * Used only to turn a misspelled query into the closest real word.
     *
     * @return array<string, string> folded word => original spelling
     */
    public static function vocabulary(): array
    {
        static $words = null;

        if ($words !== null) {
            return $words;
        }

        $words = [];
        $rows  = Database::select('SELECT title FROM products WHERE is_active = 1 ORDER BY id ASC LIMIT 400');

        foreach ($rows as $row) {
            $title = (string) ($row['title'] ?? '');

            foreach (preg_split('/[^\p{L}\p{N}]+/u', $title, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
                $folded = self::fold($word);

                if (mb_strlen($folded) < 3 || ctype_digit($folded) || isset($words[$folded])) {
                    continue;
                }

                $words[$folded] = mb_strtolower($word);
            }
        }

        return $words;
    }

    /**
     * Closest real words for a query the catalogue could not match.
     *
     * Returns null when every word is already known (or too far from the
     * vocabulary), so the caller keeps showing the user's own query.
     */
    public static function correctQuery(string $query): ?string
    {
        $vocabulary = self::vocabulary();

        if ($vocabulary === []) {
            return null;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($tokens === []) {
            return null;
        }

        $changed = false;
        $words   = [];

        foreach ($tokens as $token) {
            $folded = self::fold($token);

            if ($folded === '' || mb_strlen($folded) < 3 || isset($vocabulary[$folded])) {
                $words[] = $token;

                continue;
            }

            // Typos grow with the word: short words tolerate one edit only.
            $limit        = mb_strlen($folded) <= 4 ? 1 : (mb_strlen($folded) <= 7 ? 2 : 3);
            $best         = null;
            $bestDistance = $limit + 1;

            foreach ($vocabulary as $candidate => $original) {
                if (abs(strlen($candidate) - strlen($folded)) > $limit) {
                    continue;
                }

                $distance = levenshtein($folded, $candidate);

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $best         = $original;
                }
            }

            if ($best !== null) {
                $words[] = $best;
                $changed = true;
            } else {
                $words[] = $token;
            }
        }

        return $changed ? implode(' ', $words) : null;
    }

    /**
     * Lowercase, accent-free form used for distance comparisons.
     */
    private static function fold(string $value): string
    {
        $value = mb_strtolower($value);
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae', 'ß' => 'ss',
        ]);

        return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
    }

    /**
     * Turn a title into a URL-safe slug.
     */
    public static function slugify(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y', 'œ' => 'oe', 'æ' => 'ae', 'ß' => 'ss',
        ]);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? mb_substr($value, 0, 120) : 'piece';
    }

    /**
     * Products for the admin list (inactive ones included).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function adminAll(string $search = '', int $limit = 100): array
    {
        $sql = "SELECT p.id, p.title, p.slug, p.price, p.sale_price, p.stock,
                       p.is_active, p.is_featured, p.is_flash, p.created_at,
                       c.name AS category_name, pi.image_path AS primary_image
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1";

        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE p.title LIKE :titleSearch OR p.slug LIKE :slugSearch';
            $params['titleSearch'] = '%' . $search . '%';
            $params['slugSearch']  = '%' . $search . '%';
        }

        $sql .= ' ORDER BY p.created_at DESC, p.id DESC LIMIT :limit';
        $params['limit'] = $limit;

        return Database::select($sql, $params);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function adminFind(int $id): ?array
    {
        return Database::first(
            "SELECT p.*, c.name AS category_name, pi.image_path AS primary_image
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
             WHERE p.id = :id
             LIMIT 1",
            ['id' => $id]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminStats(): array
    {
        $row = Database::first(
            "SELECT COUNT(*) AS products,
                    COALESCE(SUM(is_active = 1), 0) AS active,
                    COALESCE(SUM(is_active = 1 AND stock <= 0), 0) AS out_of_stock,
                    COALESCE(SUM(is_active = 1 AND is_flash = 1), 0) AS flash,
                    COALESCE(SUM(is_active = 1 AND sale_price IS NOT NULL), 0) AS on_sale,
                    COALESCE(SUM(stock * COALESCE(sale_price, price)), 0) AS stock_value
             FROM products"
        );

        return $row ?? [
            'products' => 0, 'active' => 0, 'out_of_stock' => 0,
            'flash' => 0, 'on_sale' => 0, 'stock_value' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        Database::execute(
            'INSERT INTO products
                (category_id, title, slug, description, price, sale_price, stock, sizes, colors, is_flash, is_featured, is_active)
             VALUES
                (:category_id, :title, :slug, :description, :price, :sale_price, :stock, :sizes, :colors, :is_flash, :is_featured, :is_active)',
            [
                'category_id' => $data['category_id'] ?: null,
                'title'       => $data['title'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'price'       => $data['price'],
                'sale_price'  => $data['sale_price'] !== null ? $data['sale_price'] : null,
                'stock'       => $data['stock'],
                'sizes'       => $data['sizes'],
                'colors'      => $data['colors'],
                'is_flash'    => $data['is_flash'],
                'is_featured' => $data['is_featured'],
                'is_active'   => $data['is_active'],
            ]
        );

        return (int) Database::pdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        Database::execute(
            'UPDATE products SET
                category_id = :category_id, title = :title, slug = :slug,
                description = :description, price = :price, sale_price = :sale_price,
                stock = :stock, sizes = :sizes, colors = :colors,
                is_flash = :is_flash, is_featured = :is_featured, is_active = :is_active
             WHERE id = :id',
            [
                'id'          => $id,
                'category_id' => $data['category_id'] ?: null,
                'title'       => $data['title'],
                'slug'        => $data['slug'],
                'description' => $data['description'],
                'price'       => $data['price'],
                'sale_price'  => $data['sale_price'] !== null ? $data['sale_price'] : null,
                'stock'       => $data['stock'],
                'sizes'       => $data['sizes'],
                'colors'      => $data['colors'],
                'is_flash'    => $data['is_flash'],
                'is_featured' => $data['is_featured'],
                'is_active'   => $data['is_active'],
            ]
        );
    }

    public static function delete(int $id): void
    {
        $images = Database::select(
            'SELECT image_path FROM product_images WHERE product_id = :id',
            ['id' => $id]
        );

        Database::execute('DELETE FROM products WHERE id = :id', ['id' => $id]);

        foreach ($images as $image) {
            $path = (string) ($image['image_path'] ?? '');

            if (!str_starts_with($path, 'uploads/products/')) {
                continue;
            }

            $file = PUBLIC_PATH . '/' . $path;

            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql    = 'SELECT COUNT(*) AS total FROM products WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $exceptId;
        }

        $row = Database::first($sql, $params);

        return (int) ($row['total'] ?? 0) > 0;
    }

    public static function setPrimaryImage(int $productId, string $path): void
    {
        Database::execute(
            'UPDATE product_images SET is_primary = 0 WHERE product_id = :id',
            ['id' => $productId]
        );

        Database::execute(
            'INSERT INTO product_images (product_id, image_path, sort_order, is_primary)
             VALUES (:product_id, :image_path, 0, 1)',
            ['product_id' => $productId, 'image_path' => $path]
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

        return 'Promotions en cours — elles se terminent dans ' . self::timeUntil($endAt);
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
