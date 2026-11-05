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

        $results = $query !== '' ? Product::search($query) : [];

        View::render('search', [
            'title'       => ($query !== '' ? $query . ' — ' : '') . 'Recherche — ' . config('app_name'),
            'description' => 'Recherchez un pyjama, une robe ou un ensemble dans la boutique Shoop Moda Style.',
            'canonical'   => url('/recherche'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'query'       => $query,
            'results'     => $results,
            'noindex'     => true,
            'page'        => 'search',
        ]);
    }
}
