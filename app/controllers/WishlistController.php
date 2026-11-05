<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;

final class WishlistController
{
    public function index(): void
    {
        View::render('wishlist', [
            'title'       => 'Mes favoris — ' . config('app_name'),
            'description' => 'Votre sélection de produits favoris chez Shoop Moda Style.',
            'canonical'   => url('/favoris'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
            'products'    => Wishlist::products(),
            'noindex'     => true,
            'page'        => 'wishlist',
        ]);
    }

    public function toggle(): void
    {
        $raw     = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        $payload = is_array($payload) ? $payload : $_POST;

        $slug = trim((string) ($payload['slug'] ?? ''), '/');

        if ($slug === '' || Product::findBySlug($slug) === null) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Produit introuvable']);

            return;
        }

        $active = Wishlist::toggle($slug);

        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $type   = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (str_contains($accept, 'application/json') || str_contains($type, 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'        => true,
                'active'    => $active,
                'wishCount' => Wishlist::count(),
                'message'   => $active ? 'Ajouté aux favoris' : 'Retiré des favoris',
            ]);

            return;
        }

        redirect('/favoris');
    }
}
