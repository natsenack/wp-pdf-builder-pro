<?php
/**
 * Script de diagnostic pour PDF Builder Pro
 * Ce script simule un environnement WordPress minimal pour tester le chargement du plugin
 */

// IMPORTANT: Définir ABSPATH AVANT tout include
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

// Simuler les fonctions WordPress de base
if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        echo "WP Die: $message\n";
        exit;
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null) {
        echo "JSON Error: " . json_encode($data) . "\n";
        exit;
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null) {
        echo "JSON Success: " . json_encode($data) . "\n";
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        return true; // Simuler un admin
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return true; // Simuler nonce valide
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim($str);
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1; // Simuler user ID 1
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return date($type === 'timestamp' ? 'U' : 'Y-m-d H:i:s');
    }
}

// Simuler les constantes WordPress
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', false);
}

// Activer le rapport d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNOSTIC PDF BUILDER PRO ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "ABSPATH défini: " . (defined('ABSPATH') ? 'OUI' : 'NON') . "\n\n";

// Test 1: Vérifier les fichiers critiques
echo "1. VÉRIFICATION DES FICHIERS CRITIQUES:\n";
$critical_files = [
    'plugin/pdf-builder-pro.php',
    'plugin/bootstrap.php',
    'plugin/core/autoloader.php',
    'plugin/src/Core/PDF_Builder_Update_Manager.php',
    'plugin/src/Core/PDF_Builder_Metrics_Analytics.php',
    'plugin/src/utilities/PDF_Builder_Notification_Manager.php'
];

foreach ($critical_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "✓ $file existe\n";
    } else {
        echo "✗ $file MANQUANT\n";
    }
}
echo "\n";

// Test 2: Tester le chargement du fichier principal
echo "2. TEST DE CHARGEMENT DU PLUGIN PRINCIPAL:\n";
try {
    // Inclure le fichier principal
    require_once __DIR__ . '/plugin/pdf-builder-pro.php';
    echo "✓ pdf-builder-pro.php chargé avec succès\n";
} catch (Exception $e) {
    echo "✗ ERREUR lors du chargement de pdf-builder-pro.php: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "✗ ERREUR FATALE lors du chargement de pdf-builder-pro.php: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Vérifier les classes critiques
echo "3. VÉRIFICATION DES CLASSES CRITIQUES:\n";
$critical_classes = [
    'PDF_Builder_Update_Manager',
    'PDF_Builder_Metrics_Analytics',
    'PDF_Builder_UI_Notification_Manager', // Classe renommée
    'PDF_Builder_Intelligent_Loader',
    'PDF_Builder_Config_Manager'
];

foreach ($critical_classes as $class) {
    if (class_exists($class)) {
        echo "✓ Classe $class existe\n";
    } else {
        echo "✗ Classe $class MANQUANTE\n";
    }
}
echo "\n";

// Test 4: Vérifier les fonctions critiques
echo "4. VÉRIFICATION DES FONCTIONS CRITIQUES:\n";
$critical_functions = [
    'pdf_builder_get_db_update_status', // Fonction renommée
    'pdf_builder_get_metrics_analytics', // Fonction renommée
    'pdf_builder_translate',
    'pdf_builder_reporting',
    'pdf_builder_generate_report',
    'pdf_builder_check_updates',
    'pdf_builder_install_update'
];

foreach ($critical_functions as $function) {
    if (function_exists($function)) {
        echo "✓ Fonction $function existe\n";
    } else {
        echo "✗ Fonction $function MANQUANTE\n";
    }
}
echo "\n";

// Test 5: Tester l'initialisation des systèmes
echo "5. TEST D'INITIALISATION DES SYSTÈMES:\n";
try {
    // Tester l'initialisation du chargeur intelligent
    if (class_exists('PDF_Builder_Intelligent_Loader')) {
        $loader = PDF_Builder_Intelligent_Loader::get_instance();
        echo "✓ PDF_Builder_Intelligent_Loader initialisé\n";
    } else {
        echo "✗ PDF_Builder_Intelligent_Loader non disponible\n";
    }

    // Tester l'initialisation du gestionnaire de configuration
    if (class_exists('PDF_Builder_Config_Manager')) {
        $config = PDF_Builder_Config_Manager::get_instance();
        echo "✓ PDF_Builder_Config_Manager initialisé\n";
    } else {
        echo "✗ PDF_Builder_Config_Manager non disponible\n";
    }

} catch (Exception $e) {
    echo "✗ ERREUR lors de l'initialisation: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "✗ ERREUR FATALE lors de l'initialisation: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Vérifier les doublons potentiels
echo "6. VÉRIFICATION DES DOUBLONS POTENTIELS:\n";
$doublons_check = [
    'pdf_builder_get_update_status' => 'pdf_builder_get_db_update_status',
    'pdf_builder_get_analytics' => 'pdf_builder_get_metrics_analytics',
    'PDF_Builder_Notification_Manager' => 'PDF_Builder_UI_Notification_Manager'
];

foreach ($doublons_check as $old => $new) {
    if (function_exists($old)) {
        echo "⚠ ANCIENNE FONCTION $old existe encore (devrait être remplacée par $new)\n";
    } else {
        echo "✓ Ancienne fonction $old supprimée correctement\n";
    }

    if (class_exists($old)) {
        echo "⚠ ANCIENNE CLASSE $old existe encore (devrait être remplacée par $new)\n";
    } else {
        echo "✓ Ancienne classe $old supprimée correctement\n";
    }
}
echo "\n";

echo "=== FIN DU DIAGNOSTIC ===\n";
?>