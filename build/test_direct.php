<?php
/**
 * Test direct d'inclusion du plugin PDF Builder Pro
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
        // Simuler quelques options de base
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

if (!function_exists('defined')) {
    function defined($constant) {
        return constant($constant) !== null;
    }
}

echo "=== TEST DIRECT D'INCLUSION DU PLUGIN ===\n";

try {
    // Tester l'inclusion du fichier principal
    echo "1. Inclusion du fichier principal...\n";
    require_once '../plugin/pdf-builder-pro.php';
    echo "   ✅ Fichier principal inclus avec succès\n";

    // Tester si les classes principales existent
    echo "2. Vérification des classes principales...\n";

    if (class_exists('PDF_Builder_Option_Config_Manager')) {
        echo "   ✅ PDF_Builder_Option_Config_Manager existe\n";
    } else {
        echo "   ❌ PDF_Builder_Option_Config_Manager n'existe pas\n";
    }

    if (class_exists('PDF_Builder_Config_Manager')) {
        echo "   ✅ PDF_Builder_Config_Manager existe\n";
    } else {
        echo "   ❌ PDF_Builder_Config_Manager n'existe pas\n";
    }

    // Tester l'initialisation basique
    echo "3. Test d'initialisation basique...\n";

    // Essayer d'instancier PDF_Builder_Option_Config_Manager
    if (class_exists('PDF_Builder_Option_Config_Manager')) {
        $config_manager = new PDF_Builder_Option_Config_Manager();
        echo "   ✅ PDF_Builder_Option_Config_Manager instancié\n";
    }

    echo "\n=== TEST TERMINÉ AVEC SUCCÈS ===\n";

} catch (Exception $e) {
    echo "❌ ERREUR FATALE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "❌ ERREUR CRITIQUE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}