<?php
/**
 * Script de test des paramètres Canvas - Version améliorée
 * À supprimer après vérification
 */

// Test des paramètres canvas
echo "<h3>🎯 Test des Paramètres Canvas - Version Complète</h3>";

$settings = [
    'canvas_element_borders_enabled' => get_option('canvas_element_borders_enabled', 'NOT_SET'),
    'canvas_border_width' => get_option('canvas_border_width', 'NOT_SET'),
    'canvas_border_color' => get_option('canvas_border_color', 'NOT_SET'),
    'canvas_border_spacing' => get_option('canvas_border_spacing', 'NOT_SET'),
    'canvas_resize_handles_enabled' => get_option('canvas_resize_handles_enabled', 'NOT_SET'),
    'canvas_handle_size' => get_option('canvas_handle_size', 'NOT_SET'),
    'canvas_handle_color' => get_option('canvas_handle_color', 'NOT_SET'),
    'canvas_handle_hover_color' => get_option('canvas_handle_hover_color', 'NOT_SET')
];

echo "<h4>📊 Paramètres PHP (WordPress Options)</h4>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Paramètre</th><th>Valeur</th><th>Status</th><th>Attendu</th></tr>";

$expected_defaults = [
    'canvas_element_borders_enabled' => true,
    'canvas_border_width' => 1,
    'canvas_border_color' => '#007cba',
    'canvas_border_spacing' => 2,
    'canvas_resize_handles_enabled' => true,
    'canvas_handle_size' => 8,
    'canvas_handle_color' => '#007cba',
    'canvas_handle_hover_color' => '#ffffff'
];

foreach ($settings as $key => $value) {
    $status = ($value === 'NOT_SET') ? '❌ Non défini' : '✅ Défini';
    $expected = isset($expected_defaults[$key]) ? $expected_defaults[$key] : 'N/A';
    $match = ($value === $expected || ($value === 'NOT_SET' && $expected === true)) ? '✅' : '❌';
    echo "<tr>";
    echo "<td>{$key}</td>";
    echo "<td>" . (is_bool($value) ? ($value ? 'true' : 'false') : htmlspecialchars($value)) . "</td>";
    echo "<td>{$status}</td>";
    echo "<td>" . (is_bool($expected) ? ($expected ? 'true' : 'false') : htmlspecialchars($expected)) . " {$match}</td>";
    echo "</tr>";
}

echo "</table>";

// Test JavaScript injection
echo "<h4>🔧 Test JavaScript pdfBuilderCanvasSettings</h4>";
echo "<div id='js-test-result'>Test en cours...</div>";
echo "<script>
console.log('🎯 Test des paramètres Canvas dans JavaScript:');
if (typeof window.pdfBuilderCanvasSettings !== 'undefined') {
    console.log('✅ pdfBuilderCanvasSettings trouvé:', window.pdfBuilderCanvasSettings);

    // Vérifier que tous les paramètres sont présents
    const requiredParams = [
        'canvas_element_borders_enabled',
        'canvas_border_width',
        'canvas_border_color',
        'canvas_border_spacing',
        'canvas_resize_handles_enabled',
        'canvas_handle_size',
        'canvas_handle_color',
        'canvas_handle_hover_color'
    ];

    let missingParams = [];
    let paramValues = {};

    requiredParams.forEach(param => {
        if (window.pdfBuilderCanvasSettings.hasOwnProperty(param)) {
            paramValues[param] = window.pdfBuilderCanvasSettings[param];
        } else {
            missingParams.push(param);
        }
    });

    if (missingParams.length === 0) {
        document.getElementById('js-test-result').innerHTML = '<div style=\"background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; color: #155724;\">✅ Tous les paramètres canvas sont disponibles en JavaScript</div>';
        console.log('✅ Tous les paramètres sont présents:', paramValues);
    } else {
        document.getElementById('js-test-result').innerHTML = '<div style=\"background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; color: #721c24;\">❌ Paramètres manquants: ' + missingParams.join(', ') + '</div>';
        console.log('❌ Paramètres manquants:', missingParams);
    }
} else {
    console.log('❌ pdfBuilderCanvasSettings n\\'est PAS défini');
    document.getElementById('js-test-result').innerHTML = '<div style=\"background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; color: #721c24;\">❌ pdfBuilderCanvasSettings n\\'est PAS disponible en JavaScript</div>';
}
</script>";

// Test des styles CSS appliqués
echo "<h4>🎨 Test des Styles CSS Appliqués</h4>";
echo "<div id='css-test-result'>Test en cours...</div>";
echo "<script>
setTimeout(() => {
    const root = document.documentElement;
    const cssVars = [
        '--resize-handle-size',
        '--resize-handle-color',
        '--selection-border-width',
        '--selection-border-color',
        '--selection-border-spacing'
    ];

    let cssResults = {};
    cssVars.forEach(varName => {
        const value = getComputedStyle(root).getPropertyValue(varName).trim();
        cssResults[varName] = value;
        console.log(varName + ':', value);
    });

    const hasValues = Object.values(cssResults).some(value => value !== '');
    if (hasValues) {
        document.getElementById('css-test-result').innerHTML = '<div style=\"background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; color: #155724;\">✅ Variables CSS appliquées: ' + Object.keys(cssResults).filter(k => cssResults[k] !== '').join(', ') + '</div>';
    } else {
        document.getElementById('css-test-result').innerHTML = '<div style=\"background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; color: #856404;\">⚠️ Aucune variable CSS trouvée (normal si pas sur la page canvas)</div>';
    }
}, 100);
</script>";
?>