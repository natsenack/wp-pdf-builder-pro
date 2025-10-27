<?php
/**
 * Moniteur de Performance - PDF Builder Pro
 *
 * Track les métriques de performance en production
 */

class PDF_Builder_Performance_Monitor {

    private static $metrics = [];
    private static $start_time;
    private static $memory_start;

    /**
     * Initialiser le monitoring
     */
    public static function init() {
        if (!defined('PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING')) {
            define('PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING', false);
        }

        if (!PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING) {
            return;
        }

        self::$start_time = microtime(true);
        self::$memory_start = memory_get_usage(true);

        // Hook pour mesurer les performances des pages admin
        add_action('admin_footer', [__CLASS__, 'track_admin_page_performance']);
        add_action('wp_footer', [__CLASS__, 'track_frontend_performance']);

        // Hook pour mesurer les performances des AJAX
        add_action('wp_ajax_pdf_builder_action', [__CLASS__, 'start_ajax_tracking'], 1);
        add_action('wp_ajax_nopriv_pdf_builder_action', [__CLASS__, 'start_ajax_tracking'], 1);

        // Hook de fin pour AJAX
        add_action('wp_ajax_pdf_builder_action', [__CLASS__, 'end_ajax_tracking'], 999);
        add_action('wp_ajax_nopriv_pdf_builder_action', [__CLASS__, 'end_ajax_tracking'], 999);
    }

    /**
     * Démarrer le tracking AJAX
     */
    public static function start_ajax_tracking() {
        self::$start_time = microtime(true);
        self::$memory_start = memory_get_usage(true);
    }

    /**
     * Terminer le tracking AJAX
     */
    public static function end_ajax_tracking() {
        $execution_time = microtime(true) - self::$start_time;
        $memory_used = memory_get_usage(true) - self::$memory_start;
        $peak_memory = memory_get_peak_usage(true);

        self::log_performance_metric('ajax_request', [
            'execution_time' => $execution_time,
            'memory_used' => $memory_used,
            'peak_memory' => $peak_memory,
            'action' => $_REQUEST['action'] ?? 'unknown'
        ]);
    }

    /**
     * Tracker les performances des pages admin
     */
    public static function track_admin_page_performance() {
        if (!isset($_GET['page']) || strpos($_GET['page'], 'pdf-builder') === false) {
            return;
        }

        $execution_time = microtime(true) - self::$start_time;
        $memory_used = memory_get_usage(true) - self::$memory_start;
        $peak_memory = memory_get_peak_usage(true);
        $query_count = get_num_queries();
        $query_time = timer_stop(0, 6);

        self::log_performance_metric('admin_page_load', [
            'page' => $_GET['page'],
            'execution_time' => $execution_time,
            'memory_used' => $memory_used,
            'peak_memory' => $peak_memory,
            'query_count' => $query_count,
            'query_time' => $query_time
        ]);

        // Afficher les métriques en mode debug
        if (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')) {
            echo '<!-- PDF Builder Performance Metrics: ';
            echo 'Time: ' . number_format($execution_time, 4) . 's, ';
            echo 'Memory: ' . size_format($memory_used) . ', ';
            echo 'Queries: ' . $query_count . ' (' . number_format($query_time, 4) . 's)';
            echo ' -->';
        }
    }

    /**
     * Tracker les performances frontend
     */
    public static function track_frontend_performance() {
        // Seulement si on est sur une page avec le canvas
        if (!isset($_GET['pdf-builder-canvas'])) {
            return;
        }

        $execution_time = microtime(true) - self::$start_time;
        $memory_used = memory_get_usage(true) - self::$memory_start;

        self::log_performance_metric('frontend_canvas_load', [
            'execution_time' => $execution_time,
            'memory_used' => $memory_used
        ]);
    }

