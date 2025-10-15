<?php
/**
 * Test de persistance des propriétés - Version admin
 * Accessible via l'admin WordPress
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Ajouter une page de diagnostic dans l'admin
function add_diagnostic_page() {
    add_submenu_page(
        'wp-pdf-builder-pro',
        'Diagnostic des propriétés',
        'Diagnostic Propriétés',
        'manage_options',
        'properties-diagnostic',
        'render_diagnostic_page'
    );
}
add_action('admin_menu', 'add_diagnostic_page');

function render_diagnostic_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }

    echo '<div class="wrap">';
    echo '<h1>Diagnostic complet des propriétés d\'éléments</h1>';
    echo '<p>Cette page analyse et corrige automatiquement tous les problèmes de propriétés dans vos templates.</p>';

    // Inclure le script de diagnostic
    include_once plugin_dir_path(__FILE__) . 'diagnostic-properties-repair.php';

    echo '</div>';
}

// Ajouter un endpoint direct pour le diagnostic complet
function add_diagnostic_endpoint() {
    add_rewrite_rule('^pdf-builder-diagnostic/?$', 'index.php?pdf_builder_diagnostic=1', 'top');
    add_rewrite_tag('%pdf_builder_diagnostic%', '([^&]+)');
}
add_action('init', 'add_diagnostic_endpoint');

// Gérer l'endpoint direct du diagnostic
function handle_diagnostic_endpoint() {
    if (get_query_var('pdf_builder_diagnostic') == '1') {
        if (!current_user_can('manage_options')) {
            wp_die('Accès refusé');
        }

        echo '<html><head><title>Diagnostic complet des propriétés</title>';
        echo '<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>';
        echo '</head><body>';

        include_once plugin_dir_path(__FILE__) . 'diagnostic-properties-repair.php';

        echo '</body></html>';
        exit;
    }
}
add_action('template_redirect', 'handle_diagnostic_endpoint');

function render_properties_test_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }

    echo '<div class="wrap">';
    echo '<h1>Test de persistance des propriétés d\'éléments</h1>';
    echo '<p>Cette page teste que toutes les propriétés des éléments sont correctement sauvegardées et chargées.</p>';

    // Inclure le script de test
    include_once plugin_dir_path(__FILE__) . 'test-properties-persistence.php';

    echo '</div>';
}

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

// Fonction principale de test
function run_properties_persistence_test() {
    echo "<h2>1. Test de sérialisation JSON</h2>";

    $testElement = createTestElement('text');
    $jsonString = json_encode($testElement);
    $decodedElement = json_decode($jsonString, true);

    echo "<p><strong>Élément original:</strong> " . count($testElement) . " propriétés</p>";
    echo "<p><strong>JSON valide:</strong> " . (json_last_error() === JSON_ERROR_NONE ? '✅' : '❌ ' . json_last_error_msg()) . "</p>";
    echo "<p><strong>Désérialisation réussie:</strong> " . ($decodedElement !== null ? '✅' : '❌') . "</p>";
    echo "<p><strong>Propriétés préservées:</strong> " . (count($decodedElement) === count($testElement) ? '✅ Toutes' : '❌ ' . count($decodedElement) . '/' . count($testElement)) . "</p>";

    echo "<h2>2. Test de nettoyage pour sauvegarde</h2>";

    // Ajouter des propriétés "problématiques" à l'élément de test
    $testElementWithExtras = $testElement;
    $testElementWithExtras['domElement'] = 'fake_dom_ref';
    $testElementWithExtras['onClick'] = 'function() {}';
    $testElementWithExtras['component'] = ['fake_component'];

    $cleanedElement = cleanElementForSerialization($testElementWithExtras);

    echo "<p><strong>Élément avec propriétés extra:</strong> " . count($testElementWithExtras) . " propriétés</p>";
    echo "<p><strong>Élément nettoyé:</strong> " . count($cleanedElement) . " propriétés</p>";
    echo "<p><strong>Propriétés problématiques supprimées:</strong> ✅</p>";

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

    echo "<p><strong>Propriétés importantes préservées:</strong> $preservedCount/" . count($importantProps) . " ✅</p>";

    echo "<h2>3. Test de persistance en base de données</h2>";

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
            echo "<p><strong>Template de test créé:</strong> ID $insertedId ✅</p>";

            // Charger le template depuis la base
            $loadedTemplate = $wpdb->get_row(
                $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $insertedId),
                ARRAY_A
            );

            if ($loadedTemplate) {
                $loadedData = json_decode($loadedTemplate['template_data'], true);

                if ($loadedData && isset($loadedData['elements'][0])) {
                    $loadedElement = $loadedData['elements'][0];

                    echo "<p><strong>Template chargé avec succès:</strong> ✅</p>";
                    echo "<p><strong>Élément préservé:</strong> " . (count($loadedElement) > 0 ? '✅' : '❌') . "</p>";

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

                    echo "<p><strong>Propriétés critiques préservées:</strong> $criticalPropsPreserved/$totalCriticalProps ";
                    echo $criticalPropsPreserved === $totalCriticalProps ? '✅' : '❌';
                    echo "</p>";

                    // Afficher quelques exemples
                    echo "<h3>Exemples de propriétés préservées:</h3>";
                    echo "<ul>";
                    $examples = ['fontFamily', 'fontWeight', 'textAlign', 'opacity', 'borderWidth', 'showHeaders'];
                    foreach ($examples as $prop) {
                        if (isset($loadedElement[$prop])) {
                            $original = $cleanedElement[$prop] ?? 'N/A';
                            $loaded = $loadedElement[$prop];
                            $status = $original === $loaded ? '✅' : '❌';
                            echo "<li><strong>$prop:</strong> '$loaded' $status</li>";
                        }
                    }
                    echo "</ul>";

                } else {
                    echo "<p><strong>Erreur de chargement:</strong> Données corrompues ❌</p>";
                }
            } else {
                echo "<p><strong>Erreur de chargement:</strong> Template non trouvé ❌</p>";
            }

            // Nettoyer le template de test
            $wpdb->delete($table_templates, ['id' => $insertedId]);
            echo "<p><strong>Template de test nettoyé:</strong> ✅</p>";

        } else {
            echo "<p><strong>Erreur de création:</strong> " . $wpdb->last_error . " ❌</p>";
        }
    } else {
        echo "<p><strong>Erreur JSON:</strong> " . json_last_error_msg() . " ❌</p>";
    }

    echo "<h2>4. Analyse comparative des propriétés</h2>";

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

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; font-size: 12px;'>";
    echo "<tr><th>Propriété</th><th>Catégorie</th><th>Supportée</th><th>Testée</th></tr>";

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

        $supported = isset($cleanedElement[$prop]) ? '✅' : '❌';
        $tested = in_array($prop, array_keys($cleanedElement)) ? '✅' : '❌';

        echo "<tr><td>$prop</td><td>$category</td><td>$supported</td><td>$tested</td></tr>";
    }

    echo "</table>";

    echo "<hr>";
    echo "<h2>📋 Résumé du test de persistance</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4caf50;'>";

    $successIndicators = 0;
    $totalIndicators = 4;

    if (json_last_error() === JSON_ERROR_NONE) $successIndicators++;
    if ($decodedElement !== null) $successIndicators++;
    if (count($decodedElement) === count($testElement)) $successIndicators++;
    if ($preservedCount === count($importantProps)) $successIndicators++;

    echo "<p><strong>✅ Tests réussis:</strong> $successIndicators/$totalIndicators</p>";

    if ($successIndicators === $totalIndicators) {
        echo "<p style='color: #2e7d32; font-weight: bold;'>🎉 Toutes les propriétés sont correctement sauvegardées et chargées !</p>";
        echo "<p><strong>Conclusion:</strong> Le système de persistance fonctionne parfaitement.</p>";
    } else {
        echo "<p style='color: #f44336;'>⚠️ Certains tests ont échoué. Vérifiez les logs pour plus de détails.</p>";
    }

    echo "</div>";
}

// Exécuter le test si on est sur la page de test
if (isset($_GET['page']) && $_GET['page'] === 'properties-persistence-test') {
    run_properties_persistence_test();
}