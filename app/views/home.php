<?php

declare(strict_types=1);

/**
 * Home page.
 *
 * Reading order on purpose: hero → reassurance → products → promotion.
 * The promotion block sits after the catalogue so the visitor sees the
 * collection first, then the offer that closes the sale.
 *
 * @var array $slides
 * @var array $whyUs
 * @var array $promo
 * @var array $featured
 * @var array $wishSlugs
 */
?>
<section class="hero" aria-labelledby="heroTitle">
    <h1 class="visually-hidden" id="heroTitle">Shoop Moda Style — mode turque femme et enfant</h1>

    <div class="hero-track" id="heroTrack">
        <?php foreach ($slides as $slide): ?>
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

<?php if (!empty($featured)): ?>
    <section class="section" aria-labelledby="featuredTitle">
        <div class="wrap">
            <div class="section-head section-head--hero">
                <div>
                    <p class="eyebrow">Sélection de la semaine</p>
                    <h2 class="section-title" id="featuredTitle">Nos pièces les plus demandées</h2>
                </div>
                <a class="section-link" href="<?= e(url('/produits')) ?>">Tout voir</a>
            </div>

            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

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
