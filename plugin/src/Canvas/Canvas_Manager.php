<?php
namespace WP_PDF_Builder_Pro\Canvas;

/**
 * Canvas Manager
 * Gère les paramètres du canvas et les applique aux générations PDF/Image
 * 
 * @package WP_PDF_Builder_Pro
 * @since 1.1.0
 */
class Canvas_Manager {

    /** @var Canvas_Manager Instance unique */
    private static $instance = null;

    /** @var array Paramètres canvas en cache */
    private $settings = [];

    /**
     * Récupère l'instance unique
     * 
     * @return Canvas_Manager
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur
     */
    private function __construct() {
        $this->load_settings();
        $this->register_hooks();
    }

    /**
     * Charge les paramètres du canvas depuis WordPress
     */
    private function load_settings() {
        $this->settings = get_option('pdf_builder_canvas_settings', []);
        
        // Valeurs par défaut
        $defaults = $this->get_default_settings();
        $this->settings = array_merge($defaults, $this->settings);
    }

    /**
     * Récupère les paramètres par défaut
     * 
     * @return array
     */
    public function get_default_settings() {
        return [
            'default_canvas_width' => 794,
            'default_canvas_height' => 1123,
            'default_canvas_unit' => 'px',
            'default_orientation' => 'portrait',
            'canvas_background_color' => '#ffffff',
            'canvas_show_transparency' => false,
            'container_background_color' => '#f8f9fa',
            'container_show_transparency' => false,
            'margin_top' => 28,
            'margin_right' => 28,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'show_margins' => false,
            'show_grid' => false,
            'grid_size' => 10,
            'grid_color' => '#e0e0e0',
            'snap_to_grid' => false,
            'snap_to_elements' => false,
            'snap_tolerance' => 5,
            'show_guides' => false,
            'default_zoom' => 100,
            'zoom_step' => 25,
            'min_zoom' => 10,
            'max_zoom' => 500,
            'zoom_with_wheel' => false,
            'pan_with_mouse' => false,
            'show_resize_handles' => false,
            'handle_size' => 8,
            'handle_color' => '#007cba',
            'enable_rotation' => false,
            'rotation_step' => 15,
            'multi_select' => false,
            'copy_paste_enabled' => false,
            'export_quality' => 'print',
            'export_format' => 'pdf',
            'compress_images' => true,
            'image_quality' => 85,
            'max_image_size' => 2048,
            'include_metadata' => true,
            'pdf_author' => 'PDF Builder Pro',
            'pdf_subject' => '',
            'auto_crop' => false,
            'embed_fonts' => true,
            'optimize_for_web' => true,
            'enable_hardware_acceleration' => true,
            'limit_fps' => true,
            'max_fps' => 60,
            'auto_save_enabled' => true,
            'auto_save_interval' => 30,
            'auto_save_versions' => 10,
            'undo_levels' => 50,
            'redo_levels' => 50,
            'enable_keyboard_shortcuts' => true,
            'debug_mode' => false,
            'show_fps' => false
        ];
    }

    /**
     * Enregistre les hooks WordPress
     */
    private function register_hooks() {
        // Filtre pour appliquer les paramètres canvas à React
        add_filter('pdf_builder_react_settings', [$this, 'apply_canvas_settings_to_react'], 10, 1);
        
        // Action pour initialiser les paramètres canvas côté client
        add_action('admin_enqueue_scripts', [$this, 'enqueue_canvas_settings_script'], 15);
    }

    /**
     * Applique les paramètres canvas aux paramètres React
     * 
     * @param array $settings Paramètres React
     * @return array Paramètres modifiés
     */
    public function apply_canvas_settings_to_react($settings) {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        $settings['canvas'] = $this->settings;
        
        return $settings;
    }

    /**
     * Enregistre le script de paramètres canvas
     */
    public function enqueue_canvas_settings_script() {
        if (!is_admin()) {
            return;
        }
        
        $current_screen = get_current_screen();
        if (!$current_screen || $current_screen->base !== 'pdf-builder-pro_page_pdf-builder-settings') {
            return;
        }
        
        wp_add_inline_script('pdf-builder-react', $this->get_canvas_settings_script(), 'before');
    }

