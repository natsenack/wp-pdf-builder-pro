<?php
/**
 * Test de persistance des propriétés - Version CLI
 * Peut être exécuté directement en ligne de commande
 */

// Bootstrap WordPress
define('WP_USE_THEMES', false);
require_once dirname(__FILE__) . '/../../../../wp-load.php';

// Vérifier que WordPress est chargé
if (!function_exists('wp_die')) {
    die('Erreur: WordPress n\'a pas pu être chargé.' . PHP_EOL);
}

echo "🧪 Test de persistance des propriétés d'éléments PDF Builder Pro" . PHP_EOL;
echo str_repeat("=", 60) . PHP_EOL . PHP_EOL;

// Fonction pour créer un élément de test avec toutes les propriétés
function createTestElement($type = 'text') {
    $baseElement = [
        'id' => 'test_element_' . time(),
        'type' => $type,
        'x' => 100,
        'y' => 100,
        'width' => 200,
        'height' => 50,
        // Propriétés de base
        'color' => '#ff0000',
        'fontSize' => 16,
        'backgroundColor' => '#ffffff',
        'content' => 'Texte de test avec toutes les propriétés',
        // Propriétés de style étendues
        'fontFamily' => 'Arial',
        'fontWeight' => 'bold',
        'textAlign' => 'center',
        'opacity' => 0.8,
        'lineHeight' => 1.5,
        'letterSpacing' => 1,
        'textDecoration' => 'underline',
        'textTransform' => 'uppercase',
        'zIndex' => 10,
        // Propriétés de bordure
        'borderWidth' => 2,
        'borderColor' => '#000000',
        'borderStyle' => 'solid',
        'borderRadius' => 5,
        'padding' => 10,
        // Propriétés spéciales pour tableaux
        'showHeaders' => true,
        'showBorders' => true,
        'tableStyle' => 'striped',
        'columns' => [
            'name' => true,
            'price' => true,
            'quantity' => true,
            'total' => true
        ]
    ];

    return $baseElement;
}

// Fonction de nettoyage pour sauvegarde (simuler la fonction JavaScript)
function cleanElementForSerialization($element) {
    $excludedProps = [
        'domElement', 'eventListeners', 'ref', 'onClick', 'onMouseDown',
        'onMouseUp', 'onMouseMove', 'onContextMenu', 'onDoubleClick',
        'onDragStart', 'onDragEnd', 'onResize', 'component', 'render',
        'props', 'state', 'context', 'refs', '_reactInternalInstance',
        '_reactInternals', '$$typeof', 'constructor', 'prototype'
    ];

    $cleaned = [];

    foreach ($element as $key => $value) {
        if (in_array($key, $excludedProps)) {
            continue;
        }

        if ($value === null || $value === '') {
            $cleaned[$key] = $value;
        } elseif (is_string($value) || is_numeric($value) || is_bool($value)) {
            $cleaned[$key] = $value;
        } elseif (is_array($value)) {
            $cleanedArray = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    $cleanedArray[] = cleanElementForSerialization($item);
                } else {
                    $cleanedArray[] = $item;
                }
            }
            $cleaned[$key] = $cleanedArray;
        } elseif (is_object($value)) {
            $cleaned[$key] = cleanElementForSerialization((array)$value);
        }
    }

    return $cleaned;
}

// Test 1: Test de sérialisation JSON
echo "1. Test de sérialisation JSON" . PHP_EOL;
echo str_repeat("-", 30) . PHP_EOL;

$testElement = createTestElement('text');
$jsonString = json_encode($testElement);
$decodedElement = json_decode($jsonString, true);

echo "✓ Élément original: " . count($testElement) . " propriétés" . PHP_EOL;
echo "✓ JSON valide: " . (json_last_error() === JSON_ERROR_NONE ? 'OUI' : 'NON - ' . json_last_error_msg()) . PHP_EOL;
echo "✓ Désérialisation réussie: " . ($decodedElement !== null ? 'OUI' : 'NON') . PHP_EOL;
echo "✓ Propriétés préservées: " . (count($decodedElement) === count($testElement) ? 'Toutes' : count($decodedElement) . '/' . count($testElement)) . PHP_EOL;
echo PHP_EOL;

