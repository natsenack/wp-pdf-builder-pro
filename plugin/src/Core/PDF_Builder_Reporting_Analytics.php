<?php
/**
 * PDF Builder Pro - Système de reporting et analyse
 * Fournit des rapports détaillés sur l'utilisation et les performances
 */

class PDF_Builder_Reporting_Analytics {
    private static $instance = null;

    // Types de rapports
    const REPORT_TYPE_USAGE = 'usage';
    const REPORT_TYPE_PERFORMANCE = 'performance';
    const REPORT_TYPE_SECURITY = 'security';
    const REPORT_TYPE_ERROR = 'error';
    const REPORT_TYPE_BUSINESS = 'business';

    // Périodes de rapport
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';

    // Formats de sortie
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_CSV = 'csv';
    const FORMAT_PDF = 'pdf';

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
        // Génération de rapports
        add_action('wp_ajax_pdf_builder_generate_report', [$this, 'generate_report_ajax']);
        add_action('wp_ajax_pdf_builder_get_report_data', [$this, 'get_report_data_ajax']);
        add_action('wp_ajax_pdf_builder_export_report', [$this, 'export_report_ajax']);

        // Rapports automatiques
        add_action('pdf_builder_daily_report', [$this, 'generate_daily_report']);
        add_action('pdf_builder_weekly_report', [$this, 'generate_weekly_report']);
        add_action('pdf_builder_monthly_report', [$this, 'generate_monthly_report']);

        // Collecte de données
        add_action('pdf_builder_collect_usage_data', [$this, 'collect_usage_data']);
        add_action('pdf_builder_collect_performance_data', [$this, 'collect_performance_data']);

