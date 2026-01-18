<?php
/**
 * PDF Builder Template Element Mappings
 *
 * Centralise tous les mappings pour les éléments de template
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Template_Mappings {

    // ==========================================
    // MAPPINGS DES ÉLÉMENTS DE BASE
    // ==========================================

    private static $element_mappings = [
        // Texte
        'text_content' => 'pdf_builder_text_content',
        'text_font_family' => 'pdf_builder_text_font_family',
        'text_font_size' => 'pdf_builder_text_font_size',
        'text_font_weight' => 'pdf_builder_text_font_weight',
        'text_font_style' => 'pdf_builder_text_font_style',
        'text_color' => 'pdf_builder_text_color',
        'text_align' => 'pdf_builder_text_align',
        'text_line_height' => 'pdf_builder_text_line_height',
        'text_letter_spacing' => 'pdf_builder_text_letter_spacing',
        'text_decoration' => 'pdf_builder_text_decoration',
        'text_transform' => 'pdf_builder_text_transform',
        'text_shadow' => 'pdf_builder_text_shadow',

        // Image
        'image_src' => 'pdf_builder_image_src',
        'image_alt' => 'pdf_builder_image_alt',
        'image_width' => 'pdf_builder_image_width',
        'image_height' => 'pdf_builder_image_height',
        'image_fit' => 'pdf_builder_image_fit',
        'image_position' => 'pdf_builder_image_position',
        'image_opacity' => 'pdf_builder_image_opacity',
        'image_border_radius' => 'pdf_builder_image_border_radius',
        'image_border_width' => 'pdf_builder_image_border_width',
        'image_border_color' => 'pdf_builder_image_border_color',
        'image_shadow' => 'pdf_builder_image_shadow',

        // Forme
        'shape_type' => 'pdf_builder_shape_type',
        'shape_fill_color' => 'pdf_builder_shape_fill_color',
        'shape_stroke_color' => 'pdf_builder_shape_stroke_color',
        'shape_stroke_width' => 'pdf_builder_shape_stroke_width',
        'shape_opacity' => 'pdf_builder_shape_opacity',
        'shape_border_radius' => 'pdf_builder_shape_border_radius',

        // Ligne
        'line_start_x' => 'pdf_builder_line_start_x',
        'line_start_y' => 'pdf_builder_line_start_y',
        'line_end_x' => 'pdf_builder_line_end_x',
        'line_end_y' => 'pdf_builder_line_end_y',
        'line_color' => 'pdf_builder_line_color',
        'line_width' => 'pdf_builder_line_width',
        'line_style' => 'pdf_builder_line_style',
        'line_opacity' => 'pdf_builder_line_opacity',

        // Rectangle
        'rect_x' => 'pdf_builder_rect_x',
        'rect_y' => 'pdf_builder_rect_y',
        'rect_width' => 'pdf_builder_rect_width',
        'rect_height' => 'pdf_builder_rect_height',
        'rect_fill_color' => 'pdf_builder_rect_fill_color',
        'rect_stroke_color' => 'pdf_builder_rect_stroke_color',
        'rect_stroke_width' => 'pdf_builder_rect_stroke_width',
        'rect_opacity' => 'pdf_builder_rect_opacity',
        'rect_border_radius' => 'pdf_builder_rect_border_radius',

        // Cercle
        'circle_cx' => 'pdf_builder_circle_cx',
        'circle_cy' => 'pdf_builder_circle_cy',
        'circle_r' => 'pdf_builder_circle_r',
        'circle_fill_color' => 'pdf_builder_circle_fill_color',
        'circle_stroke_color' => 'pdf_builder_circle_stroke_color',
        'circle_stroke_width' => 'pdf_builder_circle_stroke_width',
        'circle_opacity' => 'pdf_builder_circle_opacity',

        // Position et dimensions communes
        'element_x' => 'pdf_builder_element_x',
        'element_y' => 'pdf_builder_element_y',
        'element_width' => 'pdf_builder_element_width',
        'element_height' => 'pdf_builder_element_height',
        'element_rotation' => 'pdf_builder_element_rotation',
        'element_scale_x' => 'pdf_builder_element_scale_x',
        'element_scale_y' => 'pdf_builder_element_scale_y',
        'element_skew_x' => 'pdf_builder_element_skew_x',
        'element_skew_y' => 'pdf_builder_element_skew_y',
        'element_opacity' => 'pdf_builder_element_opacity',
        'element_visible' => 'pdf_builder_element_visible',
        'element_locked' => 'pdf_builder_element_locked',
        'element_layer' => 'pdf_builder_element_layer',
        'element_z_index' => 'pdf_builder_element_z_index',

        // Styles communs
        'element_fill_color' => 'pdf_builder_element_fill_color',
        'element_stroke_color' => 'pdf_builder_element_stroke_color',
        'element_stroke_width' => 'pdf_builder_element_stroke_width',
        'element_shadow_color' => 'pdf_builder_element_shadow_color',
        'element_shadow_blur' => 'pdf_builder_element_shadow_blur',
        'element_shadow_offset_x' => 'pdf_builder_element_shadow_offset_x',
        'element_shadow_offset_y' => 'pdf_builder_element_shadow_offset_y',

        // Animations
        'element_animation_type' => 'pdf_builder_element_animation_type',
        'element_animation_duration' => 'pdf_builder_element_animation_duration',
        'element_animation_delay' => 'pdf_builder_element_animation_delay',
        'element_animation_easing' => 'pdf_builder_element_animation_easing',
        'element_animation_loop' => 'pdf_builder_element_animation_loop',

        // Interactions
        'element_clickable' => 'pdf_builder_element_clickable',
        'element_hoverable' => 'pdf_builder_element_hoverable',
        'element_draggable' => 'pdf_builder_element_draggable',
        'element_resizable' => 'pdf_builder_element_resizable',
        'element_rotatable' => 'pdf_builder_element_rotatable',
        'element_selectable' => 'pdf_builder_element_selectable',

        // Données
        'element_data_type' => 'pdf_builder_element_data_type',
        'element_data_source' => 'pdf_builder_element_data_source',
        'element_data_field' => 'pdf_builder_element_data_field',
        'element_data_format' => 'pdf_builder_element_data_format',
        'element_data_default' => 'pdf_builder_element_data_default',

        // Conditions
        'element_condition_type' => 'pdf_builder_element_condition_type',
        'element_condition_field' => 'pdf_builder_element_condition_field',
        'element_condition_value' => 'pdf_builder_element_condition_value',
        'element_condition_operator' => 'pdf_builder_element_condition_operator'
    ];

    // ==========================================
    // MAPPINGS PAR TYPE D'ÉLÉMENT
    // ==========================================

    private static $element_type_mappings = [
        'text' => [
            'content' => 'pdf_builder_text_content',
            'font_family' => 'pdf_builder_text_font_family',
            'font_size' => 'pdf_builder_text_font_size',
            'font_weight' => 'pdf_builder_text_font_weight',
            'font_style' => 'pdf_builder_text_font_style',
            'color' => 'pdf_builder_text_color',
            'align' => 'pdf_builder_text_align',
            'line_height' => 'pdf_builder_text_line_height',
            'letter_spacing' => 'pdf_builder_text_letter_spacing',
            'decoration' => 'pdf_builder_text_decoration',
            'transform' => 'pdf_builder_text_transform',
            'shadow' => 'pdf_builder_text_shadow'
        ],

        'image' => [
            'src' => 'pdf_builder_image_src',
            'alt' => 'pdf_builder_image_alt',
            'width' => 'pdf_builder_image_width',
            'height' => 'pdf_builder_image_height',
            'fit' => 'pdf_builder_image_fit',
            'position' => 'pdf_builder_image_position',
            'opacity' => 'pdf_builder_image_opacity',
            'border_radius' => 'pdf_builder_image_border_radius',
            'border_width' => 'pdf_builder_image_border_width',
            'border_color' => 'pdf_builder_image_border_color',
            'shadow' => 'pdf_builder_image_shadow'
        ],

        'shape' => [
            'type' => 'pdf_builder_shape_type',
            'fill_color' => 'pdf_builder_shape_fill_color',
            'stroke_color' => 'pdf_builder_shape_stroke_color',
            'stroke_width' => 'pdf_builder_shape_stroke_width',
            'opacity' => 'pdf_builder_shape_opacity',
            'border_radius' => 'pdf_builder_shape_border_radius'
        ],

        'line' => [
            'start_x' => 'pdf_builder_line_start_x',
            'start_y' => 'pdf_builder_line_start_y',
            'end_x' => 'pdf_builder_line_end_x',
            'end_y' => 'pdf_builder_line_end_y',
            'color' => 'pdf_builder_line_color',
            'width' => 'pdf_builder_line_width',
            'style' => 'pdf_builder_line_style',
            'opacity' => 'pdf_builder_line_opacity'
        ],

        'rectangle' => [
            'x' => 'pdf_builder_rect_x',
            'y' => 'pdf_builder_rect_y',
            'width' => 'pdf_builder_rect_width',
            'height' => 'pdf_builder_rect_height',
            'fill_color' => 'pdf_builder_rect_fill_color',
            'stroke_color' => 'pdf_builder_rect_stroke_color',
            'stroke_width' => 'pdf_builder_rect_stroke_width',
            'opacity' => 'pdf_builder_rect_opacity',
            'border_radius' => 'pdf_builder_rect_border_radius'
        ],

        'circle' => [
            'cx' => 'pdf_builder_circle_cx',
            'cy' => 'pdf_builder_circle_cy',
            'r' => 'pdf_builder_circle_r',
            'fill_color' => 'pdf_builder_circle_fill_color',
            'stroke_color' => 'pdf_builder_circle_stroke_color',
            'stroke_width' => 'pdf_builder_circle_stroke_width',
            'opacity' => 'pdf_builder_circle_opacity'
        ]
    ];

    // ==========================================
    // PROPRIÉTÉS PAR TYPE D'ÉLÉMENT
    // ==========================================

    private static $element_properties = [
        'text' => [
            'required' => ['content', 'x', 'y', 'width', 'height'],
            'optional' => ['font_family', 'font_size', 'font_weight', 'font_style', 'color', 'align', 'line_height', 'letter_spacing', 'decoration', 'transform', 'shadow', 'rotation', 'opacity', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ],

        'image' => [
            'required' => ['src', 'x', 'y', 'width', 'height'],
            'optional' => ['alt', 'fit', 'position', 'opacity', 'border_radius', 'border_width', 'border_color', 'shadow', 'rotation', 'scale_x', 'scale_y', 'skew_x', 'skew_y', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ],

        'shape' => [
            'required' => ['type', 'x', 'y', 'width', 'height'],
            'optional' => ['fill_color', 'stroke_color', 'stroke_width', 'opacity', 'border_radius', 'rotation', 'scale_x', 'scale_y', 'skew_x', 'skew_y', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ],

        'line' => [
            'required' => ['start_x', 'start_y', 'end_x', 'end_y'],
            'optional' => ['color', 'width', 'style', 'opacity', 'rotation', 'scale_x', 'scale_y', 'skew_x', 'skew_y', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ],

        'rectangle' => [
            'required' => ['x', 'y', 'width', 'height'],
            'optional' => ['fill_color', 'stroke_color', 'stroke_width', 'opacity', 'border_radius', 'rotation', 'scale_x', 'scale_y', 'skew_x', 'skew_y', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ],

        'circle' => [
            'required' => ['cx', 'cy', 'r'],
            'optional' => ['fill_color', 'stroke_color', 'stroke_width', 'opacity', 'rotation', 'scale_x', 'scale_y', 'skew_x', 'skew_y', 'visible', 'locked', 'layer', 'z_index', 'animation_type', 'animation_duration', 'animation_delay', 'animation_easing', 'animation_loop', 'clickable', 'hoverable', 'draggable', 'resizable', 'rotatable', 'selectable', 'data_type', 'data_source', 'data_field', 'data_format', 'data_default', 'condition_type', 'condition_field', 'condition_value', 'condition_operator']
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir tous les mappings d'éléments
     */
    public static function get_element_mappings() {
        return self::$element_mappings;
    }

    /**
     * Obtenir les mappings pour un type d'élément spécifique
     */
    public static function get_element_type_mappings($element_type) {
        return self::$element_type_mappings[$element_type] ?? [];
    }

    /**
     * Obtenir toutes les propriétés par type d'élément
     */
    public static function get_element_properties() {
        return self::$element_properties;
    }

    /**
     * Obtenir les propriétés pour un type d'élément spécifique
     */
    public static function get_element_type_properties($element_type) {
        return self::$element_properties[$element_type] ?? [];
    }

    /**
     * Obtenir les propriétés requises pour un type d'élément
     */
    public static function get_required_properties($element_type) {
        $properties = self::get_element_type_properties($element_type);
        return $properties['required'] ?? [];
    }

    /**
     * Obtenir les propriétés optionnelles pour un type d'élément
     */
    public static function get_optional_properties($element_type) {
        $properties = self::get_element_type_properties($element_type);
        return $properties['optional'] ?? [];
    }

    /**
     * Vérifier si une propriété est requise pour un type d'élément
     */
    public static function is_required_property($element_type, $property) {
        $required = self::get_required_properties($element_type);
        return in_array($property, $required);
    }

    /**
     * Obtenir l'option WordPress pour une propriété d'élément
     */
    public static function get_option_name($property_name) {
        return self::$element_mappings[$property_name] ?? null;
    }

    /**
     * Obtenir l'option WordPress pour une propriété d'un type d'élément spécifique
     */
    public static function get_option_name_by_type($element_type, $property_name) {
        $type_mappings = self::get_element_type_mappings($element_type);
        return $type_mappings[$property_name] ?? null;
    }
}

