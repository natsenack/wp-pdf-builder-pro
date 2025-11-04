<?php
// Test direct pour debug

// Définir les constantes manuellement
if (!defined('ABSPATH')) {
    define('ABSPATH', 'd:/wp-pdf-builder-pro/plugin/');
}
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', 'd:/wp-pdf-builder-pro/plugin/pdf-builder-pro.php');
}
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', 'd:/wp-pdf-builder-pro/plugin/');
}
if (!defined('PDF_BUILDER_PLUGIN_URL')) {
    define('PDF_BUILDER_PLUGIN_URL', 'http://localhost/wp-pdf-builder-pro/plugin/');
}

// Charger la classe
require_once 'd:/wp-pdf-builder-pro/plugin/src/Managers/PDF_Builder_Template_Manager.php';

// Créer l'instance
$manager = new PDF_Builder_Template_Manager(null);

// Tester la méthode
$templates = $manager->get_builtin_templates();

echo "=== TEST DIRECT ===" . PHP_EOL;
echo "PDF_BUILDER_PLUGIN_DIR: " . PDF_BUILDER_PLUGIN_DIR . PHP_EOL;
echo "PDF_BUILDER_PLUGIN_URL: " . PDF_BUILDER_PLUGIN_URL . PHP_EOL;
echo "Templates found: " . count($templates) . PHP_EOL;
echo PHP_EOL;

if (count($templates) > 0) {
    echo "Templates:" . PHP_EOL;
    foreach ($templates as $t) {
        echo "  - " . $t['name'] . " (id: " . $t['id'] . ")" . PHP_EOL;
    }
} else {
    echo "AUCUN TEMPLATE!" . PHP_EOL;
    echo "Vérifions le chemin:" . PHP_EOL;
    $builtin_dir = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/';
    echo "  builtin_dir: " . $builtin_dir . PHP_EOL;
    echo "  exists: " . (is_dir($builtin_dir) ? 'YES' : 'NO') . PHP_EOL;
    if (is_dir($builtin_dir)) {
        $files = glob($builtin_dir . '*.json');
        echo "  files: " . count($files) . PHP_EOL;
        foreach ($files as $f) {
            echo "    - " . basename($f) . PHP_EOL;
        }
    }
}
?>
