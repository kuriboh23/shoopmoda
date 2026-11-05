<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Product;

final class HomeController
{
    public function index(): void
    {
        $slides     = (array) config('hero_slides');
        $whyUs      = (array) config('why_us');
        $promo      = (array) config('promo_banner');
        $featured   = Product::featured(8);

        View::render('home', [
            'title'    => config('app_name') . ' — Mode turque femme & enfant',
            'description' => 'Shoop Moda Style : vêtements et pyjamas turcs pour femme et enfant au Maroc.',
            'canonical'   => url('/'),
            'slides'   => $slides,
            'whyUs'    => $whyUs,
            'promo'    => $promo,
            'featured' => $featured,
            'cartCount' => 0,
            'wishCount' => 0,
        ]);
    }
}
