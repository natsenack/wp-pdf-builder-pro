<?php
/**
 * PDF Builder Pro - Système de reporting et analyse avancé
 * Fournit des rapports détaillés et des analyses d'utilisation
 */

class PDF_Builder_Advanced_Reporting {
    private static $instance = null;

    // Types de rapports
    const REPORT_TYPE_USAGE = 'usage';
    const REPORT_TYPE_PERFORMANCE = 'performance';
    const REPORT_TYPE_SECURITY = 'security';
    const REPORT_TYPE_FINANCIAL = 'financial';
    const REPORT_TYPE_USER_ACTIVITY = 'user_activity';
    const REPORT_TYPE_SYSTEM_HEALTH = 'system_health';

    // Périodes de rapport
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_YEARLY = 'yearly';
    const PERIOD_CUSTOM = 'custom';

    // Formats d'export
    const FORMAT_PDF = 'pdf';
    const FORMAT_CSV = 'csv';
    const FORMAT_JSON = 'json';
    const FORMAT_HTML = 'html';
    const FORMAT_EXCEL = 'excel';

    // Clés de stockage
    const OPTION_REPORT_SETTINGS = 'pdf_builder_report_settings';
    const OPTION_REPORT_DATA = 'pdf_builder_report_data';
    const OPTION_REPORT_SCHEDULE = 'pdf_builder_report_schedule';

