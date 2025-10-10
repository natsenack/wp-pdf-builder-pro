<?php
/**
 * Gestionnaire d'Analytics et Insights - PDF Builder Pro
 *
 * Système d'analyse avancé avec :
 * - Métriques de performance
 * - Statistiques d'utilisation
 * - Rapports automatisés
 * - Tableaux de bord interactifs
 * - Alertes intelligentes
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire d'Analytics et Insights
 */
class PDF_Builder_Analytics_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Analytics_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Cache manager
     * @var PDF_Builder_Cache_Manager
     */
    private $cache;

    /**
     * Métriques collectées
     * @var array
     */
    private $metrics = [];

    /**
     * Seuils d'alertes
     * @var array
     */
    private $alert_thresholds = [
        'generation_time' => 30, // secondes
        'error_rate' => 5, // pourcentage
        'memory_usage' => 128, // MB
        'disk_usage' => 1024 // MB
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->database = $core->get_database_manager();
        $this->logger = $core->get_logger();
        $this->cache = $core->get_cache_manager();

        $this->init_hooks();
        $this->init_metrics_collection();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Analytics_Manager
     */
    public static function getInstance(): PDF_Builder_Analytics_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks(): void {
        // Hooks pour la collecte de métriques
        add_action('pdf_builder_document_generated', [$this, 'track_document_generation'], 10, 2);
        add_action('pdf_builder_template_used', [$this, 'track_template_usage'], 10, 2);
        add_action('pdf_builder_export_completed', [$this, 'track_export'], 10, 3);
        add_action('pdf_builder_bulk_task_completed', [$this, 'track_bulk_operation'], 10, 2);

        // Hooks AJAX pour le tableau de bord
        add_action('wp_ajax_pdf_builder_analytics_data', [$this, 'ajax_get_analytics_data']);
        add_action('wp_ajax_pdf_builder_performance_metrics', [$this, 'ajax_get_performance_metrics']);

        // Hooks pour les rapports automatisés
        add_action('pdf_builder_generate_daily_report', [$this, 'generate_daily_report']);
        add_action('pdf_builder_generate_weekly_report', [$this, 'generate_weekly_report']);

        // Planifier les rapports
        if (!wp_next_scheduled('pdf_builder_generate_daily_report')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_generate_daily_report');
        }

        if (!wp_next_scheduled('pdf_builder_generate_weekly_report')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_generate_weekly_report');
        }

        // Hook pour les alertes
        add_action('pdf_builder_check_alerts', [$this, 'check_alerts']);

        if (!wp_next_scheduled('pdf_builder_check_alerts')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_check_alerts');
        }
    }

    /**
     * Initialiser la collecte de métriques
     */
    private function init_metrics_collection(): void {
        // Métriques de base
        $this->metrics = [
            'documents_generated_today' => 0,
            'documents_generated_week' => 0,
            'documents_generated_month' => 0,
            'total_documents' => 0,
            'templates_used_today' => [],
            'export_formats_used' => [],
            'generation_times' => [],
            'error_count' => 0,
            'peak_memory_usage' => 0,
            'average_generation_time' => 0
        ];

        $this->load_metrics_from_cache();
    }

    /**
     * Charger les métriques depuis le cache
     */
    private function load_metrics_from_cache(): void {
        $cached_metrics = $this->cache->get('analytics_metrics');
        if ($cached_metrics) {
            $this->metrics = array_merge($this->metrics, $cached_metrics);
        }
    }

    /**
     * Sauvegarder les métriques dans le cache
     */
    private function save_metrics_to_cache(): void {
        $this->cache->set('analytics_metrics', $this->metrics, 3600); // 1 heure
    }

    /**
     * Suivre la génération d'un document
     */
    public function track_document_generation(int $document_id, array $metadata): void {
        $this->metrics['documents_generated_today']++;
        $this->metrics['total_documents']++;

        if (isset($metadata['generation_time'])) {
            $this->metrics['generation_times'][] = $metadata['generation_time'];
            $this->update_average_generation_time();
        }

        if (isset($metadata['memory_usage'])) {
            $this->metrics['peak_memory_usage'] = max(
                $this->metrics['peak_memory_usage'],
                $metadata['memory_usage']
            );
        }

        $this->save_metrics_to_cache();

        // Logger l'événement
        $this->logger->info('Document generation tracked', [
            'document_id' => $document_id,
            'metadata' => $metadata
        ]);
    }

    /**
     * Suivre l'utilisation d'un template
     */
    public function track_template_usage(int $template_id, array $metadata): void {
        if (!isset($this->metrics['templates_used_today'][$template_id])) {
            $this->metrics['templates_used_today'][$template_id] = 0;
        }

        $this->metrics['templates_used_today'][$template_id]++;

        $this->save_metrics_to_cache();
    }

    /**
     * Suivre un export
     */
    public function track_export(int $document_id, string $format, array $metadata): void {
        if (!isset($this->metrics['export_formats_used'][$format])) {
            $this->metrics['export_formats_used'][$format] = 0;
        }

        $this->metrics['export_formats_used'][$format]++;

        $this->save_metrics_to_cache();
    }

    /**
     * Suivre une opération bulk
     */
    public function track_bulk_operation(string $task_id, array $metadata): void {
        // Logique pour suivre les opérations bulk
        $this->logger->info('Bulk operation tracked', [
            'task_id' => $task_id,
            'metadata' => $metadata
        ]);
    }

    /**
     * Mettre à jour le temps de génération moyen
     */
    private function update_average_generation_time(): void {
        if (!empty($this->metrics['generation_times'])) {
            $this->metrics['average_generation_time'] = array_sum($this->metrics['generation_times']) / count($this->metrics['generation_times']);
        }
    }

    /**
     * Obtenir les métriques actuelles
     */
    public function get_current_metrics(): array {
        return $this->metrics;
    }

    /**
     * Obtenir les statistiques détaillées
     */
    public function get_detailed_stats(string $period = 'month'): array {
        global $wpdb;

        $date_format = $this->get_date_format_for_period($period);
        $interval = $this->get_interval_for_period($period);

        $stats = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(DISTINCT d.id) as total_documents,
                COUNT(DISTINCT d.template_id) as unique_templates,
                AVG(TIMESTAMPDIFF(SECOND, d.created_at, d.generated_at)) as avg_generation_time,
                COUNT(CASE WHEN d.status = 'failed' THEN 1 END) as failed_documents,
                SUM(d.file_size) as total_file_size
            FROM {$wpdb->prefix}pdf_builder_documents d
            WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL %s)
        ", $interval));

        // Statistiques par template
        $template_stats = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                t.name as template_name,
                COUNT(d.id) as usage_count,
                AVG(TIMESTAMPDIFF(SECOND, d.created_at, d.generated_at)) as avg_time
            FROM {$wpdb->prefix}pdf_builder_templates t
            LEFT JOIN {$wpdb->prefix}pdf_builder_documents d ON t.id = d.template_id
            WHERE d.created_at >= DATE_SUB(NOW(), INTERVAL %s)
            GROUP BY t.id, t.name
            ORDER BY usage_count DESC
            LIMIT 10
        ", $interval));

        // Statistiques d'export
        $export_stats = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                el.format,
                COUNT(*) as count,
                AVG(el.file_size) as avg_file_size
            FROM {$wpdb->prefix}pdf_builder_export_logs el
            WHERE el.created_at >= DATE_SUB(NOW(), INTERVAL %s)
            GROUP BY el.format
            ORDER BY count DESC
        ", $interval));

        return [
            'period' => $period,
            'overview' => [
                'total_documents' => intval($stats->total_documents ?? 0),
                'unique_templates' => intval($stats->unique_templates ?? 0),
                'avg_generation_time' => round(floatval($stats->avg_generation_time ?? 0), 2),
                'failed_documents' => intval($stats->failed_documents ?? 0),
                'total_file_size' => intval($stats->total_file_size ?? 0),
                'success_rate' => $stats->total_documents > 0 ?
                    round((1 - ($stats->failed_documents / $stats->total_documents)) * 100, 2) : 0
            ],
            'template_usage' => $template_stats,
            'export_stats' => $export_stats,
            'performance_trends' => $this->get_performance_trends($period)
        ];
    }

    /**
     * Obtenir les tendances de performance
     */
    private function get_performance_trends(string $period): array {
        global $wpdb;

        $interval = $this->get_interval_for_period($period);
        $group_by = $this->get_group_by_for_period($period);

        $trends = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                DATE_FORMAT(created_at, '{$group_by}') as period_date,
                COUNT(*) as document_count,
                AVG(TIMESTAMPDIFF(SECOND, created_at, generated_at)) as avg_time,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count
            FROM {$wpdb->prefix}pdf_builder_documents
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %s)
            GROUP BY period_date
            ORDER BY period_date ASC
        ", $interval));

        return array_map(function($trend) {
            return [
                'date' => $trend->period_date,
                'documents' => intval($trend->document_count),
                'avg_time' => round(floatval($trend->avg_time ?? 0), 2),
                'failed' => intval($trend->failed_count),
                'success_rate' => $trend->document_count > 0 ?
                    round((1 - ($trend->failed_count / $trend->document_count)) * 100, 2) : 0
            ];
        }, $trends);
    }

    /**
     * Obtenir le format de date pour une période
     */
    private function get_date_format_for_period(string $period): string {
        $formats = [
            'day' => '%Y-%m-%d %H:00:00',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y'
        ];

        return $formats[$period] ?? '%Y-%m-%d';
    }

    /**
     * Obtenir l'intervalle pour une période
     */
    private function get_interval_for_period(string $period): string {
        $intervals = [
            'day' => '1 DAY',
            'week' => '1 WEEK',
            'month' => '1 MONTH',
            'year' => '1 YEAR'
        ];

        return $intervals[$period] ?? '1 MONTH';
    }

    /**
     * Obtenir le GROUP BY pour une période
     */
    private function get_group_by_for_period(string $period): string {
        $groups = [
            'day' => '%Y-%m-%d %H',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y'
        ];

        return $groups[$period] ?? '%Y-%m-%d';
    }

    /**
     * Générer le rapport quotidien
     */
    public function generate_daily_report(): void {
        $stats = $this->get_detailed_stats('day');

        $report = [
            'date' => date('Y-m-d'),
            'type' => 'daily',
            'stats' => $stats,
            'alerts' => $this->check_alerts(),
            'recommendations' => $this->generate_recommendations($stats)
        ];

        // Sauvegarder le rapport
        $this->save_report($report);

        // Envoyer par email si configuré
        if (get_option('pdf_builder_daily_report_email', false)) {
            $this->send_report_email($report, 'daily');
        }

        $this->logger->info('Daily analytics report generated');
    }

    /**
     * Générer le rapport hebdomadaire
     */
    public function generate_weekly_report(): void {
        $stats = $this->get_detailed_stats('week');

        $report = [
            'date' => date('Y-m-d'),
            'type' => 'weekly',
            'stats' => $stats,
            'alerts' => $this->check_alerts(),
            'recommendations' => $this->generate_recommendations($stats)
        ];

        // Sauvegarder le rapport
        $this->save_report($report);

        // Envoyer par email
        if (get_option('pdf_builder_weekly_report_email', true)) {
            $this->send_report_email($report, 'weekly');
        }

        $this->logger->info('Weekly analytics report generated');
    }

    /**
     * Sauvegarder un rapport
     */
    private function save_report(array $report): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_analytics_reports',
            [
                'report_type' => $report['type'],
                'report_data' => wp_json_encode($report),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s']
        );
    }

    /**
     * Vérifier les alertes
     */
    public function check_alerts(): array {
        $alerts = [];

        // Alerte temps de génération
        if ($this->metrics['average_generation_time'] > $this->alert_thresholds['generation_time']) {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf(
                    'Temps de génération moyen élevé: %.2f secondes (seuil: %d secondes)',
                    $this->metrics['average_generation_time'],
                    $this->alert_thresholds['generation_time']
                ),
                'recommendation' => 'Optimiser les templates ou augmenter les ressources serveur'
            ];
        }

        // Alerte taux d'erreur
        $error_rate = $this->metrics['total_documents'] > 0 ?
            ($this->metrics['error_count'] / $this->metrics['total_documents']) * 100 : 0;

        if ($error_rate > $this->alert_thresholds['error_rate']) {
            $alerts[] = [
                'type' => 'error',
                'message' => sprintf(
                    'Taux d\'erreur élevé: %.2f%% (seuil: %d%%)',
                    $error_rate,
                    $this->alert_thresholds['error_rate']
                ),
                'recommendation' => 'Vérifier les logs d\'erreur et corriger les problèmes'
            ];
        }

        // Alerte utilisation mémoire
        if ($this->metrics['peak_memory_usage'] > $this->alert_thresholds['memory_usage'] * 1024 * 1024) {
            $alerts[] = [
                'type' => 'warning',
                'message' => sprintf(
                    'Utilisation mémoire élevée: %.2f MB (seuil: %d MB)',
                    $this->metrics['peak_memory_usage'] / 1024 / 1024,
                    $this->alert_thresholds['memory_usage']
                ),
                'recommendation' => 'Augmenter la limite mémoire PHP ou optimiser le code'
            ];
        }

        return $alerts;
    }

    /**
     * Générer des recommandations
     */
    private function generate_recommendations(array $stats): array {
        $recommendations = [];

        // Recommandation basée sur les templates populaires
        if (!empty($stats['template_usage'])) {
            $top_template = $stats['template_usage'][0];
            if ($top_template->usage_count > 10) {
                $recommendations[] = [
                    'type' => 'optimization',
                    'message' => sprintf(
                        'Le template "%s" est très utilisé (%d fois). Considérez l\'optimiser pour de meilleures performances.',
                        $top_template->template_name,
                        $top_template->usage_count
                    )
                ];
            }
        }

        // Recommandation basée sur les formats d'export
        if (!empty($stats['export_stats'])) {
            $total_exports = array_sum(array_column($stats['export_stats'], 'count'));
            foreach ($stats['export_stats'] as $export_stat) {
                $percentage = ($export_stat->count / $total_exports) * 100;
                if ($percentage > 70) {
                    $recommendations[] = [
                        'type' => 'usage',
                        'message' => sprintf(
                            'Le format %s représente %.1f%% des exports. Considérez l\'optimiser.',
                            strtoupper($export_stat->format),
                            $percentage
                        )
                    ];
                }
            }
        }

        return $recommendations;
    }

    /**
     * Envoyer le rapport par email
     */
    private function send_report_email(array $report, string $type): void {
        $to = get_option('admin_email');
        $subject = sprintf('Rapport d\'analyse PDF Builder Pro - %s (%s)', ucfirst($type), date('d/m/Y'));

        $message = $this->generate_report_html($report);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: PDF Builder Pro <' . get_option('admin_email') . '>'
        ];

        wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Générer le HTML du rapport
     */
    private function generate_report_html(array $report): string {
        $stats = $report['stats'];

        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #007cba; color: white; padding: 20px; border-radius: 5px; }
                .stats { display: flex; flex-wrap: wrap; margin: 20px 0; }
                .stat-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 10px; flex: 1; min-width: 200px; }
                .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
                .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; }
                .alert-error { background: #f8d7da; border: 1px solid #f5c6cb; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                th { background: #f8f9fa; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Rapport d\'analyse PDF Builder Pro</h1>
                <p>' . ucfirst($report['type']) . ' - ' . date('d/m/Y', strtotime($report['date'])) . '</p>
            </div>

            <div class="stats">
                <div class="stat-box">
                    <h3>Documents générés</h3>
                    <p style="font-size: 24px; font-weight: bold;">' . $stats['overview']['total_documents'] . '</p>
                </div>
                <div class="stat-box">
                    <h3>Temps moyen</h3>
                    <p style="font-size: 24px; font-weight: bold;">' . $stats['overview']['avg_generation_time'] . 's</p>
                </div>
                <div class="stat-box">
                    <h3>Taux de succès</h3>
                    <p style="font-size: 24px; font-weight: bold;">' . $stats['overview']['success_rate'] . '%</p>
                </div>
                <div class="stat-box">
                    <h3>Templates utilisés</h3>
                    <p style="font-size: 24px; font-weight: bold;">' . $stats['overview']['unique_templates'] . '</p>
                </div>
            </div>
        ';

        // Alertes
        if (!empty($report['alerts'])) {
            $html .= '<h2>Alertes</h2>';
            foreach ($report['alerts'] as $alert) {
                $class = $alert['type'] === 'error' ? 'alert-error' : 'alert-warning';
                $html .= "<div class='alert {$class}'>
                    <strong>" . ucfirst($alert['type']) . ":</strong> {$alert['message']}
                    <br><em>Recommandation: {$alert['recommendation']}</em>
                </div>";
            }
        }

        // Recommandations
        if (!empty($report['recommendations'])) {
            $html .= '<h2>Recommandations</h2>';
            foreach ($report['recommendations'] as $rec) {
                $html .= "<div class='alert alert-info'>
                    <strong>{$rec['type']}:</strong> {$rec['message']}
                </div>";
            }
        }

        // Statistiques détaillées
        if (!empty($stats['template_usage'])) {
            $html .= '<h2>Utilisation des templates</h2>
            <table>
                <tr><th>Template</th><th>Utilisations</th><th>Temps moyen</th></tr>';
            foreach ($stats['template_usage'] as $template) {
                $html .= "<tr>
                    <td>{$template->template_name}</td>
                    <td>{$template->usage_count}</td>
                    <td>{$template->avg_time}s</td>
                </tr>";
            }
            $html .= '</table>';
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * AJAX: Obtenir les données d'analytics
     */
    public function ajax_get_analytics_data(): void {
        check_ajax_referer('pdf_builder_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        $period = sanitize_text_field($_POST['period'] ?? 'month');
        $data = $this->get_detailed_stats($period);

        wp_send_json_success($data);
    }

    /**
     * AJAX: Obtenir les métriques de performance
     */
    public function ajax_get_performance_metrics(): void {
        check_ajax_referer('pdf_builder_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
        }

        wp_send_json_success($this->get_current_metrics());
    }

    /**
     * Obtenir les seuils d'alertes
     */
    public function get_alert_thresholds(): array {
        return $this->alert_thresholds;
    }

    /**
     * Définir un seuil d'alerte
     */
    public function set_alert_threshold(string $key, $value): void {
        $this->alert_thresholds[$key] = $value;
        $this->cache->set('analytics_alert_thresholds', $this->alert_thresholds, 86400); // 24h
    }

    /**
     * Réinitialiser les métriques
     */
    public function reset_metrics(): void {
        $this->metrics = array_fill_keys(array_keys($this->metrics), 0);
        $this->metrics['templates_used_today'] = [];
        $this->metrics['export_formats_used'] = [];
        $this->metrics['generation_times'] = [];

        $this->save_metrics_to_cache();

        $this->logger->info('Analytics metrics reset');
    }
}