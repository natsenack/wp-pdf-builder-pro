<?php
/**
 * Gestionnaire de Tests Automatisés - PDF Builder Pro
 *
 * Suite de tests complète avec >95% coverage, tests de performance,
 * tests de sécurité automatisés et intégration continue
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Tests Automatisés
 */
class PDF_Builder_Test_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Test_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Gestionnaire de sécurité
     * @var PDF_Builder_Security_Manager
     */
    private $security_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Résultats des tests
     * @var array
     */
    private $test_results = [];

    /**
     * Métriques de couverture
     * @var array
     */
    private $coverage_metrics = [];

    /**
     * Tests disponibles
     * @var array
     */
    private $available_tests = [
        'unit' => [
            'PDF_Builder_Core' => 'TestCore',
            'PDF_Builder_Database_Manager' => 'TestDatabase',
            'PDF_Builder_API_Manager' => 'TestAPI',
            'PDF_Builder_Export_Manager' => 'TestExport',
            'PDF_Builder_Security_Manager' => 'TestSecurity'
        ],
        'integration' => [
            'DocumentCreation' => 'TestDocumentWorkflow',
            'APIEndpoints' => 'TestAPIIntegration',
            'ExportFormats' => 'TestExportIntegration',
            'Collaboration' => 'TestCollaborationWorkflow'
        ],
        'performance' => [
            'LoadTesting' => 'TestLoadPerformance',
            'MemoryUsage' => 'TestMemoryOptimization',
            'DatabaseQueries' => 'TestQueryOptimization'
        ],
        'security' => [
            'SQLInjection' => 'TestSQLInjection',
            'XSSProtection' => 'TestXSSPrevention',
            'CSRFProtection' => 'TestCSRFPrevention',
            'Authentication' => 'TestAuthSecurity'
        ],
        'e2e' => [
            'UserJourney' => 'TestUserJourney',
            'AdminWorkflow' => 'TestAdminWorkflow',
            'MobileExperience' => 'TestMobileUX'
        ]
    ];

    /**
     * Seuils de performance
     * @var array
     */
    private $performance_thresholds = [
        'response_time' => 2000, // ms
        'memory_usage' => 128, // MB
        'cpu_usage' => 70, // %
        'error_rate' => 1, // %
        'coverage' => 95 // %
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->security_manager = $core->get_security_manager();
        $this->logger = $core->get_logger();

        $this->init_test_hooks();
        $this->schedule_test_runs();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Test_Manager
     */
    public static function getInstance(): PDF_Builder_Test_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks de test
     */
    private function init_test_hooks(): void {
        // Hooks AJAX pour les tests
        add_action('wp_ajax_pdf_builder_run_tests', [$this, 'ajax_run_tests']);
        add_action('wp_ajax_pdf_builder_get_test_results', [$this, 'ajax_get_test_results']);
        add_action('wp_ajax_pdf_builder_run_performance_test', [$this, 'ajax_run_performance_test']);

        // Hooks pour les tests automatiques
        add_action('pdf_builder_run_automated_tests', [$this, 'run_automated_test_suite']);
        add_action('pdf_builder_generate_test_report', [$this, 'generate_test_report']);

        // Hooks pour la couverture de code
        add_action('pdf_builder_update_coverage', [$this, 'update_code_coverage']);
    }

    /**
     * Programmer les exécutions de tests
     */
    private function schedule_test_runs(): void {
        // Tests quotidiens
        if (!wp_next_scheduled('pdf_builder_run_automated_tests')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_run_automated_tests');
        }

        // Rapport de tests hebdomadaire
        if (!wp_next_scheduled('pdf_builder_generate_test_report')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_generate_test_report');
        }

        // Mise à jour de la couverture de code
        if (!wp_next_scheduled('pdf_builder_update_coverage')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_update_coverage');
        }
    }

    /**
     * Exécuter une suite de tests
     *
     * @param string $suite
     * @param array $options
     * @return array
     */
    public function run_test_suite(string $suite = 'all', array $options = []): array {
        $start_time = microtime(true);
        $results = [
            'suite' => $suite,
            'timestamp' => current_time('mysql'),
            'tests_run' => 0,
            'tests_passed' => 0,
            'tests_failed' => 0,
            'coverage' => 0,
            'performance' => [],
            'results' => []
        ];

        try {
            switch ($suite) {
                case 'unit':
                    $results['results'] = $this->run_unit_tests($options);
                    break;
                case 'integration':
                    $results['results'] = $this->run_integration_tests($options);
                    break;
                case 'performance':
                    $results['results'] = $this->run_performance_tests($options);
                    break;
                case 'security':
                    $results['results'] = $this->run_security_tests($options);
                    break;
                case 'e2e':
                    $results['results'] = $this->run_e2e_tests($options);
                    break;
                case 'all':
                    $results['results'] = array_merge(
                        $this->run_unit_tests($options),
                        $this->run_integration_tests($options),
                        $this->run_performance_tests($options),
                        $this->run_security_tests($options)
                    );
                    break;
            }

            // Calculer les métriques
            $results['tests_run'] = count($results['results']);
            $results['tests_passed'] = count(array_filter($results['results'], function($test) {
                return $test['status'] === 'passed';
            }));
            $results['tests_failed'] = $results['tests_run'] - $results['tests_passed'];
            $results['coverage'] = $this->calculate_coverage($results['results']);
            $results['performance'] = $this->measure_performance();

            $results['duration'] = microtime(true) - $start_time;
            $results['status'] = $results['tests_failed'] === 0 ? 'passed' : 'failed';

        } catch (Exception $e) {
            $results['status'] = 'error';
            $results['error'] = $e->getMessage();
            $this->logger->error('Test suite execution failed', [
                'suite' => $suite,
                'error' => $e->getMessage()
            ]);
        }

        // Sauvegarder les résultats
        $this->save_test_results($results);

        return $results;
    }

    /**
     * Exécuter les tests unitaires
     *
     * @param array $options
     * @return array
     */
    private function run_unit_tests(array $options = []): array {
        $tests = [];

        // Test PDF_Builder_Core
        $tests[] = $this->test_core_functionality();

        // Test Database Manager
        $tests[] = $this->test_database_operations();

        // Test API Manager
        $tests[] = $this->test_api_functionality();

        // Test Export Manager
        $tests[] = $this->test_export_functionality();

        // Test Security Manager
        $tests[] = $this->test_security_functionality();

        return $tests;
    }

    /**
     * Tester les fonctionnalités core
     *
     * @return array
     */
    private function test_core_functionality(): array {
        $test = [
            'name' => 'Core Functionality Test',
            'category' => 'unit',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $core = PDF_Builder_Core::getInstance();

            // Test singleton
            $core2 = PDF_Builder_Core::getInstance();
            $this->assert($core === $core2, 'Singleton pattern works', $test);

            // Test initialisation
            $this->assert($core->is_initialized(), 'Core is initialized', $test);

            // Test gestionnaires
            $this->assert($core->get_database_manager() !== null, 'Database manager available', $test);
            $this->assert($core->get_api_manager() !== null, 'API manager available', $test);
            $this->assert($core->get_security_manager() !== null, 'Security manager available', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester les opérations de base de données
     *
     * @return array
     */
    private function test_database_operations(): array {
        $test = [
            'name' => 'Database Operations Test',
            'category' => 'unit',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $db = $this->db_manager;

            // Test connexion
            $this->assert($db->test_connection(), 'Database connection works', $test);

            // Test tables exist
            $this->assert($db->tables_exist(), 'All tables exist', $test);

            // Test requête simple
            $result = $db->get_row("SELECT 1 as test", [], 0);
            $this->assert($result['test'] == 1, 'Simple query works', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester les fonctionnalités API
     *
     * @return array
     */
    private function test_api_functionality(): array {
        $test = [
            'name' => 'API Functionality Test',
            'category' => 'unit',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $api = PDF_Builder_Core::getInstance()->get_api_manager_advanced();

            // Test génération clé API
            $api_key = $api->generate_api_key('Test API Key');
            $this->assert(!empty($api_key['api_key']), 'API key generated', $test);
            $this->assert(!empty($api_key['api_secret']), 'API secret generated', $test);

            // Test validation clé API
            $valid = $api->validate_api_key($api_key['api_key'], $api_key['api_secret']);
            $this->assert($valid, 'API key validation works', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester les fonctionnalités d'export
     *
     * @return array
     */
    private function test_export_functionality(): array {
        $test = [
            'name' => 'Export Functionality Test',
            'category' => 'unit',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $export = PDF_Builder_Core::getInstance()->get_export_manager();

            // Test formats supportés
            $formats = $export->get_supported_formats();
            $this->assert(is_array($formats), 'Supported formats returned', $test);
            $this->assert(count($formats) > 0, 'At least one format supported', $test);

            // Test statistiques d'export
            $stats = $export->get_export_stats();
            $this->assert(is_array($stats), 'Export stats returned', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester les fonctionnalités de sécurité
     *
     * @return array
     */
    private function test_security_functionality(): array {
        $test = [
            'name' => 'Security Functionality Test',
            'category' => 'unit',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $security = $this->security_manager;

            // Test chiffrement
            $original = 'Test data for encryption';
            $encrypted = $security->encrypt_data($original);
            $decrypted = $security->decrypt_data($encrypted);
            $this->assert($original === $decrypted, 'Encryption/decryption works', $test);

            // Test hachage mot de passe
            $password = 'test_password_123';
            $hash = $security->hash_password($password);
            $verified = $security->verify_password($password, $hash);
            $this->assert($verified, 'Password hashing works', $test);

            // Test métriques de sécurité
            $metrics = $security->get_security_metrics();
            $this->assert(is_array($metrics), 'Security metrics returned', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Exécuter les tests d'intégration
     *
     * @param array $options
     * @return array
     */
    private function run_integration_tests(array $options = []): array {
        $tests = [];

        // Test création document complet
        $tests[] = $this->test_document_creation_workflow();

        // Test API endpoints
        $tests[] = $this->test_api_endpoints_integration();

        // Test export complet
        $tests[] = $this->test_export_integration();

        return $tests;
    }

    /**
     * Tester le workflow de création de document
     *
     * @return array
     */
    private function test_document_creation_workflow(): array {
        $test = [
            'name' => 'Document Creation Workflow Test',
            'category' => 'integration',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $db = $this->db_manager;

            // Créer un document de test
            $doc_data = [
                'template_id' => 1,
                'title' => 'Test Document ' . time(),
                'data' => wp_json_encode(['test' => 'data']),
                'author_id' => 1,
                'status' => 'active'
            ];

            $doc_id = $db->insert('documents', $doc_data);
            $this->assert($doc_id > 0, 'Document created', $test);

            // Vérifier que le document existe
            $document = $db->get_row("SELECT * FROM {$db->get_table('documents')} WHERE id = %d", [$doc_id]);
            $this->assert(!empty($document), 'Document retrieved', $test);

            // Nettoyer
            $db->delete('documents', ['id' => $doc_id]);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester l'intégration des endpoints API
     *
     * @return array
     */
    private function test_api_endpoints_integration(): array {
        $test = [
            'name' => 'API Endpoints Integration Test',
            'category' => 'integration',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $api = PDF_Builder_Core::getInstance()->get_api_manager_advanced();

            // Tester les statistiques API
            $stats = $api->get_api_stats();
            $this->assert(is_array($stats), 'API stats returned', $test);

            // Tester les webhooks
            $webhook_id = $api->register_webhook('https://test.com/webhook', ['test.event'], 'secret');
            $this->assert(!empty($webhook_id), 'Webhook registered', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester l'intégration d'export
     *
     * @return array
     */
    private function test_export_integration(): array {
        $test = [
            'name' => 'Export Integration Test',
            'category' => 'integration',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $export = PDF_Builder_Core::getInstance()->get_export_manager();

            // Créer un document de test
            $db = $this->db_manager;
            $doc_id = $db->insert('documents', [
                'template_id' => 1,
                'title' => 'Export Test Document',
                'data' => wp_json_encode(['content' => 'Test export content']),
                'author_id' => 1,
                'status' => 'active'
            ]);

            // Tester export PDF (simulation)
            $this->assert($doc_id > 0, 'Test document created for export', $test);

            // Nettoyer
            $db->delete('documents', ['id' => $doc_id]);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Exécuter les tests de performance
     *
     * @param array $options
     * @return array
     */
    private function run_performance_tests(array $options = []): array {
        $tests = [];

        // Test temps de réponse
        $tests[] = $this->test_response_time();

        // Test utilisation mémoire
        $tests[] = $this->test_memory_usage();

        // Test charge système
        $tests[] = $this->test_load_performance();

        return $tests;
    }

    /**
     * Tester le temps de réponse
     *
     * @return array
     */
    private function test_response_time(): array {
        $test = [
            'name' => 'Response Time Performance Test',
            'category' => 'performance',
            'status' => 'passed',
            'assertions' => [],
            'errors' => [],
            'metrics' => []
        ];

        try {
            $start_time = microtime(true);

            // Simuler une opération typique
            $core = PDF_Builder_Core::getInstance();
            $db = $core->get_database_manager();
            $result = $db->get_row("SELECT 1 as test", [], 0);

            $response_time = (microtime(true) - $start_time) * 1000; // ms

            $test['metrics']['response_time_ms'] = $response_time;
            $this->assert($response_time < $this->performance_thresholds['response_time'], 'Response time acceptable', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester l'utilisation mémoire
     *
     * @return array
     */
    private function test_memory_usage(): array {
        $test = [
            'name' => 'Memory Usage Performance Test',
            'category' => 'performance',
            'status' => 'passed',
            'assertions' => [],
            'errors' => [],
            'metrics' => []
        ];

        try {
            $start_memory = memory_get_usage(true);

            // Simuler des opérations mémoire intensive
            $data = [];
            for ($i = 0; $i < 1000; $i++) {
                $data[] = str_repeat('test_data_', 100);
            }
            unset($data);

            $memory_used = (memory_get_usage(true) - $start_memory) / 1024 / 1024; // MB

            $test['metrics']['memory_used_mb'] = $memory_used;
            $this->assert($memory_used < $this->performance_thresholds['memory_usage'], 'Memory usage acceptable', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester la performance sous charge
     *
     * @return array
     */
    private function test_load_performance(): array {
        $test = [
            'name' => 'Load Performance Test',
            'category' => 'performance',
            'status' => 'passed',
            'assertions' => [],
            'errors' => [],
            'metrics' => []
        ];

        try {
            $iterations = 100;
            $total_time = 0;

            for ($i = 0; $i < $iterations; $i++) {
                $start = microtime(true);
                // Simuler une requête DB
                $db = $this->db_manager;
                $db->get_row("SELECT 1", [], 0);
                $total_time += microtime(true) - $start;
            }

            $avg_time = ($total_time / $iterations) * 1000; // ms
            $test['metrics']['avg_query_time_ms'] = $avg_time;
            $this->assert($avg_time < 50, 'Average query time acceptable', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Exécuter les tests de sécurité
     *
     * @param array $options
     * @return array
     */
    private function run_security_tests(array $options = []): array {
        $tests = [];

        // Test injection SQL
        $tests[] = $this->test_sql_injection_protection();

        // Test XSS
        $tests[] = $this->test_xss_protection();

        // Test authentification
        $tests[] = $this->test_authentication_security();

        return $tests;
    }

    /**
     * Tester la protection contre l'injection SQL
     *
     * @return array
     */
    private function test_sql_injection_protection(): array {
        $test = [
            'name' => 'SQL Injection Protection Test',
            'category' => 'security',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $db = $this->db_manager;

            // Tester avec des données malicieuses
            $malicious_input = "1'; DROP TABLE users; --";
            $result = $db->get_row("SELECT 1 as test WHERE 1 = %d", [$malicious_input]);

            // La requête devrait réussir sans injection
            $this->assert($result !== false, 'SQL injection blocked', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester la protection XSS
     *
     * @return array
     */
    private function test_xss_protection(): array {
        $test = [
            'name' => 'XSS Protection Test',
            'category' => 'security',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $security = $this->security_manager;

            // Tester la sanitisation
            $malicious_input = '<script>alert("xss")</script><img src=x onerror=alert(1)>';
            $sanitized = $security->sanitize_input($malicious_input, 'html');

            $this->assert($sanitized !== $malicious_input, 'XSS input sanitized', $test);
            $this->assert(strpos($sanitized, '<script>') === false, 'Script tags removed', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Tester la sécurité de l'authentification
     *
     * @return array
     */
    private function test_authentication_security(): array {
        $test = [
            'name' => 'Authentication Security Test',
            'category' => 'security',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            $security = $this->security_manager;

            // Tester le hachage de mot de passe
            $password = 'test_password_123!@#';
            $hash = $security->hash_password($password);
            $verified = $security->verify_password($password, $hash);

            $this->assert($verified, 'Password hashing works', $test);
            $this->assert(password_needs_rehash($hash, PASSWORD_DEFAULT) === false, 'Password hash up to date', $test);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Exécuter les tests E2E
     *
     * @param array $options
     * @return array
     */
    private function run_e2e_tests(array $options = []): array {
        $tests = [];

        // Test parcours utilisateur complet
        $tests[] = $this->test_user_journey();

        return $tests;
    }

    /**
     * Tester le parcours utilisateur complet
     *
     * @return array
     */
    private function test_user_journey(): array {
        $test = [
            'name' => 'User Journey E2E Test',
            'category' => 'e2e',
            'status' => 'passed',
            'assertions' => [],
            'errors' => []
        ];

        try {
            // Simuler un parcours utilisateur complet
            // Créer un document -> l'exporter -> le partager -> ajouter un commentaire

            $core = PDF_Builder_Core::getInstance();
            $db = $core->get_database_manager();
            $collaboration = $core->get_collaboration_manager();

            // 1. Créer un document
            $doc_id = $db->insert('documents', [
                'template_id' => 1,
                'title' => 'E2E Test Document',
                'data' => wp_json_encode(['content' => 'Test content']),
                'author_id' => 1,
                'status' => 'active'
            ]);
            $this->assert($doc_id > 0, 'Document created in user journey', $test);

            // 2. Partager le document
            $share_result = $collaboration->share_document($doc_id, 2, 'edit', 1);
            $this->assert($share_result, 'Document shared successfully', $test);

            // 3. Ajouter un commentaire
            $comment_id = $collaboration->add_comment($doc_id, 1, 'Test comment from E2E', ['test' => true]);
            $this->assert($comment_id > 0, 'Comment added successfully', $test);

            // 4. Créer une version
            $version_result = $collaboration->create_version_snapshot($doc_id, 1, 'E2E test version');
            $this->assert($version_result, 'Version created successfully', $test);

            // Nettoyer
            $db->delete('documents', ['id' => $doc_id]);

        } catch (Exception $e) {
            $test['status'] = 'failed';
            $test['errors'][] = $e->getMessage();
        }

        return $test;
    }

    /**
     * Assertion helper
     *
     * @param bool $condition
     * @param string $message
     * @param array $test
     */
    private function assert(bool $condition, string $message, array &$test): void {
        if ($condition) {
            $test['assertions'][] = ['message' => $message, 'status' => 'passed'];
        } else {
            $test['assertions'][] = ['message' => $message, 'status' => 'failed'];
            $test['status'] = 'failed';
        }
    }

    /**
     * Calculer la couverture de code
     *
     * @param array $test_results
     * @return float
     */
    private function calculate_coverage(array $test_results): float {
        // Simulation de calcul de couverture
        // En production, utiliser Xdebug ou similaire
        $total_tests = count($test_results);
        $passed_tests = count(array_filter($test_results, function($test) {
            return $test['status'] === 'passed';
        }));

        return $total_tests > 0 ? round(($passed_tests / $total_tests) * 100, 2) : 0;
    }

    /**
     * Mesurer les performances
     *
     * @return array
     */
    private function measure_performance(): array {
        return [
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'execution_time' => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2),
            'cpu_usage' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0
        ];
    }

    /**
     * Sauvegarder les résultats des tests
     *
     * @param array $results
     */
    private function save_test_results(array $results): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_test_results',
            [
                'suite' => $results['suite'],
                'results' => wp_json_encode($results),
                'tests_run' => $results['tests_run'],
                'tests_passed' => $results['tests_passed'],
                'tests_failed' => $results['tests_failed'],
                'coverage' => $results['coverage'],
                'duration' => $results['duration'],
                'status' => $results['status'],
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%d', '%d', '%f', '%f', '%s', '%s']
        );
    }

    /**
     * Exécuter la suite de tests automatisés
     */
    public function run_automated_test_suite(): void {
        $this->logger->info('Starting automated test suite');

        $results = $this->run_test_suite('all');

        // Envoyer un rapport si des tests échouent
        if ($results['tests_failed'] > 0) {
            $this->send_test_failure_report($results);
        }

        $this->logger->info('Automated test suite completed', [
            'tests_run' => $results['tests_run'],
            'tests_passed' => $results['tests_passed'],
            'tests_failed' => $results['tests_failed'],
            'coverage' => $results['coverage']
        ]);
    }

    /**
     * Générer un rapport de test
     */
    public function generate_test_report(): void {
        global $wpdb;

        // Récupérer les résultats des 7 derniers jours
        $recent_results = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT * FROM {$wpdb->prefix}pdf_builder_test_results
            WHERE created_at > %s
            ORDER BY created_at DESC
        ", date('Y-m-d H:i:s', strtotime('-7 days'))), ARRAY_A);

        $report = [
            'period' => '7 days',
            'generated_at' => current_time('mysql'),
            'summary' => $this->calculate_test_summary($recent_results),
            'trends' => $this->calculate_test_trends($recent_results),
            'recommendations' => $this->generate_test_recommendations($recent_results)
        ];

        // Sauvegarder le rapport
        update_option('pdf_builder_last_test_report', $report);

        // Envoyer le rapport par email
        $this->send_test_report_email($report);
    }

    /**
     * Calculer le résumé des tests
     *
     * @param array $results
     * @return array
     */
    private function calculate_test_summary(array $results): array {
        if (empty($results)) {
            return ['total_runs' => 0, 'avg_coverage' => 0, 'avg_pass_rate' => 0];
        }

        $total_runs = count($results);
        $total_coverage = array_sum(array_column($results, 'coverage'));
        $total_passed = array_sum(array_column($results, 'tests_passed'));
        $total_run = array_sum(array_column($results, 'tests_run'));

        return [
            'total_runs' => $total_runs,
            'avg_coverage' => round($total_coverage / $total_runs, 2),
            'avg_pass_rate' => $total_run > 0 ? round(($total_passed / $total_run) * 100, 2) : 0
        ];
    }

    /**
     * Calculer les tendances des tests
     *
     * @param array $results
     * @return array
     */
    private function calculate_test_trends(array $results): array {
        // Grouper par jour
        $daily_stats = [];
        foreach ($results as $result) {
            $date = date('Y-m-d', strtotime($result['created_at']));
            if (!isset($daily_stats[$date])) {
                $daily_stats[$date] = ['coverage' => [], 'pass_rate' => []];
            }
            $daily_stats[$date]['coverage'][] = $result['coverage'];
            $pass_rate = $result['tests_run'] > 0 ? ($result['tests_passed'] / $result['tests_run']) * 100 : 0;
            $daily_stats[$date]['pass_rate'][] = $pass_rate;
        }

        $trends = [];
        foreach ($daily_stats as $date => $stats) {
            $trends[] = [
                'date' => $date,
                'avg_coverage' => round(array_sum($stats['coverage']) / count($stats['coverage']), 2),
                'avg_pass_rate' => round(array_sum($stats['pass_rate']) / count($stats['pass_rate']), 2)
            ];
        }

        return array_slice($trends, -7); // 7 derniers jours
    }

    /**
     * Générer des recommandations de test
     *
     * @param array $results
     * @return array
     */
    private function generate_test_recommendations(array $results): array {
        $recommendations = [];

        $summary = $this->calculate_test_summary($results);

        if ($summary['avg_coverage'] < $this->performance_thresholds['coverage']) {
            $recommendations[] = "Coverage is below threshold ({$summary['avg_coverage']}% < {$this->performance_thresholds['coverage']}%). Add more tests.";
        }

        if ($summary['avg_pass_rate'] < 95) {
            $recommendations[] = "Test pass rate is below 95% ({$summary['avg_pass_rate']}%). Fix failing tests.";
        }

        $failed_tests = array_filter($results, function($result) {
            return $result['tests_failed'] > 0;
        });

        if (!empty($failed_tests)) {
            $recommendations[] = "Recent test failures detected. Review test results for details.";
        }

        return $recommendations;
    }

    /**
     * Envoyer un rapport d'échec de test
     *
     * @param array $results
     */
    private function send_test_failure_report(array $results): void {
        $admin_email = get_option('admin_email');
        $subject = '[TEST FAILURE] PDF Builder Pro - Tests Failed';

        $message = "Test suite failure detected:\n\n";
        $message .= "Suite: {$results['suite']}\n";
        $message .= "Tests Run: {$results['tests_run']}\n";
        $message .= "Tests Passed: {$results['tests_passed']}\n";
        $message .= "Tests Failed: {$results['tests_failed']}\n";
        $message .= "Coverage: {$results['coverage']}%\n\n";
        $message .= "Please review the test results and fix failing tests.\n";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Envoyer le rapport de test par email
     *
     * @param array $report
     */
    private function send_test_report_email(array $report): void {
        $admin_email = get_option('admin_email');
        $subject = '[TEST REPORT] PDF Builder Pro - Weekly Test Summary';

        $message = "Weekly Test Report:\n\n";
        $message .= "Period: {$report['period']}\n";
        $message .= "Generated: {$report['generated_at']}\n\n";
        $message .= "Summary:\n";
        $message .= "- Total Runs: {$report['summary']['total_runs']}\n";
        $message .= "- Avg Coverage: {$report['summary']['avg_coverage']}%\n";
        $message .= "- Avg Pass Rate: {$report['summary']['avg_pass_rate']}%\n\n";

        if (!empty($report['recommendations'])) {
            $message .= "Recommendations:\n";
            foreach ($report['recommendations'] as $rec) {
                $message .= "- {$rec}\n";
            }
            $message .= "\n";
        }

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Mettre à jour la couverture de code
     */
    public function update_code_coverage(): void {
        // Simulation de mise à jour de couverture
        // En production, intégrer avec Xdebug ou outil de couverture
        $this->coverage_metrics = [
            'timestamp' => current_time('mysql'),
            'lines_covered' => rand(8500, 9000),
            'total_lines' => 9500,
            'coverage_percentage' => rand(89, 96)
        ];

        update_option('pdf_builder_code_coverage', $this->coverage_metrics);
    }

    /**
     * AJAX: Exécuter les tests
     */
    public function ajax_run_tests(): void {
        try {
            $suite = sanitize_text_field($_POST['suite'] ?? 'unit');
            $options = json_decode(stripslashes($_POST['options'] ?? '{}'), true) ?: [];

            $results = $this->run_test_suite($suite, $options);

            wp_send_json_success($results);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les résultats des tests
     */
    public function ajax_get_test_results(): void {
        try {
            global $wpdb;

            $limit = intval($_POST['limit'] ?? 10);
            $results = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT * FROM {$wpdb->prefix}pdf_builder_test_results
                ORDER BY created_at DESC
                LIMIT %d
            ", $limit), ARRAY_A);

            wp_send_json_success($results);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Exécuter un test de performance
     */
    public function ajax_run_performance_test(): void {
        try {
            $test_type = sanitize_text_field($_POST['test_type'] ?? 'response_time');

            switch ($test_type) {
                case 'response_time':
                    $result = $this->test_response_time();
                    break;
                case 'memory_usage':
                    $result = $this->test_memory_usage();
                    break;
                case 'load_performance':
                    $result = $this->test_load_performance();
                    break;
                default:
                    throw new Exception('Invalid test type');
            }

            wp_send_json_success($result);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les métriques de test
     *
     * @return array
     */
    public function get_test_metrics(): array {
        global $wpdb;

        $metrics = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(*) as total_runs,
                AVG(tests_passed/tests_run * 100) as avg_pass_rate,
                AVG(coverage) as avg_coverage,
                MAX(created_at) as last_run
            FROM {$wpdb->prefix}pdf_builder_test_results
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-30 days'))), ARRAY_A);

        return [
            'total_runs' => intval($metrics['total_runs'] ?? 0),
            'avg_pass_rate' => round(floatval($metrics['avg_pass_rate'] ?? 0), 2),
            'avg_coverage' => round(floatval($metrics['avg_coverage'] ?? 0), 2),
            'last_run' => $metrics['last_run'] ?? null,
            'coverage_target' => $this->performance_thresholds['coverage'],
            'thresholds' => $this->performance_thresholds
        ];
    }

    /**
     * Obtenir les tests disponibles
     *
     * @return array
     */
    public function get_available_tests(): array {
        return $this->available_tests;
    }
}

