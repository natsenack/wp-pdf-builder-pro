<?php
/**
 * Diagnostic avancé pour PDF Builder Pro
 * Test progressif pour identifier la cause de la page blanche
 */

// Simuler les constantes WordPress de base
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Fonctions WordPress de base simulées
if (!function_exists('get_option')) {
    function get_option($key, $default = null) {
        $options = [
            'admin_email' => 'admin@example.com',
            'siteurl' => 'http://localhost',
            'home' => 'http://localhost'
        ];
        return $options[$key] ?? $default;
    }
}

if (!function_exists('wp_timezone_string')) {
    function wp_timezone_string() {
        return 'Europe/Paris';
    }
}

if (!function_exists('get_site_url')) {
    function get_site_url() {
        return 'http://localhost';
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10) {
        // Ne rien faire en mode test
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10) {
        // Ne rien faire en mode test
    }
}

if (!function_exists('wp_get_theme')) {
    function wp_get_theme() {
        return (object)['Name' => 'Test Theme'];
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($key) {
        $info = [
            'version' => '6.0'
        ];
        return $info[$key] ?? '';
    }
}

if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data) {
        return json_encode($data);
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '', $key);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($text) {
        return strip_tags($text);
    }
}

if (!function_exists('is_email')) {
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => '/tmp/uploads',
            'baseurl' => 'http://localhost/uploads'
        ];
    }
}

echo "=== DIAGNOSTIC AVANCÉ PDF BUILDER PRO ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$errors = [];
$warnings = [];

// Test 1: Inclusion du fichier principal
echo "1. Test d'inclusion du fichier principal...\n";
try {
    require_once '../plugin/pdf-builder-pro.php';
    echo "   ✅ Fichier principal inclus avec succès\n";
} catch (Exception $e) {
    echo "   ❌ ERREUR: " . $e->getMessage() . "\n";
    $errors[] = "Inclusion fichier principal: " . $e->getMessage();
} catch (Error $e) {
    echo "   ❌ ERREUR FATALE: " . $e->getMessage() . "\n";
    $errors[] = "Inclusion fichier principal (fatal): " . $e->getMessage();
}

// Test 2: Vérification des classes critiques
echo "\n2. Vérification des classes critiques...\n";
$critical_classes = [
    'PDF_Builder_Global_Config_Manager',
    'PDF_Builder_Option_Config_Manager',
    'PDF_Builder_Onboarding_Manager_Alias',
    'PDF_Builder_Onboarding_Manager_Standalone'
];

foreach ($critical_classes as $class) {
    if (class_exists($class)) {
        echo "   ✅ $class existe\n";
    } else {
        echo "   ❌ $class n'existe pas\n";
        $errors[] = "Classe manquante: $class";
    }
}

// Test 3: Test d'initialisation des gestionnaires
echo "\n3. Test d'initialisation des gestionnaires...\n";
try {
    if (class_exists('PDF_Builder_Global_Config_Manager')) {
        $config = PDF_Builder_Global_Config_Manager::get_instance();
        echo "   ✅ PDF_Builder_Global_Config_Manager initialisé\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur PDF_Builder_Global_Config_Manager: " . $e->getMessage() . "\n";
    $errors[] = "Initialisation config manager: " . $e->getMessage();
}

try {
    if (class_exists('PDF_Builder_Option_Config_Manager')) {
        $option_config = new PDF_Builder_Option_Config_Manager();
        echo "   ✅ PDF_Builder_Option_Config_Manager initialisé\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur PDF_Builder_Option_Config_Manager: " . $e->getMessage() . "\n";
    $errors[] = "Initialisation option config: " . $e->getMessage();
}

// Test 4: Vérification des fichiers inclus
echo "\n4. Vérification des fichiers Core inclus...\n";
$core_files = [
    'PDF_Builder_User_Manager.php',
    'PDF_Builder_License_Manager.php',
    'PDF_Builder_Ajax_Handler.php',
    'PDF_Builder_Intelligent_Loader.php',
    'PDF_Builder_Smart_Cache.php',
    'PDF_Builder_Advanced_Logger.php',
    'PDF_Builder_Security_Validator.php',
    'PDF_Builder_Error_Handler.php',
    'PDF_Builder_Task_Scheduler.php',
    'PDF_Builder_Notification_Manager.php',
    'PDF_Builder_Diagnostic_Tool.php',
    'PDF_Builder_Analytics_Manager.php',
    'PDF_Builder_Backup_Recovery_System.php',
    'PDF_Builder_Security_Monitor.php',
    'PDF_Builder_Update_Manager.php',
    'PDF_Builder_Reporting_System.php',
    'PDF_Builder_Test_Suite.php'
];

$missing_files = [];
foreach ($core_files as $file) {
    $file_path = "../plugin/src/Core/$file";
    if (!file_exists($file_path)) {
        $missing_files[] = $file;
        echo "   ❌ Fichier manquant: $file\n";
    }
}

if (empty($missing_files)) {
    echo "   ✅ Tous les fichiers Core existent\n";
} else {
    $errors[] = "Fichiers Core manquants: " . implode(', ', $missing_files);
}

// Test 5: Test des fonctions globales
echo "\n5. Test des fonctions globales...\n";
$global_functions = [
    'pdf_builder_config',
    'pdf_builder_set_config',
    'pdf_builder_save_config',
    'pdf_builder_get_system_info',
    'pdf_builder_health_check'
];

foreach ($global_functions as $func) {
    if (function_exists($func)) {
        echo "   ✅ $func existe\n";
    } else {
        echo "   ❌ $func n'existe pas\n";
        $warnings[] = "Fonction globale manquante: $func";
    }
}

// Test 6: Vérification des constantes
echo "\n6. Vérification des constantes...\n";
$required_constants = [
    'PDF_BUILDER_PLUGIN_FILE',
    'PDF_BUILDER_PLUGIN_DIR'
];

foreach ($required_constants as $const) {
    if (defined($const)) {
        echo "   ✅ $const définie\n";
    } else {
        echo "   ❌ $const non définie\n";
        $errors[] = "Constante manquante: $const";
    }
}

// Test 7: Test des utilitaires
echo "\n7. Test des utilitaires...\n";
try {
    if (function_exists('pdf_builder_ensure_onboarding_manager')) {
        $result = pdf_builder_ensure_onboarding_manager();
        echo "   ✅ pdf_builder_ensure_onboarding_manager fonctionne\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur pdf_builder_ensure_onboarding_manager: " . $e->getMessage() . "\n";
    $errors[] = "Fonction utilitaire: " . $e->getMessage();
}

// Résumé
echo "\n=== RÉSULTATS DU DIAGNOSTIC ===\n";
echo "Erreurs critiques: " . count($errors) . "\n";
echo "Avertissements: " . count($warnings) . "\n";

if (!empty($errors)) {
    echo "\n❌ ERREURS CRITIQUES:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️ AVERTISSEMENTS:\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
}

if (empty($errors)) {
    echo "\n✅ AUCUNE ERREUR CRITIQUE DÉTECTÉE\n";
    echo "Le problème pourrait être dans l'initialisation WordPress ou dans les hooks.\n";
} else {
    echo "\n🔍 ERREURS DÉTECTÉES - Nécessite correction\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";