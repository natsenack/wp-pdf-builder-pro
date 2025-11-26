<?php
/**
 * Test AJAX simulation for dimensions saving
 */

// Simuler une requête POST AJAX
$_POST = [
    'action' => 'pdf_builder_save_canvas_settings',
    'category' => 'dimensions',
    'canvas_format' => 'A4',
    'canvas_dpi' => '150',
    'nonce' => 'test_nonce' // Simulé
];

echo "=== Simulation de requête AJAX ===\n\n";
echo "POST data:\n";
print_r($_POST);
echo "\n";

// Simuler la logique de ajaxSaveCanvasSettings
$category = isset($_POST['category']) ? $_POST['category'] : '';
echo "Category: $category\n";

if ($category === 'dimensions') {
    echo "Processing dimensions category...\n";

    // Simuler saveDimensionsSettings
    $updated = 0;

    if (isset($_POST['canvas_format'])) {
        $format = $_POST['canvas_format'];
        echo "Format: $format\n";
        $updated++;
    }

    if (isset($_POST['canvas_dpi'])) {
        $dpi = intval($_POST['canvas_dpi']);
        echo "DPI: $dpi\n";

        // Calcul des dimensions
        $formatDimensionsMM = [
            'A4' => ['width' => 210, 'height' => 297]
        ];
        $dimensions = $formatDimensionsMM['A4'];
        $widthPx = round(($dimensions['width'] / 25.4) * $dpi);
        $heightPx = round(($dimensions['height'] / 25.4) * $dpi);

        echo "Calculated dimensions: {$widthPx}x{$heightPx}px\n";
        $updated++;
    }

    $saved = $updated > 0;
    echo "Save result: " . ($saved ? 'SUCCESS' : 'FAILED') . "\n";

    if ($saved) {
        $savedData = [
            'canvas_width' => $widthPx,
            'canvas_height' => $heightPx,
            'canvas_format' => 'A4',
            'canvas_orientation' => 'portrait',
            'canvas_dpi' => $dpi
        ];
        echo "Saved data:\n";
        print_r($savedData);
    }
}
?>