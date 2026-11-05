<?php

declare(strict_types=1);

use App\Controllers\CartController;
use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Core\Router;

/**
 * Route table.
 *
 * French URLs, no file extensions. New pages are added here phase by phase.
 *
 * @var Router $router
 */

$router->get('/', [HomeController::class, 'index']);

$router->get('/categorie/([a-zA-Z0-9-]+)', [ProductController::class, 'category']);
$router->get('/produit/([a-zA-Z0-9-]+)', [ProductController::class, 'product']);

$router->get('/panier', [CartController::class, 'index']);
$router->post('/panier/ajouter', [CartController::class, 'add']);
$router->post('/panier/retirer', [CartController::class, 'remove']);
$router->post('/panier/vider', [CartController::class, 'empty']);

$router->notFound([ErrorController::class, 'notFound']);