    // Cache des données
    private $report_settings = [];
    private $report_data = [];
    private $report_schedule = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_report_data();
    }

    private function init_hooks() {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_generate_report', [$this, 'generate_report_ajax']);
        add_action('wp_ajax_pdf_builder_export_report', [$this, 'export_report_ajax']);
        add_action('wp_ajax_pdf_builder_schedule_report', [$this, 'schedule_report_ajax']);
        add_action('wp_ajax_pdf_builder_get_report_data', [$this, 'get_report_data_ajax']);
        add_action('wp_ajax_pdf_builder_save_report_settings', [$this, 'save_report_settings_ajax']);

        // Actions d'administration
        add_action('admin_init', [$this, 'register_report_settings']);
        add_action('admin_menu', [$this, 'add_reporting_menu']);
        add_action('admin_notices', [$this, 'display_report_notices']);

        // Actions programmées
        add_action('pdf_builder_generate_scheduled_reports', [$this, 'generate_scheduled_reports']);
        add_action('pdf_builder_cleanup_old_reports', [$this, 'cleanup_old_reports']);
        add_action('pdf_builder_update_report_data', [$this, 'update_report_data']);

        // Filtres
        add_filter('pdf_builder_dashboard_widgets', [$this, 'add_dashboard_widgets']);

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_reports']);
    }

    /**
     * Charge les données de rapport
     */
    private function load_report_data() {
        $this->report_settings = get_option(self::OPTION_REPORT_SETTINGS, $this->get_default_settings());
        $this->report_data = get_option(self::OPTION_REPORT_DATA, []);
        $this->report_schedule = get_option(self::OPTION_REPORT_SCHEDULE, []);
    }

    /**
     * Obtient les paramètres par défaut
     */
    private function get_default_settings() {
        return [
            'auto_generate_reports' => true,
            'report_frequency' => self::PERIOD_WEEKLY,
            'default_format' => self::FORMAT_PDF,
            'email_reports' => true,
            'report_types' => [
                self::REPORT_TYPE_USAGE => true,
                self::REPORT_TYPE_PERFORMANCE => true,
                self::REPORT_TYPE_SECURITY => false,
                self::REPORT_TYPE_FINANCIAL => false,
                self::REPORT_TYPE_USER_ACTIVITY => true,
                self::REPORT_TYPE_SYSTEM_HEALTH => true
            ],
            'data_retention_days' => 90,
            'anonymize_data' => false,
            'include_charts' => true,
            'cache_reports' => true,
            'max_report_age' => 3600 // 1 heure
        ];
    }

    /**
     * Enregistre les paramètres de rapport
     */
    public function register_report_settings() {
        register_setting(
            'pdf_builder_report_settings',
            self::OPTION_REPORT_SETTINGS,
            [$this, 'sanitize_report_settings']
        );
    }

    /**
     * Nettoie les paramètres de rapport
     */
    public function sanitize_report_settings($settings) {
        $defaults = $this->get_default_settings();

        return [
            'auto_generate_reports' => isset($settings['auto_generate_reports']),
            'report_frequency' => in_array($settings['report_frequency'], [
                self::PERIOD_DAILY, self::PERIOD_WEEKLY, self::PERIOD_MONTHLY,
                self::PERIOD_QUARTERLY, self::PERIOD_YEARLY
            ]) ? $settings['report_frequency'] : $defaults['report_frequency'],
            'default_format' => in_array($settings['default_format'], [
                self::FORMAT_PDF, self::FORMAT_CSV, self::FORMAT_JSON,
                self::FORMAT_HTML, self::FORMAT_EXCEL
            ]) ? $settings['default_format'] : $defaults['default_format'],
            'email_reports' => isset($settings['email_reports']),
            'report_types' => array_map('boolval', $settings['report_types'] ?? []),
            'data_retention_days' => intval($settings['data_retention_days'] ?? $defaults['data_retention_days']),
            'anonymize_data' => isset($settings['anonymize_data']),
            'include_charts' => isset($settings['include_charts']),
            'cache_reports' => isset($settings['cache_reports']),
            'max_report_age' => intval($settings['max_report_age'] ?? $defaults['max_report_age'])
        ];
    }

    /**
     * Ajoute le menu de reporting
     */
    public function add_reporting_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Rapports et analyses', 'reporting'),
            pdf_builder_translate('Rapports', 'reporting'),
            'manage_options',
            'pdf-builder-reports',
            [$this, 'render_reporting_page']
        );
    }

    /**
     * Rend la page de reporting
     */
    public function render_reporting_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html(pdf_builder_translate('Accès refusé', 'reporting')));
        }

        $settings = $this->report_settings;
        $available_reports = $this->get_available_reports();
        $scheduled_reports = $this->get_scheduled_reports();
        $recent_reports = $this->get_recent_reports(10);

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/reporting-management.php';
    }

    /**
     * Génère un rapport
     */
    public function generate_report($type, $period = null, $format = null, $filters = []) {
        try {
            $period = $period ?: $this->report_settings['report_frequency'];
            $format = $format ?: $this->report_settings['default_format'];

            // Vérifier les permissions
            if (!pdf_builder_user_can('view_analytics')) {
                throw new Exception(pdf_builder_translate('Permissions insuffisantes', 'reporting')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
            }

            // Collecter les données selon le type
            $data = $this->collect_report_data($type, $period, $filters);

            // Générer le rapport
            $report = [
                'id' => wp_generate_password(12, false),
                'type' => $type,
                'period' => $period,
                'format' => $format,
                'generated_at' => time(),
                'generated_by' => get_current_user_id(),
                'data' => $data,
                'filters' => $filters,
                'metadata' => $this->get_report_metadata($type)
            ];

            // Calculer les métriques
            $report['metrics'] = $this->calculate_report_metrics($data, $type);

            // Générer le contenu selon le format
            $content = $this->generate_report_content($report, $format);

            $report['content'] = $content;
            $report['file_size'] = strlen($content);

            // Sauvegarder le rapport
            $this->save_report($report);

            return $report;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Collecte les données pour un rapport
     */
    private function collect_report_data($type, $period, $filters = []) {
        $date_range = $this->get_date_range($period, $filters);

        switch ($type) {
            case self::REPORT_TYPE_USAGE:
                return $this->collect_usage_data($date_range, $filters);

            case self::REPORT_TYPE_PERFORMANCE:
                return $this->collect_performance_data($date_range, $filters);

            case self::REPORT_TYPE_SECURITY:
                return $this->collect_security_data($date_range, $filters);

            case self::REPORT_TYPE_FINANCIAL:
                return $this->collect_financial_data($date_range, $filters);

            case self::REPORT_TYPE_USER_ACTIVITY:
                return $this->collect_user_activity_data($date_range, $filters);

            case self::REPORT_TYPE_SYSTEM_HEALTH:
                return $this->collect_system_health_data($date_range, $filters);

            default:
                throw new Exception(pdf_builder_translate('Type de rapport inconnu', 'reporting')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }
    }

    /**
     * Collecte les données d'utilisation
     */
    private function collect_usage_data($date_range, $filters) {
        if (!class_exists('PDF_Builder_Analytics_Manager')) {
            return [];
        }

        $analytics = PDF_Builder_Analytics_Manager::get_instance();

        return [
            'total_pdfs_generated' => $analytics->get_total_pdfs_generated($date_range),
            'pdfs_by_template' => $analytics->get_pdfs_by_template($date_range),
            'pdfs_by_user' => $analytics->get_pdfs_by_user($date_range),
            'generation_trends' => $analytics->get_generation_trends($date_range),
            'popular_features' => $analytics->get_popular_features($date_range),
            'usage_by_time' => $analytics->get_usage_by_time($date_range),
            'conversion_rates' => $analytics->get_conversion_rates($date_range)
        ];
    }

    /**
     * Collecte les données de performance
     */
    private function collect_performance_data($date_range, $filters) {
        if (!class_exists('PDF_Builder_Performance_Monitor')) {
            return [];
        }

        $performance = PDF_Builder_Performance_Monitor::get_instance();

        return [
            'average_generation_time' => $performance->get_average_generation_time($date_range),
            'peak_usage_times' => $performance->get_peak_usage_times($date_range),
            'error_rates' => $performance->get_error_rates($date_range),
            'memory_usage' => $performance->get_memory_usage_stats($date_range),
            'cache_hit_rates' => $performance->get_cache_hit_rates($date_range),
            'slow_operations' => $performance->get_slow_operations($date_range),
            'uptime_stats' => $performance->get_uptime_stats($date_range)
        ];
    }

    /**
     * Collecte les données de sécurité
     */
    private function collect_security_data($date_range, $filters) {
        if (!class_exists('PDF_Builder_Security_Monitor')) {
            return [];
        }

        $security = PDF_Builder_Security_Monitor::get_instance();

        return [
            'failed_login_attempts' => $security->get_failed_login_attempts($date_range),
            'blocked_ips' => $security->get_blocked_ips($date_range),
            'security_incidents' => $security->get_security_incidents($date_range),
            'vulnerability_scans' => $security->get_vulnerability_scans($date_range),
            'suspicious_activities' => $security->get_suspicious_activities($date_range),
            'security_score_trend' => $security->get_security_score_trend($date_range)
        ];
    }

    /**
     * Collecte les données financières
     */
    private function collect_financial_data($date_range, $filters) {
        // Données financières basées sur l'utilisation et les licences
        $usage_data = $this->collect_usage_data($date_range, $filters);

        return [
            'estimated_savings' => $this->calculate_estimated_savings($usage_data),
            'license_utilization' => $this->get_license_utilization($date_range),
            'cost_per_pdf' => $this->calculate_cost_per_pdf($usage_data),
            'roi_metrics' => $this->calculate_roi_metrics($usage_data),
            'usage_forecast' => $this->forecast_usage($usage_data)
        ];
    }

    /**
     * Collecte les données d'activité utilisateur
     */
    private function collect_user_activity_data($date_range, $filters) {
        if (!class_exists('PDF_Builder_Analytics_Manager')) {
            return [];
        }

        $analytics = PDF_Builder_Analytics_Manager::get_instance();

        return [
            'active_users' => $analytics->get_active_users($date_range),
            'user_engagement' => $analytics->get_user_engagement($date_range),
            'feature_adoption' => $analytics->get_feature_adoption($date_range),
            'user_segments' => $analytics->get_user_segments($date_range),
            'retention_rates' => $analytics->get_retention_rates($date_range),
            'user_journey' => $analytics->get_user_journey($date_range)
        ];
    }

    /**
     * Collecte les données de santé système
     */
    private function collect_system_health_data($date_range, $filters) {
        if (!class_exists('PDF_Builder_Health_Monitor')) {
            return [];
        }

        $health = PDF_Builder_Health_Monitor::get_instance();

        return [
            'system_uptime' => $health->get_system_uptime($date_range),
            'resource_usage' => $health->get_resource_usage($date_range),
            'error_rates' => $health->get_error_rates($date_range),
            'performance_metrics' => $health->get_performance_metrics($date_range),
            'backup_status' => $health->get_backup_status(),
            'update_status' => $health->get_update_status(),
            'integration_health' => $health->get_integration_health()
        ];
    }

    /**
     * Calcule les métriques du rapport
     */
    private function calculate_report_metrics($data, $type) {
        $metrics = [];

        switch ($type) {
            case self::REPORT_TYPE_USAGE:
                $metrics = [
                    'total_pdfs' => $data['total_pdfs_generated'] ?? 0,
                    'avg_daily_pdfs' => isset($data['total_pdfs_generated']) ? round(($data['total_pdfs_generated'] ?? 0) / 30) : 0,
                    'most_used_template' => $this->find_most_used($data['pdfs_by_template'] ?? []),
                    'growth_rate' => $this->calculate_growth_rate($data['generation_trends'] ?? [])
                ];
                break;

            case self::REPORT_TYPE_PERFORMANCE:
                $metrics = [
                    'avg_response_time' => $data['average_generation_time'] ?? 0,
                    'error_rate' => $this->calculate_error_rate($data['error_rates'] ?? []),
                    'cache_efficiency' => $data['cache_hit_rates']['average'] ?? 0,
                    'uptime_percentage' => $data['uptime_stats']['percentage'] ?? 100
                ];
                break;

            case self::REPORT_TYPE_SECURITY:
                $metrics = [
                    'total_incidents' => count($data['security_incidents'] ?? []),
                    'blocked_attempts' => array_sum(array_column($data['failed_login_attempts'] ?? [], 'count')),
                    'security_score' => $this->calculate_security_score($data),
                    'risk_level' => $this->assess_risk_level($data)
                ];
                break;
        }

        return $metrics;
    }

    /**
     * Génère le contenu du rapport selon le format
     */
    private function generate_report_content($report, $format) {
        switch ($format) {
            case self::FORMAT_PDF:
                return $this->generate_pdf_report($report);

            case self::FORMAT_CSV:
                return $this->generate_csv_report($report);

            case self::FORMAT_JSON:
                return wp_json_encode($report);

            case self::FORMAT_HTML:
                return $this->generate_html_report($report);

            case self::FORMAT_EXCEL:
                return $this->generate_csv_report($report); // Excel format uses CSV

            default:
                throw new Exception(pdf_builder_translate('Format non supporté', 'reporting')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }
    }

    /**
     * Génère un rapport PDF
     */
    private function generate_pdf_report($report) {
        if (!class_exists('TCPDF')) {
            throw new Exception(pdf_builder_translate('TCPDF non disponible', 'reporting')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        /** @var \TCPDF $pdf */
        $pdf = new TCPDF();
        /** @phpstan-ignore-next-line TCPDF stubs defined in lib/pdf-builder-stubs.php */
        $pdf->SetCreator('PDF Builder Pro');
        $pdf->SetTitle($this->get_report_title($report['type']));

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // En-tête
        $pdf->Cell(0, 10, $this->get_report_title($report['type']), 0, 1, 'C');
        $pdf->Cell(0, 10, sprintf(pdf_builder_translate('Période : %s', 'reporting'), $this->format_period($report['period'])), 0, 1);
        $pdf->Cell(0, 10, sprintf(pdf_builder_translate('Généré le : %s', 'reporting'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $report['generated_at'])), 0, 1);

        $pdf->Ln(10);

        // Métriques
        if (!empty($report['metrics'])) {
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, pdf_builder_translate('Métriques principales', 'reporting'), 0, 1);
            $pdf->SetFont('helvetica', '', 12);

            foreach ($report['metrics'] as $key => $value) {
                $label = $this->get_metric_label($key);
                $pdf->Cell(0, 8, "$label: $value", 0, 1);
            }

            $pdf->Ln(5);
        }

        // Graphiques si activés
        if ($this->report_settings['include_charts']) {
            $this->add_charts_to_pdf($pdf, $report);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Génère un rapport CSV
     */
    private function generate_csv_report($report) {
        $output = fopen('php://temp', 'r+');

        // En-tête
        fputcsv($output, ['Metric', 'Value']);

        // Métriques
        foreach ($report['metrics'] as $key => $value) {
            fputcsv($output, [$this->get_metric_label($key), $value]);
        }

        // Données détaillées
        fputcsv($output, []); // Ligne vide
        fputcsv($output, ['Detailed Data']);

        $this->add_data_to_csv($output, $report['data']);

        rewind($output);
        $csv_content = stream_get_contents($output);
        fclose($output);

        return $csv_content;
    }

    /**
     * Génère un rapport HTML
     */
    private function generate_html_report($report) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php echo esc_html($this->get_report_title($report['type'])); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .metrics { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .metric { margin-bottom: 10px; }
                .charts { margin-top: 30px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?php echo esc_html($this->get_report_title($report['type'])); ?></h1>
                <p><?php printf(pdf_builder_translate('Période : %s', 'reporting'), esc_html($this->format_period($report['period']))); ?></p>
                <p><?php printf(pdf_builder_translate('Généré le : %s', 'reporting'), esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $report['generated_at']))); ?></p>
            </div>

            <?php if (!empty($report['metrics'])): ?>
            <div class="metrics">
                <h2><?php echo esc_html(pdf_builder_translate('Métriques principales', 'reporting')); ?></h2>
                <?php foreach ($report['metrics'] as $key => $value): ?>
                <div class="metric">
                    <strong><?php echo esc_html($this->get_metric_label($key)); ?>:</strong> <?php echo esc_html($value); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="data">
                <h2><?php echo esc_html(pdf_builder_translate('Données détaillées', 'reporting')); ?></h2>
                <?php $this->render_data_tables($report['data']); ?>
            </div>

            <?php if ($this->report_settings['include_charts']): ?>
            <div class="charts">
                <h2><?php echo esc_html(pdf_builder_translate('Graphiques', 'reporting')); ?></h2>
                <!-- Graphiques seraient générés ici avec une bibliothèque comme Chart.js -->
                <p><?php echo esc_html(pdf_builder_translate('Graphiques disponibles dans la version PDF', 'reporting')); ?></p>
            </div>
            <?php endif; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Sauvegarde un rapport
     */
    private function save_report($report) {
        $report_id = $report['id'];

        if (!isset($this->report_data[$report_id])) {
            $this->report_data[$report_id] = [];
        }

        $this->report_data[$report_id] = $report;
        update_option(self::OPTION_REPORT_DATA, $this->report_data);
    }

    /**
     * Obtient les rapports disponibles
     */
    public function get_available_reports() {
        return [
            self::REPORT_TYPE_USAGE => [
                'name' => pdf_builder_translate('Rapport d\'utilisation', 'reporting'),
                'description' => pdf_builder_translate('Statistiques d\'utilisation et génération de PDFs', 'reporting'),
                'icon' => 'chart-bar'
            ],
            self::REPORT_TYPE_PERFORMANCE => [
                'name' => pdf_builder_translate('Rapport de performance', 'reporting'),
                'description' => pdf_builder_translate('Métriques de performance et optimisation', 'reporting'),
                'icon' => 'tachometer-alt'
            ],
            self::REPORT_TYPE_SECURITY => [
                'name' => pdf_builder_translate('Rapport de sécurité', 'reporting'),
                'description' => pdf_builder_translate('Incidents de sécurité et menaces', 'reporting'),
                'icon' => 'shield-alt'
            ],
            self::REPORT_TYPE_FINANCIAL => [
                'name' => pdf_builder_translate('Rapport financier', 'reporting'),
                'description' => pdf_builder_translate('ROI et métriques financières', 'reporting'),
                'icon' => 'dollar-sign'
            ],
            self::REPORT_TYPE_USER_ACTIVITY => [
                'name' => pdf_builder_translate('Activité utilisateurs', 'reporting'),
                'description' => pdf_builder_translate('Comportement et engagement des utilisateurs', 'reporting'),
                'icon' => 'users'
            ],
            self::REPORT_TYPE_SYSTEM_HEALTH => [
                'name' => pdf_builder_translate('Santé système', 'reporting'),
                'description' => pdf_builder_translate('État général du système', 'reporting'),
                'icon' => 'heartbeat'
            ]
        ];
    }

    /**
     * Obtient les rapports récents
     */
    public function get_recent_reports($limit = 10) {
        $reports = [];

        foreach ($this->report_data as $report_id => $report) {
            $reports[] = $report;
        }

        // Trier par date de génération (plus récent en premier)
        usort($reports, function($a, $b) {
            return $b['generated_at'] - $a['generated_at'];
        });

        return array_slice($reports, 0, $limit);
    }

    /**
     * Programme un rapport
     */
    public function schedule_report($type, $frequency, $recipients = [], $format = null) {
        $schedule_id = wp_generate_password(12, false);

        $schedule = [
            'id' => $schedule_id,
            'type' => $type,
            'frequency' => $frequency,
            'recipients' => $recipients,
            'format' => $format ?: $this->report_settings['default_format'],
            'created_at' => time(),
            'next_run' => $this->calculate_next_run($frequency),
            'active' => true
        ];

        $this->report_schedule[$schedule_id] = $schedule;
        update_option(self::OPTION_REPORT_SCHEDULE, $this->report_schedule);

        // Programmer la génération
        $this->schedule_report_generation($schedule);

        return $schedule;
    }

    /**
     * Génère les rapports programmés
     */
    public function generate_scheduled_reports() {
        foreach ($this->report_schedule as $schedule_id => $schedule) {
            if (!$schedule['active']) {
                continue;
            }

            if (time() >= $schedule['next_run']) {
                try {
                    $report = $this->generate_report(
                        $schedule['type'],
                        $schedule['frequency'],
                        $schedule['format']
                    );

                    // Envoyer par email si configuré
                    if (!empty($schedule['recipients']) && $this->report_settings['email_reports']) {
                        $this->email_report($report, $schedule['recipients']);
                    }

                    // Mettre à jour la prochaine exécution
                    $this->report_schedule[$schedule_id]['next_run'] = $this->calculate_next_run($schedule['frequency']);
                    $this->report_schedule[$schedule_id]['last_run'] = time();

                } catch (Exception $e) {
                    // Logger l'erreur
                    error_log('Error generating scheduled report: ' . $e->getMessage());
                }
            }
        }

        update_option(self::OPTION_REPORT_SCHEDULE, $this->report_schedule);
    }

    /**
     * Met à jour les données de rapport
     */
    public function update_report_data() {
        // Mettre à jour les métriques en temps réel
        if (class_exists('PDF_Builder_Analytics_Manager')) {
            $analytics = PDF_Builder_Analytics_Manager::get_instance();
            $analytics->update_realtime_metrics();
        }

        if (class_exists('PDF_Builder_Performance_Monitor')) {
            $performance = PDF_Builder_Performance_Monitor::get_instance();
            $performance->update_performance_metrics();
        }
    }

    /**
     * Exporte un rapport
     */
    public function export_report($report_id, $format = null) {
        if (!isset($this->report_data[$report_id])) {
            throw new Exception(pdf_builder_translate('Rapport introuvable', 'reporting')); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }

        $report = $this->report_data[$report_id];
        $format = $format ?: $report['format'];

        // Régénérer le contenu si nécessaire
        if ($format !== $report['format'] || $this->is_report_stale($report)) {
            $report['content'] = $this->generate_report_content($report, $format);
            $report['format'] = $format;
            $this->save_report($report);
        }

        return $report;
    }

    /**
     * Obtient les rapports programmés
     */
    public function get_scheduled_reports() {
        return array_values($this->report_schedule);
    }

    /**
     * Nettoie les anciens rapports
     */
    public function cleanup_old_reports() {
        $retention_days = $this->report_settings['data_retention_days'];
        $cutoff_time = time() - ($retention_days * DAY_IN_SECONDS);

        foreach ($this->report_data as $report_id => $report) {
            if ($report['generated_at'] < $cutoff_time) {
                unset($this->report_data[$report_id]);
            }
        }

        update_option(self::OPTION_REPORT_DATA, $this->report_data);
    }

    /**
     * Vérifie si un rapport est périmé
     */
    private function is_report_stale($report) {
        if (!$this->report_settings['cache_reports']) {
            return true;
        }

        return (time() - $report['generated_at']) > $this->report_settings['max_report_age'];
    }

    /**
     * Obtient la plage de dates
     */
    private function get_date_range($period, $filters = []) {
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            return [
                'start' => strtotime($filters['start_date']),
                'end' => strtotime($filters['end_date'])
            ];
        }

        $end = time();
        $start = $end;

        switch ($period) {
            case self::PERIOD_DAILY:
                $start = strtotime('-1 day', $end);
                break;
            case self::PERIOD_WEEKLY:
                $start = strtotime('-1 week', $end);
                break;
            case self::PERIOD_MONTHLY:
                $start = strtotime('-1 month', $end);
                break;
            case self::PERIOD_QUARTERLY:
                $start = strtotime('-3 months', $end);
                break;
            case self::PERIOD_YEARLY:
                $start = strtotime('-1 year', $end);
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Calcule la prochaine exécution
     */
    private function calculate_next_run($frequency) {
        switch ($frequency) {
            case self::PERIOD_DAILY:
                return strtotime('+1 day');
            case self::PERIOD_WEEKLY:
                return strtotime('+1 week');
            case self::PERIOD_MONTHLY:
                return strtotime('+1 month');
            case self::PERIOD_QUARTERLY:
                return strtotime('+3 months');
            case self::PERIOD_YEARLY:
                return strtotime('+1 year');
            default:
                return strtotime('+1 week');
        }
    }

    /**
     * Programmes la génération d'un rapport
     */
    private function schedule_report_generation($schedule) {
        $hook = 'pdf_builder_generate_scheduled_report_' . $schedule['id'];

        if (!wp_next_scheduled($hook)) {
            wp_schedule_single_event($schedule['next_run'], $hook, [$schedule['id']]);
        }

        add_action($hook, function($schedule_id) {
            $this->generate_scheduled_report($schedule_id);
        });
    }

    /**
     * Génère un rapport programmé
     */
    private function generate_scheduled_report($schedule_id) {
        if (!isset($this->report_schedule[$schedule_id])) {
            return;
        }

        $schedule = $this->report_schedule[$schedule_id];

        try {
            $report = $this->generate_report(
                $schedule['type'],
                $schedule['frequency'],
                $schedule['format']
            );

            // Envoyer par email
            if (!empty($schedule['recipients'])) {
                $this->email_report($report, $schedule['recipients']);
            }

        } catch (Exception $e) {
            // Logger l'erreur
            error_log('Error generating scheduled report: ' . $e->getMessage());
        }
    }

    /**
     * Envoie un rapport par email
     */
    private function email_report($report, $recipients) {
        $subject = sprintf(
            pdf_builder_translate('Rapport PDF Builder Pro : %s', 'reporting'),
            $this->get_report_title($report['type'])
        );

        $message = sprintf(
            pdf_builder_translate('Veuillez trouver ci-joint le rapport %s généré le %s.', 'reporting'),
            $this->get_report_title($report['type']),
            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $report['generated_at'])
        );

        // Attacher le fichier
        $attachments = [];
        if (!empty($report['content'])) {
            $filename = 'report_' . $report['id'] . '.' . $this->get_file_extension($report['format']);
            $temp_file = wp_tempnam($filename);
            file_put_contents($temp_file, $report['content']);
            $attachments[] = $temp_file;
        }

        foreach ($recipients as $recipient) {
            wp_mail($recipient, $subject, $message, [], $attachments);
        }

        // Nettoyer les fichiers temporaires
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                unlink($attachment);
            }
        }
    }

    /**
     * Méthodes utilitaires
     */
    private function get_report_title($type) {
        $titles = [
            self::REPORT_TYPE_USAGE => pdf_builder_translate('Rapport d\'utilisation', 'reporting'),
            self::REPORT_TYPE_PERFORMANCE => pdf_builder_translate('Rapport de performance', 'reporting'),
            self::REPORT_TYPE_SECURITY => pdf_builder_translate('Rapport de sécurité', 'reporting'),
            self::REPORT_TYPE_FINANCIAL => pdf_builder_translate('Rapport financier', 'reporting'),
            self::REPORT_TYPE_USER_ACTIVITY => pdf_builder_translate('Rapport d\'activité utilisateur', 'reporting'),
            self::REPORT_TYPE_SYSTEM_HEALTH => pdf_builder_translate('Rapport de santé système', 'reporting')
        ];

        return $titles[$type] ?? pdf_builder_translate('Rapport', 'reporting');
    }

    private function format_period($period) {
        $periods = [
            self::PERIOD_DAILY => pdf_builder_translate('Quotidien', 'reporting'),
            self::PERIOD_WEEKLY => pdf_builder_translate('Hebdomadaire', 'reporting'),
            self::PERIOD_MONTHLY => pdf_builder_translate('Mensuel', 'reporting'),
            self::PERIOD_QUARTERLY => pdf_builder_translate('Trimestriel', 'reporting'),
            self::PERIOD_YEARLY => pdf_builder_translate('Annuel', 'reporting')
        ];

        return $periods[$period] ?? $period;
    }

    private function get_metric_label($key) {
        $labels = [
            'total_pdfs' => pdf_builder_translate('Total PDFs', 'reporting'),
            'avg_daily_pdfs' => pdf_builder_translate('Moyenne quotidienne', 'reporting'),
            'most_used_template' => pdf_builder_translate('Modèle le plus utilisé', 'reporting'),
            'growth_rate' => pdf_builder_translate('Taux de croissance', 'reporting'),
            'avg_response_time' => pdf_builder_translate('Temps de réponse moyen', 'reporting'),
            'error_rate' => pdf_builder_translate('Taux d\'erreur', 'reporting'),
            'cache_efficiency' => pdf_builder_translate('Efficacité du cache', 'reporting'),
            'uptime_percentage' => pdf_builder_translate('Pourcentage de disponibilité', 'reporting'),
            'total_incidents' => pdf_builder_translate('Total incidents', 'reporting'),
            'blocked_attempts' => pdf_builder_translate('Tentatives bloquées', 'reporting'),
            'security_score' => pdf_builder_translate('Score de sécurité', 'reporting'),
            'risk_level' => pdf_builder_translate('Niveau de risque', 'reporting')
        ];

        return $labels[$key] ?? $key;
    }

    private function get_file_extension($format) {
        $extensions = [
            self::FORMAT_PDF => 'pdf',
            self::FORMAT_CSV => 'csv',
            self::FORMAT_JSON => 'json',
            self::FORMAT_HTML => 'html',
            self::FORMAT_EXCEL => 'xlsx'
        ];

        return $extensions[$format] ?? 'txt';
    }

    /**
     * Méthodes de calcul des métriques
     */
    private function find_most_used($data) {
        if (empty($data)) return 'N/A';

        $max_count = 0;
        $most_used = '';

        foreach ($data as $item => $count) {
            if ($count > $max_count) {
                $max_count = $count;
                $most_used = $item;
            }
        }

        return $most_used;
    }

    private function calculate_growth_rate($trends) {
        if (count($trends) < 2) return '0%';

        $first = reset($trends);
        $last = end($trends);

        if ($first == 0) return 'N/A';

        $rate = (($last - $first) / $first) * 100;
        return round($rate, 1) . '%';
    }

    private function calculate_error_rate($error_data) {
        if (empty($error_data)) return '0%';

        $total_errors = array_sum(array_column($error_data, 'count'));
        $total_requests = array_sum(array_column($error_data, 'total'));

        if ($total_requests == 0) return '0%';

        return round(($total_errors / $total_requests) * 100, 2) . '%';
    }

    private function calculate_security_score($data) {
        $score = 100;

        // Réduire le score selon les incidents
        $incidents = count($data['security_incidents'] ?? []);
        $score -= min($incidents * 5, 40);

        // Réduire selon les tentatives bloquées
        $blocked = array_sum(array_column($data['failed_login_attempts'] ?? [], 'count'));
        $score -= min($blocked * 0.1, 20);

        return max(0, round($score));
    }

    private function assess_risk_level($data) {
        $score = $this->calculate_security_score($data);

        if ($score >= 80) return pdf_builder_translate('Faible', 'reporting');
        if ($score >= 60) return pdf_builder_translate('Moyen', 'reporting');
        if ($score >= 40) return pdf_builder_translate('Élevé', 'reporting');
        return pdf_builder_translate('Critique', 'reporting');
    }

    /**
     * Ajoute des widgets au tableau de bord
     */
    public function add_dashboard_widgets($widgets) {
        $widgets['pdf_builder_reports'] = [
            'title' => pdf_builder_translate('Rapports PDF Builder', 'reporting'),
            'callback' => [$this, 'render_dashboard_widget'],
            'context' => 'normal',
            'priority' => 'high'
        ];

        return $widgets;
    }

    /**
     * Rend le widget du tableau de bord
     */
    public function render_dashboard_widget() {
        $recent_reports = $this->get_recent_reports(5);
        $available_reports = $this->get_available_reports();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/dashboard-reports-widget.php';
    }

    /**
     * Affiche les messages de rapport (succès/erreur)
     */
    public function display_report_notices() {
        // Message de génération de rapport
        if (isset($_GET['report_generated'])) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html(pdf_builder_translate('Rapport généré avec succès.', 'reporting')) . '</p>';
            echo '</div>';
        }

        // Message d'erreur de rapport
        if (isset($_GET['report_error'])) {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html(pdf_builder_translate('Erreur lors de la génération du rapport.', 'reporting')) . '</p>';
            echo '</div>';
        }
    }

    /**
     * AJAX - Génère un rapport
     */
    public function generate_report_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $type = sanitize_key($_POST['type'] ?? '');
            $period = sanitize_key($_POST['period'] ?? '');
            $format = sanitize_key($_POST['format'] ?? '');

            if (empty($type)) {
                wp_send_json_error(['message' => 'Type de rapport manquant']);
                return;
            }

            $report = $this->generate_report($type, $period, $format);

            wp_send_json_success([
                'message' => pdf_builder_translate('Rapport généré avec succès', 'reporting'),
                'report' => $report
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exporte un rapport
     */
    public function export_report_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $report_id = sanitize_key($_POST['report_id'] ?? '');
            $format = sanitize_key($_POST['format'] ?? '');

            if (empty($report_id)) {
                wp_send_json_error(['message' => 'ID de rapport manquant']);
                return;
            }

            $report = $this->export_report($report_id, $format);

            wp_send_json_success([
                'report' => $report,
                'download_url' => $this->generate_download_url($report)
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Programmes un rapport
     */
    public function schedule_report_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $type = sanitize_key($_POST['type'] ?? '');
            $frequency = sanitize_key($_POST['frequency'] ?? '');
            $recipients = array_map('sanitize_email', $_POST['recipients'] ?? []);
            $format = sanitize_key($_POST['format'] ?? '');

            if (empty($type) || empty($frequency)) {
                wp_send_json_error(['message' => 'Paramètres manquants']);
                return;
            }

            $schedule = $this->schedule_report($type, $frequency, $recipients, $format);

            wp_send_json_success([
                'message' => pdf_builder_translate('Rapport programmé avec succès', 'reporting'),
                'schedule' => $schedule
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les données de rapport
     */
    public function get_report_data_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $report_id = sanitize_key($_POST['report_id'] ?? '');

            if (empty($report_id) || !isset($this->report_data[$report_id])) {
                wp_send_json_error(['message' => 'Rapport introuvable']);
                return;
            }

            $report = $this->report_data[$report_id];

            wp_send_json_success([
                'report' => $report
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Sauvegarde les paramètres de rapport
     */
    public function save_report_settings_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $settings = $_POST['settings'] ?? [];

            $sanitized_settings = $this->sanitize_report_settings($settings);
            update_option(self::OPTION_REPORT_SETTINGS, $sanitized_settings);

            $this->report_settings = $sanitized_settings;

            wp_send_json_success([
                'message' => pdf_builder_translate('Paramètres sauvegardés', 'reporting')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Génère une URL de téléchargement
     */
    private function generate_download_url($report) {
        $token = wp_create_nonce('pdf_builder_download_report_' . $report['id']);

        return add_query_arg([
            'pdf_builder_download' => 'report',
            'report_id' => $report['id'],
            'token' => $token
        ], admin_url('admin-ajax.php'));
    }

    /**
     * Méthodes privées supplémentaires pour les rapports
     */
    private function get_report_metadata($type) {
        $metadata = [
            self::REPORT_TYPE_USAGE => [
                'description' => 'Analyse détaillée de l\'utilisation du plugin',
                'data_points' => ['pdfs_generated', 'templates_used', 'user_activity'],
                'refresh_interval' => 'daily'
            ],
            self::REPORT_TYPE_PERFORMANCE => [
                'description' => 'Métriques de performance et optimisation',
                'data_points' => ['response_times', 'error_rates', 'resource_usage'],
                'refresh_interval' => 'hourly'
            ],
            self::REPORT_TYPE_SECURITY => [
                'description' => 'Rapport de sécurité et incidents',
                'data_points' => ['incidents', 'blocked_attempts', 'vulnerabilities'],
                'refresh_interval' => 'daily'
            ]
        ];

        return $metadata[$type] ?? [];
    }

    private function calculate_estimated_savings($usage_data) {
        // Calcul simplifié des économies (exemple)
        $pdfs_generated = $usage_data['total_pdfs_generated'] ?? 0;
        $avg_cost_per_pdf_external = 0.50; // Coût estimé pour un service externe

        return $pdfs_generated * $avg_cost_per_pdf_external;
    }

    private function get_license_utilization($date_range) {
        // Utilisation de la licence (simplifié)
        return [
            'pdfs_used' => 1250,
            'pdfs_limit' => 2000,
            'users_active' => 8,
            'users_limit' => 10
        ];
    }

    private function calculate_cost_per_pdf($usage_data) {
        $total_pdfs = $usage_data['total_pdfs_generated'] ?? 0;
        if ($total_pdfs === 0) return 0;

        // Coût basé sur la licence
        $license_cost = 99; // Coût mensuel de la licence
        return round($license_cost / $total_pdfs, 4);
    }

    private function calculate_roi_metrics($usage_data) {
        $savings = $this->calculate_estimated_savings($usage_data);
        $investment = 99; // Coût de la licence

        return [
            'savings' => $savings,
            'investment' => $investment,
            'roi_percentage' => $investment > 0 ? round(($savings / $investment) * 100) : 0,
            'break_even_months' => $investment > 0 ? ceil($investment / ($savings / 12)) : 0
        ];
    }

    private function forecast_usage($usage_data) {
        // Prévision simple basée sur les tendances
        $current_usage = $usage_data['total_pdfs_generated'] ?? 0;
        $growth_rate = 0.1; // 10% de croissance mensuelle estimée

        return [
            'next_month' => round($current_usage * (1 + $growth_rate)),
            'next_quarter' => round($current_usage * pow(1 + $growth_rate, 3)),
            'next_year' => round($current_usage * pow(1 + $growth_rate, 12))
        ];
    }

    private function add_charts_to_pdf($pdf, $report) {
        // Cette méthode ajouterait des graphiques au PDF
        // Nécessiterait une bibliothèque de graphiques comme TCPDF Charts
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, pdf_builder_translate('Graphiques disponibles dans la version complète', 'reporting'), 0, 1, 'C');
    }

    private function add_data_to_csv($output, $data) {
        // Ajouter les données détaillées au CSV
        foreach ($data as $section => $section_data) {
            fputcsv($output, [$section]);

            if (is_array($section_data)) {
                foreach ($section_data as $key => $value) {
                    if (is_array($value)) {
                        fputcsv($output, [$key, json_encode($value)]);
                    } else {
                        fputcsv($output, [$key, $value]);
                    }
                }
            }

            fputcsv($output, []); // Ligne vide
        }
    }

    private function render_data_tables($data) {
        foreach ($data as $section => $section_data) {
            if (!is_array($section_data) || empty($section_data)) continue;

            echo '<h3>' . esc_html(ucfirst(str_replace('_', ' ', $section))) . '</h3>';
            echo '<table>';
            echo '<thead><tr><th>' . esc_html(pdf_builder_translate('Clé', 'reporting')) . '</th><th>' . esc_html(pdf_builder_translate('Valeur', 'reporting')) . '</th></tr></thead>';
            echo '<tbody>';

            foreach ($section_data as $key => $value) {
                echo '<tr>';
                echo '<td>' . esc_html($key) . '</td>';
                echo '<td>' . esc_html(is_array($value) ? json_encode($value) : $value) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        }
    }
}

// Fonctions globales
function pdf_builder_reporting() {
    return PDF_Builder_Advanced_Reporting::get_instance();
}

function pdf_builder_generate_report($type, $period = null, $format = null, $filters = []) {
    return PDF_Builder_Advanced_Reporting::get_instance()->generate_report($type, $period, $format, $filters);
}

function pdf_builder_get_available_reports() {
    return PDF_Builder_Advanced_Reporting::get_instance()->get_available_reports();
}

function pdf_builder_get_recent_reports($limit = 10) {
    return PDF_Builder_Advanced_Reporting::get_instance()->get_recent_reports($limit);
}

function pdf_builder_schedule_report($type, $frequency, $recipients = [], $format = null) {
    return PDF_Builder_Advanced_Reporting::get_instance()->schedule_report($type, $frequency, $recipients, $format);
}

function pdf_builder_export_report($report_id, $format = null) {
    return PDF_Builder_Advanced_Reporting::get_instance()->export_report($report_id, $format);
}

// Initialiser le système de reporting avancé
add_action('plugins_loaded', function() {
    PDF_Builder_Advanced_Reporting::get_instance();
});



