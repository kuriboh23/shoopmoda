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
            <p class="page-meta">
                <span><?= count($flashSales) ?> <?= count($flashSales) === 1 ? 'pièce en promotion' : 'pièces en promotion' ?></span>
                <span aria-hidden="true">·</span>
                <span>Livraison partout au Maroc</span>
            </p>
        </div>

        <?php if ($showCountdown): ?>
            <?php partial('partials/countdown', ['endAt' => $nextEndAt, 'label' => 'L’offre se termine dans']); ?>
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
            <a class="btn btn--dark btn--compact" href="<?= e(url('/produits')) ?>">Voir toutes les pièces</a>
        </div>
    <?php endif; ?>
</section>
