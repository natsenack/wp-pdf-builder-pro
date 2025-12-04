<?php
/**
 * Diagnostic script pour détecter qui charge la classe Masterminds\HTML5
 */

// Fonction pour logger les chargements de classes
function log_class_loading($class_name) {
    if (strpos($class_name, 'HTML5') !== false || strpos($class_name, 'Masterminds') !== false) {
        error_log("CLASS LOADING: $class_name loaded from: " . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[1]['file'] ?? 'unknown');
    }
}

// Enregistrer la fonction de chargement automatique
spl_autoload_register('log_class_loading');

// Vérifier si la classe existe déjà
if (class_exists('Masterminds\HTML5')) {
    error_log("HTML5 CLASS ALREADY EXISTS before plugin loading");
} else {
    error_log("HTML5 CLASS NOT YET LOADED");
}

// Simuler le chargement du plugin
$plugin_autoload = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/plugin/vendor/autoload.php';
if (file_exists($plugin_autoload)) {
    error_log("Loading plugin autoload: $plugin_autoload");
    require_once $plugin_autoload;

    if (class_exists('Masterminds\HTML5')) {
        error_log("HTML5 CLASS LOADED SUCCESSFULLY by plugin");
    } else {
        error_log("HTML5 CLASS NOT FOUND after plugin autoload");
    }
} else {
    error_log("Plugin autoload not found: $plugin_autoload");
}

// Lister tous les plugins actifs
$active_plugins = get_option('active_plugins', array());
error_log("ACTIVE PLUGINS: " . implode(', ', $active_plugins));

// Chercher dans les plugins actifs ceux qui pourraient utiliser dompdf
foreach ($active_plugins as $plugin) {
    $plugin_path = WP_PLUGIN_DIR . '/' . dirname($plugin);
    $composer_file = $plugin_path . '/composer.json';
    $vendor_autoload = $plugin_path . '/vendor/autoload.php';

    if (file_exists($composer_file)) {
        $composer_content = file_get_contents($composer_file);
        if (strpos($composer_content, 'dompdf') !== false || strpos($composer_content, 'masterminds') !== false) {
            error_log("PLUGIN WITH DOMPDF FOUND: $plugin - composer.json contains dompdf/masterminds");
        }
    }

    if (file_exists($vendor_autoload)) {
        error_log("PLUGIN WITH VENDOR AUTOLOAD: $plugin");
    }
}

echo "Diagnostic completed. Check error logs for details.";