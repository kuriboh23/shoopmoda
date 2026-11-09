<?php

declare(strict_types=1);

namespace App\Reskin;

/**
 * RESKIN — storage and CSS generation.
 *
 * All theme data lives in one JSON file under storage/, which is blocked by
 * .htaccess on the live site. No database table, no migration: drop the
 * module in a client website and it works.
 *
 * Values are always validated against app/reskin/schema.php before they are
 * saved or written into CSS, so a crafted request cannot inject rules.
 */
final class ThemeStore
{
    private const FILE_NAME = 'themes.json';
    private const MAX_THEMES = 30;

    /** @var array<string, mixed>|null */
    private static ?array $schema = null;

    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /* ==================================================================
       Schema
       ================================================================== */

    /**
     * @return array<string, mixed>
     */
    public static function schema(): array
    {
        if (self::$schema === null) {
            $file = __DIR__ . '/schema.php';
            $data = is_file($file) ? require $file : [];

            self::$schema = is_array($data) ? $data : ['categories' => []];
        }

        return self::$schema;
    }

    /**
     * Flat control list: key => control definition.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function controls(): array
    {
        $controls = [];

        foreach (self::schema()['categories'] ?? [] as $category) {
            foreach ($category['controls'] ?? [] as $key => $control) {
                $controls[(string) $key] = (array) $control;
            }
        }

        return $controls;
    }

    /**
     * @return array<string, string|float|int>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::controls() as $key => $control) {
            $defaults[$key] = $control['default'] ?? '';
        }

        return $defaults;
    }

    /* ==================================================================
       Validation
       ================================================================== */

    /**
     * Keep only known controls, coerce every value, clamp numbers.
     *
     * @param array<string, mixed> $values
     * @return array<string, string>
     */
    public static function normalize(array $values): array
    {
        $clean = [];

        foreach (self::controls() as $key => $control) {
            $raw = $values[$key] ?? $control['default'] ?? '';
            $raw = is_scalar($raw) ? (string) $raw : '';

            $clean[$key] = match ((string) ($control['type'] ?? '')) {
                'color'  => self::normalizeColor($raw, (string) ($control['default'] ?? '#000000')),
                'range'  => self::normalizeRange($raw, $control),
                'select' => self::normalizeSelect($raw, $control),
                'toggle' => in_array($raw, [(string) ($control['on'] ?? 'on'), (string) ($control['off'] ?? 'off')], true)
                    ? $raw
                    : (string) ($control['default'] ?? 'on'),
                default  => (string) ($control['default'] ?? ''),
            };
        }

        return $clean;
    }

    private static function normalizeColor(string $value, string $fallback): string
    {
        $value = trim($value);

        if (preg_match('/^#?([0-9a-fA-F]{3})$/', $value, $matches) === 1) {
            $value = '#' . $matches[1][0] . $matches[1][0]
                . $matches[1][1] . $matches[1][1]
                . $matches[1][2] . $matches[1][2];
        }

        if (preg_match('/^#?([0-9a-fA-F]{6})$/', $value, $matches) === 1) {
            return '#' . strtoupper($matches[1]);
        }

        return strtoupper($fallback);
    }

    /**
     * @param array<string, mixed> $control
     */
    private static function normalizeRange(string $raw, array $control): string
    {
        $value = is_numeric($raw) ? (float) $raw : (float) ($control['default'] ?? 0);
        $min   = (float) ($control['min'] ?? 0);
        $max   = (float) ($control['max'] ?? 100);
        $step  = (float) ($control['step'] ?? 1);

        $value = max($min, min($max, $value));

        if ($step > 0) {
            $value = round($value / $step) * $step;
        }

        $decimals = $step > 0 && floor($step) !== $step ? 2 : 0;

        return self::trimNumber(number_format($value, $decimals, '.', ''));
    }

    private static function trimNumber(string $number): string
    {
        if (!str_contains($number, '.')) {
            return $number;
        }

        return rtrim(rtrim($number, '0'), '.');
    }

    /**
     * @param array<string, mixed> $control
     */
    private static function normalizeSelect(string $raw, array $control): string
    {
        $options = (array) ($control['options'] ?? []);

        if (array_key_exists($raw, $options)) {
            return $raw;
        }

        $default = (string) ($control['default'] ?? '');

        return array_key_exists($default, $options) ? $default : (string) array_key_first($options);
    }

