<?php
/**
 * Test final de génération PDF
 */

try {
    require_once 'includes/pdf-generator.php';

    $generator = new PDF_Generator();
    $elements = array(
        array('type' => 'text', 'content' => 'Test TCPDF Résolu', 'x' => 10, 'y' => 10, 'fontSize' => 16),
        array('type' => 'text', 'content' => 'Les permissions TCPDF ont été corrigées!', 'x' => 10, 'y' => 30, 'fontSize' => 12)
    );

    $result = $generator->generate_from_elements($elements, 'A4', 'portrait');
    echo $result ? '✅ Génération PDF réussie' : '❌ Échec génération PDF';
    echo "\n";
    echo "Taille du contenu: " . strlen($result) . " octets\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}