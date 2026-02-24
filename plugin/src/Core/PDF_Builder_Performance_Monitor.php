<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Moniteur de performance
 * Surveille les performances du plugin et identifie les optimisations possibles
 */

class PDF_Builder_Performance_Monitor {
    private static $instance = null;
    private $timers = [];
    private $memory_usage = [];
    private $query_count = 0;
    private $query_time = 0;
    private $slow_queries = [];

    // Seuils de performance
    const SLOW_QUERY_THRESHOLD = 0.5; // 500ms
    const HIGH_MEMORY_THRESHOLD = 50 * 1024 * 1024; // 50MB
    const SLOW_PAGE_THRESHOLD = 2.0; // 2 secondes

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->start_global_timer();
    }

    private function init_hooks() {
        // Démarrer le monitoring au début des requêtes AJAX
        add_action('wp_ajax_pdf_builder_*', [$this, 'start_ajax_monitoring'], 1);
        add_action('wp_ajax_nopriv_pdf_builder_*', [$this, 'start_ajax_monitoring'], 1);

        // Surveiller les requêtes de base de données
        add_filter('query', [$this, 'monitor_database_query']);
        add_filter('get_col', [$this, 'monitor_database_query']);
        add_filter('get_row', [$this, 'monitor_database_query']);
        add_filter('get_results', [$this, 'monitor_database_query']);

        // Monitoring des pages admin
        add_action('admin_init', [$this, 'start_admin_monitoring']);
        add_action('admin_footer', [$this, 'end_admin_monitoring']);

        // Nettoyage périodique des métriques
        add_action('pdf_builder_daily_cleanup', [$this, 'cleanup_performance_logs']);

        // Hook pour les rapports de performance
        add_action('wp_ajax_pdf_builder_performance_report', [$this, 'generate_performance_report']);
    }

    /**
     * Démarre le timer global pour la requête
     */
    private function start_global_timer() {
        $this->timers['global'] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }

    /**
     * Démarre le monitoring AJAX
     */
    public function start_ajax_monitoring() {
        $this->timers['ajax'] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'action' => current_action()
        ];

        // Reset des compteurs pour cette requête
        $this->query_count = 0;
        $this->query_time = 0;
        $this->slow_queries = [];
    }

    /**
     * Démarre le monitoring des pages admin
     */
    public function start_admin_monitoring() {
        if (!$this->is_pdf_builder_admin_page()) {
            return;
        }

        $this->timers['admin'] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'page' => $_GET['page'] ?? 'unknown'
        ];
    }

    /**
     * Termine le monitoring des pages admin
     */
    public function end_admin_monitoring() {
        if (!isset($this->timers['admin'])) {
            return;
        }

        $timer = $this->timers['admin'];
        $duration = microtime(true) - $timer['start'];
        $memory_used = memory_get_usage(true) - $timer['memory_start'];

        // Logger si c'est lent
        if ($duration > self::SLOW_PAGE_THRESHOLD) {
            $this->log_performance_issue('slow_admin_page', [
                'page' => $timer['page'],
                'duration' => $duration,
                'memory_used' => $memory_used,
                'query_count' => $this->query_count,
                'query_time' => $this->query_time
            ]);
        }

        // Stocker les métriques
        $this->store_performance_metric('admin_page', $timer['page'], $duration, $memory_used);
    }

    /**
     * Surveille les requêtes de base de données
     */
    public function monitor_database_query($query) {
        if (!isset($this->timers['query_start'])) {
            $this->timers['query_start'] = microtime(true);
        }

        // Incrémenter le compteur
        $this->query_count++;

        // Calculer le temps de la requête
        $query_time = microtime(true) - $this->timers['query_start'];
        $this->query_time += $query_time;

        // Détecter les requêtes lentes
        if ($query_time > self::SLOW_QUERY_THRESHOLD) {
            $this->slow_queries[] = [
                'query' => substr($query, 0, 200) . (strlen($query) > 200 ? '...' : ''),
                'time' => $query_time,
                'timestamp' => current_time('timestamp')
            ];
        }

        unset($this->timers['query_start']);
        return $query;
    }

    /**
     * Démarre un timer personnalisé
     */
    public function start_timer($name) {
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];
    }

    /**
     * Arrête un timer personnalisé et retourne les métriques
     */
    public function end_timer($name) {
        if (!isset($this->timers[$name])) {
            return null;
        }

        $timer = $this->timers[$name];
        $duration = microtime(true) - $timer['start'];
        $memory_used = memory_get_usage(true) - $timer['memory_start'];

        unset($this->timers[$name]);

        return [
            'duration' => $duration,
            'memory_used' => $memory_used
        ];
    }

    /**
     * Mesure les performances d'une fonction
     */
    public function measure_function($function_name, $callable, ...$args) {
        $this->start_timer($function_name);

        try {
            $result = call_user_func_array($callable, $args);
            $metrics = $this->end_timer($function_name);

            // Logger si lent
            if ($metrics && $metrics['duration'] > 1.0) { // Plus d'1 seconde
                $this->log_performance_issue('slow_function', [
                    'function' => $function_name,
                    'duration' => $metrics['duration'],
                    'memory_used' => $metrics['memory_used']
                ]);
            }

            return $result;

        } catch (Exception $e) {
            $this->end_timer($function_name);
            throw $e;
        }
    }

    /**
     * Vérifie si on est sur une page admin du PDF Builder
     */
    private function is_pdf_builder_admin_page() {
        if (!is_admin()) {
            return false;
        }

        $page = $_GET['page'] ?? '';
        return strpos($page, 'pdf_builder') === 0;
    }

    /**
     * Log un problème de performance
     */
    private function log_performance_issue($type, $data) {
        // Stocker pour analyse
        $this->store_performance_issue($type, $data);
    }

    /**
     * Stocke un problème de performance en base
     */
    private function store_performance_issue($type, $data) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_issues';

        // Créer la table si elle n'existe pas
        $this->create_performance_tables();

        $wpdb->insert(
            $table,
            [
                'type' => $type,
                'data' => json_encode($data),
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'user_id' => get_current_user_id(),
                'ip' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s']
        );
    }

    /**
     * Stocke une métrique de performance
     */
    private function store_performance_metric($type, $identifier, $duration, $memory_used) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        // Créer la table si elle n'existe pas
        $this->create_performance_tables();

        $wpdb->insert(
            $table,
            [
                'type' => $type,
                'identifier' => $identifier,
                'duration' => $duration,
                'memory_used' => $memory_used,
                'query_count' => $this->query_count,
                'query_time' => $this->query_time,
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%f', '%d', '%d', '%f', '%s']
        );
    }

    /**
     * Crée les tables de performance si elles n'existent pas
     */
    private function create_performance_tables() {
        global $wpdb;

        $issues_table = $wpdb->prefix . 'pdf_builder_performance_issues';
        $metrics_table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        $charset_collate = $wpdb->get_charset_collate();

        // Table des problèmes de performance
        if ($wpdb->get_var("SHOW TABLES LIKE '$issues_table'") != $issues_table) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $sql = "CREATE TABLE $issues_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                type varchar(50) NOT NULL,
                data longtext NOT NULL,
                url varchar(500),
                user_id bigint(20) unsigned,
                ip varchar(45),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY type (type),
                KEY created_at (created_at)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        // Table des métriques de performance
        if ($wpdb->get_var("SHOW TABLES LIKE '$metrics_table'") != $metrics_table) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $sql = "CREATE TABLE $metrics_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                type varchar(50) NOT NULL,
                identifier varchar(100) NOT NULL,
                duration float NOT NULL,
                memory_used bigint(20) NOT NULL,
                query_count int(11) DEFAULT 0,
                query_time float DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY type (type),
                KEY identifier (identifier),
                KEY created_at (created_at)
            ) $charset_collate;";

            dbDelta($sql);
        }
    }

    /**
     * Génère un rapport de performance
     */
    public function generate_performance_report() {
        try {
            // Valider la requête
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $period = intval($_POST['period'] ?? 7); // Période en jours

            $report = [
                'summary' => $this->get_performance_summary($period),
                'slow_pages' => $this->get_slow_pages($period),
                'memory_usage' => $this->get_memory_usage_stats($period),
                'database_performance' => $this->get_database_performance($period),
                'recent_issues' => $this->get_recent_performance_issues(20)
            ];

            wp_send_json_success($report);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient un résumé des performances
     */
    private function get_performance_summary($days) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        return $wpdb->get_row($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                COUNT(*) as total_requests,
                AVG(duration) as avg_response_time,
                MAX(duration) as max_response_time,
                AVG(memory_used) as avg_memory_usage,
                MAX(memory_used) as max_memory_usage,
                SUM(query_count) as total_queries,
                AVG(query_time) as avg_query_time
            FROM $table
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $days), ARRAY_A);
    }

    /**
     * Obtient les pages les plus lentes
     */
    private function get_slow_pages($days) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                identifier,
                AVG(duration) as avg_duration,
                MAX(duration) as max_duration,
                COUNT(*) as request_count
            FROM $table
            WHERE type = 'admin_page'
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY identifier
            HAVING avg_duration > %f
            ORDER BY avg_duration DESC
            LIMIT 10
        ", $days, self::SLOW_PAGE_THRESHOLD), ARRAY_A);
    }

    /**
     * Obtient les statistiques d'utilisation mémoire
     */
    private function get_memory_usage_stats($days) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                DATE(created_at) as date,
                AVG(memory_used) as avg_memory,
                MAX(memory_used) as max_memory
            FROM $table
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", $days), ARRAY_A);
    }

    /**
     * Obtient les performances de la base de données
     */
    private function get_database_performance($days) {
        global $wpdb;

        $issues_table = $wpdb->prefix . 'pdf_builder_performance_issues';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                COUNT(*) as slow_query_count,
                AVG(JSON_EXTRACT(data, '$.time')) as avg_query_time
            FROM $issues_table
            WHERE type = 'slow_query'
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $days), ARRAY_A);
    }

    /**
     * Obtient les problèmes de performance récents
     */
    private function get_recent_performance_issues($limit) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_issues';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);
    }

    /**
     * Obtient l'IP du client
     */
    private function get_client_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Nettoie les anciens logs de performance
     */
    public function cleanup_performance_logs() {
        global $wpdb;

        $issues_table = $wpdb->prefix . 'pdf_builder_performance_issues';
        $metrics_table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        // Supprimer les métriques de plus de 30 jours
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "DELETE FROM $metrics_table WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));

        // Supprimer les problèmes de plus de 60 jours
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "DELETE FROM $issues_table WHERE created_at < DATE_SUB(NOW(), INTERVAL 60 DAY)"
        ));
    }

    /**
     * Obtient les métriques actuelles
     */
    public function get_current_metrics() {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'query_count' => $this->query_count,
            'query_time' => $this->query_time,
            'slow_queries' => count($this->slow_queries),
            'execution_time' => isset($this->timers['global']) ?
                (microtime(true) - $this->timers['global']['start']) : 0
        ];
    }
}

// Fonctions globales pour faciliter l'utilisation
function pdf_builder_start_timer($name) {
    PDF_Builder_Performance_Monitor::get_instance()->start_timer($name);
}

function pdf_builder_end_timer($name) {
    return PDF_Builder_Performance_Monitor::get_instance()->end_timer($name);
}

function pdf_builder_measure_function($function_name, $callable, ...$args) {
    return PDF_Builder_Performance_Monitor::get_instance()->measure_function($function_name, $callable, ...$args);
}

function pdf_builder_get_performance_metrics() {
    return PDF_Builder_Performance_Monitor::get_instance()->get_current_metrics();
}

// Initialiser le moniteur de performance
add_action('plugins_loaded', function() {
    PDF_Builder_Performance_Monitor::get_instance();
});



