<?php

declare(strict_types=1);

/**
 * Home page — catalogue live.
 *
 * @var array  $slides
 * @var array  $whyUs
 * @var array  $promo
 * @var array  $featured
 * @var bool   $debug
 */

$debug = $debug ?? false;
?>
<section class="hero" aria-labelledby="heroTitle">
    <h1 class="visually-hidden" id="heroTitle">Accueil</h1>

    <div class="hero-track" id="heroTrack">
        <?php foreach ($slides as $index => $slide): ?>
            <article class="hero-slide hero-slide--<?= e($slide['theme'] ?? 'light') ?>">
                <p class="hero-eyebrow"><?= e($slide['eyebrow'] ?? '') ?></p>
                <h2 class="hero-title"><?= e($slide['title'] ?? '') ?></h2>
                <p class="hero-text"><?= e($slide['text'] ?? '') ?></p>
                <a class="btn btn--compact <?= ($slide['theme'] ?? 'light') === 'dark' ? 'btn--light' : 'btn--dark' ?>" href="<?= e(url($slide['link'] ?? '/')) ?>">
                    <?= e($slide['cta'] ?? 'Découvrir') ?>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="hero-dots" role="group" aria-label="Choisir une promotion">
        <?php foreach ($slides as $index => $slide): ?>
            <button
                type="button"
                class="hero-dot<?= $index === 0 ? ' is-active' : '' ?>"
                data-slide="<?= (int) $index ?>"
                aria-label="Promotion <?= (int) ($index + 1) ?> : <?= e($slide['title'] ?? '') ?>"
                <?= $index === 0 ? 'aria-current="true"' : '' ?>
            ></button>
        <?php endforeach; ?>
    </div>
</section>

<section class="section" aria-labelledby="whyTitle">
    <div class="wrap">
        <div class="section-head">
            <h2 class="section-title" id="whyTitle">Pourquoi nous choisir</h2>
        </div>

        <div class="why-grid">
            <?php foreach ($whyUs as $item): ?>
                <article class="why-card">
                    <span class="why-icon"><?= icon((string) ($item['icon'] ?? '')) ?></span>
                    <h3 class="why-title"><?= e($item['title']) ?></h3>
                    <p class="why-text"><?= e($item['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="wrap" aria-labelledby="promoTitle">
    <div class="promo-banner">
        <div class="promo-content">
            <span class="promo-badge"><?= e($promo['badge'] ?? '') ?></span>
            <h2 class="promo-title" id="promoTitle"><?= e($promo['title'] ?? '') ?></h2>
            <p class="promo-text"><?= e($promo['text'] ?? '') ?></p>
            <a class="btn btn--light btn--compact" href="<?= e(url($promo['link'] ?? '/')) ?>">
                <?= e($promo['cta'] ?? 'Commander') ?>
            </a>
        </div>
        <span class="promo-mark" aria-hidden="true">✦</span>
    </div>
</section>

<?php if (!empty($featured)): ?>
    <section class="section" aria-labelledby="featuredTitle">
        <div class="wrap">
            <div class="section-head">
                <h2 class="section-title" id="featuredTitle">En vedette</h2>
                <a class="section-link" href="<?= e(url('/categorie/femme')) ?>">Voir tout →</a>
            </div>

            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <article class="product-card">
                        <a class="product-link" href="<?= e(url('/produit/' . $product['slug'])) ?>">
                            <div class="product-media">
                                <?php $img = $product['primary_image'] ?? null; ?>
                                <img
                                    src="<?= e(product_image_url((int) $product['id'], $img)) ?>"
                                    alt="<?= e($product['title']) ?>"
                                    loading="lazy"
                                    width="400"
                                    height="500"
                                >
                                <?php if (!empty($product['is_flash'])): ?>
                                    <span class="product-badge product-badge--flash">Flash</span>
                                <?php endif; ?>
                                <?php if (!empty($product['is_featured'])): ?>
                                    <span class="product-badge product-badge--featured">Populaire</span>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <h2 class="product-title"><?= e($product['title']) ?></h2>

                                <?php
                                $price  = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
                                $oldPrice = (float) ($product['price'] ?? 0);
                                ?>
                                <div class="product-price">
                                    <?php if (!empty($product['sale_price'])): ?>
                                        <span class="price current"><?= e(format_price($price)) ?></span>
                                        <span class="price old"><?= e(format_price($oldPrice)) ?></span>
                                    <?php else: ?>
                                        <span class="price current"><?= e(format_price($price)) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($product['rating'])): ?>
                                    <div class="product-rating" aria-label="Note : <?= e($product['rating']) ?> / 5">
                                        <?= icon('star', 'icon icon--sm') ?>
                                        <span><?= e($product['rating']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
