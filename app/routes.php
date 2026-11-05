<?php

declare(strict_types=1);

use App\Controllers\CartController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\SearchController;
use App\Controllers\WishlistController;
use App\Core\Router;

/**
 * Route table.
 *
 * Placeholders use the `{name}` syntax understood by App\Core\Router:
 * a regex written by hand is quoted literally and would never match.
 *
 * @var Router $router
 */

$router->get('/', [HomeController::class, 'index']);

$router->get('/categorie/{slug}', [ProductController::class, 'category']);
$router->get('/produit/{slug}', [ProductController::class, 'product']);
$router->get('/promotions', [ProductController::class, 'promotions']);

$router->get('/recherche', [SearchController::class, 'index']);

$router->get('/favoris', [WishlistController::class, 'index']);
$router->post('/favoris/ajouter', [WishlistController::class, 'toggle']);

$router->get('/panier', [CartController::class, 'index']);
$router->post('/panier/ajouter', [CartController::class, 'add']);
$router->post('/panier/retirer', [CartController::class, 'remove']);
$router->post('/panier/quantite', [CartController::class, 'update']);
$router->post('/panier/vider', [CartController::class, 'empty']);

$router->notFound([ErrorController::class, 'notFound']);
