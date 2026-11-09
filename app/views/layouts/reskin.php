<?php

declare(strict_types=1);

/**
 * RESKIN layout — standalone, no dependency on the storefront stylesheet,
 * so the editor can be moved to another client website as-is.
 *
 * @var string      $content
 * @var string|null $title
 * @var bool|null   $reskinAuth
 */

$reskinAuth = $reskinAuth ?? false;
$pageTitle  = $title ?? 'RESKIN — Éditeur de thème';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="color-scheme" content="light">
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/reskin.css')) ?>">
    <script src="<?= e(asset('js/reskin.js')) ?>" defer></script>
</head>
<body class="rs-body<?= $reskinAuth ? ' rs-body--auth' : '' ?>">
    <?php if ($reskinAuth): ?>
        <main class="rs-auth">
            <?= $content ?>
        </main>
    <?php else: ?>
        <?= $content ?>
    <?php endif; ?>
</body>
</html>
