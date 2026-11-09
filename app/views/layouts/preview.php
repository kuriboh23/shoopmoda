<?php

declare(strict_types=1);

/**
 * RESKIN preview layout — a real storefront page, loaded inside the
 * dashboard iframe with the site's own app.css and the RESKIN skin.
 *
 * @var string      $content
 * @var string|null $title
 * @var string      $bodyAttrs
 */

$pageTitle = $title ?? 'Aperçu';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/reskin-skin.css')) ?>">
</head>
<body class="rs-preview-body" <?= $bodyAttrs ?>>
    <?= $content ?>
</body>
</html>
