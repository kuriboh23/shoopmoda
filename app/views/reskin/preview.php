<?php

declare(strict_types=1);

/**
 * RESKIN live preview.
 *
 * Deliberately uses the real storefront classes from app.css, so the
 * dashboard shows the actual design rather than an approximation. The parent
 * dashboard writes the --rs-* custom properties and the data-rs-* attribute
 * state directly onto this document.
 *
 * @var array<string, string> $values
 * @var string $bodyAttrs
 * @var string $appName
 */

$products = [
    ['Robe longue Sultan', 'Femme', '349', '199', 'robe-longue-sultan.svg', 'Promo flash'],
    ['Pyjama satin Istanbul', 'Pyjamas', '279', '219', 'ensemble-pyjama-satin-istanbul.svg', 'Coup de cœur'],
    ['Trench automne', 'Femme', '499', '', 'trench-automne.svg', ''],
    ['T-shirt coton douceur', 'Enfants', '129', '99', 't-shirt-coton-douceur.svg', ''],
];
?>
<div class="announcement-bar rs-preview-announcement">
    <span class="announcement-badge" aria-hidden="true"><?= icon('truck', 'icon') ?></span>
    <span class="announcement-item">Livraison 24-48h partout au Maroc</span>
    <span class="location-tag">
        <span class="location-pulse" aria-hidden="true"></span>
        Maroc
    </span>
</div>

<header class="site-header">
    <button type="button" class="icon-btn menu-toggle" aria-label="Menu">
        <?= icon('menu', 'icon') ?>
    </button>

    <span class="brand">
        <span class="brand-text">
            <span class="brand-wordmark">
                <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
            </span>
        </span>
    </span>

    <nav class="desktop-nav" aria-label="Navigation principale">
        <span class="nav-link is-active">Boutique</span>
        <span class="nav-link">Femme</span>
        <span class="nav-link">Enfants</span>
        <span class="nav-link">Pyjamas</span>
        <span class="nav-link">Promotions</span>
    </nav>

    <div class="header-actions">
        <span class="icon-btn" aria-hidden="true"><?= icon('heart', 'icon') ?></span>
        <span class="icon-btn" aria-hidden="true"><?= icon('cart', 'icon') ?></span>
    </div>
</header>

<main class="site-main">
    <section class="hero">
        <div class="hero-track">
            <article class="hero-slide hero-slide--light">
                <p class="hero-eyebrow">SAISON 2026</p>
                <h2 class="hero-title">Nouvelle collection</h2>
                <p class="hero-text">Découvrez les nouveautés de la saison.</p>
                <span class="btn btn--compact btn--dark">Découvrir</span>
            </article>
        </div>
    </section>

    <section class="wrap home-offer">
        <div class="offer-panel">
            <span class="offer-glow" aria-hidden="true"></span>
            <div class="offer-copy">
                <span class="offer-badge"><?= icon('tag', 'icon icon--sm') ?> Offre limitée</span>
                <h2 class="offer-title">Nouveautés à prix de lancement</h2>
                <p class="offer-text">Une sélection de pièces de la saison, dans la limite des stocks.</p>
                <span class="btn btn--dark btn--compact offer-cta">
                    Voir les promotions
                    <?= icon('arrow-right', 'icon icon--sm') ?>
                </span>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-head section-head--hero">
                <div>
                    <p class="eyebrow">Sélection de la semaine</p>
                    <h2 class="section-title">Nos pièces les plus demandées</h2>
                </div>
                <span class="section-link">Tout voir</span>
            </div>

            <div class="product-grid">
                <?php foreach ($products as [$title, $category, $price, $sale, $image, $badge]): ?>
                    <article class="product-card">
                        <span class="product-link">
                            <div class="product-media">
                                <img
                                    src="<?= e(asset('images/placeholders/' . $image)) ?>"
                                    alt=""
                                    width="400"
                                    height="533"
                                >
                                <?php if ($badge !== ''): ?>
                                    <span class="product-badge product-badge--flash"><?= e($badge) ?></span>
                                <?php endif; ?>
                                <?php if ($sale !== ''): ?>
                                    <span class="product-badge product-badge--discount">-<?= (int) round((1 - ((float) $sale / (float) $price)) * 100) ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <p class="product-category"><?= e($category) ?></p>
                                <h3 class="product-title"><?= e($title) ?></h3>
                                <p class="product-stock">En stock</p>
                                <div class="product-footer">
                                    <span class="price current">DH <?= e($sale !== '' ? $sale : $price) ?></span>
                                    <?php if ($sale !== ''): ?>
                                        <span class="price old">DH <?= e($price) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </span>

                        <span class="product-wish" aria-hidden="true">
                            <?= icon('heart', 'icon icon--sm') ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="why-grid">
                <article class="why-card">
                    <span class="why-icon"><?= icon('shield', 'icon') ?></span>
                    <h3 class="why-title">100% Turc</h3>
                    <p class="why-text">Tissus authentiques importés d’Istanbul.</p>
                </article>
                <article class="why-card">
                    <span class="why-icon"><?= icon('truck', 'icon') ?></span>
                    <h3 class="why-title">Livraison rapide</h3>
                    <p class="why-text">Expédition express dans tout le Maroc.</p>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="wrap">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="footer-logo">
                    <span class="brand-wordmark footer-wordmark">
                        <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
                    </span>
                    <span class="footer-logo-sub">Mode turque femme &amp; enfant</span>
                </span>
                <p class="footer-desc">Des pièces choisies avec soin, livrées partout au Maroc.</p>
            </div>

            <div class="footer-col">
                <p class="footer-title">Boutique</p>
                <div class="footer-links">
                    <span>Femme</span>
                    <span>Enfants</span>
                    <span>Pyjamas</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 <?= e($appName) ?></span>
        </div>
    </div>
</footer>