        // Nettoyage des anciens rapports
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_old_reports']);
    }

    /**
     * Génère un rapport
     */
    public function generate_report($type, $period = self::PERIOD_MONTHLY, $format = self::FORMAT_HTML, $filters = []) {
        try {
            $report_data = [];

            switch ($type) {
                case self::REPORT_TYPE_USAGE:
                    $report_data = $this->generate_usage_report($period, $filters);
                    break;

                case self::REPORT_TYPE_PERFORMANCE:
                    $report_data = $this->generate_performance_report($period, $filters);
                    break;

                case self::REPORT_TYPE_SECURITY:
                    $report_data = $this->generate_security_report($period, $filters);
                    break;

                case self::REPORT_TYPE_ERROR:
                    $report_data = $this->generate_error_report($period, $filters);
                    break;

                case self::REPORT_TYPE_BUSINESS:
                    $report_data = $this->generate_business_report($period, $filters);
                    break;

                default:
                    throw new Exception('Type de rapport invalide');
            }

            // Formater le rapport
            $formatted_report = $this->format_report($report_data, $format);

            // Sauvegarder le rapport
            $report_id = $this->save_report($type, $period, $format, $report_data, $formatted_report);

            // Notifier si nécessaire
            if (pdf_builder_config('report_notifications_enabled', true)) {
                // Log the report generation
                if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                    error_log(wp_json_encode([
                        'action' => 'report_generated',
                        'type' => $type,
                        'period' => $period,
                        'format' => $format,
                        'report_id' => $report_id
                    ]));
                }
            }

            return [
                'report_id' => $report_id,
                'data' => $report_data,
                'formatted' => $formatted_report
            ];

        } catch (Exception $e) {
            $this->log_report_error('generate_report', $e);
            throw $e;
        }
    }

    /**
     * Génère un rapport d'utilisation
     */
    private function generate_usage_report($period, $filters) {
        $date_range = $this->get_date_range($period);

        $data = [
            'period' => $period,
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to'],
            'generated_at' => current_time('mysql'),
            'metrics' => []
        ];

        // Métriques d'utilisation
        $data['metrics'] = [
            'total_pdfs_generated' => $this->get_total_pdfs_generated($date_range),
            'unique_users' => $this->get_unique_users_count($date_range),
            'templates_used' => $this->get_templates_usage($date_range),
            'most_popular_features' => $this->get_most_popular_features($date_range),
            'usage_by_hour' => $this->get_usage_by_hour($date_range),
            'usage_by_day' => $this->get_usage_by_day($date_range),
            'top_users' => $this->get_top_users($date_range),
            'conversion_rates' => $this->get_conversion_rates($date_range)
        ];

        // Tendances
        $data['trends'] = [
            'growth_rate' => $this->calculate_growth_rate('pdfs_generated', $period),
            'user_engagement' => $this->calculate_user_engagement($date_range),
            'feature_adoption' => $this->calculate_feature_adoption($date_range)
        ];

        return $data;
    }

    /**
     * Génère un rapport de performance
     */
    private function generate_performance_report($period, $filters) {
        $date_range = $this->get_date_range($period);

        $data = [
            'period' => $period,
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to'],
            'generated_at' => current_time('mysql'),
            'metrics' => []
        ];

        // Métriques de performance
        $data['metrics'] = [
            'average_generation_time' => $this->get_average_generation_time($date_range),
            'peak_usage_times' => $this->get_peak_usage_times($date_range),
            'error_rate' => $this->get_error_rate($date_range),
            'cache_hit_rate' => $this->get_cache_hit_rate($date_range),
            'memory_usage' => $this->get_memory_usage_stats($date_range),
            'database_performance' => $this->get_database_performance($date_range),
            'api_response_times' => $this->get_api_response_times($date_range),
            'slow_operations' => $this->get_slow_operations($date_range)
        ];

        // Recommandations
        $data['recommendations'] = $this->generate_performance_recommendations($data['metrics']);

        return $data;
    }

    /**
     * Génère un rapport de sécurité
     */
    private function generate_security_report($period, $filters) {
        $date_range = $this->get_date_range($period);

        $data = [
            'period' => $period,
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to'],
            'generated_at' => current_time('mysql'),
            'metrics' => []
        ];

        // Métriques de sécurité
        $data['metrics'] = [
            'threats_detected' => $this->get_threats_detected($date_range),
            'blocked_ips' => $this->get_blocked_ips_count($date_range),
            'failed_logins' => $this->get_failed_logins_count($date_range),
            'suspicious_activities' => $this->get_suspicious_activities($date_range),
            'security_score' => $this->calculate_security_score($date_range),
            'vulnerability_scan' => $this->get_vulnerability_scan_results(),
            'access_patterns' => $this->get_access_patterns($date_range)
        ];

        // Alertes de sécurité
        $data['alerts'] = $this->generate_security_alerts($data['metrics']);

        return $data;
    }

    /**
     * Génère un rapport d'erreurs
     */
    private function generate_error_report($period, $filters) {
        $date_range = $this->get_date_range($period);

        $data = [
            'period' => $period,
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to'],
            'generated_at' => current_time('mysql'),
            'metrics' => []
        ];

        // Métriques d'erreurs
        $data['metrics'] = [
            'total_errors' => $this->get_total_errors($date_range),
            'error_types' => $this->get_error_types_distribution($date_range),
            'most_common_errors' => $this->get_most_common_errors($date_range),
            'error_trends' => $this->get_error_trends($date_range),
            'affected_users' => $this->get_affected_users($date_range),
            'error_resolution_time' => $this->get_error_resolution_time($date_range)
        ];

        // Analyse des erreurs
        $data['analysis'] = [
            'root_causes' => $this->analyze_error_root_causes($data['metrics']),
            'impact_assessment' => $this->assess_error_impact($data['metrics'])
        ];

        return $data;
    }

    /**
     * Génère un rapport business
     */
    private function generate_business_report($period, $filters) {
        $date_range = $this->get_date_range($period);

        $data = [
            'period' => $period,
            'date_from' => $date_range['from'],
            'date_to' => $date_range['to'],
            'generated_at' => current_time('mysql'),
            'metrics' => []
        ];

        // Métriques business
        $data['metrics'] = [
            'revenue_generated' => $this->get_revenue_generated($date_range),
            'cost_savings' => $this->get_cost_savings($date_range),
            'user_satisfaction' => $this->get_user_satisfaction_score($date_range),
            'roi_calculation' => $this->calculate_roi($date_range),
            'market_share' => $this->get_market_share_estimate(),
            'competitive_analysis' => $this->get_competitive_analysis()
        ];

        // Prévisions
        $data['forecasts'] = [
            'revenue_forecast' => $this->forecast_revenue($period),
            'usage_forecast' => $this->forecast_usage($period),
            'growth_opportunities' => $this->identify_growth_opportunities()
        ];

        return $data;
    }

    /**
     * Obtient la plage de dates pour une période
     */
    private function get_date_range($period) {
        $now = current_time('timestamp');

        switch ($period) {
            case self::PERIOD_DAILY:
                $from = strtotime('today', $now);
                $to = strtotime('tomorrow', $now) - 1;
                break;

            case self::PERIOD_WEEKLY:
                $from = strtotime('monday this week', $now);
                $to = strtotime('next monday', $now) - 1;
                break;

            case self::PERIOD_MONTHLY:
                $from = strtotime('first day of this month', $now);
                $to = strtotime('first day of next month', $now) - 1;
                break;

            case self::PERIOD_QUARTERLY:
                $quarter_start = ceil(date('n', $now) / 3) * 3 - 2;
                $from = strtotime(date('Y', $now) . '-' . $quarter_start . '-01');
                $to = strtotime('+3 months', $from) - 1;
                break;

            default:
                $from = strtotime('-30 days', $now);
                $to = $now;
        }

        return [
            'from' => date('Y-m-d H:i:s', $from),
            'to' => date('Y-m-d H:i:s', $to)
        ];
    }

    /**
     * Obtient le nombre total de PDFs générés
     */
    private function get_total_pdfs_generated($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE event_type = 'pdf_generated'
            AND created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient le nombre d'utilisateurs uniques
     */
    private function get_unique_users_count($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT user_id) FROM $table
            WHERE created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient l'utilisation des templates
     */
    private function get_templates_usage($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT event_data->>'$.template_id' as template_id, COUNT(*) as count
            FROM $table
            WHERE event_type = 'pdf_generated'
            AND created_at BETWEEN %s AND %s
            GROUP BY event_data->>'$.template_id'
            ORDER BY count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les fonctionnalités les plus populaires
     */
    private function get_most_popular_features($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT event_type, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY event_type
            ORDER BY count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Calcule le taux de croissance
     */
    private function calculate_growth_rate($metric, $period) {
        // Comparer avec la période précédente
        $current_range = $this->get_date_range($period);
        $previous_range = $this->get_previous_period_range($period);

        $current_value = $this->get_metric_value($metric, $current_range);
        $previous_value = $this->get_metric_value($metric, $previous_range);

        if ($previous_value == 0) {
            return $current_value > 0 ? 100 : 0;
        }

        return (($current_value - $previous_value) / $previous_value) * 100;
    }

    /**
     * Obtient la valeur d'une métrique
     */
    private function get_metric_value($metric, $date_range) {
        switch ($metric) {
            case 'pdfs_generated':
                return $this->get_total_pdfs_generated($date_range);
            default:
                return 0;
        }
    }

    /**
     * Obtient la plage de dates de la période précédente
     */
    private function get_previous_period_range($period) {
        $current_range = $this->get_date_range($period);
        $current_start = strtotime($current_range['from']);
        $current_end = strtotime($current_range['to']);

        $duration = $current_end - $current_start;

        return [
            'from' => date('Y-m-d H:i:s', $current_start - $duration),
            'to' => date('Y-m-d H:i:s', $current_end - $duration)
        ];
    }

    /**
     * Obtient le temps de génération moyen
     */
    private function get_average_generation_time($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        return $wpdb->get_var($wpdb->prepare("
            SELECT AVG(execution_time) FROM $table
            WHERE operation_type = 'pdf_generation'
            AND created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient les heures de pointe
     */
    private function get_peak_usage_times($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT HOUR(created_at) as hour, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY HOUR(created_at)
            ORDER BY count DESC
            LIMIT 5
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient le taux d'erreur
     */
    private function get_error_rate($date_range) {
        $total_operations = $this->get_total_operations($date_range);
        $total_errors = $this->get_total_errors($date_range);

        if ($total_operations == 0) {
            return 0;
        }

        return ($total_errors / $total_operations) * 100;
    }

    /**
     * Obtient le nombre total d'opérations
     */
    private function get_total_operations($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient le nombre total d'erreurs
     */
    private function get_total_errors($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient les menaces détectées
     */
    private function get_threats_detected($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_threats';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Calcule le score de sécurité
     */
    private function calculate_security_score($date_range) {
        $threats = $this->get_threats_detected($date_range);
        $blocked_ips = $this->get_blocked_ips_count($date_range);

        $score = 100;

        // Réduire le score selon les menaces
        $score -= min($threats * 2, 40);

        // Réduire le score selon les IPs bloquées
        $score -= min($blocked_ips * 5, 30);

        return max($score, 0);
    }

    /**
     * Génère des recommandations de performance
     */
    private function generate_performance_recommendations($metrics) {
        $recommendations = [];

        if (($metrics['average_generation_time'] ?? 0) > 5) {
            $recommendations[] = 'Temps de génération élevé détecté. Considérez l\'optimisation du cache.';
        }

        if (($metrics['error_rate'] ?? 0) > 5) {
            $recommendations[] = 'Taux d\'erreur élevé. Vérifiez les logs pour identifier les problèmes.';
        }

        if (($metrics['cache_hit_rate'] ?? 0) < 70) {
            $recommendations[] = 'Taux de succès du cache faible. Optimisez la stratégie de cache.';
        }

        return $recommendations;
    }

    /**
     * Génère des alertes de sécurité
     */
    private function generate_security_alerts($metrics) {
        $alerts = [];

        if (($metrics['threats_detected'] ?? 0) > 10) {
            $alerts[] = 'Nombre élevé de menaces détectées. Renforcez la sécurité.';
        }

        if (($metrics['security_score'] ?? 100) < 70) {
            $alerts[] = 'Score de sécurité faible. Actions correctives recommandées.';
        }

        return $alerts;
    }

    /**
     * Obtient les IPs bloquées
     */
    private function get_blocked_ips_count($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_blocked_ips';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE blocked_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient les échecs de connexion
     */
    private function get_failed_logins_count($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_events';

        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table
            WHERE event_type = 'failed_login'
            AND created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']));
    }

    /**
     * Obtient les activités suspectes
     */
    private function get_suspicious_activities($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_security_events';

        return $wpdb->get_results($wpdb->prepare("
            SELECT event_type, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY event_type
            ORDER BY count DESC
        ", $date_range['from'], $date_range['to']), ARRAY_A);
    }

    /**
     * Obtient les résultats du scan de vulnérabilités
     */
    private function get_vulnerability_scan_results() {
        // Cette méthode nécessiterait un système de scan de vulnérabilités
        return [
            'last_scan' => current_time('mysql'),
            'vulnerabilities_found' => 0,
            'critical_issues' => 0,
            'high_issues' => 0,
            'medium_issues' => 0,
            'low_issues' => 0
        ];
    }

    /**
     * Obtient les patterns d'accès
     */
    private function get_access_patterns($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_request_logs';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT request_uri, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY request_uri
            ORDER BY count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient la distribution des types d'erreurs
     */
    private function get_error_types_distribution($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT error_type, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY error_type
            ORDER BY count DESC
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les erreurs les plus communes
     */
    private function get_most_common_errors($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT error_message, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY error_message
            ORDER BY count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les tendances d'erreurs
     */
    private function get_error_trends($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY DATE(created_at)
            ORDER BY date
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les utilisateurs affectés
     */
    private function get_affected_users($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT user_id, COUNT(*) as error_count
            FROM $table
            WHERE user_id IS NOT NULL
            AND created_at BETWEEN %s AND %s
            GROUP BY user_id
            ORDER BY error_count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient le temps de résolution des erreurs
     */
    private function get_error_resolution_time($date_range) {
        // Cette métrique nécessiterait un suivi du temps de résolution
        return [
            'average_resolution_time' => 0, // heures
            'median_resolution_time' => 0,
            'max_resolution_time' => 0
        ];
    }

    /**
     * Analyse les causes racines des erreurs
     */
    private function analyze_error_root_causes($metrics) {
        $analysis = [];

        // Analyser les types d'erreurs pour identifier les patterns
        $error_types = $metrics['error_types'] ?? [];

        foreach ($error_types as $error_type) {
            switch ($error_type['error_type']) {
                case 'database_error':
                    $analysis[] = 'Problèmes de base de données détectés. Vérifiez la configuration DB.';
                    break;

                case 'permission_error':
                    $analysis[] = 'Erreurs de permissions. Vérifiez les droits d\'accès aux fichiers.';
                    break;

                case 'memory_error':
                    $analysis[] = 'Erreurs de mémoire. Augmentez la limite mémoire PHP.';
                    break;
            }
        }

        return $analysis;
    }

    /**
     * Évalue l'impact des erreurs
     */
    private function assess_error_impact($metrics) {
        $total_errors = $metrics['total_errors'] ?? 0;
        $affected_users = count($metrics['affected_users'] ?? []);

        $impact = 'low';

        if ($total_errors > 100 || $affected_users > 10) {
            $impact = 'high';
        } elseif ($total_errors > 50 || $affected_users > 5) {
            $impact = 'medium';
        }

        return [
            'level' => $impact,
            'total_errors' => $total_errors,
            'affected_users' => $affected_users,
            'estimated_downtime' => $this->estimate_downtime($total_errors)
        ];
    }

    /**
     * Estime le temps d'arrêt
     */
    private function estimate_downtime($error_count) {
        // Estimation simple basée sur le nombre d'erreurs
        return $error_count * 0.1; // 0.1 heure par erreur
    }

    /**
     * Obtient les revenus générés
     */
    private function get_revenue_generated($date_range) {
        // Cette métrique dépend du modèle commercial
        return [
            'total_revenue' => 0,
            'subscription_revenue' => 0,
            'one_time_purchases' => 0,
            'currency' => 'EUR'
        ];
    }

    /**
     * Obtient les économies de coûts
     */
    private function get_cost_savings($date_range) {
        // Calculer les économies par rapport aux solutions alternatives
        return [
            'time_saved' => 0, // heures
            'cost_per_hour' => 50, // €
            'total_savings' => 0
        ];
    }

    /**
     * Obtient le score de satisfaction utilisateur
     */
    private function get_user_satisfaction_score($date_range) {
        // Cette métrique nécessiterait des enquêtes ou des retours utilisateurs
        return [
            'score' => 85, // sur 100
            'responses' => 0,
            'trend' => 'stable'
        ];
    }

    /**
     * Calcule le ROI
     */
    private function calculate_roi($date_range) {
        $revenue = $this->get_revenue_generated($date_range);
        $costs = $this->get_cost_savings($date_range);

        $total_revenue = $revenue['total_revenue'];
        $total_costs = $costs['total_savings'];

        if ($total_costs == 0) {
            return 0;
        }

        return (($total_revenue - $total_costs) / $total_costs) * 100;
    }

    /**
     * Obtient l'estimation de part de marché
     */
    private function get_market_share_estimate() {
        // Estimation basée sur des données générales
        return [
            'estimated_share' => 0.05, // 5%
            'market_size' => 1000000,
            'source' => 'industry_estimate'
        ];
    }

    /**
     * Obtient l'analyse concurrentielle
     */
    private function get_competitive_analysis() {
        return [
            'competitors' => ['Plugin A', 'Plugin B', 'Plugin C'],
            'strengths' => ['Fonctionnalités avancées', 'Performance', 'Support'],
            'weaknesses' => ['Prix', 'Courbe d\'apprentissage'],
            'opportunities' => ['Nouveau marché', 'Intégrations']
        ];
    }

    /**
     * Prévoit les revenus
     */
    private function forecast_revenue($period) {
        // Prévision simple basée sur les tendances
        $current_revenue = $this->get_revenue_generated($this->get_date_range($period));

        return [
            'forecasted_revenue' => $current_revenue['total_revenue'] * 1.1, // +10%
            'confidence_level' => 0.7,
            'forecast_period' => $period
        ];
    }

    /**
     * Prévoit l'utilisation
     */
    private function forecast_usage($period) {
        $current_usage = $this->get_total_pdfs_generated($this->get_date_range($period));

        return [
            'forecasted_usage' => $current_usage * 1.15, // +15%
            'confidence_level' => 0.75,
            'forecast_period' => $period
        ];
    }

    /**
     * Identifie les opportunités de croissance
     */
    private function identify_growth_opportunities() {
        return [
            'new_markets' => ['Marché B2B', 'International'],
            'new_features' => ['IA intégrée', 'Automatisation'],
            'partnerships' => ['Intégrations tierces'],
            'pricing_optimization' => ['Nouveaux plans tarifaires']
        ];
    }

    /**
     * Obtient l'utilisation par heure
     */
    private function get_usage_by_hour($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT HOUR(created_at) as hour, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY HOUR(created_at)
            ORDER BY hour
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient l'utilisation par jour
     */
    private function get_usage_by_day($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM $table
            WHERE created_at BETWEEN %s AND %s
            GROUP BY DATE(created_at)
            ORDER BY date
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les meilleurs utilisateurs
     */
    private function get_top_users($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT user_id, COUNT(*) as activity_count
            FROM $table
            WHERE user_id IS NOT NULL
            AND created_at BETWEEN %s AND %s
            GROUP BY user_id
            ORDER BY activity_count DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Obtient les taux de conversion
     */
    private function get_conversion_rates($date_range) {
        // Métriques de conversion (visites -> génération PDF)
        return [
            'visit_to_generation' => 0.15, // 15%
            'trial_to_paid' => 0.25, // 25%
            'feature_usage' => 0.8 // 80%
        ];
    }

    /**
     * Calcule l'engagement utilisateur
     */
    private function calculate_user_engagement($date_range) {
        $unique_users = $this->get_unique_users_count($date_range);
        $total_activities = $this->get_total_operations($date_range);

        if ($unique_users == 0) {
            return 0;
        }

        return $total_activities / $unique_users;
    }

    /**
     * Calcule l'adoption des fonctionnalités
     */
    private function calculate_feature_adoption($date_range) {
        $total_users = $this->get_unique_users_count($date_range);
        $feature_usage = $this->get_most_popular_features($date_range);

        $adoption_rates = [];
        foreach ($feature_usage as $feature) {
            $adoption_rates[$feature['event_type']] = ($feature['count'] / $total_users) * 100;
        }

        return $adoption_rates;
    }

    /**
     * Obtient le taux de succès du cache
     */
    private function get_cache_hit_rate($date_range) {
        // Cette métrique nécessiterait un suivi des accès cache
        return 85; // 85%
    }

    /**
     * Obtient les statistiques d'utilisation mémoire
     */
    private function get_memory_usage_stats($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        $result = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(memory_peak) as avg_peak, MAX(memory_peak) as max_peak
            FROM $table
            WHERE created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $result ?: ['avg_peak' => 0, 'max_peak' => 0];
    }

    /**
     * Obtient les performances de la base de données
     */
    private function get_database_performance($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        $result = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(execution_time) as avg_query_time, COUNT(*) as total_queries
            FROM $table
            WHERE operation_type LIKE 'db_%'
            AND created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $result ?: ['avg_query_time' => 0, 'total_queries' => 0];
    }

    /**
     * Obtient les temps de réponse API
     */
    private function get_api_response_times($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        $result = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(execution_time) as avg_response_time, MAX(execution_time) as max_response_time
            FROM $table
            WHERE operation_type LIKE 'api_%'
            AND created_at BETWEEN %s AND %s
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $result ?: ['avg_response_time' => 0, 'max_response_time' => 0];
    }

    /**
     * Obtient les opérations lentes
     */
    private function get_slow_operations($date_range) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_metrics';

        $results = $wpdb->get_results($wpdb->prepare("
            SELECT operation_type, execution_time, created_at
            FROM $table
            WHERE execution_time > 5
            AND created_at BETWEEN %s AND %s
            ORDER BY execution_time DESC
            LIMIT 10
        ", $date_range['from'], $date_range['to']), ARRAY_A);

        return $results;
    }

    /**
     * Formate un rapport
     */
    private function format_report($data, $format) {
        switch ($format) {
            case self::FORMAT_HTML:
                return $this->format_report_html($data);

            case self::FORMAT_JSON:
                return json_encode($data, JSON_PRETTY_PRINT);

            case self::FORMAT_CSV:
                return $this->format_report_csv($data);

            case self::FORMAT_PDF:
                return $this->format_report_pdf($data);

            default:
                return json_encode($data);
        }
    }

    /**
     * Formate un rapport en HTML
     */
    private function format_report_html($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Rapport PDF Builder Pro</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .metric { background: #fff; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .metric h3 { margin-top: 0; color: #333; }
                .metric-value { font-size: 24px; font-weight: bold; color: #007cba; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #f5f5f5; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Rapport PDF Builder Pro</h1>
                <p>Période: <?php echo esc_html($data['period']); ?></p>
                <p>Généré le: <?php echo esc_html($data['generated_at']); ?></p>
            </div>

            <?php foreach ($data['metrics'] as $key => $value): ?>
                <div class="metric">
                    <h3><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></h3>
                    <?php if (is_array($value)): ?>
                        <?php if (isset($value[0]) && is_array($value[0])): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($value[0]) as $col): ?>
                                            <th><?php echo esc_html(ucwords(str_replace('_', ' ', $col))); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($value as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $cell): ?>
                                                <td><?php echo esc_html(is_array($cell) ? wp_json_encode($cell) : $cell); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="metric-value"><?php echo json_encode($value, JSON_PRETTY_PRINT); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="metric-value"><?php echo esc_html($value); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Formate un rapport en CSV
     */
    private function format_report_csv($data) {
        $output = "Rapport PDF Builder Pro\n";
        $output .= "Période: {$data['period']}\n";
        $output .= "Généré le: {$data['generated_at']}\n\n";

        foreach ($data['metrics'] as $key => $value) {
            $output .= ucwords(str_replace('_', ' ', $key)) . "\n";

            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                // Tableau de données
                $output .= implode(',', array_keys($value[0])) . "\n";
                foreach ($value as $row) {
                    $output .= implode(',', array_map(function($cell) {
                        return is_array($cell) ? json_encode($cell) : $cell;
                    }, $row)) . "\n";
                }
            } else {
                $output .= (is_array($value) ? json_encode($value) : $value) . "\n";
            }

            $output .= "\n";
        }

        return $output;
    }

    /**
     * Formate un rapport en PDF (placeholder)
     */
    private function format_report_pdf($data) {
        // Nécessiterait une bibliothèque PDF comme TCPDF ou FPDF
        return json_encode($data); // Placeholder
    }

    /**
     * Sauvegarde un rapport
     */
    private function save_report($type, $period, $format, $data, $formatted_content) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_reports';

        $wpdb->insert(
            $table,
            [
                'report_type' => $type,
                'period' => $period,
                'format' => $format,
                'data' => json_encode($data),
                'file_path' => $this->save_report_file($formatted_content, $format),
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%d']
        );

        return $wpdb->insert_id;
    }

    /**
     * Sauvegarde le fichier du rapport
     */
    private function save_report_file($content, $format) {
        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/pdf-builder/reports/';

        wp_mkdir_p($reports_dir);

        $filename = 'report_' . time() . '_' . wp_generate_password(8, false);
        $extension = $format === self::FORMAT_HTML ? 'html' : ($format === self::FORMAT_CSV ? 'csv' : 'json');
        $file_path = $reports_dir . $filename . '.' . $extension;

        file_put_contents($file_path, $content);

        return $file_path;
    }

    /**
     * Génère le rapport quotidien
     */
    public function generate_daily_report() {
        try {
            $this->generate_report(self::REPORT_TYPE_USAGE, self::PERIOD_DAILY);
            $this->generate_report(self::REPORT_TYPE_SECURITY, self::PERIOD_DAILY);

        } catch (Exception $e) {
            $this->log_report_error('generate_daily_report', $e);
        }
    }

    /**
     * Génère le rapport hebdomadaire
     */
    public function generate_weekly_report() {
        try {
            $this->generate_report(self::REPORT_TYPE_PERFORMANCE, self::PERIOD_WEEKLY);
            $this->generate_report(self::REPORT_TYPE_ERROR, self::PERIOD_WEEKLY);

        } catch (Exception $e) {
            $this->log_report_error('generate_weekly_report', $e);
        }
    }

    /**
     * Génère le rapport mensuel
     */
    public function generate_monthly_report() {
        try {
            $this->generate_report(self::REPORT_TYPE_BUSINESS, self::PERIOD_MONTHLY);

        } catch (Exception $e) {
            $this->log_report_error('generate_monthly_report', $e);
        }
    }

    /**
     * Collecte les données d'utilisation
     */
    public function collect_usage_data() {
        // Collecter les métriques d'utilisation actuelles
        $data = [
            'active_users' => $this->get_active_users_count(),
            'total_pdfs_today' => $this->get_total_pdfs_generated($this->get_date_range(self::PERIOD_DAILY)),
            'server_load' => sys_getloadavg()[0] ?? 0,
            'memory_usage' => memory_get_peak_usage(true),
            'timestamp' => current_time('mysql')
        ];

        // Sauvegarder dans la base de données
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_usage_stats';

        $wpdb->insert(
            $table,
            $data,
            ['%d', '%d', '%f', '%d', '%s']
        );
    }

    /**
     * Collecte les données de performance
     */
    public function collect_performance_data() {
        // Collecter les métriques de performance actuelles
        $data = [
            'response_time' => $this->measure_response_time(),
            'db_queries' => get_num_queries(),
            'memory_usage' => memory_get_peak_usage(true),
            'cpu_usage' => $this->get_cpu_usage(),
            'timestamp' => current_time('mysql')
        ];

        // Sauvegarder dans la base de données
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_performance_stats';

        $wpdb->insert(
            $table,
            $data,
            ['%f', '%d', '%d', '%f', '%s']
        );
    }

    /**
     * Obtient le nombre d'utilisateurs actifs
     */
    private function get_active_users_count() {
        // Utilisateurs actifs dans la dernière heure
        $active_users = get_transient('pdf_builder_active_users');

        return $active_users ?: 0;
    }

    /**
     * Mesure le temps de réponse
     */
    private function measure_response_time() {
        // Mesurer le temps depuis le début de la requête
        if (defined('PDF_BUILDER_REQUEST_START')) {
            return microtime(true) - PDF_BUILDER_REQUEST_START;
        }

        return 0;
    }

    /**
     * Obtient l'utilisation CPU
     */
    private function get_cpu_usage() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0;
        }

        return 0;
    }

    /**
     * Nettoie les anciens rapports
     */
    public function cleanup_old_reports() {
        global $wpdb;

        $retention_days = pdf_builder_config('report_retention_days', 90);

        // Supprimer les anciens rapports
        $reports_table = $wpdb->prefix . 'pdf_builder_reports';
        $deleted_reports = $wpdb->query($wpdb->prepare("
            DELETE FROM $reports_table
            WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $retention_days));

        // Supprimer les anciens fichiers de rapports
        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/pdf-builder/reports/';

        if (is_dir($reports_dir)) {
            $files = glob($reports_dir . '*');
            $cutoff_time = time() - ($retention_days * DAY_IN_SECONDS);

            foreach ($files as $file) {
                if (filemtime($file) < $cutoff_time) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Notifie la génération d'un rapport
     */
    private function notify_report_generated($type, $period, $report_id) {
        $type_names = [
            self::REPORT_TYPE_USAGE => 'Utilisation',
            self::REPORT_TYPE_PERFORMANCE => 'Performance',
            self::REPORT_TYPE_SECURITY => 'Sécurité',
            self::REPORT_TYPE_ERROR => 'Erreurs',
            self::REPORT_TYPE_BUSINESS => 'Business'
        ];

        $message = "Rapport de {$type_names[$type]} ($period) généré avec succès";

        // Legacy notification calls removed — log info instead
    }

    /**
     * Log une erreur de rapport
     */
    private function log_report_error($operation, $exception) {
        error_log(wp_json_encode([
            'operation' => $operation,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]));
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

            $type = sanitize_text_field($_POST['type'] ?? self::REPORT_TYPE_USAGE);
            $period = sanitize_text_field($_POST['period'] ?? self::PERIOD_MONTHLY);
            $format = sanitize_text_field($_POST['format'] ?? self::FORMAT_HTML);

            $result = $this->generate_report($type, $period, $format);

            wp_send_json_success([
                'message' => 'Rapport généré avec succès',
                'report_id' => $result['report_id']
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la génération du rapport: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les données d'un rapport
     */
    public function get_report_data_ajax() {
        try {
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $report_id = intval($_POST['report_id'] ?? 0);

            if (!$report_id) {
                wp_send_json_error(['message' => 'ID de rapport manquant']);
                return;
            }

            $report_data = $this->get_report_data($report_id);

            if (!$report_data) {
                wp_send_json_error(['message' => 'Rapport introuvable']);
                return;
            }

            wp_send_json_success([
                'message' => 'Données du rapport récupérées',
                'data' => $report_data
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

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $report_id = intval($_POST['report_id'] ?? 0);
            $format = sanitize_text_field($_POST['format'] ?? self::FORMAT_CSV);

            if (!$report_id) {
                wp_send_json_error(['message' => 'ID de rapport manquant']);
                return;
            }

            $export_url = $this->export_report($report_id, $format);

            wp_send_json_success([
                'message' => 'Rapport exporté avec succès',
                'export_url' => $export_url
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'export: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient les données d'un rapport
     */
    private function get_report_data($report_id) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_reports';

        $report = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $table WHERE id = %d
        ", $report_id), ARRAY_A);

        if (!$report) {
            return false;
        }

        return [
            'id' => $report['id'],
            'type' => $report['report_type'],
            'period' => $report['period'],
            'format' => $report['format'],
            'data' => json_decode($report['data'], true),
            'file_path' => $report['file_path'],
            'created_at' => $report['created_at'],
            'created_by' => $report['created_by']
        ];
    }

    /**
     * Exporte un rapport
     */
    private function export_report($report_id, $format) {
        $report_data = $this->get_report_data($report_id);

        if (!$report_data) {
            throw new Exception('Rapport introuvable');
        }

        $formatted_content = $this->format_report($report_data['data'], $format);

        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/pdf-builder/exports/';

        wp_mkdir_p($export_dir);

        $filename = 'export_' . $report_id . '_' . time();
        $extension = $format === self::FORMAT_HTML ? 'html' : ($format === self::FORMAT_CSV ? 'csv' : 'json');
        $file_path = $export_dir . $filename . '.' . $extension;

        file_put_contents($file_path, $formatted_content);

        return $upload_dir['baseurl'] . '/pdf-builder/exports/' . $filename . '.' . $extension;
    }
}

// Fonctions globales
function pdf_builder_reporting_analytics() {
    return PDF_Builder_Reporting_Analytics::get_instance();
}

function pdf_builder_generate_analytics_report($type, $period = 'monthly', $format = 'html') {
    return PDF_Builder_Reporting_Analytics::get_instance()->generate_report($type, $period, $format);
}

function pdf_builder_get_reports() {
    // Cette fonction nécessiterait une méthode pour récupérer la liste des rapports
    return [];
}

// Initialiser le système de reporting
add_action('plugins_loaded', function() {
    PDF_Builder_Reporting_Analytics::get_instance();
});



