<?php
/**
 * PDF Builder Pro - Canvas Manager
 *
 * Gère les paramètres et fonctionnalités du canvas d'édition
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer les paramètres du canvas
 */
class PDF_Builder_Canvas_Manager {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Paramètres par défaut du canvas
     */
    private $default_settings = [];

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_default_settings();
        $this->init_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les paramètres par défaut
     */
    private function init_default_settings() {
        $this->default_settings = [
            // Dimensions
            'default_canvas_width' => 794,      // A4 width in pixels
            'default_canvas_height' => 1123,    // A4 height in pixels

            // Couleurs
            'canvas_background_color' => '#ffffff',
            'container_background_color' => '#f8f9fa',

            // Marges
            'show_margins' => false,
            'margin_top' => 28,
            'margin_right' => 28,
            'margin_bottom' => 28,
            'margin_left' => 10,

            // Grille
            'show_grid' => false,
            'grid_size' => 10,
            'grid_color' => '#e0e0e0',
            'snap_to_grid' => false,
            'snap_to_elements' => false,
            'snap_tolerance' => 5,
            'show_guides' => false,

            // Zoom
            'default_zoom' => 100,
            'zoom_step' => 25,
            'min_zoom' => 10,
            'max_zoom' => 500,
            'zoom_with_wheel' => false,
            'pan_with_mouse' => false,

            // Manipulation
            'show_resize_handles' => false,
            'handle_size' => 8,
            'enable_rotation' => false,
            'rotation_step' => 15,
            'multi_select' => false,
            'copy_paste_enabled' => false,

            // Undo/Redo
            'undo_levels' => 50,
            'redo_levels' => 50,
            'auto_save_versions' => 10,
        ];
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Hook pour l'initialisation du canvas
        add_action('pdf_builder_canvas_init', array($this, 'init_canvas_settings'), 10, 1);

        // Hook pour la validation des paramètres canvas
        add_filter('pdf_builder_validate_canvas_settings', array($this, 'validate_settings'), 10, 1);
    }

    /**
     * Obtenir tous les paramètres canvas
     */
    public function get_canvas_settings() {
        // Les paramètres canvas sont sauvegardés dans pdf_builder_settings
        $all_settings = get_option('pdf_builder_settings', []);
        
        // Extraire seulement les paramètres canvas
        $canvas_settings = [];
        $canvas_keys = array_keys($this->default_settings);
        foreach ($canvas_keys as $key) {
            if (isset($all_settings[$key])) {
                $canvas_settings[$key] = $all_settings[$key];
            }
        }

        // Fusionner avec les paramètres par défaut
        return array_merge($this->default_settings, $canvas_settings);
    }

    /**
     * Sauvegarder les paramètres canvas
     */
    public function save_canvas_settings($settings) {
        // Valider les paramètres
        $validated_settings = $this->validate_settings($settings);

        // Récupérer tous les paramètres existants
        $all_settings = get_option('pdf_builder_settings', []);
        
        // Mettre à jour seulement les paramètres canvas
        $updated_settings = array_merge($all_settings, $validated_settings);
        
        // Sauvegarder
        update_option('pdf_builder_settings', $updated_settings);

        // Logger
        error_log('PDF Builder: Canvas settings saved - ' . count($validated_settings) . ' parameters');

        return $validated_settings;
    }

    /**
     * Obtenir un paramètre spécifique
     */
    public function get_setting($key, $default = null) {
        $settings = $this->get_canvas_settings();

        if ($default === null && isset($this->default_settings[$key])) {
            $default = $this->default_settings[$key];
        }

        return $settings[$key] ?? $default;
    }

    /**
     * Initialiser les paramètres du canvas pour l'éditeur
     */
    public function init_canvas_settings($canvas_data) {
        $settings = $this->get_canvas_settings();

        // Appliquer les paramètres par défaut si non définis
        if (!isset($canvas_data['width']) || empty($canvas_data['width'])) {
            $canvas_data['width'] = $settings['default_canvas_width'];
        }
        if (!isset($canvas_data['height']) || empty($canvas_data['height'])) {
            $canvas_data['height'] = $settings['default_canvas_height'];
        }
        if (!isset($canvas_data['backgroundColor'])) {
            $canvas_data['backgroundColor'] = $settings['canvas_background_color'];
        }

        return $canvas_data;
    }

