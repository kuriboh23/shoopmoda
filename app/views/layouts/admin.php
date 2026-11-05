<?php

declare(strict_types=1);

/**
 * Admin layout: sidebar on desktop, bottom navigation on phones.
 *
 * @var string      $content
 * @var string|null $title
 * @var string|null $adminTitle
 * @var string      $adminActive
 * @var bool        $adminAuth
 */

$adminAuth   = $adminAuth ?? false;
$adminActive = $adminActive ?? '';
$pageTitle   = $title ?? 'Gestion — ' . config('app_name');
$adminTitle  = $adminTitle ?? 'Gestion';
$flash       = $flash ?? flash_message();

$navItems = [
    ['key' => 'dashboard', 'label' => 'Tableau', 'icon' => 'grid', 'url' => admin_url()],
    ['key' => 'products', 'label' => 'Produits', 'icon' => 'box', 'url' => admin_url('produits')],
    ['key' => 'categories', 'label' => 'Catégories', 'icon' => 'tag', 'url' => admin_url('categories')],
    ['key' => 'settings', 'label' => 'Réglages', 'icon' => 'settings', 'url' => admin_url('reglages')],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($pageTitle) ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#1B1A18">
    <link rel="icon" href="<?= e(url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="admin-body">
    <a class="skip-link" href="#contenu">Aller au contenu</a>

    <?php if ($adminAuth): ?>
        <main id="contenu" class="admin-auth">
            <?= $content ?>
        </main>
    <?php else: ?>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="<?= e(admin_url()) ?>">
                    <span class="brand-wordmark">
                        <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
                    </span>
                    <span class="admin-brand-tag">Gestion</span>
                </a>

                <nav class="admin-nav" aria-label="Navigation de gestion">
                    <?php foreach ($navItems as $item): ?>
                        <a
                            class="admin-nav-link<?= $adminActive === $item['key'] ? ' is-active' : '' ?>"
                            href="<?= e($item['url']) ?>"
                            <?= $adminActive === $item['key'] ? 'aria-current="page"' : '' ?>
                        >
                            <?= icon($item['icon'], 'icon icon--sm') ?>
                            <?= e($item['label']) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="admin-sidebar-foot">
                    <a class="admin-nav-link" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">
                        <?= icon('eye', 'icon icon--sm') ?>
                        Voir la boutique
                    </a>

                    <form method="POST" action="<?= e(admin_url('deconnexion')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-nav-link admin-nav-link--button">
                            <?= icon('logout', 'icon icon--sm') ?>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div class="admin-topbar-text">
                        <p class="admin-topbar-eyebrow"><?= e((string) setting('store_name', config('app_name'))) ?></p>
                        <h1 class="admin-page-title"><?= e($adminTitle) ?></h1>
                    </div>

                    <div class="admin-topbar-actions">
                        <a class="btn btn--ghost btn--compact" href="<?= e(admin_url('produits/nouveau')) ?>">
                            <?= icon('plus', 'icon icon--sm') ?>
                            <span class="admin-btn-label">Produit</span>
                        </a>

                        <form class="admin-topbar-logout" method="POST" action="<?= e(admin_url('deconnexion')) ?>">
                            <?= csrf_field() ?>
                            <button class="admin-icon-btn" type="submit" aria-label="Se déconnecter">
                                <?= icon('logout', 'icon icon--sm') ?>
                            </button>
                        </form>
                    </div>
                </header>

                <main id="contenu" class="admin-content">
                    <?php if (is_array($flash)): ?>
                        <p class="admin-flash admin-flash--<?= e($flash['type']) ?>" role="status">
                            <?= e($flash['message']) ?>
                        </p>
                    <?php endif; ?>

                    <?= $content ?>
                </main>
            </div>
        </div>

        <nav class="admin-bottomnav" aria-label="Navigation rapide">
            <?php foreach ($navItems as $item): ?>
                <a
                    class="admin-bottomnav-link<?= $adminActive === $item['key'] ? ' is-active' : '' ?>"
                    href="<?= e($item['url']) ?>"
                    <?= $adminActive === $item['key'] ? 'aria-current="page"' : '' ?>
                >
                    <?= icon($item['icon'], 'icon') ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</body>
</html>
