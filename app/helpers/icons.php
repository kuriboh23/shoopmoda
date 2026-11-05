<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Small inline SVG icons used across the site.
 *
 * Larger, reusable icons live in app/assets/sprite.svg.
 */
final class Icons
{
    /**
     * Inline SVG markup for a named icon.
     *
     * @param string $class  Additional CSS class (e.g. 'icon icon--sm')
     */
    public static function inline(string $name, string $class = 'icon'): string
    {
        $icons = self::icons();

        if (!isset($icons[$name])) {
            $name = 'circle';
        }

        $markup = $icons[$name];

        $attrs = 'class="' . htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" ' . $attrs . '>' . $markup . '</svg>';
    }

    /**
     * @return array<string, string>
     */
    private static function icons(): array
    {
        return [
            'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
            'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
            'heart' => '<path d="M19 14c1.5-1.5 2.5-4 2.5-6.5C21.5 4 19 2 16 2c-2 0-4 1-5.5 2.5C9.5 3 8.5 2 6.5 2 4 2 1.5 4 1.5 6.5c0 2.5 1 5 2.5 6.5l7 7Z"/>',
            'cart' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
            'home' => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
            'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
            'truck' => '<path d="M1 3h15v13h-15z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
            'whatsapp' => '<path d="M17.5 2h-11a4.5 4.5 0 0 0 0 9h11a4.5 4.5 0 0 0 0-9Z"/><path d="M9 22a3 3 0 0 1-6 0"/>',
            'instagram' => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
            'magnifying-glass' => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
            'star' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
            'arrow-left' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
            'arrow-right' => '<path d="M5 12h14"/><path d="m15 19 7-7-7-7"/>',
            'shoe' => '<path d="M10 2h4v4h-4Z"/><path d="M1.5 9.5h10l1 10h4l1-10h3" />',
        ];
    }
}


