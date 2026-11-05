<?php

declare(strict_types=1);

/**
 * Admin product list.
 *
 * @var array<int, array<string, mixed>> $products
 * @var string $search
 */

$count = count($products);
?>
<div class="admin-toolbar">
    <form class="admin-search" method="GET" action="<?= e(admin_url('produits')) ?>" role="search">
        <span class="admin-search-icon"><?= icon('search', 'icon icon--sm') ?></span>
        <label class="visually-hidden" for="adminProductSearch">Rechercher un produit</label>
        <input
            id="adminProductSearch"
            type="search"
            name="q"
            value="<?= e($search) ?>"
            placeholder="Titre ou référence…"
            autocomplete="off"
        >
        <button class="btn btn--dark btn--compact" type="submit">Chercher</button>
    </form>

    <a class="btn btn--primary btn--compact admin-toolbar-new" href="<?= e(admin_url('produits/nouveau')) ?>">
        <?= icon('plus', 'icon icon--sm') ?>
        Nouveau
    </a>
</div>

<p class="admin-result-count">
    <?= $count ?> <?= $count === 1 ? 'produit' : 'produits' ?>
    <?php if ($search !== ''): ?>
        pour « <?= e($search) ?> »
    <?php endif; ?>
</p>

<?php if ($products === []): ?>
    <div class="admin-empty-state">
        <?= icon('box', 'icon') ?>
        <p>Aucun produit trouvé.</p>
        <a class="btn btn--dark btn--compact" href="<?= e(admin_url('produits/nouveau')) ?>">Ajouter un produit</a>
    </div>
<?php else: ?>
    <div class="admin-list admin-list--products">
        <?php foreach ($products as $product): ?>
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

                    <div class="admin-row-flags">
                        <span class="admin-status<?= !empty($product['is_active']) ? ' is-on' : '' ?>">
                            <?= !empty($product['is_active']) ? 'En ligne' : 'Brouillon' ?>
                        </span>
                        <?php if (!empty($product['sale_price'])): ?>
                            <span class="admin-pill admin-pill--sale">Promo</span>
                        <?php endif; ?>
                        <?php if (!empty($product['is_flash'])): ?>
                            <span class="admin-pill">Flash</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="admin-row-actions">
                    <a
                        class="admin-icon-btn"
                        href="<?= e(admin_url('produits/' . $product['id'])) ?>"
                        aria-label="Modifier <?= e($product['title']) ?>"
                        title="Modifier"
                    ><?= icon('edit', 'icon icon--sm') ?></a>

                    <a
                        class="admin-icon-btn"
                        href="<?= e(url('/produit/' . $product['slug'])) ?>"
                        target="_blank"
                        rel="noopener"
                        aria-label="Voir la fiche"
                        title="Voir la fiche"
                    ><?= icon('eye', 'icon icon--sm') ?></a>

                    <form
                        method="POST"
                        action="<?= e(admin_url('produits/' . $product['id'] . '/supprimer')) ?>"
                        data-confirm="Supprimer « <?= e($product['title']) ?> » ? Cette action est définitive."
                    >
                        <?= csrf_field() ?>
                        <button
                            type="submit"
                            class="admin-icon-btn admin-icon-btn--danger"
                            aria-label="Supprimer <?= e($product['title']) ?>"
                            title="Supprimer"
                        ><?= icon('trash', 'icon icon--sm') ?></button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
