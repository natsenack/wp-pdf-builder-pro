<?php
/**
 * Test simple pour vérifier TCPDF
 */

// Simuler ABSPATH pour les tests
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
    define('PDF_GENERATOR_TEST_MODE', true);
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🚀 Test de chargement TCPDF...<br><br>";

try {
    echo "📚 Chargement de l'autoload TCPDF...<br>";
    require_once __DIR__ . '/lib/tcpdf/tcpdf_autoload.php';
    echo "✅ Autoload TCPDF chargé<br>";

    echo "🔨 Test de création d'instance TCPDF...<br>";
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    echo "✅ Instance TCPDF créée avec succès<br>";

    echo "📊 Version TCPDF : " . TCPDF_STATIC::getTCPDFVersion() . "<br>";

    echo "<br>🎉 TCPDF fonctionne correctement !<br>";

} catch (Exception $e) {
    echo '❌ Erreur : ' . $e->getMessage() . '<br>';
    echo '📍 Fichier : ' . $e->getFile() . ' ligne ' . $e->getLine() . '<br>';
}
?>