    /**
     * Valider les paramètres canvas
     */
    public function validate_settings($settings) {
        $validated = [];

        // Dimensions
        $validated['default_canvas_width'] = $this->validate_numeric($settings['default_canvas_width'] ?? null, 50, 2000, $this->default_settings['default_canvas_width']);
        $validated['default_canvas_height'] = $this->validate_numeric($settings['default_canvas_height'] ?? null, 50, 2000, $this->default_settings['default_canvas_height']);

        // Couleurs
        $validated['canvas_background_color'] = $this->validate_color($settings['canvas_background_color'] ?? null, $this->default_settings['canvas_background_color']);
        $validated['container_background_color'] = $this->validate_color($settings['container_background_color'] ?? null, $this->default_settings['container_background_color']);

        // Marges
        $validated['show_margins'] = (isset($settings['show_margins']) && ($settings['show_margins'] === '1' || $settings['show_margins'] === true));
        $validated['margin_top'] = $this->validate_numeric($settings['margin_top'] ?? null, 0, 200, $this->default_settings['margin_top']);
        $validated['margin_right'] = $this->validate_numeric($settings['margin_right'] ?? null, 0, 200, $this->default_settings['margin_right']);
        $validated['margin_bottom'] = $this->validate_numeric($settings['margin_bottom'] ?? null, 0, 200, $this->default_settings['margin_bottom']);
        $validated['margin_left'] = $this->validate_numeric($settings['margin_left'] ?? null, 0, 200, $this->default_settings['margin_left']);

        // Grille
        $validated['show_grid'] = (isset($settings['show_grid']) && ($settings['show_grid'] === '1' || $settings['show_grid'] === true));
        $validated['grid_size'] = $this->validate_numeric($settings['grid_size'] ?? null, 5, 100, $this->default_settings['grid_size']);
        $validated['grid_color'] = $this->validate_color($settings['grid_color'] ?? null, $this->default_settings['grid_color']);
        $validated['snap_to_grid'] = (isset($settings['snap_to_grid']) && ($settings['snap_to_grid'] === '1' || $settings['snap_to_grid'] === true));
        $validated['snap_to_elements'] = (isset($settings['snap_to_elements']) && ($settings['snap_to_elements'] === '1' || $settings['snap_to_elements'] === true));
        $validated['snap_tolerance'] = $this->validate_numeric($settings['snap_tolerance'] ?? null, 1, 50, $this->default_settings['snap_tolerance']);
        $validated['show_guides'] = (isset($settings['show_guides']) && ($settings['show_guides'] === '1' || $settings['show_guides'] === true));

        // Zoom
        $validated['default_zoom'] = $this->validate_numeric($settings['default_zoom'] ?? null, 10, 500, $this->default_settings['default_zoom']);
        $validated['zoom_step'] = $this->validate_numeric($settings['zoom_step'] ?? null, 5, 100, $this->default_settings['zoom_step']);
        $validated['min_zoom'] = $this->validate_numeric($settings['min_zoom'] ?? null, 1, 100, $this->default_settings['min_zoom']);
        $validated['max_zoom'] = $this->validate_numeric($settings['max_zoom'] ?? null, 100, 2000, $this->default_settings['max_zoom']);
        $validated['zoom_with_wheel'] = (isset($settings['zoom_with_wheel']) && ($settings['zoom_with_wheel'] === '1' || $settings['zoom_with_wheel'] === true));
        $validated['pan_with_mouse'] = (isset($settings['pan_with_mouse']) && ($settings['pan_with_mouse'] === '1' || $settings['pan_with_mouse'] === true));

        // Manipulation
        $validated['show_resize_handles'] = (isset($settings['show_resize_handles']) && ($settings['show_resize_handles'] === '1' || $settings['show_resize_handles'] === true));
        $validated['handle_size'] = $this->validate_numeric($settings['handle_size'] ?? null, 4, 20, $this->default_settings['handle_size']);
        $validated['enable_rotation'] = (isset($settings['enable_rotation']) && ($settings['enable_rotation'] === '1' || $settings['enable_rotation'] === true));
        $validated['rotation_step'] = $this->validate_numeric($settings['rotation_step'] ?? null, 1, 90, $this->default_settings['rotation_step']);
        $validated['multi_select'] = (isset($settings['multi_select']) && ($settings['multi_select'] === '1' || $settings['multi_select'] === true));
        $validated['copy_paste_enabled'] = (isset($settings['copy_paste_enabled']) && ($settings['copy_paste_enabled'] === '1' || $settings['copy_paste_enabled'] === true));

        // Undo/Redo
        $validated['undo_levels'] = $this->validate_numeric($settings['undo_levels'] ?? null, 1, 500, $this->default_settings['undo_levels']);
        $validated['redo_levels'] = $this->validate_numeric($settings['redo_levels'] ?? null, 1, 500, $this->default_settings['redo_levels']);
        $validated['auto_save_versions'] = $this->validate_numeric($settings['auto_save_versions'] ?? null, 1, 100, $this->default_settings['auto_save_versions']);

        return $validated;
    }

    /**
     * Valider une valeur numérique
     */
    private function validate_numeric($value, $min, $max, $default) {
        if (!is_numeric($value)) {
            return $default;
        }

        $num = intval($value);
        return max($min, min($max, $num));
    }

    /**
     * Valider une couleur hexadécimale
     */
    private function validate_color($value, $default) {
        if (!is_string($value) || !preg_match('/^#[a-fA-F0-9]{6}$/', $value)) {
            return $default;
        }
        return $value;
    }

    /**
     * Réinitialiser les paramètres aux valeurs par défaut
     */
    public function reset_to_defaults() {
        delete_option('pdf_builder_canvas_settings');
        return $this->default_settings;
    }
}