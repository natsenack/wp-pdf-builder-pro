<?php
// Analyse détaillée du template corporate et de son aperçu SVG
$json = file_get_contents('templates/builtin/corporate.json');
$data = json_decode($json, true);

$svg_content = file_get_contents('assets/images/templates/corporate-preview.svg');

echo "=== ANALYSE DÉTAILLÉE DU TEMPLATE CORPORATE ===\n\n";
echo "TOTAL ÉLÉMENTS DANS LE JSON: " . count($data['elements']) . "\n\n";

$grouped = [];
foreach ($data['elements'] as $element) {
    $type = $element['type'];
    if (!isset($grouped[$type])) {
        $grouped[$type] = [];
    }
    $grouped[$type][] = $element['id'];
}

echo "ÉLÉMENTS PAR TYPE:\n";
foreach ($grouped as $type => $ids) {
    echo "  $type: " . count($ids) . " éléments\n";
    foreach ($ids as $id) {
        // Check if element appears in SVG
        if (strpos($svg_content, $id) !== false) {
            echo "    ✅ $id\n";
        } else {
            echo "    ❌ $id (MANQUANT)\n";
        }
    }
}

echo "\n=== VÉRIFICATION PAR CONTENU ===\n";
$text_elements = ['Entreprise XYZ', 'CLIENT:', 'Commande #12345', 'Sous-total:', 'Coupon:', 'TOTAL:'];
foreach ($text_elements as $text) {
    if (strpos($svg_content, $text) !== false) {
        echo "✅ Trouve: $text\n";
    } else {
        echo "❌ Manquant: $text\n";
    }
}

echo "\n=== VÉRIFICATION DES VALEURS DE DÉMONSTRATION ===\n";
$demo_values = ['€2500.00', '-€250.00', '€2250.00', 'Produit Sample'];
foreach ($demo_values as $value) {
    if (strpos($svg_content, $value) !== false) {
        echo "✅ Trouve: $value\n";
    } else {
        echo "❌ Manquant: $value\n";
    }
}

echo "\n=== HAUTEUR TOTALE UTILISÉE ===\n";
preg_match_all('/y="([^"]+)"/', $svg_content, $matches);
if (!empty($matches[1])) {
    $max_y = max(array_map('floatval', $matches[1]));
    echo "Position Y maximale: $max_y\n";
    echo "Hauteur disponible: 494px\n";
    echo "Espace utilisé: " . round(($max_y / 494) * 100, 1) . "%\n";
}
?>
