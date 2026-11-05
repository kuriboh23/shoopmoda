<?php

declare(strict_types=1);

/**
 * Catégorie : grille de produits avec pagination.
 *
 * @var array $category
 * @var array $products     ['products'=>..., 'total'=>..., 'page'=>..., 'per_page'=>..., 'total_pages'=>...]
 * @var int   $cartCount
 * @var int   $wishCount
 */

$categoryName = $category !== null
    ? htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')
    : 'Catégorie';
$categorySlug = $category !== null ? $category['slug'] : '';
$title        = $categoryName . ' — Shoop Moda Style';
$productCount = count($products['products'] ?? []);
?>
<section class="wrap category-page">
    <header class="section-head section-head--large">
        <div>
            <p class="eyebrow">Catégorie</p>
            <h1 class="section-title"><?= e($categoryName) ?></h1>
            <p class="subtitle"><?= e($productCount) ?> <?= $productCount === 1 ? 'article' : 'articles' ?> disponibles</p>
        </div>

        <?php if ($category !== null && !empty($category['icon'])): ?>
            <span class="category-icon" aria-hidden="true"><?= icon($category['icon'], 'icon') ?></span>
        <?php endif; ?>
    </header>

    <?php if (!empty($products['products'])): ?>
        <div class="product-grid">
            <?php foreach ($products['products'] as $product): ?>
                <article class="product-card">
                    <a class="product-link" href="<?= e(url('/produit/' . $product['slug'])) ?>">
                        <div class="product-media">
                            <?php $img = $product['primary_image'] ?? null; ?>
                            <img
                                src="<?= e(product_image_url((int) $product['id'], $img)) ?>"
                                alt="<?= e($product['title']) ?>"
                                loading="lazy"
                                width="400"
                                height="500"
                            >
                            <?php if (!empty($product['is_flash'])): ?>
                                <span class="product-badge product-badge--flash">Flash</span>
                            <?php endif; ?>
                            <?php if (!empty($product['is_featured'])): ?>
                                <span class="product-badge product-badge--featured">Populaire</span>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <h2 class="product-title"><?= e($product['title']) ?></h2>

                            <?php
                            $price = (float) ($product['sale_price'] ?? $product['price'] ?? 0);
                            $oldPrice = (float) ($product['price'] ?? 0);
                            ?>
                            <div class="product-price">
                                <?php if (!empty($product['sale_price'])): ?>
                                    <span class="price current"><?= e(format_price($price)) ?></span>
                                    <span class="price old"><?= e(format_price($oldPrice)) ?></span>
                                <?php else: ?>
                                    <span class="price current"><?= e(format_price($price)) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($product['rating'])): ?>
                                <div class="product-rating" aria-label="Note : <?= e($product['rating']) ?> / 5">
                                    <?= icon('star', 'icon icon--sm') ?>
                                    <span><?= e($product['rating']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($products['total_pages'] > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($products['page'] > 1): ?>
                    <a class="page-link" href="<?= e(url('/categorie/' . $categorySlug . '?page=' . ($products['page'] - 1))) ?>">
                        <?= icon('arrow-left', 'icon icon--sm') ?>
                        Précédent
                    </a>
                <?php endif; ?>

                <span class="page-info">Page <?= (int) $products['page'] ?> / <?= (int) $products['total_pages'] ?></span>

                <?php if ($products['page'] < $products['total_pages']): ?>
                    <a class="page-link" href="<?= e(url('/categorie/' . $categorySlug . '?page=' . ($products['page'] + 1))) ?>">
                        Suivant
                        <?= icon('arrow-right', 'icon icon--sm') ?>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon"><?= icon('search', 'icon') ?></span>
            <h2>Aucun article</h2>
            <p>Cette catégorie est vide pour le moment. Revenez bientôt !</p>
            <a class="btn btn--dark btn--compact" href="<?= e(url('/')) ?>">Retour à l'accueil</a>
        </div>
    <?php endif; ?>
</section>

<style>
.category-page {
    padding-top: 24px;
    padding-bottom: 48px;
}

.category-page .section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--light);
}

.category-page .section-head--large .section-title {
    font-size: 22px;
    margin-top: 4px;
}

.category-page .subtitle {
    margin-top: 6px;
    color: var(--gray-text);
    font-size: 14px;
}

.category-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    background: var(--primary-soft);
    border-radius: 12px;
    color: var(--primary);
    flex-shrink: 0;
}

.category-icon .icon {
    width: 24px;
    height: 24px;
    fill: var(--primary);
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.product-card {
    background: var(--white);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.product-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.product-media {
    position: relative;
    aspect-ratio: 4 / 5;
    background: #f5f5f5;
    overflow: hidden;
}

.product-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-media img {
    transform: scale(1.03);
}

.product-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.product-badge--flash {
    background: var(--whatsapp);
    color: var(--dark);
}

.product-badge--featured {
    background: var(--dark);
    color: var(--white);
}

.product-info {
    padding: 12px 14px 14px;
}

.product-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.35;
    margin-bottom: 8px;
    color: var(--dark);
}

.product-price {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 4px;
}

.price {
    font-size: 15px;
    font-weight: 700;
}

.price.current {
    color: var(--primary);
}

.price.old {
    color: var(--gray-text);
    text-decoration: line-through;
    font-size: 13px;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--star);
    font-size: 12px;
}

.product-rating .icon {
    width: 14px;
    height: 14px;
    fill: var(--star);
}

.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--light);
}

.page-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: var(--white);
    border: 1px solid var(--light);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    color: var(--dark);
    transition: all 0.2s ease;
}

.page-link:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.page-link .icon {
    width: 16px;
    height: 16px;
}

.page-info {
    color: var(--gray-text);
    font-size: 14px;
}

.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: var(--gray-text);
}

.empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    background: var(--light);
    border-radius: 50%;
    margin-bottom: 16px;
    color: var(--gray);
}

.empty-icon .icon {
    width: 28px;
    height: 28px;
}

.empty-state h2 {
    font-size: 18px;
    color: var(--dark);
    margin-bottom: 8px;
}

.empty-state p {
    font-size: 14px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .product-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}
</style>
