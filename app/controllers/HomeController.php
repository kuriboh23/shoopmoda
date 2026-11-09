<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;

final class HomeController
{
    public function index(): void
    {
        $slides     = (array) config('hero_slides');
        $whyUs      = (array) config('why_us');
        $promo      = (array) config('promo_banner');
        $featured   = Product::featured(8);
        $categories = Category::all(true);
        $offerEnd   = Product::nextFlashEnd();

        View::render('home', [
            'title'       => store_name() . ' — Mode turque femme & enfant',
            'description' => store_name() . ' : vêtements et pyjamas turcs pour femme et enfant au Maroc.',
            'canonical'   => url('/'),
            'slides'      => $slides,
            'whyUs'       => $whyUs,
            'promo'       => $promo,
            'featured'    => $featured,
            'categories'  => $categories,
            'offerEnd'    => $offerEnd,
            'cartCount'   => Cart::count(),
            'wishCount'   => Wishlist::count(),
            'wishSlugs'   => Wishlist::all(),
        ]);
    }
}
