<?php
/**
 * Script pour valider spécifiquement le template corporate
 */

require_once 'core/constants.php';
require_once 'src/Managers/PDF_Builder_Template_Manager.php';

echo "Validation du template Corporate\n";
echo "================================\n\n";

// Charger le fichier corporate
$corporate_file = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/corporate.json';

if (!file_exists($corporate_file)) {
    echo "❌ Fichier corporate.json non trouvé\n";
    exit(1);
}

$content = file_get_contents($corporate_file);
if ($content === false) {
    echo "❌ Impossible de lire le fichier\n";
    exit(1);
}

$data = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

echo "✅ JSON valide\n";
echo "📊 Nombre d'éléments: " . count($data['elements']) . "\n\n";

// Créer une instance du Template Manager pour utiliser sa validation
$template_manager = new PDF_Builder_Template_Manager(null);

// Tester la validation
echo "🔍 Validation de la structure...\n";
$errors = $template_manager->validate_template_structure($data);

if (empty($errors)) {
    echo "✅ Structure valide\n";
} else {
    echo "❌ Erreurs de validation:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

// Tester chaque élément individuellement
echo "\n🔍 Validation des éléments...\n";
$element_errors = [];
foreach ($data['elements'] as $index => $element) {
    $elem_errors = $template_manager->validate_template_element($element, $index);
    if (!empty($elem_errors)) {
        $element_errors[$index] = $elem_errors;
    }
}

if (empty($element_errors)) {
    echo "✅ Tous les éléments valides\n";
} else {
    echo "❌ Erreurs dans les éléments:\n";
    foreach ($element_errors as $index => $errors) {
        echo "   Élément $index (" . ($data['elements'][$index]['id'] ?? 'unknown') . "):\n";
        foreach ($errors as $error) {
            echo "     - $error\n";
        }
    }
}

echo "\n🏁 Validation terminée\n";