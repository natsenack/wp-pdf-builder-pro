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

// Tester un template spécifique
$template_file = 'd:/wp-pdf-builder-pro/plugin/templates/builtin/classic.json';

echo "=== TEST TEMPLATE CHARGEMENT ===\n";
echo "Fichier: $template_file\n";
echo "Existe: " . (file_exists($template_file) ? 'OUI' : 'NON') . "\n";

if (file_exists($template_file)) {
    $json_content = file_get_contents($template_file);
    echo "Contenu lu: " . (strlen($json_content) > 0 ? 'OUI' : 'NON') . " (" . strlen($json_content) . " caractères)\n";

    $template_data = json_decode($json_content, true);
    echo "JSON décodé: " . (json_last_error() === JSON_ERROR_NONE ? 'OUI' : 'NON - ' . json_last_error_msg()) . "\n";

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Structure: " . (is_array($template_data) ? 'ARRAY' : gettype($template_data)) . "\n";

        // Vérifier les clés requises
        $required_keys = ['elements', 'canvasWidth', 'canvasHeight', 'version'];
        $missing_keys = [];
        foreach ($required_keys as $key) {
            if (!isset($template_data[$key])) {
                $missing_keys[] = $key;
            }
        }
        echo "Clés manquantes: " . (empty($missing_keys) ? 'AUCUNE' : implode(', ', $missing_keys)) . "\n";

        if (empty($missing_keys)) {
            echo "Types:\n";
            echo "  elements: " . gettype($template_data['elements']) . "\n";
            echo "  canvasWidth: " . gettype($template_data['canvasWidth']) . "\n";
            echo "  canvasHeight: " . gettype($template_data['canvasHeight']) . "\n";
            echo "  version: " . gettype($template_data['version']) . "\n";

            // Tester la validation
            echo "Validation des types:\n";
            $errors = [];

            if (!is_array($template_data['elements'])) {
                $errors[] = "'elements' doit être un tableau";
            }

            if (!is_numeric($template_data['canvasWidth'])) {
                $errors[] = "'canvasWidth' doit être numérique";
            }

            if (!is_numeric($template_data['canvasHeight'])) {
                $errors[] = "'canvasHeight' doit être numérique";
            }

            if (!is_string($template_data['version'])) {
                $errors[] = "'version' doit être une chaîne";
            }

            echo "  Erreurs: " . (empty($errors) ? 'AUCUNE' : implode(', ', $errors)) . "\n";
        }
    }
}

// Tester la méthode complète
echo "\n=== TEST MÉTHODE COMPLÈTE ===\n";
$templates = $manager->get_builtin_templates();

echo "Templates trouvés: " . count($templates) . "\n";

foreach ($templates as $template) {
    echo "  - " . ($template['name'] ?? 'SANS NOM') . " (id: " . ($template['id'] ?? 'SANS ID') . ")\n";
}
?>
