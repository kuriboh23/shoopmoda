<?php

declare(strict_types=1);

/**
 * Promotions / Flash sales.
 *
 * @var array $flashSales
 * @var string|null $nextEndAt
 * @var int   $cartCount
 * @var int   $wishCount
 */

$flashCount  = count($flashSales);
$showCountdown = $nextEndAt !== null && $flashCount > 0;
?>
<section class="wrap promotions-page">
    <header class="section-head">
        <div>
            <p class="eyebrow">Offres spéciales</p>
            <h1 class="section-title">Promotions</h1>
            <?php if ($showCountdown): ?>
                <p class="subtitle">
                    <span class="countdown-label">Offre — se termine dans</span>
                    <span class="countdown-timer" id="countdownTimer" data-end="<?= e($nextEndAt) ?>">
                        -- : -- : --
                    </span>
                </p>
            <?php endif; ?>
        </div>

        <a
            class="btn btn--wa btn--compact"
            href="<?= e(whatsapp_url('Bonjour, je souhaite commander les articles en promo.')) ?>"
            target="_blank"
            rel="noopener"
        >
            <?= icon('whatsapp', 'icon icon--sm') ?>
            Commander en promo
        </a>
    </header>

    <?php if (!empty($flashSales)): ?>
        <div class="promo-grid">
            <?php foreach ($flashSales as $product): ?>
                <article class="promo-card">
                    <a class="promo-link" href="<?= e(url('/produit/' . $product['slug'])) ?>">
                        <div class="promo-media">
                            <?php $img = $product['primary_image'] ?? null; ?>
                            <img
                                src="<?= e(product_image_url((int) $product['id'], $img)) ?>"
                                alt="<?= e($product['title']) ?>"
                                loading="lazy"
                                width="400"
                                height="500"
                            >
                            <span class="promo-badge">Flash</span>
                            <?php
                            $discount = (float) $product['price'] - (float) ($product['sale_price'] ?? $product['price']);
                            if ($discount > 0): ?>
                                <span class="discount-tag">-<?= e(number_format($discount, 0, '.', ' ')) ?> DH</span>
                            <?php endif; ?>
                        </div>

                        <div class="promo-info">
                            <h2 class="promo-title"><?= e($product['title']) ?></h2>

                            <div class="promo-price">
                                <span class="price current"><?= e(format_price((float) ($product['sale_price'] ?? $product['price']))) ?></span>
                                <?php if (!empty($product['sale_price'])): ?>
                                    <span class="price old"><?= e(format_price((float) $product['price'])) ?></span>
                                <?php endif; ?>
                            </div>

                            <button
                                class="btn btn--dark btn--compact btn--flash-cart"
                                data-slug="<?= e($product['slug']) ?>"
                                data-price="<?= (float) ($product['sale_price'] ?? $product['price']) ?>"
                                data-name="<?= e($product['title']) ?>"
                            >
                                <?= icon('cart', 'icon icon--sm') ?>
                                Ajouter au panier
                            </button>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-promo">
            <span class="empty-icon"><?= icon('tag', 'icon') ?></span>
            <h2>Aucune promotion en cours</h2>
            <p>Revenez bientôt pour découvrir nos offres flash !</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Voir la collection</a>
        </div>
    <?php endif; ?>
</section>

<style>
.promotions-page {
    padding-top: 24px;
    padding-bottom: 64px;
}

.promotions-page .section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 16px;
    border-bottom: 2px solid var(--primary);
}

.promotions-page .section-title {
    font-size: 24px;
    margin-top: 4px;
}

.promotions-page .subtitle {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--gray-text);
    font-size: 14px;
    flex-wrap: wrap;
}

.countdown-label {
    color: var(--gray-text);
}

.countdown-timer {
    font-family: monospace;
    font-size: 16px;
    font-weight: 700;
    color: var(--whatsapp);
    background: rgba(37, 211, 102, 0.1);
    padding: 4px 10px;
    border-radius: 6px;
    letter-spacing: 1px;
}

.promo-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.promo-card {
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--white);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.promo-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.promo-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.promo-media {
    position: relative;
    aspect-ratio: 4 / 5;
    background: #f5f5f5;
    overflow: hidden;
}

.promo-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.promo-card:hover .promo-media img {
    transform: scale(1.04);
}

.promo-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 4px 10px;
    background: var(--whatsapp);
    color: var(--dark);
    font-size: 10px;
    font-weight: 700;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.discount-tag {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 8px;
    background: var(--dark);
    color: var(--white);
    font-size: 11px;
    font-weight: 700;
    border-radius: 4px;
}

.promo-info {
    padding: 14px;
}

.promo-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    margin-bottom: 10px;
    color: var(--dark);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.promo-price {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 12px;
}

.promo-price .price.current {
    font-size: 18px;
    color: var(--primary);
}

.promo-price .price.old {
    font-size: 14px;
    color: var(--gray-text);
    text-decoration: line-through;
}

.btn--flash-cart {
    width: 100%;
    height: 44px;
    padding: 0 16px;
    border-radius: var(--radius-sm);
}

.empty-promo {
    text-align: center;
    padding: 64px 20px;
    color: var(--gray-text);
}

.empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: var(--light);
    border-radius: 50%;
    margin-bottom: 20px;
    color: var(--gray);
}

.empty-icon .icon {
    width: 36px;
    height: 36px;
}

.empty-promo h2 {
    font-size: 22px;
    color: var(--dark);
    margin-bottom: 8px;
}

.empty-promo p {
    font-size: 14px;
    margin-bottom: 24px;
}

@media (min-width: 768px) {
    .promo-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .promo-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 640px) {
    .promo-grid {
        grid-template-columns: 1fr;
    }

    .promotions-page .section-head {
        flex-direction: column;
    }
}
</style>
