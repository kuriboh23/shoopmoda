<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Product;
use App\Models\Setting;

/** Convenience wrappers so templates stay terse. */
final class Helpers
{
    /**
     * Build an absolute URL for a path.
     */
    public static function url(string $path = ''): string
    {
        $base = rtrim(self::baseUrl(), '/');

        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Base URL from APP_URL; fall back to current scheme + host.
     */
    public static function baseUrl(): string
    {
        $appUrl = getenv('APP_URL');

        if ($appUrl !== false && $appUrl !== '') {
            return $appUrl;
        }

        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return "{$scheme}://{$host}";
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
     * SVG icon from the sprite sheet.
     */
    public static function icon(string $name, string $class = 'icon'): string
    {
        $spritePath = APP_PATH . '/assets/sprite.svg';

        if (!is_file($spritePath)) {
            return '';
        }

        $defs = file_get_contents($spritePath);
        if ($defs === false) {
            return '';
        }

        $pattern = '#<symbol[^>]+\bid=["\']' . preg_quote($name, '#') . '["\'][^>]*>(.*?)</symbol>#s';
        if (!preg_match($pattern, $defs, $m)) {
            return '';
        }

        $svg = trim($m[0]);

        return preg_replace(
            '#\bid=["\'][^"\']+["\']#',
            'id="icon-' . $name . '-' . substr(md5($name . mt_rand()), 0, 6) . '"',
            $svg
        );
    }

    /**
     * Product image URL — prefer the primary stored image, fall back to the generated placeholder.
     */
    public static function productImageUrl(int $productId, ?string $image = null): string
    {
        $catalogue = APP_PATH . '/storage/products';

        if ($image !== null && is_file($catalogue . '/' . $image)) {
            return self::assetUrl('storage/products/' . $image);
        }

        $placeholder = $catalogue . '/' . $productId . '.webp';

        if (is_file($placeholder)) {
            return self::assetUrl('storage/products/' . $productId . '.webp');
        }

        return self::assetUrl('assets/placeholder.webp');
    }

    /**
     * Asset path relative to the public root.
     */
    public static function assetUrl(string $path): string
    {
        return rtrim(self::baseUrl(), '/') . '/' . ltrim($path, '/');
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

        $whole = number_format($price, 0, '.', ' ');
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
        return (float) Setting::getFloat('delivery_fee', 25);
    }

    /**
     * Flashsale alert message for the announcement bar.
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
        $phone = Setting::get('whatsapp_phone', '212725605520');
        $text = rawurlencode($message);

        return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) . '?text=' . $text;
    }

    /**
     * Active announcement bar copy assembled from settings on every request.
     *
     * @return array{emoji: string, text: string, button_text: string, button_url: string}
     */
    public static function announcementBar(): array
    {
        $emoji = Setting::get('announcement_emoji', '📦');
        $text = Setting::get('announcement_text', 'Livraison rapide partout au Maroc');
        $buttonText = Setting::get('announcement_button_text', 'En savoir plus');
        $buttonUrl = Setting::get('announcement_button_url', '');

        $flash = self::flashAlertMessage();
        if ($flash !== null) {
            $text = $flash;
            $buttonText = Setting::get('flash_button_text', 'Voir les offres');
            $buttonUrl = Setting::get('flash_button_url', '/');
        }

        return compact('emoji', 'text', 'buttonText', 'buttonUrl');
    }
}

// Keep these aliases so existing templates keep working.
function url(string $path = ''): string
{
    return Helpers::url($path);
}

function e(mixed $value): string
{
    return Helpers::e($value);
}

function icon(string $name, string $class = 'icon'): string
{
    return Helpers::icon($name, $class);
}

function setting(string $key, ?string $default = null): ?string
{
    return \App\Models\Setting::get($key, $default);
}

function product_image_url(int $productId, ?string $image = null): string
{
    return Helpers::productImageUrl($productId, $image);
}

function currency(?float $price = null): string
{
    return Helpers::currency($price);
}

function format_price(?float $price = null): string
{
    return Helpers::formatPrice($price);
}

function delivery_fee(): float
{
    return Helpers::deliveryFee();
}

function whatsapp_url(string $message = ''): string
{
    return Helpers::whatsappUrl($message);
}

function cart_count(): int
{
    if (function_exists('session_count')) {
        return session_count();
    }

    return (int) ($_SESSION['cart_count'] ?? 0);
}

function wish_count(): int
{
    if (function_exists('session_wishlist_count')) {
        return session_wishlist_count();
    }

    return (int) ($_SESSION['wishlist_count'] ?? 0);
}

function flash_sale_alert(): ?string
{
    return Helpers::flashAlertMessage();
}

function flash_sale_ends_at(): ?string
{
    return \App\Models\Product::nextFlashEnd();
}
