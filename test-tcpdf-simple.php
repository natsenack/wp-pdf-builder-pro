<?php
/**
 * Test simple de chargement TCPDF
 */

echo "🔍 Test de chargement TCPDF simple...\n\n";

// Définir les constantes
if (!defined('K_TCPDF_EXTERNAL_CONFIG')) {
    define('K_TCPDF_EXTERNAL_CONFIG', true);
}
if (!defined('K_TCPDF_VERSION')) {
    define('K_TCPDF_VERSION', '6.6.2');
}

echo "1️⃣ Constantes définies\n";
echo "   K_TCPDF_VERSION: " . (defined('K_TCPDF_VERSION') ? K_TCPDF_VERSION : 'NON DEFINIE') . "\n";
echo "   K_TCPDF_EXTERNAL_CONFIG: " . (defined('K_TCPDF_EXTERNAL_CONFIG') ? 'true' : 'false') . "\n\n";

echo "2️⃣ Test de chargement tcpdf_font_data.php...\n";
try {
    require_once __DIR__ . '/lib/tcpdf/include/tcpdf_font_data.php';
    echo "✅ tcpdf_font_data.php chargé avec succès\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du chargement: " . $e->getMessage() . "\n";
}

echo "\n3️⃣ Test de chargement tcpdf_static.php...\n";
try {
    require_once __DIR__ . '/lib/tcpdf/include/tcpdf_static.php';
    echo "✅ tcpdf_static.php chargé avec succès\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du chargement: " . $e->getMessage() . "\n";
}

echo "\n4️⃣ Test de chargement tcpdf.php...\n";
try {
    require_once __DIR__ . '/lib/tcpdf/tcpdf.php';
    echo "✅ tcpdf.php chargé avec succès\n";
} catch (Exception $e) {
    echo "❌ Erreur lors du chargement: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test terminé\n";