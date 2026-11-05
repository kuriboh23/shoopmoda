<?php

declare(strict_types=1);

/**
 * Admin store settings.
 *
 * @var array<string, string> $values
 * @var array<string, string> $errors
 */

$flashValue = '';

if ($values['flash_sale_end'] !== '' && strtotime($values['flash_sale_end']) !== false) {
    $flashValue = date('Y-m-d\TH:i', (int) strtotime($values['flash_sale_end']));
}

$error = static fn (string $key): string => isset($errors[$key]) ? ' is-invalid' : '';
?>
<?php if ($errors !== []): ?>
    <p class="admin-flash admin-flash--error" role="alert">Merci de corriger les champs signalés.</p>
<?php endif; ?>

<form class="admin-form" method="POST" action="<?= e(admin_url('reglages')) ?>" novalidate>
    <?= csrf_field() ?>

    <section class="admin-card">
        <h2 class="admin-card-title">Boutique</h2>

        <div class="field-grid">
            <label class="field">
                <span class="field-label">Nom de la boutique</span>
                <input class="input<?= $error('store_name') ?>" type="text" name="store_name" value="<?= e($values['store_name']) ?>" maxlength="60">
                <?php if (isset($errors['store_name'])): ?><span class="field-error"><?= e($errors['store_name']) ?></span><?php endif; ?>
            </label>

            <label class="field">
                <span class="field-label">Frais de livraison (DH)</span>
                <input class="input<?= $error('delivery_fee') ?>" type="number" step="0.01" min="0" name="delivery_fee" value="<?= e($values['delivery_fee']) ?>" inputmode="decimal">
                <span class="field-hint">Offerte automatiquement dès 500 DH.</span>
                <?php if (isset($errors['delivery_fee'])): ?><span class="field-error"><?= e($errors['delivery_fee']) ?></span><?php endif; ?>
            </label>
        </div>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Commande WhatsApp</h2>

        <label class="field">
            <span class="field-label">Numéro WhatsApp du vendeur</span>
            <input
                class="input<?= $error('whatsapp_number') ?>"
                type="tel"
                name="whatsapp_number"
                value="<?= e($values['whatsapp_number']) ?>"
                placeholder="212725605520"
                inputmode="tel"
            >
            <span class="field-hint">Format international, sans + ni espaces (ex. 212725605520).</span>
            <?php if (isset($errors['whatsapp_number'])): ?><span class="field-error"><?= e($errors['whatsapp_number']) ?></span><?php endif; ?>
        </label>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Bandeau d’annonce</h2>

        <label class="field">
            <span class="field-label">Message principal</span>
            <input class="input" type="text" name="announcement_text" value="<?= e($values['announcement_text']) ?>" maxlength="140" placeholder="Livraison rapide partout au Maroc">
            <span class="field-hint">140 caractères maximum. Affiché dans le bandeau haut du site.</span>
        </label>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Promo flash</h2>

        <label class="field">
            <span class="field-label">Fin de la promo</span>
            <input class="input<?= $error('flash_sale_end') ?>" type="datetime-local" name="flash_sale_end" value="<?= e($flashValue) ?>">
            <span class="field-hint">Vide = pas de compte à rebours.</span>
            <?php if (isset($errors['flash_sale_end'])): ?><span class="field-error"><?= e($errors['flash_sale_end']) ?></span><?php endif; ?>
        </label>
    </section>

    <div class="admin-form-actions">
        <a class="btn btn--ghost" href="<?= e(admin_url()) ?>">Annuler</a>
        <button class="btn btn--dark" type="submit">
            <?= icon('check-circle', 'icon icon--sm') ?>
            Enregistrer
        </button>
    </div>
</form>
