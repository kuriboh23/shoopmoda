<?php

declare(strict_types=1);

/**
 * Shared product card.
 *
 * @var array<string, mixed> $product
 * @var array<int, string>   $wishSlugs
 */

$cardId     = (int) ($product['id'] ?? 0);
$cardSlug   = (string) ($product['slug'] ?? '');
$cardTitle  = (string) ($product['title'] ?? 'Produit');
$cardUrl    = url('/produit/' . $cardSlug);
$cardPrice  = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
$cardOld    = (float) ($product['price'] ?? 0);
$cardIsSale = !empty($product['sale_price']) && $cardOld > $cardPrice;
$cardImage  = product_image_url($cardId, $product['primary_image'] ?? null);
$cardRating = (float) ($product['rating'] ?? 0);
$cardStock  = (int) ($product['stock'] ?? 0);
$cardCat    = (string) ($product['category_name'] ?? '');

$cardDescription = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($product['description'] ?? ''))) ?? '');
$cardDescription = $cardDescription !== '' ? mb_substr($cardDescription, 0, 84) : '';
if ($cardDescription !== '' && mb_strlen($cardDescription) >= 84) {
    $cardDescription .= '…';
}

$cardDiscount = $cardIsSale ? (int) round((1 - ($cardPrice / max($cardOld, 0.01))) * 100) : 0;
$cardWished   = in_array($cardSlug, $wishSlugs ?? [], true);
?>
<article class="product-card">
    <a class="product-link" href="<?= e($cardUrl) ?>">
        <div class="product-media">
            <img
                src="<?= e($cardImage) ?>"
                alt="<?= e($cardTitle) ?>"
                loading="lazy"
                width="400"
                height="500"
            >

            <?php if (!empty($product['is_flash']) || !empty($product['is_featured']) || $cardDiscount > 0): ?>
                <div class="product-badges">
                    <?php if (!empty($product['is_flash'])): ?>
                        <span class="product-badge product-badge--flash">Promo flash</span>
                    <?php elseif (!empty($product['is_featured'])): ?>
                        <span class="product-badge product-badge--featured">Coup de cœur</span>
                    <?php endif; ?>

                    <?php if ($cardDiscount > 0): ?>
                        <span class="product-badge product-badge--discount">-<?= $cardDiscount ?>%</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <?php if ($cardCat !== ''): ?>
                <p class="product-category"><?= e($cardCat) ?></p>
            <?php endif; ?>

            <h3 class="product-title"><?= e($cardTitle) ?></h3>

            <?php if ($cardDescription !== ''): ?>
                <p class="product-desc"><?= e($cardDescription) ?></p>
            <?php endif; ?>

            <div class="product-meta">
                <?php if ($cardRating > 0): ?>
                    <span class="product-rating" aria-label="Note : <?= e($cardRating) ?> sur 5">
                        <?= icon('star', 'icon icon--sm') ?>
                        <?= e(number_format($cardRating, 1, ',', '')) ?>/5
                    </span>
                <?php endif; ?>

                <?php if ($cardStock <= 0): ?>
                    <span class="product-stock product-stock--out">Rupture</span>
                <?php elseif ($cardStock <= 5): ?>
                    <span class="product-stock product-stock--low">Plus que <?= $cardStock ?></span>
                <?php else: ?>
                    <span class="product-stock">En stock</span>
                <?php endif; ?>
            </div>

            <div class="product-footer">
                <div class="product-price">
                    <span class="price current"><?= e(format_price($cardPrice)) ?></span>
                    <?php if ($cardIsSale): ?>
                        <span class="price old"><?= e(format_price($cardOld)) ?></span>
                    <?php endif; ?>
                </div>

                <span class="product-cta" aria-hidden="true">
                    Voir
                    <?= icon('arrow-right', 'icon icon--sm') ?>
                </span>
            </div>
        </div>
    </a>

    <button
        type="button"
        class="product-wish<?= $cardWished ? ' is-active' : '' ?>"
        data-wish-slug="<?= e($cardSlug) ?>"
        data-wishlist-url="<?= e(url('/favoris/ajouter')) ?>"
        aria-pressed="<?= $cardWished ? 'true' : 'false' ?>"
        aria-label="<?= $cardWished ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
        title="<?= $cardWished ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
    >
        <?= icon('heart', 'icon icon--sm') ?>
    </button>
</article>
