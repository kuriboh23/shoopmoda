<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\CategoryModel;
use App\Models\Product;

final class ProductController
{
    public function category(string $slug): void
    {
        $category = CategoryModel::bySlug($slug, false);

        if ($category === null) {
            http_response_code(404);
            View::render('errors/404', [
                'title'       => 'Catégorie introuvable — ' . config('app_name'),
                'description' => 'Cette catégorie n’existe pas ou n’a pas encore de produits.',
                'canonical'   => url('/'),
                'cartCount'   => Cart::count(),
                'wishCount'   => 0,
                'noindex'     => true,
            ]);

            return;
        }

        $page   = isset($_GET['page']) && ctype_digit((string) $_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 12;
        $result = Product::list($slug, $page, $perPage);

        View::render('products/category', [
            'title'       => $category['menu_label'] . ' — ' . config('app_name'),
            'description' => 'Découvrez la collection ' . $category['menu_label'] . ' chez Shoop Moda Style. Vêtements turcs femme et enfant au Maroc.',
            'canonical'   => url('/categorie/' . $slug),
            'cartCount'   => Cart::count(),
            'wishCount'   => 0,
            'category'    => $category,
            'list'        => $result,
            'crumbs'      => [
                ['label' => 'Accueil', 'url' => url('/')],
                ['label' => $category['menu_label'], 'url' => null],
            ],
            'page'        => 'category',
        ]);
    }

    public function product(string $slug): void
    {
        $product = Product::findBySlug($slug);

        if ($product === null) {
            http_response_code(404);
            View::render('errors/404', [
                'title'       => 'Produit introuvable — ' . config('app_name'),
                'description' => 'Ce produit n’existe pas ou n’est plus disponible.',
                'canonical'   => url('/'),
                'cartCount'   => Cart::count(),
                'wishCount'   => 0,
                'noindex'     => true,
            ]);

            return;
        }

        View::render('products/show', [
            'title'       => $product['title'] . ' — ' . config('app_name'),
            'description' => $product['short_description'] ?? mb_substr(strip_tags($product['description'] ?? ''), 0, 160),
            'canonical'   => url('/produit/' . $slug),
            'cartCount'   => Cart::count(),
            'wishCount'   => 0,
            'product'     => $product,
            'crumbs'      => [
                ['label' => 'Accueil', 'url' => url('/')],
                ['label' => $product['category_name'], 'url' => url('/categorie/' . $product['category_slug'])],
                ['label' => $product['title'], 'url' => null],
            ],
            'page'        => 'product',
        ]);
    }
}
