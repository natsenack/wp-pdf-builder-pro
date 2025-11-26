<?php
/**
 * Test script for dimensions calculation logic
 */

echo "=== Test du calcul des dimensions ===\n\n";

// Dimensions standard des formats papier en mm
$formatDimensionsMM = [
    'A4' => ['width' => 210, 'height' => 297],
    'A3' => ['width' => 297, 'height' => 420],
    'A5' => ['width' => 148, 'height' => 210],
    'Letter' => ['width' => 215.9, 'height' => 279.4],
    'Legal' => ['width' => 215.9, 'height' => 355.6],
    'Tabloid' => ['width' => 279.4, 'height' => 431.8]
];

// Test pour différents DPI
$testDPI = [72, 96, 150, 300];
$format = 'A4';

echo "Format: $format\n";
echo "Dimensions mm: " . $formatDimensionsMM[$format]['width'] . "×" . $formatDimensionsMM[$format]['height'] . "mm\n\n";

foreach ($testDPI as $dpi) {
    // Convertir mm en pixels (1 pouce = 25.4 mm)
    $widthPx = round(($formatDimensionsMM[$format]['width'] / 25.4) * $dpi);
    $heightPx = round(($formatDimensionsMM[$format]['height'] / 25.4) * $dpi);

    echo "DPI: $dpi\n";
    echo "Dimensions pixels: {$widthPx}×{$heightPx}px\n";
    echo "Calcul: (" . $formatDimensionsMM[$format]['width'] . " / 25.4) * $dpi = $widthPx\n";
    echo "Calcul: (" . $formatDimensionsMM[$format]['height'] . " / 25.4) * $dpi = $heightPx\n\n";
}

echo "=== Test terminé ===";
?>