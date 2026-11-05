<?php

declare(strict_types=1);

/**
 * Checkout: customer details before the WhatsApp hand-off.
 *
 * @var array<string, array<string, mixed>> $items
 * @var float $subtotal
 * @var float $deliveryFee
 * @var float $total
 * @var array<string, string> $errors
 * @var array<string, string> $old
 */

$itemCount = array_sum(array_map(
    static fn (array $item): int => (int) ($item['quantity'] ?? 1),
    $items
));
?>
<section class="wrap checkout-page">
    <header class="checkout-head">
        <a class="back-link" href="<?= e(url('/panier')) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour au panier
        </a>
        <p class="eyebrow">Commande</p>
        <h1 class="checkout-title">Finaliser ma commande</h1>
        <p class="checkout-sub">Renseignez vos informations de livraison, puis confirmez la commande sur WhatsApp.</p>
    </header>

    <div class="checkout-layout">
        <form class="checkout-form" method="POST" action="<?= e(url('/commande')) ?>" novalidate>
            <?= csrf_field() ?>

            <?php if (isset($errors['form'])): ?>
                <p class="checkout-error" role="alert"><?= e($errors['form']) ?></p>
            <?php endif; ?>

            <section class="checkout-card">
                <h2 class="checkout-card-title">
                    <?= icon('truck', 'icon icon--sm') ?>
                    Informations de livraison
                </h2>

                <div class="checkout-grid">
                    <label class="field">
                        <span class="field-label">Nom complet *</span>
                        <input
                            class="input<?= isset($errors['name']) ? ' is-invalid' : '' ?>"
                            type="text"
                            name="name"
                            value="<?= e($old['name']) ?>"
                            autocomplete="name"
                            maxlength="80"
                            required
                        >
                        <?php if (isset($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
                    </label>

                    <label class="field">
                        <span class="field-label">Téléphone *</span>
                        <input
                            class="input<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                            type="tel"
                            name="phone"
                            value="<?= e($old['phone']) ?>"
                            autocomplete="tel"
                            inputmode="tel"
                            maxlength="20"
                            placeholder="06 12 34 56 78"
                            required
                        >
                        <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                    </label>

                    <label class="field">
                        <span class="field-label">Ville *</span>
                        <input
                            class="input<?= isset($errors['city']) ? ' is-invalid' : '' ?>"
                            type="text"
                            name="city"
                            value="<?= e($old['city']) ?>"
                            autocomplete="address-level2"
                            maxlength="60"
                            required
                        >
                        <?php if (isset($errors['city'])): ?><span class="field-error"><?= e($errors['city']) ?></span><?php endif; ?>
                    </label>

                    <label class="field field--wide">
                        <span class="field-label">Adresse complète *</span>
                        <input
                            class="input<?= isset($errors['address']) ? ' is-invalid' : '' ?>"
                            type="text"
                            name="address"
                            value="<?= e($old['address']) ?>"
                            autocomplete="street-address"
                            maxlength="200"
                            placeholder="Rue, immeuble, appartement…"
                            required
                        >
                        <?php if (isset($errors['address'])): ?><span class="field-error"><?= e($errors['address']) ?></span><?php endif; ?>
                    </label>

                    <label class="field field--wide">
                        <span class="field-label">Note (facultatif)</span>
                        <textarea
                            class="input"
                            name="note"
                            rows="3"
                            maxlength="300"
                            placeholder="Repère, horaire de livraison, taille souhaitée…"
                        ><?= e($old['note']) ?></textarea>
                    </label>
                </div>
            </section>

            <button class="btn btn--wa btn--block checkout-submit" type="submit">
                <?= icon('whatsapp', 'icon icon--sm') ?>
                Envoyer la commande sur WhatsApp
            </button>

            <p class="checkout-note">
                <?= icon('shield', 'icon icon--sm') ?>
                Aucun paiement en ligne. Vous confirmez le récapitulatif et le total directement avec la boutique sur WhatsApp.
            </p>
        </form>

        <aside class="checkout-summary" aria-labelledby="checkoutSummaryTitle">
            <h2 class="checkout-card-title" id="checkoutSummaryTitle">
                <?= icon('cart', 'icon icon--sm') ?>
                Votre commande
                <span class="checkout-count"><?= (int) $itemCount ?></span>
            </h2>

            <ul class="checkout-items">
                <?php foreach ($items as $slug => $item): ?>
                    <li class="checkout-item">
                        <img
                            class="checkout-thumb"
                            src="<?= e(product_image_url(0, $item['_img'] ?? null)) ?>"
                            alt=""
                            width="48"
                            height="60"
                            loading="lazy"
                        >

                        <div class="checkout-item-main">
                            <span class="checkout-item-name"><?= e($item['name']) ?></span>
                            <span class="checkout-item-variant">
                                <?= (int) ($item['quantity'] ?? 1) ?> × <?= e(format_price((float) $item['price'])) ?>
                                <?php if (!empty($item['size'])): ?> · <?= e($item['size']) ?><?php endif; ?>
                                <?php if (!empty($item['color'])): ?> · <?= e($item['color']) ?><?php endif; ?>
                            </span>
                        </div>

                        <span class="checkout-item-price">
                            <?= e(format_price((float) $item['price'] * (int) ($item['quantity'] ?? 1))) ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="summary-rows">
                <div class="summary-row">
                    <span>Sous-total</span>
                    <span class="row-value"><?= e(format_price($subtotal)) ?></span>
                </div>
                <div class="summary-row">
                    <span>Livraison</span>
                    <span class="row-value">
                        <?= $deliveryFee > 0 ? e(format_price($deliveryFee)) : '<span class="free-shipping">Gratuite</span>' ?>
                    </span>
                </div>
            </div>

            <hr class="summary-divider">

            <div class="summary-row summary-row--total">
                <span>Total</span>
                <span class="total-value"><?= e(format_price($total)) ?></span>
            </div>

            <a class="checkout-edit" href="<?= e(url('/panier')) ?>">
                <?= icon('edit', 'icon icon--sm') ?>
                Modifier le panier
            </a>
        </aside>
    </div>
</section>
