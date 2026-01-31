<?php
/**
 * PDF Builder Pro - Système de métriques et analyses avancées
 * Collecte et analyse les métriques de performance et d'utilisation
 */

class PDF_Builder_Metrics_Analytics {
    private static $instance = null;

    // Types de métriques
    const METRIC_PDF_GENERATION = 'pdf_generation';
    const METRIC_API_CALLS = 'api_calls';
    const METRIC_USER_ACTIONS = 'user_actions';
    const METRIC_PERFORMANCE = 'performance';
    const METRIC_ERRORS = 'errors';
    const METRIC_SECURITY = 'security';

    // Périodes d'analyse
    const PERIOD_HOURLY = 'hourly';
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';

    // Métriques en temps réel
    private $realtime_metrics = [];
    private $metric_buffers = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_metric_collection();
    }

    private function init_hooks() {
        // Collecte de métriques
        add_action('wp_ajax_pdf_builder_track_metric', [$this, 'track_metric_ajax']);
        add_action('wp_ajax_pdf_builder_get_metrics', [$this, 'get_metrics_ajax']);
        add_action('wp_ajax_pdf_builder_get_metrics_analytics', [$this, 'get_analytics_ajax']);

        // Actions de métriques automatiques
        add_action('pdf_builder_pdf_generated', [$this, 'track_pdf_generation']);
        add_action('pdf_builder_api_call', [$this, 'track_api_call']);
        add_action('pdf_builder_user_action', [$this, 'track_user_action']);
        add_action('pdf_builder_performance_metric', [$this, 'track_performance_metric']);
        add_action('pdf_builder_error_occurred', [$this, 'track_error']);
        add_action('pdf_builder_security_event', [$this, 'track_security_event']);

        // Agrégation périodique
        add_action('pdf_builder_hourly_aggregation', [$this, 'aggregate_hourly_metrics']);
        add_action('pdf_builder_daily_aggregation', [$this, 'aggregate_daily_metrics']);
        add_action('pdf_builder_weekly_aggregation', [$this, 'aggregate_weekly_metrics']);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_metrics']);

        // Rapports automatiques
        add_action('pdf_builder_weekly_report', [$this, 'generate_weekly_report']);
        add_action('pdf_builder_monthly_report', [$this, 'generate_monthly_report']);
    }

    /**
     * Initialise la collecte de métriques
     */
    private function init_metric_collection() {
        // Planifier les agrégations
        if (!wp_next_scheduled('pdf_builder_hourly_aggregation')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_hourly_aggregation');
        }

        if (!wp_next_scheduled('pdf_builder_daily_aggregation')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_daily_aggregation');
        }

        if (!wp_next_scheduled('pdf_builder_weekly_aggregation')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_weekly_aggregation');
        }

        if (!wp_next_scheduled('pdf_builder_weekly_report')) {
            wp_schedule_event(strtotime('next monday'), 'weekly', 'pdf_builder_weekly_report');
        }

        if (!wp_next_scheduled('pdf_builder_monthly_report')) {
            wp_schedule_event(strtotime('first day of next month'), 'monthly', 'pdf_builder_monthly_report');
        }
    }

    /**
     * Suit une métrique
     */
    public function track_metric($type, $name, $value = 1, $metadata = [], $user_id = null) {
        $user_id = $user_id ?: get_current_user_id();

        $metric = [
            'type' => $type,
            'name' => $name,
            'value' => $value,
            'metadata' => $metadata,
            'user_id' => $user_id,
            'timestamp' => microtime(true),
            'session_id' => session_id() ?: wp_generate_password(32, false),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];

        // Ajouter aux métriques temps réel
        $this->add_realtime_metric($metric);

        // Stocker dans le buffer pour l'insertion en lot
        $this->buffer_metric($metric);

        // Déclencher les actions
        do_action('pdf_builder_metric_tracked', $metric);

        return $metric;
    }

    /**
     * Ajoute une métrique temps réel
     */
    private function add_realtime_metric($metric) {
        $key = $metric['type'] . '_' . $metric['name'];

        if (!isset($this->realtime_metrics[$key])) {
            $this->realtime_metrics[$key] = [
                'count' => 0,
                'sum' => 0,
                'avg' => 0,
                'min' => PHP_FLOAT_MAX,
                'max' => PHP_FLOAT_MIN,
                'last_updated' => time()
            ];
        }

        $stats = &$this->realtime_metrics[$key];
        $stats['count']++;
        $stats['sum'] += $metric['value'];
        $stats['avg'] = $stats['sum'] / $stats['count'];
        $stats['min'] = min($stats['min'], $metric['value']);
        $stats['max'] = max($stats['max'], $metric['value']);
        $stats['last_updated'] = time();
    }

    /**
     * Met en buffer une métrique pour insertion en lot
     */
    private function buffer_metric($metric) {
        $this->metric_buffers[] = $metric;

        // Insérer en lot tous les 100 métriques ou toutes les 30 secondes
        if (count($this->metric_buffers) >= 100) {
            $this->flush_metric_buffer();
        }
    }

    /**
     * Vide le buffer de métriques
     */
    public function flush_metric_buffer() {
        if (empty($this->metric_buffers)) {
            return;
        }

        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_metrics';

        $values = [];
        $placeholders = [];

        foreach ($this->metric_buffers as $metric) {
            $values = array_merge($values, [
                $metric['type'],
                $metric['name'],
                $metric['value'],
                json_encode($metric['metadata']),
                $metric['user_id'],
                date('Y-m-d H:i:s', $metric['timestamp']),
                $metric['session_id'],
                $metric['ip_address'],
                $metric['user_agent']
            ]);

            $placeholders[] = '(%s, %s, %f, %s, %d, %s, %s, %s, %s)';
        }

        $query = "INSERT INTO $table (type, name, value, metadata, user_id, timestamp, session_id, ip_address, user_agent) VALUES " . implode(', ', $placeholders);

        $wpdb->query($wpdb->prepare($query, $values));

        $this->metric_buffers = [];
    }

    /**
     * Obtient l'adresse IP du client
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

                // Gérer X-Forwarded-For avec multiples IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Suit la génération de PDF
     */
    public function track_pdf_generation($pdf_data) {
        $this->track_metric(
            self::METRIC_PDF_GENERATION,
            'pdf_generated',
            1,
            [
                'template_id' => $pdf_data['template_id'] ?? null,
                'size' => $pdf_data['size'] ?? 0,
                'generation_time' => $pdf_data['generation_time'] ?? 0,
                'success' => $pdf_data['success'] ?? true
            ]
        );
    }

    /**
     * Suit les appels API
     */
    public function track_api_call($api_data) {
        $this->track_metric(
            self::METRIC_API_CALLS,
            $api_data['endpoint'] ?? 'unknown',
            1,
            [
                'method' => $api_data['method'] ?? 'GET',
                'response_time' => $api_data['response_time'] ?? 0,
                'status_code' => $api_data['status_code'] ?? 0,
                'success' => $api_data['success'] ?? false
            ]
        );
    }

    /**
     * Suit les actions utilisateur
     */
    public function track_user_action($action_data) {
        $this->track_metric(
            self::METRIC_USER_ACTIONS,
            $action_data['action'],
            1,
            [
                'page' => $action_data['page'] ?? null,
                'element' => $action_data['element'] ?? null,
                'value' => $action_data['value'] ?? null
            ]
        );
    }

    /**
     * Suit les métriques de performance
     */
    public function track_performance_metric($performance_data) {
        $this->track_metric(
            self::METRIC_PERFORMANCE,
            $performance_data['metric'],
            $performance_data['value'],
            [
                'context' => $performance_data['context'] ?? null,
                'threshold' => $performance_data['threshold'] ?? null
            ]
        );
    }

    /**
     * Suit les erreurs
     */
    public function track_error($error_data) {
        $this->track_metric(
            self::METRIC_ERRORS,
            $error_data['type'] ?? 'unknown',
            1,
            [
                'message' => $error_data['message'] ?? '',
                'file' => $error_data['file'] ?? '',
                'line' => $error_data['line'] ?? 0,
                'trace' => $error_data['trace'] ?? ''
            ]
        );
    }

    /**
     * Suit les événements de sécurité
     */
    public function track_security_event($security_data) {
        $this->track_metric(
            self::METRIC_SECURITY,
            $security_data['event'],
            1,
            [
                'severity' => $security_data['severity'] ?? 'low',
                'details' => $security_data['details'] ?? [],
                'blocked' => $security_data['blocked'] ?? false
            ]
        );
    }

    /**
     * Obtient les métriques temps réel
     */
    public function get_realtime_metrics() {
        // Nettoyer les métriques anciennes (plus de 5 minutes)
        $cutoff = time() - 300;

        foreach ($this->realtime_metrics as $key => $stats) {
            if ($stats['last_updated'] < $cutoff) {
                unset($this->realtime_metrics[$key]);
            }
        }

        return $this->realtime_metrics;
    }

    /**
     * Obtient les métriques historiques
     */
    public function get_metrics($type = null, $name = null, $period = self::PERIOD_DAILY, $limit = 30) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_metrics_aggregated';

        $where = ['period = %s'];
        $params = [$period];

        if ($type) {
            $where[] = 'type = %s';
            $params[] = $type;
        }

        if ($name) {
            $where[] = 'name = %s';
            $params[] = $name;
        }

        $query = $wpdb->prepare("
            SELECT * FROM $table
            WHERE " . implode(' AND ', $where) . "
            ORDER BY date DESC
            LIMIT %d
        ", array_merge($params, [$limit]));

        return $wpdb->get_results($query, ARRAY_A);
    }

    /**
     * Obtient les analyses avancées
     */
    public function get_analytics($period = self::PERIOD_MONTHLY) {
        $analytics = [
            'overview' => $this->get_overview_analytics($period),
            'performance' => $this->get_performance_analytics($period),
            'usage' => $this->get_usage_analytics($period),
            'errors' => $this->get_error_analytics($period),
            'security' => $this->get_security_analytics($period),
            'trends' => $this->get_trend_analytics($period)
        ];

        return $analytics;
    }

    /**
     * Obtient les analyses générales
     */
    private function get_overview_analytics($period) {
        $metrics = $this->get_metrics(null, null, $period, 1);

        if (empty($metrics)) {
            return [
                'total_pdfs' => 0,
                'total_users' => 0,
                'avg_generation_time' => 0,
                'success_rate' => 0
            ];
        }

        $data = $metrics[0]['data'];
        $data = json_decode($data, true);

        return [
            'total_pdfs' => $data['pdf_generation_count'] ?? 0,
            'total_users' => $data['unique_users'] ?? 0,
            'avg_generation_time' => $data['avg_generation_time'] ?? 0,
            'success_rate' => $data['pdf_success_rate'] ?? 0
        ];
    }

    /**
     * Obtient les analyses de performance
     */
    private function get_performance_analytics($period) {
        $performance_metrics = $this->get_metrics(self::METRIC_PERFORMANCE, null, $period, 30);

        $analytics = [
            'response_times' => [],
            'memory_usage' => [],
            'cpu_usage' => [],
            'slow_operations' => []
        ];

        foreach ($performance_metrics as $metric) {
            $data = json_decode($metric['data'], true);

            $analytics['response_times'][] = [
                'date' => $metric['date'],
                'avg' => $data['avg_response_time'] ?? 0,
                'p95' => $data['p95_response_time'] ?? 0
            ];

            $analytics['memory_usage'][] = [
                'date' => $metric['date'],
                'avg' => $data['avg_memory_usage'] ?? 0,
                'peak' => $data['peak_memory_usage'] ?? 0
            ];
        }

        return $analytics;
    }

    /**
     * Obtient les analyses d'utilisation
     */
    private function get_usage_analytics($period) {
        $usage_metrics = $this->get_metrics(self::METRIC_USER_ACTIONS, null, $period, 30);

        $analytics = [
            'page_views' => [],
            'feature_usage' => [],
            'user_engagement' => []
        ];

        foreach ($usage_metrics as $metric) {
            $data = json_decode($metric['data'], true);

            $analytics['page_views'][] = [
                'date' => $metric['date'],
                'count' => $data['page_view_count'] ?? 0
            ];

            $analytics['feature_usage'][] = [
                'date' => $metric['date'],
                'features' => $data['feature_usage'] ?? []
            ];
        }

        return $analytics;
    }

    /**
     * Obtient les analyses d'erreurs
     */
    private function get_error_analytics($period) {
        $error_metrics = $this->get_metrics(self::METRIC_ERRORS, null, $period, 30);

        $analytics = [
            'error_counts' => [],
            'error_types' => [],
            'error_trends' => []
        ];

        foreach ($error_metrics as $metric) {
            $data = json_decode($metric['data'], true);

            $analytics['error_counts'][] = [
                'date' => $metric['date'],
                'count' => $data['total_errors'] ?? 0
            ];

            $analytics['error_types'][] = [
                'date' => $metric['date'],
                'types' => $data['error_types'] ?? []
            ];
        }

        return $analytics;
    }

    /**
     * Obtient les analyses de sécurité
     */
    private function get_security_analytics($period) {
        $security_metrics = $this->get_metrics(self::METRIC_SECURITY, null, $period, 30);

        $analytics = [
            'security_events' => [],
            'blocked_attempts' => [],
            'threat_levels' => []
        ];

        foreach ($security_metrics as $metric) {
            $data = json_decode($metric['data'], true);

            $analytics['security_events'][] = [
                'date' => $metric['date'],
                'count' => $data['total_events'] ?? 0
            ];

            $analytics['blocked_attempts'][] = [
                'date' => $metric['date'],
                'count' => $data['blocked_attempts'] ?? 0
            ];
        }

        return $analytics;
    }

    /**
     * Obtient les analyses de tendances
     */
    private function get_trend_analytics($period) {
        $all_metrics = $this->get_metrics(null, null, $period, 60);

        $trends = [
            'growth_rate' => 0,
            'performance_trend' => 'stable',
            'usage_trend' => 'stable',
            'error_trend' => 'stable'
        ];

        if (count($all_metrics) >= 2) {
            $current = json_decode($all_metrics[0]['data'], true);
            $previous = json_decode($all_metrics[1]['data'], true);

            // Calculer les taux de croissance
            if (isset($current['pdf_generation_count']) && isset($previous['pdf_generation_count'])) {
                $trends['growth_rate'] = (($current['pdf_generation_count'] - $previous['pdf_generation_count']) / $previous['pdf_generation_count']) * 100;
            }

            // Déterminer les tendances
            $trends['performance_trend'] = $this->calculate_trend($current['avg_response_time'] ?? 0, $previous['avg_response_time'] ?? 0, 'desc');
            $trends['usage_trend'] = $this->calculate_trend($current['pdf_generation_count'] ?? 0, $previous['pdf_generation_count'] ?? 0, 'asc');
            $trends['error_trend'] = $this->calculate_trend($current['total_errors'] ?? 0, $previous['total_errors'] ?? 0, 'desc');
        }

        return $trends;
    }

    /**
     * Calcule une tendance
     */
    private function calculate_trend($current, $previous, $direction = 'asc') {
        if ($previous == 0) {
            return 'stable';
        }

        $change = (($current - $previous) / $previous) * 100;

        if ($direction === 'asc') {
            return $change > 5 ? 'increasing' : ($change < -5 ? 'decreasing' : 'stable');
        } else {
            return $change < -5 ? 'improving' : ($change > 5 ? 'worsening' : 'stable');
        }
    }

    /**
     * Agrège les métriques horaires
     */
    public function aggregate_hourly_metrics() {
        $this->aggregate_metrics('hourly', '-1 hour');
    }

    /**
     * Agrège les métriques quotidiennes
     */
    public function aggregate_daily_metrics() {
        $this->aggregate_metrics('daily', '-1 day');
    }

    /**
     * Agrège les métriques hebdomadaires
     */
    public function aggregate_weekly_metrics() {
        $this->aggregate_metrics('weekly', '-1 week');
    }

    /**
     * Agrège les métriques pour une période
     */
    private function aggregate_metrics($period, $time_range) {
        global $wpdb;

        $raw_table = $wpdb->prefix . 'pdf_builder_metrics';
        $agg_table = $wpdb->prefix . 'pdf_builder_metrics_aggregated';

        $start_time = date('Y-m-d H:i:s', strtotime($time_range));

        // Agréger par type et nom
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT
                type,
                name,
                COUNT(*) as count,
                AVG(value) as avg_value,
                MIN(value) as min_value,
                MAX(value) as max_value,
                SUM(value) as sum_value
            FROM $raw_table
            WHERE timestamp >= %s
            GROUP BY type, name
        ", $start_time), ARRAY_A);

        foreach ($results as $result) {
            $aggregated_data = $this->calculate_aggregated_data($result['type'], $start_time);

            $wpdb->replace(
                $agg_table,
                [
                    'period' => $period,
                    'type' => $result['type'],
                    'name' => $result['name'],
                    'date' => date('Y-m-d H:i:s'),
                    'count' => $result['count'],
                    'avg_value' => $result['avg_value'],
                    'min_value' => $result['min_value'],
                    'max_value' => $result['max_value'],
                    'sum_value' => $result['sum_value'],
                    'data' => json_encode($aggregated_data)
                ],
                ['%s', '%s', '%s', '%s', '%d', '%f', '%f', '%f', '%f', '%s']
            );
        }

        // Vider les métriques brutes anciennes
        $wpdb->query($wpdb->prepare("
            DELETE FROM $raw_table
            WHERE timestamp < %s
        ", $start_time));
    }

    /**
     * Calcule les données agrégées spécifiques au type
     */
    private function calculate_aggregated_data($type, $start_time) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_metrics';

        switch ($type) {
            case self::METRIC_PDF_GENERATION:
                return $wpdb->get_row($wpdb->prepare("
                    SELECT
                        COUNT(CASE WHEN JSON_EXTRACT(metadata, '$.success') = 'true' THEN 1 END) as successful_generations,
                        COUNT(*) as total_generations,
                        AVG(CAST(JSON_EXTRACT(metadata, '$.generation_time') AS DECIMAL)) as avg_generation_time,
                        AVG(CAST(JSON_EXTRACT(metadata, '$.size') AS DECIMAL)) as avg_file_size
                    FROM $table
                    WHERE type = %s AND timestamp >= %s
                ", $type, $start_time), ARRAY_A);

            case self::METRIC_API_CALLS:
                return $wpdb->get_row($wpdb->prepare("
                    SELECT
                        COUNT(CASE WHEN JSON_EXTRACT(metadata, '$.success') = 'true' THEN 1 END) as successful_calls,
                        COUNT(*) as total_calls,
                        AVG(CAST(JSON_EXTRACT(metadata, '$.response_time') AS DECIMAL)) as avg_response_time
                    FROM $table
                    WHERE type = %s AND timestamp >= %s
                ", $type, $start_time), ARRAY_A);

            case self::METRIC_USER_ACTIONS:
                return $wpdb->get_row($wpdb->prepare("
                    SELECT
                        COUNT(DISTINCT user_id) as unique_users,
                        COUNT(*) as total_actions
                    FROM $table
                    WHERE type = %s AND timestamp >= %s
                ", $type, $start_time), ARRAY_A);

            default:
                return [];
        }
    }

    /**
     * Génère le rapport hebdomadaire
     */
    public function generate_weekly_report() {
        $analytics = $this->get_analytics(self::PERIOD_WEEKLY);

        $report = [
            'period' => 'weekly',
            'generated_at' => current_time('mysql'),
            'data' => $analytics
        ];

        // Sauvegarder le rapport
        pdf_builder_update_option('pdf_builder_weekly_report', $report);

        // Legacy notification calls removed — replaced by logger info
        PDF_Builder_Logger::get_instance()->info('Rapport hebdomadaire généré: Le rapport d\'analyse hebdomadaire est disponible dans le tableau de bord.', ['report' => $report]);
    }

    /**
     * Génère le rapport mensuel
     */
    public function generate_monthly_report() {
        $analytics = $this->get_analytics(self::PERIOD_MONTHLY);

        $report = [
            'period' => 'monthly',
            'generated_at' => current_time('mysql'),
            'data' => $analytics
        ];

        // Sauvegarder le rapport
        pdf_builder_update_option('pdf_builder_monthly_report', $report);

        // Legacy notification calls removed — replaced by logger info
        PDF_Builder_Logger::get_instance()->info('Rapport mensuel généré: Le rapport d\'analyse mensuel est disponible dans le tableau de bord.', ['report' => $report]);
    }

    /**
     * Nettoie les anciennes métriques
     */
    public function cleanup_old_metrics() {
        global $wpdb;

        $agg_table = $wpdb->prefix . 'pdf_builder_metrics_aggregated';

        // Garder seulement 1 an de données agrégées
        $wpdb->query($wpdb->prepare("
            DELETE FROM $agg_table
            WHERE date < %s
        ", date('Y-m-d H:i:s', strtotime('-1 year'))));
    }

    /**
     * AJAX - Suit une métrique
     */
    public function track_metric_ajax() {
        try {
            $type = sanitize_text_field($_POST['type'] ?? '');
            $name = sanitize_text_field($_POST['name'] ?? '');
            $value = floatval($_POST['value'] ?? 1);
            $metadata = $_POST['metadata'] ?? [];

            if (empty($type) || empty($name)) {
                wp_send_json_error(['message' => 'Type et nom de métrique requis']);
                return;
            }

            $metadata = array_map('sanitize_text_field', $metadata);

            $metric = $this->track_metric($type, $name, $value, $metadata);

            wp_send_json_success([
                'message' => 'Métrique suivie',
                'metric' => $metric
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les métriques
     */
    public function get_metrics_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $type = sanitize_text_field($_POST['type'] ?? null);
            $name = sanitize_text_field($_POST['name'] ?? null);
            $period = sanitize_text_field($_POST['period'] ?? self::PERIOD_DAILY);
            $limit = intval($_POST['limit'] ?? 30);

            $metrics = $this->get_metrics($type, $name, $period, $limit);

            wp_send_json_success([
                'message' => 'Métriques récupérées',
                'metrics' => $metrics
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les analyses
     */
    public function get_analytics_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $period = sanitize_text_field($_POST['period'] ?? self::PERIOD_MONTHLY);

            $analytics = $this->get_analytics($period);

            wp_send_json_success([
                'message' => 'Analyses récupérées',
                'analytics' => $analytics
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_metrics() {
    return PDF_Builder_Metrics_Analytics::get_instance();
}

function pdf_builder_track_metric($type, $name, $value = 1, $metadata = [], $user_id = null) {
    return PDF_Builder_Metrics_Analytics::get_instance()->track_metric($type, $name, $value, $metadata, $user_id);
}

function pdf_builder_get_metrics($type = null, $name = null, $period = 'daily', $limit = 30) {
    return PDF_Builder_Metrics_Analytics::get_instance()->get_metrics($type, $name, $period, $limit);
}

function pdf_builder_get_metrics_analytics($period = 'monthly') {
    return PDF_Builder_Metrics_Analytics::get_instance()->get_analytics($period);
}

function pdf_builder_get_realtime_metrics() {
    return PDF_Builder_Metrics_Analytics::get_instance()->get_realtime_metrics();
}

// Initialiser le système de métriques
add_action('plugins_loaded', function() {
    PDF_Builder_Metrics_Analytics::get_instance();
});

// Vider le buffer de métriques à la fin de la requête
add_action('shutdown', function() {
    if (class_exists('PDF_Builder_Metrics_Analytics')) {
        PDF_Builder_Metrics_Analytics::get_instance()->flush_metric_buffer();
    }
});




