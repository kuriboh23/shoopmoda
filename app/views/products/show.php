<?php

declare(strict_types=1);

/**
 * Product detail page.
 *
 * The buy bar (quantity + add to cart) is sticky at the bottom of the
 * viewport while the visitor reads the product sheet. The quantity input
 * is attached to the main form through the `form` attribute so it can
 * live inside that bar.
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

$mainImage   = product_image_url((int) $product['id'], $images[0] ?? null);
$isWished    = in_array((string) $product['slug'], $wishSlugs ?? [], true);
$discount    = $isOnSale ? (int) round((1 - ($price / max($oldPrice, 0.01))) * 100) : 0;
$categoryUrl = !empty($product['category_slug']) ? url('/categorie/' . $product['category_slug']) : null;
?>
<section class="wrap product-page">
    <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="<?= e(url('/')) ?>">Accueil</a>
        <?php if ($categoryUrl !== null): ?>
            <span aria-hidden="true">›</span>
            <a href="<?= e($categoryUrl) ?>"><?= e($product['category_name'] ?? 'Catégorie') ?></a>
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
            <?php if (!empty($product['category_name']) && $categoryUrl !== null): ?>
                <p class="product-category">
                    <a href="<?= e($categoryUrl) ?>"><?= e($product['category_name']) ?></a>
                </p>
            <?php endif; ?>

            <h1 class="product-detail-title"><?= e($product['title']) ?></h1>

            <div class="product-price-block">
                <span class="price current"><?= e(format_price($price)) ?></span>
                <?php if ($isOnSale): ?>
                    <span class="price old"><?= e(format_price($oldPrice)) ?></span>
                    <span class="discount-badge">-<?= $discount ?>%</span>
                <?php endif; ?>

                <span class="stock-status <?= $hasStock ? 'stock-ok' : 'stock-out' ?>">
                    <?= icon($hasStock ? 'check-circle' : 'alert-circle', 'icon icon--sm') ?>
                    <?= $hasStock ? 'En stock' : 'Rupture de stock' ?>
                </span>
            </div>

            <p class="product-description"><?= nl2br(e($product['description'] ?? '')) ?></p>

            <form
                id="cartForm"
                class="cart-form"
                action="<?= e(url('/panier/ajouter')) ?>"
                method="POST"
                data-checkout-url="<?= e(url('/commande')) ?>"
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

            <?php /* Adds the piece to the cart and goes straight to the details form. */ ?>
            <button
                type="submit"
                form="cartForm"
                name="after"
                value="checkout"
                class="btn btn--ghost btn--block product-wa"
                <?= $hasStock ? '' : 'disabled' ?>
            >
                <?= icon('whatsapp', 'icon icon--sm') ?>
                Commander par WhatsApp
            </button>
            <p class="product-wa-note">Vos coordonnées de livraison sont demandées avant l’envoi de la commande.</p>

            <div class="cart-actions">
                <div class="qty-selector">
                    <button type="button" class="qty-btn qty-btn--minus" aria-label="Diminuer la quantité" disabled>
                        <?= icon('minus', 'icon icon--sm') ?>
                    </button>
                    <input
                        class="qty-input"
                        type="number"
                        name="quantity"
                        form="cartForm"
                        value="1"
                        min="1"
                        max="99"
                        aria-label="Quantité"
                    >
                    <button type="button" class="qty-btn qty-btn--plus" aria-label="Augmenter la quantité">
                        <?= icon('plus', 'icon icon--sm') ?>
                    </button>
                </div>

                <button type="submit" form="cartForm" class="btn btn--dark btn--add-cart" <?= $hasStock ? '' : 'disabled' ?>>
                    <?= icon('cart', 'icon icon--sm') ?>
                    Ajouter au panier
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <section class="related-section" aria-labelledby="relatedTitle">
            <div class="section-head">
                <h2 class="section-title" id="relatedTitle">Vous aimerez aussi</h2>
                <?php if ($categoryUrl !== null): ?>
                    <a class="section-link" href="<?= e($categoryUrl) ?>">Voir la collection</a>
                <?php endif; ?>
            </div>

            <ul class="related-list">
                <?php foreach ($related as $item): ?>
                    <?php
                    $relatedPrice = (float) ($item['sale_price'] ?: $item['price']);
                    $relatedOld   = (float) ($item['price'] ?? 0);
                    ?>
                    <li>
                        <a class="related-item" href="<?= e(url('/produit/' . $item['slug'])) ?>">
                            <img
                                class="related-thumb"
                                src="<?= e(product_image_url((int) ($item['id'] ?? 0), $item['primary_image'] ?? null)) ?>"
                                alt=""
                                loading="lazy"
                                width="46"
                                height="58"
                            >
                            <span class="related-info">
                                <span class="related-name"><?= e($item['title'] ?? '') ?></span>
                                <span class="related-price">
                                    <?= e(format_price($relatedPrice)) ?>
                                    <?php if ($relatedOld > $relatedPrice): ?>
                                        <s><?= e(format_price($relatedOld)) ?></s>
                                    <?php endif; ?>
                                </span>
                            </span>
                            <?= icon('chevron-right', 'icon icon--sm') ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</section>