    /**
     * Logger une métrique de performance
     */
    private static function log_performance_metric($type, $data) {
        $metric = [
            'timestamp' => time(),
            'type' => $type,
            'data' => $data,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        self::$metrics[] = $metric;

        // Log dans le fichier de performance
        $log_file = WP_CONTENT_DIR . '/pdf-builder-performance.log';
        $log_entry = date('Y-m-d H:i:s') . ' - ' . json_encode($metric) . "\n";

        // Rotation du log (max 2MB)
        if (file_exists($log_file) && filesize($log_file) > 2 * 1024 * 1024) {
            $content = file_get_contents($log_file);
            $lines = explode("\n", $content);
            // Garder seulement les 2000 dernières lignes
            $lines = array_slice($lines, -2000);
            file_put_contents($log_file, implode("\n", $lines));
        }

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Log aussi dans le logger du plugin
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::log("Performance [$type]: " . json_encode($data), 'info');
        }
    }

    /**
     * Obtenir les métriques récentes
     */
    public static function get_recent_metrics($limit = 100) {
        $log_file = WP_CONTENT_DIR . '/pdf-builder-performance.log';
        if (!file_exists($log_file)) {
            return [];
        }

        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $metrics = [];

        foreach (array_slice($lines, -$limit) as $line) {
            $parts = explode(' - ', $line, 2);
            if (count($parts) === 2) {
                $metrics[] = json_decode($parts[1], true);
            }
        }

        return array_reverse($metrics);
    }

    /**
     * Obtenir les statistiques de performance
     */
    public static function get_performance_stats() {
        $metrics = self::get_recent_metrics(1000);
        $stats = [
            'total_requests' => count($metrics),
            'avg_execution_time' => 0,
            'max_execution_time' => 0,
            'avg_memory_usage' => 0,
            'max_memory_usage' => 0,
            'slow_requests' => 0, // > 1 seconde
            'high_memory_requests' => 0 // > 50MB
        ];

        if (empty($metrics)) {
            return $stats;
        }

        $execution_times = [];
        $memory_usages = [];

        foreach ($metrics as $metric) {
            if (isset($metric['data']['execution_time'])) {
                $execution_times[] = $metric['data']['execution_time'];
                if ($metric['data']['execution_time'] > 1) {
                    $stats['slow_requests']++;
                }
                if ($metric['data']['execution_time'] > $stats['max_execution_time']) {
                    $stats['max_execution_time'] = $metric['data']['execution_time'];
                }
            }

            if (isset($metric['data']['memory_used'])) {
                $memory_usages[] = $metric['data']['memory_used'];
                if ($metric['data']['memory_used'] > 50 * 1024 * 1024) { // 50MB
                    $stats['high_memory_requests']++;
                }
                if ($metric['data']['memory_used'] > $stats['max_memory_usage']) {
                    $stats['max_memory_usage'] = $metric['data']['memory_used'];
                }
            }
        }

        if (!empty($execution_times)) {
            $stats['avg_execution_time'] = array_sum($execution_times) / count($execution_times);
        }

        if (!empty($memory_usages)) {
            $stats['avg_memory_usage'] = array_sum($memory_usages) / count($memory_usages);
        }

        return $stats;
    }

    /**
     * Nettoyer les anciens logs de performance
     */
    public static function cleanup_old_logs($days = 30) {
        $log_file = WP_CONTENT_DIR . '/pdf-builder-performance.log';
        if (!file_exists($log_file)) {
            return;
        }

        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        $filtered_lines = [];

        foreach ($lines as $line) {
            $parts = explode(' - ', $line, 2);
            if (count($parts) === 2) {
                $timestamp = strtotime($parts[0]);
                if ($timestamp > $cutoff_time) {
                    $filtered_lines[] = $line;
                }
            }
        }

        file_put_contents($log_file, implode("\n", $filtered_lines) . "\n");
    }
}

// Initialiser le monitoring si activé
if (defined('PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING') &&
    PDF_BUILDER_ENABLE_PERFORMANCE_MONITORING) {
    PDF_Builder_Performance_Monitor::init();
}