<?php
// Script de diagnostic pour vérifier les templates PDF
require_once '../../../wp-load.php'; // Ajuster le chemin selon l'emplacement

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier tous les templates
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates", ARRAY_A);

echo "=== DIAGNOSTIC TEMPLATES PDF ===\n\n";
echo "Nombre de templates trouvés: " . count($templates) . "\n\n";

foreach($templates as $template) {
    echo "Template ID: {$template['id']}\n";
    echo "Nom: {$template['name']}\n";

    if (!empty($template['template_data'])) {
        $data = json_decode($template['template_data'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "JSON valide: ✓\n";

            if (isset($data['elements'])) {
                echo "Éléments trouvés: " . count($data['elements']) . "\n";
                if (count($data['elements']) > 0) {
                    echo "Premier élément: " . json_encode($data['elements'][0], JSON_PRETTY_PRINT) . "\n";
                }
            } else {
                echo "❌ Aucun élément trouvé dans le template\n";
            }

            if (isset($data['canvasWidth'])) {
                echo "Canvas: {$data['canvasWidth']}x{$data['canvasHeight']}\n";
            }
        } else {
            echo "❌ JSON invalide: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "❌ Aucune donnée template_data\n";
    }

    echo "---\n\n";
}

// Test spécifique du template ID 1
echo "=== TEST TEMPLATE ID 1 ===\n";
$template_1 = $wpdb->get_row(
    $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", 1),
    ARRAY_A
);

if ($template_1) {
    echo "Template ID 1 trouvé ✓\n";
    if (!empty($template_1['template_data'])) {
        $data = json_decode($template_1['template_data'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "JSON valide ✓\n";
            if (isset($data['elements']) && is_array($data['elements'])) {
                echo "Éléments: " . count($data['elements']) . " trouvés\n";
                foreach($data['elements'] as $i => $element) {
                    echo "Élément $i: type={$element['type']}, x={$element['x']}, y={$element['y']}\n";
                }
            } else {
                echo "❌ Aucun élément dans le template\n";
            }
        } else {
            echo "❌ JSON invalide\n";
        }
    } else {
        echo "❌ template_data vide\n";
    }
} else {
    echo "❌ Template ID 1 non trouvé\n";
}
?>