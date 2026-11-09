<?php

declare(strict_types=1);

/**
 * Footer partial.
 *
 * Categories, description, logo and social link are all editable from the
 * admin / RESKIN, so this template never needs to be touched by the owner.
 */

$content   = \App\Models\SiteContent::section('footer');
$footerCats = nav_categories(5);
$instagram = trim((string) \App\Models\Setting::raw('instagram_url', (string) config('instagram_url')));
$logo      = site_logo_url();
[$brandFirst, $brandSecond] = brand_parts();
?>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= e(url('/')) ?>">
                <?php if ($logo !== null): ?>
                    <img class="footer-logo-img" src="<?= e($logo) ?>" alt="<?= e(store_name()) ?>" width="160" height="48">
                <?php else: ?>
                    <span class="brand-wordmark footer-wordmark">
                        <span class="brand-first"><?= e($brandFirst) ?></span><?php if ($brandSecond !== ''): ?><span class="brand-second"><?= e($brandSecond) ?></span><?php endif; ?>
                    </span>
                <?php endif; ?>
                <span class="footer-logo-sub"><?= e((string) ($content['logo_sub'] ?? '')) ?></span>
                <span class="visually-hidden"><?= e(store_name()) ?> — Accueil</span>
            </a>

            <p class="footer-desc">
                <?= e((string) ($content['description'] ?? '')) ?>
            </p>

            <?php if ($instagram !== ''): ?>
                <a
                    class="footer-social"
                    href="<?= e($instagram) ?>"
                    target="_blank"
                    rel="noopener"
                    aria-label="Instagram <?= e(store_name()) ?>"
                >
                    <?= icon('instagram', 'icon') ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="footer-col">
            <h2 class="footer-title"><?= e((string) ($content['categories_title'] ?? 'Catégories')) ?></h2>
            <ul class="footer-links">
                <li><a href="<?= e(url('/produits')) ?>">Toutes les pièces</a></li>
                <?php foreach ($footerCats as $category): ?>
                    <li>
                        <a href="<?= e(url('/categorie/' . (string) ($category['slug'] ?? ''))) ?>">
                            <?= e((string) ($category['name'] ?? '')) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><a href="<?= e(url('/promotions')) ?>">Promotions</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h2 class="footer-title"><?= e((string) ($content['help_title'] ?? 'Aide')) ?></h2>
            <ul class="footer-links">
                <li><a href="<?= e(url('/panier')) ?>">Mon panier</a></li>
                <li><a href="<?= e(url('/favoris')) ?>">Mes favoris</a></li>
                <li>
                    <a href="<?= e(whatsapp_url('Bonjour, j’ai une question sur ma commande.')) ?>" target="_blank" rel="noopener">
                        Nous contacter
                    </a>
                </li>
            </ul>
        </div>

        <div class="footer-col">
            <h2 class="footer-title"><?= e((string) ($content['order_title'] ?? 'Commande rapide')) ?></h2>
            <div class="footer-wa-card">
                <span class="footer-wa-label"><?= e((string) ($content['whatsapp_label'] ?? '')) ?></span>
                <a class="footer-wa-btn" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', 'icon icon--sm') ?>
                    <?= e(whatsapp_display()) ?>
                </a>
                <div class="footer-pills">
                    <span class="pill"><?= e((string) ($content['pill_1'] ?? '')) ?></span>
                    <span class="pill"><?= e((string) ($content['pill_2'] ?? '')) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="wrap footer-bottom">
        <p>&copy; <?= e(date('Y')) ?> <strong><?= e(store_name()) ?></strong>. Tous droits réservés.</p>
        <p><?= e((string) ($content['bottom_note'] ?? '')) ?></p>
    </div>
</footer>
