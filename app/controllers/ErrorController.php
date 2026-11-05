<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Wishlist;

final class ErrorController
{
    public function notFound(): void
    {
        http_response_code(404);

        View::render('errors/404', [
            'title'       => 'Page introuvable',
            'description' => 'La page demandée n’existe pas ou a été déplacée.',
            'canonical'   => url(current_path()),
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'noindex'     => true,
        ], 'layouts/main');
    }
}
