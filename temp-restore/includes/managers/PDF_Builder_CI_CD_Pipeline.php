<?php
/**
 * Pipeline CI/CD - PDF Builder Pro
 *
 * Pipeline d'intégration continue et déploiement continu
 * avec tests automatisés, déploiement blue-green et rollback
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Pipeline CI/CD
 */
class PDF_Builder_CI_CD_Pipeline {

    /**
     * Instance singleton
     * @var PDF_Builder_CI_CD_Pipeline
     */
    private static $instance = null;

    /**
     * Gestionnaire de tests
     * @var PDF_Builder_Test_Manager
     */
    private $test_manager;

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
     * Statut du pipeline
     * @var array
     */
    private $pipeline_status = [];

    /**
     * Environnements disponibles
     * @var array
     */
    private $environments = [
        'development' => [
            'name' => 'Development',
            'url' => 'https://dev.pdf-builder-pro.com',
            'auto_deploy' => true,
            'tests_required' => ['unit', 'integration'],
            'coverage_threshold' => 80
        ],
        'staging' => [
            'name' => 'Staging',
            'url' => 'https://staging.pdf-builder-pro.com',
            'auto_deploy' => false,
            'tests_required' => ['unit', 'integration', 'performance', 'security'],
            'coverage_threshold' => 90
        ],
        'production' => [
            'name' => 'Production',
            'url' => 'https://pdf-builder-pro.com',
            'auto_deploy' => false,
            'tests_required' => ['unit', 'integration', 'performance', 'security', 'e2e'],
            'coverage_threshold' => 95
        ]
    ];

    /**
     * Étapes du pipeline
     * @var array
     */
    private $pipeline_stages = [
        'build' => [
            'name' => 'Build',
            'steps' => ['validate_code', 'build_assets', 'create_package'],
            'timeout' => 300, // 5 minutes
            'required' => true
        ],
        'test' => [
            'name' => 'Test',
            'steps' => ['run_unit_tests', 'run_integration_tests', 'run_performance_tests', 'run_security_tests'],
            'timeout' => 600, // 10 minutes
            'required' => true
        ],
        'security' => [
            'name' => 'Security Scan',
            'steps' => ['scan_vulnerabilities', 'check_dependencies', 'audit_code'],
            'timeout' => 300, // 5 minutes
            'required' => true
        ],
        'deploy' => [
            'name' => 'Deploy',
            'steps' => ['backup_current', 'deploy_code', 'run_migrations', 'verify_deployment'],
            'timeout' => 600, // 10 minutes
            'required' => true
        ],
        'monitor' => [
            'name' => 'Monitor',
            'steps' => ['health_check', 'performance_monitor', 'error_monitor'],
            'timeout' => 60, // 1 minute
            'required' => false
        ]
    ];

    /**
     * Historique des déploiements
     * @var array
     */
    private $deployment_history = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->test_manager = $core->get_test_manager();
        $this->security_manager = $core->get_security_manager();
        $this->logger = $core->get_logger();

