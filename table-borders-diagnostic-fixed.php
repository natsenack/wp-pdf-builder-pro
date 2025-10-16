<?php
/**
 * Diagnostic et réparation améliorés des bordures de tableau
 * PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🔧 Diagnostic Bordures de Tableau - Version Améliorée</h1>";
echo "<p>Ce script analyse et corrige automatiquement les problèmes de bordures dans les tableaux PDF.</p>";

// Fonction pour analyser un tableau
function analyzeTableBorders($element) {
    $issues = [];
    $fixes = [];

    // Vérifier la structure de base
    if (!isset($element['type']) || $element['type'] !== 'product_table') {
        return ['error' => 'Not a product table'];
    }

    // Vérifier les propriétés de bordure
    $borderProps = ['showHeaders', 'showBorders', 'tableStyle'];
    foreach ($borderProps as $prop) {
        if (!isset($element[$prop])) {
            $issues[] = "Propriété manquante: $prop";
            $fixes[$prop] = getDefaultTableProperty($prop);
        }
    }

    // Vérifier la cohérence des styles
    if (isset($element['tableStyle'])) {
        $styleIssues = validateTableStyle($element['tableStyle'], $element);
        $issues = array_merge($issues, $styleIssues['issues']);
        $fixes = array_merge($fixes, $styleIssues['fixes']);
    }

    // Vérifier les colonnes
    if (isset($element['columns']) && is_array($element['columns'])) {
        foreach ($element['columns'] as $colKey => $colValue) {
            if (!is_bool($colValue)) {
                $issues[] = "Colonne $colKey: valeur non booléenne ($colValue)";
                $fixes["columns.$colKey"] = (bool)$colValue;
            }
        }
    } else {
        $issues[] = "Propriété columns manquante ou invalide";
        $fixes['columns'] = [
            'name' => true,
            'sku' => false,
            'image' => true,
            'quantity' => true,
            'price' => true,
            'total' => true
        ];
    }

    return ['issues' => $issues, 'fixes' => $fixes];
}

// Fonction pour obtenir les valeurs par défaut
function getDefaultTableProperty($property) {
    $defaults = [
        'showHeaders' => true,
        'showBorders' => true,
        'tableStyle' => 'default',
        'borderWidth' => 1,
        'borderColor' => '#e2e8f0',
        'headerBg' => '#f8fafc',
        'rowBorder' => '#f1f5f9'
    ];

    return $defaults[$property] ?? null;
}

// Fonction pour valider le style de tableau
function validateTableStyle($style, $element) {
    $issues = [];
    $fixes = [];

    $validStyles = ['default', 'classic', 'minimal', 'bold'];
    if (!in_array($style, $validStyles)) {
        $issues[] = "Style de tableau invalide: $style";
        $fixes['tableStyle'] = 'default';
    }

    // Vérifier les propriétés spécifiques au style
    $styleSpecificProps = [
        'default' => ['headerBg' => '#f8fafc', 'borderWidth' => 1],
        'classic' => ['headerBg' => '#1e293b', 'borderWidth' => 1.5],
        'minimal' => ['headerBg' => '#ffffff', 'borderWidth' => 0.5],
        'bold' => ['headerBg' => '#000000', 'borderWidth' => 2]
    ];

    if (isset($styleSpecificProps[$style])) {
        foreach ($styleSpecificProps[$style] as $prop => $expectedValue) {
            if (!isset($element[$prop]) || $element[$prop] !== $expectedValue) {
                $fixes[$prop] = $expectedValue;
            }
        }
    }

    return ['issues' => $issues, 'fixes' => $fixes];
}

// Fonction pour appliquer les corrections
function applyTableFixes($element, $fixes) {
    $fixedElement = $element;

    foreach ($fixes as $key => $value) {
        if (strpos($key, '.') !== false) {
            // Propriété imbriquée (ex: columns.name)
            $parts = explode('.', $key);
            $current = &$fixedElement;
            foreach ($parts as $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            $current = $value;
        } else {
            // Propriété simple
            $fixedElement[$key] = $value;
        }
    }

    return $fixedElement;
}

// Analyser tous les templates
echo "<h2>1. Analyse des Templates</h2>";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$templates = $wpdb->get_results("SELECT id, template_name, template_data FROM $table_templates", ARRAY_A);

if (empty($templates)) {
    echo "<p>⚠️ Aucun template trouvé dans la base de données.</p>";
} else {
    echo "<p>📊 Analyse de " . count($templates) . " templates...</p>";

    $totalIssues = 0;
    $fixableIssues = 0;
    $templatesWithIssues = [];

    foreach ($templates as $template) {
        echo "<h3>Template: {$template['template_name']} (ID: {$template['id']})</h3>";

        $templateData = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "<p style='color: red;'>❌ Erreur JSON dans le template: " . json_last_error_msg() . "</p>";
            continue;
        }

        if (!isset($templateData['elements']) || !is_array($templateData['elements'])) {
            echo "<p>⚠️ Template sans éléments valides</p>";
            continue;
        }

        $tablesFound = 0;
        $tablesWithIssues = 0;

        foreach ($templateData['elements'] as $element) {
            if ($element['type'] === 'product_table') {
                $tablesFound++;
                $analysis = analyzeTableBorders($element);

                if (!empty($analysis['issues'])) {
                    $tablesWithIssues++;
                    $totalIssues += count($analysis['issues']);
                    $fixableIssues += count($analysis['fixes']);

                    echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; background: #fff3cd;'>";
                    echo "<h4>Tableau ID: {$element['id']}</h4>";
                    echo "<p><strong>Problèmes détectés:</strong></p>";
                    echo "<ul>";
                    foreach ($analysis['issues'] as $issue) {
                        echo "<li style='color: #856404;'>$issue</li>";
                    }
                    echo "</ul>";

                    if (!empty($analysis['fixes'])) {
                        echo "<p><strong>Corrections proposées:</strong></p>";
                        echo "<ul>";
                        foreach ($analysis['fixes'] as $key => $value) {
                            echo "<li style='color: #155724;'>$key → " . json_encode($value) . "</li>";
                        }
                        echo "</ul>";
                    }
                    echo "</div>";
                }
            }
        }

        if ($tablesFound > 0) {
            echo "<p>📋 Résumé: $tablesFound tableau(x) trouvé(s), $tablesWithIssues avec problèmes</p>";
        } else {
            echo "<p>📋 Aucun tableau trouvé dans ce template</p>";
        }

        if ($tablesWithIssues > 0) {
            $templatesWithIssues[] = $template['id'];
        }
    }

    // Résumé global
    echo "<h2>📊 Résumé Global</h2>";
    echo "<div style='border: 1px solid #ddd; padding: 15px; background: #f8f9fa;'>";
    echo "<p><strong>Templates analysés:</strong> " . count($templates) . "</p>";
    echo "<p><strong>Templates avec problèmes:</strong> " . count($templatesWithIssues) . "</p>";
    echo "<p><strong>Problèmes totaux:</strong> $totalIssues</p>";
    echo "<p><strong>Problèmes corrigables:</strong> $fixableIssues</p>";
    echo "</div>";

    // Bouton de réparation automatique
    if ($fixableIssues > 0) {
        echo "<h2>🔧 Réparation Automatique</h2>";
        echo "<p>Des problèmes corrigables ont été détectés. Voulez-vous les réparer automatiquement ?</p>";

        echo "<form method='post' style='margin: 20px 0;'>";
        echo "<input type='hidden' name='auto_fix_tables' value='1'>";
        echo "<button type='submit' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>";
        echo "🔧 Appliquer les corrections automatiquement";
        echo "</button>";
        echo "</form>";
    }
}

// Traitement de la réparation automatique
if (isset($_POST['auto_fix_tables']) && $_POST['auto_fix_tables'] === '1') {
    echo "<h2>⚙️ Application des Corrections</h2>";

    $correctionsApplied = 0;
    $errors = 0;

    foreach ($templates as $template) {
        $templateData = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) continue;

        $modified = false;

        foreach ($templateData['elements'] as &$element) {
            if ($element['type'] === 'product_table') {
                $analysis = analyzeTableBorders($element);

                if (!empty($analysis['fixes'])) {
                    $element = applyTableFixes($element, $analysis['fixes']);
                    $modified = true;
                    $correctionsApplied++;
                }
            }
        }

        if ($modified) {
            $updatedJson = wp_json_encode($templateData);
            if ($updatedJson !== false) {
                $result = $wpdb->update(
                    $table_templates,
                    ['template_data' => $updatedJson],
                    ['id' => $template['id']],
                    ['%s'],
                    ['%d']
                );

                if ($result === false) {
                    $errors++;
                    echo "<p style='color: red;'>❌ Erreur lors de la sauvegarde du template {$template['id']}</p>";
                } else {
                    echo "<p style='color: green;'>✅ Template {$template['template_name']} corrigé</p>";
                }
            } else {
                $errors++;
                echo "<p style='color: red;'>❌ Erreur d'encodage JSON pour le template {$template['id']}</p>";
            }
        }
    }

    echo "<h3>Résultats de la réparation</h3>";
    echo "<div style='border: 1px solid #ddd; padding: 15px; background: #f8f9fa;'>";
    echo "<p><strong>Corrections appliquées:</strong> $correctionsApplied</p>";
    echo "<p><strong>Erreurs:</strong> $errors</p>";
    if ($correctionsApplied > 0 && $errors === 0) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Toutes les corrections ont été appliquées avec succès !</p>";
    }
    echo "</div>";
}

echo "<hr><p><strong>Diagnostic terminé le " . date('d/m/Y à H:i:s') . "</strong></p>";
?>