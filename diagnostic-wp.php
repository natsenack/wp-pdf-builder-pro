<?php
/**
 * Diagnostic WordPress pour PDF Builder Pro
 * À déployer sur le serveur WordPress pour diagnostiquer les erreurs
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PDF_Builder_Diagnostic {

    private $results = [];

    public function __construct() {
        // Initialisation différée pour éviter les erreurs de chargement WordPress
        $this->results['timestamp'] = date('Y-m-d H:i:s');
        $this->results['wordpress_version'] = 'Unknown';
        $this->results['php_version'] = phpversion();
        $this->results['plugin_active'] = false;
    }

    private function init_wordpress_data() {
        // Initialiser les données WordPress seulement quand nécessaire
        if (function_exists('current_time')) {
            $this->results['timestamp'] = current_time('mysql');
        }
        if (function_exists('get_bloginfo')) {
            $this->results['wordpress_version'] = get_bloginfo('version');
        }
        if (function_exists('is_plugin_active')) {
            $this->results['plugin_active'] = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
        }
    }

    public function run_full_diagnostic() {
        // Initialiser les données WordPress en toute sécurité
        $this->init_wordpress_data();

        $this->results['sections'] = [];

        // Test 1: Vérification des fichiers
        $this->results['sections']['files'] = $this->check_critical_files();

        // Test 2: Test de chargement du plugin
        $this->results['sections']['plugin_load'] = $this->test_plugin_loading();

        // Test 3: Vérification des classes
        $this->results['sections']['classes'] = $this->check_classes();

        // Test 4: Vérification des fonctions
        $this->results['sections']['functions'] = $this->check_functions();

        // Test 5: Test des hooks WordPress
        $this->results['sections']['hooks'] = $this->check_wordpress_hooks();

        // Test 6: Test des erreurs PHP
        $this->results['sections']['errors'] = $this->check_php_errors();

        return $this->results;
    }

    private function check_critical_files() {
        $files = [
            'pdf-builder-pro.php',
            'bootstrap.php',
            'core/autoloader.php',
            'src/Core/PDF_Builder_Update_Manager.php',
            'src/Core/PDF_Builder_Metrics_Analytics.php',
            'src/utilities/PDF_Builder_Notification_Manager.php'
        ];

        $results = [];
        foreach ($files as $file) {
            $path = plugin_dir_path(__FILE__) . $file;
            $results[$file] = [
                'exists' => file_exists($path),
                'readable' => is_readable($path),
                'size' => file_exists($path) ? filesize($path) : 0
            ];
        }

        return $results;
    }

    private function test_plugin_loading() {
        $results = ['can_load' => false, 'error' => null];

        try {
            // Tester le chargement du bootstrap
            $bootstrap_path = plugin_dir_path(__FILE__) . 'bootstrap.php';
            if (file_exists($bootstrap_path)) {
                ob_start();
                include_once $bootstrap_path;
                ob_end_clean();
                $results['can_load'] = true;
            }
        } catch (Exception $e) {
            $results['error'] = [
                'type' => 'Exception',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        } catch (Error $e) {
            $results['error'] = [
                'type' => 'Error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        return $results;
    }

    private function check_classes() {
        $classes = [
            'PDF_Builder_Update_Manager',
            'PDF_Builder_Metrics_Analytics',
            'PDF_Builder_UI_Notification_Manager',
            'PDF_Builder_Intelligent_Loader',
            'PDF_Builder_Config_Manager',
            'PDF_Builder_Error_Handler',
            'PDF_Builder_Notification_Manager' // Vérifier si l'ancienne existe encore
        ];

        $results = [];
        foreach ($classes as $class) {
            $results[$class] = [
                'exists' => class_exists($class),
                'instantiable' => false,
                'error' => null
            ];

            if (class_exists($class)) {
                try {
                    // Tester si la classe peut être instanciée (si elle n'est pas abstraite/singleton)
                    $reflection = new ReflectionClass($class);
                    if (!$reflection->isAbstract() && !$reflection->hasMethod('get_instance')) {
                        $instance = new $class();
                        $results[$class]['instantiable'] = true;
                    } elseif ($reflection->hasMethod('get_instance')) {
                        // Tester les singletons
                        $instance = $class::get_instance();
                        $results[$class]['instantiable'] = true;
                    }
                } catch (Exception $e) {
                    $results[$class]['error'] = $e->getMessage();
                }
            }
        }

        return $results;
    }

    private function check_functions() {
        $functions = [
            'pdf_builder_get_db_update_status',
            'pdf_builder_get_metrics_analytics',
            'pdf_builder_translate',
            'pdf_builder_reporting',
            'pdf_builder_generate_report',
            'pdf_builder_check_updates',
            'pdf_builder_install_update',
            // Vérifier les anciennes fonctions
            'pdf_builder_get_update_status',
            'pdf_builder_get_analytics'
        ];

        $results = [];
        foreach ($functions as $function) {
            $results[$function] = function_exists($function);
        }

        return $results;
    }

    private function check_wordpress_hooks() {
        $results = [];

        $hooks_to_check = [
            'plugins_loaded',
            'init',
            'admin_init',
            'wp_enqueue_scripts',
            'admin_enqueue_scripts'
        ];

        // Vérifier si $wp_filter est disponible
        if (isset($GLOBALS['wp_filter'])) {
            global $wp_filter;

            foreach ($hooks_to_check as $hook) {
                $results[$hook] = isset($wp_filter[$hook]);
            }
        } else {
            // Si $wp_filter n'est pas disponible, marquer comme non disponible
            foreach ($hooks_to_check as $hook) {
                $results[$hook] = 'wp_filter_not_available';
            }
        }

        return $results;
    }

    private function check_php_errors() {
        $results = [
            'error_reporting' => error_reporting(),
            'display_errors' => ini_get('display_errors'),
            'log_errors' => ini_get('log_errors'),
            'error_log' => ini_get('error_log')
        ];

        // Tester la journalisation d'erreurs
        $test_error = false;
        set_error_handler(function() use (&$test_error) {
            $test_error = true;
        });

        // Générer une erreur de test
        trigger_error('Test error for diagnostic', E_USER_NOTICE);

        restore_error_handler();
        $results['error_logging_works'] = $test_error;

        return $results;
    }

    public function output_results() {
        header('Content-Type: application/json');
        echo json_encode($this->results, JSON_PRETTY_PRINT);
    }
}

// Fonction pour exécuter le diagnostic via AJAX
function pdf_builder_run_diagnostic_ajax() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $diagnostic = new PDF_Builder_Diagnostic();
    $results = $diagnostic->run_full_diagnostic();

    wp_send_json_success($results);
}
add_action('wp_ajax_pdf_builder_diagnostic', 'pdf_builder_run_diagnostic_ajax');

// Fonction pour afficher la page de diagnostic
function pdf_builder_diagnostic_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
    }

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Diagnostic PDF Builder Pro</title>
        <meta charset="utf-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .diagnostic-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .success { background-color: #d4edda; border-color: #c3e6cb; }
            .error { background-color: #f8d7da; border-color: #f5c6cb; }
            .warning { background-color: #fff3cd; border-color: #ffeaa7; }
            .info { background-color: #d1ecf1; border-color: #bee5eb; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
            button { padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer; }
            button:hover { background: #005a87; }
        </style>
    </head>
    <body>
        <h1>🔍 Diagnostic PDF Builder Pro</h1>
        <p>Cette page exécute un diagnostic complet du plugin PDF Builder Pro pour identifier les problèmes potentiels.</p>

        <button onclick="runDiagnostic()">Lancer le diagnostic</button>

        <div id="results"></div>

        <script>
        function runDiagnostic() {
            const button = document.querySelector('button');
            const resultsDiv = document.getElementById('results');

            button.disabled = true;
            button.textContent = 'Diagnostic en cours...';
            resultsDiv.innerHTML = '<div class="diagnostic-section info">⏳ Exécution du diagnostic...</div>';

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_diagnostic',
                    nonce: '<?php echo wp_create_nonce('pdf_builder_diagnostic'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                button.textContent = 'Lancer le diagnostic';

                if (data.success) {
                    displayResults(data.data);
                } else {
                    resultsDiv.innerHTML = '<div class="diagnostic-section error">❌ Erreur: ' + data.data + '</div>';
                }
            })
            .catch(error => {
                button.disabled = false;
                button.textContent = 'Lancer le diagnostic';
                resultsDiv.innerHTML = '<div class="diagnostic-section error">❌ Erreur de réseau: ' + error.message + '</div>';
            });
        }

        function displayResults(results) {
            const resultsDiv = document.getElementById('results');
            let html = '';

            html += '<div class="diagnostic-section info">';
            html += '<h3>📊 Informations générales</h3>';
            html += '<p><strong>Date:</strong> ' + results.timestamp + '</p>';
            html += '<p><strong>WordPress:</strong> ' + results.wordpress_version + '</p>';
            html += '<p><strong>PHP:</strong> ' + results.php_version + '</p>';
            html += '<p><strong>Plugin actif:</strong> ' + (results.plugin_active ? '✅ Oui' : '❌ Non') + '</p>';
            html += '</div>';

            // Section fichiers
            html += '<div class="diagnostic-section ' + (checkSectionStatus(results.sections.files) ? 'success' : 'error') + '">';
            html += '<h3>📁 Vérification des fichiers</h3>';
            html += '<pre>' + JSON.stringify(results.sections.files, null, 2) + '</pre>';
            html += '</div>';

            // Section chargement
            const loadStatus = results.sections.plugin_load.can_load;
            html += '<div class="diagnostic-section ' + (loadStatus ? 'success' : 'error') + '">';
            html += '<h3>🔌 Chargement du plugin</h3>';
            if (loadStatus) {
                html += '<p>✅ Plugin chargé avec succès</p>';
            } else {
                html += '<p>❌ Erreur de chargement</p>';
                if (results.sections.plugin_load.error) {
                    html += '<pre>' + JSON.stringify(results.sections.plugin_load.error, null, 2) + '</pre>';
                }
            }
            html += '</div>';

            // Section classes
            html += '<div class="diagnostic-section ' + (checkSectionStatus(results.sections.classes) ? 'success' : 'error') + '">';
            html += '<h3>🏗️ Classes disponibles</h3>';
            html += '<pre>' + JSON.stringify(results.sections.classes, null, 2) + '</pre>';
            html += '</div>';

            // Section fonctions
            html += '<div class="diagnostic-section ' + (checkSectionStatus(results.sections.functions) ? 'success' : 'error') + '">';
            html += '<h3>⚙️ Fonctions disponibles</h3>';
            html += '<pre>' + JSON.stringify(results.sections.functions, null, 2) + '</pre>';
            html += '</div>';

            // Section hooks
            html += '<div class="diagnostic-section info">';
            html += '<h3>🎣 Hooks WordPress</h3>';
            html += '<pre>' + JSON.stringify(results.sections.hooks, null, 2) + '</pre>';
            html += '</div>';

            // Section erreurs
            html += '<div class="diagnostic-section info">';
            html += '<h3>🐛 Configuration des erreurs PHP</h3>';
            html += '<pre>' + JSON.stringify(results.sections.errors, null, 2) + '</pre>';
            html += '</div>';

            resultsDiv.innerHTML = html;
        }

        function checkSectionStatus(section) {
            // Logique simple pour déterminer si une section est "OK"
            if (isObject(section)) {
                for (let key in section) {
                    if (section[key] === false || section[key] === null) {
                        return false;
                    }
                    if (typeof section[key] === 'object' && !checkSectionStatus(section[key])) {
                        return false;
                    }
                }
                return true;
            }
            return Boolean(section);
        }

        function isObject(obj) {
            return obj !== null && typeof obj === 'object';
        }
        </script>
    </body>
    </html>
    <?php
}

// Hook pour ajouter le menu de diagnostic
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Diagnostic PDF Builder',
        'Diagnostic PDF Builder',
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_builder_diagnostic_page'
    );
});
?>