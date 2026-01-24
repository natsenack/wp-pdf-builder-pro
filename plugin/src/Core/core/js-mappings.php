<?php
/**
 * PDF Builder JavaScript Mappings
 *
 * Centralise tous les mappings JavaScript utilisés dans le plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_JS_Mappings
{

    // ==========================================
    // CONSTANTES JAVASCRIPT
    // ==========================================

    private static $js_constants = [
        // Événements
        'EVENT_CANVAS_READY' => 'pdf_builder_canvas_ready',
        'EVENT_ELEMENT_SELECTED' => 'pdf_builder_element_selected',
        'EVENT_ELEMENT_DESELECTED' => 'pdf_builder_element_deselected',
        'EVENT_ELEMENT_ADDED' => 'pdf_builder_element_added',
        'EVENT_ELEMENT_REMOVED' => 'pdf_builder_element_removed',
        'EVENT_ELEMENT_UPDATED' => 'pdf_builder_element_updated',
        'EVENT_CANVAS_ZOOM_CHANGED' => 'pdf_builder_canvas_zoom_changed',
        'EVENT_CANVAS_PAN_CHANGED' => 'pdf_builder_canvas_pan_changed',
        'EVENT_TEMPLATE_LOADED' => 'pdf_builder_template_loaded',
        'EVENT_TEMPLATE_SAVED' => 'pdf_builder_template_saved',
        'EVENT_EXPORT_STARTED' => 'pdf_builder_export_started',
        'EVENT_EXPORT_COMPLETED' => 'pdf_builder_export_completed',
        'EVENT_ERROR_OCCURRED' => 'pdf_builder_error_occurred',

        // Classes CSS
        'CSS_CLASS_CANVAS' => 'pdf-builder-canvas',
        'CSS_CLASS_ELEMENT' => 'pdf-builder-element',
        'CSS_CLASS_SELECTED' => 'pdf-builder-selected',
        'CSS_CLASS_DRAGGING' => 'pdf-builder-dragging',
        'CSS_CLASS_RESIZING' => 'pdf-builder-resizing',
        'CSS_CLASS_ROTATING' => 'pdf-builder-rotating',
        'CSS_CLASS_HANDLE' => 'pdf-builder-handle',
        'CSS_CLASS_GUIDE' => 'pdf-builder-guide',
        'CSS_CLASS_GRID' => 'pdf-builder-grid',
        'CSS_CLASS_TOOLBAR' => 'pdf-builder-toolbar',
        'CSS_CLASS_PROPERTIES' => 'pdf-builder-properties',
        'CSS_CLASS_LOADING' => 'pdf-builder-loading',
        'CSS_CLASS_ERROR' => 'pdf-builder-error',
        'CSS_CLASS_SUCCESS' => 'pdf-builder-success',
        'CSS_CLASS_WARNING' => 'pdf-builder-warning',

        // Attributs de données
        'DATA_ATTR_ELEMENT_ID' => 'data-pdf-element-id',
        'DATA_ATTR_ELEMENT_TYPE' => 'data-pdf-element-type',
        'DATA_ATTR_ELEMENT_LAYER' => 'data-pdf-element-layer',
        'DATA_ATTR_ELEMENT_LOCKED' => 'data-pdf-element-locked',
        'DATA_ATTR_ELEMENT_VISIBLE' => 'data-pdf-element-visible',
        'DATA_ATTR_CANVAS_ZOOM' => 'data-pdf-canvas-zoom',
        'DATA_ATTR_CANVAS_PAN_X' => 'data-pdf-canvas-pan-x',
        'DATA_ATTR_CANVAS_PAN_Y' => 'data-pdf-canvas-pan-y',
        'DATA_ATTR_TEMPLATE_ID' => 'data-pdf-template-id',
        'DATA_ATTR_TOOL_ACTIVE' => 'data-pdf-tool-active',

        // Sélecteurs jQuery
        'SELECTOR_CANVAS' => '.pdf-builder-canvas',
        'SELECTOR_ELEMENT' => '.pdf-builder-element',
        'SELECTOR_SELECTED' => '.pdf-builder-selected',
        'SELECTOR_HANDLE' => '.pdf-builder-handle',
        'SELECTOR_TOOLBAR' => '.pdf-builder-toolbar',
        'SELECTOR_PROPERTIES' => '.pdf-builder-properties',
        'SELECTOR_LOADING' => '.pdf-builder-loading',
        'SELECTOR_ERROR' => '.pdf-builder-error',

        // Actions AJAX
        'AJAX_ACTION_SAVE_TEMPLATE' => 'pdf_builder_save_template',
        'AJAX_ACTION_LOAD_TEMPLATE' => 'pdf_builder_load_template',
        'AJAX_ACTION_DELETE_TEMPLATE' => 'pdf_builder_delete_template',
        'AJAX_ACTION_EXPORT_PDF' => 'pdf_builder_export_pdf',
        'AJAX_ACTION_SAVE_SETTINGS' => 'pdf_builder_save_settings',
        'AJAX_ACTION_LOAD_SETTINGS' => 'pdf_builder_load_settings',
        'AJAX_ACTION_UPLOAD_IMAGE' => 'pdf_builder_upload_image',
        'AJAX_ACTION_GET_FONTS' => 'pdf_builder_get_fonts',

        // Types d'éléments
        'ELEMENT_TYPE_TEXT' => 'text',
        'ELEMENT_TYPE_IMAGE' => 'image',
        'ELEMENT_TYPE_SHAPE' => 'shape',
        'ELEMENT_TYPE_LINE' => 'line',
        'ELEMENT_TYPE_RECTANGLE' => 'rectangle',
        'ELEMENT_TYPE_CIRCLE' => 'circle',
        'ELEMENT_TYPE_GROUP' => 'group',

        // Outils
        'TOOL_SELECT' => 'select',
        'TOOL_TEXT' => 'text',
        'TOOL_IMAGE' => 'image',
        'TOOL_SHAPE' => 'shape',
        'TOOL_LINE' => 'line',
        'TOOL_RECTANGLE' => 'rectangle',
        'TOOL_CIRCLE' => 'circle',
        'TOOL_PAN' => 'pan',
        'TOOL_ZOOM' => 'zoom',

        // Modes de sélection
        'SELECTION_MODE_SINGLE' => 'single',
        'SELECTION_MODE_MULTI' => 'multi',
        'SELECTION_MODE_GROUP' => 'group',

        // Formats d'export
        'EXPORT_FORMAT_PDF' => 'pdf',
        'EXPORT_FORMAT_PNG' => 'png',
        'EXPORT_FORMAT_JPG' => 'jpg',
        'EXPORT_FORMAT_SVG' => 'svg',

        // Unités
        'UNIT_PX' => 'px',
        'UNIT_MM' => 'mm',
        'UNIT_CM' => 'cm',
        'UNIT_IN' => 'in',
        'UNIT_PT' => 'pt',

        // Alignements
        'ALIGN_LEFT' => 'left',
        'ALIGN_CENTER' => 'center',
        'ALIGN_RIGHT' => 'right',
        'ALIGN_JUSTIFY' => 'justify',

        // Positions d'image
        'IMAGE_POSITION_FILL' => 'fill',
        'IMAGE_POSITION_FIT' => 'fit',
        'IMAGE_POSITION_STRETCH' => 'stretch',
        'IMAGE_POSITION_TILE' => 'tile',

        // Styles de ligne
        'LINE_STYLE_SOLID' => 'solid',
        'LINE_STYLE_DASHED' => 'dashed',
        'LINE_STYLE_DOTTED' => 'dotted',

        // Animations
        'ANIMATION_FADE_IN' => 'fadeIn',
        'ANIMATION_FADE_OUT' => 'fadeOut',
        'ANIMATION_SLIDE_IN' => 'slideIn',
        'ANIMATION_SLIDE_OUT' => 'slideOut',
        'ANIMATION_SCALE_IN' => 'scaleIn',
        'ANIMATION_SCALE_OUT' => 'scaleOut',
        'ANIMATION_ROTATE_IN' => 'rotateIn',
        'ANIMATION_ROTATE_OUT' => 'rotateOut',

        // Easing
        'EASING_LINEAR' => 'linear',
        'EASING_EASE' => 'ease',
        'EASING_EASE_IN' => 'ease-in',
        'EASING_EASE_OUT' => 'ease-out',
        'EASING_EASE_IN_OUT' => 'ease-in-out',

        // États
        'STATE_LOADING' => 'loading',
        'STATE_READY' => 'ready',
        'STATE_ERROR' => 'error',
        'STATE_SUCCESS' => 'success',
        'STATE_WARNING' => 'warning'
    ];

    // ==========================================
    // OBJETS JAVASCRIPT COMPLETS
    // ==========================================

    private static $js_objects = [
        'DEFAULT_ELEMENT_PROPERTIES' => [
            'x' => 0,
            'y' => 0,
            'width' => 100,
            'height' => 100,
            'rotation' => 0,
            'scaleX' => 1,
            'scaleY' => 1,
            'skewX' => 0,
            'skewY' => 0,
            'opacity' => 1,
            'visible' => true,
            'locked' => false,
            'layer' => 0,
            'zIndex' => 0,
            'fillColor' => '#000000',
            'strokeColor' => '#000000',
            'strokeWidth' => 1,
            'shadowColor' => '#000000',
            'shadowBlur' => 0,
            'shadowOffsetX' => 0,
            'shadowOffsetY' => 0,
            'animationType' => '',
            'animationDuration' => 0,
            'animationDelay' => 0,
            'animationEasing' => 'linear',
            'animationLoop' => false,
            'clickable' => false,
            'hoverable' => false,
            'draggable' => true,
            'resizable' => true,
            'rotatable' => true,
            'selectable' => true
        ],

        'CANVAS_DEFAULTS' => [
            'width' => 595,
            'height' => 842,
            'dpi' => 72,
            'unit' => 'px',
            'bgColor' => '#ffffff',
            'containerBgColor' => '#f0f0f0',
            'borderColor' => '#cccccc',
            'borderWidth' => 1,
            'shadowEnabled' => false,
            'guidesEnabled' => true,
            'gridEnabled' => true,
            'gridSize' => 10,
            'snapToGrid' => true,
            'zoomMin' => 0.1,
            'zoomMax' => 5,
            'zoomDefault' => 1,
            'zoomStep' => 0.1,
            'selectionMode' => 'single',
            'multiSelect' => true,
            'dragEnabled' => true,
            'resizeEnabled' => true,
            'rotateEnabled' => true,
            'keyboardShortcuts' => true,
            'lazyLoadingEditor' => true,
            'preloadCritical' => true,
            'lazyLoadingPlugin' => false,
            'fpsTarget' => 60,
            'memoryLimitJS' => 50,
            'memoryLimitPHP' => 128,
            'responseTimeout' => 30000,
            'exportFormat' => 'pdf',
            'exportQuality' => 90,
            'exportTransparent' => false,
            'historyEnabled' => true,
            'historyMax' => 50,
            'debugEnabled' => false,
            'performanceMonitoring' => false,
            'errorReporting' => true
        ],

        'TOOLBAR_TOOLS' => [
            'select' => [
                'id' => 'select',
                'name' => 'Sélection',
                'icon' => 'cursor',
                'cursor' => 'default',
                'shortcut' => 'V'
            ],
            'text' => [
                'id' => 'text',
                'name' => 'Texte',
                'icon' => 'text',
                'cursor' => 'text',
                'shortcut' => 'T'
            ],
            'image' => [
                'id' => 'image',
                'name' => 'Image',
                'icon' => 'image',
                'cursor' => 'crosshair',
                'shortcut' => 'I'
            ],
            'shape' => [
                'id' => 'shape',
                'name' => 'Forme',
                'icon' => 'shape',
                'cursor' => 'crosshair',
                'shortcut' => 'S'
            ],
            'line' => [
                'id' => 'line',
                'name' => 'Ligne',
                'icon' => 'line',
                'cursor' => 'crosshair',
                'shortcut' => 'L'
            ],
            'rectangle' => [
                'id' => 'rectangle',
                'name' => 'Rectangle',
                'icon' => 'rectangle',
                'cursor' => 'crosshair',
                'shortcut' => 'R'
            ],
            'circle' => [
                'id' => 'circle',
                'name' => 'Cercle',
                'icon' => 'circle',
                'cursor' => 'crosshair',
                'shortcut' => 'C'
            ],
            'pan' => [
                'id' => 'pan',
                'name' => 'Déplacer',
                'icon' => 'pan',
                'cursor' => 'grab',
                'shortcut' => 'H'
            ],
            'zoom' => [
                'id' => 'zoom',
                'name' => 'Zoom',
                'icon' => 'zoom',
                'cursor' => 'zoom-in',
                'shortcut' => 'Z'
            ]
        ],

        'KEYBOARD_SHORTCUTS' => [
            'select' => 'V',
            'text' => 'T',
            'image' => 'I',
            'shape' => 'S',
            'line' => 'L',
            'rectangle' => 'R',
            'circle' => 'C',
            'pan' => 'H',
            'zoom' => 'Z',
            'delete' => 'Delete',
            'copy' => 'Ctrl+C',
            'paste' => 'Ctrl+V',
            'undo' => 'Ctrl+Z',
            'redo' => 'Ctrl+Y',
            'save' => 'Ctrl+S',
            'export' => 'Ctrl+E',
            'zoom_in' => 'Ctrl++',
            'zoom_out' => 'Ctrl+-',
            'zoom_fit' => 'Ctrl+0',
            'select_all' => 'Ctrl+A',
            'group' => 'Ctrl+G',
            'ungroup' => 'Ctrl+Shift+G'
        ]
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir toutes les constantes JavaScript
     */
    public static function get_js_constants()
    {
        return self::$js_constants;
    }

    /**
     * Obtenir une constante JavaScript spécifique
     */
    public static function get_js_constant($key)
    {
        return self::$js_constants[$key] ?? null;
    }

    /**
     * Obtenir tous les objets JavaScript
     */
    public static function get_js_objects()
    {
        return self::$js_objects;
    }

    /**
     * Obtenir un objet JavaScript spécifique
     */
    public static function get_js_object($key)
    {
        return self::$js_objects[$key] ?? null;
    }

    /**
     * Générer le code JavaScript pour les constantes
     */
    public static function generate_js_constants_script()
    {
        $script = "<script>\n";
        $script .= "window.PDF_BUILDER_CONSTANTS = {\n";

        $constants = [];
        foreach (self::$js_constants as $key => $value) {
            $constants[] = "    {$key}: " . json_encode($value);
        }

        $script .= implode(",\n", $constants);
        $script .= "\n};\n";
        $script .= "</script>\n";

        return $script;
    }

    /**
     * Générer le code JavaScript pour les objets
     */
    public static function generate_js_objects_script()
    {
        $script = "<script>\n";
        $script .= "window.PDF_BUILDER_OBJECTS = {\n";

        $objects = [];
        foreach (self::$js_objects as $key => $value) {
            $objects[] = "    {$key}: " . json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $script .= implode(",\n", $objects);
        $script .= "\n};\n";
        $script .= "</script>\n";

        return $script;
    }

    /**
     * Générer tout le code JavaScript nécessaire
     */
    public static function generate_all_js_scripts()
    {
        return self::generate_js_constants_script() . "\n" . self::generate_js_objects_script();
    }
}
