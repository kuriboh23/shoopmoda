<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Product;
use App\Models\Setting;

/**
 * Namespaced convenience wrappers.
 *
 * The global template helpers (url(), e(), icon(), …) live in
 * app/helpers/functions.php only: defining them twice makes PHP fatal on
 * "Cannot redeclare".
 */
final class Helpers
{
    /**
     * Link to a page of the site (root-relative, see url()).
     */
    public static function url(string $path = ''): string
    {
        return \url($path);
    }

    /**
     * Absolute base URL, built from the current request.
     */
    public static function baseUrl(): string
    {
        return \abs_url('');
    }

    /**
     * HTML-escape a value for safe output.
     */
    public static function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Inline SVG icon (no sprite request, no external icon font).
     */
    public static function icon(string $name, string $class = 'icon'): string
    {
        return Icons::inline($name, $class);
    }

    /**
     * Product image URL.
     *
     * The database stores a path relative to the public root
     * ("assets/images/placeholders/xxx.svg" or "uploads/xxx.webp").
     * Missing files fall back to the branded default placeholder so a
     * product card is never rendered with a broken image.
     */
    public static function productImageUrl(int $productId, ?string $image = null): string
    {
        $image = trim((string) $image);

        if ($image !== '') {
            if (preg_match('#^https?://#i', $image) === 1) {
                return $image;
            }

            $image = ltrim(str_replace('\\', '/', $image), '/');

            $candidates = [$image];

            if (!str_starts_with($image, 'assets/') && !str_starts_with($image, 'uploads/')) {
                $candidates[] = 'uploads/' . $image;
                $candidates[] = 'assets/images/products/' . $image;
            }

            foreach ($candidates as $candidate) {
                if (is_file(PUBLIC_PATH . '/' . $candidate)) {
                    return self::assetUrl($candidate);
                }
            }
        }

        return self::assetUrl('assets/images/placeholders/default.svg');
    }

    /**
     * Asset path relative to the site root.
     */
    public static function assetUrl(string $path): string
    {
        return \url($path);
    }

    /**
     * Format a price with the configured currency.
     */
    public static function currency(?float $price = null): string
    {
        $currency = Setting::get('currency', 'MAD');

        $currencyCode = ($currency === 'MAD') ? 'DH' : $currency;

        if ($price === null) {
            return $currencyCode;
        }

        $whole   = number_format($price, 0, '.', ' ');
        $decimal = (fmod($price, 1) > 0.005) ? number_format($price, 2, '.', ' ') : $whole;

        return $currencyCode . ' ' . $decimal;
    }

    /**
     * Full price string including currency symbol.
     */
    public static function formatPrice(?float $price = null): string
    {
        return self::currency($price);
    }

    /**
     * Delivery fee from settings.
     */
    public static function deliveryFee(): float
    {
        return (float) Setting::getFloat('delivery_fee_economy', 25);
    }

    /**
     * Flash sale alert message for the announcement bar.
     */
    public static function flashAlertMessage(): ?string
    {
        return Product::flashAlertMessage();
    }

    /**
     * WhatsApp pre-filled message URL.
     */
    public static function whatsappUrl(string $message = ''): string
    {
        $phone = preg_replace('/[^0-9]/', '', \whatsapp_number()) ?? '';

        if ($message === '') {
            return 'https://wa.me/' . $phone;
        }

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    /**
     * Active announcement bar copy assembled from settings on every request.
     *
     * @return array{emoji: string, text: string, buttonText: string, buttonUrl: string}
     */
    public static function announcementBar(): array
    {
        $emoji      = Setting::get('announcement_emoji', '📦');
        $text       = Setting::get('announcement_text', 'Livraison rapide partout au Maroc');
        $buttonText = Setting::get('announcement_button_text', 'En savoir plus');
        $buttonUrl  = Setting::get('announcement_button_url', '');

        $flash = self::flashAlertMessage();
        if ($flash !== null) {
            $text       = $flash;
            $buttonText = Setting::get('flash_button_text', 'Voir les offres');
            $buttonUrl  = Setting::get('flash_button_url', '/');
        }

        return compact('emoji', 'text', 'buttonText', 'buttonUrl');
    }
}
