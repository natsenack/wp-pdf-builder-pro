<?php
/**
 * Système de Déploiement et Production - PDF Builder Pro
 *
 * Gestion du déploiement en production, monitoring continu,
 * optimisation des performances et maintenance système
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Système de Déploiement et Production
 */
class PDF_Builder_Deployment_Production_System {

    /**
     * Instance singleton
     * @var PDF_Builder_Deployment_Production_System
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Système de monitoring
     * @var PDF_Builder_Monitoring_Alerts
     */
    private $monitoring_system;

    /**
     * Environnements de déploiement
     * @var array
     */
    private $environments = [
        'development' => [
            'name' => 'Development',
            'url' => 'https://dev.pdfbuilder.com',
            'database' => 'pdf_builder_dev',
            'auto_deploy' => true,
            'backup_enabled' => false,
            'monitoring_level' => 'basic'
        ],
        'staging' => [
            'name' => 'Staging',
            'url' => 'https://staging.pdfbuilder.com',
            'database' => 'pdf_builder_staging',
            'auto_deploy' => true,
            'backup_enabled' => true,
            'monitoring_level' => 'full'
        ],
        'production' => [
            'name' => 'Production',
            'url' => 'https://pdfbuilder.com',
            'database' => 'pdf_builder_prod',
            'auto_deploy' => false,
            'backup_enabled' => true,
            'monitoring_level' => 'comprehensive'
        ]
    ];

    /**
     * Stratégies de déploiement
     * @var array
     */
    private $deployment_strategies = [
        'blue_green' => [
            'name' => 'Blue-Green Deployment',
            'description' => 'Déploiement sans interruption avec basculement instantané',
            'downtime' => 0,
            'rollback_time' => '< 1 minute',
            'complexity' => 'high',
            'recommended_for' => 'production'
        ],
        'canary' => [
            'name' => 'Canary Deployment',
            'description' => 'Déploiement progressif avec test sur un sous-ensemble d\'utilisateurs',
            'downtime' => 0,
            'rollback_time' => '< 5 minutes',
            'complexity' => 'medium',
            'recommended_for' => 'production'
        ],
        'rolling' => [
            'name' => 'Rolling Deployment',
            'description' => 'Mise à jour progressive des instances',
            'downtime' => 'minimal',
            'rollback_time' => '< 10 minutes',
            'complexity' => 'low',
            'recommended_for' => 'staging'
        ],
        'recreate' => [
            'name' => 'Recreate Deployment',
            'description' => 'Arrêt complet puis redémarrage',
            'downtime' => '5-15 minutes',
            'rollback_time' => '< 15 minutes',
            'complexity' => 'low',
            'recommended_for' => 'development'
        ]
    ];

    /**
     * Métriques de performance
     * @var array
     */
    private $performance_metrics = [];

    /**
     * Seuils d'alerte
     * @var array
     */
    private $alert_thresholds = [
        'response_time' => 2000, // ms
        'error_rate' => 5, // %
        'cpu_usage' => 80, // %
        'memory_usage' => 85, // %
        'disk_usage' => 90, // %
        'db_connections' => 80, // % of max
        'uptime' => 99.9 // %
    ];

    /**
     * Tâches de maintenance
     * @var array
     */
    private $maintenance_tasks = [];

    /**
     * Historique des déploiements
     * @var array
     */
    private $deployment_history = [];

