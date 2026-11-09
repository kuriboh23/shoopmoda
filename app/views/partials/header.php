<?php

declare(strict_types=1);

/**
 * Header partial: announcement bar + sticky header.
 * The hamburger is the first element (left side) and opens the drawer.
 *
 * Announcement messages and navigation come from the editable store content
 * (SiteContent) and from the categories managed in the admin area, so the
 * owner never has to edit a template to change the shop.
 *
 * @var int $cartCount
 * @var int $wishCount
 */

$content              = \App\Models\SiteContent::section('announcement');
$announcementMessages = array_values((array) ($content['messages'] ?? []));

if ($announcementMessages === []) {
    $announcement         = (array) config('announcement');
    $announcementMessages = array_values(array_filter([
        (string) ($announcement['highlight'] ?? ''),
        (string) ($announcement['text'] ?? ''),
    ]));
}

$location = trim((string) ($content['location'] ?? 'Maroc'));

$navItems = [
    ['label' => 'Boutique', 'url' => '/produits', 'icon' => 'shirt'],
];

foreach (nav_categories(4) as $category) {
    $navItems[] = [
        'label' => (string) ($category['name'] ?? ''),
        'url'   => '/categorie/' . (string) ($category['slug'] ?? ''),
        'icon'  => (string) ($category['icon'] ?? 'shirt'),
    ];
}

$navItems[] = ['label' => 'Promotions', 'url' => '/promotions', 'icon' => 'tag'];

$logo  = site_logo_url();
[$brandFirst, $brandSecond] = brand_parts();
?>
<aside class="announcement-bar" aria-label="Informations boutique">
    <span class="announcement-badge" aria-hidden="true">
        <?= icon('truck', 'icon') ?>
    </span>

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

    <?php if ($location !== ''): ?>
        <span class="location-tag">
            <span class="location-pulse" aria-hidden="true"></span>
            <?= e($location) ?>
        </span>
    <?php endif; ?>
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

    <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e(store_name()) ?> — Accueil">
        <?php if ($logo !== null): ?>
            <img class="brand-logo" src="<?= e($logo) ?>" alt="<?= e(store_name()) ?>" width="160" height="48">
        <?php else: ?>
            <span class="brand-text">
                <span class="brand-wordmark">
                    <span class="brand-first"><?= e($brandFirst) ?></span><?php if ($brandSecond !== ''): ?><span class="brand-second"><?= e($brandSecond) ?></span><?php endif; ?>
                </span>
            </span>
        <?php endif; ?>
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
            <span class="badge badge--dot" data-badge="wishlist"<?= $wishCount > 0 ? '' : ' hidden' ?>></span>
        </a>

        <a class="icon-btn" href="<?= e(url('/panier')) ?>" aria-label="Mon panier">
            <?= icon('cart', 'icon') ?>
            <span class="badge" data-badge="cart"<?= $cartCount > 0 ? '' : ' hidden' ?>><?= (int) $cartCount ?></span>
        </a>
    </div>
</header>
