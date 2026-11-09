<?php

declare(strict_types=1);

/**
 * RESKIN — theme schema.
 *
 * This is the only file to edit when RESKIN is installed on a new client
 * website. The dashboard, the live preview, the saved theme format and the
 * exported CSS are all generated from the categories and controls below, so
 * adding a control here is enough: no other file needs to change.
 *
 * Control types
 * -------------
 *  color   : hex colour picker. `var` receives the value.
 *  range   : slider, value + `unit`. `var` receives the value.
 *  select  : dropdown. `var` receives `values[choice]` (or the choice itself
 *            when no `values` map is given). With `attr` instead of `var`,
 *            the choice is written on <body> as data-{attr}="choice".
 *  toggle  : switch. `attr` gives the attribute name, `on` / `off` its
 *            values on <body>.
 *
 * Defaults reproduce the current Shoop Moda Style design, so applying a
 * fresh theme does not change the storefront until a value is edited.
 */

return [
    'version' => 1,

    'categories' => [

        /* ------------------------------------------------------------------ */
        'colors' => [
            'label'       => 'Couleurs',
            'icon'        => 'palette',
            'description' => 'La palette générale de la boutique.',
            'controls'    => [
                'primary' => [
                    'label'   => 'Couleur principale',
                    'type'    => 'color',
                    'default' => '#A9552F',
                    'var'     => '--primary',
                    'help'    => 'Liens, accents, badges et boutons principaux.',
                    // Keeps the translucent tint used by app.css in sync.
                    'derived' => [
                        ['var' => '--primary-soft', 'alpha' => 0.10],
                    ],
                ],
                'secondary' => [
                    'label'   => 'Couleur secondaire',
                    'type'    => 'color',
                    'default' => '#1B1A18',
                    'var'     => '--rs-secondary',
                    'help'    => 'Bandeau d’annonce, pied de page et surfaces sombres.',
                ],
                'background' => [
                    'label'   => 'Fond du site',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-bg',
                    'help'    => 'Couleur de fond derrière le contenu.',
                ],
                'text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#1B1A18',
                    'var'     => '--rs-text',
                    'help'    => 'Texte courant et titres.',
                ],
                'accent' => [
                    'label'   => 'Couleur d’accent',
                    'type'    => 'color',
                    'default' => '#A9552F',
                    'var'     => '--rs-accent',
                    'help'    => 'Prix, catégories et petits rappels visuels.',
                ],
                'button_bg' => [
                    'label'   => 'Couleur des boutons',
                    'type'    => 'color',
                    'default' => '#1B1A18',
                    'var'     => '--rs-btn-bg',
                    'derived' => [
                        ['var' => '--rs-btn-soft', 'alpha' => 0.12],
                    ],
                ],
                'button_hover' => [
                    'label'   => 'Couleur au survol',
                    'type'    => 'color',
                    'default' => '#A9552F',
                    'var'     => '--rs-btn-hover',
                ],
            ],
        ],

        /* ------------------------------------------------------------------ */
        'typography' => [
            'label'       => 'Typographie',
            'icon'        => 'type',
            'description' => 'Polices, tailles et rythme de lecture.',
            'controls'    => [
                'font_family' => [
                    'label'   => 'Police',
                    'type'    => 'select',
                    'default' => 'system',
                    'var'     => '--rs-font',
                    'options' => [
                        'system'    => 'Système (rapide)',
                        'geometric' => 'Trebuchet MS',
                        'humanist'  => 'Gill Sans / Calibri',
                        'serif'     => 'Georgia (serif)',
                        'mono'      => 'Consolas (mono)',
                    ],
                    'values' => [
                        'system'    => 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
                        'geometric' => '"Trebuchet MS", "Segoe UI", system-ui, sans-serif',
                        'humanist'  => '"Gill Sans", "Segoe UI", Calibri, system-ui, sans-serif',
                        'serif'     => 'Georgia, "Times New Roman", Times, serif',
                        'mono'      => '"SFMono-Regular", Consolas, "Liberation Mono", monospace',
                    ],
                    'help' => 'Aucune police externe : le site reste rapide.',
                ],
                'heading_size' => [
                    'label'   => 'Taille des titres',
                    'type'    => 'range',
                    'default' => 17,
                    'min'     => 14,
                    'max'     => 40,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-heading-size',
                ],
                'body_size' => [
                    'label'   => 'Taille du texte',
                    'type'    => 'range',
                    'default' => 15,
                    'min'     => 12,
                    'max'     => 20,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-body-size',
                ],
                'font_weight' => [
                    'label'   => 'Graisse des titres',
                    'type'    => 'select',
                    'default' => '600',
                    'var'     => '--rs-heading-weight',
                    'options' => [
                        '400' => 'Fine (400)',
                        '500' => 'Moyenne (500)',
                        '600' => 'Semi-gras (600)',
                        '700' => 'Gras (700)',
                    ],
                ],
                'line_height' => [
                    'label'   => 'Interligne',
                    'type'    => 'range',
                    'default' => 1.5,
                    'min'     => 1.2,
                    'max'     => 1.9,
                    'step'    => 0.05,
                    'unit'    => '',
                    'var'     => '--rs-line-height',
                ],
            ],
        ],

        /* ------------------------------------------------------------------ */
        'buttons' => [
            'label'       => 'Boutons',
            'icon'        => 'cursor',
            'description' => 'Forme, couleur et réaction au survol.',
            'controls'    => [
                'button_radius' => [
                    'label'   => 'Arrondi des boutons',
                    'type'    => 'range',
                    'default' => 8,
                    'min'     => 0,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-btn-radius',
                ],
                'button_padding' => [
                    'label'   => 'Padding horizontal',
                    'type'    => 'range',
                    'default' => 20,
                    'min'     => 8,
                    'max'     => 40,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-btn-pad-x',
                ],
                'button_text' => [
                    'label'   => 'Couleur du texte des boutons',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-btn-text',
                ],
                'button_style' => [
                    'label'   => 'Style des boutons',
                    'type'    => 'select',
                    'default' => 'solid',
                    'attr'    => 'rs-button',
                    'options' => [
                        'solid'   => 'Plein',
                        'outline' => 'Contour',
                        'soft'    => 'Doux (fond léger)',
                    ],
                ],
                'button_hover_effect' => [
                    'label'   => 'Effet au survol',
                    'type'    => 'select',
                    'default' => 'none',
                    'attr'    => 'rs-btn-hover',
                    'options' => [
                        'none'  => 'Aucun (design actuel)',
                        'lift'  => 'Légère élévation',
                        'scale' => 'Agrandissement',
                        'fade'  => 'Opacité',
                    ],
                ],
            ],
        ],

        /* ------------------------------------------------------------------ */
        'layout' => [
            'label'       => 'Mise en page',
            'icon'        => 'layout',
            'description' => 'Largeur, espacements et grille produits.',
            'controls'    => [
                'container_width' => [
                    'label'   => 'Largeur du conteneur',
                    'type'    => 'range',
                    'default' => 1200,
                    'min'     => 960,
                    'max'     => 1440,
                    'step'    => 20,
                    'unit'    => 'px',
                    'var'     => '--rs-wrap',
                ],
                'section_spacing' => [
                    'label'   => 'Espace entre les sections',
                    'type'    => 'range',
                    'default' => 28,
                    'min'     => 12,
                    'max'     => 80,
                    'step'    => 2,
                    'unit'    => 'px',
                    'var'     => '--rs-section-space',
                ],
                'header_height' => [
                    'label'   => 'Hauteur de l’en-tête',
                    'type'    => 'range',
                    'default' => 64,
                    'min'     => 48,
                    'max'     => 96,
                    'step'    => 2,
                    'unit'    => 'px',
                    'var'     => '--rs-header-h',
                ],
                'border_radius' => [
                    'label'   => 'Arrondi général',
                    'type'    => 'range',
                    'default' => 12,
                    'min'     => 0,
                    'max'     => 28,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--radius',
                ],
                'card_spacing' => [
                    'label'   => 'Espacement des cartes',
                    'type'    => 'range',
                    'default' => 12,
                    'min'     => 6,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-gap',
                ],
                'grid_columns' => [
                    'label'   => 'Colonnes produits (bureau)',
                    'type'    => 'select',
                    'default' => '4',
                    'var'     => '--rs-grid-cols',
                    'options' => [
                        '2' => '2 colonnes',
                        '3' => '3 colonnes',
                        '4' => '4 colonnes',
                    ],
                ],
            ],
        ],

        /* ------------------------------------------------------------------ */
        'header' => [
            'label'       => 'En-tête & navigation',
            'icon'        => 'panel',
            'description' => 'Barre supérieure, logo et liens du menu.',
            'controls'    => [
                'header_bg' => [
                    'label'   => 'Fond de l’en-tête',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-header-bg',
                ],
                'logo_size' => [
                    'label'   => 'Taille du logo',
                    'type'    => 'range',
                    'default' => 18,
                    'min'     => 14,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-logo-size',
                ],
                'nav_color' => [
                    'label'   => 'Couleur des liens',
                    'type'    => 'color',
                    'default' => '#1B1A18',
                    'var'     => '--rs-nav-color',
                ],
                'nav_spacing' => [
                    'label'   => 'Espacement du menu',
                    'type'    => 'range',
                    'default' => 22,
                    'min'     => 8,
                    'max'     => 48,
                    'step'    => 2,
                    'unit'    => 'px',
                    'var'     => '--rs-nav-gap',
                ],
                'sticky_header' => [
                    'label'   => 'En-tête collant',
                    'type'    => 'toggle',
                    'default' => 'on',
                    'attr'    => 'rs-sticky',
                    'on'      => 'on',
                    'off'     => 'off',
                    'help'    => 'Garde le menu visible pendant le défilement.',
                ],
            ],
        ],

        /* ------------------------------------------------------------------ */
        'extras' => [
            'label'       => 'Finitions',
            'icon'        => 'sparkle',
            'description' => 'Ombres, bordures et cartes produits.',
            'controls'    => [
                'shadow' => [
                    'label'   => 'Intensité des ombres',
                    'type'    => 'select',
                    'default' => 'subtle',
                    'var'     => '--rs-shadow',
                    'options' => [
                        'none'   => 'Aucune',
                        'subtle' => 'Discrète',
                        'soft'   => 'Douce',
                        'strong' => 'Marquée',
                    ],
                    'values' => [
                        'none'   => 'none',
                        'subtle' => '0 1px 2px rgba(27, 26, 24, 0.04)',
                        'soft'   => '0 10px 28px rgba(27, 26, 24, 0.08)',
                        'strong' => '0 18px 46px rgba(27, 26, 24, 0.16)',
                    ],
                ],
                'border_color' => [
                    'label'   => 'Couleur des bordures',
                    'type'    => 'color',
                    'default' => '#E7DFD5',
                    'var'     => '--rs-border',
                ],
                'border_width' => [
                    'label'   => 'Épaisseur des bordures',
                    'type'    => 'range',
                    'default' => 1,
                    'min'     => 0,
                    'max'     => 3,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-border-width',
                ],
                'card_style' => [
                    'label'   => 'Apparence des cartes',
                    'type'    => 'select',
                    'default' => 'elevated',
                    'attr'    => 'rs-card',
                    'options' => [
                        'elevated' => 'Élévation / ombre',
                        'outlined' => 'Contour fin',
                        'flat'     => 'Plate, sans bordure',
                    ],
                ],
                'product_hover' => [
                    'label'   => 'Animation des cartes produit',
                    'type'    => 'toggle',
                    'default' => 'on',
                    'attr'    => 'rs-hover',
                    'on'      => 'on',
                    'off'     => 'off',
                    'help'    => 'Légère élévation au survol.',
                ],
                'image_radius' => [
                    'label'   => 'Arrondi des images',
                    'type'    => 'range',
                    'default' => 0,
                    'min'     => 0,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-image-radius',
                ],
            ],
        ],
    ],
];
