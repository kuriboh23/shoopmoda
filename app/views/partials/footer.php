<?php

declare(strict_types=1);

/**
 * Footer partial.
 * Only links that exist in the PRD scope are rendered here
 * (no fake "Track order" / "FAQ" pages).
 */
?>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <a class="footer-logo" href="<?= e(url('/')) ?>">
                <span class="brand-wordmark footer-wordmark">
                    <span class="brand-first">Shoop</span><span class="brand-second">Moda</span>
                </span>
                <span class="footer-logo-sub"></span>
                <span class="visually-hidden"><?= e(config('app_name')) ?> — Accueil</span>
            </a>

            <p class="footer-desc">
                Mode turque premium et pyjamas pour femme et enfant au Maroc.
                Livraison directe à votre porte.
            </p>

            <a
                class="footer-social"
                href="<?= e(config('instagram_url')) ?>"
                target="_blank"
                rel="noopener"
                aria-label="Instagram Shoop Moda Style"
            >
                <?= icon('instagram', 'icon') ?>
            </a>
        </div>

        <div class="footer-col">
            <h2 class="footer-title">Catégories</h2>
            <ul class="footer-links">
                <li><a href="<?= e(url('/produits')) ?>">Toutes les pièces</a></li>
                <li><a href="<?= e(url('/categorie/femme')) ?>">Collection femme</a></li>
                <li><a href="<?= e(url('/categorie/enfants')) ?>">Mode enfant</a></li>
                <li><a href="<?= e(url('/categorie/pyjamas')) ?>">Pyjamas &amp; ensembles</a></li>
                <li><a href="<?= e(url('/categorie/robes')) ?>">Robes</a></li>
                <li><a href="<?= e(url('/promotions')) ?>">Promotions</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h2 class="footer-title">Aide</h2>
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
            <h2 class="footer-title">Commande rapide</h2>
            <div class="footer-wa-card">
                <span class="footer-wa-label">Commandez directement via WhatsApp :</span>
                <a class="footer-wa-btn" href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', 'icon icon--sm') ?>
                    07 25 60 55 20
                </a>
                <div class="footer-pills">
                    <span class="pill">Tout le Maroc</span>
                    <span class="pill">Express 24-48h</span>
                </div>
            </div>
        </div>
    </div>

    <div class="wrap footer-bottom">
        <p>&copy; <?= e(date('Y')) ?> <strong><?= e(config('app_name')) ?></strong>. Tous droits réservés.</p>
        <p>Conçu pour un shopping mobile rapide au Maroc.</p>
    </div>
</footer>
