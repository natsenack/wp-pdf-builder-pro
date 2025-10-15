<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Réparation des bordures des tableaux
 * Force showBorders = true pour tous les tableaux existants
 */

// Inclure le gestionnaire d'éléments
require_once __DIR__ . '/includes/managers/PDF_Builder_Canvas_Elements_Manager.php';

function pdf_builder_repair_table_borders() {
    global $wpdb;

    echo "<h2>🔧 Réparation des bordures des tableaux</h2>";

    // Récupérer tous les templates
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $templates = $wpdb->get_results("SELECT id, template_name, template_data FROM $table_templates", ARRAY_A);

    if (empty($templates)) {
        echo "<p>Aucun template trouvé.</p>";
        return;
    }

    $repaired_count = 0;
    $total_tables = 0;

    foreach ($templates as $template) {
        echo "<h3>📄 Template: {$template['template_name']} (ID: {$template['id']})</h3>";

        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'>❌ Erreur JSON dans le template</p>";
            continue;
        }

        if (!isset($template_data['elements']) || !is_array($template_data['elements'])) {
            echo "<p>Aucun élément dans ce template.</p>";
            continue;
        }

        $modified = false;

        foreach ($template_data['elements'] as &$element) {
            if (isset($element['type']) && $element['type'] === 'product_table') {
                $total_tables++;

                $old_value = $element['showBorders'] ?? 'NON_DEFINI';
                $element['showBorders'] = true;

                if ($old_value !== true) {
                    $modified = true;
                    $repaired_count++;
                    echo "<p>✅ Réparé: {$element['id']} - showBorders: {$old_value} → true</p>";
                } else {
                    echo "<p>ℹ️ Déjà correct: {$element['id']} - showBorders: true</p>";
                }
            }
        }

        if ($modified) {
            // Sauvegarder le template modifié
            $updated_data = wp_json_encode($template_data);
            if ($updated_data !== false) {
                $result = $wpdb->update(
                    $table_templates,
                    ['template_data' => $updated_data],
                    ['id' => $template['id']],
                    ['%s'],
                    ['%d']
                );

                if ($result !== false) {
                    echo "<p style='color: green;'>💾 Template sauvegardé avec succès</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erreur lors de la sauvegarde du template</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Erreur lors de l'encodage JSON</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Aucun changement nécessaire</p>";
        }

        echo "<hr>";
    }

    echo "<h3>📊 Résumé de la réparation</h3>";
    echo "<ul>";
    echo "<li><strong>Total de tableaux trouvés:</strong> {$total_tables}</li>";
    echo "<li><strong>Tableaux réparés:</strong> {$repaired_count}</li>";
    echo "</ul>";

    if ($repaired_count > 0) {
        echo "<p style='color: green; font-weight: bold;'>✅ Réparation terminée ! Les bordures des tableaux devraient maintenant s'afficher.</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Tous les tableaux étaient déjà correctement configurés.</p>";
    }
}

// Exécuter la réparation si ce fichier est appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    pdf_builder_repair_table_borders();
}