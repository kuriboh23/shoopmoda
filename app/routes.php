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
 * Route table.
 *
 * Placeholders use the `{name}` syntax understood by App\Core\Router:
 * a regex written by hand is quoted literally and would never match.
 *
 * @var Router $router
 */

$router->get('/', [HomeController::class, 'index']);

$router->get('/produits', [ProductController::class, 'catalog']);
$router->get('/categorie/{slug}', [ProductController::class, 'category']);
$router->get('/produit/{slug}', [ProductController::class, 'product']);
$router->get('/promotions', [ProductController::class, 'promotions']);

$router->get('/recherche', [SearchController::class, 'index']);
$router->get('/recherche/suggestions', [SearchController::class, 'suggestions']);

$router->get('/favoris', [WishlistController::class, 'index']);
$router->post('/favoris/ajouter', [WishlistController::class, 'toggle']);

$router->get('/panier', [CartController::class, 'index']);
$router->post('/panier/ajouter', [CartController::class, 'add']);
$router->post('/panier/retirer', [CartController::class, 'remove']);
$router->post('/panier/quantite', [CartController::class, 'update']);
$router->post('/panier/vider', [CartController::class, 'empty']);

$router->get('/commande', [CartController::class, 'checkout']);
$router->post('/commande', [CartController::class, 'placeOrder']);

// Admin area. The prefix comes from ADMIN_PATH ("gestion" by default).
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

$router->notFound([ErrorController::class, 'notFound']);
