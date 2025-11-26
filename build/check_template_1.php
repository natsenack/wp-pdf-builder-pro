<?php

/**
 * Diagnostic du template ID 1
 * Vérifie si le template existe et si ses données JSON sont valides
 */

echo "🔍 Diagnostic du Template ID 1\n";
echo "==============================\n\n";

// Inclure WordPress
require_once '../../../wp-load.php';

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier si le template existe
$template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", 1), ARRAY_A);

if (!$template) {
    echo "❌ Template ID 1 n'existe pas dans la base de données\n";
    exit;
}

echo "✅ Template ID 1 trouvé\n";
echo "Nom: " . $template['name'] . "\n";
echo "Date de création: " . $template['created_at'] . "\n";
echo "Date de modification: " . $template['updated_at'] . "\n\n";

// Vérifier les données JSON
echo "Test de décodage JSON...\n";
$template_data = $template['template_data'];

$decoded = json_decode($template_data, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide - décodage direct réussi\n";
    echo "Clés du template: " . implode(', ', array_keys($decoded)) . "\n";
    if (isset($decoded['elements'])) {
        echo "Nombre d'éléments: " . count($decoded['elements']) . "\n";
    }
} else {
    echo "❌ JSON invalide - Erreur: " . json_last_error_msg() . "\n";

    // Tester le nettoyage JSON
    echo "\nTest du nettoyage JSON...\n";
    $data_utils = new \PDF_Builder\Admin\Data\DataUtils(new \PDF_Builder\Admin\PdfBuilderAdmin());

    $clean_json = $data_utils->cleanJsonData($template_data);
    if ($clean_json !== $template_data) {
        echo "✅ Nettoyage normal appliqué\n";
        $decoded_clean = json_decode($clean_json, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON valide après nettoyage normal\n";
        } else {
            echo "❌ JSON toujours invalide après nettoyage normal\n";
        }
    } else {
        echo "ℹ️ Aucun nettoyage nécessaire\n";
    }

    // Tester le nettoyage agressif
    $aggressive_clean = $data_utils->aggressiveJsonClean($template_data);
    if ($aggressive_clean !== $template_data) {
        echo "✅ Nettoyage agressif appliqué\n";
        $decoded_aggressive = json_decode($aggressive_clean, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON valide après nettoyage agressif\n";
        } else {
            echo "❌ JSON toujours invalide après nettoyage agressif\n";
        }
    } else {
        echo "ℹ️ Aucun nettoyage agressif nécessaire\n";
    }
}

echo "\nTest terminé.\n";