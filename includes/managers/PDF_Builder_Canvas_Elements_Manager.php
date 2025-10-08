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
        $required_fields = ['id', 'type', 'position', 'size'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (!isset($element_data[$field])) {
                $errors[] = "Champ requis manquant: {$field}";
            }
        }

        // Validation de la position
        if (isset($element_data['position'])) {
            if (!is_array($element_data['position']) ||
                !isset($element_data['position']['x']) ||
                !isset($element_data['position']['y'])) {
                $errors[] = "Position invalide";
            }
        }

        // Validation de la taille
        if (isset($element_data['size'])) {
            if (!is_array($element_data['size']) ||
                !isset($element_data['size']['width']) ||
                !isset($element_data['size']['height'])) {
                $errors[] = "Taille invalide";
            }

            // Vérifier les tailles minimales
            if ($element_data['size']['width'] < 10 || $element_data['size']['height'] < 10) {
                $errors[] = "Taille minimale: 10x10 pixels";
            }
        }

        return $errors;
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
        if (!$this->db_manager) {
            return new WP_Error('db_manager_missing', 'Database manager not available');
        }

        // Valider tous les éléments
        foreach ($elements as $element) {
            $errors = $this->validate_element_data($element);
            if (!empty($errors)) {
                return new WP_Error('validation_error', 'Invalid element data: ' . implode(', ', $errors));
            }
        }

        // Sauvegarder en base de données
        $result = $this->db_manager->save_template_elements($template_id, $elements);

        // Invalider le cache
        if ($this->cache_manager) {
            $this->cache_manager->invalidate_template_cache($template_id);
        }

        return $result;
    }

    /**
     * Charger les éléments du canvas
     */
    public function load_canvas_elements($template_id) {
        if (!$this->db_manager) {
            return [];
        }

        // Essayer le cache d'abord
        if ($this->cache_manager) {
            $cached_elements = $this->cache_manager->get_template_elements($template_id);
            if ($cached_elements !== false) {
                return $cached_elements;
            }
        }

        // Charger depuis la base de données
        $elements = $this->db_manager->get_template_elements($template_id);

        // Mettre en cache
        if ($this->cache_manager && !empty($elements)) {
            $this->cache_manager->set_template_elements($template_id, $elements);
        }

        return $elements ?: [];
    }
}