<?php

declare(strict_types=1);

/**
 * 404 page.
 *
 * @var string $title
 */
?>
<section class="wrap state-page">
    <span class="state-404" aria-hidden="true">404</span>
    <h1 class="state-title">Oups ! Page introuvable</h1>
    <p class="state-text">
        Cette page n'existe pas ou a été déplacée.
        Retournez à l'accueil pour découvrir nos nouveautés.
    </p>

    <div class="state-actions">
        <a class="btn btn--dark btn--block" href="<?= e(url('/')) ?>">
            <?= icon('arrow-left', 'icon icon--sm') ?>
            Retour à l'accueil
        </a>
        <a class="btn btn--wa btn--block" href="<?= e(whatsapp_url('Bonjour, je souhaite voir vos produits disponibles.')) ?>"
           target="_blank" rel="noopener">
            <?= icon('whatsapp', 'icon icon--sm') ?>
            Voir par WhatsApp
        </a>
    </div>
</section>

<style>
.state-page {
    text-align: center;
    padding: 80px 20px;
    max-width: 480px;
    margin-inline: auto;
}

.state-404 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: var(--light);
    border-radius: 50%;
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 16px;
}

.state-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--dark);
    margin: 0 0 12px;
}

.state-text {
    color: var(--gray-text);
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 24px;
}

.state-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.state-actions .btn--wa {
    height: 44px;
}

@media (min-width: 768px) {
    .state-actions {
        flex-direction: row;
        justify-content: center;
    }
}
</style>
