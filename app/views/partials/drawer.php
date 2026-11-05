<?php

declare(strict_types=1);

/**
 * Left side drawer (replaces the bottom navigation from the templates).
 * Opened by #drawerToggle, controlled by assets/js/app.js.
 */

$drawerLinks = [
    ['label' => 'Accueil',            'url' => '/',                    'icon' => 'home'],
    ['label' => 'Femme',              'url' => '/categorie/femme',     'icon' => null],
    ['label' => 'Enfants',            'url' => '/categorie/enfants',   'icon' => null],
    ['label' => 'Pyjamas & Ensembles', 'url' => '/categorie/pyjamas',  'icon' => null],
    ['label' => 'Robes',              'url' => '/categorie/robes',     'icon' => null],
    ['label' => 'Promotions',         'url' => '/promotions',          'icon' => null],
];
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
            <span class="brand-text">
                <span class="brand-wordmark">
                    <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
                </span>
                <span class="brand-tagline"></span>
            </span>
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
                href="<?= e(whatsapp_url('Bonjour Shoop Moda Style, je souhaite passer une commande.')) ?>"
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
