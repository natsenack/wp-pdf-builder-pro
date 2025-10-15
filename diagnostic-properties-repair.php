<?php
/**
 * Diagnostic complet des propriétés d'éléments PDF Builder Pro
 * Identifie et corrige tous les problèmes de propriétés
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🔧 Diagnostic complet des propriétés d'éléments</h1>";
echo "<p>Cette page analyse minutieusement toutes les propriétés des éléments pour identifier et corriger les problèmes.</p>";

// Fonction pour analyser les propriétés d'un élément
function analyzeElementProperties($element, $index) {
    $issues = [];
    $warnings = [];
    $info = [];

    // Vérifier les propriétés de base
    $requiredProps = ['id', 'type', 'x', 'y', 'width', 'height'];
    foreach ($requiredProps as $prop) {
        if (!isset($element[$prop])) {
            $issues[] = "Propriété requise manquante: $prop";
        }
    }

    // Vérifier les types de propriétés
    $typeChecks = [
        'x' => 'number',
        'y' => 'number',
        'width' => 'number',
        'height' => 'number',
        'fontSize' => 'number',
        'opacity' => 'number',
        'lineHeight' => 'number',
        'letterSpacing' => 'number',
        'zIndex' => 'number',
        'borderWidth' => 'number',
        'borderRadius' => 'number',
        'rotation' => 'number'
    ];

    foreach ($typeChecks as $prop => $expectedType) {
        if (isset($element[$prop])) {
            $actualType = gettype($element[$prop]);
            if ($actualType !== $expectedType && $actualType !== 'string') {
                $issues[] = "Type incorrect pour $prop: attendu $expectedType, obtenu $actualType";
            }
            // Vérifier si c'est une string numérique
            if ($actualType === 'string' && !is_numeric($element[$prop])) {
                $issues[] = "Valeur non numérique pour $prop: '{$element[$prop]}'";
            }
        }
    }

    // Vérifier les couleurs
    $colorProps = ['color', 'backgroundColor', 'borderColor'];
    foreach ($colorProps as $prop) {
        if (isset($element[$prop]) && $element[$prop] !== 'transparent') {
            if (!preg_match('/^#[0-9A-Fa-f]{3,6}$/', $element[$prop])) {
                $warnings[] = "Format de couleur invalide pour $prop: '{$element[$prop]}'";
            }
        }
    }

    // Vérifier les propriétés spéciales selon le type
    if ($element['type'] === 'product_table') {
        $tableProps = ['showHeaders', 'showBorders', 'tableStyle', 'columns'];
        foreach ($tableProps as $prop) {
            if (!isset($element[$prop])) {
                $warnings[] = "Propriété de tableau manquante: $prop";
            }
        }
        if (isset($element['columns']) && !is_array($element['columns'])) {
            $issues[] = "Propriété columns doit être un tableau";
        }
    }

    // Vérifier les propriétés WooCommerce
    $wooProps = ['field', 'format', 'prefix', 'suffix', 'fallback'];
    $hasWooProps = false;
    foreach ($wooProps as $prop) {
        if (isset($element[$prop])) {
            $hasWooProps = true;
            break;
        }
    }
    if ($hasWooProps) {
        $info[] = "Élément WooCommerce détecté";
        foreach ($wooProps as $prop) {
            if (isset($element[$prop])) {
                $info[] = "Propriété WC: $prop = '{$element[$prop]}'";
            }
        }
    }

    // Vérifier les propriétés avancées de style
    $advancedProps = [
        'fontFamily', 'fontWeight', 'textAlign', 'opacity', 'lineHeight',
        'letterSpacing', 'textDecoration', 'textTransform', 'zIndex',
        'borderWidth', 'borderColor', 'borderStyle', 'borderRadius', 'padding'
    ];

    $advancedCount = 0;
    foreach ($advancedProps as $prop) {
        if (isset($element[$prop])) {
            $advancedCount++;
        }
    }

    if ($advancedCount > 0) {
        $info[] = "$advancedCount propriétés de style avancées détectées";
    }

    return [
        'issues' => $issues,
        'warnings' => $warnings,
        'info' => $info,
        'element' => $element
    ];
}

// Fonction pour corriger les propriétés d'un élément
function fixElementProperties($element) {
    $fixed = $element;
    $changes = [];

    // Corriger les types numériques
    $numericProps = [
        'x', 'y', 'width', 'height', 'fontSize', 'opacity',
        'lineHeight', 'letterSpacing', 'zIndex', 'borderWidth',
        'borderRadius', 'rotation'
    ];

    foreach ($numericProps as $prop) {
        if (isset($fixed[$prop])) {
            $original = $fixed[$prop];
            if (is_string($fixed[$prop]) && is_numeric($fixed[$prop])) {
                $fixed[$prop] = floatval($fixed[$prop]);
                $changes[] = "Converti $prop: '$original' → {$fixed[$prop]}";
            } elseif (!is_numeric($fixed[$prop])) {
                // Valeur par défaut selon la propriété
                $defaults = [
                    'x' => 0, 'y' => 0, 'width' => 100, 'height' => 50,
                    'fontSize' => 14, 'opacity' => 1, 'lineHeight' => 1.2,
                    'letterSpacing' => 0, 'zIndex' => 0, 'borderWidth' => 0,
                    'borderRadius' => 0, 'rotation' => 0
                ];
                $fixed[$prop] = $defaults[$prop] ?? 0;
                $changes[] = "Corrigé $prop: '$original' → {$fixed[$prop]}";
            }
        }
    }

    // Corriger les couleurs
    $colorProps = ['color', 'backgroundColor', 'borderColor'];
    foreach ($colorProps as $prop) {
        if (isset($fixed[$prop]) && $fixed[$prop] !== 'transparent') {
            $original = $fixed[$prop];
            if (!preg_match('/^#[0-9A-Fa-f]{3,6}$/', $fixed[$prop])) {
                // Essayer de normaliser
                $normalized = normalizeColor($fixed[$prop]);
                if ($normalized !== $fixed[$prop]) {
                    $fixed[$prop] = $normalized;
                    $changes[] = "Normalisé $prop: '$original' → '$normalized'";
                }
            }
        }
    }

    // Ajouter des propriétés manquantes pour les tableaux
    if ($fixed['type'] === 'product_table') {
        $defaults = [
            'showHeaders' => true,
            'showBorders' => true,
            'tableStyle' => 'default',
            'columns' => [
                'name' => true,
                'price' => true,
                'quantity' => true,
                'total' => true
            ]
        ];

        foreach ($defaults as $prop => $default) {
            if (!isset($fixed[$prop])) {
                $fixed[$prop] = $default;
                $changes[] = "Ajouté $prop: $default";
            }
        }
    }

    return [$fixed, $changes];
}

// Fonction pour normaliser une couleur
function normalizeColor($color) {
    if (!$color || $color === 'transparent') return $color;

    // Codes hex valides
    if (preg_match('/^#[0-9A-Fa-f]{3,6}$/', $color)) return $color;

    // Couleurs nommées communes
    $namedColors = [
        'black' => '#000000',
        'white' => '#ffffff',
        'red' => '#ff0000',
        'green' => '#008000',
        'blue' => '#0000ff',
        'gray' => '#808080',
        'grey' => '#808080'
    ];

    $lowerColor = strtolower($color);
    if (isset($namedColors[$lowerColor])) {
        return $namedColors[$lowerColor];
    }

    // Par défaut, retourner noir
    return '#000000';
}

// Analyser tous les templates
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates", ARRAY_A);

echo "<h2>📊 Analyse des templates existants</h2>";
echo "<p><strong>Templates trouvés:</strong> " . count($templates) . "</p>";

$totalElements = 0;
$totalIssues = 0;
$totalWarnings = 0;
$templatesToFix = [];

foreach ($templates as $template) {
    echo "<h3>Template: {$template['name']} (ID: {$template['id']})</h3>";

    $templateData = json_decode($template['template_data'], true);
    if (!$templateData || !isset($templateData['elements'])) {
        echo "<p style='color: red;'>❌ Données corrompues</p>";
        continue;
    }

    $elements = $templateData['elements'];
    echo "<p><strong>Éléments:</strong> " . count($elements) . "</p>";

    $templateIssues = 0;
    $templateWarnings = 0;

    foreach ($elements as $index => $element) {
        $totalElements++;
        $analysis = analyzeElementProperties($element, $index);

        if (!empty($analysis['issues'])) {
            $templateIssues += count($analysis['issues']);
            echo "<div style='background: #ffebee; border-left: 4px solid #f44336; padding: 10px; margin: 5px 0;'>";
            echo "<strong>Élément $index ({$element['type']}) - PROBLÈMES:</strong><br>";
            foreach ($analysis['issues'] as $issue) {
                echo "❌ $issue<br>";
            }
            echo "</div>";
        }

        if (!empty($analysis['warnings'])) {
            $templateWarnings += count($analysis['warnings']);
            echo "<div style='background: #fff3e0; border-left: 4px solid #ff9800; padding: 10px; margin: 5px 0;'>";
            echo "<strong>Élément $index ({$element['type']}) - AVERTISSEMENTS:</strong><br>";
            foreach ($analysis['warnings'] as $warning) {
                echo "⚠️ $warning<br>";
            }
            echo "</div>";
        }

        if (!empty($analysis['info'])) {
            echo "<div style='background: #e8f5e8; border-left: 4px solid #4caf50; padding: 10px; margin: 5px 0;'>";
            echo "<strong>Élément $index ({$element['type']}) - INFO:</strong><br>";
            foreach ($analysis['info'] as $info) {
                echo "ℹ️ $info<br>";
            }
            echo "</div>";
        }

        // Marquer pour correction si nécessaire
        if (!empty($analysis['issues'])) {
            if (!isset($templatesToFix[$template['id']])) {
                $templatesToFix[$template['id']] = [
                    'name' => $template['name'],
                    'elements' => $elements,
                    'fixes' => []
                ];
            }
            $templatesToFix[$template['id']]['fixes'][] = [
                'index' => $index,
                'issues' => $analysis['issues'],
                'element' => $element
            ];
        }
    }

    $totalIssues += $templateIssues;
    $totalWarnings += $templateWarnings;

    echo "<p><strong>Résumé template:</strong> $templateIssues problèmes, $templateWarnings avertissements</p>";
    echo "<hr>";
}

echo "<h2>📋 Résumé global</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>Total éléments analysés:</strong> $totalElements</p>";
echo "<p><strong>Total problèmes détectés:</strong> <span style='color: red;'>$totalIssues</span></p>";
echo "<p><strong>Total avertissements:</strong> <span style='color: orange;'>$totalWarnings</span></p>";
echo "</div>";

// Corriger les templates problématiques
if (!empty($templatesToFix)) {
    echo "<h2>🔧 Correction automatique des problèmes</h2>";

    foreach ($templatesToFix as $templateId => $templateInfo) {
        echo "<h3>Correction du template: {$templateInfo['name']}</h3>";

        $correctedElements = [];
        $totalFixes = 0;

        foreach ($templateInfo['elements'] as $index => $element) {
            $analysis = analyzeElementProperties($element, $index);

            if (!empty($analysis['issues'])) {
                list($fixedElement, $changes) = fixElementProperties($element);
                $correctedElements[] = $fixedElement;

                echo "<div style='background: #e8f5e8; border-left: 4px solid #4caf50; padding: 10px; margin: 5px 0;'>";
                echo "<strong>Élément $index corrigé:</strong><br>";
                foreach ($changes as $change) {
                    echo "✅ $change<br>";
                    $totalFixes++;
                }
                echo "</div>";
            } else {
                $correctedElements[] = $element;
            }
        }

        // Sauvegarder le template corrigé
        if ($totalFixes > 0) {
            $correctedTemplateData = [
                'elements' => $correctedElements,
                'canvasWidth' => 595,
                'canvasHeight' => 842,
                'version' => '1.0'
            ];

            $jsonData = json_encode($correctedTemplateData);
            if (json_last_error() === JSON_ERROR_NONE) {
                $updated = $wpdb->update(
                    $table_templates,
                    ['template_data' => $jsonData],
                    ['id' => $templateId]
                );

                if ($updated !== false) {
                    echo "<p style='color: green; font-weight: bold;'>✅ Template corrigé et sauvegardé avec succès !</p>";
                } else {
                    echo "<p style='color: red;'>❌ Erreur lors de la sauvegarde du template corrigé</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ Erreur JSON lors de la correction: " . json_last_error_msg() . "</p>";
            }
        }

        echo "<hr>";
    }
} else {
    echo "<h2>✅ Aucun problème détecté</h2>";
    echo "<p>Tous les templates sont en bon état !</p>";
}

// Test de création d'un élément propre
echo "<h2>🧪 Test de création d'élément propre</h2>";

$testElement = [
    'id' => 'test_element_' . time(),
    'type' => 'text',
    'x' => 100,
    'y' => 100,
    'width' => 200,
    'height' => 50,
    'content' => 'Texte de test avec toutes les propriétés avancées',
    'color' => '#333333',
    'fontSize' => 14,
    'fontFamily' => 'Arial',
    'fontWeight' => 'bold',
    'textAlign' => 'center',
    'backgroundColor' => 'transparent',
    'opacity' => 0.9,
    'lineHeight' => 1.4,
    'letterSpacing' => 0.5,
    'textDecoration' => 'none',
    'textTransform' => 'none',
    'zIndex' => 5,
    'borderWidth' => 1,
    'borderColor' => '#cccccc',
    'borderStyle' => 'solid',
    'borderRadius' => 4,
    'padding' => 8
];

$testAnalysis = analyzeElementProperties($testElement, 0);

echo "<p><strong>Élément de test créé avec succès</strong></p>";
echo "<p><strong>Propriétés:</strong> " . count($testElement) . "</p>";

if (empty($testAnalysis['issues']) && empty($testAnalysis['warnings'])) {
    echo "<p style='color: green; font-weight: bold;'>✅ Élément de test valide - toutes les propriétés sont correctes</p>";
} else {
    echo "<p style='color: red;'>❌ Problèmes dans l'élément de test:</p>";
    foreach (array_merge($testAnalysis['issues'], $testAnalysis['warnings']) as $problem) {
        echo "<p>⚠️ $problem</p>";
    }
}

// Test de sérialisation
$jsonTest = json_encode($testElement);
$decodedTest = json_decode($jsonTest, true);

if (json_last_error() === JSON_ERROR_NONE && $decodedTest) {
    echo "<p style='color: green;'>✅ Sérialisation JSON réussie</p>";
    echo "<p><strong>Propriétés préservées:</strong> " . count($decodedTest) . "/" . count($testElement) . "</p>";
} else {
    echo "<p style='color: red;'>❌ Erreur de sérialisation: " . json_last_error_msg() . "</p>";
}

echo "<hr>";
echo "<h2>🎯 Recommandations</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; border-left: 4px solid #2196f3;'>";
echo "<ul>";
echo "<li><strong>Validation côté client:</strong> Assurez-vous que toutes les propriétés sont validées avant la sauvegarde</li>";
echo "<li><strong>Types de données:</strong> Convertissez toujours les strings numériques en nombres appropriés</li>";
echo "<li><strong>Couleurs:</strong> Utilisez uniquement des codes hex valides (#RGB ou #RRGGBB)</li>";
echo "<li><strong>Propriétés spéciales:</strong> Vérifiez que les tableaux et éléments WooCommerce ont toutes leurs propriétés requises</li>";
echo "<li><strong>Test régulier:</strong> Exécutez ce diagnostic après chaque modification majeure</li>";
echo "</ul>";
echo "</div>";
?>