<?php
/**
 * Test script pour vérifier la génération PDF
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Simuler une requête AJAX pour tester la génération PDF
function test_pdf_generation() {
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

    try {
        // Inclure le générateur PDF
        require_once plugin_dir_path(__FILE__) . 'includes/pdf-generator.php';

        // Générer le PDF
        $generator = new PDF_Generator();
        $pdf_content = $generator->generate_from_elements($test_elements);

        if ($pdf_content) {
            // Sauvegarder le PDF de test
            $test_file = plugin_dir_path(__FILE__) . 'test-pdf-output.pdf';
            file_put_contents($test_file, $pdf_content);

            echo '✅ PDF généré avec succès ! Fichier créé : ' . $test_file . '<br>';
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