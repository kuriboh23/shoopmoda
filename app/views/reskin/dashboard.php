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
                <div class="rs-select-wrap">
                    <select id="<?= e($id) ?>" class="rs-select" <?= $common ?>>
                        <?php foreach ((array) ($control['options'] ?? []) as $optionValue => $optionLabel): ?>
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
                Les textes sont publiés sur la boutique dès « Enregistrer ». Les couleurs et la mise en page nécessitent « Appliquer au site ».
            </p>
        </div>

        <div class="rs-sidebar-foot">
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
                <p class="rs-eyebrow"><?= e($storeName) ?> · Apparence &amp; contenu</p>
                <h1 class="rs-app-title">RESKIN</h1>
            </div>

            <div class="rs-toolbar-actions">
                <span class="rs-status" data-rs-status data-state="saved">
                    <span class="rs-status-dot" aria-hidden="true"></span>
                    <span data-rs-status-text>Enregistré</span>
                </span>

                <button type="button" class="rs-btn rs-btn--ghost" data-rs-reset>
                    <?= icon('refresh', 'icon icon--sm') ?>
                    <span class="rs-btn-label">Réinitialiser</span>
                </button>

                <a class="rs-btn rs-btn--ghost" href="<?= e($exportUrl) ?>" data-rs-export>
                    <?= icon('download', 'icon icon--sm') ?>
                    <span class="rs-btn-label">Exporter CSS</span>
                </a>

                <button type="button" class="rs-btn rs-btn--primary" data-rs-save>
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
            <section class="rs-panel" aria-label="Personnalisation">
                <div class="rs-panel-head">
                    <h2 class="rs-panel-title" data-rs-panel-title><?= e((string) ($categories[$firstKey]['label'] ?? 'Personnalisation')) ?></h2>
                    <p class="rs-panel-desc" data-rs-panel-desc><?= e((string) ($categories[$firstKey]['description'] ?? '')) ?></p>
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
                        $lastGroup = '';

                        foreach (($category['controls'] ?? []) as $controlKey => $control):
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
                        ?>
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
