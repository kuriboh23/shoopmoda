<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SiteContent;

/**
 * Store admin panel.
 *
 * Single-password access configured through ADMIN_EMAIL / ADMIN_PASSWORD.
 * Everything the owner touches daily is reachable in one tap on a phone:
 * dashboard, products, categories, settings.
 */
final class AdminController
{
    private const SESSION_KEY = 'admin';

    /**
     * Icons the owner can attach to a category. The value is the icon name
     * understood by app/helpers/icons.php.
     */
    private const CATEGORY_ICONS = [
        'shirt'  => 'Vêtement',
        'dress'  => 'Robe',
        'jacket' => 'Veste',
        'moon'   => 'Pyjama / nuit',
        'users'  => 'Enfants',
        'shoe'   => 'Chaussure',
        'tag'    => 'Étiquette',
        'gift'   => 'Cadeau',
        'star'   => 'Étoile',
        'home'   => 'Maison',
    ];

    /* ------------------------------------------------------------------
       Authentication
       ------------------------------------------------------------------ */

    public function loginForm(): void
    {
        if ($this->isLoggedIn()) {
            redirect($this->basePath());
        }

        View::render('admin/login', [
            'title'     => 'Connexion — Gestion ' . config('app_name'),
            'adminAuth' => true,
            'noindex'   => true,
            'email'     => '',
            'error'     => null,
        ], 'layouts/admin');
    }

    public function login(): void
    {
        $email    = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        $expectedEmail    = mb_strtolower(trim((string) config('admin_email')));
        $expectedPassword = (string) config('admin_password');

        $valid = csrf_verify($_POST['_csrf'] ?? null)
            && $expectedPassword !== ''
            && hash_equals($expectedEmail, $email)
            && hash_equals($expectedPassword, $password);

        if (!$valid) {
            usleep(300000);

            http_response_code(422);

            View::render('admin/login', [
                'title'     => 'Connexion — Gestion ' . config('app_name'),
                'adminAuth' => true,
                'noindex'   => true,
                'email'     => $email,
                'error'     => 'Email ou mot de passe incorrect.',
            ], 'layouts/admin');

            return;
        }

        Session::start();
        session_regenerate_id(true);
        Session::set(self::SESSION_KEY, ['email' => $expectedEmail, 'at' => time()]);

        flash('success', 'Bienvenue dans votre espace de gestion.');
        redirect($this->basePath());
    }

    public function logout(): void
    {
        $this->requireCsrf($this->basePath());

        Session::remove(self::SESSION_KEY);
        Session::start();
        session_regenerate_id(true);

        redirect($this->path('connexion'));
    }

    /* ------------------------------------------------------------------
       Dashboard
       ------------------------------------------------------------------ */

    public function dashboard(): void
    {
        $this->requireAdmin();

        View::render('admin/dashboard', [
            'title'       => 'Tableau de bord — Gestion ' . config('app_name'),
            'adminActive' => 'dashboard',
            'adminTitle'  => 'Tableau de bord',
            'noindex'     => true,
            'stats'       => Product::adminStats(),
            'recent'      => Product::adminAll('', 5),
            'categories'  => Category::adminAll(),
        ], 'layouts/admin');
    }

    /* ------------------------------------------------------------------
       Products
       ------------------------------------------------------------------ */

    public function products(): void
    {
        $this->requireAdmin();

        $search = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 60);

