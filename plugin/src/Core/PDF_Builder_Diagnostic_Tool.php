<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Outil de diagnostic
 * Fournit des outils de diagnostic et débogage complets
 */

class PDF_Builder_Diagnostic_Tool {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // AJAX pour les diagnostics
        add_action('wp_ajax_pdf_builder_run_diagnostic', [$this, 'run_diagnostic_ajax']);
        add_action('wp_ajax_pdf_builder_export_diagnostic', [$this, 'export_diagnostic_ajax']);
        add_action('wp_ajax_pdf_builder_clear_diagnostic_cache', [$this, 'clear_diagnostic_cache_ajax']);
    }

    /**
     * Exécute un diagnostic complet
     */
    public function run_full_diagnostic() {
        $results = [
            'timestamp' => current_time('mysql'),
            'system_info' => $this->get_system_info(),
            'plugin_status' => $this->check_plugin_status(),
            'database_status' => $this->check_database_status(),
            'file_permissions' => $this->check_file_permissions(),
            'performance_metrics' => $this->get_performance_metrics(),
            'security_check' => $this->run_security_check(),
            'configuration_check' => $this->check_configuration(),
            'error_analysis' => $this->analyze_recent_errors(),
            'recommendations' => []
        ];

        // Générer des recommandations
        $results['recommendations'] = $this->generate_recommendations($results);

        return $results;
    }

    /**
     * Obtient les informations système
     */
    private function get_system_info() {
        global $wpdb;

        return [
            'wordpress' => [
                'version' => get_bloginfo('version'),
                'multisite' => is_multisite(),
                'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
                'memory_limit' => WP_MEMORY_LIMIT,
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size')
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'extensions' => $this->get_php_extensions_status(),
                'error_reporting' => error_reporting()
            ],
            'mysql' => [
                'version' => $wpdb->db_version(),
                'charset' => $wpdb->charset,
                'collate' => $wpdb->collate
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'php_sapi' => php_sapi_name(),
                'os' => PHP_OS,
                'architecture' => PHP_INT_SIZE * 8 . '-bit'
            ],
            'plugin' => [
                'version' => PDF_BUILDER_VERSION,
                'path' => PDF_BUILDER_PLUGIN_FILE,
                'url' => plugin_dir_url(PDF_BUILDER_PLUGIN_FILE)
            ]
        ];
    }

    /**
     * Vérifie le statut des extensions PHP
     */
    private function get_php_extensions_status() {
        $required_extensions = [
            'gd', 'mbstring', 'xml', 'zip', 'json', 'curl'
        ];

        $optional_extensions = [
            'imagick', 'openssl', 'soap', 'intl'
        ];

        $status = [];

        foreach ($required_extensions as $ext) {
            $status[$ext] = [
                'loaded' => extension_loaded($ext),
                'required' => true,
                'version' => phpversion($ext)
            ];
        }

        foreach ($optional_extensions as $ext) {
            $status[$ext] = [
                'loaded' => extension_loaded($ext),
                'required' => false,
                'version' => phpversion($ext)
            ];
        }

        return $status;
    }

    /**
     * Vérifie le statut du plugin
     */
    private function check_plugin_status() {
        $status = [
            'active' => is_plugin_active(plugin_basename(PDF_BUILDER_PLUGIN_FILE)),
            'network_active' => is_plugin_active_for_network(plugin_basename(PDF_BUILDER_PLUGIN_FILE)),
            'update_available' => false,
            'compatibility' => $this->check_wordpress_compatibility(),
            'dependencies' => $this->check_dependencies()
        ];

        // Vérifier les mises à jour
        $update_plugins = get_site_transient('update_plugins');
        $plugin_file = plugin_basename(PDF_BUILDER_PLUGIN_FILE);
        
        if ($plugin_file && $update_plugins && isset($update_plugins->response[$plugin_file])) {
            $status['update_available'] = true;
            $status['update_info'] = $update_plugins->response[$plugin_file];
        }

        return $status;
    }

    /**
     * Vérifie la compatibilité WordPress
     */
    private function check_wordpress_compatibility() {
        global $wp_version;

        $min_version = '5.0';
        $max_version = '6.5'; // Version testée

        return [
            'current_version' => $wp_version,
            'min_required' => $min_version,
            'max_tested' => $max_version,
            'compatible' => version_compare($wp_version, $min_version, '>=') &&
                           version_compare($wp_version, $max_version, '<=')
        ];
    }

    /**
     * Vérifie les dépendances
     */
    private function check_dependencies() {
        $dependencies = [
            'wordpress' => [
                'name' => 'WordPress',
                'required' => true,
                'version' => get_bloginfo('version'),
                'min_version' => '5.0'
            ],
            'php' => [
                'name' => 'PHP',
                'required' => true,
                'version' => PHP_VERSION,
                'min_version' => '7.4'
            ],
            'mysql' => [
                'name' => 'MySQL',
                'required' => true,
                'version' => $GLOBALS['wpdb']->db_version(),
                'min_version' => '5.6'
            ]
        ];

        foreach ($dependencies as &$dep) {
            $dep['satisfied'] = version_compare($dep['version'], $dep['min_version'], '>=');
        }

        return $dependencies;
    }

    /**
     * Vérifie le statut de la base de données
     */
    private function check_database_status() {
        global $wpdb;

        $tables = [
            'pdf_builder_templates',
            'pdf_builder_cache',
            'pdf_builder_errors',
            'pdf_builder_performance_metrics',
            'pdf_builder_performance_issues',
            'pdf_builder_backups'
        ];

        $status = [
            'connection' => $this->test_database_connection(),
            'tables' => [],
            'size' => 0,
            'optimization_needed' => false
        ];

        foreach ($tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name; // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

            if ($exists) {
                $table_info = $wpdb->get_row("SHOW TABLE STATUS LIKE '$full_table_name'", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                $status['tables'][$table] = [
                    'exists' => true,
                    'rows' => $table_info['Rows'] ?? 0,
                    'size' => $this->format_bytes(($table_info['Data_length'] ?? 0) + ($table_info['Index_length'] ?? 0)),
                    'engine' => $table_info['Engine'] ?? 'unknown',
                    'collation' => $table_info['Collation'] ?? 'unknown'
                ];
                $status['size'] += ($table_info['Data_length'] ?? 0) + ($table_info['Index_length'] ?? 0);
            } else {
                $status['tables'][$table] = ['exists' => false];
            }
        }

        $status['size_formatted'] = $this->format_bytes($status['size']);

        // Vérifier si l'optimisation est nécessaire
        $status['optimization_needed'] = $this->check_optimization_needed();

        return $status;
    }

    /**
     * Teste la connexion à la base de données
     */
    private function test_database_connection() {
        global $wpdb;

        $start_time = microtime(true);

        try {
            $wpdb->get_var("SELECT 1"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $end_time = microtime(true);

            return [
                'connected' => true,
                'response_time' => round(($end_time - $start_time) * 1000, 2) . 'ms'
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie si l'optimisation de la DB est nécessaire
     */
    private function check_optimization_needed() {
        global $wpdb;

        // Vérifier la fragmentation des tables
        $fragmented_tables = $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SHOW TABLE STATUS
            WHERE Name LIKE '{$wpdb->prefix}pdf_builder_%'
            AND Data_free > 0
        ");

        return !empty($fragmented_tables);
    }

    /**
     * Vérifie les permissions des fichiers
     */
    private function check_file_permissions() {
        $paths = [
            PDF_BUILDER_PLUGIN_DIR => 'Dossier plugin',
            PDF_BUILDER_PLUGIN_DIR . 'resources/assets/' => 'Dossier assets',
            PDF_BUILDER_PLUGIN_DIR . 'src/' => 'Dossier src',
            PDF_BUILDER_PLUGIN_DIR . 'resources/templates/' => 'Dossier resources/templates',
            WP_CONTENT_DIR . '/uploads/pdf-builder/' => 'Dossier uploads PDF',
            WP_CONTENT_DIR . '/cache/pdf-builder/' => 'Dossier cache'
        ];

        $permissions = [];

        foreach ($paths as $path => $description) {
            $permissions[$description] = [
                'path' => $path,
                'exists' => file_exists($path),
                'writable' => is_writable($path), // phpcs:ignore WordPress.WP.AlternativeFunctions
                'readable' => is_readable($path),
                'permissions' => $this->get_file_permissions($path)
            ];
        }

        return $permissions;
    }

    /**
     * Obtient les permissions d'un fichier/dossier
     */
    private function get_file_permissions($path) {
        if (!file_exists($path)) {
            return 'N/A';
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Obtient les métriques de performance
     */
    private function get_performance_metrics() {
        if (!class_exists('PDF_Builder_Performance_Monitor')) {
            return ['error' => 'Performance monitor not available'];
        }

        $monitor = PDF_Builder_Performance_Monitor::get_instance();

        return [
            'current' => $monitor->get_current_metrics(),
            'summary' => $monitor->get_performance_summary(7),
            'slow_pages' => $monitor->get_slow_pages(7),
            'memory_usage' => $monitor->get_memory_usage_stats(7)
        ];
    }

    /**
     * Exécute une vérification de sécurité
     */
    private function run_security_check() {
        if (!class_exists('PDF_Builder_Security_Validator')) {
            return ['error' => 'Security validator not available'];
        }

        $validator = PDF_Builder_Security_Validator::get_instance();

        return [
            'health_check' => pdf_builder_health_check(),
            'csrf_tokens' => $this->check_csrf_tokens(),
            'file_security' => $this->check_file_security(),
            'configuration_security' => $this->check_configuration_security()
        ];
    }

    /**
     * Vérifie les tokens CSRF
     */
    private function check_csrf_tokens() {
        global $wpdb;

        $expired_tokens = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %s",
            '_transient_pdf_builder_csrf_%',
            time() - HOUR_IN_SECONDS
        ));

        return [
            'expired_tokens' => $expired_tokens,
            'status' => $expired_tokens > 10 ? 'warning' : 'ok'
        ];
    }

    /**
     * Vérifie la sécurité des fichiers
     */
    private function check_file_security() {
        $issues = [];

        // Vérifier les fichiers avec des permissions trop permissives
        $plugin_files = $this->scan_directory(PDF_BUILDER_PLUGIN_DIR);
        foreach ($plugin_files as $file) {
            $perms = fileperms($file);
            if (($perms & 0x0002) || ($perms & 0x0010)) { // writable by group or others
                $issues[] = [
                    'file' => str_replace(PDF_BUILDER_PLUGIN_DIR, '', $file),
                    'issue' => 'Permissions trop permissives',
                    'permissions' => substr(sprintf('%o', $perms), -4)
                ];
            }
        }

        return [
            'issues' => $issues,
            'status' => empty($issues) ? 'ok' : 'warning'
        ];
    }

    /**
     * Vérifie la sécurité de la configuration
     */
    private function check_configuration_security() {
        $issues = [];

        // Vérifier les paramètres sensibles
        if (pdf_builder_config('debug_mode') && (!defined('WP_DEBUG') || !WP_DEBUG)) {
            $issues[] = 'Mode debug activé en production';
        }

        if (!pdf_builder_config('enable_nonce_check')) {
            $issues[] = 'Vérification des nonce désactivée';
        }

        if (pdf_builder_config('log_level') === 'DEBUG') {
            $issues[] = 'Niveau de log DEBUG peut exposer des informations sensibles';
        }

        return [
            'issues' => $issues,
            'status' => empty($issues) ? 'ok' : 'warning'
        ];
    }

    /**
     * Vérifie la configuration
     */
    private function check_configuration() {
        $config = pdf_builder_config();

        $issues = [];

        // Vérifier les valeurs critiques
        if (empty($config['company_name'])) {
            $issues[] = 'Nom de l\'entreprise non configuré';
        }

        // Notification email removed from config

        if (!$config['cache_enabled']) {
            $issues[] = 'Cache désactivé - impact sur les performances';
        }

        if ($config['memory_limit'] > 512) {
            $issues[] = 'Limite mémoire élevée détectée';
        }

        return [
            'config' => $config,
            'issues' => $issues,
            'status' => empty($issues) ? 'ok' : 'warning'
        ];
    }

    /**
     * Analyse les erreurs récentes
     */
    private function analyze_recent_errors() {
        if (!class_exists('PDF_Builder_Error_Handler')) {
            return ['error' => 'Error handler not available'];
        }

        $handler = PDF_Builder_Error_Handler::get_instance();

        return [
            'recent_errors' => $handler->get_recent_errors(20),
            'error_stats' => $handler->get_error_stats(7),
            'error_trends' => $this->analyze_error_trends()
        ];
    }

    /**
     * Analyse les tendances d'erreurs
     */
    private function analyze_error_trends() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $trends = $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                DATE(created_at) as date,
                type,
                COUNT(*) as count
            FROM $table
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at), type
            ORDER BY date DESC
        ", []), ARRAY_A);

        return $trends;
    }

    /**
     * Génère des recommandations
     */
    private function generate_recommendations($diagnostic_results) {
        $recommendations = [];

        // Recommandations basées sur les informations système
        $system = $diagnostic_results['system_info'];
        if (version_compare($system['php']['version'], '8.0', '<')) {
            $recommendations[] = [
                'type' => 'warning',
                'category' => 'php',
                'title' => 'Mise à jour PHP recommandée',
                'description' => 'Votre version PHP (' . $system['php']['version'] . ') est ancienne. PHP 8.0+ offre de meilleures performances.',
                'action' => 'Contactez votre hébergeur pour mettre à jour PHP.'
            ];
        }

        // Recommandations de sécurité
        $security = $diagnostic_results['security_check'];
        if ($security['configuration_security']['status'] === 'warning') {
            $recommendations[] = [
                'type' => 'critical',
                'category' => 'security',
                'title' => 'Problèmes de sécurité détectés',
                'description' => 'Des problèmes de sécurité ont été identifiés dans la configuration.',
                'action' => 'Vérifiez les paramètres de sécurité dans la configuration du plugin.'
            ];
        }

        // Recommandations de performance
        $performance = $diagnostic_results['performance_metrics'];
        if (isset($performance['summary']['avg_response_time']) &&
            $performance['summary']['avg_response_time'] > 2.0) {
            $recommendations[] = [
                'type' => 'warning',
                'category' => 'performance',
                'title' => 'Performance dégradée',
                'description' => 'Le temps de réponse moyen est élevé (' . round($performance['summary']['avg_response_time'], 2) . 's).',
                'action' => 'Activez le cache et optimisez la configuration de performance.'
            ];
        }

        // Recommandations de base de données
        $db = $diagnostic_results['database_status'];
        if ($db['optimization_needed']) {
            $recommendations[] = [
                'type' => 'info',
                'category' => 'database',
                'title' => 'Optimisation base de données',
                'description' => 'La base de données nécessite une optimisation.',
                'action' => 'Exécutez l\'optimisation automatique depuis les outils du plugin.'
            ];
        }

        return $recommendations;
    }

    /**
     * Scanne récursivement un répertoire
     */
    private function scan_directory($dir, $max_depth = 3, $current_depth = 0) {
        $files = [];

        if ($current_depth > $max_depth || !is_dir($dir)) {
            return $files;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            $files[] = $path;

            if (is_dir($path)) {
                $files = array_merge($files, $this->scan_directory($path, $max_depth, $current_depth + 1));
            }
        }

        return $files;
    }

    /**
     * Formate des octets en unité lisible
     */
    private function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * AJAX - Exécute un diagnostic
     */
    public function run_diagnostic_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $diagnostic = $this->run_full_diagnostic();

            wp_send_json_success([
                'message' => 'Diagnostic terminé',
                'diagnostic' => $diagnostic
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du diagnostic: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exporte le diagnostic
     */
    public function export_diagnostic_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $diagnostic = $this->run_full_diagnostic();
            $export_data = json_encode($diagnostic, JSON_PRETTY_PRINT);

            wp_send_json_success([
                'message' => 'Diagnostic exporté',
                'data' => $export_data,
                'filename' => 'pdf-builder-diagnostic-' . gmdate('Y-m-d-H-i-s') . '.json'
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'export: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Vide le cache de diagnostic
     */
    public function clear_diagnostic_cache_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            // Vider les transients de diagnostic
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_diagnostic_%'"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

            wp_send_json_success(['message' => 'Cache de diagnostic vidé']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_run_diagnostic() {
    return PDF_Builder_Diagnostic_Tool::get_instance()->run_full_diagnostic();
}

// Initialiser l'outil de diagnostic
add_action('plugins_loaded', function() {
    PDF_Builder_Diagnostic_Tool::get_instance();
});



