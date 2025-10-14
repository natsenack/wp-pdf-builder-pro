<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Resize Manager
 * Gestion des interactions de redimensionnement des éléments
 *
 * @version 1.0.0
 */



/**
 * Classe pour gérer les interactions de redimensionnement
 */
class PDF_Builder_Resize_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Resize_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire des éléments du canvas
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private $elements_manager = null;

    /**
     * État des sessions de redimensionnement actives
     * @var array
     */
    private $active_resize_sessions = [];

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
        if (class_exists('PDF_Builder_Canvas_Elements_Manager')) {
            $this->elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        }
    }

    /**
     * Démarrer une session de redimensionnement
     */
    public function start_resize_session($session_id, $element_id, $handle, $start_position, $initial_size, $constraints = []) {
        $session = [
            'id' => $session_id,
            'element_id' => $element_id,
            'handle' => $handle,
            'start_position' => $start_position,
            'initial_size' => $initial_size,
            'current_size' => $initial_size,
            'current_position' => ['x' => 0, 'y' => 0], // Sera calculé
            'constraints' => array_merge([
                'min_width' => 10,
                'min_height' => 10,
                'max_width' => 2000,
                'max_height' => 2000,
                'maintain_aspect_ratio' => false,
                'aspect_ratio' => null
            ], $constraints),
            'start_time' => time(),
            'is_active' => true,
            'has_resized' => false
        ];

        $this->active_resize_sessions[$session_id] = $session;

        // Logger l'événement
        $this->log_resize_event('start', $session_id, [
            'element_id' => $element_id,
            'handle' => $handle,
            'initial_size' => $initial_size
        ]);

        return $session;
    }

    /**
     * Mettre à jour les dimensions pendant le redimensionnement
     */
    public function update_resize_dimensions($session_id, $delta_x, $delta_y, $element_position = null) {
        if (!isset($this->active_resize_sessions[$session_id])) {
            return new WP_Error('session_not_found', 'Resize session not found');
        }

        $session = &$this->active_resize_sessions[$session_id];

        if (!$session['is_active']) {
            return new WP_Error('session_inactive', 'Resize session is not active');
        }

        // Créer un élément temporaire pour les calculs
        $temp_element = [
            'id' => $session['element_id'],
            'position' => $element_position ?: ['x' => 0, 'y' => 0],
            'size' => $session['initial_size']
        ];

        // Calculer les nouvelles dimensions
        $result = $this->elements_manager->calculate_resize_dimensions(
            $temp_element,
            $session['handle'],
            $delta_x,
            $delta_y,
            $session['constraints']
        );

        // Appliquer le ratio d'aspect si nécessaire
        if ($session['constraints']['maintain_aspect_ratio'] && $session['constraints']['aspect_ratio']) {
            $result = $this->apply_aspect_ratio($result, $session['constraints']['aspect_ratio'], $session['handle']);
        }

        $session['current_size'] = $result['size'];
        $session['current_position'] = $result['position'];
        $session['has_resized'] = true;

        return $result;
    }

    /**
     * Appliquer le ratio d'aspect pendant le redimensionnement
     */
    private function apply_aspect_ratio($dimensions, $aspect_ratio, $handle) {
        $width = $dimensions['size']['width'];
        $height = $dimensions['size']['height'];

        // Calculer la nouvelle hauteur basée sur la largeur et le ratio
        $calculated_height = $width / $aspect_ratio;

        // Pour certains poignées, ajuster la largeur au lieu de la hauteur
        $adjust_width = in_array($handle, ['n', 's']);
        $adjust_height = in_array($handle, ['w', 'e']);

        if ($adjust_width) {
            $dimensions['size']['width'] = $height * $aspect_ratio;
        } elseif ($adjust_height) {
            $dimensions['size']['height'] = $width / $aspect_ratio;
        } else {
            // Pour les poignées diagonales, trouver le meilleur compromis
            if (abs($calculated_height - $height) < abs(($width * $aspect_ratio) - $width)) {
                $dimensions['size']['height'] = $calculated_height;
            } else {
                $dimensions['size']['width'] = $height * $aspect_ratio;
            }
        }

        return $dimensions;
    }

    /**
     * Finaliser une session de redimensionnement
     */
    public function end_resize_session($session_id, $final_dimensions = null) {
        if (!isset($this->active_resize_sessions[$session_id])) {
            return new WP_Error('session_not_found', 'Resize session not found');
        }

        $session = &$this->active_resize_sessions[$session_id];

        if ($final_dimensions) {
            $session['current_size'] = $final_dimensions['size'];
            $session['current_position'] = $final_dimensions['position'];
        }

        $session['is_active'] = false;
        $session['end_time'] = time();
        $session['duration'] = $session['end_time'] - $session['start_time'];

        // Logger l'événement
        $this->log_resize_event('end', $session_id, [
            'duration' => $session['duration'],
            'has_resized' => $session['has_resized'],
            'final_size' => $session['current_size'],
            'final_position' => $session['current_position']
        ]);

        $result = $session;

        // Nettoyer la session après un délai
        wp_schedule_single_event(time() + 300, 'pdf_builder_cleanup_resize_session', [$session_id]);

        return $result;
    }

    /**
     * Obtenir l'état d'une session de redimensionnement
     */
    public function get_resize_session($session_id) {
        return $this->active_resize_sessions[$session_id] ?? null;
    }

    /**
     * Nettoyer une session de redimensionnement
     */
    public function cleanup_resize_session($session_id) {
        if (isset($this->active_resize_sessions[$session_id])) {
            unset($this->active_resize_sessions[$session_id]);
        }
    }

    /**
     * Valider les données de redimensionnement
     */
    public function validate_resize_data($element_id, $handle, $initial_size) {
        $errors = [];

        if (empty($element_id)) {
            $errors[] = 'Element ID is required';
        }

        $valid_handles = ['nw', 'ne', 'sw', 'se', 'n', 's', 'w', 'e'];
        if (!in_array($handle, $valid_handles)) {
            $errors[] = 'Invalid resize handle';
        }

        if (!is_array($initial_size) ||
            !isset($initial_size['width']) ||
            !isset($initial_size['height'])) {
            $errors[] = 'Invalid initial size format';
        }

        return $errors;
    }

    /**
     * Calculer les contraintes de redimensionnement pour un élément
     */
    public function calculate_resize_constraints($element, $canvas_bounds = null, $options = []) {
        $constraints = [
            'min_width' => $options['min_width'] ?? 10,
            'min_height' => $options['min_height'] ?? 10,
            'max_width' => $element['size']['width'] * ($options['max_scale'] ?? 5),
            'max_height' => $element['size']['height'] * ($options['max_scale'] ?? 5),
            'maintain_aspect_ratio' => $options['maintain_aspect_ratio'] ?? false,
            'aspect_ratio' => null
        ];

        // Calculer le ratio d'aspect si nécessaire
        if ($constraints['maintain_aspect_ratio']) {
            $constraints['aspect_ratio'] = $element['size']['width'] / $element['size']['height'];
        }

        // Appliquer les contraintes du canvas
        if ($canvas_bounds) {
            $constraints['max_width'] = min($constraints['max_width'], $canvas_bounds['width']);
            $constraints['max_height'] = min($constraints['max_height'], $canvas_bounds['height']);
        }

        return $constraints;
    }

    /**
     * Obtenir le curseur approprié pour une poignée de redimensionnement
     */
    public function get_resize_cursor($handle) {
        return $this->elements_manager->get_resize_cursor($handle);
    }

    /**
     * Logger les événements de redimensionnement
     */
    private function log_resize_event($event_type, $session_id, $data = []) {
        $logger = PDF_Builder_Logger::getInstance();
        if ($logger) {
            $logger->log('resize', $event_type, array_merge([
                'session_id' => $session_id
            ], $data));
        }
    }

    /**
     * Obtenir les statistiques de performance du redimensionnement
     */
    public function get_resize_performance_stats() {
        $stats = [
            'active_sessions' => count($this->active_resize_sessions),
            'total_sessions_today' => 0, // À implémenter avec historique
            'average_resize_duration' => 0, // À implémenter
            'constraint_violations' => 0 // À implémenter
        ];

        return $stats;
    }
}


