<?php
/**
 * PDF Builder Pro - Diagnostic Tool
 * Vérifie que les constantes et le chargement fonctionnent correctement
 */

// Simuler un environnement WordPress minimal pour les tests
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Fonction mock pour plugins_url
if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://localhost/wp-content/plugins/wp-pdf-builder-pro/' . ltrim($path, '/');
    }
}

// Fonction mock pour register_activation_hook
if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $function) {
        // Mock - ne fait rien
    }
}

// Fonction mock pour register_deactivation_hook
if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $function) {
        // Mock - ne fait rien
    }
}

// Fonction mock pour add_action
if (!function_exists('add_action')) {
    function add_action($hook, $function, $priority = 10, $accepted_args = 1) {
        // Mock - ne fait rien
    }
}

// Fonction mock pour load_plugin_textdomain
if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $path = false) {
        // Mock - ne fait rien
    }
}

// Fonction mock pour plugin_basename
if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename(dirname($file)) . '/' . basename($file);
    }
}

echo "=== PDF Builder Pro - Diagnostic ===\n\n";

// Tester le chargement du fichier principal
echo "1. Chargement du fichier principal...\n";
try {
    require_once 'pdf-builder-pro.php';
    echo "   ✓ Fichier principal chargé avec succès\n";
} catch (Exception $e) {
    echo "   ✗ Erreur lors du chargement: " . $e->getMessage() . "\n";
}

// Tester les constantes
echo "\n2. Vérification des constantes...\n";
$constants = [
    'PDF_BUILDER_PLUGIN_FILE',
    'PDF_BUILDER_PLUGIN_DIR',
    'PDF_BUILDER_VERSION'
];

foreach ($constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        if ($const === 'PDF_BUILDER_PLUGIN_DIR') {
            $value = basename(dirname($value)) . '/' . basename($value);
        } elseif ($const === 'PDF_BUILDER_PLUGIN_FILE') {
            $value = basename($value);
        }
        echo "   ✓ $const = $value\n";
    } else {
        echo "   ✗ $const non défini\n";
    }
}

// Tester le chargement du bootstrap
echo "\n3. Test du chargement du bootstrap...\n";
if (function_exists('pdf_builder_init')) {
    echo "   ✓ Fonction pdf_builder_init() existe\n";

    try {
        // Appeler la fonction d'initialisation pour charger le bootstrap
        echo "   → Appel de pdf_builder_init()...\n";
        pdf_builder_init();
        echo "   ✓ pdf_builder_init() exécutée\n";

        // Vérifier les conditions dans pdf_builder_init
        echo "   → Vérification des conditions WordPress...\n";
        echo "     - function_exists('get_option'): " . (function_exists('get_option') ? 'YES' : 'NO') . "\n";
        echo "     - defined('ABSPATH'): " . (defined('ABSPATH') ? 'YES' : 'NO') . "\n";

        if (function_exists('pdf_builder_load_bootstrap')) {
            echo "   ✓ Fonction pdf_builder_load_bootstrap() existe après init\n";

            try {
                // Tester le chargement du bootstrap
                ob_start();
                pdf_builder_load_bootstrap();
                ob_end_clean();
                echo "   ✓ Bootstrap chargé sans erreur fatale\n";
            } catch (Exception $e) {
                echo "   ✗ Erreur bootstrap: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ✗ Fonction pdf_builder_load_bootstrap() n'existe pas après init\n";
        }
    } catch (Exception $e) {
        echo "   ✗ Erreur lors de pdf_builder_init(): " . $e->getMessage() . "\n";
    }
} else {
    echo "   ✗ Fonction pdf_builder_init() n'existe pas\n";
}

echo "\n=== Diagnostic terminé ===\n";