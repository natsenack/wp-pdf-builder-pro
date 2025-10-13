<?php
/**
 * Test de diagnostic TCPDF - chargement étape par étape
 */

echo "🔍 Diagnostic TCPDF - Chargement étape par étape...<br><br>";

$tcpdf_dir = __DIR__ . '/lib/tcpdf/';

echo "1️⃣ Test d'accès aux fichiers...<br>";
$files_to_check = [
    'tcpdf.php',
    'include/tcpdf_static.php',
    'include/tcpdf_font_data.php',
    'include/tcpdf_fonts.php',
    'include/tcpdf_colors.php',
    'include/tcpdf_images.php',
    'autoload.php'
];

foreach ($files_to_check as $file) {
    $path = $tcpdf_dir . $file;
    if (file_exists($path)) {
        echo "✅ $file existe<br>";
        if (is_readable($path)) {
            echo "   📖 Accessible en lecture<br>";
        } else {
            echo "   ❌ Non accessible en lecture<br>";
        }
    } else {
        echo "❌ $file introuvable: $path<br>";
    }
}

echo "<br>2️⃣ Test de chargement des constantes...<br>";
define('K_TCPDF_EXTERNAL_CONFIG', true);
define('K_PATH_MAIN', $tcpdf_dir);
define('K_PATH_FONTS', $tcpdf_dir . 'fonts/');
define('K_PATH_CACHE', __DIR__ . '/cache/');
define('K_PATH_IMAGES', $tcpdf_dir . 'images/');

if (!file_exists(K_PATH_CACHE)) {
    mkdir(K_PATH_CACHE, 0755, true);
}

echo "✅ Constantes définies<br>";

echo "<br>3️⃣ Test de chargement des fichiers inclus...<br>";
$include_files = [
    'include/tcpdf_font_data.php',
    'include/tcpdf_fonts.php',
    'include/tcpdf_colors.php',
    'include/tcpdf_images.php'
];

foreach ($include_files as $file) {
    echo "Test de $file...<br>";
    try {
        require_once $tcpdf_dir . $file;
        echo "✅ $file chargé<br>";
    } catch (Exception $e) {
        echo "❌ Exception $file: " . $e->getMessage() . "<br>";
    } catch (Error $e) {
        echo "❌ Erreur $file: " . $e->getMessage() . "<br>";
        break; // Arrêter au premier problème
    }
}

echo "<br>4️⃣ Test de chargement tcpdf.php...<br>";
try {
    require_once $tcpdf_dir . 'tcpdf.php';
    echo "✅ tcpdf.php chargé<br>";

    if (class_exists('TCPDF')) {
        echo "✅ Classe TCPDF disponible<br>";
    } else {
        echo "❌ Classe TCPDF non trouvée<br>";
    }
} catch (Exception $e) {
    echo "❌ Exception tcpdf.php: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Erreur tcpdf.php: " . $e->getMessage() . "<br>";
}

echo "<br>✅ Diagnostic terminé<br>";