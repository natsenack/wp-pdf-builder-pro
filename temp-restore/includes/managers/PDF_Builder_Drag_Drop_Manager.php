<?php
/**
 * PDF Builder Pro - Drag & Drop Manager
 * Gestion des interactions de déplacement des éléments
 *
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe pour gérer les interactions de drag & drop
 */
class PDF_Builder_Drag_Drop_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Drag_Drop_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire des éléments du canvas
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private $elements_manager = null;

    /**
     * État des sessions de drag actives
     * @var array
     */
    private $active_drag_sessions = [];

    /**
     * Constructeur privé (singleton)
     */
    private function __construct() {
        $this->init_dependencies();
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les dépendances
     */
    private function init_dependencies() {
        if (class_exists('PDF_Builder_Canvas_Elements_Manager')) {
            $this->elements_manager = PDF_Builder_Canvas_Elements_Manager::get_instance();
        }
    }

    /**
     * Démarrer une session de drag
     */
    public function start_drag_session($session_id, $element_ids, $initial_positions, $canvas_bounds = null) {
        $session = [
            'id' => $session_id,
            'element_ids' => $element_ids,
            'initial_positions' => $initial_positions,
            'current_positions' => $initial_positions,
            'canvas_bounds' => $canvas_bounds,
            'start_time' => time(),
            'is_active' => true,
            'drag_threshold' => 5, // pixels
            'has_moved' => false
        ];

        $this->active_drag_sessions[$session_id] = $session;

        // Logger l'événement
        $this->log_drag_event('start', $session_id, [
            'element_count' => count($element_ids),
            'canvas_bounds' => $canvas_bounds
        ]);

        return $session;
    }

    /**
     * Mettre à jour la position pendant le drag
     */
    public function update_drag_position($session_id, $delta_x, $delta_y, $snap_to_grid = false, $grid_size = 10) {
        if (!isset($this->active_drag_sessions[$session_id])) {
            return new WP_Error('session_not_found', 'Drag session not found');
        }

        $session = &$this->active_drag_sessions[$session_id];

        if (!$session['is_active']) {
            return new WP_Error('session_inactive', 'Drag session is not active');
        }

        // Vérifier le seuil de mouvement
        $total_movement = abs($delta_x) + abs($delta_y);
        if ($total_movement >= $session['drag_threshold']) {
            $session['has_moved'] = true;
        }

        // Calculer les nouvelles positions
        $new_positions = [];
        foreach ($session['element_ids'] as $element_id) {
            if (!isset($session['initial_positions'][$element_id])) {
                continue;
            }

            $initial_pos = $session['initial_positions'][$element_id];
            $new_pos = [
                'x' => $initial_pos['x'] + $delta_x,
                'y' => $initial_pos['y'] + $delta_y
            ];

            // Appliquer l'alignement sur la grille si demandé
            if ($snap_to_grid) {
                $new_pos = $this->elements_manager->snap_to_grid($new_pos, $grid_size);
            }

            // Appliquer les contraintes du canvas
            if ($session['canvas_bounds']) {
                // Note: Ici on aurait besoin des dimensions de l'élément pour calculer les vraies contraintes
                // Pour l'instant, on applique juste des contraintes basiques
                $new_pos['x'] = max(0, $new_pos['x']);
                $new_pos['y'] = max(0, $new_pos['y']);
            }

            $new_positions[$element_id] = $new_pos;
        }

        $session['current_positions'] = $new_positions;

        return $new_positions;
    }

    /**
     * Finaliser une session de drag
     */
    public function end_drag_session($session_id, $final_positions = null) {
        if (!isset($this->active_drag_sessions[$session_id])) {
            return new WP_Error('session_not_found', 'Drag session not found');
        }

        $session = &$this->active_drag_sessions[$session_id];

        if ($final_positions) {
            $session['current_positions'] = $final_positions;
        }

        $session['is_active'] = false;
        $session['end_time'] = time();
        $session['duration'] = $session['end_time'] - $session['start_time'];

        // Logger l'événement
        $this->log_drag_event('end', $session_id, [
            'duration' => $session['duration'],
            'has_moved' => $session['has_moved'],
            'final_positions' => $session['current_positions']
        ]);

        $result = $session;

        // Nettoyer la session après un délai
        wp_schedule_single_event(time() + 300, 'pdf_builder_cleanup_drag_session', [$session_id]);

        return $result;
    }

    /**
     * Obtenir l'état d'une session de drag
     */
    public function get_drag_session($session_id) {
        return $this->active_drag_sessions[$session_id] ?? null;
    }

    /**
     * Nettoyer une session de drag
     */
    public function cleanup_drag_session($session_id) {
        if (isset($this->active_drag_sessions[$session_id])) {
            unset($this->active_drag_sessions[$session_id]);
        }
    }

    /**
     * Valider les données de drag
     */
    public function validate_drag_data($element_ids, $initial_positions) {
        $errors = [];

        if (empty($element_ids) || !is_array($element_ids)) {
            $errors[] = 'Element IDs must be a non-empty array';
        }

        if (empty($initial_positions) || !is_array($initial_positions)) {
            $errors[] = 'Initial positions must be a non-empty array';
        }

        foreach ($element_ids as $element_id) {
            if (!isset($initial_positions[$element_id])) {
                $errors[] = "Missing initial position for element {$element_id}";
            } elseif (!is_array($initial_positions[$element_id]) ||
                     !isset($initial_positions[$element_id]['x']) ||
                     !isset($initial_positions[$element_id]['y'])) {
                $errors[] = "Invalid position format for element {$element_id}";
            }
        }

        return $errors;
    }

    /**
     * Calculer les collisions pendant le drag
     */
    public function calculate_drag_collisions($session_id, $all_elements) {
        if (!isset($this->active_drag_sessions[$session_id])) {
            return [];
        }

        $session = $this->active_drag_sessions[$session_id];
        $collisions = [];

        foreach ($session['element_ids'] as $element_id) {
            if (!isset($session['current_positions'][$element_id])) {
                continue;
            }

            // Créer un élément temporaire avec la position actuelle
            $temp_element = [
                'id' => $element_id,
                'position' => $session['current_positions'][$element_id],
                'size' => ['width' => 100, 'height' => 50] // Valeurs par défaut, à améliorer
            ];

            // Trouver l'élément original pour obtenir sa vraie taille
            foreach ($all_elements as $element) {
                if ($element['id'] === $element_id) {
                    $temp_element['size'] = $element['size'];
                    break;
                }
            }

            // Calculer les collisions
            $element_collisions = $this->elements_manager->check_collisions($temp_element, $all_elements, 5);
            if (!empty($element_collisions)) {
                $collisions[$element_id] = $element_collisions;
            }
        }

        return $collisions;
    }

    /**
     * Logger les événements de drag
     */
    private function log_drag_event($event_type, $session_id, $data = []) {
        $logger = PDF_Builder_Logger::getInstance();
        if ($logger) {
            $logger->log('drag_drop', $event_type, array_merge([
                'session_id' => $session_id
            ], $data));
        }
    }

    /**
     * Obtenir les statistiques de performance du drag
     */
    public function get_drag_performance_stats() {
        $stats = [
            'active_sessions' => count($this->active_drag_sessions),
            'total_sessions_today' => 0, // À implémenter avec historique
            'average_drag_duration' => 0, // À implémenter
            'collision_rate' => 0 // À implémenter
        ];

        return $stats;
    }
}