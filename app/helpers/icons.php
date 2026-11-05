<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Inline SVG icon library.
 *
 * Icons are rendered directly in the page: no sprite file, no network
 * request, no external icon font. Stroke icons use `currentColor` so the
 * colour is driven by CSS (`color`), filled icons (star, whatsapp, heart
 * active) use `fill: currentColor`.
 */
final class Icons
{
    /**
     * Inline SVG markup for a named icon.
     *
     * @param string $class Additional CSS class (e.g. 'icon icon--sm')
     */
    public static function inline(string $name, string $class = 'icon'): string
    {
        $icons = self::icons();

        if (!isset($icons[$name])) {
            $name = 'circle';
        }

        $inner   = $icons[$name];
        $filled  = in_array($name, ['star', 'whatsapp'], true);
        $classes = htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if ($filled) {
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" '
                . 'fill="currentColor" stroke="none" aria-hidden="true" focusable="false" class="' . $classes . '">'
                . $inner
                . '</svg>';
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" '
            . 'fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
            . 'aria-hidden="true" focusable="false" class="' . $classes . '">'
            . $inner
            . '</svg>';
    }

    public static function exists(string $name): bool
    {
        return isset(self::icons()[$name]);
    }

    /**
     * @return array<string, string>
     */
    private static function icons(): array
    {
        return [
            'menu'           => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'close'          => '<path d="M18 6 6 18M6 6l12 12"/>',
            'search'         => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
            'heart'          => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
            'cart'           => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
            'home'           => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
            'chevron-right'  => '<path d="m9 18 6-6-6-6"/>',
            'chevron-left'   => '<path d="m15 18-6-6 6-6"/>',
            'arrow-left'     => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'arrow-right'    => '<path d="M5 12h14"/><path d="m15 19 7-7-7-7"/>',
            'truck'          => '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
            'instagram'      => '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><path d="M17.5 6.5h.01"/>',
            'star'           => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
            'shield'         => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
            'users'          => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'check-circle'   => '<circle cx="12" cy="12" r="9"/><path d="m9 12 2 2 4-4"/>',
            'alert-circle'   => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
            'minus'          => '<path d="M5 12h14"/>',
            'plus'           => '<path d="M12 5v14"/><path d="M5 12h14"/>',
            'bell'           => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
            'refresh'        => '<path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
            'tag'            => '<path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><path d="M7 7h.01"/>',
            'trash'          => '<path d="M3 6h18"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
            'shirt'          => '<path d="M16 3.5 20 5l-2 5-2.5-.9V21h-7V9.1L6 10 4 5l4-1.5a4 4 0 0 0 8 0z"/>',
            'tshirt'         => '<path d="M6 3 2 6l3 4 2-1v12h10V9l2 1 3-4-4-3a5 5 0 0 1-10 0z"/>',
            'dress'          => '<path d="M9 2h6l-1.2 4.5 4.7 11.2A2.5 2.5 0 0 1 16 21H8a2.5 2.5 0 0 1-2.5-3.3L10.2 6.5 9 2z"/><path d="M9.5 6.5h5"/>',
            'jacket'         => '<path d="M8 3 4 5v5h2.5v11h4V11l1.5-1.5L13.5 11v10h4V10H20V5l-4-2-2.5 3h-3L8 3z"/>',
            'moon'           => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            'shoe'           => '<path d="M2 17h20v3H2z"/><path d="M2 17c0-3.3 2.2-5 5-5l3.5 1.8L16 12l5 3.5V17"/>',
            'box'            => '<path d="m21 8-9-5-9 5v8l9 5 9-5V8z"/><path d="m3 8 9 5 9-5"/><path d="M12 13v8"/>',
            'whatsapp'       => '<path d="M20.5 3.49A11.87 11.87 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.9c0 2.1.55 4.14 1.59 5.94L0 24l6.34-1.65a11.9 11.9 0 0 0 5.71 1.45h.01c6.55 0 11.89-5.34 11.89-11.9 0-3.18-1.24-6.17-3.45-8.41zM12.06 21.8h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.27c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.89-9.89 9.89zm5.42-7.4c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.47-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.18-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.05-.52-.1-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2-1.41.25-.69.25-1.29.18-1.41-.08-.13-.28-.2-.57-.35z"/>',
            'grid'           => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
            'settings'       => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.56V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.56 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.7 1.7 0 0 0 .34-1.87 1.7 1.7 0 0 0-1.56-1H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.56-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.7 1.7 0 0 0 1.87.34h.01a1.7 1.7 0 0 0 1-1.56V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.56 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.7 1.7 0 0 0-.34 1.87v.01a1.7 1.7 0 0 0 1.56 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.56 1z"/>',
            'logout'         => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
            'edit'           => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/>',
            'eye'            => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
            'circle'         => '<circle cx="12" cy="12" r="9"/>',
            'gift'           => '<path d="M20 12v9H4v-9"/><path d="M2 7h20v5H2z"/><path d="M12 21V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        ];
    }
}
