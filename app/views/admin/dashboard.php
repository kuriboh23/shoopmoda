<?php

declare(strict_types=1);

/**
 * Admin dashboard.
 *
 * @var array<string, mixed> $stats
 * @var array<int, array<string, mixed>> $recent
 * @var array<int, array<string, mixed>> $categories
 */

$cards = [
    ['label' => 'Produits', 'value' => (int) ($stats['products'] ?? 0), 'icon' => 'box', 'hint' => (int) ($stats['active'] ?? 0) . ' en ligne'],
    ['label' => 'Ruptures', 'value' => (int) ($stats['out_of_stock'] ?? 0), 'icon' => 'alert-circle', 'hint' => 'stock à zéro'],
    ['label' => 'Promos', 'value' => (int) ($stats['on_sale'] ?? 0), 'icon' => 'tag', 'hint' => (int) ($stats['flash'] ?? 0) . ' flash'],
    ['label' => 'Valeur stock', 'value' => format_price((float) ($stats['stock_value'] ?? 0)), 'icon' => 'box', 'hint' => 'au prix de vente'],
];
?>
<div class="admin-stats">
    <?php foreach ($cards as $card): ?>
        <article class="admin-stat">
            <span class="admin-stat-icon"><?= icon($card['icon'], 'icon icon--sm') ?></span>
            <p class="admin-stat-label"><?= e($card['label']) ?></p>
            <p class="admin-stat-value"><?= e((string) $card['value']) ?></p>
            <p class="admin-stat-hint"><?= e($card['hint']) ?></p>
        </article>
    <?php endforeach; ?>
</div>

<section class="admin-section">
    <div class="admin-section-head">
        <h2 class="admin-section-title">Actions rapides</h2>
    </div>

    <div class="admin-quick">
        <a class="admin-quick-link" href="<?= e(admin_url('produits/nouveau')) ?>">
            <?= icon('plus', 'icon') ?>
            <span>Ajouter un produit</span>
        </a>
        <a class="admin-quick-link" href="<?= e(admin_url('categories')) ?>">
            <?= icon('tag', 'icon') ?>
            <span>Gérer les catégories</span>
        </a>
        <a class="admin-quick-link" href="<?= e(admin_url('produits')) ?>">
            <?= icon('box', 'icon') ?>
            <span>Voir le catalogue</span>
        </a>
    </div>
</section>

<section class="admin-section">
    <div class="admin-section-head">
        <h2 class="admin-section-title">Derniers produits</h2>
        <a class="admin-link" href="<?= e(admin_url('produits')) ?>">Tout voir</a>
    </div>

    <?php if ($recent === []): ?>
        <p class="admin-empty">Aucun produit pour le moment.</p>
    <?php else: ?>
        <div class="admin-list">
            <?php foreach ($recent as $product): ?>
                <article class="admin-row">
                    <img
                        class="admin-thumb"
                        src="<?= e(product_image_url((int) $product['id'], $product['primary_image'] ?? null)) ?>"
                        alt=""
                        width="52"
                        height="65"
                        loading="lazy"
                    >

                    <div class="admin-row-main">
                        <a class="admin-row-title" href="<?= e(admin_url('produits/' . $product['id'])) ?>">
                            <?= e($product['title']) ?>
                        </a>
                        <p class="admin-row-meta">
                            <?= e($product['category_name'] ?? 'Sans catégorie') ?>
                            · <?= e(format_price((float) ($product['sale_price'] ?: $product['price']))) ?>
                            · Stock <?= (int) $product['stock'] ?>
                        </p>
                    </div>

                    <span class="admin-status<?= !empty($product['is_active']) ? ' is-on' : '' ?>">
                        <?= !empty($product['is_active']) ? 'En ligne' : 'Brouillon' ?>
                    </span>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="admin-section">
    <div class="admin-section-head">
        <h2 class="admin-section-title">Catégories</h2>
        <a class="admin-link" href="<?= e(admin_url('categories')) ?>">Gérer</a>
    </div>

    <div class="admin-chips">
        <?php foreach ($categories as $category): ?>
            <span class="admin-chip">
                <?= e($category['name']) ?>
                <span><?= (int) ($category['product_count'] ?? 0) ?></span>
            </span>
        <?php endforeach; ?>
    </div>
</section>
