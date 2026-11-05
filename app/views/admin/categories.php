<?php

declare(strict_types=1);

/**
 * Admin categories.
 *
 * @var array<int, array<string, mixed>> $categories
 */
?>
<section class="admin-card">
    <h2 class="admin-card-title">Ajouter une catégorie</h2>

    <form class="admin-inline-form" method="POST" action="<?= e(admin_url('categories')) ?>">
        <?= csrf_field() ?>

        <label class="field">
            <span class="field-label">Nom</span>
            <input class="input" type="text" name="name" maxlength="100" required placeholder="Ex. Robes">
        </label>

        <label class="field field--small">
            <span class="field-label">Ordre</span>
            <input class="input" type="number" name="sort_order" value="0" step="1">
        </label>

        <button class="btn btn--dark" type="submit">
            <?= icon('plus', 'icon icon--sm') ?>
            Ajouter
        </button>
    </form>
</section>

<section class="admin-section">
    <div class="admin-section-head">
        <h2 class="admin-section-title"><?= count($categories) ?> catégories</h2>
    </div>

    <?php if ($categories === []): ?>
        <p class="admin-empty">Aucune catégorie.</p>
    <?php else: ?>
        <div class="admin-list">
            <?php foreach ($categories as $category): ?>
                <article class="admin-row admin-row--category">
                    <span class="admin-cat-icon"><?= icon('tag', 'icon icon--sm') ?></span>

                    <div class="admin-row-main">
                        <p class="admin-row-title"><?= e($category['name']) ?></p>
                        <p class="admin-row-meta">
                            /categorie/<?= e($category['slug']) ?>
                            · <?= (int) ($category['product_count'] ?? 0) ?> produit(s)
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="<?= e(admin_url('categories/' . $category['id'] . '/supprimer')) ?>"
                        data-confirm="Supprimer la catégorie « <?= e($category['name']) ?> » ? Les produits seront conservés sans catégorie."
                    >
                        <?= csrf_field() ?>
                        <button
                            type="submit"
                            class="admin-icon-btn admin-icon-btn--danger"
                            aria-label="Supprimer la catégorie <?= e($category['name']) ?>"
                            title="Supprimer"
                        ><?= icon('trash', 'icon icon--sm') ?></button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
