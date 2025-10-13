<?php
/**
 * Test de génération PDF côté client avec jsPDF
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Inclure le générateur client
require_once __DIR__ . '/includes/pdf-generator-client.php';

echo "🚀 Test de génération PDF côté client...<br><br>";

// Éléments de test
$test_elements = [
    [
        'type' => 'text',
        'text' => 'Test PDF Client-Side Generation',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'fontSize' => 16,
        'color' => '#000000',
        'fontWeight' => 'bold'
    ],
    [
        'type' => 'text',
        'text' => 'Généré avec jsPDF',
        'x' => 50,
        'y' => 100,
        'width' => 150,
        'height' => 20,
        'fontSize' => 12,
        'color' => '#666666'
    ]
];

echo "📋 Éléments de test préparés<br>";

try {
    $client_generator = new PDF_Generator_Client();
    $result = $client_generator->generate_client_script($test_elements);

    echo "✅ Script JavaScript généré<br>";
    echo "📊 Taille du script : " . strlen($result['script']) . " caractères<br>";
    echo "📊 Taille du HTML : " . strlen($result['html']) . " caractères<br>";

    // Sauvegarder les fichiers de test
    file_put_contents(__DIR__ . '/test-pdf-client-script.js', $result['script']);
    file_put_contents(__DIR__ . '/test-pdf-client-preview.html', $result['html']);

    echo "💾 Fichiers de test créés :<br>";
    echo "- test-pdf-client-script.js<br>";
    echo "- test-pdf-client-preview.html<br>";

    echo "<br>🎯 Pour utiliser :<br>";
    echo "1. Inclure jsPDF : https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js<br>";
    echo "2. Inclure le script généré<br>";
    echo "3. Ouvrir test-pdf-client-preview.html dans un navigateur<br>";
    echo "4. Cliquer sur 'Générer PDF'<br>";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

echo "<br>✅ Test terminé<br>";