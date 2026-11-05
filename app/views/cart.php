<?php

declare(strict_types=1);

/**
 * Panier.
 *
 * @var array  $items
 * @var float  $subtotal
 * @var float  $deliveryFee
 * @var float  $total
 * @var int    $cartCount
 * @var int    $wishCount
 */

$itemCount = count($items);
?>
<section class="wrap cart-page">
    <header class="cart-header">
        <a class="back-link" href="<?= e(url('/')) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour à l'accueil
        </a>
        <h1 class="cart-title">Mon panier</h1>
        <p class="cart-count"><?= (int) $itemCount ?> <?= $itemCount === 1 ? 'article' : 'articles' ?></p>
    </header>

    <?php if (!empty($items)): ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($items as $slug => $item): ?>
                    <div class="cart-item" data-slug="<?= e($slug) ?>">
                        <div class="item-gallery">
                            <?php
                            $img = !empty($item['_img']) ? $item['_img'] : null;
                            $imgSrc = product_image_url(0, $img);
                            if ($imgSrc === asset('assets/placeholder.webp')):
                            ?>
                                <div class="item-placeholder">
                                    <?= icon('shirt', 'icon') ?>
                                </div>
                            <?php else: ?>
                                <img src="<?= e($imgSrc) ?>" alt="<?= e($item['name']) ?>" loading="lazy" width="120" height="150">
                            <?php endif; ?>
                        </div>

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
                                <div class="qty-selector qty-mini">
                                    <button
                                        type="button"
                                        class="qty-btn qty-btn--minus"
                                        data-action="update"
                                        data-qty="<?= max(0, ($item['quantity'] ?? 1) - 1) ?>"
                                        aria-label="Diminuer la quantité"
                                    >
                                        <?= icon('minus', 'icon icon--sm') ?>
                                    </button>
                                    <span class="qty-value"><?= (int) ($item['quantity'] ?? 1) ?></span>
                                    <button
                                        type="button"
                                        class="qty-btn qty-btn--plus"
                                        data-action="update"
                                        data-qty="<?= min(99, ($item['quantity'] ?? 1) + 1) ?>"
                                        aria-label="Augmenter la quantité"
                                    >
                                        <?= icon('plus', 'icon icon--sm') ?>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    class="item-delete"
                                    data-action="remove"
                                    aria-label="Supprimer l'article"
                                >
                                    <?= icon('trash', 'icon icon--sm') ?>
                                </button>
                            </div>
                        </div>

                        <div class="item-price">
                            <span class="price"><?= e(format_price((float) $item['price'])) ?></span>
                            <span class="item-total"><?= e(format_price((float) $item['price'] * ($item['quantity'] ?? 1))) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <aside class="cart-summary">
                <h2 class="summary-title">Récapitulatif</h2>

                <div class="summary-rows">
                    <div class="summary-row">
                        <span>Sous-total (<?= (int) Cart::count() ?> articles)</span>
                        <span class="row-value"><?= e(format_price($subtotal)) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Livraison</span>
                        <span class="row-value">
                            <?php if ($subtotal >= 500): ?>
                                <span class="free-shipping">Gratuite</span>
                            <?php else: ?>
                                <?= e(format_price($deliveryFee)) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($subtotal >= 500): ?>
                        <div class="summary-row summary-row--highlight">
                            <span>🚚 Livraison offerte dès 500 DH</span>
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
                        href="<?= e(whatsapp_url('Bonjour, voici ma commande : ' . implode(', ', array_map(static fn (array $item): string => $item['name'] . ' x' . $item['quantity'], $items)) . ' - Total ' . format_price($total))) ?>"
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
        <div class="empty-cart">
            <span class="empty-icon"><?= icon('cart', 'icon') ?></span>
            <h2 class="empty-title">Votre panier est vide</h2>
            <p class="empty-text">Découvrez notre collection et trouvez votrestyle.</p>
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

<style>
.cart-page {
    padding-top: 24px;
    padding-bottom: 64px;
    min-height: calc(100vh - 200px);
}

.cart-header {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--light);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--gray-text);
    text-decoration: none;
    margin-bottom: 4px;
    transition: color 0.2s ease;
}

