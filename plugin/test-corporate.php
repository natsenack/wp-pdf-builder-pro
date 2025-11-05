<?php
/**
 * Script de test pour déboguer le template corporate
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once 'core/constants.php';
require_once 'src/Managers/PDF_Builder_Template_Manager.php';

echo "<h1>Test Template Corporate</h1>";

// Créer une instance du Template Manager
$template_manager = new PDF_Builder_Template_Manager(null);

// Tester la récupération des templates
$templates = $template_manager->get_builtin_templates();

echo "<h2>Templates trouvés: " . count($templates) . "</h2>";
echo "<ul>";
foreach ($templates as $template) {
    echo "<li>" . $template['id'] . " - " . $template['name'] . "</li>";
}
echo "</ul>";

// Tester spécifiquement corporate
echo "<h2>Test Corporate</h2>";
$corporate_file = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/corporate.json';

if (file_exists($corporate_file)) {
    echo "<p>✅ Fichier corporate.json existe</p>";

    $content = file_get_contents($corporate_file);
    $data = json_decode($content, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<p>✅ JSON valide</p>";

        // Tester la validation
        $errors = $template_manager->validate_template_structure($data);

        if (empty($errors)) {
            echo "<p>✅ Structure valide</p>";
        } else {
            echo "<p>❌ Erreurs de validation:</p>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p>❌ Erreur JSON: " . json_last_error_msg() . "</p>";
    }
} else {
    echo "<p>❌ Fichier corporate.json n'existe pas</p>";
}