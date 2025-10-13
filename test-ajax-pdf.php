<?php
/**
 * Test AJAX pour vérifier la génération PDF
 */

// Simuler WordPress
define('ABSPATH', dirname(__FILE__) . '/');
define('PDF_GENERATOR_TEST_MODE', true);

// Charger les dépendances nécessaires
require_once 'lib/tcpdf_autoload.php';
require_once 'includes/pdf-generator.php';

// Simuler une requête POST
$_POST = [
    'action' => 'pdf_builder_generate_pdf',
    'nonce' => 'test_nonce', // On va bypass la vérification nonce pour le test
    'elements' => json_encode([
        [
            'type' => 'text',
            'text' => 'Test TCPDF Generation',
            'x' => 50,
            'y' => 50,
            'width' => 200,
            'height' => 30,
            'fontSize' => 16,
            'color' => '#000000',
            'fontWeight' => 'bold'
        ]
    ]),
    'canvasWidth' => 595,
    'canvasHeight' => 842
];

echo "🚀 Test de génération PDF via AJAX simulé...<br><br>";

try {
    // Simuler la fonction AJAX (sans vérification nonce pour le test)
    echo "📋 Récupération des éléments...<br>";
    $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);
    echo "✅ " . count($elements) . " élément(s) trouvé(s)<br>";

    echo "🔨 Génération du PDF...<br>";
    $generator = new PDF_Generator();
    $pdf_content = $generator->generate_from_elements($elements);

    if ($pdf_content) {
        $size = strlen($pdf_content);
        echo "✅ PDF généré avec succès !<br>";
        echo "📊 Taille : {$size} octets<br>";

        // Sauvegarder pour test
        $test_file = __DIR__ . '/test-pdf-ajax.pdf';
        file_put_contents($test_file, $pdf_content);
        echo "💾 Fichier de test créé : {$test_file}<br>";

        // Vérifier que c'est un PDF valide
        if (strpos($pdf_content, '%PDF-') === 0) {
            echo "✅ Format PDF valide détecté<br>";
        } else {
            echo "⚠️ Format PDF non détecté<br>";
        }

    } else {
        echo "❌ Aucun contenu PDF généré<br>";
    }

} catch (Exception $e) {
    echo '❌ Erreur : ' . $e->getMessage() . '<br>';
    echo '📍 Ligne : ' . $e->getLine() . '<br>';
}

echo "<br>🎉 Test terminé<br>";
?>