    /**
     * Génère le script d'initialisation des paramètres canvas
     * 
     * @return string
     */
    private function get_canvas_settings_script() {
        $settings = wp_json_encode($this->settings);
        
        return <<<JS
(function() {
    window.pdfBuilderCanvasSettings = {$settings};
    if (typeof pdfBuilderSettings !== 'undefined') {
        pdfBuilderSettings.canvas = window.pdfBuilderCanvasSettings;
    }
})();
JS;
    }

    /**
     * Récupère une valeur de paramètre canvas
     * 
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    public function get_setting($key, $default = null) {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        return $default;
    }

    /**
     * Récupère tous les paramètres
     * 
     * @return array
     */
    public function get_all_settings() {
        return $this->settings;
    }

    /**
     * Récupère les dimensions du canvas
     * 
     * @return array
     */
    public function get_canvas_dimensions() {
        return [
            'width' => $this->get_setting('default_canvas_width', 794),
            'height' => $this->get_setting('default_canvas_height', 1123),
            'unit' => $this->get_setting('default_canvas_unit', 'px'),
            'orientation' => $this->get_setting('default_orientation', 'portrait'),
        ];
    }

    /**
     * Récupère les marges du canvas
     * 
     * @return array
     */
    public function get_canvas_margins() {
        return [
            'top' => $this->get_setting('margin_top', 28),
            'right' => $this->get_setting('margin_right', 28),
            'bottom' => $this->get_setting('margin_bottom', 10),
            'left' => $this->get_setting('margin_left', 10),
        ];
    }

    /**
     * Récupère les paramètres de grille
     * 
     * @return array
     */
    public function get_grid_settings() {
        return [
            'show' => $this->get_setting('show_grid', false),
            'size' => $this->get_setting('grid_size', 10),
            'color' => $this->get_setting('grid_color', '#e0e0e0'),
            'snap_enabled' => $this->get_setting('snap_to_grid', false),
            'snap_tolerance' => $this->get_setting('snap_tolerance', 5),
        ];
    }

    /**
     * Récupère les paramètres de zoom
     * 
     * @return array
     */
    public function get_zoom_settings() {
        return [
            'default' => $this->get_setting('default_zoom', 100),
            'step' => $this->get_setting('zoom_step', 25),
            'min' => $this->get_setting('min_zoom', 10),
            'max' => $this->get_setting('max_zoom', 500),
            'wheel_enabled' => $this->get_setting('zoom_with_wheel', false),
        ];
    }

    /**
     * Récupère les paramètres de sélection
     * 
     * @return array
     */
    public function get_selection_settings() {
        return [
            'multi_select' => $this->get_setting('multi_select', false),
            'copy_paste' => $this->get_setting('copy_paste_enabled', false),
            'rotation' => $this->get_setting('enable_rotation', false),
            'rotation_step' => $this->get_setting('rotation_step', 15),
            'show_handles' => $this->get_setting('show_resize_handles', false),
            'handle_size' => $this->get_setting('handle_size', 8),
        ];
    }

    /**
     * Récupère les paramètres d'export
     * 
     * @return array
     */
    public function get_export_settings() {
        return [
            'quality' => $this->get_setting('export_quality', 'print'),
            'format' => $this->get_setting('export_format', 'pdf'),
            'compress_images' => $this->get_setting('compress_images', true),
            'image_quality' => $this->get_setting('image_quality', 85),
            'max_image_size' => $this->get_setting('max_image_size', 2048),
            'include_metadata' => $this->get_setting('include_metadata', true),
        ];
    }

    /**
     * Récupère les paramètres d'historique
     * 
     * @return array
     */
    public function get_history_settings() {
        return [
            'undo_levels' => $this->get_setting('undo_levels', 50),
            'redo_levels' => $this->get_setting('redo_levels', 50),
            'auto_save' => $this->get_setting('auto_save_enabled', true),
            'auto_save_interval' => $this->get_setting('auto_save_interval', 30),
            'auto_save_versions' => $this->get_setting('auto_save_versions', 10),
        ];
    }

    /**
     * Vérifie si une fonctionnalité est activée
     * 
     * @param string $feature Nom de la fonctionnalité
     * @return bool
     */
    public function is_feature_enabled($feature) {
        return (bool) $this->get_setting($feature, false);
    }

