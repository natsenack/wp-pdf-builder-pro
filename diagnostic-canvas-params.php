<?php
/**
 * Diagnostic des paramètres canvas
 */

// Charger WordPress
require_once('../../../wp-load.php');

// Vérifier les paramètres sauvegardés
$settings = get_option('pdf_builder_settings', []);
echo "=== PARAMÈTRES SAUVEGARDÉS ===\n";
echo "pan_with_mouse: " . (isset($settings['pan_with_mouse']) ? ($settings['pan_with_mouse'] ? 'true' : 'false') : 'NOT SET') . "\n";
echo "smooth_zoom: " . (isset($settings['smooth_zoom']) ? ($settings['smooth_zoom'] ? 'true' : 'false') : 'NOT SET') . "\n";
echo "zoom_with_wheel: " . (isset($settings['zoom_with_wheel']) ? ($settings['zoom_with_wheel'] ? 'true' : 'false') : 'NOT SET') . "\n";

// Vérifier les anciennes options individuelles (au cas où)
echo "\n=== ANCIENNES OPTIONS INDIVIDUELLES ===\n";
echo "pdf_builder_pan_with_mouse: " . (get_option('pdf_builder_pan_with_mouse', 'NOT SET')) . "\n";
echo "pdf_builder_smooth_zoom: " . (get_option('pdf_builder_smooth_zoom', 'NOT SET')) . "\n";

// Vérifier la localisation JavaScript
echo "\n=== LOCALISATION JAVASCRIPT ===\n";
$canvas_settings = get_option('pdf_builder_settings', []);
$localized = [
    'pan_with_mouse' => $canvas_settings['pan_with_mouse'] ?? true,
    'smooth_zoom' => $canvas_settings['smooth_zoom'] ?? true,
];
echo "pan_with_mouse (localized): " . ($localized['pan_with_mouse'] ? 'true' : 'false') . "\n";
echo "smooth_zoom (localized): " . ($localized['smooth_zoom'] ? 'true' : 'false') . "\n";

echo "\n=== DIAGNOSTIC TERMINÉ ===\n";
?>