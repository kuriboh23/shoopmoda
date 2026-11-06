<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;

/**
 * Global template helpers. Loaded once by bootstrap.php.
 *
 * Everything the templates need to build a link lives here: url() is
 * root-relative, so the shop works on any domain and in any folder without
 * being told its own address.
 */

/**
 * Escape any value for HTML output. Use this for EVERY dynamic output.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Folder the site is served from, discovered from the running script.
 *
 *   https://example.com/            -> ""          (document root, htdocs/)
 *   https://example.com/shop/       -> "/shop"     (sub-folder install)
 *
 * This is the only "base path" logic in the project: no setting to keep in
 * sync with the real address, so links and assets can never point at the
 * wrong host.
 */
function base_path(): string
{
    static $base = null;

    if ($base === null) {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
        $base   = rtrim(str_replace('\\', '/', dirname($script)), '/');

        if ($base === '/' || $base === '.') {
            $base = '';
        }
    }

    return $base;
}

/**
 * Read a setting from config.php at the root of the site.
 */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;

    if ($config === null) {
        $root    = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $config  = [];
        $file    = $root . '/config.php';

        if (is_file($file)) {
            $values = require $file;

            if (is_array($values)) {
                $config = $values;
            }
        }
    }

    return $config[$key] ?? $default;
}

/**
 * Should links look like /produit/robe instead of /?p=/produit/robe?
 *
 * Pretty URLs need the ".htaccess" file sitting next to index.php, because
 * that is what forwards /produit/robe to index.php on the server.
 *
 * The setting is on, but the file is checked too: FTP clients hide names
 * starting with a dot, so .htaccess is the one file people forget to upload.
 * When it is absent the links quietly become /?p=/produit/robe, which works
 * on every host with no configuration at all. Either way the shop runs.
 */
function pretty_urls(): bool
{
    static $pretty = null;

    if ($pretty === null) {
        $root   = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
        $pretty = (bool) config('pretty_urls', true) && is_file($root . '/.htaccess');
    }

    return $pretty;
}

/**
 * Link to a page of the site: "/?p=/panier", or "/panier" when clean URLs
 * are turned on in config.php.
 *
 * Absolute URLs (https://wa.me/...) are returned untouched. Real files
 * (app.css, photo.webp, favicon.svg) are always linked directly, never
 * through the router, so styles and images load in every mode.
 */
function url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $base  = base_path();
    $query = '';
    $hash  = '';

    if (($mark = strpos($path, '#')) !== false) {
        $hash = substr($path, $mark);
        $path = substr($path, 0, $mark);
    }

    if (($mark = strpos($path, '?')) !== false) {
        $query = substr($path, $mark);
        $path  = substr($path, 0, $mark);
    }

    $path = trim($path, '/');

    // A last segment containing a dot is a file on disk, not a page.
    if ($path !== '' && str_contains(basename($path), '.')) {
        return $base . '/' . $path . $query . $hash;
    }

    // Home page.
    if ($path === '') {
        return $base . '/' . $query . $hash;
    }

    if (pretty_urls()) {
        return $base . '/' . $path . $query . $hash;
    }

    // No rewrite on the host: the page travels in "p", read by the router.
    $link = $base . '/?p=' . rawurlencode('/' . $path);

    if ($query !== '') {
        $link .= '&' . ltrim($query, '?');
    }

    return $link . $hash;
}

/**
 * Hidden field that keeps the current page when a GET form is submitted.
 *
 * A GET form REPLACES the query string of its action, which would drop the
 * "p" part and send the visitor to the home page. This field puts it back.
 * It prints nothing when clean URLs are on, where the page is in the path
 * and cannot be lost.
 */
function route_field(string $path): string
{
    if (pretty_urls()) {
        return '';
    }

    return '<input type="hidden" name="p" value="' . e('/' . trim($path, '/')) . '">';
}

/**
 * Full URL including the domain, built from the current request.
 * Used for canonical / Open Graph tags, which must be absolute.
 *
 * It accepts either a page (/panier) or a link that url() already built
 * (/shop/panier, /shop/?p=/panier). A finished link is only given the
 * domain, never encoded a second time.
 */
function abs_url(string $path = ''): string
{
    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $origin = ($https ? 'https' : 'http') . '://' . $host;
    $base   = base_path();

    // Already a complete site path: just add the domain.
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        return $origin . '/' . ltrim($path, '/');
    }

    return $origin . url($path);
}

/**
 * Asset URL with a filemtime cache-buster. Safe to cache for a year.
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
 * Current page path with its query string, without the install folder.
 * Example: "/recherche?q=robe". The routing parameter "p" is left out, so
 * url(current_path()) gives back a working link.
 */