    /**
     * Réinitialise les paramètres aux valeurs par défaut
     */
    public function reset_to_defaults() {
        $this->settings = $this->get_default_settings();
        update_option('pdf_builder_canvas_settings', $this->settings);
    }

    /**
     * Sauvegarde les paramètres
     * 
     * @param array $settings Paramètres à sauvegarder
     * @return bool
     */
    public function save_settings($settings) {
        // Valider les paramètres
        $validated = $this->validate_settings($settings);
        
        // Fusionner avec les paramètres existants
        $this->settings = array_merge($this->settings, $validated);
        
        // Sauvegarder dans WordPress
        $saved = update_option('pdf_builder_canvas_settings', $this->settings);
        
        do_action('pdf_builder_canvas_settings_updated', $this->settings);
        
        return $saved;
    }

    /**
     * Valide les paramètres
     * 
     * @param array $settings Paramètres à valider
     * @return array Paramètres validés
     */
    private function validate_settings($settings) {
        $validated = [];
        
        // Dimensions
        if (isset($settings['default_canvas_width'])) {
            $validated['default_canvas_width'] = intval($settings['default_canvas_width']);
        }
        if (isset($settings['default_canvas_height'])) {
            $validated['default_canvas_height'] = intval($settings['default_canvas_height']);
        }
        
        // Couleurs
        if (isset($settings['canvas_background_color'])) {
            $validated['canvas_background_color'] = sanitize_text_field($settings['canvas_background_color']);
        }
        if (isset($settings['container_background_color'])) {
            $validated['container_background_color'] = sanitize_text_field($settings['container_background_color']);
        }
        if (isset($settings['grid_color'])) {
            $validated['grid_color'] = sanitize_text_field($settings['grid_color']);
        }
        if (isset($settings['handle_color'])) {
            $validated['handle_color'] = sanitize_text_field($settings['handle_color']);
        }
        
        // Marges et espacement
        foreach (['margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'grid_size', 'snap_tolerance', 'rotation_step', 'handle_size', 'max_fps'] as $key) {
            if (isset($settings[$key])) {
                $validated[$key] = intval($settings[$key]);
            }
        }
        
        // Zoom
        foreach (['default_zoom', 'zoom_step', 'min_zoom', 'max_zoom'] as $key) {
            if (isset($settings[$key])) {
                $validated[$key] = intval($settings[$key]);
            }
        }
        
        // Image quality
        if (isset($settings['image_quality'])) {
            $validated['image_quality'] = max(30, min(100, intval($settings['image_quality'])));
        }
        if (isset($settings['max_image_size'])) {
            $validated['max_image_size'] = intval($settings['max_image_size']);
        }
        
        // Historique
        foreach (['undo_levels', 'redo_levels', 'auto_save_interval', 'auto_save_versions'] as $key) {
            if (isset($settings[$key])) {
                $validated[$key] = intval($settings[$key]);
            }
        }
        
        // Booleans
        foreach (['canvas_show_transparency', 'container_show_transparency', 'show_margins', 'show_grid', 'snap_to_grid', 'snap_to_elements', 'show_guides', 'zoom_with_wheel', 'pan_with_mouse', 'show_resize_handles', 'enable_rotation', 'multi_select', 'copy_paste_enabled', 'compress_images', 'include_metadata', 'auto_crop', 'embed_fonts', 'optimize_for_web', 'enable_hardware_acceleration', 'limit_fps', 'auto_save_enabled', 'enable_keyboard_shortcuts', 'debug_mode', 'show_fps'] as $key) {
            if (isset($settings[$key])) {
                $validated[$key] = (bool) $settings[$key];
            }
        }
        
        // Texte
        if (isset($settings['export_quality'])) {
            $validated['export_quality'] = sanitize_text_field($settings['export_quality']);
        }
        if (isset($settings['export_format'])) {
            $validated['export_format'] = sanitize_text_field($settings['export_format']);
        }
        if (isset($settings['pdf_author'])) {
            $validated['pdf_author'] = sanitize_text_field($settings['pdf_author']);
        }
        if (isset($settings['pdf_subject'])) {
            $validated['pdf_subject'] = sanitize_text_field($settings['pdf_subject']);
        }
        
        return $validated;
    }
}
