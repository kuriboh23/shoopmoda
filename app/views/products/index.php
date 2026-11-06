<?php

declare(strict_types=1);

/**
 * Full catalogue — every active product.
 *
 * Search, category filter and sorting all live in the URL so a filtered
 * view can be shared or refreshed without losing state. Search itself is
 * a single icon opening the dedicated search page.
 *
 * @var array{products: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int} $products
 * @var array<int, array<string, mixed>> $categories
 * @var string                           $query
 * @var string|null                      $suggestedQuery
 * @var string                           $activeCategory
 * @var string                           $sort
 * @var array<int, string>               $wishSlugs
 */

$total       = (int) ($products['total'] ?? 0);
$currentPage = (int) ($products['page'] ?? 1);
$totalPages  = (int) ($products['total_pages'] ?? 1);
$allCount    = array_sum(array_map(
    static fn (array $category): int => (int) ($category['product_count'] ?? 0),
    $categories
));

$catalogUrl = static function (array $overrides = []) use ($query, $activeCategory, $sort): string {
    $params = [
        'q'         => $query,
        'categorie' => $activeCategory,
        'tri'       => $sort,
        'page'      => '',
    ];

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    $params = array_filter($params, static fn (string $value): bool => $value !== '');

    return url('/produits' . ($params !== [] ? '?' . http_build_query($params) : ''));
};
?>
<section class="wrap catalog-page">
    <header class="catalog-head">
        <div class="catalog-head-text">
            <p class="eyebrow">Boutique</p>
            <h1 class="catalog-title">Toutes les pièces</h1>
            <p class="catalog-meta">
                <span><?= $total ?> <?= $total === 1 ? 'pièce' : 'pièces' ?></span>
                <span aria-hidden="true">·</span>
                <span>Femme, enfant, pyjamas et robes</span>
            </p>
        </div>

        <div class="catalog-tools">
            <a class="catalog-search-btn" href="<?= e(url('/recherche')) ?>" aria-label="Rechercher une pièce" title="Rechercher">
                <?= icon('search', 'icon') ?>
            </a>

            <form class="catalog-sort" action="<?= e(url('/produits')) ?>" method="GET">
                <?= route_field('/produits') ?>
                <?php if ($query !== ''): ?>
                    <input type="hidden" name="q" value="<?= e($query) ?>">
                <?php endif; ?>
                <?php if ($activeCategory !== ''): ?>
                    <input type="hidden" name="categorie" value="<?= e($activeCategory) ?>">
                <?php endif; ?>

                <label class="visually-hidden" for="catalogSort">Trier les produits</label>
                <select id="catalogSort" name="tri" data-auto-submit>
                    <option value="nouveautes"<?= $sort === 'nouveautes' ? ' selected' : '' ?>>Nouveautés</option>
                    <option value="prix-asc"<?= $sort === 'prix-asc' ? ' selected' : '' ?>>Prix croissant</option>
                    <option value="prix-desc"<?= $sort === 'prix-desc' ? ' selected' : '' ?>>Prix décroissant</option>
                    <option value="promos"<?= $sort === 'promos' ? ' selected' : '' ?>>Promotions</option>
                </select>
            </form>
        </div>
    </header>

    <nav class="chip-row catalog-chips" aria-label="Filtrer par catégorie">
        <a class="chip<?= $activeCategory === '' ? ' is-active' : '' ?>" href="<?= e($catalogUrl(['categorie' => '', 'page' => ''])) ?>">
            Tout <span class="chip-count"><?= $allCount ?></span>
        </a>
        <?php foreach ($categories as $category): ?>
            <a
                class="chip<?= $activeCategory === $category['slug'] ? ' is-active' : '' ?>"
                href="<?= e($catalogUrl(['categorie' => (string) $category['slug'], 'page' => ''])) ?>"
            >
                <?= e($category['name']) ?>
                <span class="chip-count"><?= (int) ($category['product_count'] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if ($query !== '' && !empty($suggestedQuery)): ?>
        <div class="search-didyoumean">
            <?= icon('search', 'icon') ?>
            <p class="search-didyoumean-text">
                Aucune correspondance pour « <strong><?= e($query) ?></strong> ».
                Affichage des résultats pour <strong>« <?= e($suggestedQuery) ?> »</strong>.
            </p>
        </div>
    <?php endif; ?>

    <?php if ($query !== ''): ?>
        <div class="catalog-query">
            <span>Résultats pour <strong>« <?= e($suggestedQuery ?? $query) ?> »</strong></span>
            <a class="catalog-query-clear" href="<?= e($catalogUrl(['q' => '', 'page' => ''])) ?>">
                Effacer
                <?= icon('close', 'icon icon--sm') ?>
            </a>
        </div>
    <?php endif; ?>

    <?php if (!empty($products['products'])): ?>
        <div class="product-grid">
            <?php foreach ($products['products'] as $product): ?>
                <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($currentPage > 1): ?>
                    <a class="page-link" href="<?= e($catalogUrl(['page' => (string) ($currentPage - 1)])) ?>">
                        <?= icon('arrow-left', 'icon icon--sm') ?>
                        Précédent
                    </a>
                <?php endif; ?>

                <span class="page-info">Page <?= $currentPage ?> / <?= $totalPages ?></span>

                <?php if ($currentPage < $totalPages): ?>
                    <a class="page-link" href="<?= e($catalogUrl(['page' => (string) ($currentPage + 1)])) ?>">
                        Suivant
                        <?= icon('arrow-right', 'icon icon--sm') ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('search', 'icon') ?></span>
            <h2>Aucune pièce trouvée</h2>
            <p>Essayez un autre mot-clé ou retirez le filtre de catégorie.</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/produits')) ?>">Tout afficher</a>
        </div>
    <?php endif; ?>
</section>
