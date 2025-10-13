<?php
/**
 * Test direct de TCPDF avec configuration minimale
 */

echo "🧪 Test direct de TCPDF...<br><br>";

// 1. Définir les constantes avant de charger TCPDF
define('K_TCPDF_EXTERNAL_CONFIG', true);

if (!isset($_SERVER['DOCUMENT_ROOT']) || empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
}

$tcpdf_path = __DIR__ . '/lib/tcpdf/';

// Définir les chemins
define('K_PATH_MAIN', $tcpdf_path);
define('K_PATH_FONTS', $tcpdf_path . 'fonts/');
define('K_PATH_CACHE', __DIR__ . '/cache/');
define('K_PATH_IMAGES', $tcpdf_path . 'images/');

// Créer le répertoire cache s'il n'existe pas
if (!file_exists(K_PATH_CACHE)) {
    mkdir(K_PATH_CACHE, 0755, true);
    echo "✅ Répertoire cache créé<br>";
}

echo "📂 Chemins configurés:<br>";
echo "- K_PATH_MAIN: " . K_PATH_MAIN . "<br>";
echo "- K_PATH_FONTS: " . K_PATH_FONTS . "<br>";
echo "- K_PATH_CACHE: " . K_PATH_CACHE . "<br>";
echo "- K_PATH_IMAGES: " . K_PATH_IMAGES . "<br><br>";

echo "🔄 Chargement de TCPDF...<br>";
try {
    require_once $tcpdf_path . 'tcpdf_autoload.php';
    echo "✅ TCPDF chargé<br>";

    if (class_exists('TCPDF')) {
        echo "✅ Classe TCPDF trouvée<br>";

        echo "🏗️ Création d'une instance TCPDF...<br>";
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        echo "✅ Instance TCPDF créée<br>";

        echo "📝 Ajout de contenu...<br>";
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 10, 'Test TCPDF avec permissions corrigées', 0, 1, 'C');

        echo "📤 Génération du PDF...<br>";
        $content = $pdf->Output('test.pdf', 'S');
        $size = strlen($content);

        echo "✅ PDF généré avec succès !<br>";
        echo "📊 Taille: {$size} octets<br>";

        if (strpos($content, '%PDF-') === 0) {
            echo "📄 Format PDF valide<br>";
        }

        // Sauvegarder
        file_put_contents(__DIR__ . '/test-tcpdf-direct.pdf', $content);
        echo "💾 PDF sauvegardé<br>";

    } else {
        echo "❌ Classe TCPDF non trouvée<br>";
    }

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
} catch (Error $e) {
    echo "❌ Erreur fatale: " . $e->getMessage() . "<br>";
}

echo "<br>✅ Test terminé<br>";