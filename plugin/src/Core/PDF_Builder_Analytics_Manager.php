<?php
/**
 * PDF Builder Pro - Système de métriques et analyses
 * Collecte et analyse les données d'utilisation du plugin
 */

class PDF_Builder_Analytics_Manager {
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
        // Collecte des métriques d'utilisation
        add_action('pdf_builder_template_created', [$this, 'track_template_created']);
        add_action('pdf_builder_template_updated', [$this, 'track_template_updated']);
        add_action('pdf_builder_template_deleted', [$this, 'track_template_deleted']);
        add_action('pdf_builder_pdf_generated', [$this, 'track_pdf_generated']);
        add_action('pdf_builder_user_action', [$this, 'track_user_action']);

        // Collecte des métriques de performance
        add_action('wp_ajax_pdf_builder_*', [$this, 'track_ajax_request'], 1);
        add_action('admin_init', [$this, 'track_admin_page_view']);

        // Génération de rapports
        add_action('wp_ajax_pdf_builder_get_analytics', [$this, 'get_analytics_ajax']);
        add_action('wp_ajax_pdf_builder_export_analytics', [$this, 'export_analytics_ajax']);

        // Nettoyage des anciennes données
        add_action('pdf_builder_weekly_cleanup', [$this, 'cleanup_old_analytics']);
    }

    /**
     * Suit la création d'un template
     */
    public function track_template_created($template_data) {
        $this->record_event('template_created', [
            'template_id' => $template_data['id'] ?? null,
            'user_id' => get_current_user_id(),
            'template_type' => $template_data['type'] ?? 'unknown',
            'elements_count' => count($template_data['elements'] ?? [])
        ]);
    }

    /**
     * Suit la mise à jour d'un template
     */
    public function track_template_updated($template_data) {
        $this->record_event('template_updated', [
            'template_id' => $template_data['id'] ?? null,
            'user_id' => get_current_user_id(),
            'changes_count' => $template_data['changes_count'] ?? 0
        ]);
    }

    /**
     * Suit la suppression d'un template
     */
    public function track_template_deleted($template_data) {
        $this->record_event('template_deleted', [
            'template_id' => $template_data['id'] ?? null,
            'user_id' => get_current_user_id(),
            'template_type' => $template_data['type'] ?? 'unknown'
        ]);
    }

    /**
     * Suit la génération d'un PDF
     */
    public function track_pdf_generated($pdf_data) {
        $this->record_event('pdf_generated', [
            'template_id' => $pdf_data['template_id'] ?? null,
            'user_id' => get_current_user_id(),
            'file_size' => $pdf_data['file_size'] ?? 0,
            'generation_time' => $pdf_data['generation_time'] ?? 0,
            'pages_count' => $pdf_data['pages_count'] ?? 1,
            'success' => $pdf_data['success'] ?? true
        ]);
    }

    /**
     * Suit les actions utilisateur
     */
    public function track_user_action($action_data) {
        $this->record_event('user_action', [
            'action' => $action_data['action'] ?? 'unknown',
            'user_id' => get_current_user_id(),
            'context' => $action_data['context'] ?? 'unknown',
            'metadata' => $action_data['metadata'] ?? []
        ]);
    }

    /**
     * Suit les requêtes AJAX
     */
    public function track_ajax_request() {
        $action = current_action();
        $action = str_replace('wp_ajax_pdf_builder_', '', $action);
        $action = str_replace('wp_ajax_nopriv_pdf_builder_', '', $action);

        $this->record_event('ajax_request', [
            'action' => $action,
            'user_id' => get_current_user_id(),
            'is_admin' => is_admin(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Suit les vues de pages admin
     */
    public function track_admin_page_view() {
        if (!isset($_GET['page']) || strpos($_GET['page'], 'pdf_builder') !== 0) {
            return;
        }

        $this->record_event('admin_page_view', [
            'page' => $_GET['page'],
            'user_id' => get_current_user_id(),
            'tab' => $_GET['tab'] ?? 'default',
            'query_params' => $this->sanitize_query_params($_GET)
        ]);
    }

    /**
     * Enregistre un événement
     */
    private function record_event($event_type, $data) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        // Créer la table si elle n'existe pas
        $this->create_analytics_table();

        $wpdb->insert(
            $table,
            [
                'event_type' => $event_type,
                'event_data' => json_encode($data),
                'user_id' => $data['user_id'] ?? get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'session_id' => session_id() ?: $this->generate_session_id(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Crée la table des analytics
     */
    private function create_analytics_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                event_type varchar(50) NOT NULL,
                event_data longtext NOT NULL,
                user_id bigint(20) unsigned,
                ip_address varchar(45),
                user_agent text,
                session_id varchar(64),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY event_type (event_type),
                KEY user_id (user_id),
                KEY session_id (session_id),
                KEY created_at (created_at)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Génère un ID de session
     */
    private function generate_session_id() {
        if (!session_id()) {
            session_start();
        }
        return session_id();
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
     * Sanitise les paramètres de requête
     */
    private function sanitize_query_params($params) {
        $sensitive_keys = ['nonce', 'password', 'pwd', 'key', 'token'];
        $sanitized = [];

        foreach ($params as $key => $value) {
            if (in_array(strtolower($key), $sensitive_keys)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = is_scalar($value) ? sanitize_text_field($value) : '[COMPLEX]';
            }
        }

        return $sanitized;
    }

    /**
     * Obtient les données d'analyses
     */
    public function get_analytics($period_days = 30, $event_types = []) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $where = [];
        $where_values = [];

        if (!empty($event_types)) {
            $placeholders = str_repeat('%s,', count($event_types) - 1) . '%s';
            $where[] = "event_type IN ($placeholders)";
            $where_values = array_merge($where_values, $event_types);
        }

        $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)';
        $where_values[] = $period_days;

        $where_clause = 'WHERE ' . implode(' AND ', $where);

        // Statistiques générales
        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total_events,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT session_id) as total_sessions,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM $table
            $where_clause
        ", $where_values), ARRAY_A);

        // Événements par type
        $events_by_type = $wpdb->get_results($wpdb->prepare("
            SELECT
                event_type,
                COUNT(*) as count,
                COUNT(DISTINCT user_id) as unique_users
            FROM $table
            $where_clause
            GROUP BY event_type
            ORDER BY count DESC
        ", $where_values), ARRAY_A);

        // Activité par jour
        $daily_activity = $wpdb->get_results($wpdb->prepare("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as events_count,
                COUNT(DISTINCT user_id) as users_count
            FROM $table
            $where_clause
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ", $where_values), ARRAY_A);

        // Top utilisateurs
        $top_users = $wpdb->get_results($wpdb->prepare("
            SELECT
                user_id,
                COUNT(*) as events_count,
                MAX(created_at) as last_activity
            FROM $table
            $where_clause
            GROUP BY user_id
            ORDER BY events_count DESC
            LIMIT 10
        ", $where_values), ARRAY_A);

        // Métriques spécifiques
        $specific_metrics = $this->get_specific_metrics($period_days);

        return [
            'period_days' => $period_days,
            'stats' => $stats,
            'events_by_type' => $events_by_type,
            'daily_activity' => $daily_activity,
            'top_users' => $top_users,
            'specific_metrics' => $specific_metrics
        ];
    }

    /**
     * Obtient des métriques spécifiques
     */
    private function get_specific_metrics($period_days) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        $metrics = [];

        // Métriques des templates
        $template_metrics = $wpdb->get_row($wpdb->prepare("
            SELECT
                SUM(CASE WHEN event_type = 'template_created' THEN 1 ELSE 0 END) as templates_created,
                SUM(CASE WHEN event_type = 'template_updated' THEN 1 ELSE 0 END) as templates_updated,
                SUM(CASE WHEN event_type = 'template_deleted' THEN 1 ELSE 0 END) as templates_deleted
            FROM $table
            WHERE event_type IN ('template_created', 'template_updated', 'template_deleted')
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $period_days), ARRAY_A);

        $metrics['templates'] = $template_metrics;

        // Métriques des PDFs
        $pdf_metrics = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total_generated,
                AVG(JSON_EXTRACT(event_data, '$.generation_time')) as avg_generation_time,
                AVG(JSON_EXTRACT(event_data, '$.file_size')) as avg_file_size,
                SUM(CASE WHEN JSON_EXTRACT(event_data, '$.success') = 'true' THEN 1 ELSE 0 END) as successful_generations
            FROM $table
            WHERE event_type = 'pdf_generated'
            AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
        ", $period_days), ARRAY_A);

        $metrics['pdfs'] = $pdf_metrics;

        // Métriques des utilisateurs
        $user_metrics = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(DISTINCT user_id) as active_users,
                AVG(events_per_user) as avg_events_per_user
            FROM (
                SELECT user_id, COUNT(*) as events_per_user
                FROM $table
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY user_id
            ) as user_events
        ", $period_days), ARRAY_A);

        $metrics['users'] = $user_metrics;

        return $metrics;
    }

    /**
     * Génère un rapport d'analyses
     */
    public function generate_report($period_days = 30) {
        $analytics = $this->get_analytics($period_days);

        $report = [
            'generated_at' => current_time('mysql'),
            'period' => $period_days . ' days',
            'summary' => [
                'total_events' => $analytics['stats']['total_events'],
                'unique_users' => $analytics['stats']['unique_users'],
                'total_sessions' => $analytics['stats']['total_sessions'],
                'templates_created' => $analytics['specific_metrics']['templates']['templates_created'],
                'pdfs_generated' => $analytics['specific_metrics']['pdfs']['total_generated'],
                'success_rate' => $analytics['specific_metrics']['pdfs']['total_generated'] > 0 ?
                    round(($analytics['specific_metrics']['pdfs']['successful_generations'] / $analytics['specific_metrics']['pdfs']['total_generated']) * 100, 2) : 0
            ],
            'insights' => $this->generate_insights($analytics),
            'recommendations' => $this->generate_recommendations($analytics)
        ];

        return $report;
    }

    /**
     * Génère des insights à partir des données
     */
    private function generate_insights($analytics) {
        $insights = [];

        $stats = $analytics['stats'];
        $metrics = $analytics['specific_metrics'];

        // Insight sur l'activité
        if ($stats['total_events'] > 1000) {
            $insights[] = 'Activité très élevée détectée - le plugin est intensivement utilisé';
        } elseif ($stats['total_events'] < 10) {
            $insights[] = 'Activité faible - envisagez de promouvoir les fonctionnalités du plugin';
        }

        // Insight sur les PDFs
        $pdf_success_rate = $metrics['pdfs']['total_generated'] > 0 ?
            ($metrics['pdfs']['successful_generations'] / $metrics['pdfs']['total_generated']) * 100 : 0;

        if ($pdf_success_rate < 80) {
            $insights[] = 'Taux de succès des PDFs faible - vérifiez les erreurs de génération';
        }

        // Insight sur les templates
        $templates_created = $metrics['templates']['templates_created'];
        $templates_updated = $metrics['templates']['templates_updated'];

        if ($templates_updated > $templates_created * 2) {
            $insights[] = 'Les templates sont fréquemment modifiés - bonne adaptabilité utilisateur';
        }

        // Insight sur les utilisateurs
        if ($stats['unique_users'] > 10) {
            $insights[] = 'Base d\'utilisateurs étendue - plugin populaire';
        }

        return $insights;
    }

    /**
     * Génère des recommandations
     */
    private function generate_recommendations($analytics) {
        $recommendations = [];

        $stats = $analytics['stats'];
        $metrics = $analytics['specific_metrics'];

        // Recommandations basées sur les métriques
        if ($metrics['pdfs']['avg_generation_time'] > 5.0) {
            $recommendations[] = 'Optimiser les performances de génération PDF - temps moyen élevé détecté';
        }

        if ($stats['total_events'] > 5000) {
            $recommendations[] = 'Envisager l\'archivage des anciennes données d\'analytics pour améliorer les performances';
        }

        if ($metrics['templates']['templates_created'] < 5) {
            $recommendations[] = 'Peu de templates créés - améliorer l\'onboarding utilisateur';
        }

        return $recommendations;
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

            $period_days = intval($_POST['period_days'] ?? 30);
            $event_types = isset($_POST['event_types']) ? (array) $_POST['event_types'] : [];

            $analytics = $this->get_analytics($period_days, $event_types);

            wp_send_json_success([
                'message' => 'Analyses récupérées',
                'analytics' => $analytics
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la récupération des analyses: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exporte les analyses
     */
    public function export_analytics_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $period_days = intval($_POST['period_days'] ?? 30);
            $report = $this->generate_report($period_days);

            $export_data = json_encode($report, JSON_PRETTY_PRINT);

            wp_send_json_success([
                'message' => 'Rapport exporté',
                'data' => $export_data,
                'filename' => 'pdf-builder-analytics-' . date('Y-m-d') . '.json'
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'export: ' . $e->getMessage()]);
        }
    }

    /**
     * Nettoie les anciennes données d'analyses
     */
    public function cleanup_old_analytics() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_analytics';

        // Supprimer les données de plus de 90 jours
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        ));

        // Logger le nettoyage
        if (class_exists('PDF_Builder_Logger')) {
            $logger = PDF_Builder_Logger::get_instance();
            $deleted = $wpdb->rows_affected;
            $logger->info("Analytics cleanup completed: $deleted old records removed");
        }
    }
}

// Fonctions globales
function pdf_builder_track_event($event_type, $data = []) {
    PDF_Builder_Analytics_Manager::get_instance()->record_event($event_type, $data);
}

function pdf_builder_get_analytics($period_days = 30, $event_types = []) {
    return PDF_Builder_Analytics_Manager::get_instance()->get_analytics($period_days, $event_types);
}

function pdf_builder_generate_analytics_report($period_days = 30) {
    return PDF_Builder_Analytics_Manager::get_instance()->generate_report($period_days);
}

// Initialiser le gestionnaire d'analyses
add_action('plugins_loaded', function() {
    PDF_Builder_Analytics_Manager::get_instance();
});
