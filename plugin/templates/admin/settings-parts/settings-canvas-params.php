<?php
/**
 * Paramètres canvas pour JavaScript
 * Définit les paramètres canvas globalement avant le chargement du JS
 */

// Assurer que les constantes sont chargées
if (!defined('PDF_BUILDER_PAPER_FORMATS')) {
    // Définir les constantes manuellement si non chargées
    define('PDF_BUILDER_PAPER_FORMATS', [
        'A4' => ['width' => 210, 'height' => 297],
        'A3' => ['width' => 297, 'height' => 420],
        'A5' => ['width' => 148, 'height' => 210],
        'Letter' => ['width' => 215.9, 'height' => 279.4],
        'Legal' => ['width' => 215.9, 'height' => 355.6],
        'Tabloid' => ['width' => 279.4, 'height' => 431.8]
    ]);
}

// Récupérer les paramètres canvas depuis les options séparées (synchronisées)
$canvas_settings_js = [
    // Dimensions & Format
    'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
    'default_canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'default_canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
    'default_canvas_dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
    'canvas_width' => intval(get_option('pdf_builder_canvas_width', 794)),
    'canvas_height' => intval(get_option('pdf_builder_canvas_height', 1123)),
    
    // Apparence
    'canvas_bg_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
    'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
    'canvas_border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
    'canvas_shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') === '1',
    'canvas_container_bg_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
    
    // Zoom & Navigation
    'zoom_min' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
    'zoom_max' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
    'zoom_default' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
    'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
    
    // Grille
    'canvas_grid_enabled' => get_option('pdf_builder_canvas_grid_enabled', '1') === '1',
    'canvas_grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
    'canvas_snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') === '1',
    'canvas_guides_enabled' => get_option('pdf_builder_canvas_guides_enabled', '1') === '1',
    
    // Interactions
    'canvas_drag_enabled' => get_option('pdf_builder_canvas_drag_enabled', '1') === '1',
    'canvas_resize_enabled' => get_option('pdf_builder_canvas_resize_enabled', '1') === '1',
    'canvas_rotate_enabled' => get_option('pdf_builder_canvas_rotate_enabled', '1') === '1',
    'canvas_multi_select' => get_option('pdf_builder_canvas_multi_select', '1') === '1',
    'canvas_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') === '1',
    'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'bounding_box'),
    
    // Export
    'canvas_export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
    'canvas_export_quality' => intval(get_option('pdf_builder_canvas_export_quality', 90)),
    'canvas_export_transparent' => get_option('pdf_builder_canvas_export_transparent', '0') === '1',
    
    // Performance
    'canvas_fps_target' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    'canvas_memory_limit_js' => intval(get_option('pdf_builder_canvas_memory_limit_js', 128)),
    'canvas_memory_limit_php' => intval(get_option('pdf_builder_canvas_memory_limit_php', 256)),
    'canvas_lazy_loading_editor' => get_option('pdf_builder_canvas_lazy_loading_editor', '1') === '1',
    'canvas_preload_critical' => get_option('pdf_builder_canvas_preload_critical', '1') === '1',
    'canvas_lazy_loading_plugin' => get_option('pdf_builder_canvas_lazy_loading_plugin', '1') === '1',
    
    // Debug
    'canvas_debug_enabled' => get_option('pdf_builder_canvas_debug_enabled', '0') === '1',
    'canvas_performance_monitoring' => get_option('pdf_builder_canvas_performance_monitoring', '0') === '1',
    'canvas_error_reporting' => get_option('pdf_builder_canvas_error_reporting', '0') === '1'
];

// Debug: Afficher les valeurs PHP chargées
PDF_Builder_Security_Manager::debug_log('php_errors', 'Canvas settings loaded:', $canvas_settings_js);

// Définir pdfBuilderCanvasSettings globalement avant tout autre script
?>
<script>
window.pdfBuilderCanvasSettings = <?php echo wp_json_encode($canvas_settings_js); ?>;
pdfBuilderDebug('Canvas settings loaded from WordPress options:', window.pdfBuilderCanvasSettings);
window.pdfBuilderCanvasSettings.nonce = '<?php echo wp_create_nonce('pdf_builder_canvas_nonce'); ?>';

// Dimensions standard des formats de papier en mm (centralisées)
window.pdfBuilderPaperFormats = <?php echo wp_json_encode(PDF_BUILDER_PAPER_FORMATS); ?>;

// Fonction pour convertir le format et l'orientation en dimensions pixels
window.pdfBuilderCanvasSettings.getDimensionsFromFormat = function(format, orientation) {
    // Utiliser les dimensions standard centralisées en mm
    const formatDimensionsMM = window.pdfBuilderPaperFormats || {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'A5': { width: 148, height: 210 },
        'Letter': { width: 215.9, height: 279.4 },
        'Legal': { width: 215.9, height: 355.6 },
        'Tabloid': { width: 279.4, height: 431.8 }
    };

    const dimsMM = formatDimensionsMM[format] || formatDimensionsMM['A4'];

    // Calculer les dimensions en pixels (1 inch = 25.4mm)
    const dpi = window.pdfBuilderCanvasSettings?.default_canvas_dpi || 96;
    const pixelsPerMM = dpi / 25.4;
    const dims = {
        width: Math.round(dimsMM.width * pixelsPerMM),
        height: Math.round(dimsMM.height * pixelsPerMM)
    };

    // Inverser les dimensions si orientation paysage
    if (orientation === 'landscape') {
        return { width: dims.height, height: dims.width };
    }

    return dims;
};

// Ajouter les dimensions calculées aux paramètres
window.pdfBuilderCanvasSettings.default_canvas_width = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
    window.pdfBuilderCanvasSettings.default_canvas_format,
    window.pdfBuilderCanvasSettings.default_canvas_orientation
).width;

window.pdfBuilderCanvasSettings.default_canvas_height = window.pdfBuilderCanvasSettings.getDimensionsFromFormat(
    window.pdfBuilderCanvasSettings.default_canvas_format,
    window.pdfBuilderCanvasSettings.default_canvas_orientation
).height;

// ✅ PDF_BUILDER_VERBOSE initialized in PDF_Builder_Admin.php via wp_add_inline_script()
</script>