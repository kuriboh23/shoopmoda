<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;

final class CartController
{
    public function index(): void
    {
        $items     = Cart::all();
        $subtotal  = Cart::subtotal();
        $delivery  = Cart::deliveryFee($subtotal);

        View::render('cart', [
            'title'       => 'Panier — ' . config('app_name'),
            'description' => 'Récapitulatif de votre commande. Paiement et suivi via WhatsApp.',
            'canonical'   => url('/panier'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'items'       => $items,
            'subtotal'    => $subtotal,
            'deliveryFee' => $delivery,
            'total'       => $subtotal + $delivery,
            'page'        => 'cart',
        ]);
    }

    public function add(): void
    {
        $payload = $this->payload();
        $slug    = trim((string) ($payload['slug'] ?? ''), '/');
        $quantity = isset($payload['quantity']) && ctype_digit((string) $payload['quantity'])
            ? (int) $payload['quantity']
            : 1;

        if ($slug === '' || $quantity < 1) {
            $this->respond(['ok' => false, 'error' => 'Quantité invalide'], 400);

            return;
        }

        $product = Product::findBySlug($slug);

        if ($product === null) {
            $this->respond(['ok' => false, 'error' => 'Produit introuvable'], 404);

            return;
        }

        $price = (float) ($product['sale_price'] ?: $product['price']);

        Cart::add($slug, [
            'name'     => (string) $product['title'],
            'price'    => $price,
            'quantity' => min(99, max(1, $quantity)),
            'size'     => $payload['size'] ?? null,
            'color'    => $payload['color'] ?? null,
            '_img'     => $product['primary_image'] ?? null,
        ]);

        $this->respond([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'message'   => 'Produit ajouté au panier',
        ], 200, '/panier');
    }

    public function remove(): void
    {
        $payload = $this->payload();
        $slug    = trim((string) ($payload['slug'] ?? ''), '/');

        if ($slug === '') {
            $this->respond(['ok' => false, 'error' => 'Identifiant produit manquant'], 400);

            return;
        }

        Cart::remove($slug);

        $this->respond([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'message'   => 'Produit retiré du panier',
        ], 200, '/panier');
    }

    public function update(): void
    {
        $payload  = $this->payload();
        $slug     = trim((string) ($payload['slug'] ?? ''), '/');
        $quantity = (int) ($payload['quantity'] ?? 0);

        if ($slug === '') {
            $this->respond(['ok' => false, 'error' => 'Identifiant produit manquant'], 400);

            return;
        }

        Cart::update($slug, $quantity);

        $subtotal = Cart::subtotal();
        $delivery = Cart::deliveryFee($subtotal);

        $this->respond([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'subtotal'  => format_price($subtotal),
            'delivery'  => format_price($delivery),
            'total'     => format_price($subtotal + $delivery),
        ], 200, '/panier');
    }

    public function empty(): void
    {
        Cart::clear();

        $this->respond([
            'ok'        => true,
            'cartCount' => 0,
            'message'   => 'Panier vidé',
        ], 200, '/panier');
    }

    /**
     * Accept both a JSON body (fetch) and a classic form POST.
     *
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $raw = file_get_contents('php://input') ?: '';

        if ($raw !== '') {
            $json = json_decode($raw, true);

            if (is_array($json)) {
                return $json;
            }
        }

        return $_POST;
    }

    /**
     * JSON response for fetch calls, redirect for regular form posts.
     *
     * @param array<string, mixed> $data
     */
    private function respond(array $data, int $status = 200, string $fallback = '/panier'): void
    {
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $type   = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $xhr    = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        if ($this->wantsJson($accept, $type, $xhr)) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);

            return;
        }

        redirect($fallback);
    }

    private function wantsJson(string $accept, string $type, string $xhr): bool
    {
        return str_contains($accept, 'application/json')
            || str_contains($type, 'application/json')
            || $xhr === 'xmlhttprequest';
    }
}
