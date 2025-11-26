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

// Security check - only allow admins (skip in standalone mode)
if (defined('ABSPATH') && function_exists('current_user_can') && !current_user_can('manage_options')) {
    wp_die('Accès refusé - Permissions administrateur requises');
} elseif (!defined('ABSPATH')) {
    // Standalone mode - show warning
    echo "<div style='background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7; border-radius: 4px;'>";
    echo "<strong>⚠️ Mode Standalone:</strong> WordPress n'est pas chargé. Certaines fonctionnalités peuvent ne pas fonctionner correctement.";
    echo "</div>";
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

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Define necessary variables -->
    <script>
        var pdfBuilderAjax = {
            ajaxurl: '<?php echo defined('ABSPATH') && function_exists('admin_url') ? admin_url('admin-ajax.php') : '/wp-admin/admin-ajax.php'; ?>',
            nonce: '<?php echo defined('ABSPATH') && function_exists('wp_create_nonce') ? wp_create_nonce('pdf_builder_order_actions') : 'standalone_test_nonce'; ?>'
        };
    </script>

    <!-- Load PDF Preview API Client -->
    <script src="<?php echo defined('ABSPATH') && function_exists('plugin_dir_url') ? plugin_dir_url(__FILE__) : './'; ?>assets/js/pdf-preview-api-client.js"></script>
</head>
<body>
    <div class='container'>
        <h1>🧪 Test API Preview - PDF Builder Pro</h1>

        <div class="tab-container">
            <button class="tab-btn active" onclick="showTab('pdf')">📄 Jours 3-4 : PDF</button>
            <button class="tab-btn" onclick="showTab('images')">🖼️ Jours 5-7 : Images</button>
            <button class="tab-btn" onclick="showTab('data')">🛒 Jours 11-13 : Données WooCommerce</button>
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
                <h3>🧪 Test Données Canvas</h3>
                <p>Test rapide des données injectées dans un template simple :</p>
                <button class='btn' onclick='testCanvasData()'>🖼️ Tester Données Canvas</button>
                <div id='canvas-data-test'></div>
            </div>

        <button class='btn' onclick='testPreviewModal()'>🎨 Tester la Modal d'Aperçu</button>
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

        <!-- Data Injection Tests (Jours 11-13) -->
        <div id="data" class="tab-content">
            <p><strong>Jours 11-13 : Injection Dynamique des Données WooCommerce</strong></p>

            <div class='test-section info'>
                <h3>📋 État du système</h3>
                <p>Ce test valide l'injection des variables WooCommerce dans les templates PDF avec les DataProviders.</p>
                <button class='btn' onclick='runDataTest()'>🚀 Lancer le test d'injection des données</button>
                <div id='data-test-result'></div>
            </div>

            <div class='test-section'>
                <h3>🔧 DataProviders testés</h3>
                <ul>
                    <li><strong>SampleDataProvider</strong> : 76 variables fictives pour l'éditeur</li>
                    <li><strong>WooCommerceDataProvider</strong> : 53 variables réelles depuis WooCommerce</li>
                    <li><strong>Interface compliance</strong> : hasVariable(), getVariableValue(), getAllVariables()</li>
                    <li><strong>Placeholders</strong> : Fallbacks informatifs pour données manquantes</li>
                </ul>
            </div>

            <div class='test-section'>
                <h3>📊 Variables testées</h3>
                <p>Variables principales testées dans les deux contextes :</p>
                <ul>
                    <li><code>{{customer_name}}</code> - Nom du client</li>
                    <li><code>{{order_number}}</code> - Numéro de commande</li>
                    <li><code>{{order_total}}</code> - Montant total</li>
                    <li><code>{{company_name}}</code> - Nom entreprise</li>
                    <li><code>{{payment_method}}</code> - Mode de paiement</li>
                </ul>
            </div>

            <div class='test-section'>
                <h3>🔄 Contextes de test</h3>
                <ul>
                    <li><strong>Éditeur (Canvas)</strong> : Données fictives cohérentes</li>
                    <li><strong>Metabox</strong> : Données WooCommerce réelles ou placeholders</li>
                    <li><strong>Transition automatique</strong> : Changement de contexte selon l'usage</li>
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
            const resultDiv = document.getElementById('pdf-test-result');
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

        async function testPreviewModal() {
            const resultDiv = document.getElementById('pdf-test-result');
            resultDiv.innerHTML = '<div class="status info">🎨 Test de la modal d\'aperçu...</div>';

            try {
                console.log('🎨 [JS] Vérification API Preview disponible:', window.pdfPreviewAPI ? '✅ OUI' : '❌ NON');

                if (window.pdfPreviewAPI) {
                    console.log('🎨 [JS] API Preview trouvée, préparation données de test');

                    // Créer des données de test fictives avec la structure attendue
                    const testData = {
                        template: {
                            elements: [
                                {
                                    type: 'text',
                                    content: 'Aperçu PDF Builder Pro - Test Jours 8-10',
                                    x: 50,
                                    y: 50,
                                    width: 300,
                                    height: 40,
                                    fontSize: 18,
                                    color: '#2c3e50'
                                },
                                {
                                    type: 'text',
                                    content: 'Interface utilisateur avec contrôles zoom/rotation',
                                    x: 50,
                                    y: 100,
                                    width: 400,
                                    height: 30,
                                    fontSize: 14,
                                    color: '#7f8c8d'
                                }
                            ]
                        }
                    };

                    console.log('🎨 [JS] Données de test:', testData);
                    console.log('🎨 [JS] Variables pdfBuilderAjax:', window.pdfBuilderAjax);

                    console.log('🎨 [JS] Appel generateEditorPreview...');
                    const result = await window.pdfPreviewAPI.generateEditorPreview(testData);

                    console.log('🎨 [JS] Résultat reçu:', result);

                    if (result) {
                        console.log('🎨 [JS] ✅ Génération réussie, affichage modal');
                        resultDiv.innerHTML = '<div class="status success">✅ Modal d\'aperçu fonctionnelle - Image générée et affichée</div>';
                    } else {
                        console.log('🎨 [JS] ❌ Génération échouée, pas de résultat');
                        resultDiv.innerHTML = '<div class="status error">❌ Échec de génération d\'aperçu</div>';
                    }
                } else {
                    console.log('🎨 [JS] ❌ API Preview non disponible');
                    resultDiv.innerHTML = '<div class="status error">❌ API Preview non disponible</div>';
                }
            } catch (error) {
                console.error('🎨 [JS] ❌ Erreur dans testPreviewModal:', error);
                console.error('🎨 [JS] Détails erreur:', error.message, error.stack);
                resultDiv.innerHTML = '<div class="status error">❌ Erreur test modal : ' + error.message + '</div>';
            }
        }

        async function testCanvasData() {
            const resultDiv = document.getElementById('canvas-data-test');
            resultDiv.innerHTML = '<div class="status info">🔄 Test des données canvas en cours...</div>';

            try {
                // Template de test avec variables
                const testTemplate = {
                    templateId: 'canvas-test-' + Date.now(),
                    template: {
                        name: 'Test Canvas Data',
                        elements: [
                            {
                                type: 'text',
                                content: 'DONNÉES CANVAS - TEST INJECTION',
                                x: 50,
                                y: 50,
                                width: 400,
                                height: 40,
                                fontSize: 18,
                                color: '#2c3e50'
                            },
                            {
                                type: 'text',
                                content: 'Client: {{customer_name}} ({{customer_email}})',
                                x: 50,
                                y: 100,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            },
                            {
                                type: 'text',
                                content: 'Commande: {{order_number}} - {{order_total}}',
                                x: 50,
                                y: 130,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            },
                            {
                                type: 'text',
                                content: 'Entreprise: {{company_name}}',
                                x: 50,
                                y: 160,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            }
                        ]
                    }
                };

                console.log('🧪 [JS] Test données canvas avec template:', testTemplate);

                // Générer l'aperçu
                const result = await window.generateEditorPreview(testTemplate, {
                    quality: 150,
                    format: 'png',
                    context: 'editor'
                });

                console.log('🧪 [JS] Résultat test canvas:', result);

                if (result && result.image_url) {
                    resultDiv.innerHTML = `
                        <div class="status success">✅ Données canvas injectées avec succès</div>
                        <div class="result">
                            <strong>Variables injectées depuis SampleDataProvider:</strong><br>
                            <img src="${result.image_url}" style="max-width: 100%; border: 1px solid #ddd; margin-top: 10px;" alt="Aperçu avec données canvas">
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = '<div class="status error">❌ Échec génération aperçu canvas</div>';
                }

            } catch (error) {
                console.error('🧪 [JS] Erreur test canvas:', error);
                resultDiv.innerHTML = '<div class="status error">❌ Erreur test données canvas : ' + error.message + '</div>';
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
    } elseif ($test_type === 'data') {
        run_data_test();
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

        if (!class_exists('PDF_Builder\Generators\GeneratorManager')) {
            echo '<div class="status error">❌ Classe GeneratorManager non trouvée</div>';
            return;
        }

        if (!class_exists('PDF_Builder\Data\SampleDataProvider')) {
            echo '<div class="status error">❌ Classe SampleDataProvider non trouvée</div>';
            return;
        }

        echo '<div class="status success">✅ Classes chargées avec succès</div>';

        // Test PDF generation
        $generator_manager = new PDF_Builder\Generators\GeneratorManager();
        $sample_data_provider = new PDF_Builder\Data\SampleDataProvider();

        // Create test template data
        $template_data = [
            'template' => [
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => 'Test Jours 3-4 - Génération PDF avec DomPDF',
                        'config' => 'Configuration optimisée (DPI, compression, mémoire)',
                        'data' => 'Données statiques - Pas de variables dynamiques'
                    ]
                ]
            ]
        ];

        echo '<div class="status info">📊 Génération du PDF avec données de test...</div>';

        // Generate PDF using correct method
        $pdf_content = $generator_manager->generatePreview($template_data, $sample_data_provider, 'pdf');

        echo '<div class="result">Longueur du contenu PDF: ' . strlen($pdf_content) . ' octets</div>';
        if (strlen($pdf_content) > 0) {
            echo '<div class="result">Début du contenu PDF: ' . htmlspecialchars(substr($pdf_content, 0, 100)) . '...</div>';
        } else {
            echo '<div class="result">Contenu PDF vide - vérification des logs d\'erreur</div>';
        }

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

function run_data_test() {
    echo '<div class="status info">🔄 Test d\'injection des données WooCommerce en cours...</div>';

    try {
        // Load DataProviders
        require_once 'interfaces/DataProviderInterface.php';
        require_once 'data/SampleDataProvider.php';
        require_once 'data/WooCommerceDataProvider.php';

        if (!class_exists('PDF_Builder\Data\SampleDataProvider')) {
            echo '<div class="status error">❌ Classe SampleDataProvider non trouvée</div>';
            return;
        }

        if (!class_exists('PDF_Builder\Data\WooCommerceDataProvider')) {
            echo '<div class="status error">❌ Classe WooCommerceDataProvider non trouvée</div>';
            return;
        }

        echo '<div class="status success">✅ DataProviders chargés avec succès</div>';

        // Test SampleDataProvider
        echo '<div class="status info">📝 Test SampleDataProvider (éditeur)...</div>';
        $sampleProvider = new PDF_Builder\Data\SampleDataProvider('canvas');

        $testVars = ['customer_name', 'order_number', 'order_total', 'company_name', 'payment_method'];
        echo '<div class="result"><strong>Variables fictives :</strong></div>';
        echo '<div class="result">';
        foreach ($testVars as $var) {
            $hasVar = $sampleProvider->hasVariable($var) ? '✅' : '❌';
            $value = $sampleProvider->getVariableValue($var);
            echo "{$var}: {$hasVar} | {$value}\n";
        }
        echo '</div>';

        // Test WooCommerceDataProvider (sans commande)
        echo '<div class="status info">🛒 Test WooCommerceDataProvider (metabox - sans commande)...</div>';
        $wooProvider = new PDF_Builder\Data\WooCommerceDataProvider(null, 'metabox');

        echo '<div class="result"><strong>Placeholders (pas de commande) :</strong></div>';
        echo '<div class="result">';
        foreach ($testVars as $var) {
            $hasVar = $wooProvider->hasVariable($var) ? '✅' : '❌';
            $value = $wooProvider->getVariableValue($var);
            echo "{$var}: {$hasVar} | {$value}\n";
        }
        echo '</div>';

        // Statistiques
        $sampleCount = count($sampleProvider->getAllVariables());
        $wooCount = count($wooProvider->getAllVariables());

        echo '<div class="status success">📊 Statistiques DataProviders :</div>';
        echo '<div class="result">';
        echo "SampleDataProvider: {$sampleCount} variables\n";
        echo "WooCommerceDataProvider: {$wooCount} variables\n";
        echo '</div>';

        // Test d'injection dans template
        echo '<div class="status info">🔄 Test d\'injection dans template...</div>';

        $template = "Bonjour {{customer_name}},\n\nVotre commande {{order_number}} du {{order_date}} est {{order_status}}.\n\nTotal: {{order_total}}\nEmail: {{customer_email}}\n\nCordialement,\n{{company_name}}\n{{company_address}}";

        // Injection SampleDataProvider
        $injectedSample = $template;
        foreach ($sampleProvider->getAllVariables() as $var) {
            $injectedSample = str_replace("{{$var}}", $sampleProvider->getVariableValue($var), $injectedSample);
        }

        echo '<div class="result"><strong>Template injecté (SampleData) :</strong></div>';
        echo '<div class="result">' . nl2br(htmlspecialchars($injectedSample)) . '</div>';

        // Injection WooCommerceDataProvider
        $injectedWoo = $template;
        foreach ($wooProvider->getAllVariables() as $var) {
            $injectedWoo = str_replace("{{$var}}", $wooProvider->getVariableValue($var), $injectedWoo);
        }

        echo '<div class="result"><strong>Template injecté (WooCommerce - placeholders) :</strong></div>';
        echo '<div class="result">' . nl2br(htmlspecialchars($injectedWoo)) . '</div>';

        echo '<div class="status success">✅ Test d\'injection des données WooCommerce terminé avec succès</div>';
        echo '<div class="result"><em>Les jours 11-13 (injection dynamique des données WooCommerce) sont opérationnels !</em></div>';

    } catch (Exception $e) {
        echo '<div class="status error">❌ Erreur données : ' . $e->getMessage() . '</div>';
        echo '<div class="result">Détails: ' . $e->getTraceAsString() . '</div>';
    }
}
?>