    /* ==================================================================
       Theme file
       ================================================================== */

    /**
     * @return array{current: array{name: string, values: array<string, string>, updated_at: int}, library: array<string, array{values: array<string, string>, saved_at: int}>, applied: bool}
     */
    private static function read(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $empty = [
            'current' => ['name' => 'Mon thème', 'values' => self::defaults(), 'updated_at' => 0],
            'library' => [],
            'applied' => false,
        ];

        $file = self::file();

        if (!is_file($file)) {
            return self::$data = $empty;
        }

        $decoded = json_decode((string) @file_get_contents($file), true);

        if (!is_array($decoded)) {
            return self::$data = $empty;
        }

        $library = [];

        foreach ((array) ($decoded['library'] ?? []) as $name => $theme) {
            $name = self::cleanName((string) $name);

            if ($name === '' || !is_array($theme)) {
                continue;
            }

            $library[$name] = [
                'values'   => self::normalize((array) ($theme['values'] ?? [])),
                'saved_at' => (int) ($theme['saved_at'] ?? 0),
            ];
        }

        self::$data = [
            'current' => [
                'name'       => self::cleanName((string) ($decoded['current']['name'] ?? 'Mon thème')),
                'values'     => self::normalize((array) ($decoded['current']['values'] ?? [])),
                'updated_at' => (int) ($decoded['current']['updated_at'] ?? 0),
            ],
            'library' => $library,
            'applied' => (bool) ($decoded['applied'] ?? false),
        ];

        return self::$data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function write(array $data): void
    {
        $directory = dirname(self::file());

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Dossier de stockage RESKIN inaccessible.');
        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            throw new \RuntimeException('Thème impossible à sérialiser.');
        }

        if (@file_put_contents(self::file(), $json, LOCK_EX) === false) {
            throw new \RuntimeException('Enregistrement du thème impossible (droits du dossier storage).');
        }

        self::$data = $data;
    }

    private static function file(): string
    {
        $root = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(__DIR__, 2) . '/storage';

        return $root . '/reskin/' . self::FILE_NAME;
    }

