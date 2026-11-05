<?php

declare(strict_types=1);

/**
 * Category page.
 *
 * @var array<string, mixed> $category
 * @var array<string, mixed> $products  ['products'=>…, 'total'=>…, 'page'=>…, 'per_page'=>…, 'total_pages'=>…]
 * @var array<int, string>   $wishSlugs
 */

$categorySlug = (string) ($category['slug'] ?? '');
$total        = (int) ($products['total'] ?? 0);
$currentPage  = (int) ($products['page'] ?? 1);
$totalPages   = (int) ($products['total_pages'] ?? 1);
?>
<section class="wrap category-page">
    <header class="category-head">
        <div class="category-head-text">
            <p class="eyebrow">Collection</p>
            <h1 class="category-title"><?= e($category['name'] ?? 'Catégorie') ?></h1>
            <p class="category-subtitle">
                <?= $total ?> <?= $total === 1 ? 'pièce disponible' : 'pièces disponibles' ?>
                · Livraison 24-48h partout au Maroc
            </p>
        </div>

        <?php if (!empty($category['icon'])): ?>
            <span class="category-icon" aria-hidden="true"><?= icon((string) $category['icon']) ?></span>
        <?php endif; ?>
    </header>

    <?php if (!empty($products['products'])): ?>
        <div class="product-grid">
            <?php foreach ($products['products'] as $product): ?>
                <?php partial('partials/product-card', ['product' => $product, 'wishSlugs' => $wishSlugs ?? []]); ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($currentPage > 1): ?>
                    <a class="page-link" href="<?= e(url('/categorie/' . $categorySlug . '?page=' . ($currentPage - 1))) ?>">
                        <?= icon('arrow-left', 'icon icon--sm') ?>
                        Précédent
                    </a>
                <?php endif; ?>

                <span class="page-info">Page <?= $currentPage ?> / <?= $totalPages ?></span>

                <?php if ($currentPage < $totalPages): ?>
                    <a class="page-link" href="<?= e(url('/categorie/' . $categorySlug . '?page=' . ($currentPage + 1))) ?>">
                        Suivant
                        <?= icon('arrow-right', 'icon icon--sm') ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('search', 'icon') ?></span>
            <h2>Aucune pièce pour le moment</h2>
            <p>Cette collection se remplit bientôt. Découvrez nos autres modèles en attendant.</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Retour à l'accueil</a>
        </div>
    <?php endif; ?>
</section>
