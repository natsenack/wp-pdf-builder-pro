<?php
/**
 * PDF Builder Pro - Security Logging & Monitoring Module
 * Phase 4: Logs de sécurité et monitoring selon OWASP
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// LOGGER DE SÉCURITÉ
// ============================================================================

class PDF_Builder_Security_Logger {

    private static $instance = null;
    private $log_table = 'pdf_builder_security_logs';
    private $max_logs = 10000; // Nombre maximum de logs à garder

    private function __construct() {
        $this->init_hooks();
        $this->create_log_table();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialise les hooks
     */
    private function init_hooks() {
        // Événements WordPress
        add_action('wp_login_failed', [$this, 'log_failed_login']);
        add_action('wp_login', [$this, 'log_successful_login'], 10, 2);
        add_action('profile_update', [$this, 'log_profile_update'], 10, 2);

        // Événements AJAX
        add_action('wp_ajax_pdf_builder_unified_dispatch', [$this, 'log_ajax_request'], 1);

        // Nettoyage périodique des logs
        add_action('pdf_builder_cleanup_security_logs', [$this, 'cleanup_old_logs']);

        // Planifier le nettoyage
        if (!wp_next_scheduled('pdf_builder_cleanup_security_logs')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cleanup_security_logs');
        }
    }

    /**
     * Crée la table des logs de sécurité
     */
    private function create_log_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->log_table;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(100) NOT NULL,
            severity enum('low','medium','high','critical') DEFAULT 'low',
            event_data longtext,
            ip_address varchar(45),
            user_agent text,
            user_id bigint(20) UNSIGNED DEFAULT 0,
            session_id varchar(128),
            request_uri text,
            http_method varchar(10),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_event_type (event_type),
            INDEX idx_severity (severity),
            INDEX idx_created_at (created_at),
            INDEX idx_ip (ip_address),
            INDEX idx_user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log un événement de sécurité
     */
    public function log_event($event_type, $data = [], $severity = 'low') {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->log_table;

        $log_data = [
            'event_type' => sanitize_text_field($event_type),
            'severity' => $this->validate_severity($severity),
            'event_data' => json_encode($this->sanitize_log_data($data)),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => get_current_user_id(),
            'session_id' => session_id() ?: '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'http_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
        ];

        $wpdb->insert($table_name, $log_data);

        // Log aussi dans le fichier de debug WordPress pour les événements critiques
        if ($severity === 'high' || $severity === 'critical') {
            error_log(sprintf(
                'PDF_BUILDER_SECURITY [%s]: %s - IP: %s - User: %s',
                strtoupper($severity),
                $event_type,
                $log_data['ip_address'],
                $log_data['user_id']
            ));
        }
    }

    /**
     * Log une tentative de connexion échouée
     */
    public function log_failed_login($username) {
        $this->log_event('failed_login', [
            'username' => $username,
            'attempted_user' => $username
        ], 'medium');
    }

    /**
     * Log une connexion réussie
     */
    public function log_successful_login($user_login, $user) {
        $this->log_event('successful_login', [
            'username' => $user_login,
            'user_id' => $user->ID,
            'user_email' => $user->user_email
        ], 'low');
    }

    /**
     * Log une mise à jour de profil
     */
    public function log_profile_update($user_id, $old_user_data) {
        $user = get_userdata($user_id);
        $changes = [];

        if ($user) {
            // Détecter les changements importants
            if ($old_user_data->user_email !== $user->user_email) {
                $changes[] = 'email';
            }
            if ($old_user_data->user_pass !== $user->user_pass) {
                $changes[] = 'password';
            }
            if ($old_user_data->display_name !== $user->display_name) {
                $changes[] = 'display_name';
            }
        }

        if (!empty($changes)) {
            $this->log_event('profile_update', [
                'user_id' => $user_id,
                'username' => $user->user_login,
                'changes' => $changes
            ], 'low');
        }
    }

    /**
     * Log les requêtes AJAX
     */
    public function log_ajax_request() {
        $action = $_REQUEST['action'] ?? '';

        // Ne logger que les actions du PDF Builder
        if (strpos($action, 'pdf_builder') !== 0) {
            return;
        }

        // Déterminer la sévérité selon l'action
        $severity = 'low';
        $sensitive_actions = [
            'pdf_builder_save_template',
            'pdf_builder_delete_template',
            'pdf_builder_save_all_settings'
        ];

        if (in_array($action, $sensitive_actions)) {
            $severity = 'medium';
        }

        $this->log_event('ajax_request', [
            'action' => $action,
            'post_data' => $this->sanitize_post_data($_POST),
            'get_data' => $this->sanitize_post_data($_GET)
        ], $severity);
    }

    /**
     * Nettoie les données POST pour le logging
     */
    private function sanitize_post_data($data) {
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Ne pas logger les données sensibles
            if (in_array($key, ['nonce', 'password', 'pwd', 'user_pass'])) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_string($value) && strlen($value) > 100) {
                $sanitized[$key] = substr($value, 0, 100) . '...';
            } elseif (is_array($value)) {
                $sanitized[$key] = '[ARRAY]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Nettoie les données de log
     */
    private function sanitize_log_data($data) {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (in_array($key, ['password', 'pwd', 'user_pass', 'secret', 'key'])) {
                $data[$key] = '[REDACTED]';
            } elseif (is_string($value) && strlen($value) > 500) {
                $data[$key] = substr($value, 0, 500) . '...';
            }
        }

        return $data;
    }

    /**
     * Valide le niveau de sévérité
     */
    private function validate_severity($severity) {
        $valid_severities = ['low', 'medium', 'high', 'critical'];
        return in_array($severity, $valid_severities) ? $severity : 'low';
    }

    /**
     * Obtient l'IP du client
     */
    private function get_client_ip() {
        $headers = [
            'CF-CONNECTING-IP',
            'X-FORWARDED-FOR',
            'X-FORWARDED',
            'FORWARDED-FOR',
            'CLIENT_IP',
            'HTTP_CLIENT_IP'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Nettoie les anciens logs
     */
    public function cleanup_old_logs() {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->log_table;

        // Garder seulement les 30 derniers jours pour les logs de faible sévérité
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name
             WHERE severity IN ('low', 'medium')
             AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));

        // Garder seulement les 90 derniers jours pour les logs de haute sévérité
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name
             WHERE severity IN ('high', 'critical')
             AND created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        ));

        // Limiter le nombre total de logs
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count > $this->max_logs) {
            $delete_count = $count - $this->max_logs;
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $table_name ORDER BY created_at ASC LIMIT %d",
                $delete_count
            ));
        }
    }

    /**
     * Obtient les logs de sécurité
     */
    public function get_logs($filters = [], $limit = 100, $offset = 0) {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->log_table;
        $where = [];
        $values = [];

        // Filtres
        if (!empty($filters['event_type'])) {
            $where[] = 'event_type = %s';
            $values[] = $filters['event_type'];
        }

        if (!empty($filters['severity'])) {
            $where[] = 'severity = %s';
            $values[] = $filters['severity'];
        }

        if (!empty($filters['ip_address'])) {
            $where[] = 'ip_address = %s';
            $values[] = $filters['ip_address'];
        }

        if (!empty($filters['user_id'])) {
            $where[] = 'user_id = %d';
            $values[] = intval($filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'created_at >= %s';
            $values[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'created_at <= %s';
            $values[] = $filters['date_to'];
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name $where_clause
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d",
            array_merge($values, [$limit, $offset])
        );

        return $wpdb->get_results($query);
    }

    /**
     * Obtient les statistiques des logs
     */
    public function get_log_stats() {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->log_table;

        $stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total_logs,
                COUNT(CASE WHEN severity = 'low' THEN 1 END) as low_severity,
                COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium_severity,
                COUNT(CASE WHEN severity = 'high' THEN 1 END) as high_severity,
                COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical_severity,
                COUNT(DISTINCT ip_address) as unique_ips,
                COUNT(DISTINCT user_id) as unique_users,
                MAX(created_at) as latest_log
            FROM $table_name
        ");

        return $stats;
    }

    /**
     * Exporte les logs au format CSV
     */
    public function export_logs_csv($filters = []) {
        $logs = $this->get_logs($filters, 10000); // Maximum 10k logs

        $csv = "ID,Event Type,Severity,IP Address,User ID,Created At,Event Data\n";

        foreach ($logs as $log) {
            $event_data = json_decode($log->event_data, true);
            $data_string = '';
            if (is_array($event_data)) {
                $data_string = implode('; ', array_map(function($k, $v) {
                    return "$k: $v";
                }, array_keys($event_data), $event_data));
            }

            $csv .= sprintf(
                "%d,%s,%s,%s,%d,%s,\"%s\"\n",
                $log->id,
                $log->event_type,
                $log->severity,
                $log->ip_address,
                $log->user_id,
                $log->created_at,
                addslashes($data_string)
            );
        }

        return $csv;
    }
}

// ============================================================================
// MONITOR DE SÉCURITÉ
// ============================================================================

class PDF_Builder_Security_Monitor {

    private static $instance = null;
    private $alerts = [];
    private $thresholds = [];

    private function __construct() {
        $this->init_thresholds();
        $this->init_monitoring();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialise les seuils d'alerte
     */
    private function init_thresholds() {
        $this->thresholds = [
            'failed_logins_per_hour' => 5,
            'failed_logins_per_ip_hour' => 3,
            'ajax_requests_per_minute' => 30,
            'suspicious_requests_per_hour' => 10,
            'file_upload_attempts_per_hour' => 20
        ];
    }

    /**
     * Initialise le monitoring
     */
    private function init_monitoring() {
        // Vérifications périodiques
        add_action('wp', [$this, 'periodic_security_check']);

        // Monitoring en temps réel
        add_action('wp_login_failed', [$this, 'monitor_failed_login']);
        add_action('wp_ajax_pdf_builder_unified_dispatch', [$this, 'monitor_ajax_request']);
    }

    /**
     * Vérification périodique de sécurité
     */
    public function periodic_security_check() {
        // Vérifier les seuils toutes les 5 minutes
        $last_check = get_transient('pdf_builder_security_last_check');
        if ($last_check && (time() - $last_check) < 300) {
            return;
        }

        set_transient('pdf_builder_security_last_check', time(), 300);

        $this->check_failed_login_thresholds();
        $this->check_suspicious_activity();
        $this->check_file_upload_activity();

        // Traiter les alertes
        $this->process_alerts();
    }

    /**
     * Vérifie les seuils de connexions échouées
     */
    private function check_failed_login_thresholds() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_security_logs';

        // Échecs de connexion globaux (dernière heure)
        $failed_logins = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name
             WHERE event_type = 'failed_login'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        ));

        if ($failed_logins >= $this->thresholds['failed_logins_per_hour']) {
            $this->add_alert('high', 'multiple_failed_logins', [
                'count' => $failed_logins,
                'threshold' => $this->thresholds['failed_logins_per_hour'],
                'period' => '1 hour'
            ]);
        }

        // Échecs de connexion par IP (dernière heure)
        $failed_by_ip = $wpdb->get_results(
            "SELECT ip_address, COUNT(*) as count FROM $table_name
             WHERE event_type = 'failed_login'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
             GROUP BY ip_address
             HAVING count >= " . $this->thresholds['failed_logins_per_ip_hour']
        );

        foreach ($failed_by_ip as $row) {
            $this->add_alert('high', 'failed_logins_from_ip', [
                'ip' => $row->ip_address,
                'count' => $row->count,
                'threshold' => $this->thresholds['failed_logins_per_ip_hour']
            ]);
        }
    }

    /**
     * Vérifie les activités suspectes
     */
    private function check_suspicious_activity() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_security_logs';

        // Requêtes suspectes (dernière heure)
        $suspicious_requests = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name
             WHERE event_type IN ('rate_limit_exceeded', 'invalid_nonce', 'insufficient_permissions')
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        ));

        if ($suspicious_requests >= $this->thresholds['suspicious_requests_per_hour']) {
            $this->add_alert('medium', 'suspicious_activity_detected', [
                'count' => $suspicious_requests,
                'threshold' => $this->thresholds['suspicious_requests_per_hour']
            ]);
        }
    }

    /**
     * Vérifie l'activité d'upload de fichiers
     */
    private function check_file_upload_activity() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pdf_builder_security_logs';

        // Tentatives d'upload (dernière heure)
        $upload_attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name
             WHERE event_type = 'file_upload_attempt'
             AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        ));

        if ($upload_attempts >= $this->thresholds['file_upload_attempts_per_hour']) {
            $this->add_alert('medium', 'high_upload_activity', [
                'count' => $upload_attempts,
                'threshold' => $this->thresholds['file_upload_attempts_per_hour']
            ]);
        }
    }

    /**
     * Monitor les échecs de connexion
     */
    public function monitor_failed_login($username) {
        // Vérification immédiate pour une IP spécifique
        $ip = $this->get_client_ip();
        $transient_key = 'pdf_builder_failed_logins_' . md5($ip);

        $failed_count = get_transient($transient_key);
        if (!$failed_count) {
            $failed_count = 0;
        }

        $failed_count++;
        set_transient($transient_key, $failed_count, HOUR_IN_SECONDS);

        // Alerte immédiate si seuil dépassé
        if ($failed_count >= $this->thresholds['failed_logins_per_ip_hour']) {
            $this->add_alert('critical', 'brute_force_attempt', [
                'ip' => $ip,
                'failed_attempts' => $failed_count,
                'username' => $username
            ]);
        }
    }

    /**
     * Monitor les requêtes AJAX
     */
    public function monitor_ajax_request() {
        $ip = $this->get_client_ip();
        $transient_key = 'pdf_builder_ajax_requests_' . md5($ip);

        $request_count = get_transient($transient_key);
        if (!$request_count) {
            $request_count = 0;
        }

        $request_count++;
        set_transient($transient_key, $request_count, MINUTE_IN_SECONDS);

        // Alerte si seuil dépassé
        if ($request_count >= $this->thresholds['ajax_requests_per_minute']) {
            $this->add_alert('medium', 'high_ajax_activity', [
                'ip' => $ip,
                'requests' => $request_count,
                'threshold' => $this->thresholds['ajax_requests_per_minute']
            ]);
        }
    }

    /**
     * Ajoute une alerte
     */
    private function add_alert($severity, $type, $data) {
        $alert = [
            'id' => uniqid('alert_', true),
            'severity' => $severity,
            'type' => $type,
            'data' => $data,
            'timestamp' => current_time('timestamp'),
            'ip' => $this->get_client_ip()
        ];

        $this->alerts[] = $alert;

        // Log l'alerte
        $logger = PDF_Builder_Security_Logger::get_instance();
        $logger->log_event('security_alert', $alert, $severity);

        // Notification immédiate pour les alertes critiques
        if ($severity === 'critical') {
            $this->send_critical_alert_notification($alert);
        }
    }

    /**
     * Traite les alertes
     */
    private function process_alerts() {
        if (empty($this->alerts)) {
            return;
        }

        // Grouper les alertes similaires
        $grouped_alerts = [];
        foreach ($this->alerts as $alert) {
            $key = $alert['type'] . '_' . $alert['ip'];
            if (!isset($grouped_alerts[$key])) {
                $grouped_alerts[$key] = [];
            }
            $grouped_alerts[$key][] = $alert;
        }

        // Traiter chaque groupe
        foreach ($grouped_alerts as $alerts) {
            $latest_alert = end($alerts);

            // Escalade si multiple alertes du même type
            if (count($alerts) > 1) {
                $latest_alert['severity'] = $this->escalate_severity($latest_alert['severity']);
                $latest_alert['count'] = count($alerts);
            }

            // Actions selon le type d'alerte
            $this->take_alert_action($latest_alert);
        }

        // Vider les alertes traitées
        $this->alerts = [];
    }

    /**
     * Escalade la sévérité
     */
    private function escalate_severity($current_severity) {
        $escalation = [
            'low' => 'medium',
            'medium' => 'high',
            'high' => 'critical',
            'critical' => 'critical'
        ];

        return $escalation[$current_severity] ?? $current_severity;
    }

    /**
     * Prend des mesures selon l'alerte
     */
    private function take_alert_action($alert) {
        switch ($alert['type']) {
            case 'brute_force_attempt':
                $this->block_ip_temporarily($alert['ip'], 3600); // Bloquer 1h
                break;

            case 'high_ajax_activity':
                $this->throttle_ip($alert['ip'], 300); // Ralentir 5min
                break;

            case 'suspicious_activity_detected':
                $this->increase_monitoring($alert['ip']);
                break;
        }
    }

    /**
     * Bloque temporairement une IP
     */
    private function block_ip_temporarily($ip, $duration) {
        set_transient('pdf_builder_blocked_ip_' . md5($ip), time() + $duration, $duration);

        // Hook pour bloquer les requêtes
        add_action('init', function() use ($ip) {
            $client_ip = $this->get_client_ip();
            if ($client_ip === $ip) {
                $blocked_until = get_transient('pdf_builder_blocked_ip_' . md5($ip));
                if ($blocked_until && time() < $blocked_until) {
                    wp_die(__('Accès temporairement bloqué en raison d\'activité suspecte.', 'pdf-builder'), 403);
                }
            }
        });
    }

    /**
     * Ralenti une IP
     */
    private function throttle_ip($ip, $duration) {
        set_transient('pdf_builder_throttled_ip_' . md5($ip), time() + $duration, $duration);
    }

    /**
     * Augmente le monitoring pour une IP
     */
    private function increase_monitoring($ip) {
        set_transient('pdf_builder_monitored_ip_' . md5($ip), true, HOUR_IN_SECONDS);
    }

    /**
     * Envoie une notification d'alerte critique
     */
    private function send_critical_alert_notification($alert) {
        // Récupérer les admins
        $admins = get_users(['role' => 'administrator']);

        $subject = sprintf(__('Alerte de sécurité critique - PDF Builder Pro', 'pdf-builder'));
        $message = sprintf(
            __("Une alerte de sécurité critique a été détectée :\n\nType : %s\nIP : %s\nDétails : %s\n\nVérifiez immédiatement les logs de sécurité.", 'pdf-builder'),
            $alert['type'],
            $alert['ip'],
            json_encode($alert['data'], JSON_PRETTY_PRINT)
        );

        foreach ($admins as $admin) {
            wp_mail($admin->user_email, $subject, $message);
        }
    }

    /**
     * Obtient l'IP du client
     */
    private function get_client_ip() {
        $headers = [
            'CF-CONNECTING-IP',
            'X-FORWARDED-FOR',
            'X-FORWARDED',
            'FORWARDED-FOR',
            'CLIENT_IP',
            'HTTP_CLIENT_IP'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Obtient les alertes actives
     */
    public function get_active_alerts() {
        return $this->alerts;
    }

    /**
     * Obtient les seuils
     */
    public function get_thresholds() {
        return $this->thresholds;
    }

    /**
     * Met à jour un seuil
     */
    public function update_threshold($threshold, $value) {
        $this->thresholds[$threshold] = intval($value);
    }
}

// ============================================================================
// INITIALISATION
// ============================================================================

add_action('plugins_loaded', function() {
    PDF_Builder_Security_Logger::get_instance();
    PDF_Builder_Security_Monitor::get_instance();
});

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================

/**
 * Log un événement de sécurité
 */
function pdf_builder_log_security_event($event_type, $data = [], $severity = 'low') {
    $logger = PDF_Builder_Security_Logger::get_instance();
    $logger->log_event($event_type, $data, $severity);
}

/**
 * Obtient les logs de sécurité
 */
function pdf_builder_get_security_logs($filters = [], $limit = 100, $offset = 0) {
    $logger = PDF_Builder_Security_Logger::get_instance();
    return $logger->get_logs($filters, $limit, $offset);
}

/**
 * Obtient les statistiques des logs
 */
function pdf_builder_get_security_log_stats() {
    $logger = PDF_Builder_Security_Logger::get_instance();
    return $logger->get_log_stats();
}

/**
 * Exporte les logs au format CSV
 */
function pdf_builder_export_security_logs_csv($filters = []) {
    $logger = PDF_Builder_Security_Logger::get_instance();
    return $logger->export_logs_csv($filters);
}

/**
 * Obtient les alertes de sécurité actives
 */
function pdf_builder_get_security_alerts() {
    $monitor = PDF_Builder_Security_Monitor::get_instance();
    return $monitor->get_active_alerts();
}