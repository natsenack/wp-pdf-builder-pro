<?php
/**
 * Test de génération PDF - Version Web
 * Accessible via navigateur : http://votresite.com/wp-content/plugins/wp-pdf-builder-pro/test-pdf-web.php
 */

// Simuler WordPress
define('ABSPATH', dirname(__FILE__) . '/');
define('PDF_GENERATOR_TEST_MODE', true);

// Charger les dépendances nécessaires
require_once 'lib/tcpdf_autoload.php';
require_once 'includes/pdf-generator.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Génération PDF - PDF Builder Pro</title>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .success { background-color: #d4edda; border-color: #28a745; }
        .error { background-color: #f8d7da; border-color: #dc3545; }
        .info { background-color: #d1ecf1; border-color: #17a2b8; }
        .warning { background-color: #fff3cd; border-color: #ffc107; }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            font-size: 12px;
            border: 1px solid #dee2e6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary { background: #007cba; color: white; }
        .btn-primary:hover { background: #005a87; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #1e7e34; }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🧪 Test de génération PDF</h1>
            <p>PDF Builder Pro - Vérification du système TCPDF</p>
        </div>

        <div class='content'>";

function run_pdf_test() {
    echo "<div class='test-section info'>";
    echo "<h3>🚀 Démarrage du test de génération PDF...</h3>";
    echo "<pre>";

    $start_time = microtime(true);

    try {
        // Éléments de test plus complets
        $test_elements = [
            [
                'type' => 'text',
                'text' => 'Test TCPDF Generation - ' . date('d/m/Y H:i:s'),
                'x' => 50,
                'y' => 50,
                'width' => 200,
                'height' => 30,
                'fontSize' => 16,
                'color' => '#000000',
                'fontWeight' => 'bold',
                'textAlign' => 'center'
            ],
            [
                'type' => 'woocommerce-invoice-number',
                'x' => 50,
                'y' => 100,
                'width' => 150,
                'height' => 20,
                'fontSize' => 12,
                'color' => '#333333'
            ],
            [
                'type' => 'divider',
                'x' => 50,
                'y' => 140,
                'width' => 200,
                'height' => 2
            ]
        ];

        echo "📋 Éléments de test préparés (" . count($test_elements) . " éléments)\n";
        echo "🔨 Génération du PDF...\n";

        $generator = new PDF_Generator();
        $pdf_content = $generator->generate_from_elements($test_elements);

        $end_time = microtime(true);
        $duration = round(($end_time - $start_time) * 1000, 2); // en millisecondes

        if ($pdf_content) {
            $size = strlen($pdf_content);
            echo "✅ PDF généré avec succès en {$duration}ms !\n";
            echo "📊 Taille : " . number_format($size) . " octets\n";

            // Vérifier que c'est un PDF valide
            if (strpos($pdf_content, '%PDF-') === 0) {
                echo "✅ Format PDF valide détecté\n";
                echo "📄 Version PDF : " . substr($pdf_content, 5, 3) . "\n";
                echo "</pre></div>";

                echo "<div class='stats'>
                    <div class='stat'>
                        <div class='stat-number'>{$duration}ms</div>
                        <div class='stat-label'>Temps de génération</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-number'>" . number_format($size) . "</div>
                        <div class='stat-label'>Taille du PDF</div>
                    </div>
                    <div class='stat'>
                        <div class='stat-number'>" . count($test_elements) . "</div>
                        <div class='stat-label'>Éléments traités</div>
                    </div>
                </div>";

                echo "<div class='test-section success'>";
                echo "<h3>🎉 Test réussi !</h3>";
                echo "<p>Le système de génération PDF fonctionne parfaitement.</p>";
                echo "<ul>
                    <li>✅ TCPDF chargé correctement</li>
                    <li>✅ PDF généré en {$duration}ms</li>
                    <li>✅ Format PDF valide</li>
                    <li>✅ Taille : " . number_format($size) . " octets</li>
                </ul>";
                echo "</div>";

                // Offrir le téléchargement du PDF de test
                echo "<div class='test-section info'>";
                echo "<h3>📥 Télécharger le PDF de test</h3>";
                echo "<p>Cliquez ci-dessous pour télécharger le PDF généré :</p>";
                echo "<a href='data:application/pdf;base64," . base64_encode($pdf_content) . "' download='test-pdf-tcpdf-" . date('Y-m-d-H-i-s') . ".pdf' class='btn btn-primary'>📄 Télécharger PDF de test</a>";
                echo "</div>";

            } else {
                echo "⚠️ Format PDF non détecté dans le contenu\n";
                echo "Contenu (aperçu) : " . substr($pdf_content, 0, 100) . "...\n";
                echo "</pre></div>";

                echo "<div class='test-section warning'>";
                echo "<h3>⚠️ Test partiellement réussi</h3>";
                echo "<p>Le PDF a été généré mais le format n'est pas reconnu comme PDF valide.</p>";
                echo "<p>Vérifiez que TCPDF est correctement installé.</p>";
                echo "</div>";
            }

        } else {
            echo "❌ Aucun contenu PDF généré\n";
            echo "</pre></div>";

            echo "<div class='test-section error'>";
            echo "<h3>❌ Test échoué</h3>";
            echo "<p>Aucun contenu PDF n'a été généré. Vérifiez les logs d'erreur.</p>";
            echo "</div>";
        }

    } catch (Exception $e) {
        echo '❌ Erreur : ' . $e->getMessage() . "\n";
        echo '📍 Fichier : ' . $e->getFile() . "\n";
        echo '📍 Ligne : ' . $e->getLine() . "\n";
        echo "</pre></div>";

        echo "<div class='test-section error'>";
        echo "<h3>❌ Erreur lors du test</h3>";
        echo "<p><strong>Erreur :</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Fichier :</strong> " . htmlspecialchars($e->getFile()) . " ligne " . $e->getLine() . "</p>";
        echo "</div>";
    }
}

// Exécuter le test
run_pdf_test();

echo "
            <div class='test-section info'>
                <h3>🔍 Informations système</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'><strong>PHP Version :</strong></td><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'>" . phpversion() . "</td></tr>
                    <tr><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'><strong>Système :</strong></td><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'>" . php_uname() . "</td></tr>
                    <tr><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'><strong>Date du test :</strong></td><td style='padding: 5px; border-bottom: 1px solid #dee2e6;'>" . date('d/m/Y H:i:s') . "</td></tr>
                    <tr><td style='padding: 5px;'><strong>TCPDF Version :</strong></td><td style='padding: 5px;'>6.6.2</td></tr>
                </table>
            </div>

            <div class='test-section info'>
                <h3>📋 Prochaines étapes</h3>
                <p>Si le test réussit, le système TCPDF est prêt. Testez maintenant dans l'interface d'administration :</p>
                <ol>
                    <li>Allez dans l'éditeur PDF Builder Pro</li>
                    <li>Ajoutez des éléments sur le canvas</li>
                    <li>Cliquez sur 'Aperçu'</li>
                    <li>Cliquez sur '🖨️ Imprimer'</li>
                    <li>Le PDF devrait se générer et se télécharger</li>
                </ol>
                <p><strong>Note :</strong> Si vous rencontrez des problèmes, vérifiez la console du navigateur (F12) pour les erreurs JavaScript.</p>
            </div>

            <div class='test-section info'>
                <h3>🔧 Dépannage</h3>
                <p>Si le test échoue :</p>
                <ul>
                    <li>Vérifiez que tous les fichiers TCPDF sont présents dans <code>lib/tcpdf/</code></li>
                    <li>Vérifiez les permissions d'écriture sur le serveur</li>
                    <li>Consultez les logs d'erreur PHP</li>
                    <li>Vérifiez que <code>allow_url_fopen</code> est activé pour le téléchargement d'images</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>";
?>