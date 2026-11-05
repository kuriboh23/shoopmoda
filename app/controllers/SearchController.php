<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;

final class SearchController
{
    public function index(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $query = mb_substr($query, 0, 80);

        $results   = [];
        $suggested = null;

        if ($query !== '') {
            $results = Product::search($query);

            // Misspelt query: offer (and show) the closest real word.
            if ($results === []) {
                $suggested = Product::correctQuery($query);

                if ($suggested !== null) {
                    $results = Product::search($suggested);
                }
            }
        }

        View::render('search', [
            'title'       => ($query !== '' ? $query . ' — ' : '') . 'Recherche — ' . config('app_name'),
            'description' => 'Recherchez un pyjama, une robe ou un ensemble dans la boutique Shoop Moda Style.',
            'canonical'   => url('/recherche'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'query'       => $query,
            'results'     => $results,
            'suggested'   => $suggested,
            'popular'     => $query === '' ? Product::featured(4) : [],
            'noindex'     => true,
            'page'        => 'search',
        ]);
    }

    /**
     * JSON autocomplete used by the search page (small title suggestions).
     */
    public function suggestions(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $query = mb_substr($query, 0, 60);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $items      = [];
        $suggestion = null;

        if (mb_strlen($query) >= 2) {
            foreach (Product::suggestions($query, 6) as $row) {
                $items[] = [
                    'title'    => (string) $row['title'],
                    'slug'     => (string) $row['slug'],
                    'url'      => url('/produit/' . $row['slug']),
                    'price'    => format_price((float) ($row['sale_price'] ?: $row['price'])),
                    'oldPrice' => !empty($row['sale_price']) ? format_price((float) $row['price']) : null,
                    'category' => (string) ($row['category_name'] ?? ''),
                    'image'    => product_image_url((int) $row['id'], $row['primary_image'] ?? null),
                ];
            }

            if ($items === [] && mb_strlen($query) >= 3) {
                $suggestion = Product::correctQuery($query);
            }
        }

        echo json_encode(
            ['items' => $items, 'suggestion' => $suggestion],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
