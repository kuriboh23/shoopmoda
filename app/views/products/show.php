<?php

declare(strict_types=1);

/**
 * Product detail page.
 *
 * @var array<string, mixed>       $product
 * @var array<int, string>         $gallery
 * @var array<int, array>          $related
 * @var array<int, string>         $wishSlugs
 */

$price    = (float) ($product['sale_price'] ?: $product['price']);
$oldPrice = (float) $product['price'];
$isOnSale = !empty($product['sale_price']) && $oldPrice > $price;
$hasStock = (int) ($product['stock'] ?? 0) > 0;

$sizes  = json_decode((string) ($product['sizes'] ?? '[]'), true) ?: [];
$colors = json_decode((string) ($product['colors'] ?? '[]'), true) ?: [];
$images = $gallery !== [] ? $gallery : [$product['primary_image'] ?? null];

$mainImage = product_image_url((int) $product['id'], $images[0] ?? null);
$isWished  = in_array((string) $product['slug'], $wishSlugs ?? [], true);
$discount  = $isOnSale ? (int) round((1 - ($price / max($oldPrice, 0.01))) * 100) : 0;
?>
<section class="wrap product-page">
    <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="<?= e(url('/')) ?>">Accueil</a>
        <?php if (!empty($product['category_slug'])): ?>
            <span aria-hidden="true">›</span>
            <a href="<?= e(url('/categorie/' . $product['category_slug'])) ?>"><?= e($product['category_name'] ?? 'Catégorie') ?></a>
        <?php endif; ?>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?= e($product['title']) ?></span>
    </nav>

    <div class="product-layout">
        <div class="product-gallery">
            <div class="gallery-main">
                <img
                    id="galleryMainImage"
                    src="<?= e($mainImage) ?>"
                    alt="<?= e($product['title']) ?>"
                    class="gallery-image"
                    width="600"
                    height="750"
                >
                <?php if ($discount > 0): ?>
                    <span class="sale-stamp">-<?= $discount ?>%</span>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1): ?>
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
            <?php if (!empty($product['category_name'])): ?>
                <p class="product-category">
                    <a href="<?= e(url('/categorie/' . $product['category_slug'])) ?>"><?= e($product['category_name']) ?></a>
                </p>
            <?php endif; ?>

            <h1 class="product-detail-title"><?= e($product['title']) ?></h1>

            <div class="product-rating-row">
                <?php if ((float) $product['rating'] > 0): ?>
                    <span class="stars" aria-label="Note : <?= e($product['rating']) ?> sur 5">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= icon('star', 'icon icon--sm' . ($i <= (int) $product['rating'] ? '' : ' is-empty')) ?>
                        <?php endfor; ?>
                    </span>
                    <span class="rating-value"><?= e(number_format((float) $product['rating'], 1, ',', '')) ?> / 5</span>
                <?php endif; ?>

                <span class="stock-status <?= $hasStock ? 'stock-ok' : 'stock-out' ?>">
                    <?= icon($hasStock ? 'check-circle' : 'alert-circle', 'icon icon--sm') ?>
                    <?= $hasStock ? 'En stock' : 'Rupture de stock' ?>
                </span>
            </div>

            <div class="product-price-block">
                <span class="price current"><?= e(format_price($price)) ?></span>
                <?php if ($isOnSale): ?>
                    <span class="price old"><?= e(format_price($oldPrice)) ?></span>
                    <span class="discount-badge">-<?= $discount ?>%</span>
                <?php endif; ?>
            </div>

            <p class="product-description"><?= nl2br(e($product['description'] ?? '')) ?></p>

            <form
                class="cart-form"
                action="<?= e(url('/panier/ajouter')) ?>"
                method="POST"
            >
                <input type="hidden" name="slug" value="<?= e($product['slug']) ?>">
                <input type="hidden" name="size" id="selectedSize" value="<?= e($sizes[0] ?? '') ?>">
                <input type="hidden" name="color" id="selectedColor" value="<?= e($colors[0]['name'] ?? '') ?>">

                <?php if (!empty($sizes)): ?>
                    <div class="product-option">
                        <span class="option-label">
                            Taille
                            <span class="option-hint">Choisir la taille</span>
                        </span>
                        <div class="size-options" role="radiogroup" aria-label="Taille">
                            <?php foreach ($sizes as $index => $size): ?>
                                <button
                                    type="button"
                                    class="size-btn"
                                    data-size="<?= e($size) ?>"
                                    role="radio"
                                    aria-checked="<?= $index === 0 ? 'true' : 'false' ?>"
                                ><?= e($size) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($colors)): ?>
                    <div class="product-option">
                        <span class="option-label">
                            Couleur
                            <span class="option-hint">Choisir la couleur</span>
                        </span>
                        <div class="color-options" role="radiogroup" aria-label="Couleur">
                            <?php foreach ($colors as $index => $color): ?>
                                <?php
                                $hex = (string) ($color['hex'] ?? '#CCCCCC');
                                $hex = preg_match('/^#[0-9a-fA-F]{3,8}$/', $hex) === 1 ? $hex : '#CCCCCC';
                                ?>
                                <button
                                    type="button"
                                    class="color-btn"
                                    data-color="<?= e($color['name'] ?? '') ?>"
                                    aria-label="<?= e($color['name'] ?? 'Couleur') ?>"
                                    role="radio"
                                    aria-checked="<?= $index === 0 ? 'true' : 'false' ?>"
                                >
                                    <svg class="color-swatch" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle cx="12" cy="12" r="11" fill="<?= e($hex) ?>"></circle>
                                    </svg>
                                    <?php if (!empty($color['name'])): ?>
                                        <span class="color-name"><?= e($color['name']) ?></span>
                                    <?php endif; ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="product-option">
                    <span class="option-label">Quantité</span>
                    <div class="qty-selector">
                        <button type="button" class="qty-btn qty-btn--minus" aria-label="Diminuer la quantité" disabled>
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
                        <button type="button" class="qty-btn qty-btn--plus" aria-label="Augmenter la quantité">
                            <?= icon('plus', 'icon icon--sm') ?>
                        </button>
                    </div>
                </div>

                <div class="cart-actions">
                    <button type="submit" class="btn btn--dark btn--block btn--add-cart" <?= $hasStock ? '' : 'disabled' ?>>
                        <?= icon('cart', 'icon icon--sm') ?>
                        Ajouter au panier
                    </button>

                    <a
                        class="btn btn--ghost btn--block"
                        href="<?= e(whatsapp_url('Bonjour, je souhaite commander : ' . $product['title'] . ' — ' . format_price($price))) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= icon('whatsapp', 'icon icon--sm') ?>
                        Commander par WhatsApp
                    </a>
                </div>
            </form>

            <button
                type="button"
                class="wish-toggle<?= $isWished ? ' is-active' : '' ?>"
                data-wish-slug="<?= e($product['slug']) ?>"
                data-wishlist-url="<?= e(url('/favoris/ajouter')) ?>"
                aria-pressed="<?= $isWished ? 'true' : 'false' ?>"
            >
                <?= icon('heart', 'icon icon--sm') ?>
                <span><?= $isWished ? 'Dans mes favoris' : 'Ajouter aux favoris' ?></span>
            </button>

            <div class="trust-badges">
                <div class="trust-item"><?= icon('truck', 'icon icon--sm') ?><span>Livraison 24-48h</span></div>
                <div class="trust-item"><?= icon('shield', 'icon icon--sm') ?><span>Paiement à la livraison</span></div>
                <div class="trust-item"><?= icon('refresh', 'icon icon--sm') ?><span>Échange sous 7 jours</span></div>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <section class="related-section" aria-labelledby="relatedTitle">
            <div class="section-head">
                <h2 class="section-title" id="relatedTitle">Vous aimerez aussi</h2>
                <?php if (!empty($product['category_slug'])): ?>
                    <a class="section-link" href="<?= e(url('/categorie/' . $product['category_slug'])) ?>">Voir la collection</a>
                <?php endif; ?>
            </div>

            <div class="product-grid">
                <?php foreach ($related as $item): ?>
                    <?php partial('partials/product-card', ['product' => $item, 'wishSlugs' => $wishSlugs ?? []]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
