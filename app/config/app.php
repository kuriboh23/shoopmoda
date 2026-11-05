<?php

declare(strict_types=1);

use App\Core\Env;

/**
 * Application configuration.
 *
 * Content that the owner may want to tweak without a database table
 * (hero slides, trust badges, promo banner) lives here on purpose:
 * zero queries, zero admin surface.
 */
return [
    'app_name'  => Env::get('APP_NAME', 'Shoop Moda Style'),
    'app_env'   => Env::get('APP_ENV', 'production'),
    'app_debug' => Env::bool('APP_DEBUG', false),
    'app_url'   => rtrim((string) Env::get('APP_URL', 'http://localhost'), '/'),
    'locale'    => 'fr',

    'currency'        => Env::get('CURRENCY', 'DH'),
    'whatsapp_number' => Env::get('WHATSAPP_NUMBER', '212725605520'),
    'instagram_url'   => 'https://instagram.com/shoop.moda_style',

    'admin_path'    => trim((string) Env::get('ADMIN_PATH', 'gestion'), '/'),
    'admin_email'    => (string) Env::get('ADMIN_EMAIL', 'admin@shoopmoda.ma'),
    'admin_password' => (string) Env::get('ADMIN_PASSWORD', 'shoopmoda2026'),
    'cookie_secret' => (string) Env::get('COOKIE_SECRET', ''),

    // Top announcement bar.
    'announcement' => [
        'highlight' => 'Mode turque premium',
        'text'      => 'Livraison rapide partout au Maroc',
        'location'  => 'Maroc',
        'messages'  => [
            'Mode turque premium importée d’Istanbul',
            'Livraison 24-48h partout au Maroc',
            'Commande simple et directe sur WhatsApp',
            'Nouveautés chaque semaine',
            'Produits authentiques 100% turcs',
        ],
    ],

    // Hero slider (CSS scroll-snap, no JS library).
    'hero_slides' => [
        [
            'eyebrow' => 'Shoop Moda Style',
            'title'   => 'Nouvelle Collection',
            'text'    => 'Jusqu’à -50% sur votre première commande',
            'cta'     => 'Découvrir',
            'link'    => '/categorie/femme',
            'theme'   => 'light',
        ],
        [
            'eyebrow' => 'Confort premium',
            'title'   => 'Pyjamas & Ensembles',
            'text'    => 'Des tissus turcs doux pour vos soirées',
            'cta'     => 'Voir la collection',
            'link'    => '/categorie/pyjamas',
            'theme'   => 'dark',
        ],
        [
            'eyebrow' => 'Pour les petits',
            'title'   => 'Mode Enfants',
            'text'    => 'Des tenues tendres et élégantes',
            'cta'     => 'Explorer',
            'link'    => '/categorie/enfants',
            'theme'   => 'light',
        ],
    ],

    // Static trust badges (approved home block).
    'why_us' => [
        ['icon' => 'shield',   'title' => '100% Turc',      'text' => 'Tissus authentiques importés d’Istanbul.'],
        ['icon' => 'truck',    'title' => 'Livraison rapide', 'text' => 'Expédition express dans tout le Maroc.'],
        ['icon' => 'users',    'title' => 'Femme & Enfant',  'text' => 'Élégance et pyjamas douillets pour toutes.'],
        ['icon' => 'whatsapp', 'title' => 'Commande facile', 'text' => 'Commande instantanée via WhatsApp.'],
    ],

    // Static promo banner (approved home block).
    'promo_banner' => [
        'badge' => 'Offre limitée',
        'title' => 'Votre première commande à -50%',
        'text'  => 'Offre valable sur la nouvelle collection, jusqu’à épuisement des stocks.',
        'cta'   => 'Commander',
        'link'  => '/panier',
    ],
];
