<?php
/**
 * Système de Reporting Avancé pour PDF Builder Pro
 *
 * Génération de rapports complets avec analyses, métriques,
 * et export dans multiple formats.
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du système de reporting
 */
class PDF_Builder_Reporting_System {

    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Données de reporting
     */
    private $report_data = array();

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_report_data();
        $this->register_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les données de reporting
     */
    private function init_report_data() {
        $this->report_data = array(
            'system_info' => $this->get_system_info(),
            'performance_metrics' => $this->get_performance_metrics(),
            'usage_statistics' => $this->get_usage_statistics(),
            'security_status' => $this->get_security_status(),
            'database_health' => $this->get_database_health(),
            'cache_status' => $this->get_cache_status(),
        );
    }

    /**
     * Enregistrer les hooks
     */
    private function register_hooks() {
        add_action('wp_ajax_pdf_builder_generate_report', array($this, 'ajax_generate_report'));
        add_action('wp_ajax_pdf_builder_export_report', array($this, 'ajax_export_report'));
        add_action('pdf_builder_weekly_report', array($this, 'generate_weekly_report'));
    }

    /**
     * Générer un rapport complet
     */
    public function generate_full_report($format = 'html') {
        $report = array(
            'title' => 'Rapport Complet PDF Builder Pro',
            'generated_at' => current_time('Y-m-d H:i:s'),
            'period' => 'Temps réel',
            'sections' => array(
                'system' => $this->generate_system_report(),
                'performance' => $this->generate_performance_report(),
                'usage' => $this->generate_usage_report(),
                'security' => $this->generate_security_report(),
                'database' => $this->generate_database_report(),
                'recommendations' => $this->generate_recommendations(),
            ),
        );

        return $this->format_report($report, $format);
    }

    /**
     * Générer un rapport système
     */
    private function generate_system_report() {
        $system_info = $this->report_data['system_info'];

        return array(
            'title' => 'Informations Système',
            'data' => $system_info,
            'status' => $this->calculate_system_health_score($system_info),
            'alerts' => $this->get_system_alerts($system_info),
        );
    }

    /**
     * Générer un rapport de performance
     */
    private function generate_performance_report() {
        $metrics = $this->report_data['performance_metrics'];

        return array(
            'title' => 'Métriques de Performance',
            'data' => $metrics,
            'charts' => array(
                'response_times' => $this->generate_response_time_chart($metrics),
                'memory_usage' => $this->generate_memory_usage_chart($metrics),
                'cpu_usage' => $this->generate_cpu_usage_chart($metrics),
            ),
            'recommendations' => $this->get_performance_recommendations($metrics),
        );
    }

    /**
     * Générer un rapport d'utilisation
     */
    private function generate_usage_report() {
        $stats = $this->report_data['usage_statistics'];

        return array(
            'title' => 'Statistiques d\'Utilisation',
            'data' => $stats,
            'charts' => array(
                'templates_created' => $this->generate_templates_chart($stats),
                'pdfs_generated' => $this->generate_pdfs_chart($stats),
                'user_activity' => $this->generate_user_activity_chart($stats),
            ),
            'insights' => $this->get_usage_insights($stats),
        );
    }

    /**
     * Générer un rapport de sécurité
     */
    private function generate_security_report() {
        $security = $this->report_data['security_status'];

        return array(
            'title' => 'État de Sécurité',
            'data' => $security,
            'vulnerabilities' => $this->get_security_vulnerabilities($security),
            'recommendations' => $this->get_security_recommendations($security),
            'compliance' => $this->check_compliance_status($security),
        );
    }

    /**
     * Générer un rapport de base de données
     */
    private function generate_database_report() {
        $db_health = $this->report_data['database_health'];

        return array(
            'title' => 'Santé de la Base de Données',
            'data' => $db_health,
            'optimization_suggestions' => $this->get_database_optimization_suggestions($db_health),
            'backup_status' => $this->get_backup_status(),
        );
    }

