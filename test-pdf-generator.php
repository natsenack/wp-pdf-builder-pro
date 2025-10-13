<?php
/**
 * Test script pour vérifier la génération PDF
 */

// Simuler ABSPATH pour les tests
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
    define('PDF_GENERATOR_TEST_MODE', true);
}

// Simuler une requête AJAX pour tester la génération PDF
function test_pdf_generation() {
    echo "🚀 Démarrage du test de génération PDF...<br><br>";

    // Éléments de test
    $test_elements = [
        [
            'type' => 'text',
            'text' => 'Test PDF Generation',
            'x' => 50,
            'y' => 50,
            'width' => 200,
            'height' => 30,
            'fontSize' => 16,
            'fontFamily' => 'Arial',
            'color' => '#000000',
            'fontWeight' => 'bold'
        ],
        [
            'type' => 'woocommerce-invoice-number',
            'x' => 50,
            'y' => 100,
            'width' => 150,
            'height' => 20,
            'fontSize' => 12
        ]
    ];

    echo "📋 Éléments de test préparés<br>";

    try {
        // Inclure le générateur PDF
        echo "📚 Chargement du générateur PDF...<br>";
        require_once __DIR__ . '/includes/pdf-generator.php';
        echo "✅ Générateur PDF chargé<br>";

        // Générer le PDF
        echo "🔨 Génération du PDF...<br>";
        $generator = new PDF_Generator();
        $pdf_content = $generator->generate_from_elements($test_elements);

        if ($pdf_content) {
            echo '✅ PDF généré avec succès !<br>';
            echo '📊 Taille du contenu PDF : ' . strlen($pdf_content) . ' octets<br>';

            // Essayer de sauvegarder le PDF de test
            $test_file = __DIR__ . '/test-pdf-output.pdf';
            if (is_writable(__DIR__)) {
                file_put_contents($test_file, $pdf_content);
                echo '📁 Fichier créé : ' . $test_file . '<br>';
                echo '� <a href="' . basename($test_file) . '" target="_blank">Voir le PDF généré</a><br>';
            } else {
                echo '⚠️ Impossible d\'écrire le fichier (permissions)<br>';
                echo '📄 Contenu PDF généré (aperçu) : ' . substr($pdf_content, 0, 100) . '...<br>';
            }
        } else {
            echo '❌ Erreur : Aucun contenu PDF généré<br>';
        }

    } catch (Exception $e) {
        echo '❌ Erreur lors du test : ' . $e->getMessage() . '<br>';
        echo '📍 Fichier : ' . $e->getFile() . ' ligne ' . $e->getLine() . '<br>';
    }
}

// Exécuter le test si appelé directement
if (isset($_GET['test_pdf']) || !isset($_SERVER['HTTP_HOST'])) {
    test_pdf_generation();
    exit;
}
?>
            echo 'Taille du fichier : ' . filesize($test_file) . ' octets<br>';
        } else {
            echo '❌ Erreur : Aucun contenu PDF généré<br>';
        }

    } catch (Exception $e) {
        echo '❌ Erreur lors du test : ' . $e->getMessage() . '<br>';
    }
}

// Exécuter le test si appelé directement
if (isset($_GET['test_pdf'])) {
    test_pdf_generation();
    exit;
}
?>