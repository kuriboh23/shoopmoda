<?php

declare(strict_types=1);

/**
 * Front controller — point d'entrée unique du site.
 *
 * Placé à la racine du projet (et non dans /public) pour un déploiement
 * direct dans htdocs / public_html. Le code applicatif est protégé par
 * le .htaccess de ce même dossier.
 */

/** @var \App\Core\Router $router */
$router = require __DIR__ . '/app/bootstrap.php';

$router->dispatch(
    (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
    (string) ($_SERVER['REQUEST_URI'] ?? '/')
);
