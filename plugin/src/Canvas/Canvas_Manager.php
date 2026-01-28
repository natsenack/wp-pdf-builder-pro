<?php

namespace PDF_Builder\Canvas;

// Déclarations des fonctions WordPress pour l'IDE
if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1) { return true; }
}
if (!function_exists('is_admin')) {
    function is_admin() { return false; }
}
if (!function_exists('get_current_screen')) {
    function get_current_screen() { return null; }
}
if (!function_exists('do_action')) {
    function do_action($tag, ...$args) { return null; }
}

class_exists('\PDF_Builder\Admin\PdfBuilderAdminNew') || class_alias('stdClass', '\PDF_Builder\Admin\PdfBuilderAdminNew');

/**
 * @global function \add_filter
 * @global function \is_admin
 * @global function \get_current_screen
 * @global function \do_action
 */

/**
 * Canvas Manager
 * Gère les paramètres du canvas et les applique aux générations PDF/Image
 *
 * @package PDF_Builder
 * @since 1.1.0
 */
class Canvas_Manager
{
    /** @var Canvas_Manager Instance unique */
    private static $instance = null;
/** @var array Paramètres canvas en cache */
    private $settings = [];

    /**
     * Récupère l'instance unique
     *
     * @return Canvas_Manager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Alias pour getInstance() - compatibilité
     *
     * @return Canvas_Manager
     */
    public static function get_instance()
    {
        return self::getInstance();
    }

    /**
     * Alias pour enqueueCanvasSettingsScript() - compatibilité
     */
    public function enqueue_canvas_settings_script()
    {
        return $this->enqueueCanvasSettingsScript();
    }

    /**
     * Constructeur
     */
    private function __construct()
    {
        $this->loadSettings();
        $this->registerHooks();
    }

