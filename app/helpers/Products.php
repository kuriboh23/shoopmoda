<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Product;

/**
 * Front-end product helpers.
 *
 * Kept thin on purpose: catalogue pages and the home hero use ProductModel
 * (app/models/Product.php) directly, this file only bridges a couple of
 * global helpers used in templates.
 */

/**
 * @return array<int, array<string, mixed>>
 */
function featured_products(int $limit = 8): array
{
    return Product::featured($limit);
}

/**
 * @return array<int, array<string, mixed>>
 */
function category_products(?string $categorySlug = null, int $page = 1, int $perPage = 12): array
{
    return Product::list($categorySlug, $page, $perPage);
}

/**
 * @return array<int, array<string, mixed>|null>
 */
function product_by_slug(string $slug): ?array
{
    return Product::findBySlug($slug);
}

/**
 * @return array<int, array<string, mixed>>
 */
function flash_sale_products(): array
{
    return Product::flashSales();
}

function flash_sale_alert_message(): ?string
{
    return Product::flashAlertMessage();
}
