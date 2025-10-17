<?php
// Test rapide de la classe TCPDF_FONT_DATA
define('ABSPATH', __DIR__ . '/');
define('WP_DEBUG', true);

// Charger explicitement le fichier
require_once 'lib/tcpdf/tcpdf_font_data.php';

echo "=== Test TCPDF_FONT_DATA ===\n";

// Vérifier que la classe existe
if (class_exists('TCPDF_FONT_DATA')) {
    echo "✅ Classe TCPDF_FONT_DATA trouvée\n";

    // Vérifier que la propriété uni_utf8tolatin existe
    if (property_exists('TCPDF_FONT_DATA', 'uni_utf8tolatin')) {
        echo "✅ Propriété uni_utf8tolatin trouvée\n";

        // Tester l'accès à la propriété
        try {
            $test = TCPDF_FONT_DATA::$uni_utf8tolatin;
            echo "✅ Accès à uni_utf8tolatin réussi\n";
            echo "📊 Taille du tableau: " . count($test) . " éléments\n";
        } catch (Exception $e) {
            echo "❌ Erreur d'accès à uni_utf8tolatin: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Propriété uni_utf8tolatin manquante\n";
    }
} else {
    echo "❌ Classe TCPDF_FONT_DATA non trouvée\n";
}

echo "=== Fin du test ===\n";
?>