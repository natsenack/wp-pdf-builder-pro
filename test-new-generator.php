<?php
/**
 * Test simple du nouveau générateur PDF
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Inclure le générateur
require_once __DIR__ . '/includes/pdf-generator.php';

echo "Test du générateur PDF Builder Pro v2.0\n";
echo "=====================================\n\n";

// Créer une instance du générateur
try {
    $generator = new PDF_Builder_Pro_Generator();
    echo "✓ Générateur instancié avec succès\n";

    // Éléments de test simples
    $test_elements = [
        [
            'type' => 'text',
            'content' => 'Test PDF Builder Pro v2.0',
            'x' => 50,
            'y' => 50,
            'width' => 150,
            'height' => 30,
            'fontSize' => 16,
            'color' => '#000000'
        ]
    ];

    echo "✓ Éléments de test préparés\n";

    // Générer le PDF
    $pdf_content = $generator->generate($test_elements);

    if (!empty($pdf_content)) {
        echo "✓ PDF généré avec succès\n";
        echo "  Taille: " . strlen($pdf_content) . " octets\n";

        // Sauvegarder le PDF de test
        file_put_contents('test-output-new.pdf', $pdf_content);
        echo "✓ PDF sauvegardé dans test-output-new.pdf\n";

        // Afficher les métriques de performance
        $metrics = $generator->get_performance_metrics();
        echo "\nMétriques de performance:\n";
        foreach ($metrics as $key => $value) {
            if (is_numeric($value)) {
                echo "  $key: " . round($value, 4) . "s\n";
            }
        }

        // Afficher les erreurs s'il y en a
        $errors = $generator->get_errors();
        if (!empty($errors)) {
            echo "\nErreurs rencontrées:\n";
            foreach ($errors as $error) {
                echo "  - $error\n";
            }
        } else {
            echo "\nAucune erreur rencontrée ✓\n";
        }

    } else {
        echo "✗ Échec de la génération du PDF\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
} catch (Throwable $t) {
    echo "✗ Erreur fatale: " . $t->getMessage() . "\n";
}

echo "\nTest terminé.\n";