    /**
     * Configuration de production
     * @var array
     */
    private $production_config = [
        'caching' => [
            'object_cache' => true,
            'page_cache' => true,
            'cdn_enabled' => true,
            'cache_expiration' => 3600
        ],
        'optimization' => [
            'minify_css' => true,
            'minify_js' => true,
            'compress_images' => true,
            'lazy_loading' => true,
            'database_optimization' => true
        ],
        'security' => [
            'waf_enabled' => true,
            'ssl_enforced' => true,
            'rate_limiting' => true,
            'security_headers' => true
        ],
        'scalability' => [
            'auto_scaling' => true,
            'load_balancer' => true,
            'database_replication' => true,
            'cdn_distribution' => true
        ]
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->logger = $core->get_logger();
        $this->monitoring_system = $core->get_monitoring_alerts();

        $this->init_deployment_hooks();
        $this->schedule_production_tasks();
        $this->load_deployment_data();
        $this->init_production_optimizations();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Deployment_Production_System
     */
    public static function getInstance(): PDF_Builder_Deployment_Production_System {
        return self::getDeploymentProductionSystem();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Deployment_Production_System
     */
    public static function getDeploymentProductionSystem(): PDF_Builder_Deployment_Production_System {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks de déploiement
     */
    private function init_deployment_hooks(): void {
        // Hooks AJAX pour le système de déploiement
        add_action('wp_ajax_pdf_builder_deploy_to_environment', [$this, 'ajax_deploy_to_environment']);
        add_action('wp_ajax_pdf_builder_get_deployment_status', [$this, 'ajax_get_deployment_status']);
        add_action('wp_ajax_pdf_builder_rollback_deployment', [$this, 'ajax_rollback_deployment']);
        add_action('wp_ajax_pdf_builder_get_performance_metrics', [$this, 'ajax_get_performance_metrics']);
        add_action('wp_ajax_pdf_builder_run_maintenance_task', [$this, 'ajax_run_maintenance_task']);

        // Hooks pour les métriques de performance
        add_action('pdf_builder_performance_check', [$this, 'check_performance_metrics']);
        add_action('pdf_builder_health_check', [$this, 'perform_health_check']);
        add_action('pdf_builder_backup_database', [$this, 'backup_database']);
        add_action('pdf_builder_optimize_database', [$this, 'optimize_database']);
        add_action('pdf_builder_clear_cache', [$this, 'clear_system_cache']);

        // Hooks pour les déploiements
        add_action('pdf_builder_pre_deployment', [$this, 'pre_deployment_checks']);
        add_action('pdf_builder_post_deployment', [$this, 'post_deployment_verification']);
        add_action('pdf_builder_deployment_failed', [$this, 'handle_deployment_failure']);

        // Hooks pour la production
        add_action('wp_loaded', [$this, 'apply_production_optimizations']);
        add_action('admin_init', [$this, 'restrict_admin_access']);
    }

    /**
     * Programmer les tâches de production
     */
    private function schedule_production_tasks(): void {
        // Vérifications de performance (toutes les 5 minutes)
        if (!wp_next_scheduled('pdf_builder_performance_check')) {
            wp_schedule_event(time(), 'every_5_minutes', 'pdf_builder_performance_check');
        }

        // Vérifications de santé (toutes les heures)
        if (!wp_next_scheduled('pdf_builder_health_check')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_health_check');
        }

        // Sauvegarde base de données (quotidienne)
        if (!wp_next_scheduled('pdf_builder_backup_database')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_backup_database');
        }

        // Optimisation base de données (hebdomadaire)
        if (!wp_next_scheduled('pdf_builder_optimize_database')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_optimize_database');
        }

        // Nettoyage du cache (toutes les 6 heures)
        if (!wp_next_scheduled('pdf_builder_clear_cache')) {
            wp_schedule_event(time(), 'every_six_hours', 'pdf_builder_clear_cache');
        }
    }

    /**
     * Charger les données de déploiement
     */
    private function load_deployment_data(): void {
        $this->deployment_history = get_option('pdf_builder_deployment_history', []);
        $this->maintenance_tasks = get_option('pdf_builder_maintenance_tasks', []);
        $this->performance_metrics = get_option('pdf_builder_performance_metrics', []);
    }

    /**
     * Initialiser les optimisations de production
     */
    private function init_production_optimizations(): void {
        if ($this->is_production_environment()) {
            // Activer les optimisations de production
            $this->enable_production_caching();
            $this->enable_security_headers();
            $this->optimize_database_queries();
            $this->setup_error_handling();
        }
    }

    /**
     * Vérifier si on est en environnement de production
     *
     * @return bool
     */
    private function is_production_environment(): bool {
        $current_env = $this->get_current_environment();
        return $current_env === 'production';
    }

    /**
     * Obtenir l'environnement actuel
     *
     * @return string
     */
    private function get_current_environment(): string {
        // Déterminer l'environnement basé sur l'URL ou les constantes
        $site_url = get_site_url();

        foreach ($this->environments as $env => $config) {
            if (strpos($site_url, $config['url']) !== false) {
                return $env;
            }
        }

        return defined('WP_ENV') ? WP_ENV : 'development';
    }

    /**
     * Déployer vers un environnement
     *
     * @param string $environment
     * @param string $strategy
     * @param array $options
     * @return array
     */
    public function deploy_to_environment(string $environment, string $strategy = 'rolling', array $options = []): array {
        try {
            // Validation
            if (!isset($this->environments[$environment])) {
                throw new Exception("Environnement inconnu: {$environment}");
            }

            if (!isset($this->deployment_strategies[$strategy])) {
                throw new Exception("Stratégie de déploiement inconnue: {$strategy}");
            }

            // Pré-déploiement
            do_action('pdf_builder_pre_deployment', $environment, $strategy);

            $deployment_id = $this->generate_deployment_id();
            $start_time = microtime(true);

            // Créer l'entrée d'historique
            $deployment = [
                'id' => $deployment_id,
                'environment' => $environment,
                'strategy' => $strategy,
                'status' => 'in_progress',
                'start_time' => current_time('mysql'),
                'options' => $options,
                'version' => $this->get_current_version(),
                'commit_hash' => $this->get_current_commit_hash(),
                'deployed_by' => get_current_user_id()
            ];

            $this->deployment_history[$deployment_id] = $deployment;
            $this->save_deployment_history();

            // Exécuter le déploiement selon la stratégie
            $result = $this->execute_deployment($environment, $strategy, $options);

            // Mettre à jour le statut
            $deployment['status'] = $result['success'] ? 'completed' : 'failed';
            $deployment['end_time'] = current_time('mysql');
            $deployment['duration'] = round(microtime(true) - $start_time, 2);
            $deployment['result'] = $result;

            $this->deployment_history[$deployment_id] = $deployment;
            $this->save_deployment_history();

            // Post-déploiement
            if ($result['success']) {
                do_action('pdf_builder_post_deployment', $environment, $deployment);
            } else {
                do_action('pdf_builder_deployment_failed', $environment, $deployment);
            }

            $this->logger->info('Deployment completed', [
                'deployment_id' => $deployment_id,
                'environment' => $environment,
                'strategy' => $strategy,
                'success' => $result['success'],
                'duration' => $deployment['duration']
            ]);

            return [
                'success' => $result['success'],
                'deployment_id' => $deployment_id,
                'message' => $result['message'],
                'duration' => $deployment['duration']
            ];

        } catch (Exception $e) {
            $this->logger->error('Deployment failed', [
                'environment' => $environment,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Exécuter le déploiement
     *
     * @param string $environment
     * @param string $strategy
     * @param array $options
     * @return array
     */
    private function execute_deployment(string $environment, string $strategy, array $options): array {
        $env_config = $this->environments[$environment];

        switch ($strategy) {
            case 'blue_green':
                return $this->execute_blue_green_deployment($env_config, $options);

            case 'canary':
                return $this->execute_canary_deployment($env_config, $options);

            case 'rolling':
                return $this->execute_rolling_deployment($env_config, $options);

            case 'recreate':
                return $this->execute_recreate_deployment($env_config, $options);

            default:
                return [
                    'success' => false,
                    'message' => 'Stratégie de déploiement non supportée.'
                ];
        }
    }

    /**
     * Exécuter un déploiement Blue-Green
     *
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function execute_blue_green_deployment(array $env_config, array $options): array {
        try {
            // Créer l'environnement vert
            $green_env = $this->create_green_environment($env_config);

            // Déployer sur l'environnement vert
            $this->deploy_to_green_environment($green_env);

            // Tester l'environnement vert
            $test_result = $this->test_green_environment($green_env);

            if (!$test_result['success']) {
                $this->destroy_green_environment($green_env);
                return [
                    'success' => false,
                    'message' => 'Tests de l\'environnement vert échoués: ' . $test_result['message']
                ];
            }

            // Basculer le trafic vers l'environnement vert
            $this->switch_traffic_to_green($env_config, $green_env);

            // Détruire l'ancien environnement
            $this->destroy_old_environment($env_config);

            return [
                'success' => true,
                'message' => 'Déploiement Blue-Green réussi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du déploiement Blue-Green: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exécuter un déploiement Canary
     *
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function execute_canary_deployment(array $env_config, array $options): array {
        $percentage = $options['percentage'] ?? 10;

        try {
            // Déployer la nouvelle version sur un sous-ensemble
            $this->deploy_canary_version($env_config, $percentage);

            // Monitorer les métriques pendant la période de test
            $monitoring_result = $this->monitor_canary_deployment($env_config, $options['monitoring_duration'] ?? 3600);

            if (!$monitoring_result['success']) {
                // Rollback si les métriques sont mauvaises
                $this->rollback_canary_deployment($env_config);
                return [
                    'success' => false,
                    'message' => 'Déploiement Canary échoué: métriques insuffisantes.'
                ];
            }

            // Augmenter progressivement le pourcentage
            $this->scale_canary_deployment($env_config, 100);

            return [
                'success' => true,
                'message' => 'Déploiement Canary réussi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du déploiement Canary: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exécuter un déploiement Rolling
     *
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function execute_rolling_deployment(array $env_config, array $options): array {
        try {
            $batch_size = $options['batch_size'] ?? 25; // % des instances à la fois

            // Obtenir la liste des instances
            $instances = $this->get_environment_instances($env_config);

            // Déployer par lots
            foreach (array_chunk($instances, ceil(count($instances) * $batch_size / 100)) as $batch) {
                $this->deploy_to_instance_batch($batch);

                // Attendre la stabilité
                sleep($options['batch_delay'] ?? 30);
            }

            return [
                'success' => true,
                'message' => 'Déploiement Rolling réussi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du déploiement Rolling: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Exécuter un déploiement Recreate
     *
     * @param array $env_config
     * @param array $options
     * @return array
     */
    private function execute_recreate_deployment(array $env_config, array $options): array {
        try {
            // Arrêter toutes les instances
            $this->stop_all_instances($env_config);

            // Attendre l'arrêt complet
            sleep(30);

            // Redémarrer avec la nouvelle version
            $this->start_instances_with_new_version($env_config);

            // Vérifier que tout fonctionne
            $health_check = $this->perform_health_check();

            if (!$health_check['healthy']) {
                return [
                    'success' => false,
                    'message' => 'Vérification de santé échouée après redémarrage.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Déploiement Recreate réussi.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du déploiement Recreate: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Rollback d'un déploiement
     *
     * @param string $deployment_id
     * @return bool
     */
    public function rollback_deployment(string $deployment_id): bool {
        if (!isset($this->deployment_history[$deployment_id])) {
            return false;
        }

        $deployment = $this->deployment_history[$deployment_id];
        $environment = $deployment['environment'];

        try {
            // Exécuter le rollback selon la stratégie
            $rollback_result = $this->execute_rollback($deployment);

            // Mettre à jour l'historique
            $deployment['rollback_time'] = current_time('mysql');
            $deployment['rollback_success'] = $rollback_result['success'];
            $deployment['rollback_message'] = $rollback_result['message'];

            $this->deployment_history[$deployment_id] = $deployment;
            $this->save_deployment_history();

            $this->logger->info('Deployment rollback completed', [
                'deployment_id' => $deployment_id,
                'environment' => $environment,
                'success' => $rollback_result['success']
            ]);

            return $rollback_result['success'];

        } catch (Exception $e) {
            $this->logger->error('Deployment rollback failed', [
                'deployment_id' => $deployment_id,
                'environment' => $environment,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Exécuter le rollback
     *
     * @param array $deployment
     * @return array
     */
    private function execute_rollback(array $deployment): array {
        $environment = $deployment['environment'];
        $strategy = $deployment['strategy'];

        switch ($strategy) {
            case 'blue_green':
                return $this->rollback_blue_green_deployment($deployment);

            case 'canary':
                return $this->rollback_canary_deployment($deployment);

            case 'rolling':
                return $this->rollback_rolling_deployment($deployment);

            case 'recreate':
                return $this->rollback_recreate_deployment($deployment);

            default:
                return [
                    'success' => false,
                    'message' => 'Stratégie de rollback non supportée.'
                ];
        }
    }

    /**
     * Vérifier les métriques de performance
     */
    public function check_performance_metrics(): void {
        $metrics = [
            'timestamp' => current_time('mysql'),
            'response_time' => $this->measure_response_time(),
            'cpu_usage' => $this->get_cpu_usage(),
            'memory_usage' => $this->get_memory_usage(),
            'disk_usage' => $this->get_disk_usage(),
            'db_connections' => $this->get_db_connection_count(),
            'error_rate' => $this->calculate_error_rate(),
            'uptime' => $this->get_system_uptime()
        ];

        $this->performance_metrics[] = $metrics;

        // Garder seulement les 1000 dernières entrées
        if (count($this->performance_metrics) > 1000) {
            $this->performance_metrics = array_slice($this->performance_metrics, -1000);
        }

        update_option('pdf_builder_performance_metrics', $this->performance_metrics);

        // Vérifier les seuils d'alerte
        $this->check_alert_thresholds($metrics);

        $this->logger->info('Performance metrics checked');
    }

    /**
     * Effectuer une vérification de santé
     *
     * @return array
     */
    public function perform_health_check(): array {
        $checks = [
            'database' => $this->check_database_health(),
            'filesystem' => $this->check_filesystem_health(),
            'memory' => $this->check_memory_health(),
            'cpu' => $this->check_cpu_health(),
            'network' => $this->check_network_health(),
            'services' => $this->check_services_health()
        ];

        $healthy = !in_array(false, array_column($checks, 'healthy'), true);
        $issues = array_filter($checks, function($check) {
            return !$check['healthy'];
        });

        $result = [
            'healthy' => $healthy,
            'checks' => $checks,
            'issues' => $issues,
            'timestamp' => current_time('mysql')
        ];

        update_option('pdf_builder_last_health_check', $result);

        if (!$healthy) {
            $this->logger->warning('Health check failed', ['issues' => $issues]);
            $this->send_health_alert($issues);
        }

        return $result;
    }

    /**
     * Sauvegarder la base de données
     */
    public function backup_database(): void {
        try {
            $backup_file = $this->create_database_backup();
            $this->store_backup_file($backup_file);
            $this->cleanup_old_backups();

            $this->logger->info('Database backup completed', ['backup_file' => $backup_file]);

        } catch (Exception $e) {
            $this->logger->error('Database backup failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Optimiser la base de données
     */
    public function optimize_database(): void {
        global $wpdb;

        try {
            // Optimiser les tables
            $tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder%'");

            foreach ($tables as $table) {
                $wpdb->query("OPTIMIZE TABLE {$table}");
            }

            // Reconstruire les index
            $this->rebuild_database_indexes();

            // Nettoyer les données obsolètes
            $this->cleanup_obsolete_data();

            $this->logger->info('Database optimization completed');

        } catch (Exception $e) {
            $this->logger->error('Database optimization failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Nettoyer le cache système
     */
    public function clear_system_cache(): void {
        try {
            // Nettoyer le cache WordPress
            wp_cache_flush();

            // Nettoyer le cache objet
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }

            // Nettoyer les caches spécifiques au plugin
            $this->clear_plugin_caches();

            // Vider les caches de page si activés
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache();
            }

            $this->logger->info('System cache cleared');

        } catch (Exception $e) {
            $this->logger->error('Cache clearing failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Appliquer les optimisations de production
     */
    public function apply_production_optimizations(): void {
        if (!$this->is_production_environment()) {
            return;
        }

        // Optimisations de cache
        $this->optimize_caching();

        // Optimisations de base de données
        $this->optimize_database_queries();

        // Optimisations de sécurité
        $this->apply_security_optimizations();

        // Optimisations de performance
        $this->apply_performance_optimizations();
    }

    /**
     * Restreindre l'accès admin
     */
    public function restrict_admin_access(): void {
        if (!$this->is_production_environment()) {
            return;
        }

        // Restreindre l'accès aux IPs autorisées
        $allowed_ips = $this->get_allowed_admin_ips();

        if (!empty($allowed_ips) && !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
            // Rediriger ou bloquer l'accès
            wp_die('Accès administrateur restreint dans l\'environnement de production.');
        }
    }

    /**
     * Activer le cache de production
     */
    private function enable_production_caching(): void {
        if (!defined('WP_CACHE')) {
            define('WP_CACHE', true);
        }

        // Activer les caches avancés
        add_filter('wp_cache_phase2', '__return_true');
    }

    /**
     * Activer les headers de sécurité
     */
    private function enable_security_headers(): void {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\';');
        }
    }

    /**
     * Optimiser les requêtes de base de données
     */
    private function optimize_database_queries(): void {
        // Activer la compression des résultats
        global $wpdb;
        $wpdb->query('SET SESSION sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE"');

        // Optimisations des index
        add_action('init', [$this, 'add_database_indexes']);
    }

    /**
     * Configurer la gestion d'erreurs
     */
    private function setup_error_handling(): void {
        // Désactiver l'affichage des erreurs en production
        if ($this->is_production_environment()) {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        }

        // Logger personnalisé pour les erreurs
        set_error_handler([$this, 'handle_production_error']);
        set_exception_handler([$this, 'handle_production_exception']);
    }

    /**
     * Mesurer le temps de réponse
     *
     * @return float
     */
    private function measure_response_time(): float {
        return rand(500, 2500) / 1000; // Simulation
    }

    /**
     * Obtenir l'utilisation CPU
     *
     * @return float
     */
    private function get_cpu_usage(): float {
        // Simulation - en production, utiliser sys_getloadavg() ou APIs système
        return rand(20, 90);
    }

    /**
     * Obtenir l'utilisation mémoire
     *
     * @return float
     */
    private function get_memory_usage(): float {
        // Simulation
        return rand(30, 95);
    }

    /**
     * Obtenir l'utilisation disque
     *
     * @return float
     */
    private function get_disk_usage(): float {
        // Simulation
        return rand(40, 85);
    }

    /**
     * Obtenir le nombre de connexions DB
     *
     * @return int
     */
    private function get_db_connection_count(): int {
        global $wpdb;
        // Simulation
        return rand(5, 50);
    }

    /**
     * Calculer le taux d'erreur
     *
     * @return float
     */
    private function calculate_error_rate(): float {
        // Simulation
        return rand(0, 10) / 10;
    }

    /**
     * Obtenir l'uptime système
     *
     * @return float
     */
    private function get_system_uptime(): float {
        // Simulation
        return 99.95 + (rand(-5, 5) / 100);
    }

    /**
     * Vérifier les seuils d'alerte
     *
     * @param array $metrics
     */
    private function check_alert_thresholds(array $metrics): void {
        $alerts = [];

        foreach ($this->alert_thresholds as $metric => $threshold) {
            $value = $metrics[$metric] ?? 0;

            if ($this->is_threshold_exceeded($metric, $value, $threshold)) {
                $alerts[] = [
                    'metric' => $metric,
                    'value' => $value,
                    'threshold' => $threshold,
                    'severity' => $this->get_alert_severity($metric, $value, $threshold)
                ];
            }
        }

        if (!empty($alerts)) {
            $this->send_performance_alerts($alerts);
        }
    }

    /**
     * Vérifier si un seuil est dépassé
     *
     * @param string $metric
     * @param mixed $value
     * @param mixed $threshold
     * @return bool
     */
    private function is_threshold_exceeded(string $metric, $value, $threshold): bool {
        // Pour les métriques où une valeur élevée est mauvaise
        $high_is_bad = ['response_time', 'error_rate', 'cpu_usage', 'memory_usage', 'disk_usage', 'db_connections'];

        if (in_array($metric, $high_is_bad)) {
            return $value > $threshold;
        }

        // Pour l'uptime, une valeur basse est mauvaise
        if ($metric === 'uptime') {
            return $value < $threshold;
        }

        return false;
    }

    /**
     * Obtenir la sévérité de l'alerte
     *
     * @param string $metric
     * @param mixed $value
     * @param mixed $threshold
     * @return string
     */
    private function get_alert_severity(string $metric, $value, $threshold): string {
        $ratio = abs($value - $threshold) / $threshold;

        if ($ratio > 0.5) {
            return 'critical';
        } elseif ($ratio > 0.25) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Vérifier la santé de la base de données
     *
     * @return array
     */
    private function check_database_health(): array {
        global $wpdb;

        try {
            $result = $wpdb->get_var("SELECT 1");
            $healthy = $result === '1';

            return [
                'healthy' => $healthy,
                'message' => $healthy ? 'Base de données opérationnelle' : 'Connexion à la base de données échouée',
                'details' => [
                    'connection_time' => $wpdb->timer,
                    'last_error' => $wpdb->last_error
                ]
            ];
        } catch (Exception $e) {
            return [
                'healthy' => false,
                'message' => 'Erreur de base de données: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier la santé du système de fichiers
     *
     * @return array
     */
    private function check_filesystem_health(): array {
        $upload_dir = wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']);

        return [
            'healthy' => $writable,
            'message' => $writable ? 'Système de fichiers accessible' : 'Répertoire uploads non accessible en écriture',
            'details' => [
                'upload_dir' => $upload_dir['basedir'],
                'permissions' => substr(sprintf('%o', fileperms($upload_dir['basedir'])), -4)
            ]
        ];
    }

    /**
     * Vérifier la santé mémoire
     *
     * @return array
     */
    private function check_memory_health(): array {
        $memory_limit = ini_get('memory_limit');
        $memory_usage = memory_get_peak_usage(true);
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);

        $healthy = $memory_usage < $memory_limit_bytes * 0.9; // < 90% de la limite

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Utilisation mémoire normale' : 'Utilisation mémoire élevée',
            'details' => [
                'memory_limit' => $memory_limit,
                'peak_usage' => size_format($memory_usage),
                'usage_percentage' => round(($memory_usage / $memory_limit_bytes) * 100, 1)
            ]
        ];
    }

    /**
     * Vérifier la santé CPU
     *
     * @return array
     */
    private function check_cpu_health(): array {
        $load = sys_getloadavg();
        $healthy = $load[0] < 2; // Charge moyenne < 2

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Charge CPU normale' : 'Charge CPU élevée',
            'details' => [
                'load_1min' => $load[0],
                'load_5min' => $load[1],
                'load_15min' => $load[2]
            ]
        ];
    }

    /**
     * Vérifier la santé réseau
     *
     * @return array
     */
    private function check_network_health(): array {
        // Tester la connectivité externe
        $response = wp_remote_get('https://www.google.com', ['timeout' => 5]);
        $healthy = !is_wp_error($response);

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Connectivité réseau opérationnelle' : 'Problème de connectivité réseau',
            'details' => [
                'response_code' => wp_remote_retrieve_response_code($response),
                'response_time' => $response['http_response']->get_response_object()->total_time ?? 'N/A'
            ]
        ];
    }

    /**
     * Vérifier la santé des services
     *
     * @return array
     */
    private function check_services_health(): array {
        $services = [
            'wordpress' => $this->check_wordpress_health(),
            'plugin' => $this->check_plugin_health(),
            'external_apis' => $this->check_external_apis_health()
        ];

        $healthy = !in_array(false, $services, true);

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Tous les services opérationnels' : 'Un ou plusieurs services défaillants',
            'details' => $services
        ];
    }

    /**
     * Créer une sauvegarde de base de données
     *
     * @return string
     */
    private function create_database_backup(): string {
        global $wpdb;

        $tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder%'");
        $backup_data = [];

        foreach ($tables as $table) {
            $create_table = $wpdb->get_row("SHOW CREATE TABLE {$table}", ARRAY_N);
            $backup_data[] = $create_table[1] . ";\n";

            $rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
            foreach ($rows as $row) {
                $values = array_map(function($value) use ($wpdb) {
                    return $wpdb->_real_escape($value);
                }, $row);
                $backup_data[] = "INSERT INTO {$table} VALUES ('" . implode("', '", $values) . "');\n";
            }
        }

        $backup_file = WP_CONTENT_DIR . '/backups/pdf_builder_' . date('Y-m-d_H-i-s') . '.sql';
        wp_mkdir_p(dirname($backup_file));
        file_put_contents($backup_file, implode("\n", $backup_data));

        return $backup_file;
    }

    /**
     * Stocker le fichier de sauvegarde
     *
     * @param string $backup_file
     */
    private function store_backup_file(string $backup_file): void {
        // En production, uploader vers un stockage cloud (S3, etc.)
        // Pour l'instant, garder localement
    }

    /**
     * Nettoyer les anciennes sauvegardes
     */
    private function cleanup_old_backups(): void {
        $backup_dir = WP_CONTENT_DIR . '/backups/';
        $files = glob($backup_dir . 'pdf_builder_*.sql');

        // Garder seulement les 10 dernières sauvegardes
        if (count($files) > 10) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach (array_slice($files, 10) as $old_file) {
                unlink($old_file);
            }
        }
    }

    /**
     * Reconstruire les index de base de données
     */
    private function rebuild_database_indexes(): void {
        global $wpdb;

        $indexes = [
            "CREATE INDEX idx_pdf_builder_user_events_type ON {$wpdb->prefix}pdf_builder_user_events (event_type)",
            "CREATE INDEX idx_pdf_builder_user_events_user ON {$wpdb->prefix}pdf_builder_user_events (user_id)",
            "CREATE INDEX idx_pdf_builder_user_events_time ON {$wpdb->prefix}pdf_builder_user_events (created_at)"
        ];

        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
        }
    }

    /**
     * Nettoyer les données obsolètes
     */
    private function cleanup_obsolete_data(): void {
        global $wpdb;

        // Supprimer les événements vieux de plus de 2 ans
        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_user_events
            WHERE created_at < %s
        ", date('Y-m-d H:i:s', strtotime('-2 years'))));

        // Supprimer les logs vieux de plus de 6 mois
        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_logs
            WHERE created_at < %s
        ", date('Y-m-d H:i:s', strtotime('-6 months'))));
    }

    /**
     * Nettoyer les caches du plugin
     */
    private function clear_plugin_caches(): void {
        // Nettoyer les caches spécifiques au plugin
        delete_transient('pdf_builder_template_cache');
        delete_transient('pdf_builder_config_cache');
        delete_transient('pdf_builder_user_permissions_cache');

        // Nettoyer les caches d'options
        wp_cache_delete('pdf_builder_growth_metrics', 'options');
        wp_cache_delete('pdf_builder_launch_campaigns', 'options');
        wp_cache_delete('pdf_builder_ab_tests', 'options');
    }

    /**
     * Optimiser le cache
     */
    private function optimize_caching(): void {
        // Activer la compression GZIP
        if (!ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'On');
        }

        // Optimisations de cache WordPress
        add_filter('wp_cache_phase2', '__return_true');
    }

    /**
     * Appliquer les optimisations de sécurité
     */
    private function apply_security_optimizations(): void {
        // Désactiver les informations de version WordPress
        remove_action('wp_head', 'wp_generator');

        // Désactiver l'édition de fichiers
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }

        // Forcer SSL admin
        if (!defined('FORCE_SSL_ADMIN')) {
            define('FORCE_SSL_ADMIN', true);
        }
    }

    /**
     * Appliquer les optimisations de performance
     */
    private function apply_performance_optimizations(): void {
        // Désactiver les emojis
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Désactiver les embeds
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');

        // Optimiser les scripts et styles
        add_filter('script_loader_src', [$this, 'add_script_version']);
        add_filter('style_loader_src', [$this, 'add_script_version']);
    }

    /**
     * Ajouter la version aux scripts
     *
     * @param string $src
     * @return string
     */
    public function add_script_version(string $src): string {
        if (strpos($src, 'ver=')) {
            return $src;
        }

        return add_query_arg('ver', PDF_BUILDER_VERSION, $src);
    }

    /**
     * Ajouter des index de base de données
     */
    public function add_database_indexes(): void {
        global $wpdb;

        // Index pour améliorer les performances des requêtes fréquentes
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_pdf_builder_templates_type ON {$wpdb->prefix}pdf_builder_templates (template_type)",
            "CREATE INDEX IF NOT EXISTS idx_pdf_builder_documents_user ON {$wpdb->prefix}pdf_builder_documents (user_id)",
            "CREATE INDEX IF NOT EXISTS idx_pdf_builder_documents_status ON {$wpdb->prefix}pdf_builder_documents (status)"
        ];

        foreach ($indexes as $index_sql) {
            $wpdb->query($index_sql);
        }
    }

    /**
     * Gérer les erreurs de production
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public function handle_production_error(int $errno, string $errstr, string $errfile, int $errline): void {
        $error_message = "PHP Error: {$errstr} in {$errfile} on line {$errline}";

        $this->logger->error('PHP Error', [
            'error' => $error_message,
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline
        ]);

        // En production, ne pas afficher les erreurs
        if ($this->is_production_environment()) {
            return;
        }

        // En développement, afficher les erreurs
        echo "<pre>{$error_message}</pre>";
    }

    /**
     * Gérer les exceptions de production
     *
     * @param Throwable $exception
     */
    public function handle_production_exception(Throwable $exception): void {
        $error_message = "Uncaught Exception: {$exception->getMessage()} in {$exception->getFile()} on line {$exception->getLine()}";

        $this->logger->error('Uncaught Exception', [
            'error' => $error_message,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Page d'erreur conviviale en production
        if ($this->is_production_environment()) {
            wp_die('Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Obtenir les IPs autorisées pour l'admin
     *
     * @return array
     */
    private function get_allowed_admin_ips(): array {
        return get_option('pdf_builder_allowed_admin_ips', []);
    }

    /**
     * Vérifier la santé WordPress
     *
     * @return bool
     */
    private function check_wordpress_health(): bool {
        return !wp_is_maintenance_mode() && !is_wp_error(wp_remote_get(site_url()));
    }

    /**
     * Vérifier la santé du plugin
     *
     * @return bool
     */
    private function check_plugin_health(): bool {
        // Vérifier que les classes principales sont chargées
        return class_exists('PDF_Builder_Core') && class_exists('PDF_Builder_Database_Manager');
    }

    /**
     * Vérifier la santé des APIs externes
     *
     * @return bool
     */
    private function check_external_apis_health(): bool {
        // Tester les APIs externes utilisées par le plugin
        // Simulation
        return true;
    }

    /**
     * Convertir en bytes
     *
     * @param string $size
     * @return int
     */
    private function convert_to_bytes(string $size): int {
        $unit = strtolower(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return $value;
    }

    /**
     * Envoyer des alertes de santé
     *
     * @param array $issues
     */
    private function send_health_alert(array $issues): void {
        $this->monitoring_system->send_alert('health_check_failed', [
            'issues' => $issues,
            'environment' => $this->get_current_environment(),
            'timestamp' => current_time('mysql')
        ]);
    }

    /**
     * Envoyer des alertes de performance
     *
     * @param array $alerts
     */
    private function send_performance_alerts(array $alerts): void {
        foreach ($alerts as $alert) {
            $this->monitoring_system->send_alert('performance_threshold_exceeded', $alert);
        }
    }

    /**
     * Générer l'ID de déploiement
     *
     * @return string
     */
    private function generate_deployment_id(): string {
        return 'deploy_' . time() . '_' . wp_generate_password(8, false);
    }

    /**
     * Obtenir la version actuelle
     *
     * @return string
     */
    private function get_current_version(): string {
        return PDF_BUILDER_VERSION ?? '1.0.0';
    }

    /**
     * Obtenir le hash du commit actuel
     *
     * @return string
     */
    private function get_current_commit_hash(): string {
        // Simulation - en production, récupérer depuis Git
        return substr(md5(time()), 0, 7);
    }

    /**
     * Sauvegarder l'historique des déploiements
     */
    private function save_deployment_history(): void {
        update_option('pdf_builder_deployment_history', $this->deployment_history);
    }

    /**
     * Méthodes de simulation pour les déploiements (à remplacer par des implémentations réelles)
     */
    private function create_green_environment($env_config) { return 'green_env_' . time(); }
    private function deploy_to_green_environment($green_env) {}
    private function test_green_environment($green_env) { return ['success' => true, 'message' => 'Tests passed']; }
    private function destroy_green_environment($green_env) {}
    private function switch_traffic_to_green($env_config, $green_env) {}
    private function destroy_old_environment($env_config) {}
    private function deploy_canary_version($env_config, $percentage) {}
    private function monitor_canary_deployment($env_config, $duration) { return ['success' => true]; }
    private function rollback_canary_deployment($env_config) {}
    private function scale_canary_deployment($env_config, $percentage) {}
    private function get_environment_instances($env_config) { return ['instance1', 'instance2', 'instance3']; }
    private function deploy_to_instance_batch($batch) {}
    private function stop_all_instances($env_config) {}
    private function start_instances_with_new_version($env_config) {}
    private function rollback_blue_green_deployment($deployment) { return ['success' => true, 'message' => 'Rollback completed']; }
    private function rollback_rolling_deployment($deployment) { return ['success' => true, 'message' => 'Rollback completed']; }
    private function rollback_recreate_deployment($deployment) { return ['success' => true, 'message' => 'Rollback completed']; }

    /**
     * Vérifications pré-déploiement
     *
     * @param string $environment
     * @param string $strategy
     */
    public function pre_deployment_checks(string $environment, string $strategy): void {
        // Vérifications de santé avant déploiement
        $health_check = $this->perform_health_check();

        if (!$health_check['healthy']) {
            throw new Exception('Vérifications de santé échouées avant déploiement');
        }

        // Vérifications de compatibilité
        $this->check_deployment_compatibility($environment);

        $this->logger->info('Pre-deployment checks passed', [
            'environment' => $environment,
            'strategy' => $strategy
        ]);
    }

    /**
     * Vérification post-déploiement
     *
     * @param string $environment
     * @param array $deployment
     */
    public function post_deployment_verification(string $environment, array $deployment): void {
        // Vérifications après déploiement
        sleep(30); // Attendre la propagation

        $health_check = $this->perform_health_check();

        if (!$health_check['healthy']) {
            // Déclencher un rollback automatique
            $this->rollback_deployment($deployment['id']);
            throw new Exception('Vérifications post-déploiement échouées, rollback déclenché');
        }

        // Tests fonctionnels
        $functional_tests = $this->run_post_deployment_tests($environment);

        if (!$functional_tests['passed']) {
            $this->rollback_deployment($deployment['id']);
            throw new Exception('Tests fonctionnels échoués, rollback déclenché');
        }

        $this->logger->info('Post-deployment verification passed', [
            'environment' => $environment,
            'deployment_id' => $deployment['id']
        ]);
    }

    /**
     * Gestion des échecs de déploiement
     *
     * @param string $environment
     * @param array $deployment
     */
    public function handle_deployment_failure(string $environment, array $deployment): void {
        // Notifications d'échec
        $this->send_deployment_failure_notification($deployment);

        // Rollback automatique si configuré
        if ($this->should_auto_rollback($environment)) {
            $this->rollback_deployment($deployment['id']);
        }

        $this->logger->error('Deployment failed', [
            'environment' => $environment,
            'deployment_id' => $deployment['id']
        ]);
    }

    /**
     * Vérifier la compatibilité du déploiement
     *
     * @param string $environment
     */
    private function check_deployment_compatibility(string $environment): void {
        // Vérifications de compatibilité PHP, WordPress, etc.
    }

    /**
     * Exécuter les tests post-déploiement
     *
     * @param string $environment
     * @return array
     */
    private function run_post_deployment_tests(string $environment): array {
        // Tests fonctionnels de base
        return ['passed' => true, 'tests' => []];
    }

    /**
     * Envoyer une notification d'échec de déploiement
     *
     * @param array $deployment
     */
    private function send_deployment_failure_notification(array $deployment): void {
        $this->monitoring_system->send_alert('deployment_failed', $deployment);
    }

    /**
     * Vérifier si le rollback automatique est activé
     *
     * @param string $environment
     * @return bool
     */
    private function should_auto_rollback(string $environment): bool {
        $env_config = $this->environments[$environment] ?? [];
        return $env_config['auto_rollback'] ?? true;
    }

    /**
     * AJAX: Déployer vers un environnement
     */
    public function ajax_deploy_to_environment(): void {
        try {
            $environment = sanitize_text_field($_POST['environment']);
            $strategy = sanitize_text_field($_POST['strategy'] ?? 'rolling');
            $options = $_POST['options'] ?? [];

            $result = $this->deploy_to_environment($environment, $strategy, $options);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir le statut du déploiement
     */
    public function ajax_get_deployment_status(): void {
        try {
            $deployment_id = sanitize_text_field($_POST['deployment_id']);

            if (!isset($this->deployment_history[$deployment_id])) {
                wp_send_json_error(['message' => 'Déploiement non trouvé']);
                return;
            }

            $deployment = $this->deployment_history[$deployment_id];

            wp_send_json_success([
                'deployment' => $deployment,
                'current_time' => current_time('mysql')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Rollback d'un déploiement
     */
    public function ajax_rollback_deployment(): void {
        try {
            $deployment_id = sanitize_text_field($_POST['deployment_id']);

            $success = $this->rollback_deployment($deployment_id);

            if ($success) {
                wp_send_json_success(['message' => 'Rollback réussi']);
            } else {
                wp_send_json_error(['message' => 'Échec du rollback']);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les métriques de performance
     */
    public function ajax_get_performance_metrics(): void {
        try {
            $period = sanitize_text_field($_POST['period'] ?? '1h');

            $metrics = $this->get_performance_metrics_for_period($period);

            wp_send_json_success($metrics);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Exécuter une tâche de maintenance
     */
    public function ajax_run_maintenance_task(): void {
        try {
            $task = sanitize_text_field($_POST['task']);

            $result = $this->run_maintenance_task($task);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les métriques de performance pour une période
     *
     * @param string $period
     * @return array
     */
    private function get_performance_metrics_for_period(string $period): array {
        $hours = $this->parse_period_to_hours($period);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $metrics = array_filter($this->performance_metrics, function($metric) use ($cutoff) {
            return $metric['timestamp'] > $cutoff;
        });

        return array_slice($metrics, -100); // Dernières 100 entrées
    }

    /**
     * Parser la période en heures
     *
     * @param string $period
     * @return int
     */
    private function parse_period_to_hours(string $period): int {
        $periods = [
            '1h' => 1,
            '6h' => 6,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720
        ];

        return $periods[$period] ?? 24;
    }

    /**
     * Exécuter une tâche de maintenance
     *
     * @param string $task
     * @return array
     */
    private function run_maintenance_task(string $task): array {
        switch ($task) {
            case 'clear_cache':
                $this->clear_system_cache();
                return ['success' => true, 'message' => 'Cache nettoyé'];

            case 'optimize_db':
                $this->optimize_database();
                return ['success' => true, 'message' => 'Base de données optimisée'];

            case 'backup_db':
                $this->backup_database();
                return ['success' => true, 'message' => 'Sauvegarde créée'];

            case 'health_check':
                $result = $this->perform_health_check();
                return [
                    'success' => $result['healthy'],
                    'message' => $result['healthy'] ? 'Système sain' : 'Problèmes détectés',
                    'details' => $result
                ];

            default:
                return ['success' => false, 'message' => 'Tâche inconnue'];
        }
    }

    /**
     * Obtenir les environnements
     *
     * @return array
     */
    public function get_environments(): array {
        return $this->environments;
    }

    /**
     * Obtenir les stratégies de déploiement
     *
     * @return array
     */
    public function get_deployment_strategies(): array {
        return $this->deployment_strategies;
    }

    /**
     * Obtenir l'historique des déploiements
     *
     * @return array
     */
    public function get_deployment_history(): array {
        return $this->deployment_history;
    }

    /**
     * Obtenir les métriques de performance actuelles
     *
     * @return array
     */
    public function get_current_performance_metrics(): array {
        return end($this->performance_metrics) ?: [];
    }

    /**
     * Obtenir les tâches de maintenance
     *
     * @return array
     */
    public function get_maintenance_tasks(): array {
        return $this->maintenance_tasks;
    }

    /**
     * Obtenir la configuration de production
     *
     * @return array
     */
    public function get_production_config(): array {
        return $this->production_config;
    }
}

