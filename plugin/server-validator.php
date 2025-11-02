<?php
/**
 * PDF Builder Pro - Validation Serveur de Production
 * Script complet de test pour valider le déploiement
 *
 * @version 1.4.0
 * @date 2025-11-02
 */

// Empêcher l'accès direct non autorisé (mais permettre le test direct)
if (!defined('ABSPATH') && !isset($_GET['force_direct'])) {
    header('HTTP/1.0 403 Forbidden');
    echo "Accès direct interdit\n\n";
    echo "Pour utiliser ce validateur :\n";
    echo "1. Depuis WordPress : Inclure ce fichier dans un thème/plugin\n";
    echo "2. Direct : Ajouter ?force_direct=1 à l'URL\n";
    echo "3. WP-CLI : wp eval \"require_once 'server-validator.php';\"\n";
    exit;
}

class PDF_Builder_Server_Validator {

    private $results = [];
    private $errors = [];
    private $warnings = [];

    public function __construct() {
        $this->results['timestamp'] = date('Y-m-d H:i:s');
        $this->results['server'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $this->results['php_version'] = PHP_VERSION;
    }

    private function log($type, $message, $details = null) {
        $entry = ['message' => $message, 'time' => microtime(true)];
        if ($details) $entry['details'] = $details;

        switch ($type) {
            case 'error': $this->errors[] = $entry; break;
            case 'warning': $this->warnings[] = $entry; break;
            default: $this->results[$type][] = $entry;
        }
    }

    public function run_all_tests() {
        $this->log('info', '🚀 DÉBUT VALIDATION SERVEUR PDF BUILDER PRO');

        // Tests de base
        $this->test_wordpress_config();
        $this->test_plugin_activation();
        $this->test_php_requirements();
        $this->test_file_permissions();

        // Tests du plugin
        $this->test_autoloader();
        $this->test_core_classes();
        $this->test_database_tables();
        $this->test_assets();

        // Tests fonctionnels
        $this->test_api_endpoints();
        $this->test_pdf_generation();
        $this->test_woocommerce_integration();

        // Tests de performance
        $this->test_performance();

        $this->generate_report();
    }

    private function test_wordpress_config() {
        $this->log('info', '📋 Test Configuration WordPress');

        // Version WordPress
        global $wp_version;
        if (version_compare($wp_version, '5.0', '>=')) {
            $this->log('success', '✅ Version WordPress: ' . $wp_version);
        } else {
            $this->log('error', '❌ Version WordPress trop ancienne: ' . $wp_version . ' (requis: 5.0+)');
        }

        // Constantes essentielles
        $constants = [
            'WP_CONTENT_DIR' => WP_CONTENT_DIR,
            'WP_PLUGIN_DIR' => WP_PLUGIN_DIR,
            'ABSPATH' => ABSPATH
        ];

        foreach ($constants as $name => $value) {
            if (defined($name) && !empty($value)) {
                $this->log('success', "✅ Constante $name définie");
            } else {
                $this->log('error', "❌ Constante $name manquante");
            }
        }

        // Mode debug
        if (WP_DEBUG) {
            $this->log('warning', '⚠️ Mode DEBUG activé (désactiver en production)');
        } else {
            $this->log('success', '✅ Mode DEBUG désactivé');
        }
    }

    private function test_plugin_activation() {
        $this->log('info', '📦 Test Activation Plugin');

        $plugin_file = 'wp-pdf-builder-pro/pdf-builder-pro.php';
        $active_plugins = get_option('active_plugins', []);

        if (in_array($plugin_file, $active_plugins)) {
            $this->log('success', '✅ Plugin activé dans WordPress');
        } else {
            $this->log('error', '❌ Plugin NON activé dans WordPress');
            return;
        }

        // Vérifier que le plugin est chargé
        if (class_exists('WP_PDF_Builder_Pro\\Core\\Plugin')) {
            $this->log('success', '✅ Classe principale du plugin chargée');
        } else {
            $this->log('error', '❌ Classe principale du plugin NON chargée');
        }
    }

    private function test_php_requirements() {
        $this->log('info', '🐘 Test Configuration PHP');

        // Version PHP
        if (version_compare(PHP_VERSION, '8.0', '>=')) {
            $this->log('success', '✅ Version PHP: ' . PHP_VERSION);
        } else {
            $this->log('error', '❌ Version PHP trop ancienne: ' . PHP_VERSION . ' (requis: 8.0+)');
        }

        // Extensions requises
        $required_extensions = ['mbstring', 'gd', 'xml', 'zip', 'curl'];
        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext)) {
                $this->log('success', "✅ Extension $ext chargée");
            } else {
                $this->log('error', "❌ Extension $ext manquante");
            }
        }

        // Configuration PHP
        $configs = [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size')
        ];

        foreach ($configs as $key => $value) {
            $this->log('info', "📊 $key: $value");
        }

        // Vérifier memory_limit
        $memory_limit = $this->parse_size($configs['memory_limit']);
        if ($memory_limit >= 64 * 1024 * 1024) { // 64M
            $this->log('success', '✅ Memory limit suffisant');
        } else {
            $this->log('warning', '⚠️ Memory limit faible: ' . $configs['memory_limit']);
        }
    }

    private function test_file_permissions() {
        $this->log('info', '🔐 Test Permissions Fichiers');

        $plugin_dir = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/';

        if (is_dir($plugin_dir)) {
            $this->log('success', '✅ Dossier plugin existe');

            // Test écriture dans le dossier cache
            $cache_dir = $plugin_dir . 'cache/';
            if (is_dir($cache_dir)) {
                $test_file = $cache_dir . 'test_write_' . time() . '.tmp';
                if (@file_put_contents($test_file, 'test') !== false) {
                    unlink($test_file);
                    $this->log('success', '✅ Dossier cache accessible en écriture');
                } else {
                    $this->log('error', '❌ Dossier cache NON accessible en écriture');
                }
            } else {
                $this->log('warning', '⚠️ Dossier cache n\'existe pas');
            }

            // Vérifier permissions des fichiers principaux
            $main_files = ['pdf-builder-pro.php', 'bootstrap.php'];
            foreach ($main_files as $file) {
                $file_path = $plugin_dir . $file;
                if (file_exists($file_path)) {
                    $perms = substr(sprintf('%o', fileperms($file_path)), -4);
                    $this->log('info', "📄 $file: permissions $perms");
                }
            }

        } else {
            $this->log('error', '❌ Dossier plugin n\'existe pas');
        }
    }

    private function test_autoloader() {
        $this->log('info', '🔄 Test Autoloader');

        // Test chargement des classes principales
        $classes_to_test = [
            'WP_PDF_Builder_Pro\\Core\\Plugin',
            'WP_PDF_Builder_Pro\\Api\\PreviewImageAPI',
            'WP_PDF_Builder_Pro\\Data\\WooCommerceDataProvider',
            'WP_PDF_Builder_Pro\\Data\\CanvasDataProvider',
            'WP_PDF_Builder_Pro\\Generators\\PDFGenerator',
            'WP_PDF_Builder_Pro\\Interfaces\\DataProviderInterface'
        ];

        foreach ($classes_to_test as $class) {
            if (class_exists($class)) {
                $this->log('success', "✅ Classe $class chargée");
            } else {
                $this->log('error', "❌ Classe $class NON trouvée");
            }
        }
    }

    private function test_core_classes() {
        $this->log('info', '🏗️ Test Classes Core');

        try {
            // Test instanciation API
            $api = new WP_PDF_Builder_Pro\Api\PreviewImageAPI();
            $this->log('success', '✅ PreviewImageAPI instanciée');

            // Test DataProviders
            $woo_provider = new WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();
            $this->log('success', '✅ WooCommerceDataProvider instanciée');

            $canvas_provider = new WP_PDF_Builder_Pro\Data\CanvasDataProvider();
            $this->log('success', '✅ CanvasDataProvider instanciée');

            // Test interface
            if ($woo_provider instanceof WP_PDF_Builder_Pro\Interfaces\DataProviderInterface) {
                $this->log('success', '✅ Interface DataProviderInterface implémentée');
            } else {
                $this->log('error', '❌ Interface DataProviderInterface NON implémentée');
            }

        } catch (Exception $e) {
            $this->log('error', '❌ Erreur instanciation classes: ' . $e->getMessage());
        }
    }

    private function test_database_tables() {
        $this->log('info', '🗄️ Test Base de Données');

        global $wpdb;

        // Tables du plugin
        $tables_to_check = [
            'pdf_builder_templates' => $wpdb->prefix . 'pdf_builder_templates',
            'pdf_builder_logs' => $wpdb->prefix . 'pdf_builder_logs'
        ];

        foreach ($tables_to_check as $name => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
                $this->log('success', "✅ Table $name existe ($count entrées)");
            } else {
                $this->log('warning', "⚠️ Table $name n'existe pas (sera créée automatiquement)");
            }
        }

        // Test connexion DB
        if ($wpdb->check_connection()) {
            $this->log('success', '✅ Connexion base de données OK');
        } else {
            $this->log('error', '❌ Connexion base de données échouée');
        }
    }

    private function test_assets() {
        $this->log('info', '🎨 Test Assets');

        $plugin_url = plugins_url('', dirname(__FILE__));

        // Test fichiers JavaScript principaux
        $js_files = [
            'assets/js/dist/pdf-builder-admin.js',
            'assets/js/dist/pdf-builder-editor.js'
        ];

        foreach ($js_files as $js_file) {
            $file_path = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/' . $js_file;
            if (file_exists($file_path)) {
                $size = filesize($file_path);
                $this->log('success', "✅ $js_file existe ($size bytes)");
            } else {
                $this->log('warning', "⚠️ $js_file n'existe pas (compilation requise)");
            }
        }

        // Test fichiers CSS
        $css_files = [
            'assets/css/admin.css',
            'assets/css/editor.css'
        ];

        foreach ($css_files as $css_file) {
            $file_path = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/' . $css_file;
            if (file_exists($file_path)) {
                $this->log('success', "✅ $css_file existe");
            } else {
                $this->log('warning', "⚠️ $css_file n'existe pas");
            }
        }
    }

    private function test_api_endpoints() {
        $this->log('info', '🔗 Test API Endpoints');

        // Vérifier que les actions AJAX sont enregistrées
        $ajax_actions = [
            'wp_ajax_pdf_builder_load_template',
            'wp_ajax_pdf_builder_save_template',
            'wp_ajax_pdf_builder_generate_preview',
            'wp_ajax_pdf_builder_generate_pdf'
        ];

        foreach ($ajax_actions as $action) {
            if (has_action($action)) {
                $this->log('success', "✅ Action AJAX $action enregistrée");
            } else {
                $this->log('warning', "⚠️ Action AJAX $action NON trouvée");
            }
        }

        // Test endpoint PreviewImageAPI
        try {
            $api = new WP_PDF_Builder_Pro\Api\PreviewImageAPI();
            $this->log('success', '✅ PreviewImageAPI accessible');
        } catch (Exception $e) {
            $this->log('error', '❌ Erreur PreviewImageAPI: ' . $e->getMessage());
        }
    }

    private function test_pdf_generation() {
        $this->log('info', '📄 Test Génération PDF');

        try {
            // Test génération basique
            $template_data = [
                'template' => [
                    'elements' => [
                        [
                            'type' => 'text',
                            'content' => 'Test PDF Generation',
                            'x' => 50,
                            'y' => 50,
                            'fontSize' => 16
                        ]
                    ]
                ]
            ];

            $canvas_provider = new WP_PDF_Builder_Pro\Data\CanvasDataProvider();
            $generator = new WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, $canvas_provider, true);

            $result = $generator->generate('pdf');
            if ($result) {
                $this->log('success', '✅ Génération PDF basique réussie');
            } else {
                $this->log('error', '❌ Génération PDF basique échouée');
            }

            // Test génération image
            if (method_exists($generator, 'generate_preview_image')) {
                $image_result = $generator->generate_preview_image(150, 'png');
                if ($image_result && file_exists($image_result)) {
                    $this->log('success', '✅ Génération image de prévisualisation réussie');
                    unlink($image_result); // Nettoyer
                } else {
                    $this->log('error', '❌ Génération image de prévisualisation échouée');
                }
            }

        } catch (Exception $e) {
            $this->log('error', '❌ Erreur génération PDF: ' . $e->getMessage());
        }
    }

    private function test_woocommerce_integration() {
        $this->log('info', '🛒 Test Intégration WooCommerce');

        // Vérifier si WooCommerce est actif
        if (class_exists('WooCommerce')) {
            $this->log('success', '✅ WooCommerce détecté');

            // Test WooCommerceDataProvider avec données fictives
            try {
                $woo_provider = new WP_PDF_Builder_Pro\Data\WooCommerceDataProvider();

                // Créer un mock order
                $mock_order = new class {
                    public $id = 12345;
                    public function get_order_number() { return '#12345'; }
                    public function get_total() { return 99.99; }
                    public function get_formatted_billing_full_name() { return 'Jean Dupont'; }
                };

                $woo_provider->setOrder($mock_order);

                $test_vars = ['order_number', 'customer_name', 'order_total'];
                foreach ($test_vars as $var) {
                    $value = $woo_provider->getVariableValue($var);
                    if (!empty($value)) {
                        $this->log('success', "✅ Variable WooCommerce $var: OK");
                    } else {
                        $this->log('warning', "⚠️ Variable WooCommerce $var: vide");
                    }
                }

            } catch (Exception $e) {
                $this->log('error', '❌ Erreur intégration WooCommerce: ' . $e->getMessage());
            }

        } else {
            $this->log('warning', '⚠️ WooCommerce NON détecté (fonctionnalités limitées)');
        }
    }

    private function test_performance() {
        $this->log('info', '⚡ Test Performance');

        $start_time = microtime(true);
        $start_memory = memory_get_usage();

        // Test chargement d'une classe
        class_exists('WP_PDF_Builder_Pro\\Generators\\PDFGenerator');

        $end_time = microtime(true);
        $end_memory = memory_get_usage();

        $load_time = ($end_time - $start_time) * 1000; // ms
        $memory_used = ($end_memory - $start_memory) / 1024; // KB

        $this->log('info', sprintf('📊 Temps de chargement: %.2f ms', $load_time));
        $this->log('info', sprintf('📊 Mémoire utilisée: %.2f KB', $memory_used));

        if ($load_time < 100) {
            $this->log('success', '✅ Performance de chargement acceptable');
        } else {
            $this->log('warning', '⚠️ Performance de chargement lente');
        }
    }

    private function parse_size($size) {
        $unit = strtolower(substr($size, -1));
        $value = (int)$size;
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        return $value;
    }

    private function generate_report() {
        $this->log('info', '📊 GÉNÉRATION RAPPORT FINAL');

        $total_tests = count($this->results['success'] ?? []) + count($this->errors) + count($this->warnings);
        $success_count = count($this->results['success'] ?? []);
        $error_count = count($this->errors);
        $warning_count = count($this->warnings);

        $score = $total_tests > 0 ? round(($success_count / $total_tests) * 100, 1) : 0;

        $this->results['summary'] = [
            'total_tests' => $total_tests,
            'success' => $success_count,
            'errors' => $error_count,
            'warnings' => $warning_count,
            'score' => $score,
            'status' => $error_count === 0 ? 'SUCCESS' : 'FAILED'
        ];

        // Affichage du rapport
        $this->display_report();
    }

    private function display_report() {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>PDF Builder Pro - Validation Serveur</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
                .summary { background: #ecf0f1; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .score { font-size: 24px; font-weight: bold; }
                .success { color: #27ae60; }
                .error { color: #e74c3c; }
                .warning { color: #f39c12; }
                .info { color: #3498db; }
                .section { margin: 20px 0; padding: 15px; border-left: 4px solid #3498db; background: #f8f9fa; }
                .entry { margin: 5px 0; padding: 5px; }
                .timestamp { color: #7f8c8d; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚀 PDF Builder Pro - Validation Serveur de Production</h1>

                <div class="summary">
                    <h2>Résumé Exécution</h2>
                    <p><strong>📅 Date:</strong> <?php echo $this->results['timestamp']; ?></p>
                    <p><strong>🖥️ Serveur:</strong> <?php echo $this->results['server']; ?></p>
                    <p><strong>🐘 PHP:</strong> <?php echo $this->results['php_version']; ?></p>
                    <p><strong>📊 Score Global:</strong>
                        <span class="score <?php echo $this->results['summary']['status'] === 'SUCCESS' ? 'success' : 'error'; ?>">
                            <?php echo $this->results['summary']['score']; ?>/100
                        </span>
                    </p>
                    <p><strong>✅ Succès:</strong> <?php echo $this->results['summary']['success']; ?></p>
                    <p><strong>❌ Erreurs:</strong> <?php echo $this->results['summary']['errors']; ?></p>
                    <p><strong>⚠️ Avertissements:</strong> <?php echo $this->results['summary']['warnings']; ?></p>
                    <p><strong>📋 Tests Totaux:</strong> <?php echo $this->results['summary']['total_tests']; ?></p>
                </div>

                <?php if (!empty($this->errors)): ?>
                <div class="section error">
                    <h3>❌ ERREURS CRITIQUES (<?php echo count($this->errors); ?>)</h3>
                    <?php foreach ($this->errors as $error): ?>
                        <div class="entry">• <?php echo htmlspecialchars($error['message']); ?>
                            <span class="timestamp">(<?php echo date('H:i:s', $error['time']); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->warnings)): ?>
                <div class="section warning">
                    <h3>⚠️ AVERTISSEMENTS (<?php echo count($this->warnings); ?>)</h3>
                    <?php foreach ($this->warnings as $warning): ?>
                        <div class="entry">• <?php echo htmlspecialchars($warning['message']); ?>
                            <span class="timestamp">(<?php echo date('H:i:s', $warning['time']); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->results['success'])): ?>
                <div class="section success">
                    <h3>✅ TESTS RÉUSSIS (<?php echo count($this->results['success']); ?>)</h3>
                    <?php foreach ($this->results['success'] as $success): ?>
                        <div class="entry">• <?php echo htmlspecialchars($success['message']); ?>
                            <span class="timestamp">(<?php echo date('H:i:s', $success['time']); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($this->results['info'])): ?>
                <div class="section info">
                    <h3>📋 INFORMATIONS (<?php echo count($this->results['info']); ?>)</h3>
                    <?php foreach ($this->results['info'] as $info): ?>
                        <div class="entry">• <?php echo htmlspecialchars($info['message']); ?>
                            <span class="timestamp">(<?php echo date('H:i:s', $info['time']); ?>)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="summary">
                    <h3>🎯 Statut Final</h3>
                    <?php if ($this->results['summary']['status'] === 'SUCCESS'): ?>
                        <p class="success">✅ VALIDATION RÉUSSIE - Plugin prêt pour production</p>
                    <?php else: ?>
                        <p class="error">❌ VALIDATION ÉCHOUÉE - Corrections requises avant production</p>
                    <?php endif; ?>

                    <p><strong>📞 Support:</strong> En cas de problème, consultez les logs détaillés ou contactez l'équipe technique.</p>
                    <p><strong>🔄 Prochaine validation:</strong> <?php echo date('Y-m-d H:i:s', strtotime('+1 hour')); ?></p>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Récupérer les résultats de validation
     */
    public function get_results() {
        return $this->results;
    }

    /**
     * Récupérer les erreurs de validation
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Récupérer les avertissements de validation
     */
    public function get_warnings() {
        return $this->warnings;
    }
}

// Exécution de la validation
if (isset($_GET['run_validation']) || defined('RUN_PDF_BUILDER_VALIDATION')) {
    $validator = new PDF_Builder_Server_Validator();
    $validator->run_all_tests();
} else {
    // Page d'accueil du validateur
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PDF Builder Pro - Validateur Serveur</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; }
            .button { display: inline-block; background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; margin: 10px; }
            .button:hover { background: #2980b9; }
            .warning { color: #e74c3c; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🧪 PDF Builder Pro - Validateur Serveur</h1>
            <p>Cet outil valide que le plugin PDF Builder Pro est correctement déployé et configuré sur ce serveur WordPress.</p>

            <p class="warning">⚠️ Assurez-vous que le plugin est activé avant de lancer la validation.</p>

            <a href="?run_validation=1" class="button">🚀 Lancer la Validation Complète</a>

            <p><small>Version 1.4.0 - <?php echo date('d/m/Y'); ?></small></p>
        </div>
    </body>
    </html>
    <?php
}