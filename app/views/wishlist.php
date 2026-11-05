<?php

declare(strict_types=1);

/**
 * Wishlist page.
 *
 * @var array<int, array<string, mixed>> $products
 * @var array<int, string>              $wishSlugs
 */
?>
<section class="wrap category-page">
    <header class="category-head">
        <div class="category-head-text">
            <p class="eyebrow">Ma sélection</p>
            <h1 class="category-title">Mes favoris</h1>
            <p class="category-subtitle">
                <?= count($products) ?> <?= count($products) === 1 ? 'pièce sauvegardée' : 'pièces sauvegardées' ?>
                · Retrouvez vos coups de cœur à tout moment
            </p>
        </div>

        <span class="category-icon" aria-hidden="true"><?= icon('heart') ?></span>
    </header>

    <?php if (!empty($products)): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('heart', 'icon') ?></span>
            <h2>Aucun favori pour le moment</h2>
            <p>Cliquez sur le cœur d'une pièce pour la retrouver ici.</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Parcourir la collection</a>
        </div>
    <?php endif; ?>
</section>
