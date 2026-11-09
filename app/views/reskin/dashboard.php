<?php

declare(strict_types=1);

/**
 * RESKIN dashboard.
 *
 * One panel per page section. Panels are generated from app/reskin/schema.php
 * and contain both style controls (CSS variables/body attributes) and content
 * fields (saved by SiteContent). Everything the browser needs is carried by
 * data-* attributes, because the storefront CSP forbids inline scripts.
 *
 * @var array<string, mixed>  $schema
 * @var array<string, string> $values
 * @var array<string, string> $content
 * @var array<string, array{values: array<string, string>, saved_at: int}> $library
 * @var array<int, array{key: string, label: string, description: string, values: array<string, string>}> $presets
 * @var array{name: string, updated_at: int} $current
 * @var bool   $applied
 * @var string $previewUrl
 * @var string $exportUrl
 * @var string $saveUrl
 * @var string $resetUrl
 * @var string $applyUrl
 * @var string $disableUrl
 * @var string $themeSaveUrl
 * @var string $themeLoadUrl
 * @var string $themeDeleteUrl
 * @var string $shopUrl
 * @var string $snapshotUrl
 * @var string $logoutUrl
 * @var string $siteUrl
 * @var string $csrf
 */

$categories = (array) ($schema['categories'] ?? []);
$firstKey   = (string) (array_key_first($categories) ?? '');
$storeName  = store_name();

/**
 * Render one style or content control.
 *
 * @param array<string, mixed>  $control
 * @param array<string, string> $values
 * @param array<string, string> $content
 */
