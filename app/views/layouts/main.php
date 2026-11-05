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

$siteName    = (string) config('app_name');
$pageTitle   = $title ?? $siteName;


$pageDesc    = $description ?? 'Mode turque femme et enfant au Maroc. Commandez facilement via WhatsApp.';
$canonicalUrl = $canonical ?? url(current_path());
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

    <meta name="theme-color" content="#704F38">
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
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>

    <?= $head ?? '' ?>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
    <a class="skip-link" href="#contenu">Aller au contenu</a>

    <?php if (function_exists('partial')): ?>
        <?php partial('partials/header', ['cartCount' => $cartCount, 'wishCount' => $wishCount]); ?>
        <?php partial('partials/drawer'); ?>
    <?php else: ?>
        <?php if (isset($partial) && $partial instanceof \Closure): ?>
            <?php $partial('partials/header', ['cartCount' => $cartCount, 'wishCount' => $wishCount]); ?>
            <?php $partial('partials/drawer'); ?>
        <?php endif; ?>
    <?php endif; ?>

    <main id="contenu" class="site-main">
        <?= $content ?>
    </main>

    <?php if (function_exists('partial')): ?>
        <?php partial(partials/footer); ?>
    <?php elseif (isset($partial) && $partial instanceof \Closure): ?>
        <?php $partial('partials/footer'); ?>
    <?php endif; ?>
</body>
</html>