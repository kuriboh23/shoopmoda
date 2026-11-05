<?php

declare(strict_types=1);

/**
 * Router for PHP's built-in server (development only).
 *
 *   php -S 127.0.0.1:8080 tools/dev-router.php
 *
 * Serves real files and blocks the application folders, then hands
 * everything else to the front controller (emulates .htaccess).
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? rawurldecode($path) : '/';

// Never expose code, logs, cache or environment files.
if (preg_match('#^/(app|storage|tools|vendor|database)(/|$)#', $path) === 1
    || str_starts_with($path, '/.')
) {
    http_response_code(404);
    echo '404 - Page introuvable';

    return true;
}

$file = __DIR__ . '/..' . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/../index.php';
