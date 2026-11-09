<?php

declare(strict_types=1);

use App\Core\Router;
use App\Core\View;

/**
 * Application bootstrap: paths, autoloader, helpers, configuration, error
 * handling and security headers.
 *
 * Returns the Router, so index.php only has to dispatch.
 *
 * Nothing in this file needs to change to host the shop. Every setting the
 * owner may want to edit lives in config.php at the project root.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
// index.php, assets/, uploads/ and config.php sit next to each other, so the
// public root is the project root: upload the folder straight into htdocs/.
define('PUBLIC_PATH', ROOT_PATH);
define('STORAGE_PATH', ROOT_PATH . '/storage');

/**
 * Autoloader for the App\ namespace.
 *
 * Folders on disk are lowercase (app/core, app/controllers, ...) while class
 * names are StudlyCase, so only the folders are lowercased. This keeps the
 * project portable to case-sensitive Linux hosting.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $segments  = explode('\\', substr($class, strlen($prefix)));
    $file      = array_pop($segments);
    $directory = implode('/', array_map('strtolower', $segments));

    $path = APP_PATH . '/' . ($directory !== '' ? $directory . '/' : '') . $file . '.php';

    if (is_file($path)) {
        require $path;
    }
});

// Global template helpers (e(), url(), config(), csrf_*, ...). They are plain
// functions, which the autoloader cannot load, so they come first.
require APP_PATH . '/helpers/functions.php';
require APP_PATH . '/helpers/Helpers.php';
require APP_PATH . '/helpers/icons.php';
require APP_PATH . '/helpers/Products.php';

// Models are loaded explicitly as well: the first controller may use them
// before any autoload has happened.
require APP_PATH . '/models/Setting.php';
require APP_PATH . '/models/Cart.php';
require APP_PATH . '/models/Product.php';
require APP_PATH . '/models/Category.php';

$debug = (bool) config('app_debug', false);

error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('default_charset', 'UTF-8');
ini_set('session.use_strict_mode', '1');

date_default_timezone_set((string) config('timezone', 'Africa/Casablanca'));
mb_internal_encoding('UTF-8');

// Runtime folders must exist even on a fresh upload.
foreach ([STORAGE_PATH . '/cache', STORAGE_PATH . '/logs'] as $directory) {
    if (!is_dir($directory)) {
        // Some shared hosts block folder creation. The shop still runs; it
        // only loses the file cache and the log file.
        @mkdir($directory, 0775, true);
    }
}

/**
 * Write an exception to storage/logs without exposing secrets.
 */
$logException = static function (Throwable $exception) use ($debug): void {
    $line = sprintf(
        "[%s] %s: %s in %s:%d\n%s\n",
        date('Y-m-d H:i:s'),
        $exception::class,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $debug ? $exception->getTraceAsString() : ''
    );

    @file_put_contents(
        STORAGE_PATH . '/logs/app-' . date('Y-m-d') . '.log',
        $line,
        FILE_APPEND | LOCK_EX
    );
};

// In development every warning becomes an exception, so bugs are never silent.
// In production warnings are logged and the page keeps working.
set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($debug): bool {
    if ((error_reporting() & $severity) === 0) {
        return false;
    }

    if ($debug) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    @file_put_contents(
        STORAGE_PATH . '/logs/php-' . date('Y-m-d') . '.log',
        sprintf("[%s] %s in %s:%d\n", date('Y-m-d H:i:s'), $message, $file, $line),
        FILE_APPEND | LOCK_EX
    );

    return true;
});

set_exception_handler(static function (Throwable $exception) use ($logException, $debug): void {
    $logException($exception);

    if (!headers_sent()) {
        http_response_code(500);
    }

    if ($debug) {
        echo '<pre class="debug-trace">';
        echo htmlspecialchars($exception::class . ': ' . $exception->getMessage(), ENT_QUOTES, 'UTF-8');
        echo "\n\n" . htmlspecialchars($exception->getFile() . ':' . $exception->getLine(), ENT_QUOTES, 'UTF-8');
        echo "\n\n" . htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES, 'UTF-8');
        echo '</pre>';

        return;
    }

    try {
        View::render('errors/500', ['title' => 'Erreur serveur'], 'layouts/main');
    } catch (Throwable) {
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Une erreur est survenue. Merci de réessayer.';
    }
});

// Security headers. Apache also sets the important ones for static files.
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: DENY');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data:; "
        . "style-src 'self'; "
        . "script-src 'self'; "
        . "font-src 'self'; "
        . "connect-src 'self'; "
        // Chrome and Safari apply form-action to the redirect that follows a
        // form POST. The checkout ends on WhatsApp, so those hosts are part
        // of the allowlist; otherwise the browser silently drops the redirect
        // after the order has already cleared the cart.
        . "form-action 'self' https://wa.me https://api.whatsapp.com https://web.whatsapp.com whatsapp:; "
        . "base-uri 'self'; "
        . "object-src 'none'; "
        . "frame-ancestors 'none'"
    );

    if ($https) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

$router = new Router();

require APP_PATH . '/routes.php';

// Controllers live in app/controllers/ and follow the autoloader above. They
// are required here so the very first request never depends on autoload
// order on unusual hosting setups.
require APP_PATH . '/controllers/HomeController.php';
require APP_PATH . '/controllers/ProductController.php';
require APP_PATH . '/controllers/CartController.php';
require APP_PATH . '/controllers/SearchController.php';
require APP_PATH . '/controllers/WishlistController.php';
require APP_PATH . '/controllers/AdminController.php';
require APP_PATH . '/controllers/ReskinController.php';
require APP_PATH . '/controllers/ErrorController.php';

return $router;
