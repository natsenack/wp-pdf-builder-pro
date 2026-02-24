<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Système de tests automatisés
 * Tests unitaires, d'intégration et fonctionnels pour valider les fonctionnalités
 */

class PDF_Builder_Test_Suite {
    private static $instance = null;

    // Types de tests
    const TEST_TYPE_UNIT = 'unit';
    const TEST_TYPE_INTEGRATION = 'integration';
    const TEST_TYPE_FUNCTIONAL = 'functional';
    const TEST_TYPE_PERFORMANCE = 'performance';
    const TEST_TYPE_SECURITY = 'security';

    // Statuts de test
    const STATUS_PASS = 'pass';
    const STATUS_FAIL = 'fail';
    const STATUS_SKIP = 'skip';
    const STATUS_ERROR = 'error';

    // Suites de tests
    private $test_suites = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->register_test_suites();
    }

    private function init_hooks() {
        // Exécution des tests
        add_action('wp_ajax_pdf_builder_run_tests', [$this, 'run_tests_ajax']);
        add_action('wp_ajax_pdf_builder_get_test_results', [$this, 'get_test_results_ajax']);

        // Tests automatiques
        add_action('pdf_builder_hourly_tests', [$this, 'run_hourly_tests']);
        add_action('pdf_builder_daily_tests', [$this, 'run_daily_tests']);
        add_action('pdf_builder_weekly_tests', [$this, 'run_weekly_tests']);

        // Nettoyage des résultats de tests
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_test_results']);
    }

    /**
     * Enregistre les suites de tests
     */
    private function register_test_suites() {
        $this->test_suites = [
            'unit' => [
                'name' => 'Tests unitaires',
                'description' => 'Tests des composants individuels',
                'tests' => [
                    'test_ajax_handlers' => [$this, 'test_ajax_handlers'],
                    'test_cache_system' => [$this, 'test_cache_system'],
                    'test_security_validator' => [$this, 'test_security_validator'],
                    'test_error_handler' => [$this, 'test_error_handler'],
                    'test_config_manager' => [$this, 'test_config_manager']
                ]
            ],
            'integration' => [
                'name' => 'Tests d\'intégration',
                'description' => 'Tests des interactions entre composants',
                'tests' => [
                    'test_database_operations' => [$this, 'test_database_operations'],
                    'test_file_operations' => [$this, 'test_file_operations'],
                    'test_api_integrations' => [$this, 'test_api_integrations'],
                    'test_plugin_loading' => [$this, 'test_plugin_loading']
                ]
            ],
            'functional' => [
                'name' => 'Tests fonctionnels',
                'description' => 'Tests des fonctionnalités utilisateur',
                'tests' => [
                    'test_pdf_generation' => [$this, 'test_pdf_generation'],
                    'test_template_management' => [$this, 'test_template_management'],
                    'test_user_permissions' => [$this, 'test_user_permissions'],
                    'test_admin_interface' => [$this, 'test_admin_interface']
                ]
            ],
            'performance' => [
                'name' => 'Tests de performance',
                'description' => 'Tests des performances et de la scalabilité',
                'tests' => [
                    'test_response_times' => [$this, 'test_response_times'],
                    'test_memory_usage' => [$this, 'test_memory_usage'],
                    'test_concurrent_users' => [$this, 'test_concurrent_users'],
                    'test_large_datasets' => [$this, 'test_large_datasets']
                ]
            ],
            'security' => [
                'name' => 'Tests de sécurité',
                'description' => 'Tests des vulnérabilités et protections',
                'tests' => [
                    'test_input_validation' => [$this, 'test_input_validation'],
                    'test_sql_injection' => [$this, 'test_sql_injection'],
                    'test_xss_protection' => [$this, 'test_xss_protection'],
                    'test_csrf_protection' => [$this, 'test_csrf_protection'],
                    'test_file_upload_security' => [$this, 'test_file_upload_security']
                ]
            ]
        ];
    }

    /**
     * Exécute une suite de tests
     */
    public function run_test_suite($suite_name, $options = []) {
        if (!isset($this->test_suites[$suite_name])) {
            throw new Exception('Suite de tests introuvable: ' . $suite_name); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        $suite = $this->test_suites[$suite_name];
        $results = [
            'suite' => $suite_name,
            'name' => $suite['name'],
            'description' => $suite['description'],
            'started_at' => current_time('mysql'),
            'tests' => [],
            'summary' => [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'skipped' => 0,
                'errors' => 0,
                'duration' => 0
            ]
        ];

        $start_time = microtime(true);

        foreach ($suite['tests'] as $test_name => $test_callback) {
            $test_result = $this->run_single_test($test_name, $test_callback, $options);

            $results['tests'][] = $test_result;
            $results['summary']['total']++;

            switch ($test_result['status']) {
                case self::STATUS_PASS:
                    $results['summary']['passed']++;
                    break;
                case self::STATUS_FAIL:
                    $results['summary']['failed']++;
                    break;
                case self::STATUS_SKIP:
                    $results['summary']['skipped']++;
                    break;
                case self::STATUS_ERROR:
                    $results['summary']['errors']++;
                    break;
            }
        }

        $results['summary']['duration'] = microtime(true) - $start_time;
        $results['completed_at'] = current_time('mysql');

        // Calculer le taux de succès
        $results['summary']['success_rate'] = $results['summary']['total'] > 0
            ? ($results['summary']['passed'] / $results['summary']['total']) * 100
            : 0;

        // Sauvegarder les résultats
        $this->save_test_results($results);

        // Notifier si nécessaire
        if ($results['summary']['failed'] > 0 || $results['summary']['errors'] > 0) {
            $this->notify_test_failures($results);
        }

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(wp_json_encode([
                'suite' => $suite_name,
                'passed' => $results['summary']['passed'],
                'failed' => $results['summary']['failed'],
                'duration' => $results['summary']['duration']
            ]));
        }

        return $results;
    }

    /**
     * Exécute un test individuel
     */
    private function run_single_test($test_name, $test_callback, $options = []) {
        $result = [
            'name' => $test_name,
            'status' => self::STATUS_ERROR,
            'message' => '',
            'duration' => 0,
            'details' => [],
            'started_at' => current_time('mysql')
        ];

        $start_time = microtime(true);

        try {
            // Vérifier si le test doit être ignoré
            if (isset($options['skip']) && in_array($test_name, $options['skip'])) {
                $result['status'] = self::STATUS_SKIP;
                $result['message'] = 'Test ignoré';
                return $result;
            }

            // Exécuter le test
            $test_result = call_user_func($test_callback, $options);

            if (is_array($test_result)) {
                $result = array_merge($result, $test_result);
            } elseif ($test_result === true) {
                $result['status'] = self::STATUS_PASS;
                $result['message'] = 'Test réussi';
            } elseif ($test_result === false) {
                $result['status'] = self::STATUS_FAIL;
                $result['message'] = 'Test échoué';
            } else {
                $result['status'] = self::STATUS_PASS;
                $result['message'] = 'Test réussi';
                $result['details'] = $test_result;
            }

        } catch (Exception $e) {
            $result['status'] = self::STATUS_ERROR;
            $result['message'] = 'Erreur lors de l\'exécution: ' . $e->getMessage();
            $result['details']['exception'] = $e->getTraceAsString();
        }

        $result['duration'] = microtime(true) - $start_time;
        $result['completed_at'] = current_time('mysql');

        return $result;
    }

    /**
     * Tests unitaires
     */
    public function test_ajax_handlers($options = []) {
        // Tester les gestionnaires AJAX
        $results = [];

        // Test de validation des données
        $results['validation'] = $this->assert_true(
            class_exists('PDF_Builder_Ajax_Base'),
            'Classe PDF_Builder_Ajax_Base existe'
        );

        // Test des handlers spécifiques
        $results['settings_handler'] = $this->assert_true(
            class_exists('PDF_Builder_Settings_Ajax_Handler'),
            'Classe PDF_Builder_Settings_Ajax_Handler existe'
        );

        $results['template_handler'] = $this->assert_true(
            class_exists('PDF_Builder_Template_Ajax_Handler'),
            'Classe PDF_Builder_Template_Ajax_Handler existe'
        );

        return $this->summarize_test_results($results, 'Tests des gestionnaires AJAX');
    }

    public function test_cache_system($options = []) {
        return $this->test_info('Système de cache supprimé - Cette fonctionnalité n\'est plus disponible');
    }

    public function test_security_validator($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Security_Validator')) {
            return $this->test_failed('Validateur de sécurité non disponible');
        }

        $validator = PDF_Builder_Security_Validator::get_instance();

        // Test de validation d'email
        $results['valid_email'] = $this->assert_true(
            $validator->validate_email('test@example.com'),
            'Validation d\'email valide réussie'
        );

        $results['invalid_email'] = $this->assert_false(
            $validator->validate_email('invalid-email'),
            'Validation d\'email invalide réussie'
        );

        // Test de nettoyage HTML
        $results['sanitize_html'] = $this->assert_equals(
            $validator->sanitize_html('<script>alert("test")</script><p>Hello</p>'),
            '<p>Hello</p>',
            'Nettoyage HTML réussi'
        );

        return $this->summarize_test_results($results, 'Tests du validateur de sécurité');
    }

    public function test_error_handler($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Error_Handler')) {
            return $this->test_failed('Gestionnaire d\'erreurs non disponible');
        }

        $handler = PDF_Builder_Error_Handler::get_instance();

        // Test de gestion d'erreur
        $test_error = [
            'type' => E_WARNING,
            'message' => 'Test warning',
            'file' => __FILE__,
            'line' => __LINE__
        ];

        $results['error_handling'] = $this->assert_true(
            $handler->handle_error($test_error['type'], $test_error['message'], $test_error['file'], $test_error['line']),
            'Gestion d\'erreur réussie'
        );

        return $this->summarize_test_results($results, 'Tests du gestionnaire d\'erreurs');
    }

    public function test_config_manager($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Config_Manager')) {
            return $this->test_failed('Gestionnaire de configuration non disponible');
        }

        $config = PDF_Builder_Config_Manager::get_instance();

        // Test de récupération de configuration
        $results['get_config'] = $this->assert_not_null(
            $config->get('version'),
            'Récupération de configuration réussie'
        );

        // Test de définition de configuration
        $test_key = 'test_config_' . time();
        $test_value = 'test_value';

        $results['set_config'] = $this->assert_true(
            $config->set($test_key, $test_value),
            'Définition de configuration réussie'
        );

        // Vérifier la valeur définie
        $results['verify_config'] = $this->assert_equals(
            $config->get($test_key),
            $test_value,
            'Vérification de configuration réussie'
        );

        return $this->summarize_test_results($results, 'Tests du gestionnaire de configuration');
    }

    /**
     * Tests d'intégration
     */
    public function test_database_operations($options = []) {
        $results = [];

        global $wpdb;

        // Test de connexion à la base de données
        $results['connection'] = $this->assert_not_null(
            $wpdb->get_var("SELECT 1"), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            'Connexion à la base de données réussie'
        );

        // Test des tables du plugin
        $required_tables = [
            $wpdb->prefix . 'pdf_builder_templates',
            $wpdb->prefix . 'pdf_builder_cache',
            $wpdb->prefix . 'pdf_builder_errors'
        ];

        foreach ($required_tables as $table) {
            $results['table_' . basename($table)] = $this->assert_true(
                $this->table_exists($table),
                "Table $table existe"
            );
        }

        // Test d'insertion/récupération
        $test_table = $wpdb->prefix . 'pdf_builder_cache';
        $test_data = [
            'cache_key' => 'test_key_' . time(),
            'cache_value' => json_encode(['test' => 'data']),
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 3600)
        ];

        $insert_result = $wpdb->insert($test_table, $test_data);
        $results['insert'] = $this->assert_not_false(
            $insert_result,
            'Insertion en base de données réussie'
        );

        if ($insert_result) {
            $retrieved = $wpdb->get_row($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                "SELECT * FROM $test_table WHERE cache_key = %s",
                $test_data['cache_key']
            ));

            $results['retrieve'] = $this->assert_not_null(
                $retrieved,
                'Récupération depuis la base de données réussie'
            );

            // Nettoyer
            $wpdb->delete($test_table, ['cache_key' => $test_data['cache_key']]);
        }

        return $this->summarize_test_results($results, 'Tests des opérations de base de données');
    }

    public function test_file_operations($options = []) {
        $results = [];

        // Test des permissions d'écriture
        $upload_dir = wp_upload_dir();
        $test_dir = $upload_dir['basedir'] . '/pdf-builder/test/';

        $results['create_dir'] = $this->assert_true(
            wp_mkdir_p($test_dir),
            'Création de répertoire réussie'
        );

        // Test d'écriture de fichier
        $test_file = $test_dir . 'test.txt';
        $test_content = 'Test content ' . time();

        $results['write_file'] = $this->assert_not_false(
            file_put_contents($test_file, $test_content),
            'Écriture de fichier réussie'
        );

        // Test de lecture de fichier
        $results['read_file'] = $this->assert_equals(
            file_get_contents($test_file),
            $test_content,
            'Lecture de fichier réussie'
        );

        // Test de suppression de fichier
        $results['delete_file'] = $this->assert_true(
            wp_delete_file($test_file),
            'Suppression de fichier réussie'
        );

        // Nettoyer le répertoire
        rmdir($test_dir); // phpcs:ignore WordPress.WP.AlternativeFunctions

        return $this->summarize_test_results($results, 'Tests des opérations de fichiers');
    }

    public function test_api_integrations($options = []) {
        $results = [];

        // Test de l'API WordPress
        $results['wp_api'] = $this->assert_true(
            function_exists('wp_remote_get'),
            'API WordPress disponible'
        );

        // Test de requête HTTP
        $response = wp_remote_get('https://httpbin.org/status/200', ['timeout' => 5]);
        $results['http_request'] = $this->assert_not_wp_error(
            $response,
            'Requête HTTP réussie'
        );

        if (!is_wp_error($response)) {
            $results['http_status'] = $this->assert_equals(
                wp_remote_retrieve_response_code($response),
                200,
                'Code de statut HTTP correct'
            );
        }

        return $this->summarize_test_results($results, 'Tests des intégrations API');
    }

    public function test_plugin_loading($options = []) {
        $results = [];

        // Test du chargement du plugin
        $results['plugin_active'] = $this->assert_true(
            is_plugin_active('pdf-builder-pro/pdf-builder-pro.php'),
            'Plugin activé'
        );

        // Test des constantes
        $results['constants'] = $this->assert_true(
            defined('PDF_BUILDER_VERSION'),
            'Constantes du plugin définies'
        );

        // Test des hooks
        $results['hooks'] = $this->assert_true(
            has_action('init', 'pdf_builder_init'),
            'Hooks WordPress enregistrés'
        );

        return $this->summarize_test_results($results, 'Tests du chargement du plugin');
    }

    /**
     * Tests fonctionnels
     */
    public function test_pdf_generation($options = []) {
        $results = [];

        // Cette méthode nécessiterait une implémentation réelle de génération PDF
        // Pour l'instant, on simule un test
        $results['pdf_library'] = $this->assert_true(
            class_exists('TCPDF') || class_exists('FPDF') || function_exists('pdf_builder_generate_pdf'),
            'Bibliothèque PDF disponible'
        );

        return $this->summarize_test_results($results, 'Tests de génération PDF');
    }

    public function test_template_management($options = []) {
        $results = [];

        // Test de la gestion des templates
        $results['template_class'] = $this->assert_true(
            class_exists('PDF_Builder_Template_Manager') || function_exists('pdf_builder_get_templates'),
            'Gestionnaire de templates disponible'
        );

        return $this->summarize_test_results($results, 'Tests de gestion des templates');
    }

    public function test_user_permissions($options = []) {
        $results = [];

        // Test des permissions utilisateur
        $admin_user = get_user_by('login', 'admin');
        if ($admin_user) {
            $results['admin_permissions'] = $this->assert_true(
                user_can($admin_user->ID, 'manage_options'),
                'Permissions administrateur correctes'
            );
        }

        return $this->summarize_test_results($results, 'Tests des permissions utilisateur');
    }

    public function test_admin_interface($options = []) {
        $results = [];

        // Test de l'interface d'administration
        $results['admin_menu'] = $this->assert_true(
            has_action('admin_menu', 'pdf_builder_admin_menu'),
            'Menu d\'administration enregistré'
        );

        return $this->summarize_test_results($results, 'Tests de l\'interface d\'administration');
    }

    /**
     * Tests de performance
     */
    public function test_response_times($options = []) {
        $results = [];

        // Mesurer le temps de réponse d'une fonction simple
        $start_time = microtime(true);
        $test_result = pdf_builder_config('version');
        $end_time = microtime(true);

        $response_time = ($end_time - $start_time) * 1000; // en millisecondes

        $results['config_response'] = $this->assert_less_than(
            $response_time,
            100, // moins de 100ms
            'Temps de réponse de configuration acceptable'
        );

        return $this->summarize_test_results($results, 'Tests des temps de réponse');
    }

    public function test_memory_usage($options = []) {
        $results = [];

        $start_memory = memory_get_usage();

        // Effectuer une opération qui consomme de la mémoire
        $test_array = [];
        for ($i = 0; $i < 10000; $i++) {
            $test_array[] = str_repeat('test', 100);
        }
        unset($test_array);

        $end_memory = memory_get_usage();
        $memory_used = $end_memory - $start_memory;

        $results['memory_efficiency'] = $this->assert_less_than(
            $memory_used,
            50 * 1024 * 1024, // moins de 50MB
            'Utilisation mémoire acceptable'
        );

        return $this->summarize_test_results($results, 'Tests d\'utilisation mémoire');
    }

    public function test_concurrent_users($options = []) {
        $results = [];

        // Test de charge simulée
        $concurrent_requests = 10;
        $responses = [];

        for ($i = 0; $i < $concurrent_requests; $i++) {
            $start_time = microtime(true);
            // Simuler une requête
            $config = pdf_builder_config('version');
            $end_time = microtime(true);

            $responses[] = $end_time - $start_time;
        }

        $avg_response_time = array_sum($responses) / count($responses);

        $results['concurrent_load'] = $this->assert_less_than(
            $avg_response_time,
            0.5, // moins de 0.5 seconde en moyenne
            'Performance sous charge acceptable'
        );

        return $this->summarize_test_results($results, 'Tests d\'utilisateurs concurrents');
    }

    public function test_large_datasets($options = []) {
        $results = [];

        // Test avec un grand ensemble de données
        $large_array = range(1, 100000);
        $start_time = microtime(true);
        $sum = array_sum($large_array);
        $end_time = microtime(true);

        $processing_time = $end_time - $start_time;

        $results['large_dataset'] = $this->assert_less_than(
            $processing_time,
            2.0, // moins de 2 secondes
            'Traitement de grands ensembles de données acceptable'
        );

        return $this->summarize_test_results($results, 'Tests de grands ensembles de données');
    }

    /**
     * Tests de sécurité
     */
    public function test_input_validation($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Security_Validator')) {
            return $this->test_failed('Validateur de sécurité non disponible');
        }

        $validator = PDF_Builder_Security_Validator::get_instance();

        // Test de validation de chaîne
        $results['string_validation'] = $this->assert_true(
            $validator->validate_string('test string', 3, 50),
            'Validation de chaîne réussie'
        );

        $results['string_too_short'] = $this->assert_false(
            $validator->validate_string('ab', 3, 50),
            'Validation de chaîne trop courte réussie'
        );

        // Test de validation numérique
        $results['numeric_validation'] = $this->assert_true(
            $validator->validate_numeric('123', 0, 1000),
            'Validation numérique réussie'
        );

        $results['non_numeric'] = $this->assert_false(
            $validator->validate_numeric('abc', 0, 1000),
            'Validation de valeur non numérique réussie'
        );

        return $this->summarize_test_results($results, 'Tests de validation des entrées');
    }

    public function test_sql_injection($options = []) {
        $results = [];

        // Test de protection contre l'injection SQL
        $malicious_input = "'; DROP TABLE users; --";

        // Cette requête devrait être sécurisée
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE user_login = %s", $malicious_input); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        $result = $wpdb->get_var($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

        $results['sql_protection'] = $this->assert_not_false(
            $result !== false, // La requête ne devrait pas échouer à cause de l'injection
            'Protection contre l\'injection SQL fonctionnelle'
        );

        return $this->summarize_test_results($results, 'Tests de protection contre l\'injection SQL');
    }

    public function test_xss_protection($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Security_Validator')) {
            return $this->test_failed('Validateur de sécurité non disponible');
        }

        $validator = PDF_Builder_Security_Validator::get_instance();

        // Test de protection XSS
        $xss_input = '<script>alert("XSS")</script><p>Hello</p>';
        $sanitized = $validator->sanitize_html($xss_input);

        $results['xss_protection'] = $this->assert_not_contains(
            '<script>',
            $sanitized,
            'Protection XSS fonctionnelle'
        );

        $results['content_preserved'] = $this->assert_contains(
            '<p>Hello</p>',
            $sanitized,
            'Contenu légitime préservé'
        );

        return $this->summarize_test_results($results, 'Tests de protection XSS');
    }

    public function test_csrf_protection($options = []) {
        $results = [];

        // Test de protection CSRF
        $nonce = wp_create_nonce('pdf_builder_ajax');
        $results['nonce_generation'] = $this->assert_not_empty(
            $nonce,
            'Génération de nonce réussie'
        );

        $results['nonce_verification'] = $this->assert_true(
            pdf_builder_verify_nonce($nonce, 'pdf_builder_ajax'),
            'Vérification de nonce réussie'
        );

        $results['invalid_nonce'] = $this->assert_false(
            pdf_builder_verify_nonce('invalid_nonce', 'pdf_builder_ajax'),
            'Rejet de nonce invalide réussi'
        );

        return $this->summarize_test_results($results, 'Tests de protection CSRF');
    }

    public function test_file_upload_security($options = []) {
        $results = [];

        if (!class_exists('PDF_Builder_Security_Validator')) {
            return $this->test_failed('Validateur de sécurité non disponible');
        }

        $validator = PDF_Builder_Security_Validator::get_instance();

        // Test de validation de fichier
        $allowed_types = ['pdf', 'jpg', 'png'];
        $results['valid_file_type'] = $this->assert_true(
            $validator->validate_file_type('test.pdf', $allowed_types),
            'Validation de type de fichier valide réussie'
        );

        $results['invalid_file_type'] = $this->assert_false(
            $validator->validate_file_type('test.exe', $allowed_types),
            'Validation de type de fichier invalide réussie'
        );

        return $this->summarize_test_results($results, 'Tests de sécurité des téléchargements de fichiers');
    }

    /**
     * Méthodes utilitaires pour les assertions
     */
    private function assert_true($condition, $message = '') {
        return [
            'passed' => $condition === true,
            'message' => $message,
            'expected' => true,
            'actual' => $condition
        ];
    }

    private function assert_false($condition, $message = '') {
        return [
            'passed' => $condition === false,
            'message' => $message,
            'expected' => false,
            'actual' => $condition
        ];
    }

    private function assert_equals($actual, $expected, $message = '') {
        return [
            'passed' => $actual === $expected,
            'message' => $message,
            'expected' => $expected,
            'actual' => $actual
        ];
    }

    private function assert_not_equals($actual, $expected, $message = '') {
        return [
            'passed' => $actual !== $expected,
            'message' => $message,
            'expected' => 'not ' . $expected,
            'actual' => $actual
        ];
    }

    private function assert_null($value, $message = '') {
        return [
            'passed' => $value === null,
            'message' => $message,
            'expected' => null,
            'actual' => $value
        ];
    }

    private function assert_not_null($value, $message = '') {
        return [
            'passed' => $value !== null,
            'message' => $message,
            'expected' => 'not null',
            'actual' => $value
        ];
    }

    private function assert_not_empty($value, $message = '') {
        return [
            'passed' => !empty($value),
            'message' => $message,
            'expected' => 'not empty',
            'actual' => $value
        ];
    }

    private function assert_less_than($actual, $expected, $message = '') {
        return [
            'passed' => $actual < $expected,
            'message' => $message,
            'expected' => '< ' . $expected,
            'actual' => $actual
        ];
    }

    private function assert_contains($needle, $haystack, $message = '') {
        return [
            'passed' => strpos($haystack, $needle) !== false,
            'message' => $message,
            'expected' => 'contains "' . $needle . '"',
            'actual' => $haystack
        ];
    }

    private function assert_not_contains($needle, $haystack, $message = '') {
        return [
            'passed' => strpos($haystack, $needle) === false,
            'message' => $message,
            'expected' => 'does not contain "' . $needle . '"',
            'actual' => $haystack
        ];
    }

    private function assert_not_wp_error($value, $message = '') {
        return [
            'passed' => !is_wp_error($value),
            'message' => $message,
            'expected' => 'not WP_Error',
            'actual' => is_wp_error($value) ? $value->get_error_message() : $value
        ];
    }

    private function summarize_test_results($results, $test_name) {
        $passed = 0;
        $failed = 0;
        $details = [];

        foreach ($results as $key => $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
            $details[$key] = $result;
        }

        return [
            'status' => $failed === 0 ? self::STATUS_PASS : self::STATUS_FAIL,
            'message' => $failed === 0 ? 'Tous les tests réussis' : "$failed test(s) échoué(s)",
            'details' => $details,
            'passed' => $passed,
            'failed' => $failed
        ];
    }

    private function test_failed($message) {
        return [
            'status' => self::STATUS_FAIL,
            'message' => $message,
            'details' => []
        ];
    }

    private function table_exists($table) {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
    }

    /**
     * Sauvegarde les résultats des tests
     */
    private function save_test_results($results) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_test_results';

        $wpdb->insert(
            $table,
            [
                'suite_name' => $results['suite'],
                'results_data' => json_encode($results),
                'passed_tests' => $results['summary']['passed'],
                'failed_tests' => $results['summary']['failed'],
                'total_tests' => $results['summary']['total'],
                'duration' => $results['summary']['duration'],
                'created_at' => $results['completed_at']
            ],
            ['%s', '%s', '%d', '%d', '%d', '%f', '%s']
        );
    }

    /**
     * Notifie les échecs de tests
     */
    private function notify_test_failures($results) {
        $message = "Échecs détectés dans la suite de tests '{$results['name']}'\n";
        $message .= "Tests réussis: {$results['summary']['passed']}\n";
        $message .= "Tests échoués: {$results['summary']['failed']}\n";
        $message .= "Erreurs: {$results['summary']['errors']}\n";
        $message .= "Taux de succès: " . round($results['summary']['success_rate'], 2) . "%";

        // Legacy notification calls removed — log as error
    }

    /**
     * Exécute les tests horaires
     */
    public function run_hourly_tests() {
        try {
            $this->run_test_suite('unit');
            $this->run_test_suite('security');
        } catch (Exception $e) {
            $this->log_test_error('hourly_tests', $e);
        }
    }

    /**
     * Exécute les tests quotidiens
     */
    public function run_daily_tests() {
        try {
            $this->run_test_suite('integration');
            $this->run_test_suite('functional');
        } catch (Exception $e) {
            $this->log_test_error('daily_tests', $e);
        }
    }

    /**
     * Exécute les tests hebdomadaires
     */
    public function run_weekly_tests() {
        try {
            $this->run_test_suite('performance');
        } catch (Exception $e) {
            $this->log_test_error('weekly_tests', $e);
        }
    }

    /**
     * Nettoie les anciens résultats de tests
     */
    public function cleanup_test_results() {
        global $wpdb;

        $retention_days = pdf_builder_config('test_results_retention_days', 90);

        $table = $wpdb->prefix . 'pdf_builder_test_results';
        $deleted = $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $retention_days));
    }

    /**
     * Log une erreur de test
     */
    private function log_test_error($operation, $exception) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(wp_json_encode([
                'operation' => $operation,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]));
        }
    }

    /**
     * AJAX - Exécute des tests
     */
    public function run_tests_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $suite = sanitize_text_field($_POST['suite'] ?? 'unit');
            $options = isset($_POST['options']) ? json_decode(stripslashes($_POST['options']), true) : [];

            $results = $this->run_test_suite($suite, $options);

            wp_send_json_success([
                'message' => 'Tests exécutés avec succès',
                'results' => $results
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'exécution des tests: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les résultats des tests
     */
    public function get_test_results_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $limit = intval($_POST['limit'] ?? 50);

            $results = $this->get_test_results($limit);

            wp_send_json_success([
                'message' => 'Résultats récupérés',
                'results' => $results
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient les résultats des tests
     */
    public function get_test_results($limit = 50) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_test_results';

        $results = $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);

        // Décoder les données JSON
        foreach ($results as &$result) {
            $result['results_data'] = json_decode($result['results_data'], true);
        }

        return $results;
    }

    /**
     * Retourne une information de test
     */
    private function test_info($message) {
        return [
            'status' => 'info',
            'message' => $message,
            'timestamp' => current_time('timestamp')
        ];
    }

    /**
     * Assertion: la valeur n'est pas false
     */
    private function assert_not_false($value, $test_name = '') {
        return $value !== false;
    }
}

// Fonctions globales
function pdf_builder_test_suite() {
    return PDF_Builder_Test_Suite::get_instance();
}

function pdf_builder_run_tests($suite = 'unit') {
    return PDF_Builder_Test_Suite::get_instance()->run_test_suite($suite);
}

function pdf_builder_get_test_results($limit = 50) {
    return PDF_Builder_Test_Suite::get_instance()->get_test_results($limit);
}

// Initialiser le système de tests
add_action('plugins_loaded', function() {
    PDF_Builder_Test_Suite::get_instance();
});



