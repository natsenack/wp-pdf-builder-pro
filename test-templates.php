<?php
// Test script pour vérifier les templates prédéfinis
require_once __DIR__ . '/plugin/src/utilities/PDF_Builder_Onboarding_Manager.php';

try {
    $manager = new PDF_Builder_Onboarding_Manager();
    $templates = $manager->get_predefined_templates();

    echo "=== TEST TEMPLATES PRÉDÉFINIS ===\n";
    echo "Nombre de templates trouvés: " . count($templates) . "\n\n";

    foreach ($templates as $template) {
        echo "Template: {$template['name']} (ID: {$template['id']})\n";
        echo "Description: {$template['description']}\n";
        echo "Icône: {$template['icon']}\n";
        echo "Catégorie: {$template['category']}\n";
        echo "---\n";
    }

    echo "✅ Test réussi !\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>