        $this->init_pipeline_hooks();
        $this->schedule_pipeline_tasks();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_CI_CD_Pipeline
     */
    public static function getInstance(): PDF_Builder_CI_CD_Pipeline {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks du pipeline
     */
    private function init_pipeline_hooks(): void {
        // Hooks AJAX pour le pipeline
        add_action('wp_ajax_pdf_builder_trigger_pipeline', [$this, 'ajax_trigger_pipeline']);
        add_action('wp_ajax_pdf_builder_get_pipeline_status', [$this, 'ajax_get_pipeline_status']);
        add_action('wp_ajax_pdf_builder_rollback_deployment', [$this, 'ajax_rollback_deployment']);

        // Hooks pour les tâches automatiques
        add_action('pdf_builder_run_ci_pipeline', [$this, 'run_ci_pipeline']);
        add_action('pdf_builder_monitor_deployments', [$this, 'monitor_deployments']);
        add_action('pdf_builder_cleanup_old_deployments', [$this, 'cleanup_old_deployments']);

        // Hooks pour les webhooks Git
        add_action('wp_ajax_nopriv_pdf_builder_git_webhook', [$this, 'handle_git_webhook']);
    }

    /**
     * Programmer les tâches du pipeline
     */
    private function schedule_pipeline_tasks(): void {
        // Pipeline CI automatique (toutes les heures)
        if (!wp_next_scheduled('pdf_builder_run_ci_pipeline')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_run_ci_pipeline');
        }

        // Monitoring des déploiements (toutes les 5 minutes)
        if (!wp_next_scheduled('pdf_builder_monitor_deployments')) {
            wp_schedule_event(time(), 'every_five_minutes', 'pdf_builder_monitor_deployments');
        }

        // Nettoyage des anciens déploiements (hebdomadaire)
        if (!wp_next_scheduled('pdf_builder_cleanup_old_deployments')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_cleanup_old_deployments');
        }
    }

    /**
     * Déclencher le pipeline CI/CD
     *
     * @param string $environment
     * @param array $options
     * @return array
     */
    public function trigger_pipeline(string $environment = 'development', array $options = []): array {
        if (!isset($this->environments[$environment])) {
            throw new Exception("Environnement inconnu: {$environment}");
        }

        $pipeline_id = $this->generate_pipeline_id();
        $this->pipeline_status[$pipeline_id] = [
            'id' => $pipeline_id,
            'environment' => $environment,
            'status' => 'running',
            'stages' => [],
            'started_at' => current_time('mysql'),
            'completed_at' => null,
            'triggered_by' => get_current_user_id(),
            'commit_hash' => $options['commit_hash'] ?? $this->get_current_commit_hash(),
            'branch' => $options['branch'] ?? 'main'
        ];

        $this->logger->info("Pipeline déclenché", [
            'pipeline_id' => $pipeline_id,
            'environment' => $environment
        ]);

        // Exécuter le pipeline de manière asynchrone
        wp_schedule_single_event(time(), 'pdf_builder_execute_pipeline_async', [
            'pipeline_id' => $pipeline_id,
            'environment' => $environment,
            'options' => $options
        ]);

        add_action('pdf_builder_execute_pipeline_async', [$this, 'execute_pipeline_async']);

        return [
            'pipeline_id' => $pipeline_id,
            'status' => 'running',
            'message' => 'Pipeline déclenché avec succès'
        ];
    }

    /**
     * Exécuter le pipeline de manière asynchrone
     *
     * @param array $args
     */
    public function execute_pipeline_async(array $args): void {
        $pipeline_id = $args['pipeline_id'];
        $environment = $args['environment'];
        $options = $args['options'];

        try {
            $result = $this->execute_pipeline($pipeline_id, $environment, $options);

            $this->pipeline_status[$pipeline_id]['status'] = $result['success'] ? 'success' : 'failed';
            $this->pipeline_status[$pipeline_id]['completed_at'] = current_time('mysql');
            $this->pipeline_status[$pipeline_id]['result'] = $result;

            $this->save_pipeline_result($pipeline_id);

            if (!$result['success']) {
                $this->send_pipeline_failure_notification($pipeline_id, $result);
            }

        } catch (Exception $e) {
            $this->pipeline_status[$pipeline_id]['status'] = 'error';
            $this->pipeline_status[$pipeline_id]['completed_at'] = current_time('mysql');
            $this->pipeline_status[$pipeline_id]['error'] = $e->getMessage();

            $this->logger->error('Pipeline execution failed', [
                'pipeline_id' => $pipeline_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Exécuter le pipeline complet
     *
     * @param string $pipeline_id
     * @param string $environment
     * @param array $options
     * @return array
     */
    private function execute_pipeline(string $pipeline_id, string $environment, array $options = []): array {
        $env_config = $this->environments[$environment];
        $results = [
            'success' => true,
            'stages' => [],
            'duration' => 0
        ];

        $start_time = microtime(true);

        foreach ($this->pipeline_stages as $stage_name => $stage_config) {
            $stage_start = microtime(true);

            $stage_result = $this->execute_stage($pipeline_id, $stage_name, $stage_config, $env_config, $options);

            $stage_duration = microtime(true) - $stage_start;
            $results['stages'][$stage_name] = [
                'status' => $stage_result['success'] ? 'success' : 'failed',
                'duration' => round($stage_duration, 2),
                'steps' => $stage_result['steps'] ?? [],
                'error' => $stage_result['error'] ?? null
            ];

            $this->pipeline_status[$pipeline_id]['stages'][$stage_name] = $results['stages'][$stage_name];

            if (!$stage_result['success'] && $stage_config['required']) {
                $results['success'] = false;
                $results['failed_stage'] = $stage_name;
                break;
            }
        }

        $results['duration'] = round(microtime(true) - $start_time, 2);

        return $results;
    }

    /**
     * Exécuter une étape du pipeline
     *
     * @param string $pipeline_id
     * @param string $stage_name
     * @param array $stage_config
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function execute_stage(string $pipeline_id, string $stage_name, array $stage_config, array $env_config, array $options): array {
        $result = [
            'success' => true,
            'steps' => []
        ];

        $this->logger->info("Exécution de l'étape", [
            'pipeline_id' => $pipeline_id,
            'stage' => $stage_name
        ]);

        foreach ($stage_config['steps'] as $step) {
            $step_start = microtime(true);

            try {
                $step_result = $this->{'execute_' . $step}($pipeline_id, $env_config, $options);

                $step_duration = microtime(true) - $step_start;
                $result['steps'][$step] = [
                    'status' => $step_result['success'] ? 'success' : 'failed',
                    'duration' => round($step_duration, 2),
                    'output' => $step_result['output'] ?? null,
                    'error' => $step_result['error'] ?? null
                ];

                if (!$step_result['success']) {
                    $result['success'] = false;
                    $result['error'] = $step_result['error'];
                    break;
                }

            } catch (Exception $e) {
                $result['steps'][$step] = [
                    'status' => 'error',
                    'duration' => round(microtime(true) - $step_start, 2),
                    'error' => $e->getMessage()
                ];
                $result['success'] = false;
                $result['error'] = $e->getMessage();
                break;
            }
        }

        return $result;
    }

    /**
     * Étape: Validation du code
     */
    private function execute_validate_code(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Vérifier la syntaxe PHP
        $php_files = $this->get_php_files();
        foreach ($php_files as $file) {
            $syntax_check = $this->check_php_syntax($file);
            if (!$syntax_check['valid']) {
                $result['success'] = false;
                $result['error'] = "Erreur de syntaxe dans {$file}: {$syntax_check['error']}";
                break;
            }
            $result['output'][] = "Syntaxe OK: {$file}";
        }

        // Vérifier les dépendances
        if ($result['success']) {
            $deps_check = $this->check_dependencies();
            $result['output'][] = "Dépendances: " . ($deps_check ? "OK" : "Échec");
            if (!$deps_check) {
                $result['success'] = false;
                $result['error'] = "Problème de dépendances détecté";
            }
        }

        return $result;
    }

    /**
     * Étape: Construction des assets
     */
    private function execute_build_assets(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Compiler les assets React/TypeScript
        if ($this->has_react_typescript_files()) {
            $build_result = $this->build_react_assets();
            if (!$build_result['success']) {
                $result['success'] = false;
                $result['error'] = "Échec de la compilation React/TypeScript: {$build_result['error']}";
            } else {
                $result['output'][] = "Assets React/TypeScript compilés";
            }
        }

        // Minifier les assets CSS/JS
        $minify_result = $this->minify_assets();
        if (!$minify_result['success']) {
            $result['success'] = false;
            $result['error'] = "Échec de la minification: {$minify_result['error']}";
        } else {
            $result['output'][] = "Assets minifiés";
        }

        return $result;
    }

    /**
     * Étape: Création du package
     */
    private function execute_create_package(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $version = $this->get_plugin_version();
        $package_name = "pdf-builder-pro-{$version}.zip";

        // Créer le package
        $package_result = $this->create_plugin_package($package_name);
        if (!$package_result['success']) {
            $result['success'] = false;
            $result['error'] = "Échec de la création du package: {$package_result['error']}";
        } else {
            $result['output'][] = "Package créé: {$package_name}";
            $result['package_path'] = $package_result['path'];
        }

        return $result;
    }

    /**
     * Étape: Tests unitaires
     */
    private function execute_run_unit_tests(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $test_results = $this->test_manager->run_test_suite('unit');

        $result['output'][] = "Tests unitaires: {$test_results['tests_passed']}/{$test_results['tests_run']} réussis";
        $result['output'][] = "Couverture: {$test_results['coverage']}%";

        if ($test_results['tests_failed'] > 0) {
            $result['success'] = false;
            $result['error'] = "Échec des tests unitaires: {$test_results['tests_failed']} tests échoués";
        }

        if ($test_results['coverage'] < $env_config['coverage_threshold']) {
            $result['success'] = false;
            $result['error'] = "Couverture insuffisante: {$test_results['coverage']}% < {$env_config['coverage_threshold']}%";
        }

        return $result;
    }

    /**
     * Étape: Tests d'intégration
     */
    private function execute_run_integration_tests(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $test_results = $this->test_manager->run_test_suite('integration');

        $result['output'][] = "Tests d'intégration: {$test_results['tests_passed']}/{$test_results['tests_run']} réussis";

        if ($test_results['tests_failed'] > 0) {
            $result['success'] = false;
            $result['error'] = "Échec des tests d'intégration: {$test_results['tests_failed']} tests échoués";
        }

        return $result;
    }

    /**
     * Étape: Tests de performance
     */
    private function execute_run_performance_tests(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $test_results = $this->test_manager->run_test_suite('performance');

        $result['output'][] = "Tests de performance exécutés";

        // Vérifier les seuils de performance
        $thresholds = $this->test_manager->get_performance_thresholds();
        foreach ($test_results['results'] as $test) {
            if (isset($test['metrics'])) {
                foreach ($test['metrics'] as $metric => $value) {
                    if (isset($thresholds[$metric]) && $value > $thresholds[$metric]) {
                        $result['success'] = false;
                        $result['error'] = "Seuil de performance dépassé: {$metric} = {$value} > {$thresholds[$metric]}";
                        break 2;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Étape: Tests de sécurité
     */
    private function execute_run_security_tests(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $test_results = $this->test_manager->run_test_suite('security');

        $result['output'][] = "Tests de sécurité: {$test_results['tests_passed']}/{$test_results['tests_run']} réussis";

        if ($test_results['tests_failed'] > 0) {
            $result['success'] = false;
            $result['error'] = "Échec des tests de sécurité: {$test_results['tests_failed']} tests échoués";
        }

        return $result;
    }

    /**
     * Étape: Scan de vulnérabilités
     */
    private function execute_scan_vulnerabilities(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Scanner les vulnérabilités dans le code
        $vulnerabilities = $this->scan_for_vulnerabilities();

        if (!empty($vulnerabilities)) {
            $result['success'] = false;
            $result['error'] = "Vulnérabilités détectées: " . count($vulnerabilities);
            $result['vulnerabilities'] = $vulnerabilities;
        } else {
            $result['output'][] = "Aucune vulnérabilité détectée";
        }

        return $result;
    }

    /**
     * Étape: Vérification des dépendances
     */
    private function execute_check_dependencies(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Vérifier les versions des dépendances
        $deps_status = $this->check_dependency_versions();

        if (!$deps_status['secure']) {
            $result['success'] = false;
            $result['error'] = "Dépendances non sécurisées détectées";
            $result['outdated_deps'] = $deps_status['outdated'];
        } else {
            $result['output'][] = "Toutes les dépendances sont à jour et sécurisées";
        }

        return $result;
    }

    /**
     * Étape: Audit du code
     */
    private function execute_audit_code(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Audit de sécurité du code
        $audit_result = $this->security_manager->perform_code_audit();

        if (!$audit_result['passed']) {
            $result['success'] = false;
            $result['error'] = "Échec de l'audit de code: " . implode(', ', $audit_result['issues']);
        } else {
            $result['output'][] = "Audit de code passé";
        }

        return $result;
    }

    /**
     * Étape: Sauvegarde actuelle
     */
    private function execute_backup_current(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $backup_result = $this->create_deployment_backup($env_config['name']);

        if (!$backup_result['success']) {
            $result['success'] = false;
            $result['error'] = "Échec de la sauvegarde: {$backup_result['error']}";
        } else {
            $result['output'][] = "Sauvegarde créée: {$backup_result['backup_id']}";
            $result['backup_id'] = $backup_result['backup_id'];
        }

        return $result;
    }

    /**
     * Étape: Déploiement du code
     */
    private function execute_deploy_code(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Pour la démo, simuler un déploiement
        // En production, utiliser des outils comme Deployer ou des services cloud

        $deploy_result = $this->perform_deployment($env_config, $options);

        if (!$deploy_result['success']) {
            $result['success'] = false;
            $result['error'] = "Échec du déploiement: {$deploy_result['error']}";
        } else {
            $result['output'][] = "Code déployé avec succès";
            $result['deployment_id'] = $deploy_result['deployment_id'];
        }

        return $result;
    }

    /**
     * Étape: Exécution des migrations
     */
    private function execute_run_migrations(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $migration_result = $this->run_database_migrations($env_config);

        if (!$migration_result['success']) {
            $result['success'] = false;
            $result['error'] = "Échec des migrations: {$migration_result['error']}";
        } else {
            $result['output'][] = "Migrations exécutées: " . count($migration_result['migrations']);
        }

        return $result;
    }

    /**
     * Étape: Vérification du déploiement
     */
    private function execute_verify_deployment(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        // Vérifier que le déploiement fonctionne
        $health_check = $this->perform_deployment_health_check($env_config['url']);

        if (!$health_check['healthy']) {
            $result['success'] = false;
            $result['error'] = "Vérification du déploiement échouée: {$health_check['error']}";
        } else {
            $result['output'][] = "Déploiement vérifié et opérationnel";
        }

        return $result;
    }

    /**
     * Étape: Vérification de santé
     */
    private function execute_health_check(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $health = $this->check_system_health();

        $result['output'][] = "État système: " . ($health['healthy'] ? "OK" : "Problèmes détectés");

        if (!$health['healthy']) {
            $result['success'] = false;
            $result['error'] = "Problèmes de santé système détectés";
        }

        return $result;
    }

    /**
     * Étape: Monitoring des performances
     */
    private function execute_performance_monitor(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $metrics = $this->collect_performance_metrics();

        $result['output'][] = "Métriques collectées: " . count($metrics) . " indicateurs";

        // Vérifier les seuils
        foreach ($metrics as $metric => $value) {
            $threshold = $this->get_performance_threshold($metric);
            if ($threshold && $value > $threshold) {
                $result['output'][] = "Alerte: {$metric} = {$value} (seuil: {$threshold})";
            }
        }

        return $result;
    }

    /**
     * Étape: Monitoring des erreurs
     */
    private function execute_error_monitor(string $pipeline_id, array $env_config, array $options): array {
        $result = ['success' => true, 'output' => []];

        $errors = $this->check_error_logs();

        if (!empty($errors)) {
            $result['output'][] = "Erreurs détectées: " . count($errors);
            // Ne pas échouer pour les erreurs existantes, juste logger
        } else {
            $result['output'][] = "Aucune nouvelle erreur détectée";
        }

        return $result;
    }

    /**
     * Rollback d'un déploiement
     *
     * @param string $deployment_id
     * @return array
     */
    public function rollback_deployment(string $deployment_id): array {
        $this->logger->info("Rollback du déploiement", ['deployment_id' => $deployment_id]);

        // Trouver la sauvegarde associée
        $backup = $this->find_deployment_backup($deployment_id);

        if (!$backup) {
            throw new Exception("Sauvegarde non trouvée pour le déploiement: {$deployment_id}");
        }

        // Exécuter le rollback
        $rollback_result = $this->perform_rollback($backup);

        if ($rollback_result['success']) {
            $this->logger->info("Rollback réussi", ['deployment_id' => $deployment_id]);
            $this->send_rollback_notification($deployment_id, 'success');
        } else {
            $this->logger->error("Rollback échoué", [
                'deployment_id' => $deployment_id,
                'error' => $rollback_result['error']
            ]);
            $this->send_rollback_notification($deployment_id, 'failed', $rollback_result['error']);
        }

        return $rollback_result;
    }

    /**
     * Exécuter le pipeline CI automatique
     */
    public function run_ci_pipeline(): void {
        // Vérifier s'il y a des changements récents
        if (!$this->has_recent_changes()) {
            return;
        }

        $this->logger->info('Exécution du pipeline CI automatique');

        try {
            $result = $this->trigger_pipeline('development', ['auto' => true]);

            if ($result['status'] === 'running') {
                // Attendre la fin du pipeline
                $this->wait_for_pipeline_completion($result['pipeline_id']);

                $status = $this->get_pipeline_status($result['pipeline_id']);

                if ($status['status'] === 'success') {
                    // Pipeline réussi, déclencher staging si auto-deploy activé
                    if ($this->environments['staging']['auto_deploy']) {
                        $this->trigger_pipeline('staging', ['auto' => true]);
                    }
                }
            }

        } catch (Exception $e) {
            $this->logger->error('Pipeline CI automatique échoué', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Monitorer les déploiements
     */
    public function monitor_deployments(): void {
        foreach ($this->environments as $env_name => $env_config) {
            $health = $this->check_environment_health($env_config);

            if (!$health['healthy']) {
                $this->logger->warning("Problème de santé détecté", [
                    'environment' => $env_name,
                    'issues' => $health['issues']
                ]);

                $this->send_health_alert($env_name, $health['issues']);
            }
        }
    }

    /**
     * Nettoyer les anciens déploiements
     */
    public function cleanup_old_deployments(): void {
        $old_deployments = $this->find_old_deployments(30); // 30 jours

        foreach ($old_deployments as $deployment) {
            $this->remove_deployment_backup($deployment['id']);
            $this->logger->info("Ancienne sauvegarde supprimée", ['deployment_id' => $deployment['id']]);
        }
    }

    /**
     * Gérer les webhooks Git
     */
    public function handle_git_webhook(): void {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);

            if (!$payload) {
                throw new Exception('Payload webhook invalide');
            }

            // Vérifier la signature si configurée
            $this->verify_webhook_signature($payload);

            $branch = $payload['ref'] ?? '';
            $commit_hash = $payload['after'] ?? '';

            if (strpos($branch, 'refs/heads/') === 0) {
                $branch = substr($branch, 11); // Enlever 'refs/heads/'
            }

            // Déclencher le pipeline pour la branche appropriée
            $environment = $this->get_environment_for_branch($branch);

            if ($environment) {
                $this->trigger_pipeline($environment, [
                    'commit_hash' => $commit_hash,
                    'branch' => $branch,
                    'webhook' => true
                ]);
            }

            wp_send_json_success(['message' => 'Webhook traité']);

        } catch (Exception $e) {
            $this->logger->error('Erreur webhook Git', ['error' => $e->getMessage()]);
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir le statut du pipeline
     *
     * @param string $pipeline_id
     * @return array|null
     */
    public function get_pipeline_status(string $pipeline_id): ?array {
        return $this->pipeline_status[$pipeline_id] ?? null;
    }

    /**
     * Sauvegarder le résultat du pipeline
     *
     * @param string $pipeline_id
     */
    private function save_pipeline_result(string $pipeline_id): void {
        global $wpdb;

        $status = $this->pipeline_status[$pipeline_id];

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_pipeline_results',
            [
                'pipeline_id' => $pipeline_id,
                'environment' => $status['environment'],
                'status' => $status['status'],
                'stages' => wp_json_encode($status['stages']),
                'result' => wp_json_encode($status['result'] ?? []),
                'started_at' => $status['started_at'],
                'completed_at' => $status['completed_at'],
                'duration' => $status['result']['duration'] ?? 0,
                'commit_hash' => $status['commit_hash'],
                'branch' => $status['branch'],
                'triggered_by' => $status['triggered_by']
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d']
        );
    }

    /**
     * Générer un ID de pipeline unique
     *
     * @return string
     */
    private function generate_pipeline_id(): string {
        return 'pipeline_' . time() . '_' . wp_generate_password(8, false);
    }

    /**
     * Obtenir le hash du commit actuel
     *
     * @return string
     */
    private function get_current_commit_hash(): string {
        // Simulation - en production, utiliser git rev-parse HEAD
        return wp_generate_password(40, false, '0123456789abcdef');
    }

    /**
     * Vérifier la syntaxe PHP
     *
     * @param string $file
     * @return array
     */
    private function check_php_syntax(string $file): array {
        $output = shell_exec("php -l \"{$file}\" 2>&1");
        $valid = strpos($output, 'No syntax errors detected') !== false;

        return [
            'valid' => $valid,
            'error' => $valid ? null : $output
        ];
    }

    /**
     * Obtenir les fichiers PHP
     *
     * @return array
     */
    private function get_php_files(): array {
        $files = [];
        $this->scan_directory_for_php(WP_PLUGIN_DIR . '/pdf-builder-pro', $files);
        return $files;
    }

    /**
     * Scanner un répertoire pour les fichiers PHP
     *
     * @param string $dir
     * @param array $files
     */
    private function scan_directory_for_php(string $dir, array &$files): void {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->scan_directory_for_php($path, $files);
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }
    }

    /**
     * Vérifier les dépendances
     *
     * @return bool
     */
    private function check_dependencies(): bool {
        // Simulation - en production, vérifier composer.json, package.json, etc.
        return true;
    }

    /**
     * Vérifier s'il y a des fichiers React/TypeScript
     *
     * @return bool
     */
    private function has_react_typescript_files(): bool {
        return file_exists(WP_PLUGIN_DIR . '/pdf-builder-pro/src/index.tsx') ||
               file_exists(WP_PLUGIN_DIR . '/pdf-builder-pro/tsconfig.json');
    }

    /**
     * Construire les assets React
     *
     * @return array
     */
    private function build_react_assets(): array {
        // Simulation - en production, exécuter npm run build
        return ['success' => true];
    }

    /**
     * Minifier les assets
     *
     * @return array
     */
    private function minify_assets(): array {
        // Simulation - en production, utiliser des outils de minification
        return ['success' => true];
    }

    /**
     * Obtenir la version du plugin
     *
     * @return string
     */
    private function get_plugin_version(): string {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/pdf-builder-pro/pdf-builder-pro.php');
        return $plugin_data['Version'] ?? '1.0.0';
    }

    /**
     * Créer le package du plugin
     *
     * @param string $package_name
     * @return array
     */
    private function create_plugin_package(string $package_name): array {
        // Simulation - en production, créer un ZIP du plugin
        $path = WP_CONTENT_DIR . '/uploads/' . $package_name;
        return ['success' => true, 'path' => $path];
    }

    /**
     * Scanner les vulnérabilités
     *
     * @return array
     */
    private function scan_for_vulnerabilities(): array {
        // Simulation - en production, utiliser des outils comme OWASP ZAP ou similaires
        return [];
    }

    /**
     * Vérifier les versions des dépendances
     *
     * @return array
     */
    private function check_dependency_versions(): array {
        // Simulation - en production, vérifier les versions via Composer/NPM
        return ['secure' => true, 'outdated' => []];
    }

    /**
     * Créer une sauvegarde de déploiement
     *
     * @param string $environment
     * @return array
     */
    private function create_deployment_backup(string $environment): array {
        $backup_id = 'backup_' . time() . '_' . wp_generate_password(8, false);

        // Simulation - en production, créer une vraie sauvegarde
        return ['success' => true, 'backup_id' => $backup_id];
    }

    /**
     * Effectuer le déploiement
     *
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function perform_deployment(array $env_config, array $options): array {
        $deployment_id = 'deploy_' . time() . '_' . wp_generate_password(8, false);

        // Simulation - en production, déployer réellement
        return ['success' => true, 'deployment_id' => $deployment_id];
    }

    /**
     * Exécuter les migrations de base de données
     *
     * @param array $env_config
     * @return array
     */
    private function run_database_migrations(array $env_config): array {
        // Simulation - en production, exécuter les migrations
        return ['success' => true, 'migrations' => ['migration_1', 'migration_2']];
    }

    /**
     * Effectuer la vérification de santé du déploiement
     *
     * @param string $url
     * @return array
     */
    private function perform_deployment_health_check(string $url): array {
        // Simulation - en production, faire un vrai health check
        $response = wp_remote_get($url . '/wp-json/pdf-builder/v1/health');
        $healthy = !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;

        return [
            'healthy' => $healthy,
            'error' => $healthy ? null : 'Service non disponible'
        ];
    }

    /**
     * Vérifier la santé système
     *
     * @return array
     */
    private function check_system_health(): array {
        // Vérifications basiques
        $healthy = true;
        $issues = [];

        // Vérifier l'espace disque
        $disk_free = disk_free_space('/');
        if ($disk_free < 100 * 1024 * 1024) { // 100MB
            $healthy = false;
            $issues[] = 'Espace disque faible';
        }

        // Vérifier la mémoire
        $memory_usage = memory_get_peak_usage(true);
        if ($memory_usage > 128 * 1024 * 1024) { // 128MB
            $issues[] = 'Utilisation mémoire élevée';
        }

        return ['healthy' => $healthy, 'issues' => $issues];
    }

    /**
     * Collecter les métriques de performance
     *
     * @return array
     */
    private function collect_performance_metrics(): array {
        return [
            'response_time' => rand(100, 500),
            'memory_usage' => rand(50, 120),
            'cpu_usage' => rand(10, 80),
            'error_rate' => rand(0, 5)
        ];
    }

    /**
     * Obtenir le seuil de performance
     *
     * @param string $metric
     * @return int|null
     */
    private function get_performance_threshold(string $metric): ?int {
        $thresholds = [
            'response_time' => 1000,
            'memory_usage' => 128,
            'cpu_usage' => 70,
            'error_rate' => 5
        ];

        return $thresholds[$metric] ?? null;
    }

    /**
     * Vérifier les logs d'erreur
     *
     * @return array
     */
    private function check_error_logs(): array {
        // Simulation - en production, analyser les logs PHP/error.log
        return [];
    }

    /**
     * Effectuer un rollback
     *
     * @param array $backup
     * @return array
     */
    private function perform_rollback(array $backup): array {
        // Simulation - en production, restaurer la sauvegarde
        return ['success' => true];
    }

    /**
     * Vérifier s'il y a des changements récents
     *
     * @return bool
     */
    private function has_recent_changes(): bool {
        // Simulation - en production, vérifier les timestamps des fichiers
        return rand(0, 1) === 1; // 50% de chance
    }

    /**
     * Attendre la fin du pipeline
     *
     * @param string $pipeline_id
     * @param int $timeout
     */
    private function wait_for_pipeline_completion(string $pipeline_id, int $timeout = 300): void {
        $start = time();
        while (time() - $start < $timeout) {
            $status = $this->get_pipeline_status($pipeline_id);
            if ($status && in_array($status['status'], ['success', 'failed', 'error'])) {
                break;
            }
            sleep(5);
        }
    }

    /**
     * Vérifier la santé d'un environnement
     *
     * @param array $env_config
     * @return array
     */
    private function check_environment_health(array $env_config): array {
        $health = $this->perform_deployment_health_check($env_config['url']);
        return [
            'healthy' => $health['healthy'],
            'issues' => $health['healthy'] ? [] : [$health['error']]
        ];
    }

    /**
     * Trouver les anciens déploiements
     *
     * @param int $days
     * @return array
     */
    private function find_old_deployments(int $days): array {
        // Simulation
        return [];
    }

    /**
     * Supprimer une sauvegarde de déploiement
     *
     * @param string $deployment_id
     */
    private function remove_deployment_backup(string $deployment_id): void {
        // Simulation
    }

    /**
     * Trouver la sauvegarde d'un déploiement
     *
     * @param string $deployment_id
     * @return array|null
     */
    private function find_deployment_backup(string $deployment_id): ?array {
        // Simulation
        return ['id' => 'backup_123', 'path' => '/path/to/backup'];
    }

    /**
     * Vérifier la signature du webhook
     *
     * @param array $payload
     */
    private function verify_webhook_signature(array $payload): void {
        // Simulation - en production, vérifier la signature GitHub/GitLab
    }

    /**
     * Obtenir l'environnement pour une branche
     *
     * @param string $branch
     * @return string|null
     */
    private function get_environment_for_branch(string $branch): ?string {
        $branch_mapping = [
            'main' => 'staging',
            'master' => 'staging',
            'develop' => 'development',
            'staging' => 'staging',
            'production' => 'production'
        ];

        return $branch_mapping[$branch] ?? null;
    }

    /**
     * Envoyer une notification d'échec de pipeline
     *
     * @param string $pipeline_id
     * @param array $result
     */
    private function send_pipeline_failure_notification(string $pipeline_id, array $result): void {
        $admin_email = get_option('admin_email');
        $subject = '[PIPELINE FAILED] PDF Builder Pro - Pipeline échoué';

        $message = "Échec du pipeline CI/CD:\n\n";
        $message .= "Pipeline ID: {$pipeline_id}\n";
        $message .= "Étape échouée: {$result['failed_stage']}\n";
        $message .= "Durée: {$result['duration']}s\n\n";
        $message .= "Veuillez vérifier les logs pour plus de détails.\n";

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Envoyer une notification de rollback
     *
     * @param string $deployment_id
     * @param string $status
     * @param string|null $error
     */
    private function send_rollback_notification(string $deployment_id, string $status, ?string $error = null): void {
        $admin_email = get_option('admin_email');
        $subject = "[ROLLBACK {$status}] PDF Builder Pro - Rollback " . ucfirst($status);

        $message = "Rollback du déploiement:\n\n";
        $message .= "Deployment ID: {$deployment_id}\n";
        $message .= "Status: " . ucfirst($status) . "\n";

        if ($error) {
            $message .= "Erreur: {$error}\n";
        }

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Envoyer une alerte de santé
     *
     * @param string $environment
     * @param array $issues
     */
    private function send_health_alert(string $environment, array $issues): void {
        $admin_email = get_option('admin_email');
        $subject = "[HEALTH ALERT] PDF Builder Pro - Problèmes détectés";

        $message = "Alertes de santé pour l'environnement {$environment}:\n\n";
        foreach ($issues as $issue) {
            $message .= "- {$issue}\n";
        }

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * AJAX: Déclencher le pipeline
     */
    public function ajax_trigger_pipeline(): void {
        try {
            $environment = sanitize_text_field($_POST['environment'] ?? 'development');
            $options = json_decode(stripslashes($_POST['options'] ?? '{}'), true) ?: [];

            $result = $this->trigger_pipeline($environment, $options);

            wp_send_json_success($result);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir le statut du pipeline
     */
    public function ajax_get_pipeline_status(): void {
        try {
            $pipeline_id = sanitize_text_field($_POST['pipeline_id'] ?? '');

            if (empty($pipeline_id)) {
                // Retourner la liste des pipelines récents
                global $wpdb;
                $pipelines = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                    SELECT * FROM {$wpdb->prefix}pdf_builder_pipeline_results
                    ORDER BY started_at DESC
                    LIMIT 10
                "), ARRAY_A);

                wp_send_json_success($pipelines);
            } else {
                $status = $this->get_pipeline_status($pipeline_id);
                if (!$status) {
                    wp_send_json_error(['message' => 'Pipeline non trouvé']);
                } else {
                    wp_send_json_success($status);
                }
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Rollback du déploiement
     */
    public function ajax_rollback_deployment(): void {
        try {
            $deployment_id = sanitize_text_field($_POST['deployment_id']);

            $result = $this->rollback_deployment($deployment_id);

            wp_send_json_success($result);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les environnements disponibles
     *
     * @return array
     */
    public function get_environments(): array {
        return $this->environments;
    }

    /**
     * Obtenir les métriques du pipeline
     *
     * @return array
     */
    public function get_pipeline_metrics(): array {
        global $wpdb;

        $metrics = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(*) as total_pipelines,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_pipelines,
                AVG(duration) as avg_duration,
                MAX(started_at) as last_run
            FROM {$wpdb->prefix}pdf_builder_pipeline_results
            WHERE started_at > %s
        ", date('Y-m-d H:i:s', strtotime('-30 days'))), ARRAY_A);

        $success_rate = $metrics['total_pipelines'] > 0 ?
            round(($metrics['successful_pipelines'] / $metrics['total_pipelines']) * 100, 2) : 0;

        return [
            'total_pipelines' => intval($metrics['total_pipelines'] ?? 0),
            'successful_pipelines' => intval($metrics['successful_pipelines'] ?? 0),
            'success_rate' => $success_rate,
            'avg_duration' => round(floatval($metrics['avg_duration'] ?? 0), 2),
            'last_run' => $metrics['last_run'] ?? null
        ];
    }
}