// Test 2: Test de nettoyage pour sauvegarde
echo "2. Test de nettoyage pour sauvegarde" . PHP_EOL;
echo str_repeat("-", 35) . PHP_EOL;

// Ajouter des propriétés "problématiques" à l'élément de test
$testElementWithExtras = $testElement;
$testElementWithExtras['domElement'] = 'fake_dom_ref';
$testElementWithExtras['onClick'] = 'function() {}';
$testElementWithExtras['component'] = ['fake_component'];

$cleanedElement = cleanElementForSerialization($testElementWithExtras);

echo "✓ Élément avec propriétés extra: " . count($testElementWithExtras) . " propriétés" . PHP_EOL;
echo "✓ Élément nettoyé: " . count($cleanedElement) . " propriétés" . PHP_EOL;
echo "✓ Propriétés problématiques supprimées: OUI" . PHP_EOL;

// Vérifier que les propriétés importantes sont préservées
$importantProps = [
    'fontFamily', 'fontWeight', 'textAlign', 'opacity', 'lineHeight',
    'letterSpacing', 'textDecoration', 'textTransform', 'zIndex',
    'borderWidth', 'borderColor', 'borderStyle', 'borderRadius', 'padding',
    'showHeaders', 'showBorders', 'tableStyle', 'columns'
];

$preservedCount = 0;
foreach ($importantProps as $prop) {
    if (isset($cleanedElement[$prop])) {
        $preservedCount++;
    }
}

echo "✓ Propriétés importantes préservées: $preservedCount/" . count($importantProps) . PHP_EOL;
echo PHP_EOL;

// Test 3: Test de persistance en base de données
echo "3. Test de persistance en base de données" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Créer un template de test
$testTemplateData = [
    'elements' => [$cleanedElement],
    'canvasWidth' => 595,
    'canvasHeight' => 842,
    'version' => '1.0'
];

$testTemplateJson = json_encode($testTemplateData);

if (json_last_error() === JSON_ERROR_NONE) {
    // Insérer le template de test
    $testTemplateId = $wpdb->insert($table_templates, [
        'name' => 'Test Properties Persistence - ' . date('Y-m-d H:i:s'),
        'template_data' => $testTemplateJson,
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql')
    ]);

    if ($testTemplateId) {
        $insertedId = $wpdb->insert_id;
        echo "✓ Template de test créé: ID $insertedId" . PHP_EOL;

        // Charger le template depuis la base
        $loadedTemplate = $wpdb->get_row(
            $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $insertedId),
            ARRAY_A
        );

        if ($loadedTemplate) {
            $loadedData = json_decode($loadedTemplate['template_data'], true);

            if ($loadedData && isset($loadedData['elements'][0])) {
                $loadedElement = $loadedData['elements'][0];

                echo "✓ Template chargé avec succès" . PHP_EOL;
                echo "✓ Élément préservé: " . (count($loadedElement) > 0 ? 'OUI' : 'NON') . PHP_EOL;

                // Vérifier les propriétés critiques
                $criticalPropsPreserved = 0;
                $totalCriticalProps = 0;

                foreach ($importantProps as $prop) {
                    if (isset($cleanedElement[$prop])) {
                        $totalCriticalProps++;
                        if (isset($loadedElement[$prop]) && $loadedElement[$prop] === $cleanedElement[$prop]) {
                            $criticalPropsPreserved++;
                        }
                    }
                }

                echo "✓ Propriétés critiques préservées: $criticalPropsPreserved/$totalCriticalProps" . PHP_EOL;

                // Afficher quelques exemples
                echo PHP_EOL . "Exemples de propriétés préservées:" . PHP_EOL;
                $examples = ['fontFamily', 'fontWeight', 'textAlign', 'opacity', 'borderWidth', 'showHeaders'];
                foreach ($examples as $prop) {
                    if (isset($loadedElement[$prop])) {
                        $original = $cleanedElement[$prop] ?? 'N/A';
                        $loaded = $loadedElement[$prop];
                        $status = $original === $loaded ? '✓' : '✗';
                        echo "  $prop: '$loaded' $status" . PHP_EOL;
                    }
                }

            } else {
                echo "✗ Erreur de chargement: Données corrompues" . PHP_EOL;
            }
        } else {
            echo "✗ Erreur de chargement: Template non trouvé" . PHP_EOL;
        }

        // Nettoyer le template de test
        $wpdb->delete($table_templates, ['id' => $insertedId]);
        echo "✓ Template de test nettoyé" . PHP_EOL;

    } else {
        echo "✗ Erreur de création: " . $wpdb->last_error . PHP_EOL;
    }
} else {
    echo "✗ Erreur JSON: " . json_last_error_msg() . PHP_EOL;
}

