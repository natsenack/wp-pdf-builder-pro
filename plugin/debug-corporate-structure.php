<?php
/**
 * Analyse visuelle du template corporate - comprendre la structure
 */
$json = file_get_contents('templates/builtin/corporate.json');
$data = json_decode($json, true);

echo "=== STRUCTURE DU TEMPLATE CORPORATE ===\n\n";
echo "Canvas: " . $data['canvasWidth'] . "x" . $data['canvasHeight'] . "\n";
echo "Scale Factor: " . min(350 / 794, (350 * 1.414) / 1123) . "\n\n";

$canvasHeight = $data['canvasHeight'];
$previewHeight = 350 * 1.414; // 494px approximativement

echo "ZONES PRINCIPALES:\n";
echo "En-tête (0-80): 80px / $canvasHeight = " . round((80/$canvasHeight)*100, 1) . "% du document\n";
echo "CLIENT (100-140): 40px / $canvasHeight = " . round((40/$canvasHeight)*100, 1) . "% du document\n";
echo "Commande (100-120): 20px / $canvasHeight = " . round((20/$canvasHeight)*100, 1) . "% du document\n";
echo "Tableau (170-200): 30px header, 200px contenu / $canvasHeight\n";
echo "Totaux (420-480): 60px / $canvasHeight = " . round((60/$canvasHeight)*100, 1) . "% du document\n\n";

echo "POSITIONNEMENT VERTICAL:\n";
foreach ($data['elements'] as $elem) {
    $y = $elem['y'];
    $h = $elem['h'] ?? $elem['height'] ?? 0;
    $pct = round(($y / $canvasHeight) * 100, 1);
    $id = $elem['id'];
    $type = $elem['type'];
    echo "  $id ($type): y=$y (${pct}% du doc)\n";
}

echo "\n=== DANS L'APERÇU (494px) ===\n";
$scaleFactor = min(330 / 794, 466 / 1123);
echo "Scale factor réel: $scaleFactor\n";
echo "En-tête après scale: " . round(80 * $scaleFactor, 1) . "px\n";
echo "CLIENT après scale: " . round(40 * $scaleFactor, 1) . "px\n";
echo "Tableau après scale: " . round(230 * $scaleFactor, 1) . "px\n";
echo "Totaux après scale: " . round(60 * $scaleFactor, 1) . "px\n";
echo "Espace utilisé: " . round((230 + 60) * $scaleFactor / 466 * 100, 1) . "% de la hauteur\n";
?>