    /**
     * Charge les paramètres du canvas depuis WordPress
     */
    private function loadSettings()
    {
        // Charger depuis l'array unifié de settings pour cohérence avec les modales
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Récupérer les valeurs par défaut
        $defaults = $this->getDefaultSettings();

        // Vérifier si l'utilisateur est premium (temporairement désactivé)
        $is_premium = false; // TODO: Implémenter la vérification de licence premium

        $this->settings = [
            'default_canvas_format' => $settings['pdf_builder_canvas_format'] ?? $defaults['default_canvas_format'],
            'default_canvas_orientation' => $settings['pdf_builder_canvas_default_orientation'] ?? $defaults['default_orientation'],
            'default_canvas_unit' => $settings['pdf_builder_canvas_unit'] ?? $defaults['default_canvas_unit'],
            'default_canvas_dpi' => intval($settings['pdf_builder_canvas_dpi'] ?? $defaults['default_canvas_dpi']),
            'default_canvas_width' => intval($settings['pdf_builder_canvas_width'] ?? $defaults['default_canvas_width']),
            'default_canvas_height' => intval($settings['pdf_builder_canvas_height'] ?? $defaults['default_canvas_height']),
            'canvas_background_color' => $settings['pdf_builder_canvas_bg_color'] ?? $defaults['canvas_background_color'],
            'canvas_show_transparency' => ($settings['pdf_builder_canvas_show_transparency'] ?? ($defaults['canvas_show_transparency'] ? '1' : '0')) == '1',
            'container_background_color' => $settings['pdf_builder_canvas_container_bg_color'] ?? $defaults['container_background_color'],
            'container_show_transparency' => ($settings['pdf_builder_canvas_container_show_transparency'] ?? ($defaults['container_show_transparency'] ? '1' : '0')) == '1',
            'border_color' => $settings['pdf_builder_canvas_border_color'] ?? $defaults['border_color'],
            'border_width' => intval($settings['pdf_builder_canvas_border_width'] ?? $defaults['border_width']),
            'shadow_enabled' => ($settings['pdf_builder_canvas_shadow_enabled'] ?? ($defaults['shadow_enabled'] ? '1' : '0')) == '1',
            'margin_top' => intval($settings['pdf_builder_canvas_margin_top'] ?? $defaults['margin_top']),
            'margin_right' => intval($settings['pdf_builder_canvas_margin_right'] ?? $defaults['margin_right']),
            'margin_bottom' => intval($settings['pdf_builder_canvas_margin_bottom'] ?? $defaults['margin_bottom']),
            'margin_left' => intval($settings['pdf_builder_canvas_margin_left'] ?? $defaults['margin_left']),
            'show_margins' => ($settings['pdf_builder_canvas_show_margins'] ?? ($defaults['show_margins'] ? '1' : '0')) == '1',
            'show_grid' => (\PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation') ? 
                (($settings['pdf_builder_canvas_grid_enabled'] ?? ($defaults['show_grid'] ? '1' : '0')) == '1') : false),
            'grid_size' => intval($settings['pdf_builder_canvas_grid_size'] ?? $defaults['grid_size']),
            'grid_color' => $settings['pdf_builder_canvas_grid_color'] ?? $defaults['grid_color'],
            'snap_to_grid' => (\PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation') ? 
                (($settings['pdf_builder_canvas_snap_to_grid'] ?? ($defaults['snap_to_grid'] ? '1' : '0')) == '1') : false),
            'snap_to_elements' => ($settings['pdf_builder_canvas_snap_to_elements'] ?? ($defaults['snap_to_elements'] ? '1' : '0')) == '1',
            'snap_tolerance' => intval($settings['pdf_builder_canvas_snap_tolerance'] ?? $defaults['snap_tolerance']),
            'show_guides' => (\PDF_Builder\Managers\PDF_Builder_Feature_Manager::canUseFeature('grid_navigation') ? 
                (($settings['pdf_builder_canvas_guides_enabled'] ?? ($defaults['show_guides'] ? '1' : '0')) == '1') : false),
            'default_zoom' => intval($settings['pdf_builder_canvas_zoom_default'] ?? $defaults['default_zoom']),
            'zoom_step' => intval($settings['pdf_builder_canvas_zoom_step'] ?? $defaults['zoom_step']),
            'min_zoom' => intval($settings['pdf_builder_canvas_zoom_min'] ?? $defaults['min_zoom']),
            'max_zoom' => intval($settings['pdf_builder_canvas_zoom_max'] ?? $defaults['max_zoom']),
            'zoom_with_wheel' => ($settings['pdf_builder_canvas_zoom_with_wheel'] ?? ($defaults['zoom_with_wheel'] ? '1' : '0')) == '1',
            'pan_with_mouse' => ($settings['pdf_builder_canvas_pan_enabled'] ?? ($defaults['pan_with_mouse'] ? '1' : '0')) == '1',
            'show_resize_handles' => ($settings['pdf_builder_canvas_show_resize_handles'] ?? ($defaults['show_resize_handles'] ? '1' : '0')) == '1',
            'handle_size' => intval($settings['pdf_builder_canvas_handle_size'] ?? $defaults['handle_size']),
            'handle_color' => $settings['pdf_builder_canvas_handle_color'] ?? $defaults['handle_color'],
            'enable_rotation' => ($settings['pdf_builder_canvas_rotate_enabled'] ?? ($defaults['enable_rotation'] ? '1' : '0')) == '1',
            'rotation_step' => intval($settings['pdf_builder_canvas_rotation_step'] ?? $defaults['rotation_step']),
            'multi_select' => ($settings['pdf_builder_canvas_multi_select'] ?? ($defaults['multi_select'] ? '1' : '0')) == '1',
            'copy_paste_enabled' => ($settings['pdf_builder_canvas_copy_paste_enabled'] ?? ($defaults['copy_paste_enabled'] ? '1' : '0')) == '1',
            'export_quality' => $settings['pdf_builder_canvas_export_quality'] ?? $defaults['export_quality'],
            'export_format' => $settings['pdf_builder_canvas_export_format'] ?? $defaults['export_format'],
            'compress_images' => ($settings['pdf_builder_canvas_compress_images'] ?? ($defaults['compress_images'] ? '1' : '0')) == '1',
            'image_quality' => intval($settings['pdf_builder_canvas_image_quality'] ?? $defaults['image_quality']),
            'max_image_size' => intval($settings['pdf_builder_canvas_max_image_size'] ?? $defaults['max_image_size']),
            'include_metadata' => ($settings['pdf_builder_canvas_include_metadata'] ?? ($defaults['include_metadata'] ? '1' : '0')) == '1',
            'pdf_author' => $settings['pdf_builder_canvas_pdf_author'] ?? $defaults['pdf_author'],
            'pdf_subject' => $settings['pdf_builder_canvas_pdf_subject'] ?? $defaults['pdf_subject'],
            'auto_crop' => ($settings['pdf_builder_canvas_auto_crop'] ?? ($defaults['auto_crop'] ? '1' : '0')) == '1',
            'embed_fonts' => ($settings['pdf_builder_canvas_embed_fonts'] ?? ($defaults['embed_fonts'] ? '1' : '0')) == '1',
            'optimize_for_web' => ($settings['pdf_builder_canvas_optimize_for_web'] ?? ($defaults['optimize_for_web'] ? '1' : '0')) == '1',
            'enable_hardware_acceleration' => ($settings['pdf_builder_canvas_enable_hardware_acceleration'] ?? ($defaults['enable_hardware_acceleration'] ? '1' : '0')) == '1',
            'limit_fps' => ($settings['pdf_builder_canvas_limit_fps'] ?? ($defaults['limit_fps'] ? '1' : '0')) == '1',
            'max_fps' => intval($settings['pdf_builder_canvas_fps_target'] ?? $defaults['max_fps']),
            'auto_save_enabled' => ($settings['pdf_builder_canvas_auto_save'] ?? ($defaults['auto_save_enabled'] ? '1' : '0')) == '1',
            'auto_save_interval' => intval($settings['pdf_builder_canvas_auto_save_interval'] ?? $defaults['auto_save_interval']),
            'auto_save_versions' => intval($settings['pdf_builder_canvas_auto_save_versions'] ?? $defaults['auto_save_versions']),
            'undo_levels' => intval($settings['pdf_builder_canvas_undo_levels'] ?? $defaults['undo_levels']),
            'redo_levels' => intval($settings['pdf_builder_canvas_redo_levels'] ?? $defaults['redo_levels']),
            'enable_keyboard_shortcuts' => ($settings['pdf_builder_canvas_keyboard_shortcuts'] ?? ($defaults['enable_keyboard_shortcuts'] ? '1' : '0')) == '1',
            'canvas_selection_mode' => $settings['pdf_builder_canvas_selection_mode'] ?? $defaults['canvas_selection_mode'],
            'debug_mode' => ($settings['pdf_builder_canvas_debug_mode'] ?? ($defaults['debug_mode'] ? '1' : '0')) == '1',
            'show_fps' => ($settings['pdf_builder_canvas_show_fps'] ?? ($defaults['show_fps'] ? '1' : '0')) == '1'
        ];
    }

