<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Editable storefront content.
 *
 * Every text block of the home page, the announcement bar and the footer
 * lives here. Defaults come from config.php, so an untouched installation
 * keeps exactly the same wording. Once the owner edits something from RESKIN
 * or from the admin area, the saved copy in the `settings` table wins.
 *
 * Content is stored as one JSON value, which keeps writes atomic and avoids
 * a schema migration. Values are normalised against the shape below before
 * they are rendered, so a corrupted row can never break the storefront.
 */
final class SiteContent
{
    private const SETTING_KEY = 'site_content';
    private const MAX_SLIDES = 3;
    private const MAX_CARDS = 4;
    private const MAX_MESSAGES = 8;

    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /* ==================================================================
       Reading
       ================================================================== */

    /**
     * Complete content, defaults filled in.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $defaults = self::defaults();
        $stored   = self::stored();

        foreach ($stored as $section => $value) {
            if (array_key_exists($section, $defaults) && is_array($value)) {
                $defaults[$section] = $value;
            }
        }

        return self::$data = self::normalize($defaults);
    }

    /**
     * @return array<string, mixed>
     */
    public static function section(string $name): array
    {
        $section = self::all()[$name] ?? [];

        return is_array($section) ? $section : [];
    }

    /**
     * Flat path => value map used by the RESKIN dashboard and its preview.
     *
     * @return array<string, string>
     */
    public static function flat(): array
    {
        $flat = [];

        foreach (array_keys(self::editable()) as $path) {
            $value = self::valueAt(self::all(), $path);

            if (is_array($value)) {
                $flat[$path] = implode("\n", array_map('strval', $value));
            } else {
                $flat[$path] = (string) $value;
            }
        }

        return $flat;
    }

    /**
     * First announcement message, also used by the admin settings form.
     */
    public static function firstAnnouncement(): string
    {
        return (string) (self::section('announcement')['messages'][0] ?? '');
    }

    /* ==================================================================
       Writing
       ================================================================== */

    /**
     * Apply a flat path => value map (RESKIN) and persist the result.
     *
     * Only paths returned by editable() can be written, so a crafted request
     * cannot add arbitrary keys to the stored JSON.
     *
     * @param array<string, mixed> $flat
     * @return array<string, mixed> Saved content.
     */
    public static function applyFlat(array $flat): array
    {
        $data    = self::all();
        $editable = self::editable();

        foreach ($flat as $path => $raw) {
            $path = (string) $path;

            if (!isset($editable[$path]) || !is_scalar($raw)) {
                continue;
            }

            $field = $editable[$path];

            if (($field['type'] ?? 'text') === 'lines') {
                $value = self::cleanLines((string) $raw, (int) $field['max'], (int) ($field['lines'] ?? 6));
            } elseif (($field['type'] ?? 'text') === 'enum') {
                $allowed = (array) ($field['values'] ?? []);
                $value   = in_array((string) $raw, $allowed, true)
                    ? (string) $raw
                    : (string) ($field['default'] ?? ($allowed[0] ?? ''));
            } else {
                $value = self::cleanText((string) $raw, (int) $field['max']);
            }

            self::setAt($data, $path, $value);
        }

        $data = self::normalize($data);

        self::persist($data);

        return $data;
    }

    /**
     * The admin settings form edits the first announcement message.
     */
    public static function setFirstAnnouncement(string $message): void
    {
        $message = self::cleanText($message, 140);
        $data    = self::all();

        if ($message === '') {
            return;
        }

        $data['announcement']['messages'][0] = $message;
        $data = self::normalize($data);

        self::persist($data);
    }

    /* ==================================================================
       Defaults and shape
       ================================================================== */

