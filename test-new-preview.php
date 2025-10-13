<?php
// Test du nouveau système d'aperçu

// Définir les constantes nécessaires
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

echo "<h1>Test Nouveau Système d'Aperçu PDF</h1>";

// Éléments de test
$test_elements = [
    [
        'id' => 'text-1',
        'type' => 'text',
        'content' => 'Aperçu PDF Test',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000'
    ],
    [
        'id' => 'rect-1',
        'type' => 'rectangle',
        'x' => 40,
        'y' => 100,
        'width' => 220,
        'height' => 50,
        'borderColor' => '#ff0000',
        'borderWidth' => 2
    ]
];

echo "<h2>Éléments de test :</h2>";
echo "<pre>" . json_encode($test_elements, JSON_PRETTY_PRINT) . "</pre>";

// Tester le générateur d'aperçu
echo "<h2>Inclusion des fichiers...</h2>";

if (!defined('PDF_PREVIEW_TEST_MODE')) {
    define('PDF_PREVIEW_TEST_MODE', true);
}

echo "<p>Inclusion pdf-preview-generator.php...</p>";
require_once 'includes/pdf-preview-generator.php';
echo "<p>✅ Fichier inclus avec succès</p>";

echo "<p>Instanciation PDF_Preview_Generator...</p>";
$preview_gen = new PDF_Preview_Generator();
echo "<p>✅ Classe instanciée avec succès</p>";

$preview_gen = new PDF_Preview_Generator();
$result = $preview_gen->generate_preview($test_elements);

echo "<h2>Résultat génération aperçu :</h2>";
if ($result['success']) {
    echo "<p style='color: green;'>✅ Aperçu généré avec succès !</p>";
    echo "<p>Taille image: " . strlen(base64_decode($result['preview'])) . " octets</p>";
    echo "<p>Dimensions: {$result['width']} × {$result['height']} px</p>";
    echo "<p>Éléments: {$result['elements_count']}</p>";

    // Afficher l'image d'aperçu
    echo "<h3>Aperçu visuel :</h3>";
    echo "<img src='data:image/png;base64,{$result['preview']}' style='border: 1px solid #ccc; max-width: 100%;' alt='Aperçu PDF'>";

} else {
    echo "<p style='color: red;'>❌ Erreur génération aperçu: {$result['error']}</p>";
}

// Tester l'aperçu HTML (fallback)
echo "<h2>Aperçu HTML (fallback) :</h2>";
$html_preview = $preview_gen->generate_html_preview($test_elements);
echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>{$html_preview}</div>";

echo "<h2>Conclusion :</h2>";
if ($result['success']) {
    echo "<p style='color: green; font-weight: bold;'>🎉 Le nouveau système d'aperçu fonctionne parfaitement !</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Le système d'aperçu a encore des problèmes à résoudre.</p>";
}
?>