<?php

declare(strict_types=1);

/**
 * Shoop Moda Style - single entry point.
 *
 * Every request ends up here and is matched against the routes in
 * app/routes.php. The page normally arrives in the URL (/panier), thanks to
 * the rewrite rules of .htaccess; without that file the same page still
 * works as /?p=/panier, so the shop never depends on the server setup.
 *
 * This file must stay at the root of the site: index.php, .htaccess, assets/,
 * uploads/ and config.php sit side by side in htdocs/ (or public_html/). No
 * .env, no database install, nothing else to configure on the server.
 */

$router = require __DIR__ . '/app/bootstrap.php';

$router->dispatch();
