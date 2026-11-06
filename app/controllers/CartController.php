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

        // The product page button asks for the delivery details before the
        // WhatsApp hand-off, so it adds the piece and goes to checkout.
        $toCheckout = (string) ($payload['after'] ?? '') === 'checkout';

        $this->respond([
            'ok'        => true,
            'cartCount' => Cart::count(),
            'message'   => 'Produit ajouté au panier',
            'redirect'  => $toCheckout ? url('/commande') : null,
        ], 200, $toCheckout ? '/commande' : '/panier');
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

    /**
     * Checkout: collect the delivery details before opening WhatsApp.
     */
    public function checkout(): void
    {
        if (Cart::all() === []) {
            redirect('/panier');
        }

        $this->renderCheckout([], [
            'name'    => '',
            'phone'   => '',
            'city'    => '',
            'address' => '',
            'note'    => '',
        ]);
    }

    /**
     * Validate the customer details and hand the order to WhatsApp.
     */
    public function placeOrder(): void
    {
        $items = Cart::all();

        if ($items === []) {
            redirect('/panier');
        }

        $old = [
            'name'    => $this->cleanLine((string) ($_POST['name'] ?? ''), 80),
            'phone'   => $this->cleanLine((string) ($_POST['phone'] ?? ''), 20),
            'city'    => $this->cleanLine((string) ($_POST['city'] ?? ''), 60),
            'address' => $this->cleanLine((string) ($_POST['address'] ?? ''), 200),
            'note'    => $this->cleanLine((string) ($_POST['note'] ?? ''), 300),
        ];

        $errors = [];

        if (!csrf_verify($_POST['_csrf'] ?? null)) {
            $errors['form'] = 'Session expirée. Merci de renvoyer le formulaire.';
        }

        if (mb_strlen($old['name']) < 2) {
            $errors['name'] = 'Merci d’indiquer votre nom complet.';
        }

        if (preg_match('/^[0-9+\s().-]{9,20}$/', $old['phone']) !== 1) {
            $errors['phone'] = 'Numéro de téléphone invalide.';
        }

        if (mb_strlen($old['city']) < 2) {
            $errors['city'] = 'Merci d’indiquer votre ville.';
        }

        if (mb_strlen($old['address']) < 5) {
            $errors['address'] = 'Merci d’indiquer une adresse complète.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->renderCheckout($errors, $old);

            return;
        }

        $subtotal = Cart::subtotal();
        $delivery = Cart::deliveryFee($subtotal);
        $message  = $this->orderMessage($old, $items, $subtotal, $delivery);

        Cart::clear();

        redirect_external(whatsapp_url($message, true));
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, string> $old
     */
    private function renderCheckout(array $errors, array $old): void
    {
        $items    = Cart::all();
        $subtotal = Cart::subtotal();
        $delivery = Cart::deliveryFee($subtotal);

        View::render('checkout', [
            'title'       => 'Finaliser ma commande — ' . config('app_name'),
            'description' => 'Renseignez vos informations de livraison : la commande est confirmée sur WhatsApp.',
            'canonical'   => url('/commande'),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'items'       => $items,
            'subtotal'    => $subtotal,
            'deliveryFee' => $delivery,
            'total'       => $subtotal + $delivery,
            'errors'      => $errors,
            'old'         => $old,
            'noindex'     => true,
            'page'        => 'checkout',
        ]);
    }

    /**
     * Keep customer input on a single line so it cannot break the message.
     */
    private function cleanLine(string $value, int $max): string
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';

        return mb_substr($value, 0, $max);
    }

    /**
     * @param array<string, string> $customer
     * @param array<string, array<string, mixed>> $items
     */
    private function orderMessage(array $customer, array $items, float $subtotal, float $delivery): string
    {
        $lines   = [];
        $lines[] = '🛍️ *Nouvelle commande* — ' . config('app_name');
        $lines[] = '';
        $lines[] = '*👤 Client*';
        $lines[] = '• Nom : ' . $customer['name'];
        $lines[] = '• Téléphone : ' . $customer['phone'];
        $lines[] = '• Ville : ' . $customer['city'];
        $lines[] = '• Adresse : ' . $customer['address'];

        if ($customer['note'] !== '') {
            $lines[] = '• Note : ' . $customer['note'];
        }

        $lines[] = '';
        $lines[] = '*📦 Articles*';

        $index = 1;

        foreach ($items as $item) {
            $variant = [];

            if (!empty($item['size'])) {
                $variant[] = 'taille ' . $item['size'];
            }

            if (!empty($item['color'])) {
                $variant[] = 'couleur ' . $item['color'];
            }

            $lines[] = $index . '. ' . $item['name']
                . ($variant !== [] ? ' (' . implode(', ', $variant) . ')' : '')
                . ' — ×' . (int) ($item['quantity'] ?? 1)
                . ' : ' . format_price((float) $item['price'] * (int) ($item['quantity'] ?? 1));

            $index++;
        }

        $lines[] = '';
        $lines[] = '*💰 Total*';
        $lines[] = 'Sous-total : ' . format_price($subtotal);
        $lines[] = 'Livraison : ' . ($delivery > 0 ? format_price($delivery) : 'Offerte');
        $lines[] = '*À payer à la livraison : ' . format_price($subtotal + $delivery) . '*';
        $lines[] = '';
        $lines[] = 'Merci de confirmer la commande. 🙏';

        return implode("\n", $lines);
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
