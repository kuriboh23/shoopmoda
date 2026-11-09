<?php

declare(strict_types=1);

/** Storefront layout.
 *
 * Variables in scope: $content, plus whatever the controller passed.
 *
 * @var string $content
 * @var string|null $title
 * @var string|null $description
 * @var string|null $canonical
 * @var string|null $head
 * @var bool|null   $noindex
 * @var int|null    $cartCount
 * @var int|null    $wishCount
 */

$siteName    = store_name();
$pageTitle   = $title ?? $siteName;


$pageDesc    = $description ?? 'Mode turque femme et enfant au Maroc. Commandez facilement via WhatsApp.';

// RESKIN: a theme only loads when the owner explicitly applied it. Without
// that, the storefront is byte-for-byte the original design.
$reskinApplied   = class_exists(\App\Reskin\ThemeStore::class) && \App\Reskin\ThemeStore::isApplied();
$reskinBodyAttrs = $reskinApplied ? \App\Reskin\ThemeStore::bodyAttributes() : '';

// Canonical / Open Graph URLs must include the domain; url() is root-relative.
$canonicalUrl = $canonical ?? current_path();

if (preg_match('#^https?://#i', (string) $canonicalUrl) !== 1) {
    $canonicalUrl = abs_url((string) $canonicalUrl);
}
$cartCount   = $cartCount ?? 0;
$wishCount   = $wishCount ?? 0;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <?php if (!empty($noindex)): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <meta name="theme-color" content="#A9552F">
    <meta name="format-detection" content="telephone=no">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:locale" content="fr_MA">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDesc) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <?php if ($reskinApplied): ?>
        <link rel="stylesheet" href="<?= e(\App\Reskin\ThemeStore::cssUrl()) ?>">
    <?php endif; ?>
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>

    <?= $head ?? '' ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>"<?= $reskinBodyAttrs !== '' ? ' ' . $reskinBodyAttrs : '' ?>>
    <a class="skip-link" href="#contenu">Aller au contenu</a>

    <?php partial('partials/header', ['cartCount' => $cartCount, 'wishCount' => $wishCount]); ?>
    <?php partial('partials/drawer'); ?>

    <main id="contenu" class="site-main">
        <?= $content ?>
    </main>

    <div class="toast" id="smToast" role="status" aria-live="polite" hidden>
        <p class="toast-text">
            <?= icon('check-circle', 'icon icon--sm') ?>
            <span data-toast-text>Article ajouté au panier</span>
        </p>
        <div class="toast-actions">
            <a class="btn btn--dark btn--compact" href="<?= e(url('/panier')) ?>">Voir le panier</a>
            <button type="button" class="btn btn--ghost btn--compact" data-toast-close>Continuer</button>
        </div>
    </div>

    <?php partial('partials/footer'); ?>
</body>
</html>