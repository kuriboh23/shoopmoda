<?php

declare(strict_types=1);

/**
 * RESKIN login.
 *
 * @var string|null $error
 */
?>
<div class="rs-auth-card">
    <span class="rs-auth-mark" aria-hidden="true">
        <?= icon('palette', 'icon') ?>
    </span>

    <p class="rs-auth-eyebrow">Espace propriétaire</p>
    <h1 class="rs-auth-title">RESKIN</h1>
    <p class="rs-auth-text">
        Design, contenu et informations de la boutique. Cet espace est séparé
        de la gestion client (produits et catégories).
    </p>

    <?php if (!empty($error)): ?>
        <p class="rs-alert rs-alert--error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="rs-auth-form" method="POST" action="<?= e(url('/' . trim((string) config('reskin_path', 'reskin'), '/') . '/connexion')) ?>">
        <?= csrf_field() ?>

        <label class="rs-field">
            <span class="rs-field-label">Mot de passe</span>
            <input
                type="password"
                name="password"
                autocomplete="current-password"
                required
                autofocus
                placeholder="••••••••"
            >
        </label>

        <button class="rs-btn rs-btn--primary rs-btn--block" type="submit">
            <?= icon('arrow-right', 'icon icon--sm') ?>
            Ouvrir RESKIN
        </button>
    </form>

    <a class="rs-auth-back" href="<?= e(url('/')) ?>">
        <?= icon('arrow-left', 'icon icon--sm') ?>
        Retour à la boutique
    </a>
</div>
