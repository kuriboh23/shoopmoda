<?php

declare(strict_types=1);

/**
 * ==========================================================================
 *  SHOP SETTINGS - THIS IS THE ONLY FILE YOU NEED TO EDIT
 * ==========================================================================
 *
 *  1. Open this file.
 *  2. Change the text between the quotes. Keep the quotes and the comma at
 *     the end of each line.
 *  3. Save and reload the site. That is all: no build step, no cache to
 *     clear, nothing to install.
 *
 *  A. THE SHOP      name, currency, WhatsApp number, languages
 *  B. THE ADMIN     secret URL and login of the admin area
 *  C. DATABASE      the four values your hosting panel gives you
 *  D. HOME PAGE     announcement bar, slider, trust badges, promo block
 *
 *  There is no other configuration file: no .env, no .htaccess, no install
 *  step. Upload the files, fill in section C below, open the site.
 */

return [

    // ======================================================================
    // A. THE SHOP
    // ======================================================================

    // Name used in page titles, the footer and WhatsApp messages.
    'app_name' => 'Shoop Moda Style',

    // "production" hides errors from visitors. Leave it as it is.
    'app_env' => 'production',

    // Show PHP errors on screen instead of a blank 500 page.
    // Set this to true ONLY while installing (it reveals code paths),
    // then back to false once the shop works.
    'app_debug' => false,

    // Pretty URLs: /produit/robe instead of /?p=/produit/robe.
    // They work thanks to the .htaccess file next to this one, which simply
    // forwards every page to index.php. Keep it true.
    // If your host shows a 404 on every page (it ignores .htaccess), or if
    // you delete .htaccess, links fall back to /?p=/panier on their own and
    // the shop keeps working. Set this to false to force that shorter form.
    'pretty_urls' => true,

    // Language of the interface and timezone used for dates.
    'locale'   => 'fr',
    'timezone' => 'Africa/Casablanca',

    // Price suffix shown everywhere ("DH 249").
    'currency' => 'DH',

    // WhatsApp number that receives the orders.
    // Country code first, digits only, no "+" and no spaces.
    'whatsapp_number' => '212725605520',

    // Instagram link in the footer (empty string hides it).
    'instagram_url' => 'https://instagram.com/shoop.moda_style',


    // ======================================================================
    // B. THE ADMIN AREA
    // ======================================================================

    // Secret folder of the admin area: the dashboard is at /gestion
    // Change it to something only you know, e.g. 'gestion-8f2'.
    'admin_path' => 'gestion',

    // Login of the admin area. Change the password before going live.
    'admin_email'    => 'admin@shoopmoda.ma',
    'admin_password' => 'shoopmoda2026',

    // Random text used to sign cookies. Any long random string works.
    'cookie_secret' => 'change-this-to-a-long-random-string',


    // ======================================================================
    // B2. RESKIN — OWNER-ONLY DESIGN & STORE INFO
    // ======================================================================
    // The editor is at /reskin, with its own password. It contains the design,
    // the page content and the private shop identity (logo, WhatsApp, delivery,
    // Instagram). The client admin only opens products and categories.
    // "Enregistrer" writes assets/css/reskin-theme.css and publishes it;
    // that file only changes when RESKIN is saved again.

    'reskin_path'     => 'reskin',
    'reskin_password' => 'thiccolight',


    // ======================================================================
    // C. DATABASE (MySQL)
    // ======================================================================
    //
    // Local XAMPP:        host 127.0.0.1 | user root  | password empty
    // InfinityFree:       copy the four values from the panel:
    //                     "MySQL Hostname"  -> db_host   (sql123.infinityfree.com)
    //                     "Database Name"   -> db_name   (if0_12345678_shoopmoda)
    //                     "Username"        -> db_user   (if0_12345678)
    //                     account password  -> db_pass
    // Keep the database name and the username exactly as the panel shows
    // them, prefix included (if0_ with a zero, not the letter o).

    'db_host'    => '127.0.0.1',
    'db_port'    => 3306,
    'db_name'    => 'shoopmoda',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',


    // ======================================================================
    // D. HOME PAGE TEXT
    // ======================================================================

    // Scrolling bar at the very top of every page.
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

    // Big slider on the home page. Each block is one slide:
    // eyebrow = small text above, title = big text, text = sentence below,
    // cta = button label, link = where it goes, theme = light or dark.
    'hero_slides' => [
        [
            'eyebrow' => 'Shoop Moda Style',
            'title'   => 'Nouvelle Collection',
            'text'    => 'Découvrez les nouveautés de la saison',
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

    // Four trust badges under the slider.
    'why_us' => [
        ['icon' => 'shield',   'title' => '100% Turc',       'text' => 'Tissus authentiques importés d’Istanbul.'],
        ['icon' => 'truck',    'title' => 'Livraison rapide', 'text' => 'Expédition express dans tout le Maroc.'],
        ['icon' => 'users',    'title' => 'Femme & Enfant',   'text' => 'Élégance et pyjamas douillets pour toutes.'],
        ['icon' => 'whatsapp', 'title' => 'Commande facile',  'text' => 'Commande instantanée via WhatsApp.'],
    ],

    // Promo banner near the bottom of the home page.
    // Keep the wording factual: only promise what the catalogue offers.
    'promo_banner' => [
        'badge' => 'Offre limitée',
        'title' => 'Nouveautés à prix de lancement',
        'text'  => 'Une sélection de pièces de la saison à prix réduit, dans la limite des stocks disponibles.',
        'cta'   => 'Voir les promotions',
        'link'  => '/promotions',
    ],
];
