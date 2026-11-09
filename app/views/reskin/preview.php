<?php

declare(strict_types=1);

/**
 * RESKIN live preview.
 *
 * This is the real home page structure, using the real app.css classes, the
 * real products and categories, and the editable SiteContent. The dashboard
 * writes unsaved CSS variables and content values straight into this
 * document, so the preview is always one step ahead of the saved site.
 *
 * @var array<string, mixed>            $content
 * @var array<int, array<string, mixed>> $previewProducts
 * @var array<int, array<string, mixed>> $previewCategories
 * @var array<string, string>           $values
 * @var string                          $bodyAttrs
 * @var string                          $appName
 * @var string|null                     $logo
 * @var string                          $instagram
 */

$announcement = (array) ($content['announcement'] ?? []);
$hero         = (array) ($content['hero'] ?? []);
$offer        = (array) ($content['offer'] ?? []);
$categories   = (array) ($content['categories'] ?? []);
$featured     = (array) ($content['featured'] ?? []);
$why          = (array) ($content['why'] ?? []);
$footer       = (array) ($content['footer'] ?? []);
$slides       = array_values((array) ($hero['slides'] ?? []));
$whyCards     = array_values((array) ($why['cards'] ?? []));

[$brandFirst, $brandSecond] = brand_parts();

$imageUrl = static function (array $product): string {
    $image = trim((string) ($product['primary_image'] ?? ''));

    if ($image === '') {
        return asset('images/placeholders/default.svg');
    }

    if (str_starts_with($image, 'images/')) {
        return url($image);
    }

    return product_image_url((int) ($product['id'] ?? 0), $image);
};
?>
<aside class="announcement-bar" data-rs-section="announcement">
    <span class="announcement-badge" aria-hidden="true"><?= icon('truck', 'icon') ?></span>

    <div class="announcement-marquee">
        <div class="announcement-track" data-rs-content-messages>
            <?php foreach ((array) ($announcement['messages'] ?? []) as $message): ?>
                <span class="announcement-item"><?= e((string) $message) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <span class="location-tag">
        <span class="location-pulse" aria-hidden="true"></span>
        <span data-rs-content="announcement.location"><?= e((string) ($announcement['location'] ?? '')) ?></span>
    </span>
</aside>

<header class="site-header" data-rs-section="header">
    <span class="icon-btn menu-toggle" aria-hidden="true"><?= icon('menu', 'icon') ?></span>

    <span class="brand">
        <?php if ($logo !== null): ?>
            <img class="brand-logo" src="<?= e($logo) ?>" alt="<?= e($appName) ?>" width="160" height="48">
        <?php else: ?>
            <span class="brand-text">
                <span class="brand-wordmark">
                    <span class="brand-first"><?= e($brandFirst) ?></span><?php if ($brandSecond !== ''): ?><span class="brand-second"><?= e($brandSecond) ?></span><?php endif; ?>
                </span>
            </span>
        <?php endif; ?>
    </span>

    <nav class="desktop-nav" aria-label="Navigation principale">
        <span class="nav-link is-active">Boutique</span>
        <?php foreach (array_slice($previewCategories, 0, 4) as $category): ?>
            <span class="nav-link"><?= e((string) ($category['name'] ?? '')) ?></span>
        <?php endforeach; ?>
        <span class="nav-link">Promotions</span>
    </nav>

    <div class="header-actions">
        <span class="icon-btn" aria-hidden="true"><?= icon('heart', 'icon') ?></span>
        <span class="icon-btn" aria-hidden="true"><?= icon('cart', 'icon') ?></span>
    </div>
</header>