$renderControl = static function (string $key, array $control, array $values, array $content): void {
    $type    = (string) ($control['type'] ?? '');
    $label   = (string) ($control['label'] ?? $key);
    $help    = (string) ($control['help'] ?? '');
    $id      = 'rs-' . $key;
    $unit    = (string) ($control['unit'] ?? '');
    $isField = isset($control['content']);

    if ($isField) {
        $path  = (string) $control['content'];
        $value = (string) ($content[$path] ?? '');
        $common = 'data-rs-content="' . e($path) . '" data-rs-content-type="' . e($type) . '"';
        ?>
        <div class="rs-control" data-rs-control="<?= e($key) ?>">
            <div class="rs-control-head">
                <label class="rs-control-label" for="<?= e($id) ?>"><?= e($label) ?></label>
                <span class="rs-content-badge">Contenu</span>
            </div>

            <?php if ($type === 'textarea'): ?>
                <textarea
                    id="<?= e($id) ?>"
                    class="rs-textarea"
                    rows="5"
                    spellcheck="false"
                    <?= $common ?>
                ><?= e($value) ?></textarea>
            <?php elseif ($type === 'select'): ?>
                <?php
                $options = (array) ($control['options'] ?? []);

                // A saved link may point outside the short page/category list
                // (for example a product chosen before the list was trimmed).
                // Keep it selectable, but flag values that look stale.
                if ($value !== '' && !array_key_exists($value, $options)) {
                    $options[$value] = str_starts_with($value, '/produit/')
                        ? 'Lien produit actuel (non listé) : ' . $value
                        : '⚠ Lien actuel introuvable : ' . $value;
                }
                ?>
                <div class="rs-select-wrap">
                    <select id="<?= e($id) ?>" class="rs-select" <?= $common ?>>
                        <?php foreach ($options as $optionValue => $optionLabel): ?>
                            <option value="<?= e((string) $optionValue) ?>"<?= (string) $optionValue === $value ? ' selected' : '' ?>>
                                <?= e((string) $optionLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?= icon('chevron-right', 'icon icon--sm rs-select-caret') ?>
                </div>
            <?php else: ?>
                <input
                    type="text"
                    id="<?= e($id) ?>"
                    class="rs-input"
                    value="<?= e($value) ?>"
                    <?= isset($control['max']) && $type === 'text' ? 'maxlength="' . (int) $control['max'] . '"' : '' ?>
                    spellcheck="false"
                    autocomplete="off"
                    <?= $common ?>
                >
            <?php endif; ?>

            <?php if ($help !== ''): ?>
                <p class="rs-control-help"><?= e($help) ?></p>
            <?php endif; ?>
        </div>
        <?php

        return;
    }

    $value = (string) ($values[$key] ?? $control['default'] ?? '');
    $var   = (string) ($control['var'] ?? '');
    $attr  = (string) ($control['attr'] ?? '');

    $common = 'data-rs-input="' . e($key) . '" data-rs-type="' . e($type) . '"';

    if ($var !== '') {
        $common .= ' data-rs-var="' . e($var) . '"';
    }

    if ($attr !== '') {
        $common .= ' data-rs-attr="' . e($attr) . '"';
    }

    if (!empty($control['derived'])) {
        $common .= " data-rs-derived='" . e((string) json_encode($control['derived'])) . "'";
    }
    ?>
    <div class="rs-control" data-rs-control="<?= e($key) ?>">
        <div class="rs-control-head">
            <label class="rs-control-label" for="<?= e($id) ?>"><?= e($label) ?></label>

            <?php if ($type === 'range'): ?>
                <span class="rs-control-value" data-rs-output="<?= e($key) ?>"><?= e($value) ?><?= $unit !== '' ? ' ' . e($unit) : '' ?></span>
            <?php elseif ($type === 'color'): ?>
                <span class="rs-control-value" data-rs-output="<?= e($key) ?>"><?= e($value) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($type === 'color'): ?>
            <div class="rs-color-field">
                <input type="color" id="<?= e($id) ?>" value="<?= e($value) ?>" <?= $common ?>>
                <input
                    type="text"
                    class="rs-color-hex"
                    value="<?= e($value) ?>"
                    maxlength="7"
                    spellcheck="false"
                    autocomplete="off"
                    aria-label="<?= e($label) ?> — code hexadécimal"
                    data-rs-hex="<?= e($key) ?>"
                >
            </div>
        <?php elseif ($type === 'range'): ?>
            <input
                type="range"
                id="<?= e($id) ?>"
                class="rs-range"
                min="<?= e((string) ($control['min'] ?? 0)) ?>"
                max="<?= e((string) ($control['max'] ?? 100)) ?>"
                step="<?= e((string) ($control['step'] ?? 1)) ?>"
                value="<?= e($value) ?>"
                data-rs-unit="<?= e($unit) ?>"
                <?= $common ?>
            >
        <?php elseif ($type === 'select'): ?>
            <div class="rs-select-wrap">
                <select id="<?= e($id) ?>" class="rs-select" <?= $common ?>>
                    <?php foreach ((array) ($control['options'] ?? []) as $optionValue => $optionLabel): ?>
                        <?php $cssValue = (string) (($control['values'][$optionValue] ?? '') ?: ''); ?>
                        <option
                            value="<?= e((string) $optionValue) ?>"
                            <?= $cssValue !== '' ? 'data-rs-css-value="' . e($cssValue) . '"' : '' ?>
                            <?= (string) $optionValue === $value ? 'selected' : '' ?>
                        ><?= e((string) $optionLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= icon('chevron-right', 'icon icon--sm rs-select-caret') ?>
            </div>
        <?php elseif ($type === 'toggle'): ?>
            <?php $onValue = (string) ($control['on'] ?? 'on'); ?>
            <label class="rs-switch">
                <input
                    type="checkbox"
                    id="<?= e($id) ?>"
                    data-rs-on="<?= e($onValue) ?>"
                    data-rs-off="<?= e((string) ($control['off'] ?? 'off')) ?>"
                    <?= $value === $onValue ? 'checked' : '' ?>
                    <?= $common ?>
                >
                <span class="rs-switch-track" aria-hidden="true"></span>
                <span class="rs-switch-text"><?= $value === $onValue ? 'Activé' : 'Désactivé' ?></span>
            </label>
        <?php endif; ?>

        <?php if ($help !== ''): ?>
            <p class="rs-control-help"><?= e($help) ?></p>
        <?php endif; ?>
    </div>
    <?php
};

/**
 * Render a list of controls, keeping the optional group headings.
 *
 * @param array<string, mixed>  $controls
 * @param array<string, string> $values
 * @param array<string, string> $content
 */
$renderControlList = static function (array $controls, array $values, array $content) use ($renderControl): void {
    $lastGroup = '';

    foreach ($controls as $controlKey => $control):
        $control = (array) $control;
        $group   = (string) ($control['group'] ?? '');

        if ($group !== '' && $group !== $lastGroup):
            ?>
            <p class="rs-control-group"><?= e($group) ?></p>
            <?php
        endif;

        $lastGroup = $group;

        $renderControl((string) $controlKey, $control, $values, $content);
    endforeach;
};
?>
<div
    class="rs-app"
    id="reskinApp"
    data-applied="<?= $applied ? '1' : '0' ?>"
    data-csrf="<?= e($csrf) ?>"
    data-save-url="<?= e($saveUrl) ?>"
    data-reset-url="<?= e($resetUrl) ?>"
    data-apply-url="<?= e($applyUrl) ?>"
    data-disable-url="<?= e($disableUrl) ?>"
    data-theme-save-url="<?= e($themeSaveUrl) ?>"
    data-theme-load-url="<?= e($themeLoadUrl) ?>"
    data-theme-delete-url="<?= e($themeDeleteUrl) ?>"
    data-snapshot-url="<?= e($snapshotUrl) ?>"
    data-store-name="<?= e($storeName) ?>"
>
    <div class="rs-scrim" data-rs-sidebar-close hidden></div>

    <aside class="rs-sidebar" id="reskinSidebar">
        <div class="rs-brand-row">
            <span class="rs-brand-mark" aria-hidden="true"><?= icon('palette', 'icon') ?></span>
            <span class="rs-brand-copy">
                <span class="rs-brand-name">RESKIN</span>
                <span class="rs-brand-sub">Éditeur par section</span>
            </span>
            <button type="button" class="rs-sidebar-close" data-rs-sidebar-close aria-label="Fermer le menu">
                <?= icon('close', 'icon icon--sm') ?>
            </button>
        </div>

        <nav class="rs-nav" aria-label="Sections de la page">
            <?php foreach ($categories as $categoryKey => $category): ?>
                <button
                    type="button"
                    class="rs-nav-link<?= (string) $categoryKey === $firstKey ? ' is-active' : '' ?>"
                    data-rs-tab="<?= e((string) $categoryKey) ?>"
                    data-rs-target="<?= e((string) ($category['target'] ?? '')) ?>"
                    aria-controls="rs-panel-<?= e((string) $categoryKey) ?>"
                    aria-selected="<?= (string) $categoryKey === $firstKey ? 'true' : 'false' ?>"
                >
                    <?= icon((string) ($category['icon'] ?? 'palette'), 'icon icon--sm') ?>
                    <span><?= e((string) ($category['label'] ?? $categoryKey)) ?></span>
                    <?= icon('chevron-right', 'icon icon--sm rs-nav-chevron') ?>
                </button>
            <?php endforeach; ?>
        </nav>

        <div class="rs-sidebar-block">
            <div class="rs-sidebar-heading-row">
                <p class="rs-sidebar-heading">Styles enregistrés</p>
                <span class="rs-count" data-rs-library-count><?= count($library) ?></span>
            </div>

            <ul class="rs-theme-list" data-rs-theme-list>
                <?php foreach ($library as $name => $theme): ?>
                    <li class="rs-theme-item">
                        <button
                            type="button"
                            class="rs-theme-load"
                            data-rs-theme-load="<?= e((string) $name) ?>"
                            title="Charger « <?= e((string) $name) ?> »"
                        >
                            <span class="rs-theme-swatches" aria-hidden="true">
                                <i data-rs-swatch-color="<?= e((string) ($theme['values']['primary'] ?? '#704F38')) ?>"></i>
                                <i data-rs-swatch-color="<?= e((string) ($theme['values']['accent'] ?? '#704F38')) ?>"></i>
                                <i data-rs-swatch-color="<?= e((string) ($theme['values']['background'] ?? '#FFFFFF')) ?>"></i>
                            </span>
                            <span class="rs-theme-name"><?= e((string) $name) ?></span>
                        </button>

                        <button
                            type="button"
                            class="rs-theme-remove"
                            data-rs-theme-delete="<?= e((string) $name) ?>"
                            aria-label="Supprimer le style <?= e((string) $name) ?>"
                        >
                            <?= icon('trash', 'icon icon--sm') ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="rs-theme-empty" data-rs-theme-empty<?= $library === [] ? '' : ' hidden' ?>>
                Aucun style enregistré pour le moment.
            </p>

            <div class="rs-save-theme">
                <input
                    type="text"
                    class="rs-input"
                    maxlength="40"
                    placeholder="Nom du style"
                    aria-label="Nom du style à enregistrer"
                    data-rs-theme-name
                >
                <button type="button" class="rs-btn rs-btn--ghost rs-btn--compact" data-rs-theme-save>
                    <?= icon('plus', 'icon icon--sm') ?>
                    Ajouter
                </button>
            </div>

            <p class="rs-sidebar-note">
                « Enregistrer » publie les textes et réécrit le fichier CSS du site. « Sauvegarde » crée en plus une copie JSON de sécurité.
            </p>
        </div>

        <div class="rs-sidebar-foot">
            <span class="rs-owner-tag">Espace propriétaire</span>

            <a class="rs-foot-link" href="<?= e($shopUrl) ?>">
                <?= icon('settings', 'icon icon--sm') ?>
                Infos boutique
            </a>

            <a class="rs-foot-link" href="<?= e($siteUrl) ?>" target="_blank" rel="noopener">
                <?= icon('eye', 'icon icon--sm') ?>
                Voir la boutique
            </a>

            <form method="POST" action="<?= e($logoutUrl) ?>">
                <?= csrf_field() ?>
                <button type="submit" class="rs-foot-link rs-foot-link--button">
                    <?= icon('logout', 'icon icon--sm') ?>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <div class="rs-main">
        <header class="rs-toolbar">
            <button type="button" class="rs-icon-btn rs-menu-btn" data-rs-sidebar-open aria-label="Ouvrir les sections">
                <?= icon('menu', 'icon') ?>
            </button>

            <div class="rs-toolbar-title">
                <p class="rs-eyebrow"><?= e($storeName) ?> · Espace propriétaire</p>
                <h1 class="rs-app-title">RESKIN</h1>
            </div>

            <div class="rs-toolbar-actions">
                <span class="rs-status" data-rs-status data-state="saved">
                    <span class="rs-status-dot" aria-hidden="true"></span>
                    <span data-rs-status-text>Enregistré</span>
                </span>

                <div class="rs-history" role="group" aria-label="Historique des modifications">
                    <button
                        type="button"
                        class="rs-btn rs-btn--ghost rs-btn--icon"
                        data-rs-undo
                        title="Annuler (Ctrl+Z)"
                        aria-label="Annuler la dernière modification"
                        aria-keyshortcuts="Control+Z"
                        disabled
                    >
                        <?= icon('arrow-left', 'icon icon--sm') ?>
                    </button>

                    <button
                        type="button"
                        class="rs-btn rs-btn--ghost rs-btn--icon"
                        data-rs-redo
                        title="Rétablir (Ctrl+Maj+Z)"
                        aria-label="Rétablir la modification annulée"
                        aria-keyshortcuts="Control+Shift+Z Control+Y"
                        disabled
                    >
                        <?= icon('arrow-right', 'icon icon--sm') ?>
                    </button>
                </div>

                <button
                    type="button"
                    class="rs-btn rs-btn--ghost"
                    data-rs-snapshot
                    title="Créer une copie JSON de sécurité (les modifications en attente sont d’abord enregistrées)"
                >
                    <?= icon('box', 'icon icon--sm') ?>
                    <span class="rs-btn-label">Sauvegarde</span>
                </button>

                <details class="rs-actions-menu">
                    <summary class="rs-btn rs-btn--ghost rs-btn--icon" aria-label="Plus d’actions" title="Plus d’actions">
                        <?= icon('menu', 'icon icon--sm') ?>
                    </summary>

                    <div class="rs-actions-menu-panel">
                        <a class="rs-menu-action" href="<?= e($exportUrl) ?>" data-rs-export>
                            <?= icon('download', 'icon icon--sm') ?>
                            <span>
                                <strong>Exporter le CSS</strong>
                                <small>Télécharger le thème généré</small>
                            </span>
                        </a>

                        <button type="button" class="rs-menu-action rs-menu-action--danger" data-rs-reset>
                            <?= icon('refresh', 'icon icon--sm') ?>
                            <span>
                                <strong>Réinitialiser le design</strong>
                                <small>Le contenu et les infos boutique sont conservés</small>
                            </span>
                        </button>
                    </div>
                </details>

                <button
                    type="button"
                    class="rs-btn rs-btn--primary"
                    data-rs-save
                    title="Enregistrer et publier le fichier CSS du site"
                >
                    <?= icon('check-circle', 'icon icon--sm') ?>
                    <span class="rs-btn-label">Enregistrer</span>
                </button>

                <button type="button" class="rs-btn rs-btn--apply" data-rs-apply>
                    <?= icon('power', 'icon icon--sm') ?>
                    <span class="rs-btn-label" data-rs-apply-label><?= $applied ? 'Retirer les styles' : 'Appliquer les styles' ?></span>
                </button>
            </div>
        </header>

        <div class="rs-workspace">
            <?php if ($presets !== []): ?>
                <section class="rs-starters" aria-labelledby="rs-starters-title">
                    <div class="rs-starters-head">
                        <div>
                            <span class="rs-starters-eyebrow">Départ rapide</span>
                            <h2 class="rs-starters-title" id="rs-starters-title">Partez d’une ambiance</h2>
                            <p class="rs-starters-desc">
                                Des bases complètes, dont le design d’origine. Le contenu reste intact et rien n’est publié avant « Enregistrer ».
                            </p>
                        </div>

                        <span class="rs-starters-count"><?= count($presets) ?> styles</span>
                    </div>

                    <div class="rs-starter-grid">
                        <?php foreach ($presets as $preset): ?>
                            <?php $isDefault = $preset['key'] === 'default'; ?>
                            <button
                                type="button"
                                class="rs-starter<?= $isDefault ? ' rs-starter--default' : '' ?>"
                                data-rs-preset="<?= e($preset['key']) ?>"
                                data-rs-preset-label="<?= e($preset['label']) ?>"
                                data-rs-preset-values="<?= e((string) json_encode($preset['values'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                aria-pressed="false"
                                title="<?= $isDefault ? 'Restaurer tous les réglages de design d’origine' : 'Appliquer « ' . e($preset['label']) . ' » comme brouillon' ?>"
                            >
                                <span class="rs-starter-top">
                                    <span class="rs-starter-swatches" aria-hidden="true">
                                        <i data-rs-swatch-color="<?= e((string) ($preset['values']['primary'] ?? '#704F38')) ?>"></i>
                                        <i data-rs-swatch-color="<?= e((string) ($preset['values']['accent'] ?? '#704F38')) ?>"></i>
                                        <i data-rs-swatch-color="<?= e((string) ($preset['values']['background'] ?? '#FFFFFF')) ?>"></i>
                                    </span>
                                    <span class="rs-starter-action">
                                        <?= $isDefault ? 'Restaurer' : 'Choisir' ?>
                                        <?= icon($isDefault ? 'refresh' : 'arrow-right', 'icon icon--sm') ?>
                                    </span>
                                </span>

                                <span class="rs-starter-name"><?= e($preset['label']) ?></span>
                                <span class="rs-starter-desc"><?= e($preset['description']) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="rs-panel" aria-label="Personnalisation">
                <div class="rs-panel-head">
                    <span class="rs-panel-icon" data-rs-panel-icon aria-hidden="true">
                        <?= icon((string) ($categories[$firstKey]['icon'] ?? 'palette'), 'icon') ?>
                    </span>

                    <div class="rs-panel-heading-copy">
                        <span class="rs-panel-eyebrow">Personnalisation</span>
                        <h2 class="rs-panel-title" data-rs-panel-title><?= e((string) ($categories[$firstKey]['label'] ?? 'Personnalisation')) ?></h2>
                        <p class="rs-panel-desc" data-rs-panel-desc><?= e((string) ($categories[$firstKey]['description'] ?? '')) ?></p>
                    </div>
                </div>

                <?php foreach ($categories as $categoryKey => $category): ?>
                    <div
                        class="rs-controls"
                        id="rs-panel-<?= e((string) $categoryKey) ?>"
                        data-rs-panel="<?= e((string) $categoryKey) ?>"
                        data-title="<?= e((string) ($category['label'] ?? $categoryKey)) ?>"
                        data-desc="<?= e((string) ($category['description'] ?? '')) ?>"
                        <?= (string) $categoryKey === $firstKey ? '' : 'hidden' ?>
                    >
                        <?php if (!empty($category['notice'])): ?>
                            <p class="rs-notice"><?= icon('alert-circle', 'icon icon--sm') ?> <?= e((string) $category['notice']) ?></p>
                        <?php endif; ?>

                        <?php
                        $designControls  = [];
                        $contentControls = [];

                        foreach (($category['controls'] ?? []) as $controlKey => $control) {
                            if (isset($control['content'])) {
                                $contentControls[$controlKey] = $control;
                            } else {
                                $designControls[$controlKey] = $control;
                            }
                        }
                        ?>

                        <?php if ($designControls !== []): ?>
                            <div class="rs-panel-block">
                                <div class="rs-block-head">
                                    <span class="rs-block-title">Design</span>
                                    <span class="rs-block-count"><?= count($designControls) ?></span>
                                </div>
                                <?php $renderControlList($designControls, $values, $content); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($contentControls !== []): ?>
                            <div class="rs-panel-block rs-panel-block--content">
                                <div class="rs-block-head">
                                    <span class="rs-block-title">Contenu</span>
                                    <span class="rs-block-count"><?= count($contentControls) ?></span>
                                </div>
                                <?php $renderControlList($contentControls, $values, $content); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <section class="rs-preview" aria-label="Aperçu en direct">
                <div class="rs-preview-bar">
                    <span class="rs-browser-dots" aria-hidden="true">
                        <i></i><i></i><i></i>
                    </span>

                    <span class="rs-preview-url" title="Aperçu de la boutique">
                        <?= icon('eye', 'icon icon--sm') ?>
                        <span><?= e($storeName) ?></span>
                    </span>

                    <div class="rs-devices" role="group" aria-label="Taille d’aperçu">
                        <button type="button" class="rs-device is-active" data-rs-device="desktop" aria-label="Aperçu bureau">
                            <?= icon('monitor', 'icon icon--sm') ?>
                        </button>
                        <button type="button" class="rs-device" data-rs-device="tablet" aria-label="Aperçu tablette">
                            <?= icon('tablet', 'icon icon--sm') ?>
                        </button>
                        <button type="button" class="rs-device" data-rs-device="mobile" aria-label="Aperçu mobile">
                            <?= icon('smartphone', 'icon icon--sm') ?>
                        </button>
                    </div>
                </div>

                <div class="rs-preview-stage" data-rs-stage>
                    <iframe
                        class="rs-preview-frame"
                        data-rs-preview
                        src="<?= e($previewUrl) ?>"
                        title="Aperçu en direct de la boutique"
                        sandbox="allow-same-origin"
                        loading="eager"
                    ></iframe>
                </div>

                <div class="rs-preview-foot">
                    <span class="rs-live"><span class="rs-live-dot" aria-hidden="true"></span> Aperçu en direct</span>
                    <span class="rs-apply-state" data-rs-apply-state>
                        <?= $applied ? 'Styles appliqués sur la boutique' : 'Non appliqué — la boutique garde son design actuel' ?>
                    </span>
                </div>
            </section>
        </div>
    </div>

    <div class="rs-toast" data-rs-toast role="status" aria-live="polite" hidden>
        <span data-rs-toast-text></span>
    </div>
</div>
