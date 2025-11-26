<?php
/**
 * Test direct de sauvegarde des options WordPress
 */

// Simuler les données POST pour dimensions
$_POST = [
    'canvas_format' => 'A4',
    'canvas_dpi' => '150'
];

// Simuler la logique de saveDimensionsSettings
echo "=== Test de sauvegarde directe ===\n\n";

$updated = 0;

// Format du document
if (isset($_POST['canvas_format'])) {
    $format = $_POST['canvas_format']; // sanitize_text_field simulé
    $valid_formats = ['A4', 'A3', 'A5', 'Letter', 'Legal', 'Tabloid'];
    if (in_array($format, $valid_formats)) {
        // Simuler update_option
        echo "Would update pdf_builder_canvas_format to: $format\n";
        $updated++;
    }
}

// Orientation
echo "Would update pdf_builder_canvas_orientation to: portrait\n";
$updated++;

// Résolution DPI
if (isset($_POST['canvas_dpi'])) {
    $dpi = intval($_POST['canvas_dpi']);
    $valid_dpi = [72, 96, 150, 300];
    if (in_array($dpi, $valid_dpi)) {
        // Simuler update_option
        echo "Would update pdf_builder_canvas_dpi to: $dpi\n";

        // Recalculer les dimensions
        $format = 'A4';
        $formatDimensionsMM = [
            'A4' => ['width' => 210, 'height' => 297],
            'A3' => ['width' => 297, 'height' => 420],
            'A5' => ['width' => 148, 'height' => 210],
            'Letter' => ['width' => 215.9, 'height' => 279.4],
            'Legal' => ['width' => 215.9, 'height' => 355.6],
            'Tabloid' => ['width' => 279.4, 'height' => 431.8]
        ];

        $dimensions = isset($formatDimensionsMM[$format]) ? $formatDimensionsMM[$format] : $formatDimensionsMM['A4'];

        $widthPx = round(($dimensions['width'] / 25.4) * $dpi);
        $heightPx = round(($dimensions['height'] / 25.4) * $dpi);

        echo "Would update pdf_builder_canvas_width to: $widthPx\n";
        echo "Would update pdf_builder_canvas_height to: $heightPx\n";

        $updated++;
    }
}

echo "\nTotal settings that would be updated: $updated\n";
echo "Save result: " . ($updated > 0 ? 'SUCCESS' : 'FAILED') . "\n";
?>