<?php

declare(strict_types=1);

/**
 * Admin categories.
 *
 * Categories feed the header menu, the drawer, the home chips, the catalogue
 * filters and the footer links, so one edit here updates the whole shop.
 *
 * @var array<int, array<string, mixed>> $categories
 * @var array<string, string>            $icons
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

        <label class="field">
            <span class="field-label">Icône</span>
            <select class="input" name="icon">
                <option value="">Aucune</option>
                <?php foreach ($icons as $value => $label): ?>
                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="field field--small">
            <span class="field-label">Ordre</span>
            <input class="input" type="number" name="sort_order" value="0" step="1">
        </label>

        <label class="admin-check">
            <input type="checkbox" name="is_active" value="1" checked>
            <span>Visible</span>
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
                    <span class="admin-cat-icon"><?= icon((string) ($category['icon'] ?: 'tag'), 'icon icon--sm') ?></span>

                    <div class="admin-row-main">
                        <p class="admin-row-title">
                            <?= e($category['name']) ?>
                            <?php if (empty($category['is_active'])): ?>
                                <span class="admin-tag">Masquée</span>
                            <?php endif; ?>
                        </p>
                        <p class="admin-row-meta">
                            /categorie/<?= e($category['slug']) ?>
                            · <?= (int) ($category['product_count'] ?? 0) ?> produit(s)
                            · ordre <?= (int) ($category['sort_order'] ?? 0) ?>
                        </p>
                    </div>

                    <div class="admin-row-actions">
                        <a
                            class="admin-icon-btn"
                            href="<?= e(admin_url('categories/' . $category['id'] . '/modifier')) ?>"
                            aria-label="Modifier la catégorie <?= e($category['name']) ?>"
                            title="Modifier"
                        ><?= icon('edit', 'icon icon--sm') ?></a>

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
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
