<?php
/**
 * Test direct de la validation du template corporate
 */

require_once '../../../wp-load.php';

echo "<h1>Test de validation du template Corporate</h1>";

// Charger le fichier corporate
$corporate_file = plugin_dir_path(__FILE__) . 'templates/builtin/corporate.json';

if (!file_exists($corporate_file)) {
    echo "<p style='color:red'>❌ Fichier corporate.json non trouvé: $corporate_file</p>";
    exit;
}

$content = file_get_contents($corporate_file);
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<p style='color:red'>❌ Erreur JSON: " . json_last_error_msg() . "</p>";
    exit;
}

echo "<p>✅ JSON chargé avec " . count($data['elements']) . " éléments</p>";

// Tester la validation
require_once 'src/Managers/PDF_Builder_Template_Manager.php';

$template_manager = new PDF_Builder_Template_Manager(null);

echo "<h2>Test de validation:</h2>";

$errors = $template_manager->validate_template_structure($data);

if (empty($errors)) {
    echo "<p style='color:green'>✅ Validation réussie - aucune erreur</p>";
} else {
    echo "<p style='color:red'>❌ Erreurs de validation:</p>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

// Test de chargement direct
echo "<h2>Test de chargement dans get_builtin_templates:</h2>";

$builtin_dir = plugin_dir_path(__FILE__) . 'templates/builtin/';
$files = glob($builtin_dir . '*.json');

echo "<p>Fichiers trouvés dans $builtin_dir:</p>";
echo "<ul>";
foreach ($files as $file) {
    $filename = basename($file, '.json');
    echo "<li>$filename.json</li>";
}
echo "</ul>";

$corporate_found = false;
foreach ($files as $file) {
    $filename = basename($file, '.json');
    if ($filename === 'corporate') {
        $corporate_found = true;
        echo "<p style='color:blue'>📄 Traitement de corporate.json...</p>";

        $content = file_get_contents($file);
        $template_data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color:red'>❌ Erreur JSON pour corporate</p>";
            continue;
        }

        $validation_errors = $template_manager->validate_template_structure($template_data);

        if (!empty($validation_errors)) {
            echo "<p style='color:red'>❌ Validation échouée pour corporate:</p>";
            echo "<ul>";
            foreach ($validation_errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:green'>✅ Corporate passe la validation</p>";
        }
    }
}

if (!$corporate_found) {
    echo "<p style='color:red'>❌ corporate.json non trouvé dans la liste des fichiers</p>";
}