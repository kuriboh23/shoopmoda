<?php

declare(strict_types=1);

/**
 * RESKIN — per-section schema.
 *
 * Every panel below styles ONE part of the storefront. Changing the header
 * does not move the footer, and each panel can also edit the words shown in
 * that section (hero slides, offer, headings, values, footer text).
 *
 * This is the only file to edit when RESKIN is installed on a new client
 * website. The dashboard, live preview, saved-theme format and exported CSS
 * are generated from it.
 *
 * Control types
 * -------------
 *  color    : hex colour picker. `var` receives the value.
 *  range    : slider, value + `unit`. `var` receives the value.
 *  select   : dropdown. `var` receives `values[choice]` (or the choice
 *             itself); with `attr`, the choice is written on <body> as
 *             data-{attr}="choice".
 *  toggle   : switch written on <body>.
 *  text     : content field. `content` is the path in SiteContent.
 *  textarea : multi-line content field (one item per line when editing lists).
 *
 * Defaults reproduce the storefront shipped with the site, so applying an
 * untouched theme changes nothing visually.
 */

return [
    'version' => 2,

    'categories' => [

        /* ================================================================== */
        'global' => [
            'label'       => 'Général',
            'icon'        => 'settings',
            'description' => 'La base commune : palette, typographie et largeur de page.',
            'target'      => '',
            'controls'    => [
                'primary' => [
                    'label'   => 'Couleur principale',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--primary',
                    'help'    => 'Accents, badges, liens et survols.',
                    'derived' => [
                        ['var' => '--primary-soft', 'alpha' => 0.08],
                        ['var' => '--rs-offer-glow', 'alpha' => 0.18],
                    ],
                ],
                'background' => [
                    'label'   => 'Fond du site',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-bg',
                ],
                'text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-text',
                ],
                'accent' => [
                    'label'   => 'Couleur d’accent',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--rs-accent',
                    'help'    => 'Sur-titres, liens « Tout voir » et catégories produits.',
                ],
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
                'heading_size' => [
                    'label'   => 'Taille des titres de section',
                    'type'    => 'range',
                    'default' => 17,
                    'min'     => 14,
                    'max'     => 40,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-heading-size',
                ],
                'heading_weight' => [
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
                'radius' => [
                    'label'   => 'Arrondi général',
                    'type'    => 'range',
                    'default' => 12,
                    'min'     => 0,
                    'max'     => 28,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--radius',
                ],
            ],
        ],

        /* ================================================================== */
        'announcement' => [
            'label'       => 'Bandeau d’annonce',
            'icon'        => 'bell',
            'description' => 'La barre défilante tout en haut, avec ses messages.',
            'target'      => 'announcement',
            'controls'    => [
                'ann_bg' => [
                    'label'   => 'Fond du bandeau',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-ann-bg',
                ],
                'ann_text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#5C5C5C',
                    'var'     => '--rs-ann-text',
                ],
                'ann_border' => [
                    'label'   => 'Couleur de la bordure',
                    'type'    => 'color',
                    'default' => '#EDEDED',
                    'var'     => '--rs-ann-border',
                ],
                'ann_height' => [
                    'label'   => 'Hauteur',
                    'type'    => 'range',
                    'default' => 40,
                    'min'     => 28,
                    'max'     => 64,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-ann-height',
                ],
                'ann_messages' => [
                    'label'   => 'Messages défilants',
                    'type'    => 'textarea',
                    'content' => 'announcement.messages',
                    'group'   => 'Contenu du bandeau',
                    'max'     => 8,
                    'help'    => 'Un message par ligne. Huit messages maximum.',
                ],
                'ann_location' => [
                    'label'   => 'Étiquette de pays',
                    'type'    => 'text',
                    'content' => 'announcement.location',
                    'max'     => 40,
                ],
            ],
        ],

        /* ================================================================== */
        'hero' => [
            'label'       => 'Hero / slider',
            'icon'        => 'layout',
            'description' => 'Le grand slider d’accueil : fonds, titres et trois slides.',
            'target'      => 'hero',
            'controls'    => [
                'hero_light_bg' => [
                    'label'   => 'Fond des slides clairs',
                    'type'    => 'color',
                    'default' => '#EDEDED',
                    'var'     => '--rs-hero-light-bg',
                ],
                'hero_dark_bg' => [
                    'label'   => 'Fond des slides foncés',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-hero-dark-bg',
                ],
                'hero_dark_bg_2' => [
                    'label'   => 'Seconde teinte (dégradé)',
                    'type'    => 'color',
                    'default' => '#343644',
                    'var'     => '--rs-hero-dark-bg-2',
                ],
                'hero_radius' => [
                    'label'   => 'Arrondi des slides',
                    'type'    => 'range',
                    'default' => 16,
                    'min'     => 0,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-hero-radius',
                ],
                'hero_title_size' => [
                    'label'   => 'Taille du titre',
                    'type'    => 'range',
                    'default' => 22,
                    'min'     => 16,
                    'max'     => 48,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-hero-title-size',
                ],
                'hero_eyebrow' => [
                    'label'   => 'Couleur du sur-titre',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--rs-hero-eyebrow',
                ],
                'hero_dark_eyebrow' => [
                    'label'   => 'Sur-titre des slides foncés',
                    'type'    => 'color',
                    'default' => '#C2A691',
                    'var'     => '--rs-hero-dark-eyebrow',
                ],
                'hero_light_title' => [
                    'label'   => 'Titre sur fond clair',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-hero-light-title',
                ],
                'hero_dark_title' => [
                    'label'   => 'Titre sur fond foncé',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-hero-dark-title',
                ],
                'hero_light_text' => [
                    'label'   => 'Texte sur fond clair',
                    'type'    => 'color',
                    'default' => '#5C5C5C',
                    'var'     => '--rs-hero-light-text',
                ],
                'hero_dark_text' => [
                    'label'   => 'Texte sur fond foncé',
                    'type'    => 'color',
                    'default' => '#C8C9D1',
                    'var'     => '--rs-hero-dark-text',
                ],
                'hero_slide_1_eyebrow' => ['label' => 'Sur-titre', 'type' => 'text', 'content' => 'hero.slides.0.eyebrow', 'group' => 'Slide 1', 'max' => 60],
                'hero_slide_1_title'   => ['label' => 'Titre', 'type' => 'text', 'content' => 'hero.slides.0.title', 'max' => 100],
                'hero_slide_1_text'    => ['label' => 'Texte', 'type' => 'text', 'content' => 'hero.slides.0.text', 'max' => 180],
                'hero_slide_1_cta'     => ['label' => 'Bouton', 'type' => 'text', 'content' => 'hero.slides.0.cta', 'max' => 40],
                'hero_slide_1_link'    => ['label' => 'Lien du bouton', 'type' => 'select', 'content' => 'hero.slides.0.link', 'options_source' => 'links', 'help' => 'Choisissez une page réelle : aucun lien mort.'],
                'hero_slide_1_theme'   => [
                    'label' => 'Style', 'type' => 'select', 'content' => 'hero.slides.0.theme',
                    'options' => ['light' => 'Fond clair', 'dark' => 'Fond foncé'],
                ],
                'hero_slide_2_eyebrow' => ['label' => 'Sur-titre', 'type' => 'text', 'content' => 'hero.slides.1.eyebrow', 'group' => 'Slide 2', 'max' => 60],
                'hero_slide_2_title'   => ['label' => 'Titre', 'type' => 'text', 'content' => 'hero.slides.1.title', 'max' => 100],
                'hero_slide_2_text'    => ['label' => 'Texte', 'type' => 'text', 'content' => 'hero.slides.1.text', 'max' => 180],
                'hero_slide_2_cta'     => ['label' => 'Bouton', 'type' => 'text', 'content' => 'hero.slides.1.cta', 'max' => 40],
                'hero_slide_2_link'    => ['label' => 'Lien du bouton', 'type' => 'select', 'content' => 'hero.slides.1.link', 'options_source' => 'links'],
                'hero_slide_2_theme'   => [
                    'label' => 'Style', 'type' => 'select', 'content' => 'hero.slides.1.theme',
                    'options' => ['light' => 'Fond clair', 'dark' => 'Fond foncé'],
                ],
                'hero_slide_3_eyebrow' => ['label' => 'Sur-titre', 'type' => 'text', 'content' => 'hero.slides.2.eyebrow', 'group' => 'Slide 3', 'max' => 60],
                'hero_slide_3_title'   => ['label' => 'Titre', 'type' => 'text', 'content' => 'hero.slides.2.title', 'max' => 100],
                'hero_slide_3_text'    => ['label' => 'Texte', 'type' => 'text', 'content' => 'hero.slides.2.text', 'max' => 180],
                'hero_slide_3_cta'     => ['label' => 'Bouton', 'type' => 'text', 'content' => 'hero.slides.2.cta', 'max' => 40],
                'hero_slide_3_link'    => ['label' => 'Lien du bouton', 'type' => 'select', 'content' => 'hero.slides.2.link', 'options_source' => 'links'],
                'hero_slide_3_theme'   => [
                    'label' => 'Style', 'type' => 'select', 'content' => 'hero.slides.2.theme',
                    'options' => ['light' => 'Fond clair', 'dark' => 'Fond foncé'],
                ],
            ],
        ],

        /* ================================================================== */
        'offer' => [
            'label'       => 'Bandeau promo',
            'icon'        => 'tag',
            'description' => 'Le panneau d’offre avec compte à rebours.',
            'target'      => 'offer',
            'controls'    => [
                'offer_style' => [
                    'label'   => 'Fond du panneau',
                    'type'    => 'select',
                    'default' => 'gradient',
                    'attr'    => 'rs-offer',
                    'options' => [
                        'gradient' => 'Dégradé (actuel)',
                        'solid'    => 'Couleur unie',
                        'outline'  => 'Contour seul',
                    ],
                ],
                'offer_bg' => [
                    'label'   => 'Couleur du panneau',
                    'type'    => 'color',
                    'default' => '#F8F1E8',
                    'var'     => '--rs-offer-bg',
                    'help'    => 'Utilisée par les styles « couleur unie » et « contour ».',
                ],
                'offer_border' => [
                    'label'   => 'Bordure',
                    'type'    => 'color',
                    'default' => '#EADFD2',
                    'var'     => '--rs-offer-border',
                ],
                'offer_radius' => [
                    'label'   => 'Arrondi',
                    'type'    => 'range',
                    'default' => 20,
                    'min'     => 0,
                    'max'     => 40,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-offer-radius',
                ],
                'offer_title_color' => [
                    'label'   => 'Couleur du titre',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-offer-title',
                ],
                'offer_title_size' => [
                    'label'   => 'Taille du titre',
                    'type'    => 'range',
                    'default' => 21,
                    'min'     => 14,
                    'max'     => 40,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-offer-title-size',
                ],
                'offer_text_color' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#5C5C5C',
                    'var'     => '--rs-offer-text',
                ],
                'offer_badge' => ['label' => 'Badge', 'type' => 'text', 'content' => 'offer.badge', 'group' => 'Contenu de l’offre', 'max' => 60],
                'offer_title' => ['label' => 'Titre', 'type' => 'text', 'content' => 'offer.title', 'max' => 120],
                'offer_text'  => ['label' => 'Texte', 'type' => 'text', 'content' => 'offer.text', 'max' => 300],
                'offer_cta'   => ['label' => 'Bouton', 'type' => 'text', 'content' => 'offer.cta', 'max' => 40],
                'offer_link'  => ['label' => 'Lien du bouton', 'type' => 'select', 'content' => 'offer.link', 'options_source' => 'links', 'help' => 'Choisissez une page réelle : aucun lien mort.'],
                'offer_countdown_label' => ['label' => 'Libellé du compte à rebours', 'type' => 'text', 'content' => 'offer.countdown_label', 'max' => 80],
            ],
        ],

        /* ================================================================== */
        'categories' => [
            'label'       => 'Catégories',
            'icon'        => 'tag',
            'description' => 'Les raccourcis de collections sous le hero.',
            'target'      => 'categories',
            'notice'      => 'Les collections elles-mêmes se créent et se renomment dans Gestion → Catégories.',
            'controls'    => [
                'chip_style' => [
                    'label'   => 'Forme des puces',
                    'type'    => 'select',
                    'default' => 'pill',
                    'attr'    => 'rs-chip',
                    'options' => [
                        'pill'   => 'Pilule (actuel)',
                        'soft'   => 'Arrondi doux',
                        'square' => 'Carré',
                    ],
                ],
                'chip_bg' => [
                    'label'   => 'Fond',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-chip-bg',
                ],
                'chip_border' => [
                    'label'   => 'Bordure',
                    'type'    => 'color',
                    'default' => '#E7DFD5',
                    'var'     => '--rs-chip-border',
                ],
                'chip_text' => [
                    'label'   => 'Texte',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-chip-text',
                ],
                'chip_icon' => [
                    'label'   => 'Icônes',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--rs-chip-icon',
                ],
                'categories_title' => ['label' => 'Titre de la rangée', 'type' => 'text', 'content' => 'categories.title', 'group' => 'Contenu', 'max' => 80],
                'categories_link_label' => ['label' => 'Libellé du lien', 'type' => 'text', 'content' => 'categories.link_label', 'max' => 40],
            ],
        ],

        /* ================================================================== */
        'products' => [
            'label'       => 'Grille produits',
            'icon'        => 'grid',
            'description' => 'Cartes produits, espacements et prix.',
            'target'      => 'products',
            'controls'    => [
                'grid_columns' => [
                    'label'   => 'Colonnes (bureau)',
                    'type'    => 'select',
                    'default' => '4',
                    'var'     => '--rs-grid-cols',
                    'options' => [
                        '2' => '2 colonnes',
                        '3' => '3 colonnes',
                        '4' => '4 colonnes',
                    ],
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
                'card_style' => [
                    'label'   => 'Style de carte',
                    'type'    => 'select',
                    'default' => 'elevated',
                    'attr'    => 'rs-card',
                    'options' => [
                        'elevated' => 'Élévation / ombre',
                        'outlined' => 'Contour fin',
                        'flat'     => 'Plate, sans bordure',
                    ],
                ],
                'card_bg' => [
                    'label'   => 'Fond des cartes',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-card-bg',
                ],
                'card_border' => [
                    'label'   => 'Couleur des bordures',
                    'type'    => 'color',
                    'default' => '#E7DFD5',
                    'var'     => '--rs-card-border',
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
                'card_radius' => [
                    'label'   => 'Arrondi des cartes',
                    'type'    => 'range',
                    'default' => 14,
                    'min'     => 0,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-card-radius',
                ],
                'card_shadow' => [
                    'label'   => 'Ombre',
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
                'product_hover' => [
                    'label'   => 'Animation au survol',
                    'type'    => 'toggle',
                    'default' => 'on',
                    'attr'    => 'rs-hover',
                    'on'      => 'on',
                    'off'     => 'off',
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
                'image_ratio' => [
                    'label'   => 'Ratio des images',
                    'type'    => 'select',
                    'default' => '3 / 4',
                    'var'     => '--rs-image-ratio',
                    'options' => [
                        '3 / 4'  => 'Portrait 3:4 (actuel)',
                        '4 / 5'  => 'Portrait 4:5',
                        '1 / 1'  => 'Carré 1:1',
                        '16 / 9' => 'Paysage 16:9',
                    ],
                ],
                'price_color' => [
                    'label'   => 'Couleur du prix',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-price',
                ],
                'featured_eyebrow' => ['label' => 'Sur-titre', 'type' => 'text', 'content' => 'featured.eyebrow', 'group' => 'Titres de la section', 'max' => 60],
                'featured_title' => ['label' => 'Titre', 'type' => 'text', 'content' => 'featured.title', 'max' => 100],
                'featured_link_label' => ['label' => 'Libellé du lien', 'type' => 'text', 'content' => 'featured.link_label', 'max' => 40],
            ],
        ],

        /* ================================================================== */
        'why' => [
            'label'       => 'Engagements',
            'icon'        => 'shield',
            'description' => 'Le panneau « Pourquoi nous choisir » et ses quatre cartes.',
            'target'      => 'why',
            'controls'    => [
                'why_bg' => [
                    'label'   => 'Fond de la section',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-why-bg',
                ],
                'why_card_bg' => [
                    'label'   => 'Fond des cartes',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-why-card-bg',
                ],
                'why_line' => [
                    'label'   => 'Lignes de séparation',
                    'type'    => 'color',
                    'default' => '#E7DFD5',
                    'var'     => '--rs-why-line',
                ],
                'why_title' => [
                    'label'   => 'Couleur des titres',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-why-title',
                ],
                'why_text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#5C5C5C',
                    'var'     => '--rs-why-text',
                ],
                'why_icon_bg' => [
                    'label'   => 'Fond des icônes',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-why-icon-bg',
                ],
                'why_icon' => [
                    'label'   => 'Couleur des icônes',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--rs-why-icon',
                ],
                'why_radius' => [
                    'label'   => 'Arrondi du panneau',
                    'type'    => 'range',
                    'default' => 18,
                    'min'     => 0,
                    'max'     => 32,
                    'step'    => 1,
                    'unit'    => 'px',
                    'var'     => '--rs-why-radius',
                ],
                'why_eyebrow' => ['label' => 'Sur-titre', 'type' => 'text', 'content' => 'why.eyebrow', 'group' => 'En-tête', 'max' => 60],
                'why_heading' => ['label' => 'Titre', 'type' => 'text', 'content' => 'why.title', 'max' => 100],
                'why_lead'    => ['label' => 'Chapeau', 'type' => 'text', 'content' => 'why.lead', 'max' => 300],
                'why_card_1_title' => ['label' => 'Carte 1 — titre', 'type' => 'text', 'content' => 'why.cards.0.title', 'group' => 'Cartes', 'max' => 80],
                'why_card_1_text'  => ['label' => 'Carte 1 — texte', 'type' => 'text', 'content' => 'why.cards.0.text', 'max' => 160],
                'why_card_2_title' => ['label' => 'Carte 2 — titre', 'type' => 'text', 'content' => 'why.cards.1.title', 'max' => 80],
                'why_card_2_text'  => ['label' => 'Carte 2 — texte', 'type' => 'text', 'content' => 'why.cards.1.text', 'max' => 160],
                'why_card_3_title' => ['label' => 'Carte 3 — titre', 'type' => 'text', 'content' => 'why.cards.2.title', 'max' => 80],
                'why_card_3_text'  => ['label' => 'Carte 3 — texte', 'type' => 'text', 'content' => 'why.cards.2.text', 'max' => 160],
                'why_card_4_title' => ['label' => 'Carte 4 — titre', 'type' => 'text', 'content' => 'why.cards.3.title', 'max' => 80],
                'why_card_4_text'  => ['label' => 'Carte 4 — texte', 'type' => 'text', 'content' => 'why.cards.3.text', 'max' => 160],
            ],
        ],

        /* ================================================================== */
        'footer' => [
            'label'       => 'Pied de page',
            'icon'        => 'panel',
            'description' => 'Le bloc sombre en bas de page, ses textes et ses liens.',
            'target'      => 'footer',
            'controls'    => [
                'footer_bg' => [
                    'label'   => 'Fond',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-footer-bg',
                ],
                'footer_heading' => [
                    'label'   => 'Couleur des titres',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-footer-heading',
                ],
                'footer_text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#B0B1B8',
                    'var'     => '--rs-footer-text',
                ],
                'footer_link' => [
                    'label'   => 'Couleur des liens',
                    'type'    => 'color',
                    'default' => '#B0B1B8',
                    'var'     => '--rs-footer-link',
                ],
                'footer_accent' => [
                    'label'   => 'Couleur d’accent',
                    'type'    => 'color',
                    'default' => '#C2A691',
                    'var'     => '--rs-footer-accent',
                ],
                'footer_description' => ['label' => 'Description', 'type' => 'text', 'content' => 'footer.description', 'group' => 'Contenu du pied de page', 'max' => 300],
                'footer_logo_sub' => ['label' => 'Sous-titre du logo', 'type' => 'text', 'content' => 'footer.logo_sub', 'max' => 80],
                'footer_categories_title' => ['label' => 'Titre « catégories »', 'type' => 'text', 'content' => 'footer.categories_title', 'max' => 40],
                'footer_help_title' => ['label' => 'Titre « aide »', 'type' => 'text', 'content' => 'footer.help_title', 'max' => 40],
                'footer_order_title' => ['label' => 'Titre « commande »', 'type' => 'text', 'content' => 'footer.order_title', 'max' => 40],
                'footer_pill_1' => ['label' => 'Pastille 1', 'type' => 'text', 'content' => 'footer.pill_1', 'max' => 40],
                'footer_pill_2' => ['label' => 'Pastille 2', 'type' => 'text', 'content' => 'footer.pill_2', 'max' => 40],
                'footer_whatsapp_label' => ['label' => 'Libellé WhatsApp', 'type' => 'text', 'content' => 'footer.whatsapp_label', 'max' => 100],
                'footer_bottom_note' => ['label' => 'Note de bas de page', 'type' => 'text', 'content' => 'footer.bottom_note', 'max' => 120],
            ],
        ],

        /* ================================================================== */
        'buttons' => [
            'label'       => 'Boutons',
            'icon'        => 'cursor',
            'description' => 'Forme, couleur et réaction des boutons du site.',
            'target'      => '',
            'controls'    => [
                'button_radius' => [
                    'label'   => 'Arrondi',
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
                'button_bg' => [
                    'label'   => 'Fond des boutons foncés',
                    'type'    => 'color',
                    'default' => '#1F2029',
                    'var'     => '--rs-btn-bg',
                    'derived' => [
                        ['var' => '--rs-btn-soft', 'alpha' => 0.12],
                    ],
                ],
                'button_text' => [
                    'label'   => 'Couleur du texte',
                    'type'    => 'color',
                    'default' => '#FFFFFF',
                    'var'     => '--rs-btn-text',
                ],
                'button_hover' => [
                    'label'   => 'Couleur au survol',
                    'type'    => 'color',
                    'default' => '#704F38',
                    'var'     => '--rs-btn-hover',
                ],
                'button_style' => [
                    'label'   => 'Style',
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
    ],
];
