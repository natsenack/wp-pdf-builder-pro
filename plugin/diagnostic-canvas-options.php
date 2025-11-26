<?php
/**
 * Diagnostic script pour identifier les données problématiques dans les options WordPress
 * qui pourraient causer des erreurs JavaScript "Unexpected end of input"
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Liste des options à vérifier
$options_to_check = [
    'pdf_builder_canvas_format',
    'pdf_builder_canvas_orientation',
    'pdf_builder_canvas_unit',
    'pdf_builder_canvas_bg_color',
    'pdf_builder_canvas_container_bg_color',
    'pdf_builder_canvas_border_color',
    'pdf_builder_canvas_grid_color',
    'pdf_builder_canvas_handle_color',
    'pdf_builder_canvas_export_format',
    'pdf_builder_canvas_pdf_author',
    'pdf_builder_canvas_pdf_subject',
    'pdf_builder_canvas_selection_mode',
];

echo "<h2>Diagnostic des options WordPress pour PDF Builder</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Option</th><th>Valeur</th><th>Contient caractères spéciaux</th><th>JSON valide</th></tr>";

foreach ($options_to_check as $option) {
    $value = get_option($option, '');
    $has_special_chars = false;
    $json_valid = true;

    // Vérifier les caractères spéciaux
    if (is_string($value)) {
        if (strpos($value, '"') !== false || strpos($value, "'") !== false ||
            strpos($value, "\r") !== false || strpos($value, "\n") !== false ||
            strpos($value, "\t") !== false || strpos($value, "\\") !== false) {
            $has_special_chars = true;
        }

        // Tester si le JSON est valide
        $test_json = wp_json_encode([$option => $value]);
        if ($test_json === false) {
            $json_valid = false;
        }
    }

    $special_status = $has_special_chars ? '<span style="color: red;">OUI</span>' : '<span style="color: green;">NON</span>';
    $json_status = $json_valid ? '<span style="color: green;">OUI</span>' : '<span style="color: red;">NON</span>';

    echo "<tr>";
    echo "<td>{$option}</td>";
    echo "<td style='max-width: 300px; word-wrap: break-word;'>" . esc_html($value) . "</td>";
    echo "<td>{$special_status}</td>";
    echo "<td>{$json_status}</td>";
    echo "</tr>";
}

echo "</table>";

// Tester la génération complète du script canvas
echo "<h3>Test de génération du script canvas settings</h3>";
$canvas_settings = [
    'default_canvas_format' => get_option('pdf_builder_canvas_format', 'A4'),
    'default_canvas_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'default_canvas_unit' => get_option('pdf_builder_canvas_unit', 'px'),
    'default_canvas_dpi' => intval(get_option('pdf_builder_canvas_dpi', 96)),
    'default_orientation' => get_option('pdf_builder_canvas_orientation', 'portrait'),
    'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
    'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false),
    'container_background_color' => get_option('pdf_builder_canvas_container_bg_color', '#f8f9fa'),
    'container_show_transparency' => get_option('pdf_builder_canvas_container_show_transparency', false),
    'border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
    'border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
    'shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', '0') == '1',
    'margin_top' => intval(get_option('pdf_builder_canvas_margin_top', 28)),
    'margin_right' => intval(get_option('pdf_builder_canvas_margin_right', 28)),
    'margin_bottom' => intval(get_option('pdf_builder_canvas_margin_bottom', 10)),
    'margin_left' => intval(get_option('pdf_builder_canvas_margin_left', 10)),
    'show_margins' => get_option('pdf_builder_canvas_show_margins', '0') == '1',
    'show_grid' => get_option('pdf_builder_canvas_grid_enabled', '1') == '1',
    'grid_size' => intval(get_option('pdf_builder_canvas_grid_size', 20)),
    'grid_color' => get_option('pdf_builder_canvas_grid_color', '#e0e0e0'),
    'snap_to_grid' => get_option('pdf_builder_canvas_snap_to_grid', '1') == '1',
    'snap_to_elements' => get_option('pdf_builder_canvas_snap_to_elements', '0') == '1',
    'snap_tolerance' => intval(get_option('pdf_builder_canvas_snap_tolerance', 5)),
    'show_guides' => get_option('pdf_builder_canvas_guides_enabled', '1') == '1',
    'navigation_enabled' => get_option('pdf_builder_canvas_navigation_enabled', '1') == '1',
    'default_zoom' => intval(get_option('pdf_builder_canvas_zoom_default', 100)),
    'min_zoom' => intval(get_option('pdf_builder_canvas_zoom_min', 10)),
    'max_zoom' => intval(get_option('pdf_builder_canvas_zoom_max', 500)),
    'zoom_step' => intval(get_option('pdf_builder_canvas_zoom_step', 25)),
    'zoom_with_wheel' => get_option('pdf_builder_canvas_zoom_with_wheel', '1') == '1',
    'pan_with_mouse' => get_option('pdf_builder_canvas_pan_enabled', '1') == '1',
    'show_resize_handles' => get_option('pdf_builder_canvas_show_resize_handles', '1') == '1',
    'handle_size' => intval(get_option('pdf_builder_canvas_handle_size', 8)),
    'handle_color' => get_option('pdf_builder_canvas_handle_color', '#007cba'),
    'enable_rotation' => get_option('pdf_builder_canvas_rotate_enabled', '1') == '1',
    'rotation_step' => intval(get_option('pdf_builder_canvas_rotation_step', 15)),
    'multi_select' => get_option('pdf_builder_canvas_multi_select', '1') == '1',
    'copy_paste_enabled' => get_option('pdf_builder_canvas_copy_paste_enabled', '1') == '1',
    'export_quality' => get_option('pdf_builder_canvas_export_quality', 90),
    'export_format' => get_option('pdf_builder_canvas_export_format', 'png'),
    'compress_images' => get_option('pdf_builder_canvas_compress_images', '1') == '1',
    'image_quality' => intval(get_option('pdf_builder_canvas_image_quality', 85)),
    'max_image_size' => intval(get_option('pdf_builder_canvas_max_image_size', 2048)),
    'include_metadata' => get_option('pdf_builder_canvas_include_metadata', '1') == '1',
    'pdf_author' => get_option('pdf_builder_canvas_pdf_author', 'PDF Builder Pro'),
    'pdf_subject' => get_option('pdf_builder_canvas_pdf_subject', ''),
    'auto_crop' => get_option('pdf_builder_canvas_auto_crop', '0') == '1',
    'embed_fonts' => get_option('pdf_builder_canvas_embed_fonts', '1') == '1',
    'optimize_for_web' => get_option('pdf_builder_canvas_optimize_for_web', '1') == '1',
    'enable_hardware_acceleration' => get_option('pdf_builder_canvas_enable_hardware_acceleration', '1') == '1',
    'limit_fps' => get_option('pdf_builder_canvas_limit_fps', '1') == '1',
    'max_fps' => intval(get_option('pdf_builder_canvas_fps_target', 60)),
    'undo_levels' => intval(get_option('pdf_builder_canvas_undo_levels', 50)),
    'redo_levels' => intval(get_option('pdf_builder_canvas_redo_levels', 50)),
    'enable_keyboard_shortcuts' => get_option('pdf_builder_canvas_keyboard_shortcuts', '1') == '1',
    'canvas_selection_mode' => get_option('pdf_builder_canvas_selection_mode', 'click'),
    'debug_mode' => get_option('pdf_builder_canvas_debug_mode', '0') == '1',
    'show_fps' => get_option('pdf_builder_canvas_show_fps', '0') == '1'
];

$json_result = wp_json_encode($canvas_settings);
if ($json_result === false) {
    echo "<p style='color: red;'>ERREUR: Impossible de générer le JSON pour les paramètres canvas!</p>";
} else {
    echo "<p style='color: green;'>JSON généré avec succès pour les paramètres canvas.</p>";
    echo "<details><summary>Voir le JSON généré</summary><pre>" . esc_html($json_result) . "</pre></details>";
}

// Test du script complet
$canvas_settings_script = "
window.pdfBuilderCanvasSettings = " . $json_result . ";
";

echo "<h3>Test du script JavaScript complet</h3>";
echo "<details><summary>Voir le script JavaScript généré</summary><pre>" . esc_html($canvas_settings_script) . "</pre></details>";

// Vérifier la syntaxe JavaScript basique
if (substr_count($canvas_settings_script, '{') !== substr_count($canvas_settings_script, '}')) {
    echo "<p style='color: red;'>ERREUR: Nombre d'accolades déséquilibré dans le script!</p>";
} else {
    echo "<p style='color: green;'>Syntaxe des accolades correcte.</p>";
}

if (substr_count($canvas_settings_script, '[') !== substr_count($canvas_settings_script, ']')) {
    echo "<p style='color: red;'>ERREUR: Nombre de crochets déséquilibré dans le script!</p>";
} else {
    echo "<p style='color: green;'>Syntaxe des crochets correcte.</p>";
}