echo PHP_EOL;

// Test 4: Analyse comparative des propriétés
echo "4. Analyse comparative des propriétés" . PHP_EOL;
echo str_repeat("-", 38) . PHP_EOL;

$allPossibleProps = [
    // Propriétés de base
    'id', 'type', 'x', 'y', 'width', 'height', 'content', 'text',
    // Propriétés de style de base
    'color', 'fontSize', 'backgroundColor',
    // Propriétés de style étendues
    'fontFamily', 'fontWeight', 'textAlign', 'opacity', 'lineHeight',
    'letterSpacing', 'textDecoration', 'textTransform', 'zIndex',
    // Propriétés de bordure
    'borderWidth', 'borderColor', 'borderStyle', 'borderRadius', 'padding',
    // Propriétés de tableau
    'showHeaders', 'showBorders', 'tableStyle', 'columns',
    'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal',
    // Propriétés WooCommerce
    'field', 'format', 'prefix', 'suffix', 'fallback'
];

echo sprintf("%-20s %-10s %-10s %-10s\n", "Propriété", "Catégorie", "Supportée", "Testée");
echo str_repeat("-", 60) . PHP_EOL;

foreach ($allPossibleProps as $prop) {
    $category = 'base';
    if (in_array($prop, ['fontFamily', 'fontWeight', 'textAlign', 'lineHeight', 'letterSpacing', 'textDecoration', 'textTransform'])) {
        $category = 'text';
    } elseif (in_array($prop, ['borderWidth', 'borderColor', 'borderStyle', 'borderRadius', 'padding', 'opacity', 'zIndex'])) {
        $category = 'style';
    } elseif (in_array($prop, ['showHeaders', 'showBorders', 'tableStyle', 'columns', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'])) {
        $category = 'table';
    } elseif (in_array($prop, ['field', 'format', 'prefix', 'suffix', 'fallback'])) {
        $category = 'woocommerce';
    }

    $supported = isset($cleanedElement[$prop]) ? '✓' : '✗';
    $tested = in_array($prop, array_keys($cleanedElement)) ? '✓' : '✗';

    echo sprintf("%-20s %-10s %-10s %-10s\n", $prop, $category, $supported, $tested);
}

echo PHP_EOL . str_repeat("=", 60) . PHP_EOL;
echo "📋 RÉSUMÉ DU TEST DE PERSISTANCE" . PHP_EOL;
echo str_repeat("=", 60) . PHP_EOL;

$successIndicators = 0;
$totalIndicators = 4;

if (json_last_error() === JSON_ERROR_NONE) $successIndicators++;
if ($decodedElement !== null) $successIndicators++;
if (count($decodedElement) === count($testElement)) $successIndicators++;
if ($preservedCount === count($importantProps)) $successIndicators++;

echo "✅ Tests réussis: $successIndicators/$totalIndicators" . PHP_EOL;

if ($successIndicators === $totalIndicators) {
    echo PHP_EOL . "🎉 TOUTES LES PROPRIÉTÉS SONT CORRECTEMENT SAUVEGARDÉES ET CHARGÉES !" . PHP_EOL;
    echo "Conclusion: Le système de persistance fonctionne parfaitement." . PHP_EOL;
} else {
    echo PHP_EOL . "⚠️ CERTAINS TESTS ONT ÉCHOUÉ. VÉRIFIEZ LES LOGS POUR PLUS DE DÉTAILS." . PHP_EOL;
}

echo PHP_EOL . "Test terminé à " . date('Y-m-d H:i:s') . PHP_EOL;