function current_path(): string
{
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');

    if (isset($_GET['p'])) {
        parse_str($query, $parameters);
        unset($parameters['p']);
        $query = http_build_query($parameters);
    }

    return $query !== '' ? Router::path() . '?' . $query : Router::path();
}

/**
 * Current page path only, without the install folder. Used by the header and
 * the drawer to highlight the active menu entry.
 */
function current_pathname(): string
{
    return Router::path();
}

/**
 * Current request method, uppercased (GET, POST, ...).
 */
function current_method(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}

/**
 * Build a URL that opens the shop WhatsApp number with a pre-filled message.
 */
function whatsapp_url(string $message = '', bool $preserveFormatting = false): string
{
    static $number = null;

    if ($number === null) {
        // The number saved in the admin area wins over config.php, so changing
        // it once updates every WhatsApp link of the shop.
        $number = whatsapp_number();
    }

    if ($message !== '') {
        if (!$preserveFormatting) {
            $message = preg_replace('/\s+/', ' ', trim($message));
        }

        $message = rawurlencode(trim($message));
    } else {
        $message = '';
    }

    return 'https://wa.me/' . $number . ($message !== '' ? '?text=' . $message : '');
}

/**
 * WhatsApp number in international format, digits only (212XXXXXXXXX).
 */
function whatsapp_number(): string
{
    $raw = setting('whatsapp_number') ?? config('whatsapp_number');

    return preg_replace('/\D+/', '', (string) $raw) ?? '';
}

/**
 * Same number written the way the owner reads it (212725605520 -> 07 25 60 55 20).
 */
function whatsapp_display(): string
{
    $digits = whatsapp_number();

    if ($digits === '') {
        return '';
    }

    // Moroccan numbers: 212 + 9 digits, displayed locally with a leading 0.
    $local = str_starts_with($digits, '212') && strlen($digits) === 12
        ? '0' . substr($digits, 3)
        : $digits;

    return trim((string) chunk_split($local, 2, ' '));
}

/**
 * Inline SVG icon markup.
 */
function icon(string $name, string $class = 'icon'): string
{
    if (class_exists(\App\Helpers\Icons::class)) {
        return \App\Helpers\Icons::inline($name, $class);
    }

    return '';
}

/**
 * Render a partial from inside a view or the layout.
 *
 * @param array<string, mixed> $data
 */
function partial(string $name, array $data = []): void
{
    if (class_exists(View::class) && method_exists(View::class, 'partial')) {
        View::partial($name, $data);
    }
}

/**
 * Redirect to a page of the site and stop the request.
 */
function redirect(string $path = '/'): void
{
    header('Location: ' . url($path), true, 302);
    exit;
}

/**
 * Read a single setting from the settings table, falling back to config.php.
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

    return asset('images/placeholders/default.svg');
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
 * Formatted flash sale alert message, or null when none is active.
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
 * Next flash sale end date, or null when there is none.
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

/**
 * CSRF token bound to the current session.
 */
function csrf_token(): string
{
    $token = \App\Core\Session::get('_csrf');

    if (!is_string($token) || strlen($token) < 32) {
        $token = bin2hex(random_bytes(32));
        \App\Core\Session::set('_csrf', $token);
    }

    return $token;
}

/**
 * Hidden CSRF input for a POST form.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Constant-time check of a submitted CSRF token.
 */
function csrf_verify(mixed $token): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals(csrf_token(), $token);
}

/**
 * Store a one-shot message shown on the next page.
 */
function flash(string $type, string $message): void
{
    \App\Core\Session::set('_flash', ['type' => $type, 'message' => $message]);
}

/**
 * Read and clear the one-shot message.
 *
 * @return array{type: string, message: string}|null
 */
function flash_message(): ?array
{
    $flash = \App\Core\Session::get('_flash');

    if (!is_array($flash) || empty($flash['message'])) {
        return null;
    }

    \App\Core\Session::remove('_flash');

    return [
        'type'    => (string) ($flash['type'] ?? 'success'),
        'message' => (string) $flash['message'],
    ];
}

/**
 * Redirect to an external URL (WhatsApp, for instance).
 */
function redirect_external(string $url): void
{
    header('Location: ' . $url, true, 302);
    exit;
}

/**
 * URL inside the admin area (folder configured by 'admin_path').
 */
function admin_url(string $path = ''): string
{
    $base = trim((string) config('admin_path'), '/');
    $path = trim($path, '/');

    return url('/' . $base . ($path !== '' ? '/' . $path : ''));
}
