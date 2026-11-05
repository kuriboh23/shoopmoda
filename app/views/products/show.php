<?php

declare(strict_types=1);

/**
 * Fiche produit.
 *
 * @var array $product
 * @var int   $cartCount
 * @var int   $wishCount
 */

$price       = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
$oldPrice    = (float) ($product['price'] ?? 0);
$isOnSale    = !empty($product['sale_price']);
$hasStock    = (int) ($product['stock'] ?? 0) > 0;
$sizes       = $product['sizes'] !== null ? json_decode($product['sizes'], true) : [];
$colors      = $product['colors'] !== null ? json_decode($product['colors'], true) : [];
$images      = $product['images'] !== null ? json_decode($product['images'], true) : [];
$primaryImg  = $product['primary_image'] ?? null;
?>
<section class="wrap product-page">
    <div class="breadcrumb">
        <a href="<?= e(url('/')) ?>">Accueil</a>
        <?php if (!empty($product['category_slug'])): ?>
            <span aria-hidden="true">›</span>
            <a href="<?= e(url('/categorie/' . $product['category_slug'])) ?>"><?= e($product['category_name'] ?? 'Catégorie') ?></a>
        <?php endif; ?>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?= e($product['title']) ?></span>
    </div>

    <div class="product-layout">
        <div class="product-gallery">
            <div class="gallery-main">
                <?php
                $imgSrc = product_image_url((int) $product['id'], $primaryImg);
                ?>
                <img
                    src="<?= e($imgSrc) ?>"
                    alt="<?= e($product['title']) ?>"
                    class="gallery-image"
                    width="600"
                    height="750"
                >
                <?php if ($isOnSale): ?>
                    <span class="sale-stamp">-<?= e(number_format((float) $product['price'] - $price, 0, '.', ' ')) ?> DH</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($images)): ?>
                <div class="gallery-thumbs" role="list" aria-label="Galerie produit">
                    <?php foreach ($images as $index => $image): ?>
                        <button
                            type="button"
                            class="gallery-thumb<?= $index === 0 ? ' is-active' : '' ?>"
                            data-img="<?= e(product_image_url((int) $product['id'], $image)) ?>"
                            aria-label="Voir l'image <?= (int) ($index + 1) ?>"
                        >
                            <img src="<?= e(product_image_url((int) $product['id'], $image)) ?>" alt="" loading="lazy" width="80" height="100">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-details">
            <p class="product-category">
                <?php if (!empty($product['category_slug'])): ?>
                    <a href="<?= e(url('/categorie/' . $product['category_slug'])) ?>">
                        <?= e($product['category_name'] ?? 'Catégorie') ?>
                    </a>
                <?php endif; ?>
            </p>

            <h1 class="product-title"><?= e($product['title']) ?></h1>

            <?php if (!empty($product['rating'])): ?>
                <div class="product-rating-row">
                    <span class="stars" aria-label="Note : <?= e($product['rating']) ?> / 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= icon('star', 'icon icon--sm ' . ($i <= (int) $product['rating'] ? '' : 'is-empty')) ?>
                        <?php endfor; ?>
                    </span>
                    <span class="rating-value"><?= e($product['rating']) ?> / 5</span>
                </div>
            <?php endif; ?>

            <div class="product-price-block">
                <?php if ($isOnSale): ?>
                    <span class="price current"><?= e(format_price($price)) ?></span>
                    <span class="price old"><?= e(format_price($oldPrice)) ?></span>
                    <span class="discount-badge">-<?= e(number_format((float) $product['price'] - $price, 0, '.', ' ')) ?> DH</span>
                <?php else: ?>
                    <span class="price current"><?= e(format_price($price)) ?></span>
                <?php endif; ?>
            </div>

            <p class="product-description">
                <?= nl2br(e($product['description'] ?? '')) ?>
            </p>

            <?php if (!empty($sizes)): ?>
                <div class="product-option">
                    <label class="option-label">
                        Taille
                        <span class="option-hint">Choisir la taille</span>
                    </label>
                    <div class="size-options" role="radiogroup" aria-label="Taille">
                        <?php foreach ($sizes as $size): ?>
                            <button
                                type="button"
                                class="size-btn"
                                data-size="<?= e($size) ?>"
                                role="radio"
                                aria-checked="false"
                            >
                                <?= e($size) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($colors)): ?>
                <div class="product-option">
                    <label class="option-label">
                        Couleur
                        <span class="option-hint">Choisir la couleur</span>
                    </label>
                    <div class="color-options" role="radiogroup" aria-label="Couleur">
                        <?php foreach ($colors as $index => $color): ?>
                            <button
                                type="button"
                                class="color-btn"
                                data-color="<?= e($color['name'] ?? '') ?>"
                                style="background: <?= e($color['hex'] ?? '#ccc') ?>"
                                aria-label="<?= e($color['name'] ?? 'Couleur') ?>"
                                role="radio"
                                aria-checked="<?= (int) ($index === 0) ?>"
                            >
                                <?php if (!empty($color['name'])): ?>
                                    <span class="color-name"><?= e($color['name']) ?></span>
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($hasStock): ?>
                <div class="stock-status stock-ok">
                    <?= icon('check-circle', 'icon icon--sm') ?>
                    En stock
                </div>
            <?php else: ?>
                <div class="stock-status stock-out">
                    <?= icon('alert-circle', 'icon icon--sm') ?>
                    Rupture de stock
                </div>
            <?php endif; ?>

            <div class="cart-actions">
                <?php if ($hasStock): ?>
                    <form
                        class="cart-form"
                        action="<?= e(url('/panier/ajouter')) ?>"
                        method="POST"
                        data-product-id="<?= (int) $product['id'] ?>"
                        data-product-slug="<?= e($product['slug']) ?>"
                        data-product-price="<?= (float) $price ?>"
                    >
                        <input type="hidden" name="slug" value="<?= e($product['slug']) ?>">
                        <input type="hidden" name="price" value="<?= (float) $price ?>">
                        <input type="hidden" name="name" value="<?= e($product['title']) ?>">

                        <div class="qty-selector">
                            <button
                                type="button"
                                class="qty-btn qty-btn--minus"
                                aria-label="Diminuer la quantité"
                                disabled
                            >
                                <?= icon('minus', 'icon icon--sm') ?>
                            </button>
                            <input
                                type="number"
                                name="quantity"
                                value="1"
                                min="1"
                                max="99"
                                class="qty-input"
                                aria-label="Quantité"
                            >
                            <button
                                type="button"
                                class="qty-btn qty-btn--plus"
                                aria-label="Augmenter la quantité"
                            >
                                <?= icon('plus', 'icon icon--sm') ?>
                            </button>
                        </div>

                        <button
                            type="submit"
                            class="btn btn--dark btn--block btn--add-cart"
                            <?= !$hasStock ? 'disabled' : '' ?>
                        >
                            <?= icon('cart', 'icon icon--sm') ?>
                            Ajouter au panier
                        </button>
                    </form>

                    <a
                        class="btn btn--ghost btn--block"
                        href="<?= e(whatsapp_url('Bonjour, je souhaite commander : ' . $product['title'] . ' - ' . format_price($price))) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= icon('whatsapp', 'icon icon--sm') ?>
                        Commander par WhatsApp
                    </a>
                <?php else: ?>
                    <a
                        class="btn btn--ghost btn--block"
                        href="<?= e(whatsapp_url('Bonjour, je souhaite être prévenu quand : ' . $product['title'] . ' sera de nouveau disponible.')) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= icon('bell', 'icon icon--sm') ?>
                        Disponible notification
                    </a>
                <?php endif; ?>
            </div>

            <div class="trust-badges">
                <div class="trust-item">
                    <?= icon('truck', 'icon icon--sm') ?>
                    <span>Livraison 24-48h</span>
                </div>
                <div class="trust-item">
                    <?= icon('shield', 'icon icon--sm') ?>
                    <span>Paiement sécurisé</span>
                </div>
                <div class="trust-item">
                    <?= icon('refresh', 'icon icon--sm') ?>
                    <span>Retour 7 jours</span>
                </div>
            </div>
        </div>
    </div>

    <?php
    $related = !empty($product['category_slug'])
        ? \App\Models\Product::related((int) $product['id'], $product['category_slug'], 4)
        : [];
    ?>
    <?php if (!empty($related)): ?>
        <section class="related-section">
            <h2 class="related-title">Vous aimerez aussi</h2>
            <div class="related-grid">
                <?php foreach ($related as $item): ?>
                    <article class="related-card">
                        <a href="<?= e(url('/produit/' . $item['slug'])) ?>" class="related-link">
                            <div class="related-media">
                                <img
                                    src="<?= e(product_image_url((int) $item['id'], $item['primary_image'] ?? null)) ?>"
                                    alt="<?= e($item['title']) ?>"
                                    loading="lazy"
                                    width="300"
                                    height="375"
                                >
                            </div>
                            <div class="related-info">
                                <h3 class="related-title-card"><?= e($item['title']) ?></h3>
                                <p class="related-price"><?= e(format_price((float) ($item['sale_price'] ?? $item['price'] ?? 0))) ?></p>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<style>
