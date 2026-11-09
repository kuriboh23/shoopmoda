<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
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
            'title'     => 'Aperçu — RESKIN',
            'values'    => ThemeStore::values(),
            'bodyAttrs' => ThemeStore::bodyAttributes(),
            'appName'   => (string) setting('store_name', (string) config('app_name')),
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

            $this->json([
                'ok'      => true,
                'values'  => $values,
                'info'    => ThemeStore::currentInfo(),
                'applied' => ThemeStore::isApplied(),
                'message' => 'Thème enregistré.',
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
                'info'    => ThemeStore::currentInfo(),
                'applied' => ThemeStore::isApplied(),
                'message' => 'Réglages d’origine restaurés.',
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
            $this->json(['ok' => false, 'error' => 'Session expirée, rechargez la page.'], 419);
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
                    'secondary'  => $theme['values']['secondary'] ?? '',
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