    /**
     * @return array<string, mixed>
     */
    private static function defaults(): array
    {
        $announcement = (array) config('announcement', []);
        $slides       = array_values((array) config('hero_slides', []));
        $whyCards     = array_values((array) config('why_us', []));
        $promo        = (array) config('promo_banner', []);

        $messages = array_values(array_filter(array_map(
            static fn (mixed $message): string => is_scalar($message) ? trim((string) $message) : '',
            (array) ($announcement['messages'] ?? [])
        )));

        // Older installations edited a single message from the admin area.
        // It still wins as the first message until content is saved from RESKIN.
        $override = trim((string) setting('announcement_text', ''));

        if ($override !== '' && !in_array($override, $messages, true)) {
            array_unshift($messages, $override);
        }

        return self::normalize([
            'announcement' => [
                'messages' => $messages,
                'location' => (string) ($announcement['location'] ?? 'Maroc'),
            ],
            'hero' => [
                'slides' => $slides,
            ],
            'offer' => [
                'badge' => (string) ($promo['badge'] ?? 'Offre limitée'),
                'title' => (string) ($promo['title'] ?? ''),
                'text'  => (string) ($promo['text'] ?? ''),
                'cta'   => (string) ($promo['cta'] ?? 'Commander'),
                'link'  => (string) ($promo['link'] ?? '/produits'),
                'countdown_label' => 'Fin de l’offre dans',
            ],
            'categories' => [
                'title'      => 'Explorer par catégorie',
                'link_label' => 'Tout voir',
            ],
            'featured' => [
                'eyebrow'    => 'Sélection de la semaine',
                'title'      => 'Nos pièces les plus demandées',
                'link_label' => 'Tout voir',
            ],
            'why' => [
                'eyebrow' => 'Nos engagements',
                'title'   => 'Pourquoi nous choisir',
                'lead'    => 'Des pièces sélectionnées avec soin, une commande simple sur WhatsApp et une livraison partout au Maroc.',
                'cards'   => $whyCards,
            ],
            'footer' => [
                'description'   => 'Mode turque premium et pyjamas pour femme et enfant au Maroc. Livraison directe à votre porte.',
                'logo_sub'      => 'Mode turque femme & enfant',
                'categories_title' => 'Catégories',
                'help_title'    => 'Aide',
                'order_title'   => 'Commande rapide',
                'pill_1'        => 'Tout le Maroc',
                'pill_2'        => 'Express 24-48h',
                'whatsapp_label' => 'Commandez directement via WhatsApp :',
                'bottom_note'   => 'Conçu pour un shopping mobile rapide au Maroc.',
            ],
        ]);
    }

