<?php
/**
 * PDF Builder Pro - Canvas Elements Manager
 * Gestion des éléments du canvas (drag & drop, redimensionnement)
 *
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe pour gérer les éléments du canvas
 */
class PDF_Builder_Canvas_Elements_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager = null;

    /**
     * Gestionnaire de cache
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager = null;

    /**
     * Constructeur privé (singleton)
     */
    private function __construct() {
        $this->init_dependencies();
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les dépendances
     */
    private function init_dependencies() {
        if (class_exists('PDF_Builder_Database_Manager')) {
            $this->db_manager = PDF_Builder_Database_Manager::getInstance();
        }

        if (class_exists('PDF_Builder_Cache_Manager')) {
            $this->cache_manager = PDF_Builder_Cache_Manager::getInstance();
        }
    }

    /**
     * Valider les données d'un élément
     */
    public function validate_element_data($element_data) {
        $required_fields = ['id', 'type', 'x', 'y', 'width', 'height'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (!isset($element_data[$field])) {
                $errors[] = "Champ requis manquant: {$field}";
            }
        }

        // Validation des coordonnées
        if (isset($element_data['x']) && !is_numeric($element_data['x'])) {
            $errors[] = "Coordonnée X invalide";
        }
        if (isset($element_data['y']) && !is_numeric($element_data['y'])) {
            $errors[] = "Coordonnée Y invalide";
        }

        // Validation des dimensions
        if (isset($element_data['width']) && (!is_numeric($element_data['width']) || $element_data['width'] < 1)) {
            $errors[] = "Largeur invalide (minimum 1px)";
        }
        if (isset($element_data['height']) && (!is_numeric($element_data['height']) || $element_data['height'] < 1)) {
            $errors[] = "Hauteur invalide (minimum 1px)";
        }

        // Validation des propriétés avancées
        if (isset($element_data['fontSize']) && (!is_numeric($element_data['fontSize']) || $element_data['fontSize'] < 8 || $element_data['fontSize'] > 72)) {
            $errors[] = "Taille de police invalide (8-72px)";
        }

        if (isset($element_data['borderWidth']) && (!is_numeric($element_data['borderWidth']) || $element_data['borderWidth'] < 0 || $element_data['borderWidth'] > 20)) {
            $errors[] = "Largeur de bordure invalide (0-20px)";
        }

        if (isset($element_data['borderStyle']) && !in_array($element_data['borderStyle'], ['solid', 'dashed', 'dotted', 'double'])) {
            $errors[] = "Style de bordure invalide";
        }

        if (isset($element_data['borderRadius']) && (!is_numeric($element_data['borderRadius']) || $element_data['borderRadius'] < 0 || $element_data['borderRadius'] > 100)) {
            $errors[] = "Rayon de bordure invalide (0-100px)";
        }

        if (isset($element_data['opacity']) && (!is_numeric($element_data['opacity']) || $element_data['opacity'] < 0 || $element_data['opacity'] > 100)) {
            $errors[] = "Opacité invalide (0-100%)";
        }

        if (isset($element_data['rotation']) && (!is_numeric($element_data['rotation']) || $element_data['rotation'] < -180 || $element_data['rotation'] > 180)) {
            $errors[] = "Rotation invalide (-180° à 180°)";
        }

        if (isset($element_data['scale']) && (!is_numeric($element_data['scale']) || $element_data['scale'] < 10 || $element_data['scale'] > 200)) {
            $errors[] = "Échelle invalide (10-200%)";
        }

        // Validation des couleurs (format hexadécimal)
        $color_fields = ['color', 'backgroundColor', 'borderColor', 'shadowColor'];
        foreach ($color_fields as $field) {
            if (isset($element_data[$field]) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $element_data[$field]) && $element_data[$field] !== 'transparent') {
                $errors[] = "Couleur {$field} invalide (format hexadécimal attendu)";
            }
        }

        // Validation des pourcentages
        $percentage_fields = ['brightness', 'contrast', 'saturate'];
        foreach ($percentage_fields as $field) {
            if (isset($element_data[$field]) && (!is_numeric($element_data[$field]) || $element_data[$field] < 0 || $element_data[$field] > 200)) {
                $errors[] = "Pourcentage {$field} invalide (0-200%)";
            }
        }

        return $errors;
    }

    /**
     * Obtenir les propriétés par défaut pour un élément
     */
    public function get_default_element_properties($element_type = 'text') {
        // Propriétés communes à tous les éléments
        $defaults = [
            // Propriétés de base
            'x' => 50,
            'y' => 50,
            'width' => 100,
            'height' => 50,
            'opacity' => 100,
            'rotation' => 0,
            'scale' => 100,
            'visible' => true,

            // Apparence
            'backgroundColor' => 'transparent',
            'borderColor' => '#e2e8f0',
            'borderWidth' => 0,
            'borderStyle' => 'solid',
            'borderRadius' => 0,

            // Typographie
            'color' => '#1e293b',
            'fontFamily' => 'Inter, sans-serif',
            'fontSize' => 14,
            'fontWeight' => 'normal',
            'fontStyle' => 'normal',
            'textAlign' => 'left',
            'textDecoration' => 'none',

            // Contenu
            'content' => 'Texte',

            // Images
            'src' => '',
            'alt' => '',
            'objectFit' => 'cover',
            'imageUrl' => '',

            // Effets
            'shadow' => false,
            'shadowColor' => '#000000',
            'shadowOffsetX' => 2,
            'shadowOffsetY' => 2,
            'brightness' => 100,
            'contrast' => 100,
            'saturate' => 100,

            // Propriétés spécifiques aux tableaux
            'showHeaders' => true,
            'showBorders' => true,
            'headers' => ['Produit', 'Qté', 'Prix'],
            'dataSource' => 'order_items',
            'columns' => [
                'image' => true,
                'name' => true,
                'sku' => false,
                'quantity' => true,
                'price' => true,
                'total' => true
            ],
            'showSubtotal' => false,
            'showShipping' => true,
            'showTaxes' => true,
            'showDiscount' => false,
            'showTotal' => false,

            // Propriétés pour les barres de progression
            'progressColor' => '#3b82f6',
            'progressValue' => 75,

            // Propriétés pour les codes
            'lineColor' => '#64748b',
            'lineWidth' => 2,

            // Propriétés pour les types de document
            'documentType' => 'invoice',

            // Propriétés d'espacement et mise en page
            'spacing' => 8,
            'layout' => 'vertical',
            'alignment' => 'left',
            'fit' => 'contain',

            // Propriétés pour les champs et options
            'fields' => [],
            'showLabel' => false,
            'labelText' => '',

            // Propriétés pour les lignes
            'lineHeight' => 1.2
        ];

        // Ajustements spécifiques selon le type d'élément
        $type_adjustments = [
            'text' => [
                'width' => 150,
                'height' => 30
            ],
            'image' => [
                'width' => 150,
                'height' => 100
            ],
            'rectangle' => [
                'backgroundColor' => '#f1f5f9',
                'borderWidth' => 1,
                'width' => 150,
                'height' => 80
            ],
            'product_table' => [
                'width' => 300,
                'height' => 150
            ],
            'customer_info' => [
                'width' => 200,
                'height' => 100
            ],
            'company_logo' => [
                'width' => 100,
                'height' => 60
            ],
            'order_number' => [
                'width' => 150,
                'height' => 30
            ],
            'company_info' => [
                'width' => 200,
                'height' => 80
            ],
            'document_type' => [
                'width' => 120,
                'height' => 40
            ],
            'watermark' => [
                'width' => 300,
                'height' => 200,
                'opacity' => 10,
                'content' => 'CONFIDENTIEL'
            ],
            'progress-bar' => [
                'width' => 200,
                'height' => 20
            ],
            'barcode' => [
                'width' => 150,
                'height' => 60
            ],
            'qrcode' => [
                'width' => 80,
                'height' => 80
            ],
            'icon' => [
                'width' => 50,
                'height' => 50
            ],
            'line' => [
                'width' => 200,
                'height' => 2
            ]
        ];

        // Fusionner les propriétés par défaut avec les ajustements spécifiques
        if (isset($type_adjustments[$element_type])) {
            $defaults = array_merge($defaults, $type_adjustments[$element_type]);
        }

        return $defaults;
    }

    /**
     * Nettoyer et sanitiser les propriétés d'un élément
     */
    public function sanitize_element_properties($element_data) {
        $sanitized = [];

        // Sanitisation des champs de base
        $sanitized['id'] = sanitize_text_field($element_data['id'] ?? '');
        $sanitized['type'] = sanitize_text_field($element_data['type'] ?? 'text');

        // Sanitisation des coordonnées et dimensions
        $sanitized['x'] = floatval($element_data['x'] ?? 50);
        $sanitized['y'] = floatval($element_data['y'] ?? 50);
        $sanitized['width'] = max(1, floatval($element_data['width'] ?? 100));
        $sanitized['height'] = max(1, floatval($element_data['height'] ?? 50));

        // Sanitisation des propriétés numériques avec contraintes
        $numeric_constraints = [
            'opacity' => [0, 100, 100],
            'rotation' => [-180, 180, 0],
            'scale' => [10, 200, 100],
            'fontSize' => [8, 72, 14],
            'borderWidth' => [0, 20, 0],
            'borderRadius' => [0, 100, 0],
            'brightness' => [0, 200, 100],
            'contrast' => [0, 200, 100],
            'saturate' => [0, 200, 100],
            'spacing' => [0, 50, 8],
            'lineHeight' => [0.5, 3, 1.2],
            'progressValue' => [0, 100, 75],
            'lineWidth' => [1, 10, 2],
            'shadowOffsetX' => [-50, 50, 2],
            'shadowOffsetY' => [-50, 50, 2]
        ];

        foreach ($numeric_constraints as $field => $constraints) {
            list($min, $max, $default) = $constraints;
            $value = $element_data[$field] ?? $default;
            $sanitized[$field] = max($min, min($max, floatval($value)));
        }

        // Sanitisation des couleurs
        $color_fields = ['color', 'backgroundColor', 'borderColor', 'shadowColor', 'progressColor', 'lineColor'];
        foreach ($color_fields as $field) {
            $color = $element_data[$field] ?? '';
            if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) || $color === 'transparent') {
                $sanitized[$field] = $color;
            }
        }

        // Sanitisation des chaînes de caractères
        $text_fields = [
            'content', 'fontFamily', 'fontWeight', 'fontStyle', 'textAlign', 'textDecoration',
            'src', 'alt', 'objectFit', 'imageUrl', 'documentType', 'layout', 'alignment', 'fit', 'labelText'
        ];
        foreach ($text_fields as $field) {
            if (isset($element_data[$field])) {
                $sanitized[$field] = sanitize_text_field($element_data[$field]);
            }
        }

        // Sanitisation des booléens
        $boolean_fields = ['visible', 'shadow', 'showLabel', 'showBorders', 'showHeaders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'];
        foreach ($boolean_fields as $field) {
            $sanitized[$field] = (bool) ($element_data[$field] ?? false);
        }

        // Sanitisation des tableaux
        if (isset($element_data['headers']) && is_array($element_data['headers'])) {
            $sanitized['headers'] = array_map('sanitize_text_field', $element_data['headers']);
        }

        if (isset($element_data['fields']) && is_array($element_data['fields'])) {
            $sanitized['fields'] = array_map('sanitize_text_field', $element_data['fields']);
        }

        if (isset($element_data['columns']) && is_array($element_data['columns'])) {
            $sanitized['columns'] = array_map('boolval', $element_data['columns']);
        }

        // Sanitisation des valeurs énumérées
        $enum_fields = [
            'borderStyle' => ['solid', 'dashed', 'dotted', 'double'],
            'textAlign' => ['left', 'center', 'right', 'justify'],
            'fontWeight' => ['normal', 'bold', 'lighter', 'bolder', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
            'fontStyle' => ['normal', 'italic', 'oblique'],
            'textDecoration' => ['none', 'underline', 'overline', 'line-through'],
            'objectFit' => ['cover', 'contain', 'fill', 'none', 'scale-down'],
            'fit' => ['cover', 'contain', 'fill', 'none', 'scale-down'],
            'layout' => ['vertical', 'horizontal'],
            'alignment' => ['left', 'center', 'right'],
            'documentType' => ['invoice', 'quote', 'receipt', 'order', 'credit_note']
        ];

        foreach ($enum_fields as $field => $allowed_values) {
            if (isset($element_data[$field]) && in_array($element_data[$field], $allowed_values)) {
                $sanitized[$field] = $element_data[$field];
            }
        }

        return $sanitized;
    }

    /**
     * Sauvegarder les éléments d'un template (implémentation temporaire avec WP options)
     */
    private function save_template_elements_to_db($template_id, $elements) {
        $option_key = 'pdf_builder_template_' . $template_id . '_elements';
        $json_data = wp_json_encode($elements);

        if ($json_data === false) {
            return new WP_Error('json_encode_error', 'Erreur lors de l\'encodage JSON des éléments');
        }

        $result = update_option($option_key, $json_data, false);

        if ($result === false) {
            return new WP_Error('save_error', 'Erreur lors de la sauvegarde des éléments');
        }

        return true;
    }

    /**
     * Charger les éléments d'un template (implémentation temporaire avec WP options)
     */
    private function get_template_elements_from_db($template_id) {
        $option_key = 'pdf_builder_template_' . $template_id . '_elements';
        $json_data = get_option($option_key, false);

        if ($json_data === false) {
            return [];
        }

        $elements = json_decode($json_data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            pdf_builder_debug('Erreur de décodage JSON pour le template ' . $template_id . ': ' . json_last_error_msg(), 1, 'elements_manager');
            return [];
        }

        return $elements ?: [];
    }

    /**
     * Calculer les nouvelles coordonnées après déplacement
     */
    public function calculate_drag_position($element, $delta_x, $delta_y, $canvas_bounds = null) {
        $new_x = $element['position']['x'] + $delta_x;
        $new_y = $element['position']['y'] + $delta_y;

        // Appliquer les contraintes du canvas si fournies
        if ($canvas_bounds) {
            $new_x = max(0, min($new_x, $canvas_bounds['width'] - $element['size']['width']));
            $new_y = max(0, min($new_y, $canvas_bounds['height'] - $element['size']['height']));
        }

        return [
            'x' => round($new_x, 2),
            'y' => round($new_y, 2)
        ];
    }

    /**
     * Calculer les nouvelles dimensions après redimensionnement
     */
    public function calculate_resize_dimensions($element, $handle, $delta_x, $delta_y, $constraints = null) {
        $current_width = $element['size']['width'];
        $current_height = $element['size']['height'];
        $current_x = $element['position']['x'];
        $current_y = $element['position']['y'];

        $new_width = $current_width;
        $new_height = $current_height;
        $new_x = $current_x;
        $new_y = $current_y;

        // Appliquer les contraintes par défaut
        $min_width = $constraints['min_width'] ?? 10;
        $min_height = $constraints['min_height'] ?? 10;
        $max_width = $constraints['max_width'] ?? 2000;
        $max_height = $constraints['max_height'] ?? 2000;

        switch ($handle) {
            case 'nw': // Nord-Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_height = max($min_height, $current_height - $delta_y);
                $new_x = $current_x + ($current_width - $new_width);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 'ne': // Nord-Est
                $new_width = max($min_width, $current_width + $delta_x);
                $new_height = max($min_height, $current_height - $delta_y);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 'sw': // Sud-Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_height = max($min_height, $current_height + $delta_y);
                $new_x = $current_x + ($current_width - $new_width);
                break;

            case 'se': // Sud-Est
                $new_width = max($min_width, $current_width + $delta_x);
                $new_height = max($min_height, $current_height + $delta_y);
                break;

            case 'n': // Nord
                $new_height = max($min_height, $current_height - $delta_y);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 's': // Sud
                $new_height = max($min_height, $current_height + $delta_y);
                break;

            case 'w': // Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_x = $current_x + ($current_width - $new_width);
                break;

            case 'e': // Est
                $new_width = max($min_width, $current_width + $delta_x);
                break;
        }

        // Appliquer les contraintes maximales
        $new_width = min($new_width, $max_width);
        $new_height = min($new_height, $max_height);

        return [
            'position' => [
                'x' => round($new_x, 2),
                'y' => round($new_y, 2)
            ],
            'size' => [
                'width' => round($new_width, 2),
                'height' => round($new_height, 2)
            ]
        ];
    }

    /**
     * Détecter la poignée de redimensionnement à une position donnée
     */
    public function get_resize_handle($element, $mouse_x, $mouse_y, $threshold = 10) {
        $element_x = $element['position']['x'];
        $element_y = $element['position']['y'];
        $element_width = $element['size']['width'];
        $element_height = $element['size']['height'];

        $near_left = $mouse_x >= $element_x - $threshold && $mouse_x <= $element_x + $threshold;
        $near_right = $mouse_x >= $element_x + $element_width - $threshold && $mouse_x <= $element_x + $element_width + $threshold;
        $near_top = $mouse_y >= $element_y - $threshold && $mouse_y <= $element_y + $threshold;
        $near_bottom = $mouse_y >= $element_y + $element_height - $threshold && $mouse_y <= $element_y + $element_height + $threshold;

        if ($near_top && $near_left) return 'nw';
        if ($near_top && $near_right) return 'ne';
        if ($near_bottom && $near_left) return 'sw';
        if ($near_bottom && $near_right) return 'se';
        if ($near_top) return 'n';
        if ($near_bottom) return 's';
        if ($near_left) return 'w';
        if ($near_right) return 'e';

        return null;
    }

    /**
     * Obtenir le curseur approprié pour une poignée de redimensionnement
     */
    public function get_resize_cursor($handle) {
        $cursors = [
            'nw' => 'nw-resize',
            'ne' => 'ne-resize',
            'sw' => 'sw-resize',
            'se' => 'se-resize',
            'n' => 'n-resize',
            's' => 's-resize',
            'w' => 'w-resize',
            'e' => 'e-resize'
        ];

        return $cursors[$handle] ?? 'default';
    }

    /**
     * Vérifier les collisions entre éléments
     */
    public function check_collisions($element, $other_elements, $tolerance = 0) {
        $element_bounds = [
            'x' => $element['position']['x'] - $tolerance,
            'y' => $element['position']['y'] - $tolerance,
            'width' => $element['size']['width'] + (2 * $tolerance),
            'height' => $element['size']['height'] + (2 * $tolerance)
        ];

        $collisions = [];

        foreach ($other_elements as $other_element) {
            if ($other_element['id'] === $element['id']) continue;

            $other_bounds = [
                'x' => $other_element['position']['x'],
                'y' => $other_element['position']['y'],
                'width' => $other_element['size']['width'],
                'height' => $other_element['size']['height']
            ];

            if ($this->rectangles_overlap($element_bounds, $other_bounds)) {
                $collisions[] = $other_element['id'];
            }
        }

        return $collisions;
    }

    /**
     * Vérifier si deux rectangles se chevauchent
     */
    private function rectangles_overlap($rect1, $rect2) {
        return !(
            $rect1['x'] + $rect1['width'] < $rect2['x'] ||
            $rect2['x'] + $rect2['width'] < $rect1['x'] ||
            $rect1['y'] + $rect1['height'] < $rect2['y'] ||
            $rect2['y'] + $rect2['height'] < $rect1['y']
        );
    }

    /**
     * Aligner un élément sur la grille
     */
    public function snap_to_grid($position, $grid_size = 10) {
        return [
            'x' => round($position['x'] / $grid_size) * $grid_size,
            'y' => round($position['y'] / $grid_size) * $grid_size
        ];
    }

    /**
     * Sauvegarder les éléments du canvas
     */
    public function save_canvas_elements($template_id, $elements) {
        // Valider et sanitiser tous les éléments
        $sanitized_elements = [];
        foreach ($elements as $element) {
            $errors = $this->validate_element_data($element);
            if (!empty($errors)) {
                return new WP_Error('validation_error', 'Invalid element data: ' . implode(', ', $errors));
            }

            // Sanitiser les propriétés de l'élément
            $sanitized_elements[] = $this->sanitize_element_properties($element);
        }

        // Sauvegarder en base de données
        $result = $this->save_template_elements_to_db($template_id, $sanitized_elements);

        return $result;
    }

    /**
     * Charger les éléments du canvas
     */
    public function load_canvas_elements($template_id) {
        // Charger depuis la base de données
        $elements = $this->get_template_elements_from_db($template_id);

        return $elements ?: [];
    }
}

