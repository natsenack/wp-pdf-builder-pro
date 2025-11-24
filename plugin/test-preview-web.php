<?php
/**
 * Test Web de l'API Preview - Accessible via navigateur
 * URL: /wp-content/plugins/wp-pdf-builder-pro/test-preview-web.php
 */

// Simuler un environnement WordPress minimal
define('ABSPATH', dirname(__FILE__) . '/');
define('WP_CONTENT_DIR', dirname(__FILE__) . '/../wp-content');

// Simuler les fonctions WordPress nécessaires
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) { return true; }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '') { echo "Erreur: $message"; exit; }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($text) { return $text; }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return $value; }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback) { }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($path) { return mkdir($path, 0755, true); }
}

if (!function_exists('wp_clear_scheduled_hook')) {
    function wp_clear_scheduled_hook($hook) { }
}

if (!function_exists('wp_next_scheduled')) {
    function wp_next_scheduled($hook) { return false; }
}

if (!function_exists('wp_schedule_event')) {
    function wp_schedule_event($timestamp, $recurrence, $hook) { }
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(__FILE__) . '/../wp-content');
}

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test API Preview PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { border-color: #27ae60; background: #d5f4e6; }
        .error { border-color: #e74c3c; background: #fadbd8; }
        .info { border-color: #3498db; background: #d4e6f1; }
        .btn { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #2980b9; }
        .result { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px; font-family: monospace; white-space: pre-wrap; }
        .status { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .status.success { background: #27ae60; }
        .status.error { background: #e74c3c; }
        .status.info { background: #3498db; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Test API Preview - PDF Builder Pro</h1>
        <p><strong>Jours 3-4 : Génération PDF avec DomPDF</strong></p>

        <div class='test-section info'>
            <h3>📋 État du système</h3>
            <p>Ce test valide l'intégration DomPDF et la génération PDF dans le système d'aperçu.</p>
            <button class='btn' onclick='runTest()'>🚀 Lancer le test de génération PDF</button>
            <div id='test-result'></div>
        </div>

        <div class='test-section'>
            <h3>🔧 Configuration testée</h3>
            <ul>
                <li><strong>DomPDF</strong> : Intégré via Composer</li>
                <li><strong>DPI</strong> : 150 (optimisé)</li>
                <li><strong>Format</strong> : A4 Portrait</li>
                <li><strong>Compression</strong> : FAST</li>
                <li><strong>Mémoire</strong> : 256MB limite</li>
                <li><strong>Timeout</strong> : 30 secondes</li>
            </ul>
        </div>

        <div class='test-section'>
            <h3>📊 Template de test</h3>
            <p>Utilise <strong>SampleDataProvider</strong> avec données statiques :</p>
            <ul>
                <li>Texte : 'Test Jours 3-4 - Génération PDF avec DomPDF'</li>
                <li>Configuration : 'Configuration optimisée (DPI, compression, mémoire)'</li>
                <li>Données : 'Données statiques - Pas de variables dynamiques'</li>
            </ul>
        </div>
    </div>

    <script>
        async function runTest() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.innerHTML = '<div class=\"status info\">🔄 Test en cours...</div>';

            try {
                // Simuler l'appel à l'API (en fait, on exécute le code PHP)
                const response = await fetch(window.location.href + '?run_test=1');
                const html = await response.text();

                // Extraire la partie résultat du HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const testResult = doc.getElementById('test-result-data');

                if (testResult) {
                    resultDiv.innerHTML = testResult.innerHTML;
                } else {
                    resultDiv.innerHTML = '<div class=\"status error\">❌ Erreur : Impossible de récupérer les résultats</div>';
                }

            } catch (error) {
                resultDiv.innerHTML = '<div class=\"status error\">❌ Erreur réseau : ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>";

// Si le paramètre run_test est présent, exécuter le test réel
if (isset($_GET['run_test'])) {
    echo "<div id='test-result-data' style='display: none;'>";

    try {
        // Charger l'autoloader
        require_once 'core/autoloader.php';
        \PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__ . '/');

        // Test de génération PDF
        $generator_manager = new \PDF_Builder\Generators\GeneratorManager();
        $data_provider = new \PDF_Builder\Data\SampleDataProvider();

        $template_data = [
            'template' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => 'Test Jours 3-4 - Génération PDF avec DomPDF',
                        'style' => ['fontSize' => '16px', 'color' => '#000', 'textAlign' => 'center']
                    ],
                    [
                        'type' => 'text',
                        'content' => 'Configuration optimisée (DPI, compression, mémoire)',
                        'style' => ['fontSize' => '14px', 'color' => '#666', 'textAlign' => 'center']
                    ],
                    [
                        'type' => 'text',
                        'content' => 'Données statiques - Pas de variables dynamiques',
                        'style' => ['fontSize' => '12px', 'color' => '#999', 'textAlign' => 'center']
                    ]
                ]
            ]
        ];

        $config = [
            'dpi' => 150,
            'paper_size' => 'A4',
            'orientation' => 'portrait'
        ];

        $result = $generator_manager->generatePreview(
            $template_data,
            $data_provider,
            'pdf',
            $config
        );

        if ($result !== false) {
            $attempts = $generator_manager->getAttemptHistory();
            $lastAttempt = end($attempts);

            echo "<div class='status success'>✅ PDF généré avec succès !</div>";
            echo "<div class='result'>";
            echo "📏 Taille du fichier : " . strlen($result) . " bytes\n";
            echo "🎯 Générateur utilisé : " . ($lastAttempt['generator'] ?? 'inconnu') . "\n";
            echo "⚙️ Configuration : DPI {$config['dpi']}, {$config['paper_size']} {$config['orientation']}\n";
            echo "🎨 Éléments template : " . count($template_data['template']['elements']) . "\n";
            echo "📊 Fournisseur données : SampleDataProvider (statique)\n";
            echo "</div>";
        } else {
            echo "<div class='status error'>❌ Échec génération PDF</div>";
            echo "<div class='result'>";
            $attempts = $generator_manager->getAttemptHistory();
            echo "Historique tentatives :\n" . json_encode($attempts, JSON_PRETTY_PRINT);
            echo "</div>";
        }

    } catch (Exception $e) {
        echo "<div class='status error'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    echo "</div>";
    exit;
}
?>