    /**
     * Force the expected types, lengths and item counts.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function normalize(array $data): array
    {
        $defaults = [
            'announcement' => [
                'messages' => [],
                'location' => 'Maroc',
            ],
            'hero' => ['slides' => []],
            'offer' => ['badge' => '', 'title' => '', 'text' => '', 'cta' => '', 'link' => '', 'countdown_label' => ''],
            'categories' => ['title' => '', 'link_label' => ''],
            'featured' => ['eyebrow' => '', 'title' => '', 'link_label' => ''],
            'why' => ['eyebrow' => '', 'title' => '', 'lead' => '', 'cards' => []],
            'footer' => [
                'description' => '', 'logo_sub' => '', 'categories_title' => '',
                'help_title' => '', 'order_title' => '', 'pill_1' => '', 'pill_2' => '',
                'whatsapp_label' => '', 'bottom_note' => '',
            ],
        ];

        $data = array_replace_recursive($defaults, $data);

        $messages = array_values(array_filter(array_map(
            static fn (mixed $message): string => self::cleanText((string) (is_scalar($message) ? $message : ''), 140),
            (array) ($data['announcement']['messages'] ?? [])
        ), static fn (string $message): bool => $message !== ''));

        $messages = array_slice(array_values(array_unique($messages)), 0, self::MAX_MESSAGES);

        $slides = [];

        for ($index = 0; $index < self::MAX_SLIDES; $index++) {
            $slide = (array) ($data['hero']['slides'][$index] ?? []);

            $slides[] = [
                'eyebrow' => self::cleanText((string) ($slide['eyebrow'] ?? ''), 60),
                'title'   => self::cleanText((string) ($slide['title'] ?? ''), 100),
                'text'    => self::cleanText((string) ($slide['text'] ?? ''), 180),
                'cta'     => self::cleanText((string) ($slide['cta'] ?? 'Découvrir'), 40),
                'link'    => self::cleanLink((string) ($slide['link'] ?? '/')),
                'theme'   => ($slide['theme'] ?? 'light') === 'dark' ? 'dark' : 'light',
            ];
        }

        $cards = [];

        for ($index = 0; $index < self::MAX_CARDS; $index++) {
            $card = (array) ($data['why']['cards'][$index] ?? []);

            $cards[] = [
                'icon'  => self::cleanText((string) ($card['icon'] ?? 'shield'), 30),
                'title' => self::cleanText((string) ($card['title'] ?? ''), 80),
                'text'  => self::cleanText((string) ($card['text'] ?? ''), 160),
            ];
        }

        return [
            'announcement' => [
                'messages' => $messages,
                'location' => self::cleanText((string) ($data['announcement']['location'] ?? 'Maroc'), 40),
            ],
            'hero' => ['slides' => $slides],
            'offer' => [
                'badge' => self::cleanText((string) ($data['offer']['badge'] ?? ''), 60),
                'title' => self::cleanText((string) ($data['offer']['title'] ?? ''), 120),
                'text'  => self::cleanText((string) ($data['offer']['text'] ?? ''), 300),
                'cta'   => self::cleanText((string) ($data['offer']['cta'] ?? ''), 40),
                'link'  => self::cleanLink((string) ($data['offer']['link'] ?? '/produits')),
                'countdown_label' => self::cleanText((string) ($data['offer']['countdown_label'] ?? ''), 80),
            ],
            'categories' => [
                'title'      => self::cleanText((string) ($data['categories']['title'] ?? ''), 80),
                'link_label' => self::cleanText((string) ($data['categories']['link_label'] ?? ''), 40),
            ],
            'featured' => [
                'eyebrow'    => self::cleanText((string) ($data['featured']['eyebrow'] ?? ''), 60),
                'title'      => self::cleanText((string) ($data['featured']['title'] ?? ''), 100),
                'link_label' => self::cleanText((string) ($data['featured']['link_label'] ?? ''), 40),
            ],
            'why' => [
                'eyebrow' => self::cleanText((string) ($data['why']['eyebrow'] ?? ''), 60),
                'title'   => self::cleanText((string) ($data['why']['title'] ?? ''), 100),
                'lead'    => self::cleanText((string) ($data['why']['lead'] ?? ''), 300),
                'cards'   => $cards,
            ],
            'footer' => [
                'description'    => self::cleanText((string) ($data['footer']['description'] ?? ''), 300),
                'logo_sub'       => self::cleanText((string) ($data['footer']['logo_sub'] ?? ''), 80),
                'categories_title' => self::cleanText((string) ($data['footer']['categories_title'] ?? ''), 40),
                'help_title'     => self::cleanText((string) ($data['footer']['help_title'] ?? ''), 40),
                'order_title'    => self::cleanText((string) ($data['footer']['order_title'] ?? ''), 40),
                'pill_1'         => self::cleanText((string) ($data['footer']['pill_1'] ?? ''), 40),
                'pill_2'         => self::cleanText((string) ($data['footer']['pill_2'] ?? ''), 40),
                'whatsapp_label' => self::cleanText((string) ($data['footer']['whatsapp_label'] ?? ''), 100),
                'bottom_note'    => self::cleanText((string) ($data['footer']['bottom_note'] ?? ''), 120),
            ],
        ];
    }

    /* ==================================================================
       Editable paths
       ================================================================== */

