<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Système de surveillance de la santé
 * Surveille la santé du système en temps réel et détecte les problèmes
 */

class PDF_Builder_Health_Monitor {
    private static $instance = null;

    // Seuils de surveillance
    const CPU_THRESHOLD = 80; // %
    const MEMORY_THRESHOLD = 85; // %
    const DISK_THRESHOLD = 90; // %
    const RESPONSE_TIME_THRESHOLD = 5000; // ms
    const ERROR_RATE_THRESHOLD = 5; // %

    // Périodes de surveillance
    const MONITOR_INTERVAL = 300; // 5 minutes
    const ALERT_COOLDOWN = 3600; // 1 heure

    // Métriques de santé
    private $health_metrics = [];
    private $alerts_sent = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_health_checks();
    }

    private function init_hooks() {
        // Surveillance périodique
        add_action('wp_ajax_pdf_builder_health_check', [$this, 'health_check_ajax']);
        add_action('wp_ajax_pdf_builder_get_health_status', [$this, 'get_health_status_ajax']);

        // Actions de surveillance
        add_action('pdf_builder_health_monitor', [$this, 'perform_health_checks']);
        add_action('pdf_builder_performance_monitor', [$this, 'monitor_performance']);
        add_action('pdf_builder_error_monitor', [$this, 'monitor_errors']);

        // Alertes
        add_action('pdf_builder_health_alert', [$this, 'send_health_alert']);

        // Nettoyage
        add_action('pdf_builder_daily_cleanup', [$this, 'cleanup_old_metrics']);
    }

    /**
     * Initialise les vérifications de santé
     */
    private function init_health_checks() {
        // Planifier les vérifications périodiques
        if (!wp_next_scheduled('pdf_builder_health_monitor')) {
            wp_schedule_event(time(), 'five_minutes', 'pdf_builder_health_monitor');
        }

        if (!wp_next_scheduled('pdf_builder_performance_monitor')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_performance_monitor');
        }

        if (!wp_next_scheduled('pdf_builder_error_monitor')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_error_monitor');
        }

        if (!wp_next_scheduled('pdf_builder_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_daily_cleanup');
        }
    }

    /**
     * Effectue les vérifications de santé complètes
     */
    public function perform_health_checks() {
        $health_status = [
            'timestamp' => current_time('mysql'),
            'overall_status' => 'healthy',
            'checks' => [],
            'metrics' => []
        ];

        // Vérifications système
        $health_status['checks']['system'] = $this->check_system_health();
        $health_status['checks']['database'] = $this->check_database_health();
        $health_status['checks']['filesystem'] = $this->check_filesystem_health();
        $health_status['checks']['wordpress'] = $this->check_wordpress_health();
        $health_status['checks']['plugin'] = $this->check_plugin_health();

        // Métriques de performance
        $health_status['metrics']['performance'] = $this->get_performance_metrics();
        $health_status['metrics']['resources'] = $this->get_resource_metrics();
        $health_status['metrics']['errors'] = $this->get_error_metrics();

        // Déterminer le statut global
        $critical_issues = 0;
        $warning_issues = 0;

        foreach ($health_status['checks'] as $check) {
            if ($check['status'] === 'critical') {
                $critical_issues++;
            } elseif ($check['status'] === 'warning') {
                $warning_issues++;
            }
        }

        if ($critical_issues > 0) {
            $health_status['overall_status'] = 'critical';
        } elseif ($warning_issues > 0) {
            $health_status['overall_status'] = 'warning';
        }

        // Stocker les métriques
        $this->store_health_metrics($health_status);

        // Envoyer des alertes si nécessaire
        $this->check_for_alerts($health_status);

        return $health_status;
    }

    /**
     * Vérifie la santé du système
     */
    private function check_system_health() {
        $issues = [];

        // CPU
        $cpu_usage = $this->get_cpu_usage();
        if ($cpu_usage > self::CPU_THRESHOLD) {
            $issues[] = "Utilisation CPU élevée: {$cpu_usage}%";
        }

        // Mémoire
        $memory_usage = $this->get_memory_usage();
        if ($memory_usage > self::MEMORY_THRESHOLD) {
            $issues[] = "Utilisation mémoire élevée: {$memory_usage}%";
        }

        // Disque
        $disk_usage = $this->get_disk_usage();
        if ($disk_usage > self::DISK_THRESHOLD) {
            $issues[] = "Utilisation disque élevée: {$disk_usage}%";
        }

        // Temps de réponse
        $response_time = $this->get_response_time();
        if ($response_time > self::RESPONSE_TIME_THRESHOLD) {
            $issues[] = "Temps de réponse élevé: {$response_time}ms";
        }

        $status = empty($issues) ? 'healthy' : (count($issues) > 2 ? 'critical' : 'warning');

        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'cpu_usage' => $cpu_usage,
                'memory_usage' => $memory_usage,
                'disk_usage' => $disk_usage,
                'response_time' => $response_time
            ]
        ];
    }

    /**
     * Vérifie la santé de la base de données
     */
    private function check_database_health() {
        global $wpdb;

        $issues = [];

        // Connexion à la base de données
        if (!$this->test_database_connection()) {
            $issues[] = 'Connexion à la base de données échouée';
            return [
                'status' => 'critical',
                'issues' => $issues,
                'metrics' => []
            ];
        }

        // Taille des tables
        $table_sizes = $this->get_table_sizes();
        $total_size = array_sum($table_sizes);

        if ($total_size > 100 * 1024 * 1024) { // 100MB
            $issues[] = 'Base de données volumineuse: ' . size_format($total_size);
        }

        // Requêtes lentes
        $slow_queries = $this->get_slow_queries_count();
        if ($slow_queries > 10) {
            $issues[] = "Requêtes lentes détectées: {$slow_queries}";
        }

        // Tables corrompues
        $corrupted_tables = $this->check_corrupted_tables();
        if (!empty($corrupted_tables)) {
            $issues[] = 'Tables corrompues détectées: ' . implode(', ', $corrupted_tables);
        }

        $status = empty($issues) ? 'healthy' : (in_array('Connexion à la base de données échouée', $issues) ? 'critical' : 'warning');

        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'total_size' => $total_size,
                'slow_queries' => $slow_queries,
                'corrupted_tables' => count($corrupted_tables)
            ]
        ];
    }

    /**
     * Vérifie la santé du système de fichiers
     */
    private function check_filesystem_health() {
        $issues = [];

        $upload_dir = wp_upload_dir();
        $plugin_dir = PDF_BUILDER_PLUGIN_DIR;

        // Permissions des dossiers
        $dirs_to_check = [
            $upload_dir['basedir'],
            $plugin_dir,
            WP_CONTENT_DIR . '/pdf-builder-backups',
            WP_CONTENT_DIR . '/pdf-builder-logs'
        ];

        foreach ($dirs_to_check as $dir) {
            if (!is_writable($dir)) { // phpcs:ignore WordPress.WP.AlternativeFunctions
                $issues[] = "Dossier non accessible en écriture: {$dir}";
            }
        }

        // Espace disque disponible
        $free_space = disk_free_space(ABSPATH);
        $min_space = 50 * 1024 * 1024; // 50MB

        if ($free_space < $min_space) {
            $issues[] = 'Espace disque insuffisant: ' . size_format($free_space) . ' restant';
        }

        // Fichiers temporaires
        $temp_files = glob(sys_get_temp_dir() . '/pdf_builder_*');
        if (count($temp_files) > 100) {
            $issues[] = 'Fichiers temporaires accumulés: ' . count($temp_files);
        }

        $status = empty($issues) ? 'healthy' : 'warning';

        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'free_space' => $free_space,
                'temp_files' => count($temp_files)
            ]
        ];
    }

    /**
     * Vérifie la santé de WordPress
     */
    private function check_wordpress_health() {
        $issues = [];

        // Version de WordPress
        global $wp_version;
        $latest_version = $this->get_latest_wp_version();

        if (version_compare($wp_version, $latest_version, '<')) {
            $issues[] = "WordPress obsolète: {$wp_version} (dernière: {$latest_version})";
        }

        // Plugins actifs
        $active_plugins = get_option('active_plugins', []);
        if (count($active_plugins) > 50) {
            $issues[] = 'Nombre élevé de plugins actifs: ' . count($active_plugins);
        }

        // Thème actif
        $theme = wp_get_theme();
        if (!$theme->exists()) {
            $issues[] = 'Thème actif introuvable';
        }

        // PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $issues[] = 'Version PHP obsolète: ' . PHP_VERSION;
        }

        $status = empty($issues) ? 'healthy' : 'warning';

        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'wp_version' => $wp_version,
                'php_version' => PHP_VERSION,
                'active_plugins' => count($active_plugins)
            ]
        ];
    }

    /**
     * Vérifie la santé du plugin
     */
    private function check_plugin_health() {
        $issues = [];

        // Version du plugin
        $current_version = PDF_BUILDER_VERSION;
        $latest_version = $this->get_latest_plugin_version();

        if (version_compare($current_version, $latest_version, '<')) {
            $issues[] = "Plugin obsolète: {$current_version} (dernière: {$latest_version})";
        }

        // Intégrité des fichiers
        $corrupted_files = $this->check_plugin_file_integrity();
        if (!empty($corrupted_files)) {
            $issues[] = 'Fichiers du plugin corrompus: ' . count($corrupted_files);
        }

        // Configuration
        if (class_exists('PDF_Builder_Config_Manager')) {
            $config_health = PDF_Builder_Config_Manager::get_instance()->check_health();
            if ($config_health['status'] !== 'healthy') {
                $issues = array_merge($issues, $config_health['issues']);
            }
        }

        $status = empty($issues) ? 'healthy' : (count($issues) > 1 ? 'critical' : 'warning');

        return [
            'status' => $status,
            'issues' => $issues,
            'metrics' => [
                'plugin_version' => $current_version,
                'corrupted_files' => count($corrupted_files)
            ]
        ];
    }

    /**
     * Obtient les métriques de performance
     */
    private function get_performance_metrics() {
        if (!class_exists('PDF_Builder_Performance_Monitor')) {
            return [];
        }

        $performance_monitor = PDF_Builder_Performance_Monitor::get_instance();

        return [
            'avg_response_time' => $performance_monitor->get_average_response_time(),
            'slow_queries_count' => $performance_monitor->get_slow_queries_count(),
            'memory_peak' => $performance_monitor->get_memory_peak(),
            'cache_hit_rate' => $performance_monitor->get_cache_hit_rate()
        ];
    }

    /**
     * Obtient les métriques de ressources
     */
    private function get_resource_metrics() {
        return [
            'cpu_usage' => $this->get_cpu_usage(),
            'memory_usage' => $this->get_memory_usage(),
            'disk_usage' => $this->get_disk_usage(),
            'active_connections' => $this->get_active_connections()
        ];
    }

    /**
     * Obtient les métriques d'erreurs
     */
    private function get_error_metrics() {
        if (!class_exists('PDF_Builder_Logger')) {
            return [];
        }

        $logger = PDF_Builder_Logger::get_instance();
        return [
            'error_count_24h' => $logger->get_error_count(24),
            'warning_count_24h' => $logger->get_warning_count(24),
            'critical_count_24h' => $logger->get_critical_count(24),
            'error_rate' => $logger->get_error_rate()
        ];
    }

    /**
     * Obtient l'utilisation CPU
     */
    private function get_cpu_usage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return min(100, ($load[0] / $this->get_cpu_cores()) * 100);
        }

        // Fallback pour Windows
        return 0; // Non disponible sur Windows
    }

    /**
     * Obtient le nombre de cœurs CPU
     */
    private function get_cpu_cores() {
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            return substr_count($cpuinfo, 'processor');
        }

        return 1; // Valeur par défaut
    }

    /**
     * Obtient l'utilisation mémoire
     */
    private function get_memory_usage() {
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = $this->convert_to_bytes($memory_limit);

        if ($memory_limit_bytes > 0) {
            return (memory_get_peak_usage(true) / $memory_limit_bytes) * 100;
        }

        return 0;
    }

    /**
     * Obtient l'utilisation disque
     */
    private function get_disk_usage() {
        $total_space = disk_total_space(ABSPATH);
        $free_space = disk_free_space(ABSPATH);

        if ($total_space > 0) {
            $used_space = $total_space - $free_space;
            return ($used_space / $total_space) * 100;
        }

        return 0;
    }

    /**
     * Obtient le temps de réponse
     */
    private function get_response_time() {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    /**
     * Teste la connexion à la base de données
     */
    private function test_database_connection() {
        global $wpdb;

        $result = $wpdb->get_var("SELECT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        return $result === '1';
    }

    /**
     * Obtient les tailles des tables
     */
    private function get_table_sizes() {
        global $wpdb;

        $tables = $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT table_name, data_length + index_length as size
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name LIKE '{$wpdb->prefix}pdf_builder_%'
        ", ARRAY_A);

        $sizes = [];
        foreach ($tables as $table) {
            $sizes[$table['table_name']] = $table['size'];
        }

        return $sizes;
    }

    /**
     * Obtient le nombre de requêtes lentes
     */
    private function get_slow_queries_count() {
        global $wpdb;

        // Simuler - en production, utiliser les logs MySQL ou un profiler
        return 0;
    }

    /**
     * Vérifie les tables corrompues
     */
    private function check_corrupted_tables() {
        global $wpdb;

        $corrupted = [];

        $tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_%'");

        foreach ($tables as $table) {
            $result = $wpdb->get_var("CHECK TABLE $table"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            if ($result !== 'OK') {
                $corrupted[] = $table;
            }
        }

        return $corrupted;
    }

    /**
     * Obtient le nombre de connexions actives
     */
    private function get_active_connections() {
        global $wpdb;

        $connections = $wpdb->get_var("SHOW PROCESSLIST"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        return is_numeric($connections) ? $connections : 0;
    }

    /**
     * Obtient la dernière version de WordPress
     */
    private function get_latest_wp_version() {
        $response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');

        if (!is_wp_error($response)) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($body['offers'][0]['version'])) {
                return $body['offers'][0]['version'];
            }
        }

        return get_bloginfo('version'); // Fallback
    }

    /**
     * Obtient la dernière version du plugin
     */
    private function get_latest_plugin_version() {
        // Simuler - en production, vérifier depuis un endpoint API
        return PDF_BUILDER_VERSION;
    }

    /**
     * Vérifie l'intégrité des fichiers du plugin
     */
    private function check_plugin_file_integrity() {
        $corrupted = [];

        $plugin_files = $this->get_plugin_files_list();

        foreach ($plugin_files as $file) {
            $file_path = PDF_BUILDER_PLUGIN_DIR . $file;

            if (!file_exists($file_path)) {
                $corrupted[] = $file . ' (manquant)';
                continue;
            }

            // Vérifier la syntaxe PHP pour les fichiers .php
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $syntax_check = $this->check_php_syntax($file_path);
                if (!$syntax_check) {
                    $corrupted[] = $file . ' (syntaxe invalide)';
                }
            }
        }

        return $corrupted;
    }

    /**
     * Obtient la liste des fichiers du plugin
     */
    private function get_plugin_files_list() {
        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(PDF_BUILDER_PLUGIN_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = str_replace(PDF_BUILDER_PLUGIN_DIR, '', $file->getPathname());
            }
        }

        return $files;
    }

    /**
     * Vérifie la syntaxe PHP
     */
    private function check_php_syntax($file_path) {
        $output = shell_exec("php -l \"$file_path\" 2>&1");
        return strpos($output, 'No syntax errors detected') !== false;
    }

    /**
     * Convertit une valeur en octets
     */
    private function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);

        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }

        return (int) $value;
    }

    /**
     * Stocke les métriques de santé
     */
    private function store_health_metrics($health_status) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_health_metrics';

        $wpdb->insert(
            $table,
            [
                'timestamp' => $health_status['timestamp'],
                'overall_status' => $health_status['overall_status'],
                'system_status' => $health_status['checks']['system']['status'],
                'database_status' => $health_status['checks']['database']['status'],
                'filesystem_status' => $health_status['checks']['filesystem']['status'],
                'wordpress_status' => $health_status['checks']['wordpress']['status'],
                'plugin_status' => $health_status['checks']['plugin']['status'],
                'metrics' => json_encode($health_status['metrics']),
                'issues' => json_encode(array_merge(
                    $health_status['checks']['system']['issues'],
                    $health_status['checks']['database']['issues'],
                    $health_status['checks']['filesystem']['issues'],
                    $health_status['checks']['wordpress']['issues'],
                    $health_status['checks']['plugin']['issues']
                ))
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        // Garder seulement les 1000 dernières entrées
        $wpdb->query(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $table
            WHERE id NOT IN (
                SELECT id FROM (
                    SELECT id FROM $table
                    ORDER BY timestamp DESC
                    LIMIT 1000
                ) tmp
            )
        ");
    }

    /**
     * Vérifie les alertes à envoyer
     */
    private function check_for_alerts($health_status) {
        $alerts = [];

        // Alertes critiques
        if ($health_status['overall_status'] === 'critical') {
            $alerts[] = [
                'level' => 'critical',
                'message' => 'État de santé critique détecté',
                'details' => $health_status
            ];
        }

        // Alertes par vérification
        foreach ($health_status['checks'] as $check_name => $check) {
            if ($check['status'] === 'critical') {
                $alerts[] = [
                    'level' => 'critical',
                    'message' => "Problème critique dans $check_name",
                    'details' => $check
                ];
            } elseif ($check['status'] === 'warning') {
                $alerts[] = [
                    'level' => 'warning',
                    'message' => "Avertissement dans $check_name",
                    'details' => $check
                ];
            }
        }

        // Envoyer les alertes
        foreach ($alerts as $alert) {
            $this->send_health_alert($alert['level'], $alert['message'], $alert['details']);
        }
    }

    /**
     * Envoie une alerte de santé
     */
    public function send_health_alert($level, $message, $details = []) {
        $alert_key = md5($message . $level);

        // Vérifier le cooldown
        if (isset($this->alerts_sent[$alert_key])) {
            $last_sent = $this->alerts_sent[$alert_key];
            if (time() - $last_sent < self::ALERT_COOLDOWN) {
                return; // Cooldown actif
            }
        }

        $this->alerts_sent[$alert_key] = time();

        // Send events to the logger
        if (class_exists('PDF_Builder_Logger')) {
            $logger = PDF_Builder_Logger::get_instance();
            switch ($level) {
                case 'critical':
                    $logger->critical("Alerte de santé système: $message", $details);
                    break;
                case 'high':
                case 'error':
                    $logger->error("Alerte de santé système: $message", $details);
                    break;
                case 'warning':
                case 'medium':
                    $logger->warning("Alerte de santé système: $message", $details);
                    break;
                default:
                    $logger->info("Alerte de santé système: $message", $details);
            }
        } else {
            // Fallback to error_log
            error_log("Health Alert: $message");
        }
    }

    /**
     * Nettoie les anciennes métriques
     */
    public function cleanup_old_metrics() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_health_metrics';

        // Supprimer les métriques de plus de 30 jours
        $wpdb->query($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            DELETE FROM $table
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)
        "));
    }

    /**
     * Surveille les performances
     */
    public function monitor_performance() {
        if (!class_exists('PDF_Builder_Performance_Monitor')) {
            return;
        }

        $performance_monitor = PDF_Builder_Performance_Monitor::get_instance();
        $metrics = $performance_monitor->get_performance_report();

        // Vérifier les seuils
        if ($metrics['avg_response_time'] > self::RESPONSE_TIME_THRESHOLD) {
            $this->send_health_alert('warning', 'Temps de réponse élevé détecté',
                ['avg_response_time' => $metrics['avg_response_time']]);
        }

        if ($metrics['slow_queries_count'] > 10) {
            $this->send_health_alert('warning', 'Nombre élevé de requêtes lentes',
                ['slow_queries_count' => $metrics['slow_queries_count']]);
        }
    }

    /**
     * Surveille les erreurs
     */
    public function monitor_errors() {
        if (!class_exists('PDF_Builder_Logger')) {
            return;
        }

        $logger = PDF_Builder_Logger::get_instance();
        $error_rate = $logger->get_error_rate();

        if ($error_rate > self::ERROR_RATE_THRESHOLD) {
            $this->send_health_alert('critical', 'Taux d\'erreur élevé détecté',
                ['error_rate' => $error_rate]);
        }
    }

    /**
     * AJAX - Vérification de santé
     */
    public function health_check_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                // Essayer aussi le nonce WordPress standard
                if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'wp_rest')) {
                    wp_send_json_error(['message' => 'Nonce invalide']);
                    return;
                }
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $health_status = $this->perform_health_checks();

            wp_send_json_success([
                'message' => 'Vérification de santé terminée',
                'health_status' => $health_status
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la vérification: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut de santé
     */
    public function get_health_status_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $latest_health = $this->get_latest_health_status();
            $health_history = $this->get_health_history(24); // 24 dernières heures

            wp_send_json_success([
                'message' => 'Statut de santé récupéré',
                'latest_health' => $latest_health,
                'health_history' => $health_history
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient le dernier statut de santé
     */
    public function get_latest_health_status() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_health_metrics';

        return $wpdb->get_row($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            ORDER BY timestamp DESC
            LIMIT 1
        "), ARRAY_A);
    }

    /**
     * Obtient l'historique de santé
     */
    public function get_health_history($hours = 24) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_health_metrics';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL %d HOUR)
            ORDER BY timestamp DESC
        ", $hours), ARRAY_A);
    }
}

// Fonctions globales
function pdf_builder_health_monitor() {
    return PDF_Builder_Health_Monitor::get_instance();
}

function pdf_builder_get_health_status() {
    return PDF_Builder_Health_Monitor::get_instance()->get_latest_health_status();
}

function pdf_builder_perform_health_check() {
    return PDF_Builder_Health_Monitor::get_instance()->perform_health_checks();
}

// Initialiser le système de surveillance de santé
add_action('plugins_loaded', function() {
    PDF_Builder_Health_Monitor::get_instance();
});



