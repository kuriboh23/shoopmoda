<?php

declare(strict_types=1);

/**
 * Header partial: announcement bar + sticky header.
 * The hamburger is the first element (left side) and opens the drawer.
 *
 * @var int $cartCount
 * @var int $wishCount
 */

$announcement = (array) config('announcement');
$announcementMessages = (array) ($announcement['messages'] ?? []);
if ($announcementMessages === []) {
    $announcementMessages = array_filter([$announcement['highlight'] ?? '', $announcement['text'] ?? '']);
}
$navItems = [
    ['label' => 'Accueil',   'url' => '/'],
    ['label' => 'Femme',     'url' => '/categorie/femme'],
    ['label' => 'Enfants',   'url' => '/categorie/enfants'],
    ['label' => 'Pyjamas',   'url' => '/categorie/pyjamas'],
    ['label' => 'Promotions', 'url' => '/promotions'],
];
?>
<aside class="announcement-bar" aria-label="Informations boutique">
    <div class="announcement-marquee">
        <?php for ($pass = 0; $pass < 2; $pass++): ?>
            <div class="announcement-track"<?= $pass === 1 ? ' aria-hidden="true"' : '' ?>>
                <?php foreach ($announcementMessages as $message): ?>
                    <span class="announcement-item">
                        <?= e($message) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>

    <span class="location-tag">
        <span class="location-pulse" aria-hidden="true"></span>
        <?= e($announcement['location'] ?? 'Maroc') ?>
    </span>
</aside>

<header class="site-header">
    <button
        type="button"
        class="icon-btn menu-toggle"
        id="drawerToggle"
        aria-label="Ouvrir le menu"
        aria-controls="drawer"
        aria-expanded="false"
    >
        <?= icon('menu', 'icon') ?>
    </button>

    <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e(config('app_name')) ?> — Accueil">
        <span class="brand-text">
            <span class="brand-wordmark">
                <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
            </span>
        </span>
        <span class="visually-hidden">Accueil</span>
    </a>

    <nav class="desktop-nav" aria-label="Navigation principale">
        <?php foreach ($navItems as $item): ?>
            <?php $active = (current_pathname() === $item['url']); ?>
            <a
                class="nav-link<?= $active ? ' is-active' : '' ?>"
                href="<?= e(url($item['url'])) ?>"
                <?= $active ? 'aria-current="page"' : '' ?>
            ><?= e($item['label']) ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="header-actions">
        

        <a class="icon-btn" href="<?= e(url('/favoris')) ?>" aria-label="Ma liste de favoris">
            <?= icon('heart', 'icon') ?>
            <span class="badge" data-badge="wishlist"<?= $wishCount > 0 ? '' : ' hidden' ?>><?= (int) $wishCount ?></span>
        </a>

        <a class="icon-btn" href="<?= e(url('/panier')) ?>" aria-label="Mon panier">
            <?= icon('cart', 'icon') ?>
            <span class="badge" data-badge="cart"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= (int) $cartCount ?></span>
        </a>
    </div>
</header>
