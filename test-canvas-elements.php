<?php
/**
 * Test script pour vérifier l'application des paramètres du canvas aux éléments
 * Ce script teste si les nouveaux éléments créés utilisent les paramètres globaux du canvas
 */

echo "<h1>Test: Application des paramètres du canvas aux éléments</h1>";

// Simuler les paramètres WordPress
$canvas_settings = get_option('pdf_builder_canvas_settings', array());

// Afficher les paramètres actuels
echo "<h2>Paramètres du canvas actuels:</h2>";
echo "<pre>";
print_r($canvas_settings);
echo "</pre>";

// Simuler la création d'un élément avec les paramètres par défaut
echo "<h2>Test de création d'élément avec paramètres par défaut:</h2>";

// Fonction pour obtenir les propriétés par défaut (simulant useCanvasState.js)
function getDefaultProperties($type, $globalSettings = null) {
    $defaults = array(
        'x' => 50,
        'y' => 50,
        'width' => 100,
        'height' => 50,
        'backgroundColor' => '#ffffff',
        'borderColor' => 'transparent',
        'borderWidth' => 0,
        'borderRadius' => 4,
        'color' => '#333333',
        'fontSize' => 14,
        'fontFamily' => 'Arial, sans-serif',
        'padding' => 8
    );

    // Appliquer les paramètres globaux du canvas si disponibles
    if ($globalSettings) {
        // Couleur de bordure par défaut pour les éléments
        if (isset($globalSettings['canvas_border_color']) && $globalSettings['canvas_border_color'] !== 'var(--primary-color)') {
            $defaults['borderColor'] = $globalSettings['canvas_border_color'];
        }
        // Largeur de bordure par défaut pour les éléments
        if (isset($globalSettings['canvas_border_width']) && $globalSettings['canvas_border_width'] > 0) {
            $defaults['borderWidth'] = $globalSettings['canvas_border_width'];
        }
        // Couleur de fond par défaut pour les éléments
        if (isset($globalSettings['canvas_handle_color'])) {
            $defaults['color'] = $globalSettings['canvas_handle_color'];
        }
        // Taille de police par défaut
        if (isset($globalSettings['canvas_handle_size'])) {
            $defaults['fontSize'] = max(10, $globalSettings['canvas_handle_size'] - 2);
        }
    }

    return $defaults;
}

// Tester différents types d'éléments
$elementTypes = array('text', 'rectangle', 'woocommerce-invoice-number', 'layout-header');

echo "<h3>Propriétés par défaut SANS paramètres globaux:</h3>";
foreach ($elementTypes as $type) {
    echo "<strong>$type:</strong><br>";
    $props = getDefaultProperties($type, null);
    echo "<pre>" . json_encode($props, JSON_PRETTY_PRINT) . "</pre><br>";
}

echo "<h3>Propriétés par défaut AVEC paramètres globaux:</h3>";
foreach ($elementTypes as $type) {
    echo "<strong>$type:</strong><br>";
    $props = getDefaultProperties($type, $canvas_settings);
    echo "<pre>" . json_encode($props, JSON_PRETTY_PRINT) . "</pre><br>";
}

echo "<h2>Test JavaScript (vérifiez la console du navigateur):</h2>";
echo "<p>Ouvrez la console du navigateur dans l'éditeur de canvas et vérifiez que les nouveaux éléments utilisent les paramètres globaux.</p>";

echo "<script>
// Test JavaScript pour vérifier les paramètres globaux
console.log('Test: Paramètres du canvas dans JavaScript');
console.log('window.pdfBuilderCanvasSettings:', window.pdfBuilderCanvasSettings);

// Simuler la création d'un élément
if (window.pdfBuilderCanvasSettings) {
    console.log('Paramètres globaux disponibles pour les éléments:');
    console.log('- Couleur de bordure:', window.pdfBuilderCanvasSettings.canvas_border_color);
    console.log('- Largeur de bordure:', window.pdfBuilderCanvasSettings.canvas_border_width);
    console.log('- Couleur des poignées:', window.pdfBuilderCanvasSettings.canvas_handle_color);
    console.log('- Taille des poignées:', window.pdfBuilderCanvasSettings.canvas_handle_size);
} else {
    console.log('❌ Paramètres globaux non disponibles');
}
</script>";

echo "<h2>Instructions de test:</h2>";
echo "<ol>";
echo "<li>Allez dans l'admin WordPress > PDF Builder > Settings</li>";
echo "<li>Modifiez les paramètres du canvas (couleurs, tailles)</li>";
echo "<li>Allez dans l'éditeur de template</li>";
echo "<li>Créez de nouveaux éléments et vérifiez qu'ils utilisent les paramètres par défaut du canvas</li>";
echo "<li>Vérifiez la console du navigateur pour les logs JavaScript</li>";
echo "</ol>";
?>