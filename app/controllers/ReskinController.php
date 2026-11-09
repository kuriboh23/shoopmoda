<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteContent;
use App\Reskin\ShopSettings;
use App\Reskin\ThemeStore;

/**
 * RESKIN — visual theme editor.
 *
 * Owner-only: the dashboard is protected by its own password
 * (reskin_password in config.php). A client admin session deliberately does
 * not open it, so branding and store identity stay private.
 *
 * Every mutation returns JSON: the dashboard never reloads while editing.
 * Saving writes assets/css/reskin-theme.css, which the storefront loads as a
 * normal static file. themeCss() remains the on-demand fallback.
 */
final class ReskinController
{
    private const SESSION_KEY = 'reskin';

    /* ==================================================================
       Authentication
       ================================================================== */

    public function loginForm(): void
    {
        if ($this->isLoggedIn()) {
            redirect($this->path());
        }

        View::render('reskin/login', [
            'title' => 'Connexion — RESKIN',
            'error' => null,
        ], 'layouts/reskin');
    }

    public function login(): void
    {
        if ($this->isLoggedIn()) {
            redirect($this->path());
        }

        $password = (string) ($_POST['password'] ?? '');
        $expected = (string) config('reskin_password', '');

        $valid = csrf_verify($_POST['_csrf'] ?? null)
            && $expected !== ''
            && hash_equals($expected, $password);

        if (!$valid) {
            usleep(300000);

            http_response_code(422);

            View::render('reskin/login', [
                'title' => 'Connexion — RESKIN',
                'error' => 'Mot de passe incorrect.',
            ], 'layouts/reskin');

            return;
        }

        Session::start();
        session_regenerate_id(true);
        Session::set(self::SESSION_KEY, ['at' => time()]);

        redirect($this->path());
    }

    public function logout(): void
    {
        if (!csrf_verify($_POST['_csrf'] ?? null)) {
            redirect($this->path());
        }

        Session::remove(self::SESSION_KEY);

        redirect($this->path('connexion'));
    }

    /* ==================================================================
       Dashboard
       ================================================================== */

    public function dashboard(): void
    {
        $this->requireAuth();

        View::render('reskin/dashboard', [
            'title'       => 'RESKIN — Éditeur de thème',
            'noindex'     => true,
            'schema'      => $this->schemaWithLinks(),
            'values'      => ThemeStore::values(),
            'content'     => SiteContent::flat(),
            'library'     => ThemeStore::library(),
            'presets'     => ThemeStore::presets(),
            'current'     => ThemeStore::currentInfo(),
            'applied'     => ThemeStore::isApplied(),
            'previewUrl'  => url($this->path('apercu')),
            'exportUrl'   => url($this->path('exporter')),
            'saveUrl'     => url($this->path('enregistrer')),
            'resetUrl'    => url($this->path('reinitialiser')),
            'applyUrl'    => url($this->path('appliquer')),
            'disableUrl'  => url($this->path('desactiver')),
            'themeSaveUrl' => url($this->path('themes')),
            'themeLoadUrl' => url($this->path('themes/charger')),
            'themeDeleteUrl' => url($this->path('themes/supprimer')),
            'shopUrl'     => url($this->path('boutique')),
            'snapshotUrl' => url($this->path('sauvegarder-copie')),
            'logoutUrl'   => url($this->path('deconnexion')),
            'siteUrl'     => url('/'),
            'csrf'        => csrf_token(),
        ], 'layouts/reskin');
    }

    /**
     * Owner-only store identity. Deliberately outside the client admin: the
     * client keeps products and categories, never branding or pricing rules.
     */
    public function shopSettings(): void
    {
        $this->requireAuth();

        View::render('reskin/shop', [
            'title'      => 'Infos boutique — RESKIN',
            'noindex'    => true,
            'values'     => ShopSettings::values(),
            'errors'     => [],
            'saved'      => false,
            'shopUrl'    => url($this->path('boutique')),
            'backUrl'    => url($this->path()),
            'siteUrl'    => url('/'),
            'logoutUrl'  => url($this->path('deconnexion')),
        ], 'layouts/reskin');
    }

