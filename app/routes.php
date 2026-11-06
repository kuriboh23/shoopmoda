<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\CartController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\SearchController;
use App\Controllers\WishlistController;
use App\Core\Router;

/**
 * URL TABLE - one line per page.
 *
 * Format:  $router->get('/url', [Controller::class, 'method']);
 *
 * A {placeholder} matches any single value and is passed to the method as a
 * string. Never write a regex here: the router only understands {name}.
 *
 * Adding a page = add one line here + one method in the controller.
 *
 * @var Router $router
 */

$router->get('/', [HomeController::class, 'index']);

// Catalogue
$router->get('/produits', [ProductController::class, 'catalog']);
$router->get('/categorie/{slug}', [ProductController::class, 'category']);
$router->get('/produit/{slug}', [ProductController::class, 'product']);
$router->get('/promotions', [ProductController::class, 'promotions']);

// Search
$router->get('/recherche', [SearchController::class, 'index']);
$router->get('/recherche/suggestions', [SearchController::class, 'suggestions']);

// Wishlist
$router->get('/favoris', [WishlistController::class, 'index']);
$router->post('/favoris/ajouter', [WishlistController::class, 'toggle']);

// Cart
$router->get('/panier', [CartController::class, 'index']);
$router->post('/panier/ajouter', [CartController::class, 'add']);
$router->post('/panier/retirer', [CartController::class, 'remove']);
$router->post('/panier/quantite', [CartController::class, 'update']);
$router->post('/panier/vider', [CartController::class, 'empty']);

// Checkout (sends the order to WhatsApp, no payment on the site)
$router->get('/commande', [CartController::class, 'checkout']);
$router->post('/commande', [CartController::class, 'placeOrder']);

// Admin area. The folder name comes from 'admin_path' in config.php.
$adminPath = '/' . trim((string) config('admin_path'), '/');

$router->get($adminPath . '/connexion', [AdminController::class, 'loginForm']);
$router->post($adminPath . '/connexion', [AdminController::class, 'login']);
$router->post($adminPath . '/deconnexion', [AdminController::class, 'logout']);

$router->get($adminPath, [AdminController::class, 'dashboard']);
$router->get($adminPath . '/produits', [AdminController::class, 'products']);
$router->get($adminPath . '/produits/nouveau', [AdminController::class, 'createForm']);
$router->post($adminPath . '/produits/nouveau', [AdminController::class, 'create']);
$router->get($adminPath . '/produits/{id}', [AdminController::class, 'editForm']);
$router->post($adminPath . '/produits/{id}', [AdminController::class, 'update']);
$router->post($adminPath . '/produits/{id}/supprimer', [AdminController::class, 'delete']);

$router->get($adminPath . '/categories', [AdminController::class, 'categories']);
$router->post($adminPath . '/categories', [AdminController::class, 'storeCategory']);
$router->post($adminPath . '/categories/{id}/supprimer', [AdminController::class, 'deleteCategory']);

$router->get($adminPath . '/reglages', [AdminController::class, 'settings']);
$router->post($adminPath . '/reglages', [AdminController::class, 'saveSettings']);

// Anything else -> 404 page.
$router->notFound([ErrorController::class, 'notFound']);
