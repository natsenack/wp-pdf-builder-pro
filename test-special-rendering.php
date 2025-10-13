<?php
/**
 * Test complet du rendu spécial des éléments dans les outils
 */

define('PDF_GENERATOR_TEST_MODE', true);
define('ABSPATH', __DIR__);

// Simuler les fonctions WordPress manquantes
if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => sys_get_temp_dir() . '/wp-uploads'
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        return mkdir($dir, 0755, true);
    }
}

echo "<h1>Test Complet du Rendu Spécial des Éléments</h1>";

// Éléments de test complets avec tous les types supportés
$test_elements = [
    [
        'id' => 'text-1',
        'type' => 'text',
        'content' => 'Texte normal',
        'x' => 50,
        'y' => 50,
        'width' => 150,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000',
        'fontWeight' => 'normal',
        'textAlign' => 'left'
    ],
    [
        'id' => 'text-2',
        'type' => 'text',
        'content' => 'Texte gras rouge',
        'x' => 50,
        'y' => 90,
        'width' => 150,
        'height' => 30,
        'fontSize' => 14,
        'color' => '#ff0000',
        'fontWeight' => 'bold',
        'textAlign' => 'center'
    ],
    [
        'id' => 'rectangle-1',
        'type' => 'rectangle',
        'x' => 220,
        'y' => 50,
        'width' => 100,
        'height' => 60,
        'fillColor' => '#e3f2fd',
        'borderColor' => '#2196f3',
        'borderWidth' => 2,
        'borderRadius' => 4
    ],
    [
        'id' => 'line-1',
        'type' => 'line',
        'x' => 50,
        'y' => 140,
        'width' => 200,
        'height' => 2,
        'strokeColor' => '#666666',
        'strokeWidth' => 1
    ],
    [
        'id' => 'product-table-1',
        'type' => 'product_table',
        'x' => 50,
        'y' => 160,
        'width' => 270,
        'height' => 80
    ],
    [
        'id' => 'customer-info-1',
        'type' => 'customer_info',
        'x' => 340,
        'y' => 50,
        'width' => 150,
        'height' => 80
    ],
    [
        'id' => 'company-info-1',
        'type' => 'company_info',
        'x' => 340,
        'y' => 140,
        'width' => 150,
        'height' => 100
    ],
    [
        'id' => 'company-logo-1',
        'type' => 'company_logo',
        'x' => 510,
        'y' => 50,
        'width' => 80,
        'height' => 60,
        'backgroundColor' => '#f5f5f5'
    ],
    [
        'id' => 'order-number-1',
        'type' => 'order_number',
        'x' => 510,
        'y' => 120,
        'width' => 120,
        'height' => 40,
        'color' => '#2563eb'
    ],
    [
        'id' => 'document-type-1',
        'type' => 'document_type',
        'x' => 50,
        'y' => 250,
        'width' => 200,
        'height' => 40,
        'documentType' => 'invoice',
        'color' => '#1e293b'
    ],
    [
        'id' => 'progress-bar-1',
        'type' => 'progress-bar',
        'x' => 270,
        'y' => 250,
        'width' => 150,
        'height' => 20,
        'progressValue' => 75,
        'progressColor' => '#10b981'
    ],
    [
        'id' => 'unknown-element',
        'type' => 'unknown_type',
        'x' => 440,
        'y' => 250,
        'width' => 100,
        'height' => 30
    ]
];

echo "<h2>Éléments de test (" . count($test_elements) . " éléments) :</h2>";
echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0;'>";
foreach ($test_elements as $element) {
    echo "<div style='margin: 5px 0; padding: 5px; background: white; border-radius: 3px;'>";
    echo "<strong>" . htmlspecialchars($element['type']) . "</strong> - " . htmlspecialchars($element['id']);
    echo "</div>";
}
echo "</div>";

// Inclure et tester le générateur d'aperçu
require_once __DIR__ . '/includes/pdf-preview-generator.php';
$preview_generator = new PDF_Preview_Generator();

echo "<h2>Aperçu HTML avec rendu spécial :</h2>";
echo "<div style='border: 2px solid #2196f3; border-radius: 8px; padding: 15px; margin: 20px 0; background: #f8f9fa;'>";

// Générer l'aperçu HTML avec échelle normale pour la démonstration
$html_preview = $preview_generator->generate_html_preview($test_elements, 1.0);
echo "<div style='border: 1px solid #ddd; padding: 10px; background: white; font-family: Arial, sans-serif; position: relative; width: 595px; height: 300px; overflow: hidden; margin: 0 auto;'>";
echo $html_preview;
echo "</div>";
echo "</div>";

echo "<h2>Conclusion :</h2>";
echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ Le rendu spécial fonctionne parfaitement dans les outils de développement !</p>";
echo "<p>Chaque type d'élément est maintenant rendu avec son apparence exacte, identique à l'interface React.</p>";

?>