.back-link:hover {
    color: var(--primary);
}

.back-link .icon {
    width: 16px;
    height: 16px;
}

.cart-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark);
    margin: 0;
}

.cart-count {
    font-size: 13px;
    color: var(--gray-text);
    margin: 0;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}

@media (min-width: 768px) {
    .cart-layout {
        grid-template-columns: 1fr 360px;
        gap: 32px;
    }

    .cart-items {
        min-height: 300px;
    }
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto;
    gap: 16px;
    align-items: center;
    padding: 16px;
    background: var(--white);
    border: 1px solid var(--light);
    border-radius: var(--radius);
    transition: border-color 0.2s ease;
}

.cart-item:hover {
    border-color: var(--primary-soft);
}

.item-gallery {
    width: 100px;
    height: 120px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: #f5f5f5;
    flex-shrink: 0;
}

.item-gallery img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray);
}

.item-placeholder .icon {
    width: 40px;
    height: 40px;
    fill: var(--gray);
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.item-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--dark);
    text-decoration: none;
    line-height: 1.35;
}

.item-name:hover {
    color: var(--primary);
}

.item-option {
    font-size: 12px;
    color: var(--gray-text);
    margin: 0;
}

.item-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.qty-mini {
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--light);
    border-radius: var(--radius-sm);
    overflow: hidden;
    width: fit-content;
}

.qty-mini .qty-btn {
    width: 32px;
    height: 32px;
    background: var(--white);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.qty-mini .qty-btn:hover {
    background: var(--light);
}

.qty-mini .qty-btn .icon {
    width: 14px;
    height: 14px;
}

.qty-mini .qty-value {
    min-width: 32px;
    text-align: center;
    font-size: 13px;
    font-weight: 600;
    border-left: 1px solid var(--light);
    border-right: 1px solid var(--light);
    height: 32px;
    line-height: 32px;
}

.item-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid var(--light);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--gray);
    cursor: pointer;
    transition: all 0.2s ease;
}

.item-delete:hover {
    border-color: #e53e3e;
    color: #e53e3e;
    background: #fff5f5;
}

.item-delete .icon {
    width: 14px;
    height: 14px;
}

.item-price {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    min-width: 80px;
}

.item-price .price {
    font-size: 13px;
    color: var(--gray-text);
}

.item-price .item-total {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark);
}

.cart-summary {
    background: var(--light);
    border-radius: var(--radius);
    padding: 20px;
    position: sticky;
    top: 80px;
}

.summary-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 16px;
}

.summary-rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
    color: var(--dark);
}

.row-value {
    font-weight: 500;
}

.summary-row--highlight {
    background: rgba(37, 211, 102, 0.1);
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 13px;
    color: #1FBE5A;
    font-weight: 500;
}

.free-shipping {
    color: #1FBE5A;
    font-weight: 600;
}

.summary-divider {
    border: none;
    border-top: 1px solid var(--light);
    margin: 16px 0;
}

.summary-row--total {
    font-size: 16px;
    font-weight: 700;
    color: var(--dark);
}

.total-value {
    font-size: 20px;
    color: var(--primary);
}

.summary-note {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--gray-text);
    margin: 12px 0 16px;
}

.summary-note .icon {
    width: 14px;
    height: 14px;
    fill: var(--gray);
}

.summary-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 16px;
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid var(--light);
}

.payment-label {
    font-size: 12px;
    color: var(--gray-text);
}

.payment-icons {
    display: flex;
    gap: 4px;
}

.payment-icon {
    font-size: 18px;
    opacity: 0.6;
}

.empty-cart {
    text-align: center;
    padding: 64px 20px;
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
    fill: var(--gray);
}

.empty-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 8px;
}

.empty-text {
    color: var(--gray-text);
    font-size: 14px;
    margin: 0 0 24px;
}

.empty-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 300px;
    margin-inline: auto;
}

@media (max-width: 640px) {
    .cart-item {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .item-gallery {
        width: 100%;
        height: 150px;
    }

    .item-price {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}
</style>
