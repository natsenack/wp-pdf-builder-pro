<?php
/**
 * PDF Builder Canvas Mappings
 *
 * Centralise tous les mappings entre les champs de formulaire et les options WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Canvas_Mappings
{

    // ==========================================
    // MAPPINGS GÉNÉRAUX
    // ==========================================

    private static $general_mappings = [
        // Dimensions - géré séparément dans la logique switch
        // 'canvas_dpi' => 'pdf_builder_canvas_dpi', // REMOVED: géré dans case 'dimensions'

        // Apparence
        'canvas_bg_color' => 'pdf_builder_canvas_bg_color',
        'canvas_container_bg_color' => 'pdf_builder_canvas_container_bg_color',
        'canvas_border_color' => 'pdf_builder_canvas_border_color',
        'canvas_border_width' => 'pdf_builder_canvas_border_width',
        'canvas_shadow_enabled' => 'pdf_builder_canvas_shadow_enabled',

        // Grille
        'canvas_guides_enabled' => 'pdf_builder_canvas_guides_enabled',
        'canvas_grid_enabled' => 'pdf_builder_canvas_grid_enabled',
        'canvas_grid_size' => 'pdf_builder_canvas_grid_size',
        'canvas_snap_to_grid' => 'pdf_builder_canvas_snap_to_grid',

        // Zoom
        'canvas_zoom_min' => 'pdf_builder_canvas_zoom_min',
        'canvas_zoom_max' => 'pdf_builder_canvas_zoom_max',
        'canvas_zoom_default' => 'pdf_builder_canvas_zoom_default',
        'canvas_zoom_step' => 'pdf_builder_canvas_zoom_step',

        // Interactions
        'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
        'canvas_multi_select' => 'pdf_builder_canvas_multi_select',
        'canvas_drag_enabled' => 'pdf_builder_canvas_drag_enabled',
        'canvas_resize_enabled' => 'pdf_builder_canvas_resize_enabled',
        'canvas_rotate_enabled' => 'pdf_builder_canvas_rotate_enabled',
        'canvas_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts',

        // Performance
        'canvas_lazy_loading_editor' => 'pdf_builder_canvas_lazy_loading_editor',
        'canvas_preload_critical' => 'pdf_builder_canvas_preload_critical',
        'canvas_lazy_loading_plugin' => 'pdf_builder_canvas_lazy_loading_plugin',
        'canvas_fps_target' => 'pdf_builder_canvas_fps_target',
        'canvas_memory_limit_js' => 'pdf_builder_canvas_memory_limit_js',
        'canvas_memory_limit_php' => 'pdf_builder_canvas_memory_limit_php',
        'canvas_response_timeout' => 'pdf_builder_canvas_response_timeout',

        // Export
        'canvas_export_format' => 'pdf_builder_canvas_export_format',
        'canvas_export_quality' => 'pdf_builder_canvas_export_quality',
        'canvas_export_transparent' => 'pdf_builder_canvas_export_transparent',

        // Debug
        'canvas_debug_enabled' => 'pdf_builder_canvas_debug_enabled',
        'canvas_performance_monitoring' => 'pdf_builder_canvas_performance_monitoring',
        'canvas_error_reporting' => 'pdf_builder_canvas_error_reporting'
    ];

    // ==========================================
    // MAPPINGS PAR CATÉGORIE
    // ==========================================

    private static $category_mappings = [
        'dimensions' => [
            'canvas_format' => 'pdf_builder_canvas_format',
            'canvas_orientation' => 'pdf_builder_canvas_orientation',
            'canvas_dpi' => 'pdf_builder_canvas_dpi',
            'canvas_width' => 'pdf_builder_canvas_width',
            'canvas_height' => 'pdf_builder_canvas_height',
            'canvas_unit' => 'pdf_builder_canvas_unit'
        ],

        'apparence' => [
            'canvas_bg_color' => 'pdf_builder_canvas_bg_color',
            'canvas_container_bg_color' => 'pdf_builder_canvas_container_bg_color',
            'canvas_border_color' => 'pdf_builder_canvas_border_color',
            'canvas_border_width' => 'pdf_builder_canvas_border_width',
            'canvas_shadow_enabled' => 'pdf_builder_canvas_shadow_enabled'
        ],

        'grille' => [
            'canvas_guides_enabled' => 'pdf_builder_canvas_guides_enabled',
            'canvas_grid_enabled' => 'pdf_builder_canvas_grid_enabled',
            'canvas_grid_size' => 'pdf_builder_canvas_grid_size',
            'canvas_snap_to_grid' => 'pdf_builder_canvas_snap_to_grid',
            'canvas_snap_to_elements' => 'pdf_builder_canvas_snap_to_elements',
            'canvas_show_guides' => 'pdf_builder_canvas_show_guides'
        ],

        'zoom' => [
            'canvas_zoom_min' => 'pdf_builder_canvas_zoom_min',
            'canvas_zoom_max' => 'pdf_builder_canvas_zoom_max',
            'canvas_zoom_default' => 'pdf_builder_canvas_zoom_default',
            'canvas_zoom_step' => 'pdf_builder_canvas_zoom_step'
        ],

        'interactions' => [
            'canvas_selection_mode' => 'pdf_builder_canvas_selection_mode',
            'canvas_multi_select' => 'pdf_builder_canvas_multi_select',
            'canvas_drag_enabled' => 'pdf_builder_canvas_drag_enabled',
            'canvas_resize_enabled' => 'pdf_builder_canvas_resize_enabled',
            'canvas_rotate_enabled' => 'pdf_builder_canvas_rotate_enabled',
            'canvas_keyboard_shortcuts' => 'pdf_builder_canvas_keyboard_shortcuts',
            'canvas_show_resize_handles' => 'pdf_builder_canvas_show_resize_handles',
            'canvas_enable_rotation' => 'pdf_builder_canvas_enable_rotation'
        ],

        'performance' => [
            'canvas_lazy_loading_editor' => 'pdf_builder_canvas_lazy_loading_editor',
            'canvas_preload_critical' => 'pdf_builder_canvas_preload_critical',
            'canvas_lazy_loading_plugin' => 'pdf_builder_canvas_lazy_loading_plugin',
            'canvas_fps_target' => 'pdf_builder_canvas_fps_target',
            'canvas_memory_limit_js' => 'pdf_builder_canvas_memory_limit_js',
            'canvas_memory_limit_php' => 'pdf_builder_canvas_memory_limit_php',
            'canvas_response_timeout' => 'pdf_builder_canvas_response_timeout'
        ],

        'export' => [
            'canvas_export_format' => 'pdf_builder_canvas_export_format',
            'canvas_export_quality' => 'pdf_builder_canvas_export_quality',
            'canvas_export_transparent' => 'pdf_builder_canvas_export_transparent',
            'canvas_compress_images' => 'pdf_builder_canvas_compress_images',
            'canvas_include_metadata' => 'pdf_builder_canvas_include_metadata',
            'canvas_auto_crop' => 'pdf_builder_canvas_auto_crop',
            'canvas_embed_fonts' => 'pdf_builder_canvas_embed_fonts',
            'canvas_optimize_for_web' => 'pdf_builder_canvas_optimize_for_web'
        ]
    ];

    // ==========================================
    // CHAMPS NUMÉRIQUES PAR CATÉGORIE
    // ==========================================

    private static $numeric_fields = [
        'dimensions' => ['canvas_border_width', 'canvas_grid_size', 'canvas_dpi', 'canvas_width', 'canvas_height'],
        'apparence' => ['canvas_border_width'],
        'grille' => ['canvas_grid_size', 'canvas_snap_tolerance', 'canvas_handle_size', 'canvas_rotation_step'],
        'zoom' => ['canvas_zoom_min', 'canvas_zoom_max', 'canvas_zoom_default', 'canvas_zoom_step'],
        'interactions' => ['canvas_rotation_step'],
        'performance' => ['canvas_fps_target', 'canvas_memory_limit_js', 'canvas_memory_limit_php', 'canvas_response_timeout'],
        'export' => ['canvas_export_quality', 'canvas_image_quality', 'canvas_max_image_size', 'canvas_auto_save_interval', 'canvas_auto_save_versions', 'canvas_max_fps'],
    ];

    // ==========================================
    // CHAMPS CHECKBOX PAR CATÉGORIE
    // ==========================================

    private static $checkbox_fields = [
        'apparence' => ['canvas_shadow_enabled'],
        'grille' => ['canvas_guides_enabled', 'canvas_grid_enabled', 'canvas_snap_to_grid', 'canvas_snap_to_elements', 'canvas_show_guides'],
        'interactions' => ['canvas_multi_select', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_keyboard_shortcuts', 'canvas_show_resize_handles', 'canvas_enable_rotation'],
        'performance' => ['canvas_lazy_loading_editor', 'canvas_preload_critical', 'canvas_lazy_loading_plugin'],
        'export' => ['canvas_export_transparent', 'canvas_compress_images', 'canvas_include_metadata', 'canvas_auto_crop', 'canvas_embed_fonts', 'canvas_optimize_for_web'],
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir tous les mappings généraux
     */
    public static function get_general_mappings()
    {
        return self::$general_mappings;
    }

    /**
     * Obtenir les mappings pour une catégorie spécifique
     */
    public static function get_category_mappings($category)
    {
        return self::$category_mappings[$category] ?? [];
    }

    /**
     * Obtenir tous les mappings par catégorie
     */
    public static function get_all_category_mappings()
    {
        return self::$category_mappings;
    }

    /**
     * Obtenir les champs numériques pour une catégorie
     */
    public static function get_numeric_fields($category)
    {
        return self::$numeric_fields[$category] ?? [];
    }

    /**
     * Obtenir les champs checkbox pour une catégorie
     */
    public static function get_checkbox_fields($category)
    {
        return self::$checkbox_fields[$category] ?? [];
    }

    /**
     * Vérifier si un champ est numérique dans une catégorie
     */
    public static function is_numeric_field($category, $field)
    {
        $numeric_fields = self::get_numeric_fields($category);
        return in_array($field, $numeric_fields);
    }

    /**
     * Vérifier si un champ est une checkbox dans une catégorie
     */
    public static function is_checkbox_field($category, $field)
    {
        $checkbox_fields = self::get_checkbox_fields($category);
        return in_array($field, $checkbox_fields);
    }

    /**
     * Obtenir l'option WordPress pour un champ de formulaire
     */
    public static function get_option_name($field_name)
    {
        return self::$general_mappings[$field_name] ?? null;
    }

    /**
     * Obtenir l'option WordPress pour un champ dans une catégorie spécifique
     */
    public static function get_option_name_by_category($category, $field_name)
    {
        $category_mappings = self::get_category_mappings($category);
        return $category_mappings[$field_name] ?? null;
    }
}
