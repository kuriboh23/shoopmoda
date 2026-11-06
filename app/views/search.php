<?php

declare(strict_types=1);

/**
 * Search page.
 *
 * The input shows live title suggestions (JSON endpoint) and the recent
 * searches kept in localStorage. Submitting lands on the catalogue with
 * the query applied; a server-rendered result list stays below as a
 * no-JavaScript fallback. A misspelt query is corrected server-side to
 * the closest word found in the catalogue.
 *
 * @var string                          $query
 * @var string|null                     $suggested
 * @var array<int, array<string, mixed>> $results
 * @var array<int, array<string, mixed>> $popular
 * @var array<int, string>              $wishSlugs
 */

$popularTerms = ['pyjama', 'robe', 'ensemble', 'satin', 'femme', 'enfant'];
$resultCount  = count($results);
$shownQuery   = $suggested ?? $query;
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
            <?= route_field('/produits') ?>
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

        <?php if ($query === ''): ?>
            <div class="search-history" id="searchHistory" hidden>
                <span class="search-history-label">Recherches récentes</span>
                <div class="search-history-list" data-history-list></div>
                <button type="button" class="search-history-clear" data-history-clear>Effacer</button>
            </div>

            <div class="search-popular">
                <h2 class="search-popular-title">Recherches populaires</h2>
                <div class="search-chips">
                    <?php foreach ($popularTerms as $term): ?>
                        <a class="search-chip" href="<?= e(url('/produits?q=' . rawurlencode($term))) ?>">
                            <?= icon('search', 'icon') ?>
                            <?= e(ucfirst($term)) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($query !== ''): ?>
        <?php if ($suggested !== null && $suggested !== ''): ?>
            <div class="search-didyoumean">
                <?= icon('search', 'icon') ?>
                <p class="search-didyoumean-text">
                    Aucune correspondance pour « <strong><?= e($query) ?></strong> ».
                    Affichage des résultats pour
                    <a href="<?= e(url('/recherche?q=' . rawurlencode($suggested))) ?>">« <?= e($suggested) ?> »</a>.
                </p>
            </div>
        <?php endif; ?>

        <?php if ($resultCount > 0): ?>
            <p class="search-count">
                <?= $resultCount ?> <?= $resultCount === 1 ? 'résultat' : 'résultats' ?>
                pour « <?= e($shownQuery) ?> »
            </p>

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
    <?php elseif (!empty($popular)): ?>
        <section class="search-best" aria-labelledby="searchBestTitle">
            <h2 class="search-popular-title" id="searchBestTitle">Nos pièces les plus demandées</h2>

            <div class="product-grid">
                <?php foreach ($popular as $product): ?>
                    <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>