    /**
     * Générer des recommandations
     */
    private function generate_recommendations() {
        $recommendations = array();

        // Recommandations basées sur les métriques
        if ($this->report_data['performance_metrics']['memory_usage'] > 80) {
            $recommendations[] = array(
                'type' => 'performance',
                'priority' => 'high',
                'title' => 'Utilisation mémoire élevée',
                'description' => 'L\'utilisation mémoire dépasse 80%. Considérez optimiser les templates ou augmenter la limite mémoire.',
                'action' => 'Augmenter WP_MEMORY_LIMIT ou optimiser les templates PDF.',
            );
        }

        if ($this->report_data['cache_status']['hit_ratio'] < 50) {
            $recommendations[] = array(
                'type' => 'cache',
                'priority' => 'medium',
                'title' => 'Taux de succès du cache faible',
                'description' => 'Le cache a un faible taux de succès. Vérifiez la configuration du cache.',
                'action' => 'Ajuster le TTL du cache ou activer la compression.',
            );
        }

        if (count($this->report_data['security_status']['failed_logins']) > 10) {
            $recommendations[] = array(
                'type' => 'security',
                'priority' => 'high',
                'title' => 'Tentatives de connexion échouées',
                'description' => 'Plus de 10 tentatives de connexion échouées détectées.',
                'action' => 'Vérifier les logs de sécurité et renforcer les mesures de sécurité.',
            );
        }

        return $recommendations;
    }

    /**
     * Formater le rapport selon le format demandé
     */
    private function format_report($report, $format) {
        switch ($format) {
            case 'pdf':
                return $this->format_pdf_report($report);
            case 'csv':
                return $this->format_csv_report($report);
            case 'json':
                return $this->format_json_report($report);
            case 'html':
            default:
                return $this->format_html_report($report);
        }
    }

