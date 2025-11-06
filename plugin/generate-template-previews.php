<?php
/**
 * Script pour générer les previews des templates builtin
 * Usage: php generate-template-previews.php
 */

// Permettre l'exécution en ligne de commande
if (php_sapi_name() !== 'cli') {
    die('Accès direct interdit');
}

// Simuler un environnement WordPress minimal
define('ABSPATH', __DIR__ . '/');
define('WP_PLUGIN_DIR', dirname(__DIR__));
define('PDF_BUILDER_PLUGIN_DIR', __DIR__ . '/');
define('PDF_BUILDER_PLUGIN_URL', 'file://' . __DIR__ . '/');

// Charger les classes nécessaires
require_once 'src/Managers/PDF_Builder_Template_Manager.php';
require_once 'src/Managers/PDF_Builder_Preview_Generator.php';

// Classes mock pour éviter les dépendances WordPress
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        return mkdir($dir, 0755, true);
    }
}

if (!function_exists('wp_schedule_single_event')) {
    function wp_schedule_single_event($timestamp, $hook, $args = array()) {
        // Ne rien faire en mode CLI
    }
}

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook, $args = array()) {
        return false;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'file://' . dirname($file) . '/';
    }
}

// Charger le Template Manager
$template_manager = new PDF_Builder_Template_Manager();

// Récupérer tous les templates builtin
$templates = $template_manager->get_builtin_templates();

echo "Génération des previews pour " . count($templates) . " templates...\n\n";

foreach ($templates as $template) {
    $template_id = $template['id'];
    $template_file = plugin_dir_path(__FILE__) . 'templates/builtin/' . $template_id . '.json';

    echo "Génération preview pour: {$template_id}\n";

    if (file_exists($template_file)) {
        $result = $template_manager->generate_template_preview($template_id, $template_file);
        if ($result) {
            echo "  ✅ Preview générée: {$result}\n";
        } else {
            echo "  ❌ Échec génération preview\n";
        }
    } else {
        echo "  ❌ Fichier template introuvable: {$template_file}\n";
    }

    echo "\n";
}

echo "Terminé.\n";