    private static function cleanName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        return mb_substr($name, 0, 40);
    }

    /* ==================================================================
       Public API
       ================================================================== */

    /**
     * @return array<string, string>
     */
    public static function values(): array
    {
        return self::read()['current']['values'];
    }

    /**
     * @return array{name: string, updated_at: int}
     */
    public static function currentInfo(): array
    {
        $current = self::read()['current'];

        return ['name' => $current['name'], 'updated_at' => $current['updated_at']];
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function saveCurrent(array $values, ?string $name = null): array
    {
        $data = self::read();
        $name = $name !== null ? self::cleanName($name) : $data['current']['name'];

        $data['current'] = [
            'name'       => $name !== '' ? $name : 'Mon thème',
            'values'     => self::normalize($values),
            'updated_at' => time(),
        ];

        self::write($data);

        return $data['current']['values'];
    }

    /**
     * Restore the design shipped with the schema.
     */
    public static function resetCurrent(): array
    {
        return self::saveCurrent(self::defaults());
    }

    public static function setApplied(bool $applied): void
    {
        $data = self::read();
        $data['applied'] = $applied;

        self::write($data);
    }

    public static function isApplied(): bool
    {
        return self::read()['applied'];
    }

    /**
     * Attributes written on <body> so the skin can react to select/toggle
     * controls without inline styles.
     */
    public static function bodyAttributes(): string
    {
        $values = self::values();
        $parts  = [];

        foreach (self::controls() as $key => $control) {
            $attr = (string) ($control['attr'] ?? '');

            if ($attr === '') {
                continue;
            }

            if (($control['type'] ?? '') === 'toggle') {
                $value = $values[$key] ?? ($control['default'] ?? 'on');
            } elseif (($control['type'] ?? '') === 'select') {
                $value = $values[$key] ?? ($control['default'] ?? '');
            } else {
                continue;
            }

            $parts[] = 'data-' . $attr . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return implode(' ', $parts);
    }

    /**
     * @return array<string, array{values: array<string, string>, saved_at: int}>
     */
    public static function library(): array
    {
        return self::read()['library'];
    }

    /**
     * @param array<string, mixed> $values
     */
    public static function saveTheme(string $name, array $values): void
    {
        $name = self::cleanName($name);

        if ($name === '') {
            throw new \InvalidArgumentException('Donnez un nom au thème.');
        }

        $data = self::read();

        if (count($data['library']) >= self::MAX_THEMES && !isset($data['library'][$name])) {
            throw new \RuntimeException('Limite de ' . self::MAX_THEMES . ' thèmes atteinte.');
        }

        $data['library'][$name] = [
            'values'   => self::normalize($values),
            'saved_at' => time(),
        ];

        self::write($data);
    }

    /**
     * Load a saved theme into the current draft and return its values.
     *
     * @return array<string, string>
     */
    public static function loadTheme(string $name): array
    {
        $name  = self::cleanName($name);
        $data  = self::read();
        $theme = $data['library'][$name] ?? null;

        if (!is_array($theme)) {
            throw new \InvalidArgumentException('Thème introuvable.');
        }

        $data['current'] = [
            'name'       => $name,
            'values'     => $theme['values'],
            'updated_at' => time(),
        ];

        self::write($data);

        return $theme['values'];
    }

    public static function deleteTheme(string $name): void
    {
        $name = self::cleanName($name);
        $data = self::read();

        unset($data['library'][$name]);

        self::write($data);
    }

    /* ==================================================================
       CSS generation
       ================================================================== */

    /**
     * Full stylesheet: validated tokens + the static skin rules.
     *
     * @param array<string, mixed>|null $values
     */
    public static function css(?array $values = null): string
    {
        $values = self::normalize($values ?? self::values());
        $tokens = [];

        foreach (self::controls() as $key => $control) {
            $var = (string) ($control['var'] ?? '');

            if ($var === '') {
                continue;
            }

            $value = $values[$key] ?? '';

            if (($control['type'] ?? '') === 'range') {
                $value .= (string) ($control['unit'] ?? '');
            } elseif (($control['type'] ?? '') === 'select') {
                $mapped = (array) ($control['values'] ?? []);
                $value  = (string) ($mapped[$value] ?? $value);
            }

            $tokens[$var] = (string) $value;

            foreach ((array) ($control['derived'] ?? []) as $derived) {
                $derivedVar   = (string) ($derived['var'] ?? '');
                $derivedAlpha = (float) ($derived['alpha'] ?? 0.1);

                if ($derivedVar !== '') {
                    $source = ($control['type'] ?? '') === 'color'
                        ? (string) $value
                        : (string) ($values['primary'] ?? '#A9552F');

                    $tokens[$derivedVar] = self::rgba($source, $derivedAlpha);
                }
            }
        }

        $tokenBlock = ":root {\n";

        foreach ($tokens as $name => $value) {
            // Values come from the schema or from strict validated input.
            $tokenBlock .= '    ' . $name . ': ' . $value . ";\n";
        }

        $tokenBlock .= "}\n";

        $skin = '';

        if (is_file(self::skinFile())) {
            $skin = (string) file_get_contents(self::skinFile());
        }

        return "/* RESKIN — theme generated " . date('Y-m-d H:i') . " */\n"
            . "/* Modify it from the RESKIN dashboard, not by hand. */\n\n"
            . $tokenBlock
            . "\n"
            . $skin;
    }

    private static function skinFile(): string
    {
        $root = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2);

        return $root . '/assets/css/reskin-skin.css';
    }

    private static function rgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            $hex = 'A9552F';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, rtrim(rtrim(number_format($alpha, 2, '.', ''), '0'), '.'));
    }

    /**
     * URL- and cache-friendly version of the theme stylesheet.
     */
    public static function cssVersion(): string
    {
        $info = self::currentInfo();

        return (string) ($info['updated_at'] ?: 1);
    }

    /**
     * Public URL of the generated stylesheet, valid with and without
     * .htaccess (the "theme.css" file name would otherwise be mistaken for
     * a real static file by url()).
     */
    public static function cssUrl(): string
    {
        $path = '/' . trim((string) config('reskin_path', 'reskin'), '/') . '/theme.css';

        // PHP's built-in server treats a *.css URL as a static file and does
        // not run the front controller for it, so local development uses the
        // query-string form even when pretty URLs are enabled.
        if (function_exists('pretty_urls') && pretty_urls() && PHP_SAPI !== 'cli-server') {
            $url = url($path);
        } else {
            $url = rtrim(base_path(), '/') . '/?p=' . rawurlencode($path);
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'v=' . self::cssVersion();
    }
}