    /**
     * Récupère les paramètres par défaut
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return [
            'default_canvas_format' => 'A4',
            'default_canvas_width' => 794,
            'default_canvas_height' => 1123,
            'default_canvas_dpi' => 96,
            'default_canvas_unit' => 'px',
            'default_orientation' => 'portrait',
            'canvas_background_color' => '#ffffff',
            'canvas_show_transparency' => false,
            'container_background_color' => '#f8f9fa',
            'container_show_transparency' => false,
            'border_color' => '#cccccc',
            'border_width' => 1,
            'shadow_enabled' => false,
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
            'auto_save_interval' => 5,
            'auto_save_versions' => 10,
            'undo_levels' => 50,
            'redo_levels' => 50,
            'enable_keyboard_shortcuts' => true,
            'canvas_selection_mode' => 'click',
            'debug_mode' => false,
            'show_fps' => false
        ];
    }

    /**
     * Récupère les paramètres actuels du canvas
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Enregistre les hooks WordPress
     */
    private function registerHooks()
    {
        // Filtre pour appliquer les paramètres canvas à React
        add_filter('pdf_builder_react_settings', [$this, 'apply_canvas_settings_to_react'], 10, 1);
// Action pour initialiser les paramètres canvas côté client
        \add_action('admin_enqueue_scripts', [$this, 'enqueueCanvasSettingsScript'], 15);
    }

    /**
     * Applique les paramètres canvas aux paramètres React
     *
     * @param array $settings Paramètres React
     * @return array Paramètres modifiés
     */
    public function applyCanvasSettingsToReact($settings)
    {
        if (!is_array($settings)) {
            $settings = [];
        }

        $settings['canvas'] = $this->settings;
        return $settings;
    }

