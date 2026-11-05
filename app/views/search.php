<?php

declare(strict_types=1);

/**
 * Search page.
 *
 * @var string                          $query
 * @var array<int, array<string, mixed>> $results
 * @var array<int, string>              $wishSlugs
 */
?>
<section class="wrap category-page">
    <header class="category-head">
        <div class="category-head-text">
            <p class="eyebrow">Recherche</p>
            <h1 class="category-title">Trouver une pièce</h1>
            <p class="category-subtitle">Pyjamas, robes, ensembles, mode enfant…</p>
        </div>
    </header>

    <form class="search-form" action="<?= e(url('/recherche')) ?>" method="GET" role="search">
        <label class="visually-hidden" for="searchInput">Rechercher un produit</label>
        <div class="search-field">
            <?= icon('search', 'icon icon--sm') ?>
            <input
                type="search"
                id="searchInput"
                name="q"
                value="<?= e($query) ?>"
                placeholder="Rechercher un produit…"
                autocomplete="off"
            >
        </div>
        <button class="btn btn--dark" type="submit">Rechercher</button>
    </form>

    <?php if ($query !== ''): ?>
        <p class="search-count">
            <?= count($results) ?> <?= count($results) === 1 ? 'résultat' : 'résultats' ?>
            pour « <?= e($query) ?> »
        </p>

        <?php if (!empty($results)): ?>
            <div class="product-grid">
                <?php foreach ($results as $product): ?>
                    <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon"><?= icon('search', 'icon') ?></span>
                <h2>Aucun résultat</h2>
                <p>Essayez un autre mot-clé ou parcourez nos collections.</p>
                <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Retour à l'accueil</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
