<?php
/**
 * PDF Builder Pro V2 - Page d'administration
 * 
 * Cette page affiche l'interface React du PDF Builder
 */

// Vérifier les permissions WordPress
if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

// Inclure les assets React
require_once __DIR__ . '/includes/ReactAssetsV2.php';
?>

<div class="wrap pdf-builder-admin-container">
    <div class="pdf-builder-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="description">
            <?php _e('Édition de documents PDF avec PDF Builder Pro V2', 'pdf-builder-pro'); ?>
        </p>
    </div>
    
    <!-- Conteneur React principal -->
    <div id="pdf-builder-react-root" class="pdf-builder-root">
        <div class="pdf-builder-loading">
            <div class="spinner"></div>
            <p><?php _e('Chargement du PDF Builder...', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<style>
.pdf-builder-admin-container {
    margin: 0 -20px -20px -20px;
    padding: 0;
}

.pdf-builder-header {
    background: white;
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
    margin: 0 0 20px 0;
}

.pdf-builder-header h1 {
    margin: 0 0 10px 0;
}

.pdf-builder-root {
    background: white;
    min-height: 600px;
}

.pdf-builder-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 600px;
    gap: 20px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<?php
// Fonction helper pour récupérer les valeurs Canvas depuis les options individuelles
function get_canvas_option_editor($key, $default = '') {
    $option_key = 'pdf_builder_' . $key;
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    $value = isset($settings[$option_key]) ? $settings[$option_key] : null;

    // Debug log
    error_log("[EDITOR CANVAS] {$key}: looking for '{$option_key}', found: '" . ($value ?? 'NULL') . "', using default: '{$default}'");

    if ($value === null) {
        $value = $default;
    }

    return $value;
}

// Localiser les paramètres canvas pour JavaScript sur la page de l'éditeur
$canvas_settings_js = array(
    'border_color' => get_canvas_option_editor('canvas_border_color', '#cccccc'),
    'border_width' => get_canvas_option_editor('canvas_border_width', '1'),
    'bg_color' => get_canvas_option_editor('canvas_bg_color', '#ffffff'),
    'container_bg_color' => get_canvas_option_editor('canvas_container_bg_color', '#f8f9fa'),
    'width' => get_canvas_option_editor('canvas_width', '794'),
    'height' => get_canvas_option_editor('canvas_height', '1123'),
    'dpi' => get_canvas_option_editor('canvas_dpi', '96'),
    'format' => get_canvas_option_editor('canvas_format', 'A4'),
    'grid_enabled' => get_canvas_option_editor('canvas_grid_enabled', '1'),
    'grid_size' => get_canvas_option_editor('canvas_grid_size', '20'),
    'guides_enabled' => get_canvas_option_editor('canvas_guides_enabled', '1'),
    'snap_to_grid' => get_canvas_option_editor('canvas_snap_to_grid', '1'),
    'zoom_min' => get_canvas_option_editor('canvas_zoom_min', '25'),
    'zoom_max' => get_canvas_option_editor('canvas_zoom_max', '500'),
    'zoom_default' => get_canvas_option_editor('canvas_zoom_default', '100'),
    'zoom_step' => get_canvas_option_editor('canvas_zoom_step', '25'),
    'shadow_enabled' => get_canvas_option_editor('canvas_shadow_enabled', '0'),
    'export_quality' => get_canvas_option_editor('canvas_export_quality', '90'),
    'export_format' => get_canvas_option_editor('canvas_export_format', 'png'),
    'export_transparent' => get_canvas_option_editor('canvas_export_transparent', '0'),
    'drag_enabled' => get_canvas_option_editor('canvas_drag_enabled', '1'),
    'resize_enabled' => get_canvas_option_editor('canvas_resize_enabled', '1'),
    'rotate_enabled' => get_canvas_option_editor('canvas_rotate_enabled', '1'),
    'multi_select' => get_canvas_option_editor('canvas_multi_select', '1'),
    'selection_mode' => get_canvas_option_editor('canvas_selection_mode', 'single'),
    'keyboard_shortcuts' => get_canvas_option_editor('canvas_keyboard_shortcuts', '1'),
    'fps_target' => get_canvas_option_editor('canvas_fps_target', '60'),
    'memory_limit_js' => get_canvas_option_editor('canvas_memory_limit_js', '50'),
    'response_timeout' => get_canvas_option_editor('canvas_response_timeout', '5000'),
    'lazy_loading_editor' => get_canvas_option_editor('canvas_lazy_loading_editor', '1'),
    'preload_critical' => get_canvas_option_editor('canvas_preload_critical', '1'),
    'lazy_loading_plugin' => get_canvas_option_editor('canvas_lazy_loading_plugin', '1'),
    'debug_enabled' => get_canvas_option_editor('canvas_debug_enabled', '0'),
    'performance_monitoring' => get_canvas_option_editor('canvas_performance_monitoring', '0'),
    'error_reporting' => get_canvas_option_editor('canvas_error_reporting', '0'),
    'memory_limit_php' => get_canvas_option_editor('canvas_memory_limit_php', '128')
);

// Debug: Afficher les paramètres dans les logs PHP
error_log('[PDF BUILDER EDITOR] Canvas settings to be localized: ' . print_r($canvas_settings_js, true));

wp_localize_script('pdf-builder-react-init', 'pdfBuilderCanvasSettings', $canvas_settings_js);

// Debug: Vérifier que la localisation a été faite
error_log('[PDF BUILDER EDITOR] wp_localize_script called for pdf-builder-react-init with handle check');
?>

