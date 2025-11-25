<?php
/**
 * Script pour mettre à jour les noms des templates existants
 */

// Simuler un environnement WordPress
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');

// Charger WordPress
require_once ABSPATH . 'wp-load.php';

echo "=== MISE À JOUR DES NOMS DE TEMPLATES ===\n\n";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Récupérer tous les templates
$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates", ARRAY_A);

echo "Nombre de templates trouvés: " . count($templates) . "\n\n";

foreach ($templates as $template) {
    $template_id = $template['id'];
    $current_name = $template['name'];
    $template_data = json_decode($template['template_data'], true);

    echo "Template ID $template_id:\n";
    echo "  - Nom actuel: '$current_name'\n";

    // Vérifier si le nom est défini dans les données JSON
    $json_name = isset($template_data['name']) ? $template_data['name'] : null;
    echo "  - Nom dans JSON: '" . ($json_name ?: 'non défini') . "'\n";

    // Générer un nouveau nom si nécessaire
    $new_name = $current_name;
    if (empty($current_name) || $current_name === 'Template ' . $template_id) {
        if (!empty($json_name)) {
            $new_name = $json_name;
        } else {
            // Essayer de deviner le type de template depuis les éléments
            if (isset($template_data['elements']) && is_array($template_data['elements'])) {
                $has_invoice_elements = false;
                $has_quote_elements = false;

                foreach ($template_data['elements'] as $element) {
                    if (isset($element['type'])) {
                        if (strpos(strtolower($element['type']), 'invoice') !== false || strpos(strtolower($element['content'] ?? ''), 'facture') !== false) {
                            $has_invoice_elements = true;
                        }
                        if (strpos(strtolower($element['type']), 'quote') !== false || strpos(strtolower($element['content'] ?? ''), 'devis') !== false) {
                            $has_quote_elements = true;
                        }
                    }
                }

                if ($has_invoice_elements) {
                    $new_name = 'Facture ' . $template_id;
                } elseif ($has_quote_elements) {
                    $new_name = 'Devis ' . $template_id;
                } else {
                    $new_name = 'Template ' . $template_id;
                }
            } else {
                $new_name = 'Template ' . $template_id;
            }
        }
    }

    // Mettre à jour le nom dans la DB si nécessaire
    if ($new_name !== $current_name) {
        $wpdb->update(
            $table_templates,
            ['name' => $new_name],
            ['id' => $template_id],
            ['%s'],
            ['%d']
        );
        echo "  - ✅ Nom mis à jour: '$new_name'\n";
    } else {
        echo "  - ℹ️ Nom déjà correct\n";
    }

    // S'assurer que le nom est aussi dans les données JSON
    if (!isset($template_data['name']) || $template_data['name'] !== $new_name) {
        $template_data['name'] = $new_name;
        $updated_json = wp_json_encode($template_data);

        $wpdb->update(
            $table_templates,
            ['template_data' => $updated_json],
            ['id' => $template_id],
            ['%s'],
            ['%d']
        );
        echo "  - ✅ Nom ajouté dans JSON\n";
    }

    echo "\n";
}

echo "=== MISE À JOUR TERMINÉE ===\n";
echo "Tous les templates ont été vérifiés et mis à jour si nécessaire.\n";