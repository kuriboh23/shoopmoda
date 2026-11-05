<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;

final class ProductController
{
    public function category(string $slug): void
    {
        $category = Category::findBySlug($slug);

        if ($category === null) {
            $this->notFound('Catégorie introuvable', 'Cette catégorie n’existe pas ou n’a pas encore de produits.');

            return;
        }

        $page    = isset($_GET['page']) && ctype_digit((string) $_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $perPage = 12;
        $result  = Product::list($slug, $page, $perPage);

        View::render('products/category', [
            'title'       => $category['name'] . ' — ' . config('app_name'),
            'description' => 'Découvrez la collection ' . $category['name'] . ' chez Shoop Moda Style. Vêtements turcs femme et enfant au Maroc.',
            'canonical'   => url('/categorie/' . $slug),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'category'    => $category,
            'products'    => $result,
            'page'        => 'category',
        ]);
    }

    public function product(string $slug): void
    {
        $product = Product::findBySlug($slug);

        if ($product === null) {
            $this->notFound('Produit introuvable', 'Ce produit n’existe pas ou n’est plus disponible.');

            return;
        }

        View::render('products/show', [
            'title'       => $product['title'] . ' — ' . config('app_name'),
            'description' => mb_substr(trim(strip_tags((string) ($product['description'] ?? ''))), 0, 160),
            'canonical'   => url('/produit/' . $slug),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'product'     => $product,
            'gallery'     => Product::images((int) $product['id']),
            'related'     => !empty($product['category_slug'])
                ? Product::related((int) $product['id'], (string) $product['category_slug'], 4)
                : [],
            'page'        => 'product',
        ]);
    }

    /**
     * Full catalogue: search, category filter and sorting.
     */
    public function catalog(): void
    {
        $query = trim((string) ($_GET['q'] ?? ''));
        $query = mb_substr($query, 0, 60);

        $categorySlug = trim((string) ($_GET['categorie'] ?? ''));
        $categorySlug = (string) preg_replace('/[^a-z0-9-]/i', '', $categorySlug);

        $sort    = (string) ($_GET['tri'] ?? 'nouveautes');
        $allowed = ['nouveautes', 'prix-asc', 'prix-desc', 'promos'];

        if (!in_array($sort, $allowed, true)) {
            $sort = 'nouveautes';
        }

        $page   = isset($_GET['page']) && ctype_digit((string) $_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $result = Product::catalog(
            $query !== '' ? $query : null,
            $categorySlug !== '' ? $categorySlug : null,
            $sort,
            $page,
            12
        );

        // Misspelt query: retry with the closest word from the catalogue.
        $suggested = null;

        if ($query !== '' && (int) ($result['total'] ?? 0) === 0) {
            $suggested = Product::correctQuery($query);

            if ($suggested !== null) {
                $result = Product::catalog(
                    $suggested,
                    $categorySlug !== '' ? $categorySlug : null,
                    $sort,
                    $page,
                    12
                );
            }
        }

        View::render('products/index', [
            'title'          => 'Toutes les pièces — ' . config('app_name'),
            'description'    => 'Parcourez toutes les pièces Shoop Moda Style : femme, enfant, pyjamas et robes importés de Turquie.',
            'canonical'      => url('/produits'),
            'cartCount'      => Cart::count(),
            'wishCount'      => Wishlist::count(),
            'wishSlugs'      => Wishlist::all(),
            'products'       => $result,
            'categories'     => Category::all(),
            'query'          => $query,
            'suggestedQuery' => $suggested,
            'activeCategory' => $categorySlug,
            'sort'           => $sort,
            'page'           => 'catalog',
        ]);
    }

    public function promotions(): void
    {
        View::render('products/promotions', [
            'title'       => 'Promotions — ' . config('app_name'),
            'description' => 'Offres flash et promotions en cours chez Shoop Moda Style.',
            'canonical'   => url('/promotions'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'flashSales'  => Product::flashSales(),
            'nextEndAt'   => Product::nextFlashEnd(),
            'page'        => 'promotions',
        ]);
    }

    private function notFound(string $title, string $description): void
    {
        http_response_code(404);

        View::render('errors/404', [
            'title'       => $title . ' — ' . config('app_name'),
            'description' => $description,
            'canonical'   => url('/'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'noindex'     => true,
        ]);
    }
}