    /**
     * Paths RESKIN is allowed to write, with their length limits.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function editable(): array
    {
        $fields = [
            'announcement.messages' => ['type' => 'lines', 'max' => 140, 'lines' => self::MAX_MESSAGES],
            'announcement.location' => ['type' => 'text', 'max' => 40],
            'offer.badge'           => ['type' => 'text', 'max' => 60],
            'offer.title'           => ['type' => 'text', 'max' => 120],
            'offer.text'            => ['type' => 'text', 'max' => 300],
            'offer.cta'             => ['type' => 'text', 'max' => 40],
            'offer.link'            => ['type' => 'text', 'max' => 200],
            'offer.countdown_label' => ['type' => 'text', 'max' => 80],
            'categories.title'      => ['type' => 'text', 'max' => 80],
            'categories.link_label' => ['type' => 'text', 'max' => 40],
            'featured.eyebrow'      => ['type' => 'text', 'max' => 60],
            'featured.title'        => ['type' => 'text', 'max' => 100],
            'featured.link_label'   => ['type' => 'text', 'max' => 40],
            'why.eyebrow'           => ['type' => 'text', 'max' => 60],
            'why.title'             => ['type' => 'text', 'max' => 100],
            'why.lead'              => ['type' => 'text', 'max' => 300],
            'footer.description'    => ['type' => 'text', 'max' => 300],
            'footer.logo_sub'       => ['type' => 'text', 'max' => 80],
            'footer.categories_title' => ['type' => 'text', 'max' => 40],
            'footer.help_title'     => ['type' => 'text', 'max' => 40],
            'footer.order_title'    => ['type' => 'text', 'max' => 40],
            'footer.pill_1'         => ['type' => 'text', 'max' => 40],
            'footer.pill_2'         => ['type' => 'text', 'max' => 40],
            'footer.whatsapp_label' => ['type' => 'text', 'max' => 100],
            'footer.bottom_note'    => ['type' => 'text', 'max' => 120],
        ];

        for ($index = 0; $index < self::MAX_SLIDES; $index++) {
            $prefix = 'hero.slides.' . $index . '.';

            $fields[$prefix . 'eyebrow'] = ['type' => 'text', 'max' => 60];
            $fields[$prefix . 'title']   = ['type' => 'text', 'max' => 100];
            $fields[$prefix . 'text']    = ['type' => 'text', 'max' => 180];
            $fields[$prefix . 'cta']     = ['type' => 'text', 'max' => 40];
            $fields[$prefix . 'link']    = ['type' => 'text', 'max' => 200];
            $fields[$prefix . 'theme']   = [
                'type'    => 'enum',
                'max'     => 10,
                'values'  => ['light', 'dark'],
                'default' => 'light',
            ];
        }

        for ($index = 0; $index < self::MAX_CARDS; $index++) {
            $prefix = 'why.cards.' . $index . '.';

            $fields[$prefix . 'title'] = ['type' => 'text', 'max' => 80];
            $fields[$prefix . 'text']  = ['type' => 'text', 'max' => 160];
        }

        return $fields;
    }

    /* ==================================================================
       Storage helpers
       ================================================================== */

    /**
     * @return array<string, mixed>
     */
    private static function stored(): array
    {
        $json = setting(self::SETTING_KEY);

        if ($json === null || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function persist(array $data): void
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new \RuntimeException('Contenu impossible à sérialiser.');
        }

        Setting::put(self::SETTING_KEY, $json);

        self::$data = $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function valueAt(array $data, string $path): mixed
    {
        foreach (explode('.', $path) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return '';
            }

            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function setAt(array &$data, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $cursor   = &$data;

        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $cursor[$segment] = $value;

                return;
            }

            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }
    }

    /**
     * Remove tags and control characters, trim, then cut to a byte-safe length.
     */
    private static function cleanText(string $value, int $max): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return mb_substr($value, 0, max(1, $max));
    }

    /**
     * One line per message, duplicates removed.
     *
     * @return array<int, string>
     */
    private static function cleanLines(string $value, int $max, int $maxLines): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        $clean = [];

        foreach ($lines as $line) {
            $line = self::cleanText($line, $max);

            if ($line !== '' && !in_array($line, $clean, true)) {
                $clean[] = $line;
            }

            if (count($clean) >= max(1, $maxLines)) {
                break;
            }
        }

        return $clean;
    }

    /**
     * Site-relative path or absolute http(s) URL, nothing else.
     */
    private static function cleanLink(string $value): string
    {
        $value = trim(strip_tags($value));

        if ($value === '') {
            return '/';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return mb_substr($value, 0, 200);
        }

        return '/' . ltrim(mb_substr($value, 0, 200), '/');
    }
}
