<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Canvas Interactions Manager
 * Gestionnaire principal des interactions du canvas (drag & drop + redimensionnement)
 *
 * @version 1.0.0
 */



/**
 * Classe principale pour gérer toutes les interactions du canvas
 */
class PDF_Builder_Canvas_Interactions_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Canvas_Interactions_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire des éléments du canvas
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private $elements_manager = null;

    /**
     * Gestionnaire de drag & drop
     * @var PDF_Builder_Drag_Drop_Manager
     */
    private $drag_manager = null;

    /**
     * Gestionnaire de redimensionnement
     * @var PDF_Builder_Resize_Manager
     */
    private $resize_manager = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager = null;

    /**
     * Constructeur privé (singleton)
     */
    private function __construct() {
        $this->init_dependencies();
        $this->init_hooks();
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
        $this->elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        $this->drag_manager = PDF_Builder_Drag_Drop_Manager::getInstance();
        $this->resize_manager = PDF_Builder_Resize_Manager::getInstance();

        if (class_exists('PDF_Builder_Database_Manager')) {
            $this->db_manager = PDF_Builder_Database_Manager::getInstance();
        }
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        // AJAX handlers pour les interactions
        add_action('wp_ajax_pdf_builder_start_drag', [$this, 'ajax_start_drag']);
        add_action('wp_ajax_pdf_builder_update_drag', [$this, 'ajax_update_drag']);
        add_action('wp_ajax_pdf_builder_end_drag', [$this, 'ajax_end_drag']);

        add_action('wp_ajax_pdf_builder_start_resize', [$this, 'ajax_start_resize']);
        add_action('wp_ajax_pdf_builder_update_resize', [$this, 'ajax_update_resize']);
        add_action('wp_ajax_pdf_builder_end_resize', [$this, 'ajax_end_resize']);

        // Nettoyage des sessions
        add_action('pdf_builder_cleanup_drag_session', [$this, 'cleanup_drag_session']);
        add_action('pdf_builder_cleanup_resize_session', [$this, 'cleanup_resize_session']);
    }

    /**
     * Démarrer une session de drag via AJAX
     */
    public function ajax_start_drag() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_canvas_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }

            $element_ids = json_decode(stripslashes($_POST['element_ids'] ?? '[]'), true);
            $initial_positions = json_decode(stripslashes($_POST['initial_positions'] ?? '{}'), true);
            $canvas_bounds = isset($_POST['canvas_bounds']) ?
                json_decode(stripslashes($_POST['canvas_bounds']), true) : null;

            // Valider les données
            $errors = $this->drag_manager->validate_drag_data($element_ids, $initial_positions);
            if (!empty($errors)) {
                wp_send_json_error(['errors' => $errors]);
                return;
            }

            // Générer un ID de session unique
            $session_id = 'drag_' . session_id() . '_' . time() . '_' . uniqid();

            // Démarrer la session
            $session = $this->drag_manager->start_drag_session(
                $session_id,
                $element_ids,
                $initial_positions,
                $canvas_bounds
            );

            wp_send_json_success([
                'session_id' => $session_id,
                'session' => $session
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to start drag session: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour une session de drag via AJAX
     */
    public function ajax_update_drag() {
        try {
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $delta_x = floatval($_POST['delta_x'] ?? 0);
            $delta_y = floatval($_POST['delta_y'] ?? 0);
            $snap_to_grid = filter_var($_POST['snap_to_grid'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if (empty($session_id)) {
                wp_send_json_error('Session ID is required');
                return;
            }

            $new_positions = $this->drag_manager->update_drag_position(
                $session_id,
                $delta_x,
                $delta_y,
                $snap_to_grid
            );

            if (is_wp_error($new_positions)) {
                wp_send_json_error($new_positions->get_error_message());
                return;
            }

            wp_send_json_success([
                'positions' => $new_positions
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to update drag: ' . $e->getMessage());
        }
    }

    /**
     * Terminer une session de drag via AJAX
     */
    public function ajax_end_drag() {
        try {
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $final_positions = isset($_POST['final_positions']) ?
                json_decode(stripslashes($_POST['final_positions']), true) : null;

            if (empty($session_id)) {
                wp_send_json_error('Session ID is required');
                return;
            }

            $result = $this->drag_manager->end_drag_session($session_id, $final_positions);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
                return;
            }

            wp_send_json_success([
                'session' => $result
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to end drag session: ' . $e->getMessage());
        }
    }

    /**
     * Démarrer une session de redimensionnement via AJAX
     */
    public function ajax_start_resize() {
        try {
            $element_id = sanitize_text_field($_POST['element_id'] ?? '');
            $handle = sanitize_text_field($_POST['handle'] ?? '');
            $start_position = json_decode(stripslashes($_POST['start_position'] ?? '{}'), true);
            $initial_size = json_decode(stripslashes($_POST['initial_size'] ?? '{}'), true);
            $constraints = isset($_POST['constraints']) ?
                json_decode(stripslashes($_POST['constraints']), true) : [];

            // Valider les données
            $errors = $this->resize_manager->validate_resize_data($element_id, $handle, $initial_size);
            if (!empty($errors)) {
                wp_send_json_error(['errors' => $errors]);
                return;
            }

            // Générer un ID de session unique
            $session_id = 'resize_' . session_id() . '_' . time() . '_' . uniqid();

            // Démarrer la session
            $session = $this->resize_manager->start_resize_session(
                $session_id,
                $element_id,
                $handle,
                $start_position,
                $initial_size,
                $constraints
            );

            wp_send_json_success([
                'session_id' => $session_id,
                'session' => $session
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to start resize session: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour une session de redimensionnement via AJAX
     */
    public function ajax_update_resize() {
        try {
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $delta_x = floatval($_POST['delta_x'] ?? 0);
            $delta_y = floatval($_POST['delta_y'] ?? 0);
            $element_position = isset($_POST['element_position']) ?
                json_decode(stripslashes($_POST['element_position']), true) : null;

            if (empty($session_id)) {
                wp_send_json_error('Session ID is required');
                return;
            }

            $dimensions = $this->resize_manager->update_resize_dimensions(
                $session_id,
                $delta_x,
                $delta_y,
                $element_position
            );

            if (is_wp_error($dimensions)) {
                wp_send_json_error($dimensions->get_error_message());
                return;
            }

            wp_send_json_success([
                'dimensions' => $dimensions
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to update resize: ' . $e->getMessage());
        }
    }

    /**
     * Terminer une session de redimensionnement via AJAX
     */
    public function ajax_end_resize() {
        try {
            $session_id = sanitize_text_field($_POST['session_id'] ?? '');
            $final_dimensions = isset($_POST['final_dimensions']) ?
                json_decode(stripslashes($_POST['final_dimensions']), true) : null;

            if (empty($session_id)) {
                wp_send_json_error('Session ID is required');
                return;
            }

            $result = $this->resize_manager->end_resize_session($session_id, $final_dimensions);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
                return;
            }

            wp_send_json_success([
                'session' => $result
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Failed to end resize session: ' . $e->getMessage());
        }
    }

    /**
     * Nettoyer une session de drag
     */
    public function cleanup_drag_session($session_id) {
        $this->drag_manager->cleanup_drag_session($session_id);
    }

    /**
     * Nettoyer une session de redimensionnement
     */
    public function cleanup_resize_session($session_id) {
        $this->resize_manager->cleanup_resize_session($session_id);
    }

    /**
     * Obtenir les statistiques d'interaction
     */
    public function get_interaction_stats() {
        return [
            'drag' => $this->drag_manager->get_drag_performance_stats(),
            'resize' => $this->resize_manager->get_resize_performance_stats(),
            'elements' => [
                'total_elements' => 0, // À implémenter
                'active_elements' => 0  // À implémenter
            ]
        ];
    }

    /**
     * Traiter une interaction complète (drag + resize)
     */
    public function process_canvas_interaction($interaction_type, $data) {
        switch ($interaction_type) {
            case 'drag':
                return $this->process_drag_interaction($data);
            case 'resize':
                return $this->process_resize_interaction($data);
            default:
                return new WP_Error('invalid_interaction_type', 'Unknown interaction type');
        }
    }

    /**
     * Traiter une interaction de drag
     */
    private function process_drag_interaction($data) {
        // Logique pour traiter une interaction de drag complète
        // À implémenter selon les besoins
        return ['type' => 'drag', 'processed' => true];
    }

    /**
     * Traiter une interaction de redimensionnement
     */
    private function process_resize_interaction($data) {
        // Logique pour traiter une interaction de resize complète
        // À implémenter selon les besoins
        return ['type' => 'resize', 'processed' => true];
    }
}


