<?php
// Test script pour vérifier le cache PDF
require_once 'wp-load.php';

$upload_dir = wp_upload_dir();
$pdf_dir = $upload_dir['basedir'] . '/pdf-builder-cache/previews/';

echo "Upload base dir: " . $upload_dir['basedir'] . PHP_EOL;
echo "PDF cache dir: " . $pdf_dir . PHP_EOL;
echo "Cache dir exists: " . (file_exists($pdf_dir) ? 'YES' : 'NO') . PHP_EOL;

if (!file_exists($pdf_dir)) {
    echo "Creating cache dir..." . PHP_EOL;
    $result = wp_mkdir_p($pdf_dir);
    echo "Creation result: " . ($result ? 'SUCCESS' : 'FAILED') . PHP_EOL;
}

echo "Cache dir writable: " . (is_writable($pdf_dir) ? 'YES' : 'NO') . PHP_EOL;

// Test de la classe PDF_Builder_Pro_Generator
echo "PDF_Builder_Pro_Generator class exists: " . (class_exists('PDF_Builder_Pro_Generator') ? 'YES' : 'NO') . PHP_EOL;

if (class_exists('PDF_Builder_Pro_Generator')) {
    try {
        $generator = new PDF_Builder_Pro_Generator();
        echo "Generator instantiated: SUCCESS" . PHP_EOL;
    } catch (Exception $e) {
        echo "Generator instantiation failed: " . $e->getMessage() . PHP_EOL;
    }
}
?>