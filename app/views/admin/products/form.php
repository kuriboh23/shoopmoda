<?php

declare(strict_types=1);

/**
 * Admin product create/edit form.
 *
 * @var array<string, mixed> $product
 * @var array<string, string> $errors
 * @var string $mode
 * @var array<int, array<string, mixed>> $categories
 */

$isEdit  = $mode === 'edit';
$action  = $isEdit ? admin_url('produits/' . $product['id']) : admin_url('produits/nouveau');
$current = (int) ($product['category_id'] ?? 0);

$sizesValue = (string) ($product['sizes'] ?? '');
$decoded    = json_decode($sizesValue, true);

if (is_array($decoded)) {
    $sizesValue = implode(', ', array_map('strval', $decoded));
}

$colorsValue = (string) ($product['colors'] ?? '');
$decoded     = json_decode($colorsValue, true);

if (is_array($decoded)) {
    $lines = [];

    foreach ($decoded as $color) {
        if (is_array($color) && !empty($color['name'])) {
            $lines[] = $color['name'] . (!empty($color['hex']) ? ':' . $color['hex'] : '');
        }
    }

    $colorsValue = implode(', ', $lines);
}

$imageUrl = product_image_url((int) ($product['id'] ?? 0), $product['primary_image'] ?? null);
$error    = static fn (string $key): string => isset($errors[$key]) ? ' is-invalid' : '';
?>
<?php if ($errors !== []): ?>
    <p class="admin-flash admin-flash--error" role="alert">
        Merci de corriger les champs signalés.
    </p>
<?php endif; ?>

<form class="admin-form" method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>

    <section class="admin-card">
        <h2 class="admin-card-title">Informations</h2>

        <div class="field-grid">
            <label class="field field--wide">
                <span class="field-label">Titre *</span>
                <input
                    class="input<?= $error('title') ?>"
                    type="text"
                    name="title"
                    value="<?= e($product['title'] ?? '') ?>"
                    maxlength="255"
                    required
                >
                <?php if (isset($errors['title'])): ?><span class="field-error"><?= e($errors['title']) ?></span><?php endif; ?>
            </label>

            <label class="field">
                <span class="field-label">Catégorie</span>
                <select class="input" name="category_id">
                    <option value="0">Sans catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>"<?= $current === (int) $category['id'] ? ' selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?><span class="field-error"><?= e($errors['category_id']) ?></span><?php endif; ?>
            </label>

            <label class="field">
                <span class="field-label">Slug (URL)</span>
                <input
                    class="input"
                    type="text"
                    name="slug"
                    value="<?= e($product['slug'] ?? '') ?>"
                    placeholder="auto depuis le titre"
                    maxlength="120"
                >
                <span class="field-hint">Laissez vide pour générer automatiquement.</span>
            </label>

            <label class="field">
                <span class="field-label">Prix (DH) *</span>
                <input
                    class="input<?= $error('price') ?>"
                    type="number"
                    step="0.01"
                    min="0"
                    name="price"
                    value="<?= e($product['price'] ?? '') ?>"
                    inputmode="decimal"
                    required
                >
                <?php if (isset($errors['price'])): ?><span class="field-error"><?= e($errors['price']) ?></span><?php endif; ?>
            </label>

            <label class="field">
                <span class="field-label">Prix promo (DH)</span>
                <input
                    class="input<?= $error('sale_price') ?>"
                    type="number"
                    step="0.01"
                    min="0"
                    name="sale_price"
                    value="<?= e($product['sale_price'] ?? '') ?>"
                    inputmode="decimal"
                >
                <span class="field-hint">Vide = pas de promotion.</span>
                <?php if (isset($errors['sale_price'])): ?><span class="field-error"><?= e($errors['sale_price']) ?></span><?php endif; ?>
            </label>

            <label class="field">
                <span class="field-label">Stock *</span>
                <input
                    class="input<?= $error('stock') ?>"
                    type="number"
                    step="1"
                    min="0"
                    name="stock"
                    value="<?= e($product['stock'] ?? '0') ?>"
                    inputmode="numeric"
                    required
                >
                <?php if (isset($errors['stock'])): ?><span class="field-error"><?= e($errors['stock']) ?></span><?php endif; ?>
            </label>

            <label class="field field--wide">
                <span class="field-label">Description</span>
                <textarea class="input" name="description" rows="4" maxlength="2000"><?= e($product['description'] ?? '') ?></textarea>
            </label>
        </div>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Options</h2>

        <div class="field-grid">
            <label class="field">
                <span class="field-label">Tailles</span>
                <input
                    class="input"
                    type="text"
                    name="sizes"
                    value="<?= e($sizesValue) ?>"
                    placeholder="S, M, L, XL"
                >
                <span class="field-hint">Séparées par des virgules.</span>
            </label>

            <label class="field">
                <span class="field-label">Couleurs</span>
                <input
                    class="input"
                    type="text"
                    name="colors"
                    value="<?= e($colorsValue) ?>"
                    placeholder="Noir:#1B1A18, Beige:#C2A691"
                >
                <span class="field-hint">Nom:#hex, séparées par des virgules.</span>
            </label>
        </div>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Visibilité</h2>

        <div class="admin-checks">
            <label class="admin-check">
                <input type="checkbox" name="is_active" value="1"<?= !empty($product['is_active']) ? ' checked' : '' ?>>
                <span class="admin-check-text">
                    <strong>En ligne</strong>
                    <small>Visible dans la boutique</small>
                </span>
            </label>

            <label class="admin-check">
                <input type="checkbox" name="is_featured" value="1"<?= !empty($product['is_featured']) ? ' checked' : '' ?>>
                <span class="admin-check-text">
                    <strong>Coup de cœur</strong>
                    <small>Badge sur la fiche et priorité d’affichage</small>
                </span>
            </label>

            <label class="admin-check">
                <input type="checkbox" name="is_flash" value="1"<?= !empty($product['is_flash']) ? ' checked' : '' ?>>
                <span class="admin-check-text">
                    <strong>Promo flash</strong>
                    <small>Badge « Promo flash » et compte à rebours</small>
                </span>
            </label>
        </div>
    </section>

    <section class="admin-card">
        <h2 class="admin-card-title">Image</h2>

        <div class="admin-image-field">
            <img
                class="admin-image-preview"
                data-image-preview
                src="<?= e($imageUrl) ?>"
                alt="Aperçu du produit"
                width="120"
                height="150"
            >

            <div class="admin-image-input">
                <label class="field">
                    <span class="field-label">Photo du produit</span>
                    <input class="input" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-image-input>
                </label>
                <p class="field-hint">JPG, PNG ou WebP — 3 Mo maximum. L’image remplace l’image principale.</p>
                <?php if (isset($errors['image'])): ?><span class="field-error"><?= e($errors['image']) ?></span><?php endif; ?>
            </div>
        </div>
    </section>

    <div class="admin-form-actions">
        <a class="btn btn--ghost" href="<?= e(admin_url('produits')) ?>">Annuler</a>
        <button class="btn btn--dark" type="submit">
            <?= icon('check-circle', 'icon icon--sm') ?>
            <?= $isEdit ? 'Enregistrer' : 'Créer le produit' ?>
        </button>
    </div>
</form>
