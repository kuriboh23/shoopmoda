<?php

declare(strict_types=1);

/**
 * Search page.
 *
 * The input shows live title suggestions (JSON endpoint) and the recent
 * searches kept in localStorage. Submitting lands on the catalogue with
 * the query applied; a server-rendered result list stays below as a
 * no-JavaScript fallback.
 *
 * @var string                          $query
 * @var array<int, array<string, mixed>> $results
 * @var array<int, string>              $wishSlugs
 */
?>
<section class="wrap search-page">
    <header class="category-head search-head">
        <div class="category-head-text">
            <p class="eyebrow">Recherche</p>
            <h1 class="category-title">Trouver une pièce</h1>
        </div>
        <a class="section-link" href="<?= e(url('/produits')) ?>">Tout voir</a>
    </header>

    <div class="search-hero">
        <form
            class="search-box"
            id="siteSearchForm"
            action="<?= e(url('/produits')) ?>"
            method="GET"
            role="search"
            data-suggest-url="<?= e(url('/recherche/suggestions')) ?>"
        >
            <span class="search-box-icon"><?= icon('search', 'icon') ?></span>

            <label class="visually-hidden" for="siteSearchInput">Rechercher une pièce</label>
            <input
                type="search"
                id="siteSearchInput"
                name="q"
                value="<?= e($query) ?>"
                placeholder="Robe, pyjama, ensemble…"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="searchSuggestions"
            >

            <button class="btn btn--dark" type="submit">Rechercher</button>
        </form>

        <div class="search-suggest" id="searchSuggestions" role="listbox" aria-label="Suggestions" hidden></div>

        <div class="search-history" id="searchHistory" hidden>
            <span class="search-history-label">Recherches récentes</span>
            <div class="search-history-list" data-history-list></div>
            <button type="button" class="search-history-clear" data-history-clear>Effacer</button>
        </div>

        <p class="search-tip">Essayez « pyjama », « robe » ou « satin ».</p>
    </div>

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
                <a class="btn btn--dark btn--compact" href="<?= e(url('/produits')) ?>">Voir toutes les pièces</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
