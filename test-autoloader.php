<?php
/**
 * Test de l'autoloader TCPDF modifié
 */

echo "🔍 Test de l'autoloader TCPDF modifié...\n\n";

try {
    require_once __DIR__ . '/lib/tcpdf/tcpdf_autoload.php';
    echo "✅ Autoloader chargé avec succès\n\n";

    // Tester si la classe TCPDF est disponible
    if (class_exists('TCPDF')) {
        echo "✅ Classe TCPDF disponible\n";

        // Tester la création d'une instance
        $pdf = new TCPDF();
        echo "✅ Instance TCPDF créée avec succès\n";
        echo "   Version disponible via TCPDF_VERSION: " . (defined('TCPDF_VERSION') ? TCPDF_VERSION : 'N/A') . "\n";
    } else {
        echo "❌ Classe TCPDF non disponible\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test terminé\n";