.product-page {
    padding-top: 24px;
    padding-bottom: 64px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 13px;
    color: var(--gray-text);
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span[aria-current="page"] {
    color: var(--dark);
    font-weight: 500;
}

.product-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 32px;
    margin-bottom: 48px;
}

.product-gallery {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.gallery-main {
    position: relative;
    aspect-ratio: 4 / 5;
    background: #f8f8f8;
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sale-stamp {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 6px 12px;
    background: var(--whatsapp);
    color: var(--dark);
    font-size: 13px;
    font-weight: 700;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
}

.gallery-thumbs {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.gallery-thumbs::-webkit-scrollbar {
    height: 4px;
}

.gallery-thumb {
    flex-shrink: 0;
    width: 72px;
    height: 90px;
    border: 2px solid transparent;
    border-radius: var(--radius-sm);
    overflow: hidden;
    padding: 0;
    background: none;
    cursor: pointer;
    transition: border-color 0.2s ease;
}

.gallery-thumb:hover {
    border-color: var(--gray);
}

.gallery-thumb.is-active {
    border-color: var(--primary);
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-category {
    font-size: 13px;
    color: var(--primary);
    font-weight: 500;
}

.product-category a {
    color: var(--primary);
    text-decoration: none;
}

.product-title {
    font-size: 26px;
    font-weight: 700;
    line-height: 1.25;
    color: var(--dark);
    margin: 0;
}

.product-rating-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    display: flex;
    gap: 2px;
    color: var(--star);
}

.stars .icon {
    width: 16px;
    height: 16px;
    fill: var(--star);
}

.stars .icon.is-empty {
    fill: var(--light);
    opacity: 0.5;
}

.rating-value {
    color: var(--gray-text);
    font-size: 14px;
}

.product-price-block {
    display: flex;
    align-items: baseline;
    gap: 12px;
    flex-wrap: wrap;
}

.price.current {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
}

.price.old {
    font-size: 18px;
    color: var(--gray-text);
    text-decoration: line-through;
}

.discount-badge {
    padding: 4px 10px;
    background: var(--whatsapp);
    color: var(--dark);
    font-size: 12px;
    font-weight: 700;
    border-radius: 4px;
    text-transform: uppercase;
}

.product-description {
    color: var(--gray-text);
    font-size: 15px;
    line-height: 1.7;
    margin: 0;
}

.product-option {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.option-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
    color: var(--dark);
}

.option-hint {
    font-weight: 400;
    color: var(--gray-text);
    font-size: 12px;
}

.size-options {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.size-btn {
    min-width: 48px;
    height: 40px;
    padding: 0 16px;
    border: 1px solid var(--light);
    border-radius: var(--radius-sm);
    background: var(--white);
    font-size: 13px;
    font-weight: 500;
    color: var(--dark);
    cursor: pointer;
    transition: all 0.2s ease;
}

.size-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.size-btn[aria-checked="true"] {
    background: var(--dark);
    border-color: var(--dark);
    color: var(--white);
}

.color-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.color-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--light);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s ease;
}

.color-btn:hover {
    border-color: var(--gray);
}

.color-btn[aria-checked="true"] {
    border-color: var(--dark);
    box-shadow: 0 0 0 2px var(--white), 0 0 0 4px var(--dark);
}

.color-name {
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 10px;
    color: var(--gray-text);
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.color-btn:hover .color-name,
.color-btn[aria-checked="true"] .color-name {
    opacity: 1;
}

.stock-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 500;
}

.stock-ok {
    color: var(--whatsapp);
}

.stock-ok .icon {
    fill: var(--whatsapp);
    width: 16px;
    height: 16px;
}

.stock-out {
    color: #e53e3e;
}

.stock-out .icon {
    fill: #e53e3e;
    width: 16px;
    height: 16px;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-top: 8px;
    border-top: 1px solid var(--light);
}

.qty-selector {
    display: flex;
    align-items: center;
    gap: 0;
    width: 100%;
    max-width: 200px;
    border: 1px solid var(--light);
    border-radius: var(--radius-sm);
    overflow: hidden;
}

.qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--white);
    border: none;
    cursor: pointer;
    transition: background 0.2s ease;
}

