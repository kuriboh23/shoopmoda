<?php

declare(strict_types=1);

/**
 * Admin login.
 *
 * @var string      $email
 * @var string|null $error
 */
?>
<div class="admin-auth-card">
    <a class="admin-brand admin-brand--center" href="<?= e(url('/')) ?>">
        <span class="brand-wordmark">
            <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
        </span>
        <span class="admin-brand-tag">Gestion</span>
    </a>

    <h1 class="admin-auth-title">Espace gestion</h1>
    <p class="admin-auth-text">Connectez-vous pour gérer les produits, les catégories et la boutique.</p>

    <?php if (!empty($error)): ?>
        <p class="admin-flash admin-flash--error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form class="admin-auth-form" method="POST" action="<?= e(admin_url('connexion')) ?>">
        <?= csrf_field() ?>

        <label class="field">
            <span class="field-label">Email</span>
            <input
                type="email"
                name="email"
                value="<?= e($email) ?>"
                autocomplete="username"
                inputmode="email"
                required
                autofocus
            >
        </label>

        <label class="field">
            <span class="field-label">Mot de passe</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>

        <button class="btn btn--dark btn--block" type="submit">Se connecter</button>
    </form>

    <a class="admin-auth-back" href="<?= e(url('/')) ?>">
        <?= icon('arrow-left', 'icon icon--sm') ?>
        Retour à la boutique
    </a>
</div>
