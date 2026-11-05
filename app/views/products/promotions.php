<?php

declare(strict_types=1);

/**
 * Promotions / flash sales.
 *
 * @var array<int, array<string, mixed>> $flashSales
 * @var string|null                     $nextEndAt
 * @var array<int, string>              $wishSlugs
 */

$showCountdown = $nextEndAt !== null && $flashSales !== [];
?>
<section class="wrap category-page">
    <header class="category-head">
        <div class="category-head-text">
            <p class="eyebrow">Offres limitées</p>
            <h1 class="category-title">Promotions</h1>
            <p class="category-subtitle">
                <?= count($flashSales) ?> <?= count($flashSales) === 1 ? 'pièce en promotion' : 'pièces en promotion' ?>
                · <?= icon('truck', 'icon icon--sm') ?> Livraison partout au Maroc
            </p>
        </div>

        <?php if ($showCountdown): ?>
            <div class="countdown-card" role="timer" aria-live="polite">
                <span class="countdown-label">L'offre se termine dans</span>
                <span class="countdown-timer" id="countdownTimer" data-end="<?= e($nextEndAt) ?>">-- : -- : --</span>
            </div>
        <?php endif; ?>
    </header>

    <?php if ($flashSales !== []): ?>
        <div class="product-grid">
            <?php foreach ($flashSales as $product): ?>
                <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('tag', 'icon') ?></span>
            <h2>Aucune promotion en cours</h2>
            <p>De nouvelles offres arrivent chaque semaine. Découvrez la collection en attendant.</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Voir la collection</a>
        </div>
    <?php endif; ?>
</section>
