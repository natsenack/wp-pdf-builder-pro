<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Diagnostic des bordures des tableaux dans les templates
 * PDF Builder Pro - Table Borders Diagnostic
 */

// Inclure le gestionnaire d'éléments
require_once __DIR__ . '/includes/managers/PDF_Builder_Canvas_Elements_Manager.php';

function pdf_builder_diagnose_table_borders() {
    global $wpdb;

    echo "<h2>🔍 Diagnostic des bordures des tableaux</h2>";

    // Récupérer tous les templates
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $templates = $wpdb->get_results("SELECT id, template_name, template_data FROM $table_templates", ARRAY_A);

    if (empty($templates)) {
        echo "<p>Aucun template trouvé.</p>";
        return;
    }

    echo "<p><strong>" . count($templates) . " templates trouvés.</strong></p>";

    $issues_found = 0;
    $total_table_elements = 0;

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

        $table_elements = array_filter($template_data['elements'], function($element) {
            return isset($element['type']) && $element['type'] === 'product_table';
        });

        if (empty($table_elements)) {
            echo "<p>Aucun tableau dans ce template.</p>";
            continue;
        }

        echo "<p><strong>" . count($table_elements) . " tableaux trouvés:</strong></p>";

        foreach ($table_elements as $index => $element) {
            $total_table_elements++;
            echo "<div style='margin-left: 20px; padding: 10px; border: 1px solid #ddd; margin-bottom: 10px;'>";
            echo "<h4>Tableau #" . ($index + 1) . " (ID: {$element['id']})</h4>";

            // Vérifier showBorders
            $show_borders = $element['showBorders'] ?? 'NON_DEFINI';
            $border_status = '';

            if ($show_borders === 'NON_DEFINI') {
                $border_status = "<span style='color: orange;'>⚠️ showBorders non défini (devrait être true par défaut)</span>";
                $issues_found++;
            } elseif ($show_borders === false) {
                $border_status = "<span style='color: red;'>❌ showBorders = false</span>";
                $issues_found++;
            } elseif ($show_borders === true) {
                $border_status = "<span style='color: green;'>✅ showBorders = true</span>";
            } else {
                $border_status = "<span style='color: orange;'>⚠️ showBorders = " . json_encode($show_borders) . "</span>";
            }

            echo "<p>{$border_status}</p>";

            // Vérifier les autres propriétés de bordure
            $border_width = $element['borderWidth'] ?? 'NON_DEFINI';
            $border_color = $element['borderColor'] ?? 'NON_DEFINI';

            echo "<p><strong>Propriétés de bordure générales:</strong></p>";
            echo "<ul>";
            echo "<li>borderWidth: " . ($border_width === 'NON_DEFINI' ? '<span style="color: orange;">non défini</span>' : $border_width) . "</li>";
            echo "<li>borderColor: " . ($border_color === 'NON_DEFINI' ? '<span style="color: orange;">non défini</span>' : $border_color) . "</li>";
            echo "<li>borderStyle: " . ($element['borderStyle'] ?? 'non défini') . "</li>";
            echo "<li>borderRadius: " . ($element['borderRadius'] ?? 'non défini') . "</li>";
            echo "</ul>";

            // Vérifier les propriétés spécifiques au tableau
            echo "<p><strong>Propriétés spécifiques au tableau:</strong></p>";
            echo "<ul>";
            echo "<li>showHeaders: " . ($element['showHeaders'] ?? 'non défini') . "</li>";
            echo "<li>tableStyle: " . ($element['tableStyle'] ?? 'non défini') . "</li>";
            echo "<li>columns: " . (isset($element['columns']) ? json_encode($element['columns']) : 'non défini') . "</li>";
            echo "</ul>";

            echo "</div>";
        }
    }

    echo "<h3>📊 Résumé du diagnostic</h3>";
    echo "<ul>";
    echo "<li><strong>Total de tableaux analysés:</strong> {$total_table_elements}</li>";
    echo "<li><strong>Problèmes détectés:</strong> {$issues_found}</li>";
    echo "</ul>";

    if ($issues_found > 0) {
        echo "<h3>🔧 Réparation automatique</h3>";
        echo "<p>Les tableaux avec showBorders non défini ou false vont être corrigés...</p>";

        $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
        $repaired_count = 0;

        foreach ($templates as $template) {
            $template_data = json_decode($template['template_data'], true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            $modified = false;

            foreach ($template_data['elements'] as &$element) {
                if (isset($element['type']) && $element['type'] === 'product_table') {
                    // Corriger showBorders si nécessaire
                    if (!isset($element['showBorders']) || $element['showBorders'] === false) {
                        $element['showBorders'] = true;
                        $modified = true;
                        $repaired_count++;
                        echo "<p>✅ Réparé: {$element['id']} - showBorders défini à true</p>";
                    }
                }
            }

            if ($modified) {
                // Sauvegarder le template modifié
                $updated_data = wp_json_encode($template_data);
                $wpdb->update(
                    $table_templates,
                    ['template_data' => $updated_data],
                    ['id' => $template['id']],
                    ['%s'],
                    ['%d']
                );
            }
        }

        echo "<p><strong>{$repaired_count} tableaux réparés.</strong></p>";
    } else {
        echo "<p style='color: green;'>✅ Aucun problème détecté !</p>";
    }
}

// Exécuter le diagnostic si ce fichier est appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    pdf_builder_diagnose_table_borders();
}