    /**
     * Formater en HTML
     */
    private function format_html_report($report) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php echo esc_html($report['title']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
                .section { margin-bottom: 30px; border: 1px solid #ddd; border-radius: 5px; }
                .section-header { background: #007cba; color: white; padding: 10px; border-radius: 5px 5px 0 0; }
                .section-content { padding: 15px; }
                .metric { display: inline-block; margin: 10px; padding: 10px; background: #f9f9f9; border-radius: 3px; }
                .alert { padding: 10px; margin: 10px 0; border-radius: 3px; }
                .alert-high { background: #ffeaea; border-left: 4px solid #dc3232; }
                .alert-medium { background: #fff3cd; border-left: 4px solid #ffc107; }
                .alert-low { background: #d1ecf1; border-left: 4px solid #17a2b8; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f5f5f5; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?php echo esc_html($report['title']); ?></h1>
                <p>Généré le: <?php echo esc_html($report['generated_at']); ?></p>
                <p>Période: <?php echo esc_html($report['period']); ?></p>
            </div>

            <?php foreach ($report['sections'] as $section_key => $section): ?>
            <div class="section">
                <div class="section-header">
                    <h2><?php echo esc_html($section['title']); ?></h2>
                </div>
                <div class="section-content">
                    <?php $this->render_section_content($section, $section_key); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Rendre le contenu d'une section
     */
    private function render_section_content($section, $section_key) {
        if (isset($section['data']) && is_array($section['data'])) {
            echo '<table>';
            echo '<tr><th>Metric</th><th>Value</th><th>Status</th></tr>';
            foreach ($section['data'] as $key => $value) {
                $status = $this->get_metric_status($key, $value);
                echo '<tr>';
                echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $key))) . '</td>';
                echo '<td>' . esc_html(is_array($value) ? json_encode($value) : $value) . '</td>';
                echo '<td>' . esc_html($status) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }

        if (isset($section['alerts']) && !empty($section['alerts'])) {
            foreach ($section['alerts'] as $alert) {
                $class = 'alert-' . ($alert['priority'] ?? 'low');
                echo '<div class="alert ' . esc_attr($class) . '">';
                echo '<strong>' . esc_html($alert['title']) . ':</strong> ' . esc_html($alert['message']);
                echo '</div>';
            }
        }

        if (isset($section['recommendations']) && !empty($section['recommendations'])) {
            echo '<h3>Recommandations</h3>';
            foreach ($section['recommendations'] as $rec) {
                $class = 'alert-' . ($rec['priority'] ?? 'low');
                echo '<div class="alert ' . esc_attr($class) . '">';
                echo '<strong>' . esc_html($rec['title']) . ':</strong> ' . esc_html($rec['description']);
                if (isset($rec['action'])) {
                    echo '<br><em>Action: ' . esc_html($rec['action']) . '</em>';
                }
                echo '</div>';
            }
        }
    }

    /**
     * Obtenir le statut d'une métrique
     */
    private function get_metric_status($key, $value) {
        $thresholds = array(
            'memory_usage' => array('good' => 60, 'warning' => 80),
            'cpu_usage' => array('good' => 70, 'warning' => 90),
            'cache_hit_ratio' => array('good' => 80, 'warning' => 50),
            'failed_logins' => array('good' => 0, 'warning' => 5),
        );

        if (!isset($thresholds[$key])) {
            return 'OK';
        }

        $threshold = $thresholds[$key];
        if ($value <= $threshold['good']) {
            return 'Good';
        } elseif ($value <= $threshold['warning']) {
            return 'Warning';
        } else {
            return 'Critical';
        }
    }

    /**
     * Méthodes pour obtenir les données du système
     */
    private function get_system_info() {
        return array(
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->get_mysql_version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'plugin_version' => PDF_BUILDER_VERSION ?? 'Unknown',
            'active_theme' => wp_get_theme()->get('Name'),
            'active_plugins' => count(get_option('active_plugins', array())),
        );
    }

    private function get_performance_metrics() {
        return array(
            'memory_usage' => $this->get_memory_usage_percentage(),
            'cpu_usage' => $this->get_cpu_usage(),
            'response_time' => $this->get_average_response_time(),
            'cache_hit_ratio' => $this->get_cache_hit_ratio(),
            'database_queries' => get_num_queries(),
            'page_load_time' => timer_stop(0, 3),
        );
    }

    private function get_usage_statistics() {
        global $wpdb;

        $stats = array(
            'total_templates' => intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_templates")),
            'total_pdfs_generated' => intval(pdf_builder_get_option('pdf_builder_total_pdfs', 0)),
            'active_users' => count(get_users(array('meta_key' => 'pdf_builder_last_activity', 'meta_compare' => '>', 'meta_value' => strtotime('-30 days')))),
            'total_logins' => intval(pdf_builder_get_option('pdf_builder_total_logins', 0)),
        );

        return $stats;
    }

    private function get_security_status() {
        return array(
            'failed_logins' => intval(pdf_builder_get_option('pdf_builder_failed_logins', 0)),
            'blocked_ips' => count(pdf_builder_get_option('pdf_builder_blocked_ips', array())),
            'security_scans' => intval(pdf_builder_get_option('pdf_builder_security_scans', 0)),
            'last_security_scan' => pdf_builder_get_option('pdf_builder_last_security_scan', 'Never'),
            'ssl_enabled' => is_ssl(),
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
        );
    }

    private function get_database_health() {
        global $wpdb;

        return array(
            'table_count' => count($wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}%'")),
            'database_size' => $this->get_database_size(),
            'connection_status' => $this->check_database_connection(),
            'slow_queries' => intval(pdf_builder_get_option('pdf_builder_slow_queries', 0)),
            'orphaned_data' => $this->check_orphaned_data(),
        );
    }

    private function get_cache_status() {
        $cache_metrics = pdf_builder_get_option('pdf_builder_cache_metrics', array());

        return array(
            'enabled' => pdf_builder_get_option('pdf_builder_cache_enabled', '0') === '1',
            'size' => $cache_metrics['size'] ?? 0,
            'hit_ratio' => $this->calculate_hit_ratio($cache_metrics),
            'compression_enabled' => pdf_builder_get_option('pdf_builder_cache_compression', '0') === '1',
            'last_cleanup' => $cache_metrics['last_cleanup'] ?? 0,
        );
    }

    /**
     * Méthodes utilitaires
     */
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }

    private function get_memory_usage_percentage() {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            $limit = (int) $matches[1];
            $unit = strtolower($matches[2]);

            switch ($unit) {
                case 'g': $limit *= 1024;
                case 'm': $limit *= 1024;
                case 'k': $limit *= 1024;
            }

            $usage = memory_get_peak_usage(true);
            return round(($usage / $limit) * 100, 2);
        }
        return 0;
    }

    private function get_cpu_usage() {
        // Estimation simple - en production, utiliser un monitoring réel
        return rand(10, 50); // Placeholder
    }

    private function get_average_response_time() {
        // Calcul basé sur les métriques stockées
        $response_times = pdf_builder_get_option('pdf_builder_response_times', array());
        if (empty($response_times)) {
            return 0;
        }
        return round(array_sum($response_times) / count($response_times), 3);
    }

    private function get_cache_hit_ratio() {
        $metrics = pdf_builder_get_option('pdf_builder_cache_metrics', array());
        $hits = $metrics['hits'] ?? 0;
        $misses = $metrics['misses'] ?? 0;
        $total = $hits + $misses;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private function calculate_hit_ratio($metrics) {
        $hits = $metrics['hits'] ?? 0;
        $misses = $metrics['misses'] ?? 0;
        $total = $hits + $misses;
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    private function get_database_size() {
        global $wpdb;
        $result = $wpdb->get_row("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
            FROM information_schema.TABLES
            WHERE table_schema = '{$wpdb->dbname}'");
        return $result ? $result->size_mb : 0;
    }

    private function check_database_connection() {
        global $wpdb;
        return $wpdb->check_connection() ? 'Connected' : 'Disconnected';
    }

    private function check_orphaned_data() {
        // Vérifier les données orphelines (simplifié)
        return 0; // Placeholder
    }

    private function calculate_system_health_score($system_info) {
        $score = 100;

        if (version_compare($system_info['php_version'], '7.4', '<')) {
            $score -= 20;
        }

        if (intval($system_info['memory_limit']) < 128) {
            $score -= 15;
        }

        return max(0, $score);
    }

    private function get_system_alerts($system_info) {
        $alerts = array();

        if (version_compare($system_info['php_version'], '7.4', '<')) {
            $alerts[] = array(
                'priority' => 'high',
                'title' => 'Version PHP obsolète',
                'message' => 'PHP ' . $system_info['php_version'] . ' n\'est plus supporté. Mettez à jour vers PHP 7.4+.',
            );
        }

        return $alerts;
    }

    /**
     * Générer des graphiques (placeholders pour HTML)
     */
    private function generate_response_time_chart($metrics) {
        return '<div class="chart-placeholder">Graphique des temps de réponse</div>';
    }

    private function generate_memory_usage_chart($metrics) {
        return '<div class="chart-placeholder">Graphique d\'utilisation mémoire</div>';
    }

    private function generate_cpu_usage_chart($metrics) {
        return '<div class="chart-placeholder">Graphique d\'utilisation CPU</div>';
    }

    private function generate_templates_chart($stats) {
        return '<div class="chart-placeholder">Graphique des templates créés</div>';
    }

    private function generate_pdfs_chart($stats) {
        return '<div class="chart-placeholder">Graphique des PDFs générés</div>';
    }

    private function generate_user_activity_chart($stats) {
        return '<div class="chart-placeholder">Graphique d\'activité utilisateur</div>';
    }

    /**
     * Handlers AJAX
     */
    public function ajax_generate_report() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $format = sanitize_text_field($_POST['format'] ?? 'html');
        $report = $this->generate_full_report($format);

        wp_send_json_success(array(
            'report' => $report,
            'format' => $format,
        ));
    }

    public function ajax_export_report() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $format = sanitize_text_field($_POST['format'] ?? 'pdf');
        $report = $this->generate_full_report($format);

        // Pour l'instant, retourner le contenu - en production, générer un fichier
        wp_send_json_success(array(
            'content' => $report,
            'filename' => 'pdf-builder-report-' . date('Y-m-d') . '.' . $format,
        ));
    }

    /**
     * Générer un rapport hebdomadaire automatique
     */
    public function generate_weekly_report() {
        $report = $this->generate_full_report('html');

        // Sauvegarder le rapport
        $filename = 'weekly-report-' . date('Y-m-d') . '.html';
        $upload_dir = wp_upload_dir();
        $reports_dir = $upload_dir['basedir'] . '/pdf-builder-reports/';

        if (!file_exists($reports_dir)) {
            wp_mkdir_p($reports_dir);
        }

        file_put_contents($reports_dir . $filename, $report);

        // Envoyer par email si configuré
        $email_recipient = pdf_builder_get_option('pdf_builder_report_email', '');
        if (!empty($email_recipient)) {
            wp_mail(
                $email_recipient,
                'Rapport Hebdomadaire PDF Builder Pro',
                'Le rapport hebdomadaire est disponible en pièce jointe.',
                array('Content-Type: text/html; charset=UTF-8'),
                array($reports_dir . $filename)
            );
        }
    }

    /**
     * Placeholders pour les autres méthodes
     */
    private function get_performance_recommendations($metrics) { return array(); }
    private function get_usage_insights($stats) { return array(); }
    private function get_security_vulnerabilities($security) { return array(); }
    private function get_security_recommendations($security) { return array(); }
    private function check_compliance_status($security) { return array(); }
    private function get_database_optimization_suggestions($db_health) { return array(); }
    private function get_backup_status() { return array(); }
    private function format_pdf_report($report) { return 'PDF format not implemented yet'; }
    private function format_csv_report($report) { return 'CSV format not implemented yet'; }
    private function format_json_report($report) { return json_encode($report); }
}



