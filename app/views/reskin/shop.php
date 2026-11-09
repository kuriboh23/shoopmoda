<?php

declare(strict_types=1);

/**
 * Owner-only shop identity. Reached from RESKIN, never from the client admin.
 *
 * @var array<string, string> $values
 * @var array<string, string> $errors
 * @var bool   $saved
 * @var string $shopUrl
 * @var string $backUrl
 * @var string $siteUrl
 * @var string $logoutUrl
 */

$flash = $flash ?? flash_message();
$error = static fn (string $key): string => isset($errors[$key]) ? ' is-invalid' : '';
$logoUrl = $values['logo'] !== '' && is_file(PUBLIC_PATH . '/' . ltrim($values['logo'], '/'))
    ? url($values['logo'])
    : null;
$flashValue = '';

if ($values['flash_sale_end'] !== '' && strtotime($values['flash_sale_end']) !== false) {
    $flashValue = date('Y-m-d\TH:i', (int) strtotime($values['flash_sale_end']));
}
?>
<div class="rs-shop">
    <header class="rs-shop-top">
        <a class="rs-shop-back" href="<?= e($backUrl) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour à RESKIN
        </a>

        <div class="rs-shop-title">
            <span class="rs-shop-mark" aria-hidden="true"><?= icon('settings', 'icon') ?></span>
            <span>
                <span class="rs-shop-eyebrow">Espace propriétaire · privé</span>
                <h1>Infos boutique</h1>
            </span>
        </div>

        <div class="rs-shop-top-actions">
            <a class="rs-btn rs-btn--ghost" href="<?= e($siteUrl) ?>" target="_blank" rel="noopener">
                <?= icon('eye', 'icon icon--sm') ?>
                Voir la boutique
            </a>

            <form method="POST" action="<?= e($logoutUrl) ?>">
                <?= csrf_field() ?>
                <button class="rs-btn rs-btn--ghost" type="submit">
                    <?= icon('logout', 'icon icon--sm') ?>
                    Déconnexion
                </button>
            </form>
        </div>
    </header>

    <main class="rs-shop-main">
        <p class="rs-shop-intro">
            Ces informations sont réservées au propriétaire. Le client garde uniquement
            la gestion des produits et des catégories dans son espace.
        </p>

        <?php if (is_array($flash)): ?>
            <p class="rs-shop-flash rs-shop-flash--<?= e((string) $flash['type']) ?>" role="status">
                <?= e((string) $flash['message']) ?>
            </p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <p class="rs-shop-flash rs-shop-flash--error" role="alert">
                Merci de corriger les champs signalés.
            </p>
        <?php endif; ?>

        <form class="rs-shop-form" method="POST" action="<?= e($shopUrl) ?>" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <section class="rs-shop-card">
                <div class="rs-shop-card-head">
                    <div>
                        <h2>Identité &amp; logo</h2>
                        <p>Ce que les visiteurs voient dans l’en-tête et le pied de page.</p>
                    </div>
                </div>

                <div class="rs-shop-logo">
                    <div class="rs-shop-logo-preview">
                        <?php if ($logoUrl !== null): ?>
                            <img src="<?= e($logoUrl) ?>" alt="Logo actuel" width="180" height="64">
                        <?php else: ?>
                            <span class="rs-shop-logo-empty">Aucun logo — le nom s’affiche en lettres.</span>
                        <?php endif; ?>
                    </div>

                    <div class="rs-shop-logo-actions">
                        <label class="rs-field">
                            <span class="rs-field-label">Remplacer le logo</span>
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                            <span class="rs-shop-hint">JPG, PNG ou WebP, 2 Mo maximum. Affiché dans l’en-tête, le menu et le pied de page.</span>
                            <?php if (isset($errors['logo'])): ?><span class="rs-shop-error"><?= e($errors['logo']) ?></span><?php endif; ?>
                        </label>

                        <?php if ($logoUrl !== null): ?>
                            <label class="rs-shop-check">
                                <input type="checkbox" name="remove_logo" value="1">
                                <span>Supprimer le logo et revenir au nom écrit</span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="rs-shop-card">
                <div class="rs-shop-card-head">
                    <div>
                        <h2>Boutique</h2>
                        <p>Nom affiché et frais de livraison appliqués au panier.</p>
                    </div>
                </div>

                <div class="rs-shop-grid">
                    <label class="rs-field">
                        <span class="rs-field-label">Nom de la boutique</span>
                        <input class="rs-shop-input<?= $error('store_name') ?>" type="text" name="store_name" value="<?= e($values['store_name']) ?>" maxlength="60">
                        <?php if (isset($errors['store_name'])): ?><span class="rs-shop-error"><?= e($errors['store_name']) ?></span><?php endif; ?>
                    </label>

                    <label class="rs-field">
                        <span class="rs-field-label">Frais de livraison (DH)</span>
                        <input class="rs-shop-input<?= $error('delivery_fee') ?>" type="number" step="0.01" min="0" name="delivery_fee" value="<?= e($values['delivery_fee']) ?>" inputmode="decimal">
                        <span class="rs-shop-hint">Offerte automatiquement dès 500 DH.</span>
                        <?php if (isset($errors['delivery_fee'])): ?><span class="rs-shop-error"><?= e($errors['delivery_fee']) ?></span><?php endif; ?>
                    </label>
                </div>
            </section>

            <section class="rs-shop-card">
                <div class="rs-shop-card-head">
                    <div>
                        <h2>Commande WhatsApp</h2>
                        <p>Le numéro qui reçoit les commandes du site.</p>
                    </div>
                </div>

                <label class="rs-field">
                    <span class="rs-field-label">Numéro WhatsApp du vendeur</span>
                    <input class="rs-shop-input<?= $error('whatsapp_number') ?>" type="tel" name="whatsapp_number" value="<?= e($values['whatsapp_number']) ?>" placeholder="212725605520" inputmode="tel">
                    <span class="rs-shop-hint">Format international, sans + ni espaces (ex. 212725605520).</span>
                    <?php if (isset($errors['whatsapp_number'])): ?><span class="rs-shop-error"><?= e($errors['whatsapp_number']) ?></span><?php endif; ?>
                </label>
            </section>

            <section class="rs-shop-card">
                <div class="rs-shop-card-head">
                    <div>
                        <h2>Bandeau, pied de page &amp; réseaux</h2>
                        <p>Les messages et liens visibles sur toutes les pages.</p>
                    </div>
                </div>

                <label class="rs-field">
                    <span class="rs-field-label">Premier message du bandeau</span>
                    <input class="rs-shop-input" type="text" name="announcement_text" value="<?= e($values['announcement_text']) ?>" maxlength="140" placeholder="Livraison rapide partout au Maroc">
                    <span class="rs-shop-hint">Les autres messages défilants se modifient dans RESKIN → Bandeau d’annonce.</span>
                </label>

                <label class="rs-field">
                    <span class="rs-field-label">Description du pied de page</span>
                    <textarea class="rs-shop-input rs-shop-textarea" name="footer_description" rows="3" maxlength="300"><?= e($values['footer_description']) ?></textarea>
                </label>

                <label class="rs-field">
                    <span class="rs-field-label">Lien Instagram</span>
                    <input class="rs-shop-input<?= $error('instagram_url') ?>" type="url" name="instagram_url" value="<?= e($values['instagram_url']) ?>" maxlength="200" placeholder="https://instagram.com/votre-compte">
                    <span class="rs-shop-hint">Laisser vide pour masquer l’icône Instagram.</span>
                    <?php if (isset($errors['instagram_url'])): ?><span class="rs-shop-error"><?= e($errors['instagram_url']) ?></span><?php endif; ?>
                </label>
            </section>

            <section class="rs-shop-card">
                <div class="rs-shop-card-head">
                    <div>
                        <h2>Promo flash</h2>
                        <p>Le compte à rebours affiché sur l’accueil.</p>
                    </div>
                </div>

                <label class="rs-field">
                    <span class="rs-field-label">Fin de la promo</span>
                    <input class="rs-shop-input<?= $error('flash_sale_end') ?>" type="datetime-local" name="flash_sale_end" value="<?= e($flashValue) ?>">
                    <span class="rs-shop-hint">Vide = pas de compte à rebours.</span>
                    <?php if (isset($errors['flash_sale_end'])): ?><span class="rs-shop-error"><?= e($errors['flash_sale_end']) ?></span><?php endif; ?>
                </label>
            </section>

            <div class="rs-shop-actions">
                <a class="rs-btn rs-btn--ghost" href="<?= e($backUrl) ?>">Annuler</a>
                <button class="rs-btn rs-btn--primary" type="submit">
                    <?= icon('check-circle', 'icon icon--sm') ?>
                    Enregistrer les infos
                </button>
            </div>
        </form>
    </main>
</div>