    public function saveShopSettings(): void
    {
        $this->requireAuth();

        if (!csrf_verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Session expirée, merci de réessayer.');
            redirect($this->path('boutique'));
        }

        $result = ShopSettings::save($_POST, $_FILES);

        if ($result['saved']) {
            flash('success', 'Infos boutique enregistrées. Le site est mis à jour immédiatement.');
            redirect($this->path('boutique'));
        }

        http_response_code(422);

        View::render('reskin/shop', [
            'title'      => 'Infos boutique — RESKIN',
            'noindex'    => true,
            'values'     => $result['values'],
            'errors'     => $result['errors'],
            'saved'      => false,
            'shopUrl'    => url($this->path('boutique')),
            'backUrl'    => url($this->path()),
            'siteUrl'    => url('/'),
            'logoutUrl'  => url($this->path('deconnexion')),
        ], 'layouts/reskin');
    }

    /**
     * Permanent JSON copy of everything RESKIN can change: theme, content and
     * shop identity. Stored under storage/reskin/snapshots/ (kept out of git).
     */
    public function snapshot(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        $directory = STORAGE_PATH . '/reskin/snapshots';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->json(['ok' => false, 'error' => 'Dossier de sauvegarde inaccessible.'], 500);
        }

        $name = 'reglages-' . date('Ymd-His') . '.json';

        $payload = [
            'created_at' => date('c'),
            'site'       => store_name(),
            'theme'      => [
                'current' => ThemeStore::currentInfo(),
                'values'  => ThemeStore::values(),
                'library' => ThemeStore::library(),
                'applied' => ThemeStore::isApplied(),
            ],
            'content'    => SiteContent::all(),
            'shop'       => ShopSettings::values(),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false || @file_put_contents($directory . '/' . $name, $json, LOCK_EX) === false) {
            $this->json(['ok' => false, 'error' => 'Copie impossible à écrire.'], 500);
        }

        // Keep the folder tidy forever: 20 most recent copies are enough.
        $copies = glob($directory . '/reglages-*.json') ?: [];
        sort($copies);

        foreach (array_slice($copies, 0, max(0, count($copies) - 20)) as $old) {
            @unlink($old);
        }

