<?php
// Analyse des positions du template Classic
$json = file_get_contents('templates/builtin/classic.json');
$data = json_decode($json, true);

echo "=== ANALYSE DU TEMPLATE CLASSIC ===\n\n";

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
    echo "❌ Éléments qui débordent:\n";
    foreach ($overflows as $overflow) {
        echo "  - $overflow\n";
    }
}

// Vérifier les éléments manquants dans l'aperçu
echo "\n=== ÉLÉMENTS MANQUANTS DANS L'APERÇU ===\n";
$svg_content = file_get_contents('assets/images/templates/classic-preview.svg');
$missing_elements = [];

$expected_elements = [
    'document-title' => 'FACTURE',
    'order-date' => 'Date: 05/11/2025',
    'order-totals' => 'total',
    'payment-method' => 'Conditions de règlement',
    'due-date' => 'Date d&#039;échéance',
    'footer-text' => 'Merci de votre confiance'
];

foreach ($expected_elements as $id => $keyword) {
    if (strpos($svg_content, $keyword) === false) {
        $missing_elements[] = $id;
    }
}

if (empty($missing_elements)) {
    echo "✅ Tous les éléments sont présents dans l'aperçu\n";
} else {
    echo "❌ Éléments manquants dans l'aperçu:\n";
    foreach ($missing_elements as $element) {
        echo "  - $element\n";
    }
}
?>