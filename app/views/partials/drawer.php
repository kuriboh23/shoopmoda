<?php

declare(strict_types=1);

/**
 * Left side drawer (replaces the bottom navigation from the templates).
 * Opened by #drawerToggle, controlled by assets/js/app.js.
 */

$drawerLinks = [
    ['label' => 'Accueil',           'url' => '/',           'icon' => 'home'],
    ['label' => 'Toutes les pièces', 'url' => '/produits',   'icon' => 'shirt'],
];

foreach (nav_categories(0) as $category) {
    $drawerLinks[] = [
        'label' => (string) ($category['name'] ?? ''),
        'url'   => '/categorie/' . (string) ($category['slug'] ?? ''),
        'icon'  => (string) (($category['icon'] ?? '') !== '' ? $category['icon'] : 'tag'),
    ];
}

$drawerLinks[] = ['label' => 'Promotions', 'url' => '/promotions', 'icon' => 'tag'];

$logo = site_logo_url();
[$brandFirst, $brandSecond] = brand_parts();
?>
<aside
    class="drawer"
    id="drawer"
    role="dialog"
    aria-modal="true"
    aria-labelledby="drawerTitle"
    aria-hidden="true"
    tabindex="-1"
>
    <div class="drawer-head">
        <span class="brand">
            <?php if ($logo !== null): ?>
                <img class="brand-logo" src="<?= e($logo) ?>" alt="<?= e(store_name()) ?>" width="150" height="44">
            <?php else: ?>
                <span class="brand-text">
                    <span class="brand-wordmark">
                        <span class="brand-first"><?= e($brandFirst) ?></span><?php if ($brandSecond !== ''): ?><span class="brand-second"><?= e($brandSecond) ?></span><?php endif; ?>
                    </span>
                    <span class="brand-tagline"></span>
                </span>
            <?php endif; ?>
        </span>

        <button type="button" class="icon-btn" id="drawerClose" aria-label="Fermer le menu">
            <?= icon('close', 'icon') ?>
        </button>
    </div>

    <div class="drawer-body">
        <h2 class="drawer-title" id="drawerTitle">Menu</h2>

        <nav aria-label="Catégories">
            <ul class="drawer-nav">
                <?php foreach ($drawerLinks as $link): ?>
                    <?php $active = (current_pathname() === $link['url']); ?>
                    <li>
                        <a
                            class="drawer-link<?= $active ? ' is-active' : '' ?>"
                            href="<?= e(url($link['url'])) ?>"
                            <?= $active ? 'aria-current="page"' : '' ?>
                        >
                            <span class="drawer-link-text">
                                <?php if ($link['icon'] !== null): ?>
                                    <?= icon($link['icon'], 'icon icon--sm') ?>
                                <?php endif; ?>
                                <?= e($link['label']) ?>
                            </span>
                            <?= icon('chevron-right', 'icon icon--sm drawer-chevron') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <hr class="drawer-divider">

        <nav aria-label="Ma sélection">
            <ul class="drawer-nav">
                <li>
                    <a class="drawer-link" href="<?= e(url('/favoris')) ?>">
                        <span class="drawer-link-text">
                            <?= icon('heart', 'icon icon--sm') ?>
                            Mes favoris
                        </span>
                        <?= icon('chevron-right', 'icon icon--sm drawer-chevron') ?>
                    </a>
                </li>
                <li>
                    <a class="drawer-link" href="<?= e(url('/panier')) ?>">
                        <span class="drawer-link-text">
                            <?= icon('cart', 'icon icon--sm') ?>
                            Mon panier
                        </span>
                        <?= icon('chevron-right', 'icon icon--sm drawer-chevron') ?>
                    </a>
                </li>
                <li>
                    <a class="drawer-link" href="<?= e(url('/recherche')) ?>">
                        <span class="drawer-link-text">
                            <?= icon('search', 'icon icon--sm') ?>
                            Rechercher
                        </span>
                        <?= icon('chevron-right', 'icon icon--sm drawer-chevron') ?>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="drawer-support">
            <p class="drawer-support-title">Une question ?</p>
            <a
                class="btn btn--wa btn--block"
                href="<?= e(whatsapp_url('Bonjour ' . store_name() . ', je souhaite passer une commande.')) ?>"
                target="_blank"
                rel="noopener"
            >
                <?= icon('whatsapp', 'icon icon--sm') ?>
                Commander sur WhatsApp
            </a>
        </div>
    </div>

    <p class="drawer-foot"><?= icon('truck', 'icon icon--sm') ?> Livraison partout au Maroc</p>
</aside>

<div class="drawer-overlay" id="drawerOverlay" hidden></div>
