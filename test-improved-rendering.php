<?php
/**
 * Test simple de génération PDF avec les améliorations de rendu
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Inclure les dépendances nécessaires
require_once __DIR__ . '/includes/pdf-generator.php';

// Créer une instance du générateur
$generator = new PDF_Generator();

// Éléments de test avec propriétés CSS réalistes du frontend React
$test_elements = [
    [
        'id' => 'text-element-1',
        'type' => 'text',
        'content' => 'Texte avec fond rouge et bordure bleue',
        'x' => 50,
        'y' => 50,
        'width' => 150,
        'height' => 40,
        'fontSize' => 16,
        'fontFamily' => 'Arial, sans-serif',
        'fontWeight' => 'normal',
        'color' => '#ffffff',
        'backgroundColor' => '#ff0000', // Rouge
        'borderWidth' => 3,
        'borderColor' => '#0000ff', // Bleu
        'borderStyle' => 'solid',
        'padding' => 8,
        'textAlign' => 'center',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 1
    ],
    [
        'id' => 'text-element-2',
        'type' => 'text',
        'content' => 'Texte avec fond par défaut (devrait être ignoré)',
        'x' => 50,
        'y' => 100,
        'width' => 150,
        'height' => 40,
        'fontSize' => 14,
        'fontFamily' => 'Helvetica, sans-serif',
        'fontWeight' => 'bold',
        'color' => '#000000',
        'backgroundColor' => '#d1d5db', // Couleur par défaut React - devrait être ignorée
        'borderWidth' => 2,
        'borderColor' => '#cccccc',
        'borderStyle' => 'solid',
        'padding' => 6,
        'textAlign' => 'left',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 2
    ],
    [
        'id' => 'multiline-element-1',
        'type' => 'multiline_text',
        'content' => 'Texte multiligne avec\nbordure verte et\nfond jaune',
        'x' => 50,
        'y' => 150,
        'width' => 120,
        'height' => 60,
        'fontSize' => 12,
        'fontFamily' => 'Times New Roman, serif',
        'fontWeight' => 'normal',
        'color' => '#000000',
        'backgroundColor' => '#ffff00', // Jaune
        'borderWidth' => 2,
        'borderColor' => '#00ff00', // Vert
        'borderStyle' => 'solid',
        'padding' => 4,
        'textAlign' => 'center',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 3
    ]
];

try {
    echo "🚀 Démarrage du test de génération PDF...\n";

    // Générer le PDF
    $pdf_content = $generator->generate_from_elements($test_elements);

    if ($pdf_content) {
        // Sauvegarder le PDF de test
        $test_file = __DIR__ . '/test-output-improved.pdf';
        file_put_contents($test_file, $pdf_content);

        echo "✅ PDF généré avec succès !\n";
        echo "📁 Fichier sauvegardé : " . $test_file . "\n";
        echo "📊 Taille du fichier : " . strlen($pdf_content) . " octets\n";
        echo "🎨 Éléments testés : " . count($test_elements) . "\n";
    } else {
        echo "❌ Échec de la génération du PDF\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "📍 Fichier : " . $e->getFile() . " (ligne " . $e->getLine() . ")\n";
}
?>