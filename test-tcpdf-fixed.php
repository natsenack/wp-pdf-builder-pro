<?php
/**
 * Test de TCPDF avec configuration correcte des permissions
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Simuler les fonctions WordPress
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        $path = dirname($file) . '/';
        echo "DEBUG: plugin_dir_path('$file') = '$path'<br>";
        return $path;
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'file://' . dirname(dirname($file)) . '/';
    }
}

if (!function_exists('wp_upload_dir')) {
    function wp_upload_dir() {
        return [
            'basedir' => dirname(__FILE__) . '/uploads',
            'baseurl' => 'http://localhost/uploads'
        ];
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return true;
    }
}

echo "🧪 Test de TCPDF avec configuration des permissions...<br><br>";

// Inclure le générateur PDF
require_once __DIR__ . '/includes/pdf-generator.php';

echo "📚 Création du générateur PDF...<br>";
$generator = new PDF_Generator();

echo "📋 Éléments de test...<br>";
$test_elements = [
    [
        'type' => 'text',
        'text' => 'Test TCPDF avec permissions corrigées',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000',
        'fontWeight' => 'bold'
    ]
];

echo "🔨 Génération du PDF...<br>";
try {
    $pdf_content = $generator->generate_from_elements($test_elements);

    if ($pdf_content && strpos($pdf_content, '%PDF-') === 0) {
        $size = strlen($pdf_content);
        echo "✅ PDF généré avec succès !<br>";
        echo "📊 Taille : {$size} octets<br>";
        echo "📄 Format PDF valide détecté<br>";

        // Sauvegarder le PDF de test
        $test_file = __DIR__ . '/test-pdf-tcpdf-fixed.pdf';
        file_put_contents($test_file, $pdf_content);
        echo "💾 PDF de test sauvegardé : {$test_file}<br>";

    } elseif ($pdf_content) {
        echo "⚠️ Contenu généré mais pas au format PDF (probablement HTML fallback)<br>";
        echo "📊 Taille du contenu : " . strlen($pdf_content) . " octets<br>";
    } else {
        echo "❌ Aucun contenu généré<br>";
    }

} catch (Exception $e) {
    echo "❌ Exception : " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Erreur fatale : " . $e->getMessage() . "<br>";
}

echo "<br>✅ Test terminé<br>";