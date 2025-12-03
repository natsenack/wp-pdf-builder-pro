<?php
/**
 * Test debug pour vérifier le chargement des paramètres
 */

// Charger WordPress
require_once __DIR__ . '/../../wp-load.php';

// Vérifier les constantes
echo "=== VÉRIFICATION DES CONSTANTES ===\n";
echo "PDF_BUILDER_PRO_ASSETS_URL: " . (defined('PDF_BUILDER_PRO_ASSETS_URL') ? PDF_BUILDER_PRO_ASSETS_URL : 'NON DÉFINI') . "\n";
echo "PDF_BUILDER_PRO_VERSION: " . (defined('PDF_BUILDER_PRO_VERSION') ? PDF_BUILDER_PRO_VERSION : 'NON DÉFINI') . "\n";
echo "PDF_BUILDER_PLUGIN_FILE: " . (defined('PDF_BUILDER_PLUGIN_FILE') ? PDF_BUILDER_PLUGIN_FILE : 'NON DÉFINI') . "\n";

// Vérifier les fichiers
echo "\n=== VÉRIFICATION DES FICHIERS ===\n";
$css_file = PDF_BUILDER_PRO_ASSETS_URL . 'css/settings.css';
$js_file = PDF_BUILDER_PRO_ASSETS_URL . 'js/settings-tabs.js';
echo "CSS settings.css existe: " . (file_exists(str_replace(plugins_url(), WP_PLUGIN_DIR, $css_file)) ? 'OUI' : 'NON') . "\n";
echo "JS settings-tabs.js existe: " . (file_exists(str_replace(plugins_url(), WP_PLUGIN_DIR, $js_file)) ? 'OUI' : 'NON') . "\n";

// Vérifier les permissions
echo "\n=== VÉRIFICATION DES PERMISSIONS ===\n";
$current_user = wp_get_current_user();
echo "Utilisateur connecté: " . ($current_user->ID ? "OUI (ID: " . $current_user->ID . ")" : "NON") . "\n";
echo "Cap pdf_builder_access: " . (current_user_can('pdf_builder_access') ? 'OUI' : 'NON') . "\n";
echo "Rôles: " . implode(', ', $current_user->roles) . "\n";

// Vérifier les options
echo "\n=== VÉRIFICATION DES OPTIONS ===\n";
$settings = get_option('pdf_builder_settings', array());
echo "Paramètres PDF Builder trouvés: " . (count($settings) > 0 ? 'OUI (' . count($settings) . ' paramètres)' : 'NON') . "\n";

// Vérifier le hook WordPress
echo "\n=== VÉRIFICATION DU HOOK ===\n";
global $hook_suffix;
echo 'Hook suffix actuel: ' . ($hook_suffix ?? 'NON DISPONIBLE') . "\n";

// Vérifier si settings.css est enqueued
echo "\n=== VÉRIFICATION DES ASSETS ENQUEUÉS ===\n";
global $wp_styles;
echo "PDF Builder CSS enqueued: " . (isset($wp_styles->registered['pdf-builder-settings']) ? 'OUI' : 'NON') . "\n";

global $wp_scripts;
echo "PDF Builder JS enqueued: " . (isset($wp_scripts->registered['pdf-builder-settings-tabs']) ? 'OUI' : 'NON') . "\n";

echo "\n=== FIN TEST ===\n";
?>
