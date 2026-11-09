<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteContent;
use App\Reskin\ThemeStore;

/**
 * RESKIN — visual theme editor.
 *
 * The dashboard is protected by its own password (reskin_password in
 * config.php). An already-connected admin is accepted too, so the tool can be
 * opened from the shop administration without a second login.
 *
 * Every mutation returns JSON: the dashboard never reloads while editing.
 * The generated stylesheet is served by themeCss() and is the only public
 * route of the module.
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
            'schema'      => ThemeStore::schema(),
            'values'      => ThemeStore::values(),
            'content'     => SiteContent::flat(),
            'library'     => ThemeStore::library(),
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
            'logoutUrl'   => url($this->path('deconnexion')),
            'siteUrl'     => url('/'),
            'csrf'        => csrf_token(),
        ], 'layouts/reskin');
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

            $this->json([
                'ok'      => true,
                'values'  => $values,
                'content' => SiteContent::flat(),
                'info'    => ThemeStore::currentInfo(),
                'applied' => ThemeStore::isApplied(),
                'message' => 'Design et contenu enregistrés.',
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
            ThemeStore::setApplied(true);

            $this->json([
                'ok'      => true,
                'applied' => true,
                'message' => 'Thème appliqué à la boutique.',
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
        $admin  = Session::get('admin');

        return (is_array($reskin) && !empty($reskin['at']))
            || (is_array($admin) && !empty($admin['email']));
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
