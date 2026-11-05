<?php

declare(strict_types=1);

/**
 * Cart page.
 *
 * @var array<string, array<string, mixed>> $items
 * @var float $subtotal
 * @var float $deliveryFee
 * @var float $total
 * @var int   $cartCount
 */

$itemCount = count($items);
$freeShipping = $subtotal >= 500;
?>
<section
    class="wrap cart-page"
    data-cart-update="<?= e(url('/panier/quantite')) ?>"
    data-cart-remove="<?= e(url('/panier/retirer')) ?>"
    data-cart-empty="<?= e(url('/panier/vider')) ?>"
>
    <header class="cart-header">
        <a class="back-link" href="<?= e(url('/')) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour à la boutique
        </a>
        <h1 class="cart-title">Mon panier</h1>
        <p class="cart-count"><?= (int) $itemCount ?> <?= $itemCount === 1 ? 'article' : 'articles' ?></p>
    </header>

    <?php if (!empty($items)): ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($items as $slug => $item): ?>
                    <div class="cart-item" data-slug="<?= e($slug) ?>">
                        <a class="item-gallery" href="<?= e(url('/produit/' . $slug)) ?>">
                            <img
                                src="<?= e(product_image_url(0, $item['_img'] ?? null)) ?>"
                                alt="<?= e($item['name']) ?>"
                                loading="lazy"
                                width="120"
                                height="150"
                            >
                        </a>

                        <div class="item-info">
                            <a class="item-name" href="<?= e(url('/produit/' . $slug)) ?>">
                                <?= e($item['name']) ?>
                            </a>

                            <?php if (!empty($item['size'])): ?>
                                <p class="item-option">Taille : <?= e($item['size']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['color'])): ?>
                                <p class="item-option">Couleur : <?= e($item['color']) ?></p>
                            <?php endif; ?>

                            <div class="item-actions">
                                <div class="qty-mini">
                                    <button
                                        type="button"
                                        class="qty-btn"
                                        data-action="update"
                                        data-qty="<?= max(1, (int) ($item['quantity'] ?? 1) - 1) ?>"
                                        aria-label="Diminuer la quantité"
                                    ><?= icon('minus', 'icon icon--sm') ?></button>
                                    <span class="qty-value"><?= (int) ($item['quantity'] ?? 1) ?></span>
                                    <button
                                        type="button"
                                        class="qty-btn"
                                        data-action="update"
                                        data-qty="<?= min(99, (int) ($item['quantity'] ?? 1) + 1) ?>"
                                        aria-label="Augmenter la quantité"
                                    ><?= icon('plus', 'icon icon--sm') ?></button>
                                </div>

                                <button
                                    type="button"
                                    class="item-delete"
                                    data-action="remove"
                                    aria-label="Supprimer l'article"
                                ><?= icon('trash', 'icon icon--sm') ?></button>
                            </div>
                        </div>

                        <div class="item-price">
                            <span class="price"><?= e(format_price((float) $item['price'])) ?></span>
                            <span class="item-total"><?= e(format_price((float) $item['price'] * (int) ($item['quantity'] ?? 1))) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="button" class="cart-empty-btn" data-action="empty">
                    <?= icon('trash', 'icon icon--sm') ?>
                    Vider le panier
                </button>
            </div>

            <aside class="cart-summary">
                <h2 class="summary-title">Récapitulatif</h2>

                <div class="summary-rows">
                    <div class="summary-row">
                        <span>Sous-total (<?= (int) $cartCount ?> <?= $cartCount === 1 ? 'article' : 'articles' ?>)</span>
                        <span class="row-value"><?= e(format_price($subtotal)) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span class="row-value">
                            <?php if ($freeShipping): ?>
                                <span class="free-shipping">Gratuite</span>
                            <?php else: ?>
                                <?= e(format_price($deliveryFee)) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($freeShipping): ?>
                        <div class="summary-row summary-row--highlight">
                            <span>Livraison offerte dès 500 DH</span>
                        </div>
                    <?php endif; ?>
                </div>

                <hr class="summary-divider">

                <div class="summary-row summary-row--total">
                    <span>Total</span>
                    <span class="total-value"><?= e(format_price($total)) ?></span>
                </div>

                <p class="summary-note">
                    <?= icon('truck', 'icon icon--sm') ?>
                    Livraison 24-48h partout au Maroc
                </p>

                <div class="summary-actions">
                    <a
                        class="btn btn--wa btn--block"
                        href="<?= e(whatsapp_url('Bonjour, voici ma commande : ' . implode(', ', array_map(
                            static fn (array $item): string => $item['name'] . ' x' . (int) ($item['quantity'] ?? 1),
                            $items
                        )) . ' — Total ' . format_price($total))) ?>"
                        target="_blank"
                        rel="noopener"
                    >
                        <?= icon('whatsapp', 'icon icon--sm') ?>
                        Commander par WhatsApp
                    </a>

                    <a class="btn btn--ghost btn--block" href="<?= e(url('/')) ?>">
                        Continuer mes achats
                    </a>
                </div>

                <div class="payment-methods">
                    <span class="payment-label">Paiement à la livraison</span>
                    <div class="payment-icons">
                        <span class="payment-icon">💵</span>
                        <span class="payment-icon">🏦</span>
                        <span class="payment-icon">💳</span>
                    </div>
                </div>
            </aside>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('cart', 'icon') ?></span>
            <h2>Votre panier est vide</h2>
            <p>Découvrez notre collection et trouvez votre prochaine pièce favorite.</p>
            <div class="empty-actions">
                <a class="btn btn--dark btn--block" href="<?= e(url('/categorie/femme')) ?>">
                    Découvrir la collection
                </a>
                <a
                    class="btn btn--wa btn--block"
                    href="<?= e(whatsapp_url('Bonjour, je souhaite voir vos produits disponibles.')) ?>"
                    target="_blank"
                    rel="noopener"
                >
                    <?= icon('whatsapp', 'icon icon--sm') ?>
                    Voir par WhatsApp
                </a>
            </div>
        </div>
    <?php endif; ?>
</section>