    /**
     * Enregistre le script de paramètres canvas
     */
    public function enqueueCanvasSettingsScript()
    {
        if (!is_admin()) {
            return;
        }

        $current_screen = get_current_screen();
        if (!$current_screen || $current_screen->base !== 'pdf-builder-pro_page_pdf-builder-settings') {
            return;
        }
    }

    /**
     * Génère le script d'initialisation des paramètres canvas
     *
     * @return string
     */
    private function getCanvasSettingsScript()
    {
        $settings = wp_json_encode($this->settings);
        return <<<JS
(function() {
    // Fusionner avec les settings existants au lieu d'écraser
    if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
        window.pdfBuilderCanvasSettings = {};
    }
    Object.assign(window.pdfBuilderCanvasSettings, {$settings});
    if (typeof window.pdfBuilderSettings !== 'undefined') {
        window.pdfBuilderSettings.canvas = window.pdfBuilderCanvasSettings;
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
    public function getSetting($key, $default = null)
    {
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
    public function getAllSettings()
    {
        return $this->settings;
    }

    /**
     * Récupère les dimensions du canvas
     *
     * @return array
     */
    public function getCanvasDimensions()
    {
        return [
            'width' => $this->getSetting('default_canvas_width', 794),
            'height' => $this->getSetting('default_canvas_height', 1123),
            'unit' => $this->getSetting('default_canvas_unit', 'px'),
            'orientation' => $this->getSetting('default_canvas_orientation', 'portrait'),
        ];
    }

    /**
     * Récupère les marges du canvas
     *
     * @return array
     */
    public function getCanvasMargins()
    {
        return [
            'top' => $this->getSetting('margin_top', 28),
            'right' => $this->getSetting('margin_right', 28),
            'bottom' => $this->getSetting('margin_bottom', 10),
            'left' => $this->getSetting('margin_left', 10),
        ];
    }

    /**
     * Récupère les paramètres de grille
     *
     * @return array
     */
    public function getGridSettings()
    {
        return [
            'show' => $this->getSetting('show_grid', false),
            'size' => $this->getSetting('grid_size', 10),
            'color' => $this->getSetting('grid_color', '#e0e0e0'),
            'snap_enabled' => $this->getSetting('snap_to_grid', false),
            'snap_tolerance' => $this->getSetting('snap_tolerance', 5),
        ];
    }

    /**
     * Récupère les paramètres de zoom
     *
     * @return array
     */
    public function getZoomSettings()
    {
        return [
            'default' => $this->getSetting('default_zoom', 100),
            'step' => $this->getSetting('zoom_step', 25),
            'min' => $this->getSetting('min_zoom', 10),
            'max' => $this->getSetting('max_zoom', 500),
            'wheel_enabled' => $this->getSetting('zoom_with_wheel', false),
        ];
    }

    /**
     * Récupère les paramètres de sélection
     *
     * @return array
     */
    public function getSelectionSettings()
    {
        return [
            'multi_select' => $this->getSetting('multi_select', false),
            'copy_paste' => $this->getSetting('copy_paste_enabled', false),
            'rotation' => $this->getSetting('enable_rotation', false),
            'rotation_step' => $this->getSetting('rotation_step', 15),
            'show_handles' => $this->getSetting('show_resize_handles', false),
            'handle_size' => $this->getSetting('handle_size', 8),
        ];
    }

    /**
     * Récupère les paramètres d'export
     *
     * @return array
     */
    public function getExportSettings()
    {
        return [
            'quality' => $this->getSetting('export_quality', 'print'),
            'format' => $this->getSetting('export_format', 'pdf'),
            'compress_images' => $this->getSetting('compress_images', true),
            'image_quality' => $this->getSetting('image_quality', 85),
            'max_image_size' => $this->getSetting('max_image_size', 2048),
            'include_metadata' => $this->getSetting('include_metadata', true),
        ];
    }

    /**
     * Récupère les paramètres d'historique
     *
     * @return array
     */
    public function getHistorySettings()
    {
        return [
            'undo_levels' => $this->getSetting('undo_levels', 50),
            'redo_levels' => $this->getSetting('redo_levels', 50),
            'auto_save' => $this->getSetting('auto_save_enabled', true),
            'auto_save_interval' => $this->getSetting('auto_save_interval', 5),
            'auto_save_versions' => $this->getSetting('auto_save_versions', 10),
        ];
    }

    /**
     * Vérifie si une fonctionnalité est activée
     *
     * @param string $feature Nom de la fonctionnalité
     * @return bool
     */
    public function isFeatureEnabled($feature)
    {
        return (bool) $this->getSetting($feature, false);
    }

    /**
     * Réinitialise les paramètres aux valeurs par défaut
     */
    public function resetToDefaults()
    {
        $this->settings = $this->getDefaultSettings();
        pdf_builder_update_option('pdf_builder_canvas_settings', $this->settings);
    }

    /**
     * Sauvegarde les paramètres
     *
     * @param array $settings Paramètres à sauvegarder
     * @return bool
     */
    public function saveSettings($settings)
    {
        // Valider les paramètres
        $validated = $this->validateSettings($settings);
        
        // Mapping des paramètres vers les options séparées
        $option_mappings = [
            'default_canvas_width' => 'pdf_builder_canvas_width',
            'default_canvas_height' => 'pdf_builder_canvas_height',
            'default_canvas_unit' => 'pdf_builder_canvas_unit',
            'default_orientation' => 'pdf_builder_canvas_orientation',
            'canvas_background_color' => 'pdf_builder_canvas_bg_color',
            'canvas_show_transparency' => 'pdf_builder_canvas_show_transparency',
            'container_background_color' => 'pdf_builder_canvas_container_bg_color',
            'container_show_transparency' => 'pdf_builder_canvas_container_show_transparency',
            'border_color' => 'pdf_builder_canvas_border_color',
            'border_width' => 'pdf_builder_canvas_border_width',
            'shadow_enabled' => 'pdf_builder_canvas_shadow_enabled',
            'margin_top' => 'pdf_builder_canvas_margin_top',
            'margin_right' => 'pdf_builder_canvas_margin_right',
            'margin_bottom' => 'pdf_builder_canvas_margin_bottom',
            'margin_left' => 'pdf_builder_canvas_margin_left',
            'show_margins' => 'pdf_builder_canvas_show_margins',
            'show_grid' => 'pdf_builder_canvas_grid_enabled',
            'grid_size' => 'pdf_builder_canvas_grid_size',
            'grid_color' => 'pdf_builder_canvas_grid_color',
            'snap_to_grid' => 'pdf_builder_canvas_snap_to_grid',
            'snap_to_elements' => 'pdf_builder_canvas_snap_to_elements',
            'snap_tolerance' => 'pdf_builder_canvas_snap_tolerance',
            'show_guides' => 'pdf_builder_canvas_guides_enabled',
            'default_zoom' => 'pdf_builder_canvas_zoom_default',
            'zoom_step' => 'pdf_builder_canvas_zoom_step',
            'min_zoom' => 'pdf_builder_canvas_zoom_min',
            'max_zoom' => 'pdf_builder_canvas_zoom_max',
            'zoom_with_wheel' => 'pdf_builder_canvas_zoom_with_wheel',
            'pan_with_mouse' => 'pdf_builder_canvas_pan_enabled',
            'show_resize_handles' => 'pdf_builder_canvas_show_resize_handles',
            'handle_size' => 'pdf_builder_canvas_handle_size',
            'handle_color' => 'pdf_builder_canvas_handle_color',
            'enable_rotation' => 'pdf_builder_canvas_rotate_enabled',
            'rotation_step' => 'pdf_builder_canvas_rotation_step',
            'multi_select' => 'pdf_builder_canvas_multi_select',
            'copy_paste_enabled' => 'pdf_builder_canvas_copy_paste_enabled',
            'export_quality' => 'pdf_builder_canvas_export_quality',
            'export_format' => 'pdf_builder_canvas_export_format',
            'compress_images' => 'pdf_builder_canvas_compress_images',
            'image_quality' => 'pdf_builder_canvas_image_quality',
            'max_image_size' => 'pdf_builder_canvas_max_image_size',
            'include_metadata' => 'pdf_builder_canvas_include_metadata',
            'pdf_author' => 'pdf_builder_canvas_pdf_author',
            'pdf_subject' => 'pdf_builder_canvas_pdf_subject',
            'auto_crop' => 'pdf_builder_canvas_auto_crop',
            'embed_fonts' => 'pdf_builder_canvas_embed_fonts',
            'optimize_for_web' => 'pdf_builder_canvas_optimize_for_web',
            'enable_hardware_acceleration' => 'pdf_builder_canvas_enable_hardware_acceleration',
            'limit_fps' => 'pdf_builder_canvas_limit_fps',
            'max_fps' => 'pdf_builder_canvas_fps_target',
            'auto_save_enabled' => 'pdf_builder_canvas_auto_save',
            'auto_save_interval' => 'pdf_builder_canvas_auto_save_interval',
            'auto_save_versions' => 'pdf_builder_canvas_auto_save_versions',
            'undo_levels' => 'pdf_builder_canvas_undo_levels',
            'redo_levels' => 'pdf_builder_canvas_redo_levels',
            'enable_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts',
            'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
            'debug_mode' => 'pdf_builder_canvas_debug_mode',
            'show_fps' => 'pdf_builder_canvas_show_fps',
            // Paramètres de performance
            'fps_target' => 'pdf_builder_canvas_fps_target',
            'memory_limit_js' => 'pdf_builder_canvas_memory_limit_js',
            'memory_limit_php' => 'pdf_builder_canvas_memory_limit_php',
            'response_timeout' => 'pdf_builder_canvas_response_timeout',
            'lazy_loading_editor' => 'pdf_builder_canvas_lazy_loading_editor',
            'preload_critical' => 'pdf_builder_canvas_preload_critical',
            'lazy_loading_plugin' => 'pdf_builder_canvas_lazy_loading_plugin'
        ];
        
        // Sauvegarder dans les options séparées
        foreach ($option_mappings as $setting_key => $option_key) {
            if (isset($validated[$setting_key])) {
                $value = $validated[$setting_key];
                // Convertir les booléens en string pour la cohérence
                if (is_bool($value)) {
                    $value = $value ? '1' : '0';
                }
                update_option($option_key, $value);
            }
        }
        
        // Mettre à jour les paramètres en mémoire
        $this->settings = array_merge($this->settings, $validated);
        
        // Mettre à jour l'option globale pour la synchronisation avec l'éditeur React
        pdf_builder_update_option('pdf_builder_canvas_settings', $this->settings);

        // La sauvegarde est considérée réussie tant qu'aucune exception n'est levée
        do_action('pdfBuilderCanvasSettingsUpdated', $this->settings);
        return true;
    }

    /**
     * Valide les paramètres
     *
     * @param array $settings Paramètres à valider
     * @return array Paramètres validés
     */
    private function validateSettings($settings)
    {
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
        if (isset($settings['border_color'])) {
            $validated['border_color'] = sanitize_text_field($settings['border_color']);
        }
        if (isset($settings['grid_color'])) {
            $validated['grid_color'] = sanitize_text_field($settings['grid_color']);
        }
        if (isset($settings['handle_color'])) {
            $validated['handle_color'] = sanitize_text_field($settings['handle_color']);
        }

        // Marges et espacement
        foreach (['margin_top', 'margin_right', 'margin_bottom', 'margin_left', 'grid_size', 'snap_tolerance', 'rotation_step', 'handle_size', 'max_fps', 'border_width'] as $key) {
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
        foreach (['shadow_enabled', 'canvas_show_transparency', 'container_show_transparency', 'show_margins', 'show_grid', 'snap_to_grid', 'snap_to_elements', 'show_guides', 'zoom_with_wheel', 'pan_with_mouse', 'show_resize_handles', 'enable_rotation', 'multi_select', 'copy_paste_enabled', 'compress_images', 'include_metadata', 'auto_crop', 'embed_fonts', 'optimize_for_web', 'enable_hardware_acceleration', 'limit_fps', 'auto_save_enabled', 'enable_keyboard_shortcuts', 'debug_mode', 'show_fps'] as $key) {
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
        if (isset($settings['canvas_selection_mode'])) {
            $allowed_modes = ['click', 'lasso', 'rectangle'];
            $mode = sanitize_text_field($settings['canvas_selection_mode']);
            if (in_array($mode, $allowed_modes)) {
                $validated['canvas_selection_mode'] = $mode;
            }
        }

        return $validated;
    }
}





