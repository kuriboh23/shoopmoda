<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;

final class CartController
{
    public function index(): void
    {
        $items   = Cart::all();
        $totals  = Cart::totals();
        $payload = json_decode((string) (file_get_contents('php://input') ?: '{}'), true) ?: [];
        $returnTo = isset($payload['return_to']) && is_string($payload['return_to'])
            ? $payload['return_to']
            : url('/panier');

        View::render('cart', [
            'title'     => 'Panier — ' . config('app_name'),
            'description' => 'Récapitulatif de votre commande. Paiement et suivi via WhatsApp.',
            'canonical' => url('/panier'),
            'cartCount' => Cart::count(),
            'wishCount' => 0,
            'items'     => $items,
            'totals'    => $totals,
            'empty'     => empty($items),
            'returnTo'  => $returnTo,
            'crumbs'    => [
                ['label' => 'Accueil', 'url' => url('/')],
                ['label' => 'Panier', 'url' => null],
            ],
            'page'      => 'cart',
        ]);
    }

    public function add(): void
    {
        $payload = json_decode((string) (file_get_contents('php://input') ?: '{}'), true) ?: [];

        $slug  = trim((string) ($payload['slug'] ?? ''), '/');
        $qty   = isset($payload['qty']) && ctype_digit((string) $payload['qty']) ? (int) $payload['qty'] : 1;

        if ($slug === '' || $qty < 1) {
            $this->jsonError('Quantité invalide', 400);

            return;
        }

        $product = \App\Models\Product::findBySlug($slug);

        if ($product === null) {
            $this->jsonError('Produit introuvable', 404);

            return;
        }

        Cart::add(
            $slug,
            $product['title'],
            (float) ($product['price'] ?? 0),
            $qty,
            $product['images'] ?? ''
        );

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'message'   => 'Produit ajouté au panier',
        ]);
    }

    public function remove(): void
    {
        $payload = json_decode((string) (file_get_contents('php://input') ?: '{}'), true) ?: [];
        $slug    = trim((string) ($payload['slug'] ?? ''), '/');

        if ($slug === '') {
            $this->jsonError('Identifiant produit manquant', 400);

            return;
        }

        Cart::remove($slug);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'message'   => 'Produit retiré du panier',
        ]);
    }

    public function empty(): void
    {
        Cart::clear();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'        => true,
            'cartCount' => 0,
            'message'   => 'Panier vidé',
        ]);
    }

    private function jsonError(string $message, int $code): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $message]);
    }
}
