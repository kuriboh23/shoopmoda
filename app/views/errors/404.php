<?php

declare(strict_types=1);

/**
 * 404 page.
 *
 * @var string $title
 */
?>
<section class="wrap state-page">
    <span class="state-code" aria-hidden="true">404</span>
    <h1 class="state-title">Oups ! Page introuvable</h1>
    <p class="state-text">
        Cette page n'existe pas ou a été déplacée.
        Retournez à l'accueil pour découvrir nos nouveautés.
    </p>

    <div class="state-actions">
        <a class="btn btn--dark" href="<?= e(url('/')) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour à l'accueil
        </a>
        <a class="btn btn--wa" href="<?= e(whatsapp_url('Bonjour, je souhaite voir vos produits disponibles.')) ?>"
           target="_blank" rel="noopener">
            <?= icon('whatsapp', 'icon icon--sm') ?>
            Voir par WhatsApp
        </a>
    </div>
</section>
