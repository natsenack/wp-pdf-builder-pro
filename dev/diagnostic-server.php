<?php
// Script de diagnostic pour PDF Builder Pro
header('Content-Type: text/plain');

// Test 1: Vérifier si les constantes sont définies
echo "=== TEST 1: Constantes WordPress ===\n";
echo "WP_CONTENT_DIR: " . (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : 'NON DEFINI') . "\n";
echo "WP_PLUGIN_DIR: " . (defined('WP_PLUGIN_DIR') ? WP_PLUGIN_DIR : 'NON DEFINI') . "\n";

// Test 2: Vérifier si le plugin est actif
echo "\n=== TEST 2: Plugin actif ===\n";
$active_plugins = get_option('active_plugins', array());
$plugin_found = false;
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'pdf-builder-pro') !== false) {
        echo "Plugin trouvé: $plugin\n";
        $plugin_found = true;
    }
}
if (!$plugin_found) {
    echo "Plugin PDF Builder Pro NON trouvé dans les plugins actifs\n";
}

// Test 3: Vérifier les chemins des assets
echo "\n=== TEST 3: Chemins des assets ===\n";
$plugin_dir = dirname(dirname(plugin_dir_path(__FILE__))) . '/';
echo "Plugin directory: $plugin_dir\n";

$assets_dir = $plugin_dir . 'assets/js/dist/';
echo "Assets directory: $assets_dir\n";
echo "Assets directory exists: " . (file_exists($assets_dir) ? 'OUI' : 'NON') . "\n";

// Test 4: Vérifier le fichier JavaScript principal
echo "\n=== TEST 4: Fichier JavaScript ===\n";
$js_file = $assets_dir . 'pdf-builder-admin.js';
echo "JS file path: $js_file\n";
echo "JS file exists: " . (file_exists($js_file) ? 'OUI' : 'NON') . "\n";

if (file_exists($js_file)) {
    echo "JS file size: " . filesize($js_file) . " bytes\n";
    echo "JS file modified: " . date('Y-m-d H:i:s', filemtime($js_file)) . "\n";
}

// Test 5: Vérifier l'URL des assets
echo "\n=== TEST 5: URL des assets ===\n";
$assets_url = plugins_url('assets/js/dist/pdf-builder-admin.js', $plugin_dir . 'pdf-builder-pro.php');
echo "Assets URL: $assets_url\n";

// Test 6: Vérifier les actions AJAX
echo "\n=== TEST 6: Actions AJAX ===\n";
$ajax_actions = array(
    'wp_ajax_pdf_builder_load_template',
    'wp_ajax_nopriv_pdf_builder_load_template'
);

foreach ($ajax_actions as $action) {
    echo "Action '$action' has callbacks: " . (has_action($action) ? 'OUI' : 'NON') . "\n";
}

// Test 7: Vérifier la base de données
echo "\n=== TEST 7: Base de données ===\n";
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';
echo "Table templates: $table_templates\n";

if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") == $table_templates) {
    echo "Table exists: OUI\n";
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
    echo "Number of templates: $count\n";

    if ($count > 0) {
        $templates = $wpdb->get_results("SELECT id, name FROM $table_templates LIMIT 5");
        echo "Sample templates:\n";
        foreach ($templates as $template) {
            echo "  - ID: {$template->id}, Name: {$template->name}\n";
        }
    }
} else {
    echo "Table exists: NON\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
?>