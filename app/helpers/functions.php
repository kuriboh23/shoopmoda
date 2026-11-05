<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;

/**
 * Global helpers. Loaded once by bootstrap.php.
 */

/**
 * Escape any value for HTML output. Use this for EVERY dynamic output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Read a configuration value (app/config/*.php merged into one flat array).
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $config = [];

        foreach (glob(APP_PATH . '/config/*.php') ?: [] as $file) {
            $values = require $file;

            if (is_array($values)) {
                $config += $values;
            }
        }
    }

    return $config[$key] ?? $default;
}

/**
 * Build an absolute URL. Existing absolute URLs are returned untouched.
 */
function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $base = (string) config('app_url');
    $path = ltrim($path, '/');

    if ($path === '') {
        return $base . '/';
    }

    return $base . '/' . $path;
}

/**
 * Public asset URL with a filemtime cache-buster (safe to cache for a year).
 */
function asset(string $path): string
{
    static $versions = [];

    $relative = 'assets/' . ltrim($path, '/');

    if (!array_key_exists($relative, $versions)) {
        $file = PUBLIC_PATH . '/' . $relative;
        $versions[$relative] = is_file($file) ? (string) filemtime($file) : '';
    }

    $url = url($relative);

    return $versions[$relative] !== '' ? $url . '?v=' . $versions[$relative] : $url;
}

/**
 * Current request path (path + query string), used by the layout and Router.
 */
function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    $path = parse_url($uri, PHP_URL_PATH) ?? '/';

    $query = $_SERVER['QUERY_STRING'] ?? '';

    return $query !== '' ? $path . '?' . $query : $path;
}

/**
 * Current path name (the path only, no query string), used by the header
 * and the router as a fallback when REQUEST_URI is not reliable.
 */
function current_pathname(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';

    return parse_url($uri, PHP_URL_PATH) ?? '/';
}

/**
 * Current request method, uppercased (GET, POST, ...).
 */
function current_method(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}

/**
 * Build a URL that opens the store WhatsApp number with a pre-filled message.
 */
function whatsapp_url(string $message = ''): string
{
    static $number = null;

    if ($number === null) {
        $number = (string) config('whatsapp_number');
        $number = preg_replace('/\D+/', '', $number);
    }

    if ($message !== '') {
        $message = preg_replace('/\s+/', ' ', trim($message));
        $message = urlencode($message);
    } else {
        $message = '';
    }

    return 'https://wa.me/' . $number . ($message !== '' ? '?text=' . $message : '');
}

/**
 * Return the store icon sprite path.
 */
function icon(string $name, string $class = 'icon'): string
{
    // Forward to the Helpers class if it exists, otherwise return a placeholder
    // SVG class. Templates should call icon() only after helpers are loaded.
    if (class_exists(\App\Helpers\Helpers::class)) {
        return \App\Helpers\Helpers::icon($name, $class);
    }

    return $class;
}

/**
 * Read a single setting from the settings table / config fallback.
 */
function setting(string $key, ?string $default = null): ?string
{
    if (class_exists(\App\Models\Setting::class)) {
        return \App\Models\Setting::get($key, $default);
    }

    return $default;
}

/**
 * Return the best available image URL for a product.
 */
function product_image_url(int $productId, ?string $image = null): string
{
    if (class_exists(\App\Helpers\Helpers::class)) {
        return \App\Helpers\Helpers::productImageUrl($productId, $image);
    }

    return asset('images/placeholders/product.svg');
}

/**
 * Format a price for display.
 */
function format_price(?float $price = null): string
{
    if (class_exists(\App\Helpers\Helpers::class)) {
        return \App\Helpers\Helpers::formatPrice($price);
    }

    if ($price === null) {
        return (string) config('currency');
    }

    return (string) config('currency') . ' ' . number_format($price, 2, ',', ' ');
}

/**
 * Currency symbol only.
 */
function currency(?float $price = null): string
{
    return (string) config('currency');
}

/**
 * Current cart count.
 */
function cart_count(): int
{
    if (function_exists('session_count')) {
        return session_count();
    }

    if (class_exists(\App\Models\Cart::class)) {
        return \App\Models\Cart::count();
    }

    return (int) ($_SESSION['cart_count'] ?? 0);
}

/**
 * Current wishlist count.
 */
function wish_count(): int
{
    if (function_exists('session_wishlist_count')) {
        return session_wishlist_count();
    }

    if (class_exists(\App\Models\Cart::class)) {
        return \App\Models\Cart::wishCount();
    }

    return (int) ($_SESSION['wishlist_count'] ?? 0);
}

/**
 * Return a formatted flash sale alert message, or null if none is active.
 */
function flash_sale_alert(): ?string
{
    if (class_exists(\App\Models\Product::class)) {
        $message = \App\Models\Product::flashAlertMessage();

        if ($message !== null) {
            return $message;
        }
    }

    if (class_exists(\App\Models\ProductModel::class)) {
        return \App\Models\ProductModel::flashAlertMessage();
    }

    return null;
}

/**
 * Return the next flash sale end date, or null if none.
 */
function flash_sale_ends_at(): ?string
{
    if (class_exists(\App\Models\Product::class)) {
        return \App\Models\Product::nextFlashEnd();
    }

    if (class_exists(\App\Models\ProductModel::class)) {
        return \App\Models\ProductModel::nextFlashEnd();
    }

    return null;
}
