<?php
// Analyse des positions du template Corporate
$json = file_get_contents('templates/builtin/corporate.json');
$data = json_decode($json, true);

echo "=== ANALYSE DU TEMPLATE CORPORATE ===\n\n";

$canvas_width = $data['canvasWidth']; // 794
$canvas_height = $data['canvasHeight']; // 1123

echo "Canvas: {$canvas_width}x{$canvas_height} pixels (A4)\n\n";

echo "Éléments et leurs positions:\n";
foreach ($data['elements'] as $element) {
    $x = $element['x'];
    $y = $element['y'];
    $width = $element['width'];
    $height = $element['height'];
    $x2 = $x + $width;
    $y2 = $y + $height;

    $overflow_x = $x2 > $canvas_width ? '❌ DÉBORDE X' : '✅ OK X';
    $overflow_y = $y2 > $canvas_height ? '❌ DÉBORDE Y' : '✅ OK Y';

    echo "- {$element['id']} ({$element['type']}): ";
    echo "pos({$x},{$y}) size({$width}x{$height}) ";
    echo "→ ({$x2},{$y2}) | {$overflow_x} | {$overflow_y}\n";
}

echo "\n=== PROBLÈMES IDENTIFIÉS ===\n";

// Vérifier les débordements
$overflows = [];
foreach ($data['elements'] as $element) {
    $x = $element['x'];
    $y = $element['y'];
    $width = $element['width'];
    $height = $element['height'];

    if (($x + $width) > $canvas_width) {
        $overflows[] = "{$element['id']}: déborde en largeur (" . ($x + $width - $canvas_width) . "px)";
    }
    if (($y + $height) > $canvas_height) {
        $overflows[] = "{$element['id']}: déborde en hauteur (" . ($y + $height - $canvas_height) . "px)";
    }
}

if (empty($overflows)) {
    echo "✅ Aucun débordement détecté\n";
} else {
    echo "❌ Éléments débordants:\n";
    foreach ($overflows as $overflow) {
        echo "   - $overflow\n";
    }
}

// Vérifier les éléments dans l'aperçu SVG
echo "\n=== ÉLÉMENTS MANQUANTS DANS L'APERÇU ===\n";

$svg_content = file_get_contents('assets/images/templates/corporate-preview.svg');
$expected_elements = [
    'header-background' => 'rect',
    'logo-circle' => 'rect',
    'logo-text' => '✓',
    'company-info' => 'Entreprise XYZ',
    'client-label' => 'CLIENT',
    'client-name' => 'Sample Text',
    'client-address' => 'Sample Text',
    'order-number' => 'Commande',
    'order-date' => 'Date',
    'table-header-bg' => 'rect',
    'table-header' => 'Produit',
    'items-table' => 'Produit Sample',
    'subtotal-label' => 'Sous-total',
    'subtotal-value' => 'Sample Text',
    'discount-label' => 'Coupon',
    'discount-value' => 'Sample Text',
    'total-background' => 'rect',
    'total-label' => 'TOTAL',
    'total-value' => 'Sample Text'
];

$missing_elements = [];
foreach ($expected_elements as $element_id => $search_text) {
    if (strpos($svg_content, $search_text) === false) {
        $missing_elements[] = $element_id;
    }
}

if (empty($missing_elements)) {
    echo "✅ Tous les éléments sont présents dans l'aperçu\n";
} else {
    echo "❌ Éléments manquants dans l'aperçu:\n";
    foreach ($missing_elements as $missing) {
        echo "  - $missing\n";
    }
}
?>