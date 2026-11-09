<?php

declare(strict_types=1);

/**
 * Home page.
 *
 * Reading order: hero → offer with live countdown → collections →
 * best sellers → engagements. The offer sits directly under the hero so
 * the first scroll lands on something urgent, and the trust panel closes
 * the page.
 *
 * @var array $slides
 * @var array $whyUs
 * @var array $promo
 * @var array $featured
 * @var array $categories
 * @var string|null $offerEnd
 * @var array $wishSlugs
 */

$content = \App\Models\SiteContent::all();
$slides  = array_values((array) ($content['hero']['slides'] ?? []));
$promo   = (array) ($content['offer'] ?? []);
$why     = (array) ($content['why'] ?? []);
$whyUs   = array_values((array) ($why['cards'] ?? []));
$explore = (array) ($content['categories'] ?? []);
$featuredMeta = (array) ($content['featured'] ?? []);

$categoryIcon = static function (array $category): string {
    $icon = trim((string) ($category['icon'] ?? ''));

    if ($icon !== '') {
        return $icon;
    }

    return match (true) {
        str_contains((string) ($category['slug'] ?? ''), 'enfant') => 'users',
        str_contains((string) ($category['slug'] ?? ''), 'pyjama') => 'moon',
        str_contains((string) ($category['slug'] ?? ''), 'femme')  => 'dress',
        default                                                   => 'shirt',
    };
};
?>
<section class="hero" aria-labelledby="heroTitle">
    <h1 class="visually-hidden" id="heroTitle"><?= e(store_name()) ?> — mode turque femme et enfant</h1>

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

<section class="wrap home-offer" aria-labelledby="offerTitle">
    <div class="offer-panel">
        <span class="offer-glow" aria-hidden="true"></span>

        <div class="offer-copy">
            <span class="offer-badge"><?= icon('tag', 'icon icon--sm') ?> <?= e($promo['badge'] ?? 'Offre limitée') ?></span>
            <h2 class="offer-title" id="offerTitle"><?= e($promo['title'] ?? '') ?></h2>
            <p class="offer-text"><?= e($promo['text'] ?? '') ?></p>

            <a class="btn btn--dark btn--compact offer-cta" href="<?= e(url($promo['link'] ?? '/produits')) ?>">
                <?= e($promo['cta'] ?? 'Commander') ?>
                <?= icon('arrow-right', 'icon icon--sm') ?>
            </a>
        </div>

        <?php if ($offerEnd !== null): ?>
            <div class="offer-timer">
                <?php partial('partials/countdown', ['endAt' => $offerEnd, 'label' => (string) ($promo['countdown_label'] ?? 'Fin de l’offre dans')]); ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($categories)): ?>
    <section class="wrap home-explore" aria-labelledby="exploreTitle">
        <div class="home-explore-head">
            <h2 class="home-explore-title" id="exploreTitle"><?= e((string) ($explore['title'] ?? '')) ?></h2>
            <a class="section-link" href="<?= e(url('/produits')) ?>"><?= e((string) ($explore['link_label'] ?? 'Tout voir')) ?></a>
        </div>

        <div class="chip-row home-chips">
            <?php foreach ($categories as $category): ?>
                <a class="chip home-chip" href="<?= e(url('/categorie/' . $category['slug'])) ?>">
                    <?= icon($categoryIcon($category), 'icon icon--sm') ?>
                    <?= e($category['name']) ?>
                    <span class="chip-count"><?= (int) ($category['product_count'] ?? 0) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($featured)): ?>
    <section class="section" aria-labelledby="featuredTitle">
        <div class="wrap">
            <div class="section-head section-head--hero">
                <div>
                    <p class="eyebrow"><?= e((string) ($featuredMeta['eyebrow'] ?? '')) ?></p>
                    <h2 class="section-title" id="featuredTitle"><?= e((string) ($featuredMeta['title'] ?? '')) ?></h2>
                </div>
                <a class="section-link" href="<?= e(url('/produits')) ?>"><?= e((string) ($featuredMeta['link_label'] ?? 'Tout voir')) ?></a>
            </div>

            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section why-section" aria-labelledby="whyTitle">
    <div class="wrap">
        <div class="why-head">
            <div class="why-head-main">
                <p class="eyebrow"><?= e((string) ($why['eyebrow'] ?? '')) ?></p>
                <h2 class="section-title" id="whyTitle"><?= e((string) ($why['title'] ?? '')) ?></h2>
            </div>
            <p class="why-lead">
                <?= e((string) ($why['lead'] ?? '')) ?>
            </p>
        </div>

        <div class="why-grid">
            <?php foreach ($whyUs as $index => $item): ?>
                <article class="why-card">
                    <span class="why-icon"><?= icon((string) ($item['icon'] ?? '')) ?></span>
                    <span class="why-index" aria-hidden="true"><?= str_pad((string) ((int) $index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <h3 class="why-title"><?= e($item['title']) ?></h3>
                    <p class="why-text"><?= e($item['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
