<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Validation Mappings
 *
 * Centralise toutes les règles de validation utilisées dans le plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Validation_Mappings {

    // ==========================================
    // RÈGLES DE VALIDATION GÉNÉRALES
    // ==========================================

    private static $validation_rules = [
        // Dimensions
        'width' => [
            'type' => 'number',
            'min' => 1,
            'max' => 10000,
            'required' => true,
            'message' => 'La largeur doit être comprise entre 1 et 10000 pixels.'
        ],
        'height' => [
            'type' => 'number',
            'min' => 1,
            'max' => 10000,
            'required' => true,
            'message' => 'La hauteur doit être comprise entre 1 et 10000 pixels.'
        ],
        'dpi' => [
            'type' => 'number',
            'min' => 72,
            'max' => 600,
            'required' => true,
            'message' => 'La résolution DPI doit être comprise entre 72 et 600.'
        ],

        // Couleurs
        'color' => [
            'type' => 'string',
            'pattern' => '/^#[0-9A-Fa-f]{6}$/',
            'required' => false,
            'message' => 'La couleur doit être au format hexadécimal (#RRGGBB).'
        ],
        'opacity' => [
            'type' => 'number',
            'min' => 0,
            'max' => 1,
            'required' => false,
            'message' => 'L\'opacité doit être comprise entre 0 et 1.'
        ],

        // Textes
        'text_content' => [
            'type' => 'string',
            'max_length' => 10000,
            'required' => true,
            'message' => 'Le contenu texte ne peut pas dépasser 10000 caractères.'
        ],
        'font_size' => [
            'type' => 'number',
            'min' => 6,
            'max' => 200,
            'required' => false,
            'message' => 'La taille de police doit être comprise entre 6 et 200 pixels.'
        ],
        'line_height' => [
            'type' => 'number',
            'min' => 0.5,
            'max' => 3,
            'required' => false,
            'message' => 'L\'interligne doit être compris entre 0.5 et 3.'
        ],

        // Images
        'image_src' => [
            'type' => 'string',
            'pattern' => '/\.(jpg|jpeg|png|gif|svg|webp)$/i',
            'required' => true,
            'message' => 'L\'URL de l\'image doit pointer vers un fichier image valide (jpg, png, gif, svg, webp).'
        ],
        'image_alt' => [
            'type' => 'string',
            'max_length' => 500,
            'required' => false,
            'message' => 'Le texte alternatif ne peut pas dépasser 500 caractères.'
        ],

        // Positions et transformations
        'x' => [
            'type' => 'number',
            'min' => -10000,
            'max' => 10000,
            'required' => true,
            'message' => 'La position X doit être comprise entre -10000 et 10000.'
        ],
        'y' => [
            'type' => 'number',
            'min' => -10000,
            'max' => 10000,
            'required' => true,
            'message' => 'La position Y doit être comprise entre -10000 et 10000.'
        ],
        'rotation' => [
            'type' => 'number',
            'min' => -360,
            'max' => 360,
            'required' => false,
            'message' => 'La rotation doit être comprise entre -360° et 360°.'
        ],
        'scale_x' => [
            'type' => 'number',
            'min' => 0.1,
            'max' => 10,
            'required' => false,
            'message' => 'L\'échelle X doit être comprise entre 0.1 et 10.'
        ],
        'scale_y' => [
            'type' => 'number',
            'min' => 0.1,
            'max' => 10,
            'required' => false,
            'message' => 'L\'échelle Y doit être comprise entre 0.1 et 10.'
        ],

        // Animations
        'animation_duration' => [
            'type' => 'number',
            'min' => 0,
            'max' => 10000,
            'required' => false,
            'message' => 'La durée d\'animation doit être comprise entre 0 et 10000 millisecondes.'
        ],
        'animation_delay' => [
            'type' => 'number',
            'min' => 0,
            'max' => 10000,
            'required' => false,
            'message' => 'Le délai d\'animation doit être compris entre 0 et 10000 millisecondes.'
        ],

        // Performance
        'fps_target' => [
            'type' => 'number',
            'min' => 1,
            'max' => 120,
            'required' => false,
            'message' => 'La cible FPS doit être comprise entre 1 et 120.'
        ],
        'memory_limit_js' => [
            'type' => 'number',
            'min' => 10,
            'max' => 500,
            'required' => false,
            'message' => 'La limite mémoire JavaScript doit être comprise entre 10 et 500 Mo.'
        ],
        'memory_limit_php' => [
            'type' => 'number',
            'min' => 32,
            'max' => 1024,
            'required' => false,
            'message' => 'La limite mémoire PHP doit être comprise entre 32 et 1024 Mo.'
        ],
        'response_timeout' => [
            'type' => 'number',
            'min' => 1000,
            'max' => 120000,
            'required' => false,
            'message' => 'Le timeout de réponse doit être compris entre 1000 et 120000 millisecondes.'
        ],

        'history_max' => [
            'type' => 'number',
            'min' => 1,
            'max' => 1000,
            'required' => false,
            'message' => 'Le nombre maximum d\'historique doit être compris entre 1 et 1000.'
        ],

        // Export
        'export_quality' => [
            'type' => 'number',
            'min' => 1,
            'max' => 100,
            'required' => false,
            'message' => 'La qualité d\'export doit être comprise entre 1 et 100.'
        ]
    ];

    // ==========================================
    // RÈGLES DE VALIDATION PAR TYPE D'ÉLÉMENT
    // ==========================================

    private static $element_validation_rules = [
        'text' => [
            'content' => [
                'type' => 'string',
                'max_length' => 10000,
                'required' => true,
                'message' => 'Le contenu du texte est requis et ne peut pas dépasser 10000 caractères.'
            ],
            'font_size' => [
                'type' => 'number',
                'min' => 6,
                'max' => 200,
                'required' => false,
                'message' => 'La taille de police doit être comprise entre 6 et 200 pixels.'
            ],
            'color' => [
                'type' => 'string',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
                'required' => false,
                'message' => 'La couleur doit être au format hexadécimal (#RRGGBB).'
            ]
        ],

        'image' => [
            'src' => [
                'type' => 'string',
                'pattern' => '/\.(jpg|jpeg|png|gif|svg|webp)$/i',
                'required' => true,
                'message' => 'L\'URL de l\'image est requise et doit pointer vers un fichier image valide.'
            ],
            'width' => [
                'type' => 'number',
                'min' => 1,
                'max' => 5000,
                'required' => true,
                'message' => 'La largeur de l\'image doit être comprise entre 1 et 5000 pixels.'
            ],
            'height' => [
                'type' => 'number',
                'min' => 1,
                'max' => 5000,
                'required' => true,
                'message' => 'La hauteur de l\'image doit être comprise entre 1 et 5000 pixels.'
            ]
        ],

        'shape' => [
            'type' => [
                'type' => 'string',
                'enum' => ['rectangle', 'circle', 'triangle', 'polygon'],
                'required' => true,
                'message' => 'Le type de forme doit être rectangle, circle, triangle ou polygon.'
            ],
            'fill_color' => [
                'type' => 'string',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
                'required' => false,
                'message' => 'La couleur de remplissage doit être au format hexadécimal (#RRGGBB).'
            ],
            'stroke_color' => [
                'type' => 'string',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
                'required' => false,
                'message' => 'La couleur de contour doit être au format hexadécimal (#RRGGBB).'
            ]
        ],

        'line' => [
            'start_x' => [
                'type' => 'number',
                'min' => -10000,
                'max' => 10000,
                'required' => true,
                'message' => 'La position de départ X doit être comprise entre -10000 et 10000.'
            ],
            'start_y' => [
                'type' => 'number',
                'min' => -10000,
                'max' => 10000,
                'required' => true,
                'message' => 'La position de départ Y doit être comprise entre -10000 et 10000.'
            ],
            'end_x' => [
                'type' => 'number',
                'min' => -10000,
                'max' => 10000,
                'required' => true,
                'message' => 'La position d\'arrivée X doit être comprise entre -10000 et 10000.'
            ],
            'end_y' => [
                'type' => 'number',
                'min' => -10000,
                'max' => 10000,
                'required' => true,
                'message' => 'La position d\'arrivée Y doit être comprise entre -10000 et 10000.'
            ]
        ]
    ];

    // ==========================================
    // RÈGLES DE VALIDATION POUR LES FORMULAIRES
    // ==========================================

    private static $form_validation_rules = [
        'canvas_settings' => [
            'canvas_width' => [
                'type' => 'number',
                'min' => 100,
                'max' => 5000,
                'required' => true,
                'message' => 'La largeur du canvas doit être comprise entre 100 et 5000 pixels.'
            ],
            'canvas_height' => [
                'type' => 'number',
                'min' => 100,
                'max' => 5000,
                'required' => true,
                'message' => 'La hauteur du canvas doit être comprise entre 100 et 5000 pixels.'
            ],
            'canvas_dpi' => [
                'type' => 'number',
                'min' => 72,
                'max' => 600,
                'required' => true,
                'message' => 'La résolution DPI doit être comprise entre 72 et 600.'
            ],
            'canvas_bg_color' => [
                'type' => 'string',
                'pattern' => '/^#[0-9A-Fa-f]{6}$/',
                'required' => false,
                'message' => 'La couleur de fond doit être au format hexadécimal (#RRGGBB).'
            ]
        ],

        'template_settings' => [
            'template_name' => [
                'type' => 'string',
                'min_length' => 1,
                'max_length' => 100,
                'required' => true,
                'message' => 'Le nom du template est requis et ne peut pas dépasser 100 caractères.'
            ],
            'template_description' => [
                'type' => 'string',
                'max_length' => 500,
                'required' => false,
                'message' => 'La description du template ne peut pas dépasser 500 caractères.'
            ]
        ],

        'export_settings' => [
            'export_format' => [
                'type' => 'string',
                'enum' => ['pdf', 'png', 'jpg', 'svg'],
                'required' => true,
                'message' => 'Le format d\'export doit être pdf, png, jpg ou svg.'
            ],
            'export_quality' => [
                'type' => 'number',
                'min' => 1,
                'max' => 100,
                'required' => false,
                'message' => 'La qualité d\'export doit être comprise entre 1 et 100.'
            ]
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir toutes les règles de validation générales
     */
    public static function get_validation_rules() {
        return self::$validation_rules;
    }

    /**
     * Obtenir une règle de validation spécifique
     */
    public static function get_validation_rule($field) {
        return self::$validation_rules[$field] ?? null;
    }

    /**
     * Obtenir les règles de validation pour un type d'élément
     */
    public static function get_element_validation_rules($element_type) {
        return self::$element_validation_rules[$element_type] ?? [];
    }

    /**
     * Obtenir les règles de validation pour un formulaire
     */
    public static function get_form_validation_rules($form_type) {
        return self::$form_validation_rules[$form_type] ?? [];
    }

    /**
     * Valider une valeur selon une règle
     */
    public static function validate_value($value, $rule) {
        // Vérifier si requis
        if ($rule['required'] && (is_null($value) || $value === '')) {
            return ['valid' => false, 'message' => $rule['message'] ?? 'Ce champ est requis.'];
        }

        // Si non requis et vide, considérer comme valide
        if (!$rule['required'] && (is_null($value) || $value === '')) {
            return ['valid' => true];
        }

        // Validation par type
        switch ($rule['type']) {
            case 'number':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'message' => 'Ce champ doit être un nombre.'];
                }

                $num_value = (float) $value;

                if (isset($rule['min']) && $num_value < $rule['min']) {
                    return ['valid' => false, 'message' => $rule['message'] ?? "La valeur minimale est {$rule['min']}."];
                }

                if (isset($rule['max']) && $num_value > $rule['max']) {
                    return ['valid' => false, 'message' => $rule['message'] ?? "La valeur maximale est {$rule['max']}."];
                }
                break;

            case 'string':
                if (!is_string($value)) {
                    return ['valid' => false, 'message' => 'Ce champ doit être une chaîne de caractères.'];
                }

                if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                    return ['valid' => false, 'message' => $rule['message'] ?? "La longueur minimale est {$rule['min_length']} caractères."];
                }

                if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                    return ['valid' => false, 'message' => $rule['message'] ?? "La longueur maximale est {$rule['max_length']} caractères."];
                }

                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                    return ['valid' => false, 'message' => $rule['message'] ?? 'Le format de la valeur est invalide.'];
                }

                if (isset($rule['enum']) && !in_array($value, $rule['enum'])) {
                    return ['valid' => false, 'message' => $rule['message'] ?? 'La valeur doit être l\'une des suivantes : ' . implode(', ', $rule['enum']) . '.'];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Valider un élément selon son type
     */
    public static function validate_element($element_data, $element_type) {
        $rules = self::get_element_validation_rules($element_type);
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $element_data[$field] ?? null;
            $result = self::validate_value($value, $rule);

            if (!$result['valid']) {
                $errors[$field] = $result['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Valider un formulaire
     */
    public static function validate_form($form_data, $form_type) {
        $rules = self::get_form_validation_rules($form_type);
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $form_data[$field] ?? null;
            $result = self::validate_value($value, $rule);

            if (!$result['valid']) {
                $errors[$field] = $result['message'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}



