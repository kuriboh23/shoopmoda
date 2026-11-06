<?php

declare(strict_types=1);

/**
 * Generates the demo (SVG) images of the catalogue.
 *
 *   php tools/make-placeholders.php
 *
 * Every product gets two minimalist images in the brand colours, to be
 * replaced later by real photos from the admin area.
 */

$products = [
    ['slug' => 'ensemble-pyjama-satin-istanbul', 'word' => 'Istanbul'],
    ['slug' => 'pyjama-coton-bosphore', 'word' => 'Bosphore'],
    ['slug' => 'robe-longue-sultan', 'word' => 'Sultan'],
    ['slug' => 'robe-plissee-elegance', 'word' => 'Élégance'],
    ['slug' => 'chemise-soie-perle', 'word' => 'Perle'],
    ['slug' => 'pantalon-taille-haute-moda', 'word' => 'Moda'],
    ['slug' => 'trench-automne', 'word' => 'Automne'],
    ['slug' => 't-shirt-coton-douceur', 'word' => 'Douceur'],
    ['slug' => 'ensemble-enfant-petit-prince', 'word' => 'Prince'],
    ['slug' => 'pyjama-enfant-etoile', 'word' => 'Étoile'],
    ['slug' => 'robe-enfant-fleur', 'word' => 'Fleur'],
    ['slug' => 'ensemble-famille-cocooning', 'word' => 'Cocooning'],
];

$target = dirname(__DIR__) . '/assets/images/placeholders';

if (!is_dir($target) && !mkdir($target, 0775, true) && !is_dir($target)) {
    fwrite(STDERR, "Cannot create {$target}\n");
    exit(1);
}

/**
 * @param array{slug: string, word: string} $product
 */
function placeholder(array $product, int $variant): string
{
    $word      = mb_strtoupper($product['word'], 'UTF-8');
    $initial   = mb_substr($word, 0, 1, 'UTF-8');
    $label      = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
    $alt        = htmlspecialchars('Visuel de démonstration — ' . $product['word'], ENT_QUOTES, 'UTF-8');

    // Two different compositions for the product gallery.
    if ($variant === 1) {
        $circleA = '<circle cx="650" cy="170" r="190" fill="#704F38" opacity="0.09"/>'
            . '<circle cx="110" cy="890" r="150" fill="#C2A691" opacity="0.20"/>'
            . '<circle cx="400" cy="380" r="230" fill="#FFFFFF" opacity="0.55"/>';
        $frame = '<rect x="56" y="56" width="688" height="888" rx="10" fill="none" stroke="#704F38" stroke-opacity="0.14"/>';
        $nameY = 560;
    } else {
        $circleA = '<circle cx="140" cy="210" r="170" fill="#C2A691" opacity="0.22"/>'
            . '<circle cx="690" cy="820" r="210" fill="#704F38" opacity="0.08"/>';
        $frame = '<rect x="56" y="56" width="688" height="888" rx="10" fill="none" stroke="#704F38" stroke-opacity="0.14"/>'
            . '<line x1="400" y1="120" x2="400" y2="300" stroke="#704F38" stroke-opacity="0.18" stroke-width="1"/>';
        $nameY = 600;
    }

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000" role="img" aria-label="{$alt}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#F8F5F1"/>
      <stop offset="1" stop-color="#E7E2DB"/>
    </linearGradient>
  </defs>
  <rect width="800" height="1000" fill="url(#bg)"/>
  {$circleA}
  {$frame}
  <text x="400" y="470" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif"
        font-size="170" font-weight="700" fill="#704F38">{$initial}</text>
  <text x="400" y="{$nameY}" text-anchor="middle" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif"
        font-size="24" letter-spacing="9" fill="#797979">{$label}</text>
  <line x1="330" y1="{$nameY}" x2="470" y2="{$nameY}" stroke="#704F38" stroke-opacity="0.25" transform="translate(0 34)"/>
  <text x="400" y="905" text-anchor="middle" font-family="system-ui, -apple-system, 'Segoe UI', sans-serif"
        font-size="14" letter-spacing="6" fill="#797979" opacity="0.8">SHOOP MODA STYLE</text>
</svg>

SVG;
}

$count = 0;

foreach ($products as $product) {
    foreach ([1, 2] as $variant) {
        $suffix = $variant === 1 ? '' : '-2';
        $file   = $target . '/' . $product['slug'] . $suffix . '.svg';

        if (file_put_contents($file, placeholder($product, $variant)) === false) {
            fwrite(STDERR, "Failed: {$file}\n");
            exit(1);
        }

        $count++;
    }
}

echo "{$count} images written to assets/images/placeholders/\n";
