<?php
/**
 * Diagnostic complet des propriétés des éléments PDF Builder Pro
 * Analyse les propriétés sauvegardées vs chargées
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🔍 Diagnostic complet des propriétés d'éléments</h1>";

// Fonction pour analyser un élément
function analyzeElementProperties($element, $context = 'unknown') {
    if (!is_array($element)) {
        return ['error' => 'Not an array'];
    }

    $properties = [];

    // Propriétés de base attendues
    $expectedBaseProps = [
        'id', 'type', 'x', 'y', 'width', 'height',
        'color', 'fontSize', 'backgroundColor', 'content', 'text'
    ];

    // Propriétés spéciales pour les tableaux
    $tableProps = [
        'showHeaders', 'showBorders', 'columns', 'tableStyle',
        'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'
    ];

    // Propriétés de style
    $styleProps = [
        'borderWidth', 'borderColor', 'borderStyle', 'borderRadius',
        'padding', 'fontFamily', 'fontWeight', 'textAlign',
        'backgroundOpacity', 'opacity'
    ];

    // Propriétés WooCommerce
    $wooProps = [
        'field', 'format', 'prefix', 'suffix', 'fallback'
    ];

    foreach ($element as $key => $value) {
        $category = 'other';

        if (in_array($key, $expectedBaseProps)) {
            $category = 'base';
        } elseif (in_array($key, $tableProps)) {
            $category = 'table';
        } elseif (in_array($key, $styleProps)) {
            $category = 'style';
        } elseif (in_array($key, $wooProps)) {
            $category = 'woocommerce';
        } elseif (strpos($key, 'on') === 0) {
            $category = 'event';
        } elseif (is_object($value) || is_array($value)) {
            $category = 'complex';
        }

        $properties[$key] = [
            'value' => $value,
            'type' => gettype($value),
            'category' => $category,
            'context' => $context
        ];
    }

    return $properties;
}

// Test 1: Analyser les propriétés par défaut des éléments
echo "<h2>1. Propriétés par défaut des éléments</h2>";

$elementTypes = [
    'text' => ['content' => 'Texte exemple', 'color' => '#000000', 'fontSize' => 14],
    'rectangle' => ['backgroundColor' => '#ff0000', 'borderWidth' => 1],
    'product_table' => [
        'showHeaders' => true,
        'showBorders' => true,
        'columns' => ['name' => true, 'price' => true, 'quantity' => true],
        'tableStyle' => 'default'
    ]
];

foreach ($elementTypes as $type => $defaults) {
    echo "<h3>Type: $type</h3>";
    $testElement = array_merge([
        'id' => 'test_' . $type,
        'type' => $type,
        'x' => 50,
        'y' => 50,
        'width' => 100,
        'height' => 50
    ], $defaults);

    $properties = analyzeElementProperties($testElement, 'defaults');

    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Propriété</th><th>Valeur</th><th>Type</th><th>Catégorie</th></tr>";
    foreach ($properties as $key => $data) {
        $value = is_array($data['value']) ? json_encode($data['value']) : (string)$data['value'];
        $value = strlen($value) > 50 ? substr($value, 0, 47) . '...' : $value;
        echo "<tr><td>$key</td><td>$value</td><td>{$data['type']}</td><td>{$data['category']}</td></tr>";
    }
    echo "</table>";
}

// Test 2: Analyser les données d'un template existant
echo "<h2>2. Analyse des données de template sauvegardé</h2>";

global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates ORDER BY updated_at DESC LIMIT 3", ARRAY_A);

if ($templates) {
    foreach ($templates as $template) {
        echo "<h3>Template: {$template['name']} (ID: {$template['id']})</h3>";

        $template_data = json_decode($template['template_data'], true);
        if ($template_data && isset($template_data['elements'])) {
            echo "<p><strong>Nombre d'éléments:</strong> " . count($template_data['elements']) . "</p>";

            foreach ($template_data['elements'] as $index => $element) {
                echo "<h4>Élément $index: {$element['type']}</h4>";
                $properties = analyzeElementProperties($element, 'saved_template');

                $importantProps = array_filter($properties, function($prop) {
                    return !in_array($prop['category'], ['event', 'complex']);
                });

                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; font-size: 12px;'>";
                echo "<tr><th>Propriété</th><th>Valeur</th><th>Type</th><th>Catégorie</th></tr>";
                foreach ($importantProps as $key => $data) {
                    $value = is_array($data['value']) ? json_encode($data['value']) : (string)$data['value'];
                    $value = strlen($value) > 30 ? substr($value, 0, 27) . '...' : $value;
                    echo "<tr><td>$key</td><td>$value</td><td>{$data['type']}</td><td>{$data['category']}</td></tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: red;'>❌ Données corrompues ou manquantes</p>";
        }
    }
} else {
    echo "<p style='color: orange;'>⚠️ Aucun template trouvé dans la base de données</p>";
}

// Test 3: Vérifier la sérialisation/désérialisation
echo "<h2>3. Test de sérialisation JSON</h2>";

$testElement = [
    'id' => 'test_element',
    'type' => 'product_table',
    'x' => 50,
    'y' => 50,
    'width' => 400,
    'height' => 200,
    'color' => '#333333',
    'fontSize' => 12,
    'backgroundColor' => 'transparent',
    'showHeaders' => true,
    'showBorders' => true,
    'columns' => [
        'image' => true,
        'name' => true,
        'sku' => false,
        'quantity' => true,
        'price' => true,
        'total' => true
    ],
    'tableStyle' => 'striped',
    'borderWidth' => 1,
    'borderColor' => '#dddddd',
    'borderStyle' => 'solid',
    'padding' => 5
];

$json = json_encode($testElement);
$decoded = json_decode($json, true);

echo "<p><strong>JSON valide:</strong> " . (json_last_error() === JSON_ERROR_NONE ? '✅ Oui' : '❌ Non - ' . json_last_error_msg()) . "</p>";
echo "<p><strong>Désérialisation réussie:</strong> " . ($decoded !== null ? '✅ Oui' : '❌ Non') . "</p>";
echo "<p><strong>Propriétés préservées:</strong> " . (count($decoded) === count($testElement) ? '✅ Toutes' : '❌ ' . count($decoded) . '/' . count($testElement)) . "</p>";

// Test 4: Propriétés potentiellement manquées
echo "<h2>4. Propriétés potentiellement problématiques</h2>";

$potentiallyMissingProps = [
    'rotation' => 'Rotation de l\'élément',
    'opacity' => 'Opacité générale',
    'backgroundOpacity' => 'Opacité du fond',
    'borderRadius' => 'Coins arrondis',
    'textAlign' => 'Alignement du texte',
    'fontWeight' => 'Graisse de la police',
    'fontFamily' => 'Famille de police',
    'lineHeight' => 'Hauteur de ligne',
    'letterSpacing' => 'Espacement des lettres',
    'textDecoration' => 'Décoration du texte',
    'textTransform' => 'Transformation du texte',
    'zIndex' => 'Ordre Z',
    'boxShadow' => 'Ombre de boîte',
    'transform' => 'Transformation CSS'
];

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Propriété</th><th>Description</th><th>Supportée</th></tr>";
foreach ($potentiallyMissingProps as $prop => $desc) {
    $supported = in_array($prop, array_keys($testElement)) ? '✅' : '❌';
    echo "<tr><td>$prop</td><td>$desc</td><td>$supported</td></tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>📋 Résumé du diagnostic</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";

if ($templates && count($templates) > 0) {
    $firstTemplate = $templates[0];
    $template_data = json_decode($firstTemplate['template_data'], true);

    if ($template_data && isset($template_data['elements']) && count($template_data['elements']) > 0) {
        $totalProps = 0;
        $categories = [];

        foreach ($template_data['elements'] as $element) {
            $props = analyzeElementProperties($element, 'analysis');
            $totalProps += count($props);

            foreach ($props as $prop) {
                $cat = $prop['category'];
                $categories[$cat] = ($categories[$cat] ?? 0) + 1;
            }
        }

        echo "<p><strong>✅ Analyse réussie</strong></p>";
        echo "<p><strong>Total des propriétés analysées:</strong> $totalProps</p>";
        echo "<p><strong>Répartition par catégorie:</strong></p>";
        echo "<ul>";
        foreach ($categories as $cat => $count) {
            echo "<li>$cat: $count propriétés</li>";
        }
        echo "</ul>";
    } else {
        echo "<p><strong>❌ Problème détecté:</strong> Template sans éléments valides</p>";
    }
} else {
    echo "<p><strong>⚠️ Aucune donnée à analyser:</strong> Créez d'abord un template avec des éléments</p>";
}

echo "</div>";
?>