        $this->json([
            'ok'      => true,
            'file'    => 'storage/reskin/snapshots/' . $name,
            'message' => 'Copie permanente enregistrée dans storage/reskin/snapshots/.',
        ]);
    }

    /**
     * Storefront preview. Rendered inside the RESKIN iframe, with the real
     * app.css so every control shows exactly what the shop will look like.
     */
    public function preview(): void
    {
        $this->requireAuth();
        $this->allowFraming();

        View::render('reskin/preview', [
            'title'         => 'Aperçu — RESKIN',
            'values'        => ThemeStore::values(),
            'tokenCss'      => ThemeStore::tokenCss(),
            'bodyAttrs'     => ThemeStore::bodyAttributes(),
            'appName'       => store_name(),
            'content'       => SiteContent::all(),
            'previewProducts' => $this->previewProducts(),
            'previewCategories' => $this->previewCategories(),
            'logo'          => site_logo_url(),
            'instagram'     => trim((string) \App\Models\Setting::raw('instagram_url', (string) config('instagram_url'))),
        ], 'layouts/preview');
    }

    /* ==================================================================
       Stylesheet
       ================================================================== */

    /**
     * Public CSS of the applied theme. Loaded by the storefront layout only
     * when the user explicitly clicked "Appliquer au site".
     */
    public function themeCss(): void
    {
        if (!ThemeStore::isApplied()) {
            http_response_code(404);
            header('Content-Type: text/css; charset=utf-8');
            header('Cache-Control: no-store');
            echo '/* RESKIN: no theme applied */';

            return;
        }

        $css  = ThemeStore::css();
        $etag = '"' . md5($css) . '"';

        header('Content-Type: text/css; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-cache');
        header('ETag: ' . $etag);

        if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
            http_response_code(304);

            return;
        }

        echo $css;
    }

    /**
     * Download the generated CSS, ready to paste in another project.
     */
    public function export(): void
    {
        $this->requireAuth();

        $name = 'reskin-theme-' . date('Ymd-His') . '.css';

        header('Content-Type: text/css; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        echo ThemeStore::css();

        exit;
    }

    /* ==================================================================
       Mutations (JSON)
       ================================================================== */

    public function save(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        $payload = $this->jsonInput();

        try {
            $values = ThemeStore::saveCurrent(
                $this->valuesFrom($payload),
                isset($payload['name']) ? (string) $payload['name'] : null
            );

            $content = $payload['content'] ?? [];

            if (is_array($content) && $content !== []) {
                SiteContent::applyFlat($content);
            }

            // Saving is the publishing action: write the real CSS source and
            // activate it. Nothing else rewrites this file unless the owner
            // saves (or explicitly resets) from RESKIN.
            $css = ThemeStore::writeGeneratedCss();
            ThemeStore::setApplied(true);

            $this->json([
                'ok'      => true,
                'values'  => $values,
                'content' => SiteContent::flat(),
                'info'    => ThemeStore::currentInfo(),
                'applied' => true,
                'css'     => $css,
                'message' => 'Design et contenu enregistrés dans le CSS du site.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    public function reset(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        try {
            $values = ThemeStore::resetCurrent();

            if (ThemeStore::isApplied()) {
                ThemeStore::writeGeneratedCss();
            }

            $this->json([
                'ok'      => true,
                'values'  => $values,
                'content' => SiteContent::flat(),
                'info'    => ThemeStore::currentInfo(),
                'applied' => ThemeStore::isApplied(),
                'message' => 'Design d’origine restauré. Le contenu est conservé.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    public function apply(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        try {
            ThemeStore::writeGeneratedCss();
            ThemeStore::setApplied(true);

            $this->json([
                'ok'      => true,
                'applied' => true,
                'message' => 'Thème appliqué. Le fichier CSS du site est à jour.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    public function disable(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        try {
            ThemeStore::setApplied(false);

            $this->json([
                'ok'      => true,
                'applied' => false,
                'message' => 'Thème retiré : la boutique utilise son design d’origine.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    public function saveTheme(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        $payload = $this->jsonInput();
        $name    = (string) ($payload['name'] ?? '');

        try {
            ThemeStore::saveTheme($name, $this->valuesFrom($payload));

            $this->json([
                'ok'      => true,
                'library' => $this->libraryPayload(),
                'message' => 'Thème « ' . $name . ' » enregistré.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 422);
        }
    }

    public function loadTheme(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        $payload = $this->jsonInput();
        $name    = (string) ($payload['name'] ?? '');

        try {
            $values = ThemeStore::loadTheme($name);

            $this->json([
                'ok'     => true,
                'values' => $values,
                'info'   => ThemeStore::currentInfo(),
                'message' => 'Thème « ' . $name . ' » chargé.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 404);
        }
    }

    public function deleteTheme(): void
    {
        $this->requireAuth();
        $this->requireCsrfJson();

        $payload = $this->jsonInput();
        $name    = (string) ($payload['name'] ?? '');

        try {
            ThemeStore::deleteTheme($name);

            $this->json([
                'ok'      => true,
                'library' => $this->libraryPayload(),
                'message' => 'Thème supprimé.',
            ]);
        } catch (\Throwable $exception) {
            $this->json(['ok' => false, 'error' => $exception->getMessage()], 500);
        }
    }

    /* ==================================================================
       Helpers
       ================================================================== */

    /**
     * Real catalogue products for the preview, with a safe fallback when the
     * database is not available (fresh install, maintenance).
     *
     * @return array<int, array<string, mixed>>
     */
    private function previewProducts(): array
    {
        try {
            $products = Product::featured(4);
        } catch (\Throwable) {
            $products = [];
        }

        if ($products === []) {
            $products = [
                ['id' => 1, 'title' => 'Robe longue Sultan', 'slug' => '', 'price' => 349, 'sale_price' => 199, 'stock' => 8, 'category_name' => 'Femme', 'is_flash' => 1, 'primary_image' => 'images/placeholders/robe-longue-sultan.svg'],
                ['id' => 2, 'title' => 'Pyjama satin Istanbul', 'slug' => '', 'price' => 279, 'sale_price' => 219, 'stock' => 5, 'category_name' => 'Pyjamas', 'is_flash' => 0, 'primary_image' => 'images/placeholders/ensemble-pyjama-satin-istanbul.svg'],
                ['id' => 3, 'title' => 'Trench automne', 'slug' => '', 'price' => 499, 'sale_price' => null, 'stock' => 12, 'category_name' => 'Femme', 'is_flash' => 0, 'primary_image' => 'images/placeholders/trench-automne.svg'],
                ['id' => 4, 'title' => 'T-shirt coton douceur', 'slug' => '', 'price' => 129, 'sale_price' => 99, 'stock' => 3, 'category_name' => 'Enfants', 'is_flash' => 0, 'primary_image' => 'images/placeholders/t-shirt-coton-douceur.svg'],
            ];
        }

        return $products;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function previewCategories(): array
    {
        try {
            $categories = Category::all();
        } catch (\Throwable) {
            $categories = [];
        }

        if ($categories === []) {
            $categories = [
                ['name' => 'Femme', 'slug' => 'femme', 'icon' => 'dress'],
                ['name' => 'Enfants', 'slug' => 'enfants', 'icon' => 'users'],
                ['name' => 'Pyjamas', 'slug' => 'pyjamas', 'icon' => 'moon'],
                ['name' => 'Robes', 'slug' => 'robes', 'icon' => 'shirt'],
            ];
        }

        return $categories;
    }

    private function isLoggedIn(): bool
    {
        $reskin = Session::get(self::SESSION_KEY);

        // Owner-only. The client admin session deliberately does NOT open
        // RESKIN: products/categories and branding are separate jobs.
        return is_array($reskin) && !empty($reskin['at']);
    }

    /**
     * Replace every `options_source => links` field with a dropdown built
     * from the real shop: fixed pages and active categories. Products are
     * deliberately excluded so the list stays short and easy to scan.
     *
     * @return array<string, mixed>
     */
    private function schemaWithLinks(): array
    {
        $schema  = ThemeStore::schema();
        $options = null;

        foreach (($schema['categories'] ?? []) as $categoryKey => $category) {
            foreach (($category['controls'] ?? []) as $controlKey => $control) {
                if (($control['options_source'] ?? '') !== 'links') {
                    continue;
                }

                if ($options === null) {
                    $options = $this->linkOptions();
                }

                $schema['categories'][$categoryKey]['controls'][$controlKey]['type'] = 'select';
                $schema['categories'][$categoryKey]['controls'][$controlKey]['options'] = $options;
            }
        }

        return $schema;
    }

    /**
     * @return array<string, string>
     */
    private function linkOptions(): array
    {
        $options = [
            ''           => 'Aucun lien (bouton inactif)',
            '/'          => 'Accueil',
            '/produits'  => 'Tous les produits',
            '/promotions' => 'Promotions',
            '/favoris'   => 'Mes favoris',
            '/panier'    => 'Mon panier',
        ];

        try {
            foreach (Category::all() as $category) {
                $slug = trim((string) ($category['slug'] ?? ''));

                if ($slug === '') {
                    continue;
                }

                $options['/categorie/' . $slug] = 'Catégorie — ' . (string) ($category['name'] ?? $slug);
            }
        } catch (\Throwable) {
            // The fixed pages above keep the dropdown usable without a DB.
        }

        return $options;
    }

    private function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            redirect($this->path('connexion'));
        }
    }

    private function allowFraming(): void
    {
        // Same-origin iframe only: the dashboard embeds this page.
        header('X-Frame-Options: SAMEORIGIN');
        header(
            "Content-Security-Policy: default-src 'self'; "
            . "img-src 'self' data:; "
            . "style-src 'self' 'unsafe-inline'; "
            . "script-src 'none'; "
            . "font-src 'self'; "
            . "form-action 'self'; "
            . "base-uri 'self'; "
            . "object-src 'none'; "
            . "frame-ancestors 'self'"
        );
    }

    private function requireCsrfJson(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null);

        if (!csrf_verify($token)) {
            $this->json(['ok' => false, 'error' => 'Session expirée, rechargez la page.'], 403);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonInput(): array
    {
        $raw  = (string) file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function valuesFrom(array $payload): array
    {
        $values = $payload['values'] ?? [];

        return is_array($values) ? $values : [];
    }

    /**
     * @return array<string, array{values: array<string, string>, saved_at: int}>
     */
    private function libraryPayload(): array
    {
        $library = ThemeStore::library();
        $payload = [];

        foreach ($library as $name => $theme) {
            $payload[$name] = [
                'saved_at' => (int) $theme['saved_at'],
                'colors'   => [
                    'primary'    => $theme['values']['primary'] ?? '',
                    'accent'     => $theme['values']['accent'] ?? '',
                    'background' => $theme['values']['background'] ?? '',
                ],
            ];
        }

        return $payload;
    }

    private function path(string $suffix = ''): string
    {
        $base = '/' . trim((string) config('reskin_path', 'reskin'), '/');

        return $suffix !== '' ? $base . '/' . ltrim($suffix, '/') : $base;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        exit;
    }
}
