<?php

declare(strict_types=1);

/**
 * 500 page — shown only when APP_DEBUG is false.
 *
 * @var string $title
 */
?>
<section class="wrap state-page">
    <p class="state-code">500</p>
    <h1 class="state-title">Une erreur est survenue</h1>
    <p class="state-text">
        Nous rencontrons un problème technique momentané. Merci de réessayer
        dans quelques instants.
    </p>

    <div class="state-actions">
        <a class="btn btn--dark" href="<?= e(url('/')) ?>">Retour à l’accueil</a>
        <a class="btn btn--ghost" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener">
            <?= icon('whatsapp', 'icon icon--sm') ?>
            WhatsApp
        </a>
    </div>
</section>