        View::render('admin/products/index', [
            'title'       => 'Produits — Gestion ' . config('app_name'),
            'adminActive' => 'products',
            'adminTitle'  => 'Produits',
            'noindex'     => true,
            'products'    => Product::adminAll($search, 100),
            'search'      => $search,
        ], 'layouts/admin');
    }

    public function createForm(): void
    {
        $this->requireAdmin();

        $this->renderForm($this->emptyProduct(), [], 'create');
    }

    public function create(): void
    {
        $this->requireAdmin();
        $this->saveProduct(null);
    }

    public function editForm(string $id): void
    {
        $this->requireAdmin();

        $product = Product::adminFind((int) $id);

        if ($product === null) {
            $this->notFound();

            return;
        }

        $this->renderForm($product, [], 'edit');
    }

    public function update(string $id): void
    {
        $this->requireAdmin();

        $product = Product::adminFind((int) $id);

        if ($product === null) {
            $this->notFound();

            return;
        }

        $this->saveProduct($product);
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf($this->path('produits'));

        Product::delete((int) $id);

        flash('success', 'Produit supprimé.');
        redirect($this->path('produits'));
    }

    /**
     * Shared create/update handler.
     *
     * @param array<string, mixed>|null $existing
     */
    private function saveProduct(?array $existing): void
    {
        $id = $existing !== null ? (int) $existing['id'] : null;

        $this->requireCsrf($id !== null ? $this->path('produits/' . $id) : $this->path('produits/nouveau'));

        $post = $_POST;

        $values = [
            'id'           => $id ?? 0,
            'title'        => trim((string) ($post['title'] ?? '')),
            'slug'         => trim((string) ($post['slug'] ?? '')),
            'description'  => trim((string) ($post['description'] ?? '')),
            'price'        => trim((string) ($post['price'] ?? '')),
            'sale_price'   => trim((string) ($post['sale_price'] ?? '')),
            'stock'        => trim((string) ($post['stock'] ?? '10')),
            'sizes'        => trim((string) ($post['sizes'] ?? '')),
            'colors'       => trim((string) ($post['colors'] ?? '')),
            'category_id'  => (int) ($post['category_id'] ?? 0),
            'is_active'    => isset($post['is_active']) ? 1 : 0,
            'is_featured'  => isset($post['is_featured']) ? 1 : 0,
            'is_flash'     => isset($post['is_flash']) ? 1 : 0,
            'primary_image' => $existing['primary_image'] ?? null,
        ];

        $errors = [];

        if (mb_strlen($values['title']) < 2) {
            $errors['title'] = 'Le titre doit contenir au moins 2 caractères.';
        }

        if (!is_numeric($values['price']) || (float) $values['price'] <= 0) {
            $errors['price'] = 'Indiquez un prix supérieur à 0.';
        }

        if ($values['sale_price'] !== '') {
            if (!is_numeric($values['sale_price']) || (float) $values['sale_price'] <= 0) {
                $errors['sale_price'] = 'Prix promo invalide.';
            } elseif (is_numeric($values['price']) && (float) $values['sale_price'] >= (float) $values['price']) {
                $errors['sale_price'] = 'Le prix promo doit être inférieur au prix normal.';
            }
        }

        if (!ctype_digit($values['stock'])) {
            $errors['stock'] = 'Le stock doit être un nombre entier positif.';
        }

        $categoryIds = array_map(
            static fn (array $category): int => (int) $category['id'],
            Category::adminAll()
        );

        if ($values['category_id'] !== 0 && !in_array($values['category_id'], $categoryIds, true)) {
            $errors['category_id'] = 'Catégorie inconnue.';
        }

        [$imagePath, $imageError] = $this->handleUpload();

        if ($imageError !== null) {
            $errors['image'] = $imageError;
        }

        if ($errors !== []) {
            http_response_code(422);

            $values['sizes']  = (string) $values['sizes'];
            $values['colors'] = (string) $values['colors'];

            $this->renderForm($values, $errors, $id !== null ? 'edit' : 'create');

            return;
        }

        $slugSource = $values['slug'] !== '' ? $values['slug'] : $values['title'];
        $slug       = $this->uniqueSlug($slugSource, $id);

        $data = [
            'category_id' => $values['category_id'],
            'title'       => $values['title'],
            'slug'        => $slug,
            'description' => $values['description'],
            'price'       => number_format((float) $values['price'], 2, '.', ''),
            'sale_price'  => $values['sale_price'] !== ''
                ? number_format((float) $values['sale_price'], 2, '.', '')
                : null,
            'stock'       => (int) $values['stock'],
            'sizes'       => $this->sizesJson($values['sizes']),
            'colors'      => $this->colorsJson($values['colors']),
            'is_flash'    => $values['is_flash'],
            'is_featured' => $values['is_featured'],
            'is_active'   => $values['is_active'],
        ];

        if ($id === null) {
            $id = Product::create($data);
        } else {
            Product::update($id, $data);
        }

        if ($imagePath !== null) {
            Product::setPrimaryImage($id, $imagePath);
        }

        flash('success', $existing !== null ? 'Produit mis à jour.' : 'Produit créé.');
        redirect($this->path('produits'));
    }

    /**
     * @param array<string, mixed> $product
     * @param array<string, string> $errors
     */
    private function renderForm(array $product, array $errors, string $mode): void
    {
        View::render('admin/products/form', [
            'title'       => ($mode === 'edit' ? 'Modifier' : 'Ajouter') . ' un produit — Gestion ' . config('app_name'),
            'adminActive' => 'products',
            'adminTitle'  => $mode === 'edit' ? 'Modifier le produit' : 'Nouveau produit',
            'noindex'     => true,
            'product'     => $product,
            'errors'      => $errors,
            'mode'        => $mode,
            'categories'  => Category::adminAll(),
        ], 'layouts/admin');
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyProduct(): array
    {
        return [
            'id'            => 0,
            'title'         => '',
            'slug'          => '',
            'description'   => '',
            'price'         => '',
            'sale_price'    => '',
            'stock'         => '10',
            'sizes'         => '',
            'colors'        => '',
            'category_id'   => 0,
            'is_active'     => 1,
            'is_featured'   => 0,
            'is_flash'      => 0,
            'primary_image' => null,
        ];
    }

    private function uniqueSlug(string $source, ?int $exceptId): string
    {
        $base      = Product::slugify($source);
        $candidate = $base;
        $suffix    = 2;

        while (Product::slugExists($candidate, $exceptId)) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function sizesJson(string $raw): string
    {
        $parts = preg_split('/[,;\n]+/', $raw) ?: [];
        $sizes = [];

        foreach ($parts as $part) {
            $part = mb_substr(trim($part), 0, 20);

            if ($part !== '' && !in_array($part, $sizes, true)) {
                $sizes[] = $part;
            }
        }

        return json_encode($sizes, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    private function colorsJson(string $raw): string
    {
        $parts  = preg_split('/[,;\n]+/', $raw) ?: [];
        $colors = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            $name = $part;
            $hex  = '#CCCCCC';

            if (str_contains($part, ':')) {
                [$name, $hex] = array_map('trim', explode(':', $part, 2));
            }

            $hex = preg_match('/^#?[0-9a-fA-F]{6}$/', $hex) === 1
                ? '#' . ltrim($hex, '#')
                : '#CCCCCC';

            $colors[] = ['name' => mb_substr($name, 0, 40), 'hex' => $hex];
        }

        return json_encode($colors, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function handleUpload(): array
    {
        if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        $file = $_FILES['image'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [null, 'Le téléversement a échoué, réessayez.'];
        }

        if ((int) ($file['size'] ?? 0) > 3 * 1024 * 1024) {
            return [null, 'Image trop lourde (3 Mo maximum).'];
        }

        $info = @getimagesize((string) $file['tmp_name']);

        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        if ($info === false || !isset($extensions[$info[2]])) {
            return [null, 'Format accepté : JPG, PNG ou WebP.'];
        }

        $directory = PUBLIC_PATH . '/uploads/products';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
            return [null, 'Dossier d’images inaccessible.'];
        }

        $filename = 'p' . bin2hex(random_bytes(8)) . '.' . $extensions[$info[2]];

        if (!@move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
            return [null, 'Enregistrement de l’image impossible.'];
        }

        return ['uploads/products/' . $filename, null];
    }

    /* ------------------------------------------------------------------
       Categories
       ------------------------------------------------------------------ */

    public function categories(): void
    {
        $this->requireAdmin();

        View::render('admin/categories', [
            'title'       => 'Catégories — Gestion ' . config('app_name'),
            'adminActive' => 'categories',
            'adminTitle'  => 'Catégories',
            'noindex'     => true,
            'categories'  => Category::adminAll(),
            'icons'       => self::CATEGORY_ICONS,
        ], 'layouts/admin');
    }

    public function storeCategory(): void
    {
        $this->requireAdmin();
        $this->requireCsrf($this->path('categories'));

        $name = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 100);

        if (mb_strlen($name) < 2) {
            flash('error', 'Le nom de la catégorie est trop court.');

            redirect($this->path('categories'));
        }

        $icon = (string) ($_POST['icon'] ?? '');

        if (!array_key_exists($icon, self::CATEGORY_ICONS)) {
            $icon = '';
        }

        $slugSource = trim((string) ($_POST['slug'] ?? ''));
        $base       = Product::slugify($slugSource !== '' ? $slugSource : $name);
        $slug       = $base;
        $suffix     = 2;

        while (Category::slugExists($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        Category::create(
            $name,
            $slug,
            (int) ($_POST['sort_order'] ?? 0),
            $icon,
            isset($_POST['is_active'])
        );

        flash('success', 'Catégorie ajoutée. Elle apparaît maintenant dans le menu, les chips et le pied de page.');
        redirect($this->path('categories'));
    }

    public function editCategoryForm(string $id): void
    {
        $this->requireAdmin();

        $category = Category::adminFind((int) $id);

        if ($category === null) {
            flash('error', 'Catégorie introuvable.');
            redirect($this->path('categories'));
        }

        $this->renderCategoryForm($category, []);
    }

    public function updateCategory(string $id): void
    {
        $this->requireAdmin();

        $category = Category::adminFind((int) $id);

        if ($category === null) {
            flash('error', 'Catégorie introuvable.');
            redirect($this->path('categories'));
        }

        $this->requireCsrf($this->path('categories/' . $id . '/modifier'));

        $values = [
            'id'         => (int) $id,
            'name'       => mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 100),
            'slug'       => trim((string) ($_POST['slug'] ?? '')),
            'icon'       => (string) ($_POST['icon'] ?? ''),
            'sort_order' => (string) (int) ($_POST['sort_order'] ?? 0),
            'is_active'  => isset($_POST['is_active']) ? '1' : '0',
        ];

        $errors = [];

        if (mb_strlen($values['name']) < 2) {
            $errors['name'] = 'Le nom doit contenir au moins 2 caractères.';
        }

        if (!array_key_exists($values['icon'], self::CATEGORY_ICONS)) {
            $values['icon'] = '';
        }

        $slugSource = $values['slug'] !== '' ? $values['slug'] : $values['name'];
        $slug       = Product::slugify($slugSource);

        if (Category::slugExists($slug, (int) $id)) {
            $errors['slug'] = 'Cette adresse est déjà utilisée par une autre catégorie.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->renderCategoryForm($values, $errors);

            return;
        }

        Category::update((int) $id, [
            'name'       => $values['name'],
            'slug'       => $slug,
            'icon'       => $values['icon'],
            'sort_order' => (int) $values['sort_order'],
            'is_active'  => $values['is_active'] === '1' ? 1 : 0,
        ]);

        flash('success', 'Catégorie mise à jour sur tout le site.');
        redirect($this->path('categories'));
    }

    /**
     * @param array<string, mixed>  $category
     * @param array<string, string> $errors
     */
    private function renderCategoryForm(array $category, array $errors): void
    {
        View::render('admin/category-form', [
            'title'       => 'Modifier une catégorie — Gestion ' . config('app_name'),
            'adminActive' => 'categories',
            'adminTitle'  => 'Modifier la catégorie',
            'noindex'     => true,
            'category'    => $category,
            'errors'      => $errors,
            'icons'       => self::CATEGORY_ICONS,
        ], 'layouts/admin');
    }

    public function deleteCategory(string $id): void
    {
        $this->requireAdmin();
        $this->requireCsrf($this->path('categories'));

        Category::delete((int) $id);

        flash('success', 'Catégorie supprimée. Ses produits restent en boutique, sans catégorie.');
        redirect($this->path('categories'));
    }

    /* ------------------------------------------------------------------
       Settings
       ------------------------------------------------------------------ */

    public function settings(): void
    {
        $this->requireAdmin();

        $this->renderSettings($this->settingsValues(), []);
    }

    public function saveSettings(): void
    {
        $this->requireAdmin();
        $this->requireCsrf($this->path('reglages'));

        $values = [
            'store_name'        => mb_substr(trim((string) ($_POST['store_name'] ?? '')), 0, 60),
            'whatsapp_number'   => preg_replace('/[^0-9]/', '', (string) ($_POST['whatsapp_number'] ?? '')) ?? '',
            'delivery_fee'      => trim((string) ($_POST['delivery_fee'] ?? '0')),
            'announcement_text' => mb_substr(trim((string) ($_POST['announcement_text'] ?? '')), 0, 140),
            'flash_sale_end'    => trim((string) ($_POST['flash_sale_end'] ?? '')),
            'instagram_url'     => trim((string) ($_POST['instagram_url'] ?? '')),
            'footer_description' => mb_substr(trim((string) ($_POST['footer_description'] ?? '')), 0, 300),
            'logo'              => (string) setting('site_logo', ''),
        ];

        $errors = [];

        if (mb_strlen($values['store_name']) < 2) {
            $errors['store_name'] = 'Nom de boutique trop court.';
        }

        if (strlen($values['whatsapp_number']) < 8 || strlen($values['whatsapp_number']) > 15) {
            $errors['whatsapp_number'] = 'Numéro WhatsApp invalide (indicatif inclus, sans +).';
        }

        if (!is_numeric($values['delivery_fee']) || (float) $values['delivery_fee'] < 0) {
            $errors['delivery_fee'] = 'Frais de livraison invalides.';
        }

        if ($values['flash_sale_end'] !== '' && strtotime($values['flash_sale_end']) === false) {
            $errors['flash_sale_end'] = 'Date de fin invalide.';
        }

        if ($values['instagram_url'] !== '' && (
            filter_var($values['instagram_url'], FILTER_VALIDATE_URL) === false
            || preg_match('#^https?://#i', $values['instagram_url']) !== 1
        )) {
            $errors['instagram_url'] = 'Adresse Instagram invalide (elle doit commencer par https://).';
        }

        [$logoPath, $logoError] = $this->handleBrandUpload();

        if ($logoError !== null) {
            $errors['logo'] = $logoError;
        }

        if ($logoPath !== null) {
            $this->deleteBrandFile((string) setting('site_logo', ''));
            $values['logo'] = $logoPath;
        } elseif (isset($_POST['remove_logo'])) {
            $this->deleteBrandFile($values['logo']);
            $values['logo'] = '';
        }

        if ($errors !== []) {
            // Do not keep an uploaded file that was not saved with the form.
            if ($logoPath !== null) {
                $this->deleteBrandFile($logoPath);
            }

            $values['logo'] = (string) setting('site_logo', '');

            http_response_code(422);
            $this->renderSettings($values, $errors);

            return;
        }

        Setting::put('store_name', $values['store_name']);
        Setting::put('whatsapp_number', $values['whatsapp_number']);
        Setting::put('delivery_fee', number_format((float) $values['delivery_fee'], 2, '.', ''));
        Setting::put('announcement_text', $values['announcement_text']);
        Setting::put('instagram_url', $values['instagram_url']);
        Setting::put('site_logo', $values['logo']);
        Setting::put(
            'flash_sale_end',
            $values['flash_sale_end'] !== '' ? date('Y-m-d H:i:s', (int) strtotime($values['flash_sale_end'])) : ''
        );

        SiteContent::applyFlat([
            'announcement.messages' => implode("\n", array_slice(
                SiteContent::section('announcement')['messages'] ?? [],
                1
            )),
            'footer.description'    => $values['footer_description'],
        ]);

        // The form's announcement field replaces the first message only, so
        // the other rotating messages are preserved.
        SiteContent::setFirstAnnouncement($values['announcement_text']);

        flash('success', 'Réglages enregistrés. Le site est mis à jour immédiatement.');
        redirect($this->path('reglages'));
    }

    /**
     * @return array<string, string>
     */
    private function settingsValues(): array
    {
        return [
            'store_name'        => (string) (Setting::get('store_name') ?? config('app_name')),
            'whatsapp_number'   => (string) (Setting::get('whatsapp_number') ?? config('whatsapp_number')),
            'delivery_fee'      => (string) (Setting::get('delivery_fee') ?? Setting::get('delivery_fee_economy') ?? '25'),
            'announcement_text' => SiteContent::firstAnnouncement(),
            'flash_sale_end'    => (string) (Setting::get('flash_sale_end') ?? ''),
            'instagram_url'     => (string) (Setting::raw('instagram_url', config('instagram_url') ?? '')),
            'footer_description' => (string) (SiteContent::section('footer')['description'] ?? ''),
            'logo'              => (string) (Setting::get('site_logo') ?? ''),
        ];
    }

    /**
     * @param array<string, string> $values
     * @param array<string, string> $errors
     */
    private function renderSettings(array $values, array $errors): void
    {
        View::render('admin/settings', [
            'title'       => 'Réglages — Gestion ' . config('app_name'),
            'adminActive' => 'settings',
            'adminTitle'  => 'Réglages',
            'noindex'     => true,
            'values'      => $values,
            'errors'      => $errors,
        ], 'layouts/admin');
    }

    /* ------------------------------------------------------------------
       Brand logo upload
       ------------------------------------------------------------------ */

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function handleBrandUpload(): array
    {
        if (!isset($_FILES['logo']) || ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        $file = $_FILES['logo'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [null, 'Le téléversement du logo a échoué, réessayez.'];
        }

        if ((int) ($file['size'] ?? 0) > 2 * 1024 * 1024) {
            return [null, 'Logo trop lourd (2 Mo maximum).'];
        }

        $info = @getimagesize((string) $file['tmp_name']);

        $extensions = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        if ($info === false || !isset($extensions[$info[2]])) {
            return [null, 'Format accepté : JPG, PNG ou WebP.'];
        }

        $directory = PUBLIC_PATH . '/uploads/brand';

        if (!is_dir($directory) && !@mkdir($directory, 0775, true)) {
            return [null, 'Dossier d’images inaccessible.'];
        }

        $filename = 'logo-' . bin2hex(random_bytes(8)) . '.' . $extensions[$info[2]];

        if (!@move_uploaded_file((string) $file['tmp_name'], $directory . '/' . $filename)) {
            return [null, 'Enregistrement du logo impossible.'];
        }

        return ['uploads/brand/' . $filename, null];
    }

    private function deleteBrandFile(string $path): void
    {
        if (!str_starts_with($path, 'uploads/brand/')) {
            return;
        }

        $file = PUBLIC_PATH . '/' . $path;

        if (is_file($file)) {
            @unlink($file);
        }
    }

    /* ------------------------------------------------------------------
       Helpers
       ------------------------------------------------------------------ */

    private function isLoggedIn(): bool
    {
        $admin = Session::get(self::SESSION_KEY);

        return is_array($admin) && !empty($admin['email']);
    }

    private function requireAdmin(): void
    {
        if (!$this->isLoggedIn()) {
            redirect($this->path('connexion'));
        }
    }

    private function requireCsrf(string $fallback): void
    {
        if (!csrf_verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Session expirée, merci de réessayer.');
            redirect($fallback);
        }
    }

    private function basePath(): string
    {
        return '/' . trim((string) config('admin_path'), '/');
    }

    private function path(string $suffix = ''): string
    {
        return $this->basePath() . ($suffix !== '' ? '/' . ltrim($suffix, '/') : '');
    }

    private function notFound(): void
    {
        http_response_code(404);

        View::render('errors/404', [
            'title'       => 'Produit introuvable — ' . config('app_name'),
            'description' => 'Ce produit n’existe plus.',
            'canonical'   => url('/'),
            'cartCount'   => 0,
            'wishCount'   => 0,
            'noindex'     => true,
        ]);
    }
}
