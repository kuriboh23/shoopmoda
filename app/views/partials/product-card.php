<?php

declare(strict_types=1);

/**
 * Shared product card.
 *
 * One visual system everywhere: 3:4 media, small badges on opposite top
 * corners, two-line title, one-line price row with the old price in red.
 * No description and no rating: the card stays scannable and every card
 * in a grid keeps the same height.
 *
 * @var array<string, mixed> $product
 * @var array<int, string>   $wishSlugs
 */

$cardId       = (int) ($product['id'] ?? 0);
$cardSlug     = (string) ($product['slug'] ?? '');
$cardTitle    = (string) ($product['title'] ?? 'Produit');
$cardUrl      = url('/produit/' . $cardSlug);
$cardPrice    = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
$cardOld      = (float) ($product['price'] ?? 0);
$cardIsSale   = !empty($product['sale_price']) && $cardOld > $cardPrice;
$cardImage    = product_image_url($cardId, $product['primary_image'] ?? null);
$cardStock    = (int) ($product['stock'] ?? 0);
$cardCat      = (string) ($product['category_name'] ?? '');
$cardDiscount = $cardIsSale ? (int) round((1 - ($cardPrice / max($cardOld, 0.01))) * 100) : 0;
$cardWished   = in_array($cardSlug, $wishSlugs ?? [], true);
$cardIsFlash  = !empty($product['is_flash']);
$cardLabel    = $cardIsFlash ? 'Promo flash' : (!empty($product['is_featured']) ? 'Coup de cœur' : '');
?>
<article class="product-card">
    <a class="product-link" href="<?= e($cardUrl) ?>">
        <div class="product-media">
            <img
                src="<?= e($cardImage) ?>"
                alt="<?= e($cardTitle) ?>"
                loading="lazy"
                width="400"
                height="533"
            >

            <?php if ($cardLabel !== ''): ?>
                <span class="product-badge <?= $cardIsFlash ? 'product-badge--flash' : 'product-badge--featured' ?>">
                    <?= e($cardLabel) ?>
                </span>
            <?php endif; ?>

            <?php if ($cardDiscount > 0): ?>
                <span class="product-badge product-badge--discount">-<?= $cardDiscount ?>%</span>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <?php if ($cardCat !== ''): ?>
                <p class="product-category"><?= e($cardCat) ?></p>
            <?php endif; ?>

            <h3 class="product-title"><?= e($cardTitle) ?></h3>

            <p class="product-stock<?= $cardStock <= 0 ? ' product-stock--out' : ($cardStock <= 5 ? ' product-stock--low' : '') ?>">
                <?php if ($cardStock <= 0): ?>
                    Rupture de stock
                <?php elseif ($cardStock <= 5): ?>
                    Plus que <?= $cardStock ?> en stock
                <?php else: ?>
                    En stock
                <?php endif; ?>
            </p>

            <div class="product-footer">
                <span class="price current"><?= e(format_price($cardPrice)) ?></span>
                <?php if ($cardIsSale): ?>
                    <span class="price old"><?= e(format_price($cardOld)) ?></span>
                <?php endif; ?>
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