.qty-btn:hover:not(:disabled) {
    background: var(--light);
}

.qty-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.qty-btn .icon {
    width: 18px;
    height: 18px;
    fill: var(--dark);
}

.qty-input {
    width: 100%;
    height: 48px;
    border: none;
    border-left: 1px solid var(--light);
    border-right: 1px solid var(--light);
    text-align: center;
    font-size: 15px;
    font-weight: 600;
    -moz-appearance: textfield;
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.btn--add-cart {
    height: 52px;
    font-size: 15px;
    padding: 0 24px;
}

.trust-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--light);
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--gray-text);
}

.trust-item .icon {
    width: 14px;
    height: 14px;
    fill: var(--primary);
}

.related-section {
    padding-top: 40px;
    border-top: 1px solid var(--light);
}

.related-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--dark);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.related-card {
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--white);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.related-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.related-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.related-media {
    aspect-ratio: 4 / 5;
    overflow: hidden;
    background: #f5f5f5;
}

.related-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-card:hover .related-media img {
    transform: scale(1.03);
}

.related-info {
    padding: 12px 14px;
}

.related-title-card {
    font-size: 13px;
    font-weight: 600;
    line-height: 1.35;
    margin-bottom: 6px;
    color: var(--dark);
}

.related-price {
    font-size: 14px;
    font-weight: 700;
    color: var(--primary);
    margin: 0;
}

@media (min-width: 768px) {
    .product-layout {
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    .product-title {
        font-size: 30px;
    }

    .related-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 480px) {
    .product-title {
        font-size: 22px;
    }

    .price.current {
        font-size: 24px;
    }
}
</style>