<main class="site-main">
    <section class="hero" data-rs-section="hero">
        <div class="hero-track">
            <?php foreach ($slides as $index => $slide): ?>
                <article class="hero-slide hero-slide--<?= e((string) ($slide['theme'] ?? 'light')) ?>">
                    <p class="hero-eyebrow" data-rs-content="hero.slides.<?= $index ?>.eyebrow"><?= e((string) ($slide['eyebrow'] ?? '')) ?></p>
                    <h2 class="hero-title" data-rs-content="hero.slides.<?= $index ?>.title"><?= e((string) ($slide['title'] ?? '')) ?></h2>
                    <p class="hero-text" data-rs-content="hero.slides.<?= $index ?>.text"><?= e((string) ($slide['text'] ?? '')) ?></p>
                    <span class="btn btn--compact <?= ($slide['theme'] ?? 'light') === 'dark' ? 'btn--light' : 'btn--dark' ?>" data-rs-content="hero.slides.<?= $index ?>.cta">
                        <?= e((string) ($slide['cta'] ?? 'Découvrir')) ?>
                    </span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="wrap home-offer" data-rs-section="offer">
        <div class="offer-panel">
            <span class="offer-glow" aria-hidden="true"></span>

            <div class="offer-copy">
                <span class="offer-badge">
                    <?= icon('tag', 'icon icon--sm') ?>
                    <span data-rs-content="offer.badge"><?= e((string) ($offer['badge'] ?? '')) ?></span>
                </span>
                <h2 class="offer-title" data-rs-content="offer.title"><?= e((string) ($offer['title'] ?? '')) ?></h2>
                <p class="offer-text" data-rs-content="offer.text"><?= e((string) ($offer['text'] ?? '')) ?></p>

                <span class="btn btn--dark btn--compact offer-cta">
                    <span data-rs-content="offer.cta"><?= e((string) ($offer['cta'] ?? '')) ?></span>
                    <?= icon('arrow-right', 'icon icon--sm') ?>
                </span>
            </div>

            <div class="offer-timer">
                <div class="countdown-card">
                    <span class="countdown-label" data-rs-content="offer.countdown_label"><?= e((string) ($offer['countdown_label'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="wrap home-explore" data-rs-section="categories">
        <div class="home-explore-head">
            <h2 class="home-explore-title" data-rs-content="categories.title"><?= e((string) ($categories['title'] ?? '')) ?></h2>
            <span class="section-link" data-rs-content="categories.link_label"><?= e((string) ($categories['link_label'] ?? '')) ?></span>
        </div>

        <div class="chip-row home-chips">
            <?php foreach ($previewCategories as $category): ?>
                <span class="chip home-chip">
                    <?= icon((string) (($category['icon'] ?? '') !== '' ? $category['icon'] : 'shirt'), 'icon icon--sm') ?>
                    <?= e((string) ($category['name'] ?? '')) ?>
                    <span class="chip-count"><?= (int) ($category['product_count'] ?? 0) ?></span>
                </span>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section" data-rs-section="products">
        <div class="wrap">
            <div class="section-head section-head--hero">
                <div>
                    <p class="eyebrow" data-rs-content="featured.eyebrow"><?= e((string) ($featured['eyebrow'] ?? '')) ?></p>
                    <h2 class="section-title" data-rs-content="featured.title"><?= e((string) ($featured['title'] ?? '')) ?></h2>
                </div>
                <span class="section-link" data-rs-content="featured.link_label"><?= e((string) ($featured['link_label'] ?? '')) ?></span>
            </div>

            <div class="product-grid">
                <?php foreach ($previewProducts as $product): ?>
                    <?php
                    $price = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
                    $old   = (float) ($product['price'] ?? 0);
                    $sale  = !empty($product['sale_price']) && $old > $price;
                    $stock = (int) ($product['stock'] ?? 0);
                    ?>
                    <article class="product-card">
                        <span class="product-link">
                            <div class="product-media">
                                <img
                                    src="<?= e($imageUrl($product)) ?>"
                                    alt=""
                                    width="400"
                                    height="533"
                                    loading="lazy"
                                >
                                <?php if (!empty($product['is_flash'])): ?>
                                    <span class="product-badge product-badge--flash">Promo flash</span>
                                <?php endif; ?>
                                <?php if ($sale): ?>
                                    <span class="product-badge product-badge--discount">-<?= (int) round((1 - ($price / max($old, 0.01))) * 100) ?>%</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <p class="product-category"><?= e((string) ($product['category_name'] ?? '')) ?></p>
                                <h3 class="product-title"><?= e((string) ($product['title'] ?? 'Produit')) ?></h3>
                                <p class="product-stock"><?= $stock <= 0 ? 'Rupture de stock' : 'En stock' ?></p>
                                <div class="product-footer">
                                    <span class="price current"><?= e(format_price($price)) ?></span>
                                    <?php if ($sale): ?>
                                        <span class="price old"><?= e(format_price($old)) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </span>

                        <span class="product-wish" aria-hidden="true"><?= icon('heart', 'icon icon--sm') ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section why-section" data-rs-section="why">
        <div class="wrap">
            <div class="why-head">
                <div class="why-head-main">
                    <p class="eyebrow" data-rs-content="why.eyebrow"><?= e((string) ($why['eyebrow'] ?? '')) ?></p>
                    <h2 class="section-title" data-rs-content="why.title"><?= e((string) ($why['title'] ?? '')) ?></h2>
                </div>
                <p class="why-lead" data-rs-content="why.lead"><?= e((string) ($why['lead'] ?? '')) ?></p>
            </div>

            <div class="why-grid">
                <?php foreach ($whyCards as $index => $card): ?>
                    <article class="why-card">
                        <span class="why-icon"><?= icon((string) ($card['icon'] ?? 'shield'), 'icon') ?></span>
                        <span class="why-index" aria-hidden="true"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <h3 class="why-title" data-rs-content="why.cards.<?= $index ?>.title"><?= e((string) ($card['title'] ?? '')) ?></h3>
                        <p class="why-text" data-rs-content="why.cards.<?= $index ?>.text"><?= e((string) ($card['text'] ?? '')) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer" data-rs-section="footer">
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <span class="footer-logo">
                <?php if ($logo !== null): ?>
                    <img class="footer-logo-img" src="<?= e($logo) ?>" alt="<?= e($appName) ?>" width="160" height="48">
                <?php else: ?>
                    <span class="brand-wordmark footer-wordmark">
                        <span class="brand-first"><?= e($brandFirst) ?></span><?php if ($brandSecond !== ''): ?><span class="brand-second"><?= e($brandSecond) ?></span><?php endif; ?>
                    </span>
                <?php endif; ?>
                <span class="footer-logo-sub" data-rs-content="footer.logo_sub"><?= e((string) ($footer['logo_sub'] ?? '')) ?></span>
            </span>

            <p class="footer-desc" data-rs-content="footer.description"><?= e((string) ($footer['description'] ?? '')) ?></p>

            <?php if ($instagram !== ''): ?>
                <span class="footer-social"><?= icon('instagram', 'icon') ?></span>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <p class="footer-title" data-rs-content="footer.categories_title"><?= e((string) ($footer['categories_title'] ?? '')) ?></p>
            <div class="footer-links">
                <?php foreach (array_slice($previewCategories, 0, 5) as $category): ?>
                    <span><?= e((string) ($category['name'] ?? '')) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="footer-col">
            <p class="footer-title" data-rs-content="footer.help_title"><?= e((string) ($footer['help_title'] ?? '')) ?></p>
            <div class="footer-links">
                <span>Mon panier</span>
                <span>Mes favoris</span>
                <span>Nous contacter</span>
            </div>
        </div>

        <div class="footer-col">
            <p class="footer-title" data-rs-content="footer.order_title"><?= e((string) ($footer['order_title'] ?? '')) ?></p>
            <div class="footer-wa-card">
                <span class="footer-wa-label" data-rs-content="footer.whatsapp_label"><?= e((string) ($footer['whatsapp_label'] ?? '')) ?></span>
                <span class="footer-wa-btn">
                    <?= icon('whatsapp', 'icon icon--sm') ?>
                    <?= e(whatsapp_display()) ?>
                </span>
                <div class="footer-pills">
                    <span class="pill" data-rs-content="footer.pill_1"><?= e((string) ($footer['pill_1'] ?? '')) ?></span>
                    <span class="pill" data-rs-content="footer.pill_2"><?= e((string) ($footer['pill_2'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="wrap footer-bottom">
        <p data-rs-content="footer.bottom_note"><?= e((string) ($footer['bottom_note'] ?? '')) ?></p>
    </div>
</footer>
