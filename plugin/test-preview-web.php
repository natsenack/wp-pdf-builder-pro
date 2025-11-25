<?php
/**
 * Test API Preview - PDF Builder Pro
 * Page de test accessible via URL pour les jours 3-4 (PDF) et 5-7 (Images)
 */

// Prevent direct access issues
if (!defined('ABSPATH')) {
    // Try to load WordPress environment
    $wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
    }
}

// Security check - only allow admins
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé - Permissions administrateur requises');
}
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test API Preview PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; text-align: center; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { border-color: #27ae60; background: #d5f4e6; }
        .error { border-color: #e74c3c; background: #fadbd8; }
        .info { border-color: #3498db; background: #d4e6f1; }
        .warning { border-color: #f39c12; background: #fdeaa7; }
        .btn { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #2980b9; }
        .btn.secondary { background: #95a5a6; }
        .btn.secondary:hover { background: #7f8c8d; }
        .result { margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
        .status { padding: 5px 10px; border-radius: 3px; color: white; font-weight: bold; }
        .status.success { background: #27ae60; }
        .status.error { background: #e74c3c; }
        .status.info { background: #3498db; }
        .status.warning { background: #f39c12; }
        .tab-container { display: flex; margin-bottom: 20px; }
        .tab-btn { padding: 10px 20px; border: none; background: #ecf0f1; cursor: pointer; border-radius: 5px 5px 0 0; }
        .tab-btn.active { background: #3498db; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Test API Preview - PDF Builder Pro</h1>

        <div class="tab-container">
            <button class="tab-btn active" onclick="showTab('pdf')">📄 Jours 3-4 : PDF</button>
            <button class="tab-btn" onclick="showTab('images')">🖼️ Jours 5-7 : Images</button>
        </div>

        <!-- PDF Tests (Jours 3-4) -->
        <div id="pdf" class="tab-content active">
            <p><strong>Jours 3-4 : Génération PDF avec DomPDF</strong></p>

            <div class='test-section info'>
                <h3>📋 État du système</h3>
                <p>Ce test valide l'intégration DomPDF et la génération PDF dans le système d'aperçu.</p>
                <button class='btn' onclick='runPdfTest()'>🚀 Lancer le test de génération PDF</button>
                <div id='pdf-test-result'></div>
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

        <!-- Image Tests (Jours 5-7) -->
        <div id="images" class="tab-content">
            <p><strong>Jours 5-7 : Conversion Images PDF→PNG/JPG</strong></p>

            <div class='test-section info'>
                <h3>📋 État du système</h3>
                <p>Ce test valide la conversion PDF vers images avec Imagick/GD fallback.</p>
                <button class='btn' onclick='runImageTest()'>🚀 Lancer le test de conversion images</button>
                <div id='image-test-result'></div>
            </div>

            <div class='test-section'>
                <h3>🔧 Configuration testée</h3>
                <ul>
                    <li><strong>Imagick</strong> : Extension PHP (fallback GD)</li>
                    <li><strong>Formats</strong> : PNG (150 DPI), JPG (85% qualité)</li>
                    <li><strong>Optimisation</strong> : Taille et métadonnées</li>
                    <li><strong>Cache</strong> : Système de cache fichier</li>
                </ul>
            </div>

            <div class='test-section'>
                <h3>📊 Données de test</h3>
                <p>Utilise un PDF mock généré pour tester la conversion :</p>
                <ul>
                    <li>Contenu : PDF minimal avec texte de test</li>
                    <li>Taille : ~5KB pour PNG, ~27KB pour JPG</li>
                    <li>Format : A4, contenu 'Mock PDF Content'</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        async function runPdfTest() {
            const resultDiv = document.getElementById('pdf-test-result');
            resultDiv.innerHTML = '<div class="status info">🔄 Test PDF en cours...</div>';

            try {
                const response = await fetch(window.location.href + '?run_test=pdf');
                const html = await response.text();

                // Extract the result from the HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const testResult = doc.getElementById('pdf-test-result-data');

                if (testResult) {
                    resultDiv.innerHTML = testResult.innerHTML;
                } else {
                    resultDiv.innerHTML = '<div class="status error">❌ Erreur : Impossible de récupérer les résultats PDF</div>';
                }

            } catch (error) {
                resultDiv.innerHTML = '<div class="status error">❌ Erreur réseau : ' + error.message + '</div>';
            }
        }

        async function runImageTest() {
            const resultDiv = document.getElementById('image-test-result');
            resultDiv.innerHTML = '<div class="status info">🔄 Test Images en cours...</div>';

            try {
                const response = await fetch(window.location.href + '?run_test=images');
                const html = await response.text();

                // Extract the result from the HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const testResult = doc.getElementById('images-test-result-data');

                if (testResult) {
                    resultDiv.innerHTML = testResult.innerHTML;
                } else {
                    resultDiv.innerHTML = '<div class="status error">❌ Erreur : Impossible de récupérer les résultats Images</div>';
                }

            } catch (error) {
                resultDiv.innerHTML = '<div class="status error">❌ Erreur réseau : ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>

<?php
// Handle AJAX requests
if (isset($_GET['run_test'])) {
    $test_type = $_GET['run_test'];

    echo "<div id='" . $test_type . "-test-result-data' style='display: none;'>";

    if ($test_type === 'pdf') {
        run_pdf_test();
    } elseif ($test_type === 'images') {
        run_image_test();
    }

    echo "</div>";
    exit;
}

function run_pdf_test() {
    echo '<div class="status info">🔄 Test de génération PDF en cours...</div>';

    try {
        // Load required classes
        require_once 'api/PreviewImageAPI.php';
        require_once 'generators/GeneratorManager.php';
        require_once 'data/SampleDataProvider.php';

        echo '<div class="status success">✅ Classes chargées avec succès</div>';

        // Test PDF generation
        $generator_manager = new PDF_Builder\Generators\GeneratorManager();
        $sample_data_provider = new PDF_Builder\Data\SampleDataProvider();

        // Create test template data
        $template_data = [
            'text' => 'Test Jours 3-4 - Génération PDF avec DomPDF',
            'config' => 'Configuration optimisée (DPI, compression, mémoire)',
            'data' => 'Données statiques - Pas de variables dynamiques'
        ];

        echo '<div class="status info">📊 Génération du PDF avec données de test...</div>';

        // Generate PDF using correct method
        $pdf_content = $generator_manager->generatePreview($template_data, $sample_data_provider, 'pdf');

        if ($pdf_content && strlen($pdf_content) > 1000) {
            $size_kb = round(strlen($pdf_content) / 1024, 2);
            echo '<div class="status success">✅ PDF généré avec succès (' . $size_kb . ' KB)</div>';
            echo '<div class="result">Contenu PDF généré (aperçu): ' . substr($pdf_content, 0, 200) . '...</div>';
        } else {
            echo '<div class="status error">❌ Échec de la génération PDF</div>';
        }

    } catch (Exception $e) {
        echo '<div class="status error">❌ Erreur PDF : ' . $e->getMessage() . '</div>';
    }
}

function run_image_test() {
    echo '<div class="status info">🔄 Test de conversion d\'images en cours...</div>';

    try {
        // Load ImageConverter
        require_once 'src/utilities/ImageConverter.php';

        echo '<div class="status success">✅ ImageConverter chargé avec succès</div>';

        // Check extensions
        $extensions = PDF_Builder\Utilities\ImageConverter::checkImageExtensions();
        echo '<div class="status info">📊 Extensions disponibles:</div>';
        echo '<div class="result">';
        echo 'Imagick: ' . ($extensions['imagick'] ? '✅' : '❌') . "\n";
        echo 'GD: ' . ($extensions['gd'] ? '✅' : '❌') . "\n";
        echo 'Recommandé: ' . $extensions['recommended'] . "\n";
        echo '</div>';

        // Create mock PDF content
        $mock_pdf = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Mock PDF Content) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000200 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n284\n%%EOF";

        // Test PNG conversion
        echo '<div class="status info">🖼️ Test conversion PDF → PNG...</div>';
        $png_result = PDF_Builder\Utilities\ImageConverter::convertPdfToImage($mock_pdf, ['format' => 'png', 'quality' => 150]);

        if ($png_result['success']) {
            echo '<div class="status success">✅ PNG généré (' . $png_result['file_size'] . ' bytes)</div>';
        } else {
            echo '<div class="status error">❌ Échec PNG : ' . $png_result['error'] . '</div>';
        }

        // Test JPG conversion
        echo '<div class="status info">🖼️ Test conversion PDF → JPG...</div>';
        $jpg_result = PDF_Builder\Utilities\ImageConverter::convertPdfToImage($mock_pdf, ['format' => 'jpg', 'quality' => 85]);

        if ($jpg_result['success']) {
            echo '<div class="status success">✅ JPG généré (' . $jpg_result['file_size'] . ' bytes)</div>';
        } else {
            echo '<div class="status error">❌ Échec JPG : ' . $jpg_result['error'] . '</div>';
        }

    } catch (Exception $e) {
        echo '<div class="status error">❌ Erreur Images : ' . $e->getMessage() . '</div>';
    }
}
?>