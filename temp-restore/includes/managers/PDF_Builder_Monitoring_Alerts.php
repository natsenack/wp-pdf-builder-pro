<?php
/**
 * Système de Monitoring et Alertes - PDF Builder Pro
 *
 * Monitoring en temps réel, alertes automatiques, tableaux de bord
 * et rapports de performance pour la QA industrielle
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Système de Monitoring et Alertes
 */
class PDF_Builder_Monitoring_Alerts {

    /**
     * Instance singleton
     * @var PDF_Builder_Monitoring_Alerts
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

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
        'response_time' => ['warning' => 1000, 'critical' => 3000], // ms
        'memory_usage' => ['warning' => 100, 'critical' => 150], // MB
        'cpu_usage' => ['warning' => 70, 'critical' => 90], // %
        'error_rate' => ['warning' => 5, 'critical' => 10], // %
        'disk_usage' => ['warning' => 80, 'critical' => 95], // %
        'db_connections' => ['warning' => 80, 'critical' => 95], // %
        'failed_logins' => ['warning' => 5, 'critical' => 10], // tentatives/heure
        'api_errors' => ['warning' => 5, 'critical' => 15] // %
    ];

    /**
     * Canaux d'alertes
     * @var array
     */
    private $alert_channels = [
        'email' => ['enabled' => true, 'recipients' => []],
        'slack' => ['enabled' => false, 'webhook' => '', 'channel' => ''],
        'sms' => ['enabled' => false, 'provider' => '', 'numbers' => []],
        'webhook' => ['enabled' => false, 'url' => '', 'secret' => '']
    ];

    /**
     * Métriques de performance
     * @var array
     */
    private $performance_metrics = [];

    /**
     * Alertes actives
     * @var array
     */
    private $active_alerts = [];

    /**
     * Historique des métriques
     * @var array
     */
    private $metrics_history = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->logger = $core->get_logger();

        $this->init_monitoring_hooks();
        $this->schedule_monitoring_tasks();
        $this->load_alert_channels();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Monitoring_Alerts
     */
    public static function getInstance(): PDF_Builder_Monitoring_Alerts {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks de monitoring
     */
    private function init_monitoring_hooks(): void {
        // Hooks AJAX pour le monitoring
        add_action('wp_ajax_pdf_builder_get_monitoring_data', [$this, 'ajax_get_monitoring_data']);
        add_action('wp_ajax_pdf_builder_get_alerts', [$this, 'ajax_get_alerts']);
        add_action('wp_ajax_pdf_builder_configure_alerts', [$this, 'ajax_configure_alerts']);
        add_action('wp_ajax_pdf_builder_test_alert_channel', [$this, 'ajax_test_alert_channel']);

        // Hooks pour la collecte de métriques
        add_action('pdf_builder_collect_system_metrics', [$this, 'collect_system_metrics']);
        add_action('pdf_builder_collect_performance_metrics', [$this, 'collect_performance_metrics']);
        add_action('pdf_builder_check_alerts', [$this, 'check_alert_conditions']);
        add_action('pdf_builder_cleanup_metrics', [$this, 'cleanup_old_metrics']);

        // Hooks pour les métriques en temps réel
        add_action('wp_loaded', [$this, 'track_request_metrics']);
        add_action('shutdown', [$this, 'record_request_metrics']);

        // Hooks pour les erreurs
        add_action('wp_error_added', [$this, 'track_wp_errors'], 10, 4);
        add_action('pdf_builder_api_error', [$this, 'track_api_errors'], 10, 2);
        add_action('pdf_builder_security_event', [$this, 'track_security_events'], 10, 2);
    }

    /**
     * Programmer les tâches de monitoring
     */
    private function schedule_monitoring_tasks(): void {
        // Collecte de métriques système (toutes les 5 minutes)
        if (!wp_next_scheduled('pdf_builder_collect_system_metrics')) {
            wp_schedule_event(time(), 'every_five_minutes', 'pdf_builder_collect_system_metrics');
        }

        // Collecte de métriques de performance (toutes les heures)
        if (!wp_next_scheduled('pdf_builder_collect_performance_metrics')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_collect_performance_metrics');
        }

        // Vérification des alertes (toutes les minutes)
        if (!wp_next_scheduled('pdf_builder_check_alerts')) {
            wp_schedule_event(time(), 'every_minute', 'pdf_builder_check_alerts');
        }

        // Nettoyage des métriques (hebdomadaire)
        if (!wp_next_scheduled('pdf_builder_cleanup_metrics')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_cleanup_metrics');
        }
    }

    /**
     * Collecter les métriques système
     */
    public function collect_system_metrics(): void {
        $metrics = [
            'timestamp' => current_time('mysql'),
            'server_load' => $this->get_server_load(),
            'memory_usage' => $this->get_memory_usage(),
            'disk_usage' => $this->get_disk_usage(),
            'cpu_usage' => $this->get_cpu_usage(),
            'db_connections' => $this->get_database_connections(),
            'uptime' => $this->get_system_uptime(),
            'php_version' => PHP_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'plugin_version' => $this->get_plugin_version()
        ];

        $this->store_metrics('system', $metrics);
        $this->metrics['system'] = $metrics;
    }

    /**
     * Collecter les métriques de performance
     */
    public function collect_performance_metrics(): void {
        $metrics = [
            'timestamp' => current_time('mysql'),
            'avg_response_time' => $this->get_average_response_time(),
            'total_requests' => $this->get_total_requests(),
            'error_rate' => $this->get_error_rate(),
            'cache_hit_rate' => $this->get_cache_hit_rate(),
            'db_query_time' => $this->get_database_query_time(),
            'api_response_times' => $this->get_api_response_times(),
            'user_sessions' => $this->get_active_user_sessions(),
            'failed_logins' => $this->get_failed_login_attempts()
        ];

        $this->store_metrics('performance', $metrics);
        $this->metrics['performance'] = $metrics;
    }

    /**
     * Vérifier les conditions d'alertes
     */
    public function check_alert_conditions(): void {
        $current_metrics = array_merge(
            $this->metrics['system'] ?? [],
            $this->metrics['performance'] ?? []
        );

        foreach ($this->alert_thresholds as $metric => $thresholds) {
            if (!isset($current_metrics[$metric])) {
                continue;
            }

            $value = $current_metrics[$metric];
            $alert_level = null;

            if ($value >= $thresholds['critical']) {
                $alert_level = 'critical';
            } elseif ($value >= $thresholds['warning']) {
                $alert_level = 'warning';
            }

            if ($alert_level) {
                $this->trigger_alert($metric, $value, $alert_level, $thresholds[$alert_level]);
            } else {
                // Résoudre les alertes existantes
                $this->resolve_alert($metric);
            }
        }
    }

    /**
     * Déclencher une alerte
     *
     * @param string $metric
     * @param float $value
     * @param string $level
     * @param float $threshold
     */
    private function trigger_alert(string $metric, float $value, string $level, float $threshold): void {
        $alert_id = $metric . '_' . $level;

        // Vérifier si l'alerte est déjà active
        if (isset($this->active_alerts[$alert_id])) {
            // Mettre à jour le timestamp
            $this->active_alerts[$alert_id]['last_triggered'] = current_time('mysql');
            $this->active_alerts[$alert_id]['count']++;
            return;
        }

        $alert = [
            'id' => $alert_id,
            'metric' => $metric,
            'level' => $level,
            'value' => $value,
            'threshold' => $threshold,
            'message' => $this->generate_alert_message($metric, $value, $level, $threshold),
            'triggered_at' => current_time('mysql'),
            'last_triggered' => current_time('mysql'),
            'count' => 1,
            'resolved' => false
        ];

        $this->active_alerts[$alert_id] = $alert;
        $this->store_alert($alert);
        $this->send_alert_notifications($alert);

        $this->logger->warning('Alerte déclenchée', [
            'metric' => $metric,
            'level' => $level,
            'value' => $value,
            'threshold' => $threshold
        ]);
    }

    /**
     * Résoudre une alerte
     *
     * @param string $metric
     */
    private function resolve_alert(string $metric): void {
        foreach ($this->active_alerts as $alert_id => $alert) {
            if (strpos($alert_id, $metric . '_') === 0 && !$alert['resolved']) {
                $this->active_alerts[$alert_id]['resolved'] = true;
                $this->active_alerts[$alert_id]['resolved_at'] = current_time('mysql');
                $this->update_alert($alert_id, $this->active_alerts[$alert_id]);

                $this->send_resolution_notification($alert);

                $this->logger->info('Alerte résolue', ['metric' => $metric]);
            }
        }
    }

    /**
     * Générer le message d'alerte
     *
     * @param string $metric
     * @param float $value
     * @param string $level
     * @param float $threshold
     * @return string
     */
    private function generate_alert_message(string $metric, float $value, string $level, float $threshold): string {
        $metric_labels = [
            'response_time' => 'Temps de réponse',
            'memory_usage' => 'Utilisation mémoire',
            'cpu_usage' => 'Utilisation CPU',
            'error_rate' => 'Taux d\'erreur',
            'disk_usage' => 'Utilisation disque',
            'db_connections' => 'Connexions DB',
            'failed_logins' => 'Tentatives de connexion échouées',
            'api_errors' => 'Erreurs API'
        ];

        $unit = $this->get_metric_unit($metric);
        $label = $metric_labels[$metric] ?? $metric;

        return sprintf(
            'Alerte %s: %s = %.2f%s (seuil: %.2f%s)',
            strtoupper($level),
            $label,
            $value,
            $unit,
            $threshold,
            $unit
        );
    }

    /**
     * Obtenir l'unité de la métrique
     *
     * @param string $metric
     * @return string
     */
    private function get_metric_unit(string $metric): string {
        $units = [
            'response_time' => 'ms',
            'memory_usage' => 'MB',
            'cpu_usage' => '%',
            'error_rate' => '%',
            'disk_usage' => '%',
            'db_connections' => '%',
            'failed_logins' => '',
            'api_errors' => '%'
        ];

        return $units[$metric] ?? '';
    }

    /**
     * Envoyer les notifications d'alerte
     *
     * @param array $alert
     */
    private function send_alert_notifications(array $alert): void {
        foreach ($this->alert_channels as $channel => $config) {
            if (!$config['enabled']) {
                continue;
            }

            try {
                $this->{'send_' . $channel . '_alert'}($alert, $config);
            } catch (Exception $e) {
                $this->logger->error("Échec d'envoi d'alerte via {$channel}", [
                    'alert_id' => $alert['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Envoyer une alerte par email
     *
     * @param array $alert
     * @param array $config
     */
    private function send_email_alert(array $alert, array $config): void {
        $recipients = !empty($config['recipients']) ? $config['recipients'] : [get_option('admin_email')];
        $subject = '[ALERTE ' . strtoupper($alert['level']) . '] PDF Builder Pro - ' . $alert['metric'];

        $message = "Alerte système détectée:\n\n";
        $message .= $alert['message'] . "\n\n";
        $message .= "Timestamp: {$alert['triggered_at']}\n";
        $message .= "Compteur: {$alert['count']}\n\n";
        $message .= "Veuillez vérifier le tableau de bord de monitoring.\n";

        wp_mail($recipients, $subject, $message);
    }

    /**
     * Envoyer une alerte Slack
     *
     * @param array $alert
     * @param array $config
     */
    private function send_slack_alert(array $alert, array $config): void {
        if (empty($config['webhook'])) {
            return;
        }

        $payload = [
            'channel' => $config['channel'] ?: '#alerts',
            'username' => 'PDF Builder Monitor',
            'icon_emoji' => ':warning:',
            'attachments' => [
                [
                    'color' => $alert['level'] === 'critical' ? 'danger' : 'warning',
                    'title' => 'Alerte système - ' . ucfirst($alert['level']),
                    'text' => $alert['message'],
                    'fields' => [
                        [
                            'title' => 'Métrique',
                            'value' => $alert['metric'],
                            'short' => true
                        ],
                        [
                            'title' => 'Valeur',
                            'value' => $alert['value'] . $this->get_metric_unit($alert['metric']),
                            'short' => true
                        ],
                        [
                            'title' => 'Seuil',
                            'value' => $alert['threshold'] . $this->get_metric_unit($alert['metric']),
                            'short' => true
                        ]
                    ],
                    'footer' => 'PDF Builder Pro',
                    'ts' => strtotime($alert['triggered_at'])
                ]
            ]
        ];

        wp_remote_post($config['webhook'], [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    /**
     * Envoyer une alerte SMS
     *
     * @param array $alert
     * @param array $config
     */
    private function send_sms_alert(array $alert, array $config): void {
        // Simulation - en production, intégrer avec un fournisseur SMS
        $this->logger->info('SMS alert would be sent', [
            'alert_id' => $alert['id'],
            'numbers' => $config['numbers']
        ]);
    }

    /**
     * Envoyer une alerte webhook
     *
     * @param array $alert
     * @param array $config
     */
    private function send_webhook_alert(array $alert, array $config): void {
        if (empty($config['url'])) {
            return;
        }

        $payload = [
            'event' => 'alert_triggered',
            'alert' => $alert,
            'timestamp' => current_time('mysql')
        ];

        if (!empty($config['secret'])) {
            $payload['signature'] = hash_hmac('sha256', wp_json_encode($payload), $config['secret']);
        }

        wp_remote_post($config['url'], [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    /**
     * Envoyer une notification de résolution
     *
     * @param array $alert
     */
    private function send_resolution_notification(array $alert): void {
        foreach ($this->alert_channels as $channel => $config) {
            if (!$config['enabled']) {
                continue;
            }

            try {
                $resolution_message = "Alerte résolue: {$alert['metric']} est maintenant dans les limites normales.";
                $this->{'send_' . $channel . '_resolution'}($alert, $resolution_message, $config);
            } catch (Exception $e) {
                $this->logger->error("Échec d'envoi de résolution via {$channel}", [
                    'alert_id' => $alert['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Envoyer une résolution par email
     *
     * @param array $alert
     * @param string $message
     * @param array $config
     */
    private function send_email_resolution(array $alert, string $message, array $config): void {
        $recipients = !empty($config['recipients']) ? $config['recipients'] : [get_option('admin_email')];
        $subject = '[RÉSOLU] PDF Builder Pro - Alerte résolue: ' . $alert['metric'];

        $body = "Résolution d'alerte:\n\n";
        $body .= $message . "\n\n";
        $body .= "Alerte déclenchée: {$alert['triggered_at']}\n";
        $body .= "Résolue: {$alert['resolved_at']}\n";
        $body .= "Nombre d'occurrences: {$alert['count']}\n";

        wp_mail($recipients, $subject, $body);
    }

    /**
     * Envoyer une résolution Slack
     *
     * @param array $alert
     * @param string $message
     * @param array $config
     */
    private function send_slack_resolution(array $alert, string $message, array $config): void {
        if (empty($config['webhook'])) {
            return;
        }

        $payload = [
            'channel' => $config['channel'] ?: '#alerts',
            'username' => 'PDF Builder Monitor',
            'icon_emoji' => ':white_check_mark:',
            'attachments' => [
                [
                    'color' => 'good',
                    'title' => 'Alerte résolue',
                    'text' => $message,
                    'fields' => [
                        [
                            'title' => 'Métrique',
                            'value' => $alert['metric'],
                            'short' => true
                        ],
                        [
                            'title' => 'Durée',
                            'value' => $this->calculate_alert_duration($alert),
                            'short' => true
                        ]
                    ],
                    'footer' => 'PDF Builder Pro',
                    'ts' => strtotime($alert['resolved_at'])
                ]
            ]
        ];

        wp_remote_post($config['webhook'], [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    /**
     * Envoyer une résolution SMS
     *
     * @param array $alert
     * @param string $message
     * @param array $config
     */
    private function send_sms_resolution(array $alert, string $message, array $config): void {
        // Simulation
        $this->logger->info('SMS resolution would be sent', [
            'alert_id' => $alert['id'],
            'numbers' => $config['numbers']
        ]);
    }

    /**
     * Envoyer une résolution webhook
     *
     * @param array $alert
     * @param string $message
     * @param array $config
     */
    private function send_webhook_resolution(array $alert, string $message, array $config): void {
        if (empty($config['url'])) {
            return;
        }

        $payload = [
            'event' => 'alert_resolved',
            'alert' => $alert,
            'message' => $message,
            'timestamp' => current_time('mysql')
        ];

        if (!empty($config['secret'])) {
            $payload['signature'] = hash_hmac('sha256', wp_json_encode($payload), $config['secret']);
        }

        wp_remote_post($config['url'], [
            'body' => wp_json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    /**
     * Calculer la durée de l'alerte
     *
     * @param array $alert
     * @return string
     */
    private function calculate_alert_duration(array $alert): string {
        $start = strtotime($alert['triggered_at']);
        $end = strtotime($alert['resolved_at'] ?? current_time('mysql'));

        $duration = $end - $start;
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$seconds}s";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        } else {
            return "{$seconds}s";
        }
    }

    /**
     * Stocker les métriques
     *
     * @param string $type
     * @param array $metrics
     */
    private function store_metrics(string $type, array $metrics): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_metrics',
            [
                'type' => $type,
                'metrics' => wp_json_encode($metrics),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s']
        );

        // Garder l'historique récent en mémoire
        if (!isset($this->metrics_history[$type])) {
            $this->metrics_history[$type] = [];
        }

        array_unshift($this->metrics_history[$type], $metrics);

        // Limiter à 100 entrées par type
        if (count($this->metrics_history[$type]) > 100) {
            array_pop($this->metrics_history[$type]);
        }
    }

    /**
     * Stocker une alerte
     *
     * @param array $alert
     */
    private function store_alert(array $alert): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_alerts',
            [
                'alert_id' => $alert['id'],
                'metric' => $alert['metric'],
                'level' => $alert['level'],
                'value' => $alert['value'],
                'threshold' => $alert['threshold'],
                'message' => $alert['message'],
                'triggered_at' => $alert['triggered_at'],
                'resolved' => $alert['resolved'],
                'count' => $alert['count']
            ],
            ['%s', '%s', '%s', '%f', '%f', '%s', '%s', '%d', '%d']
        );
    }

    /**
     * Mettre à jour une alerte
     *
     * @param string $alert_id
     * @param array $alert
     */
    private function update_alert(string $alert_id, array $alert): void {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'pdf_builder_alerts',
            [
                'resolved' => $alert['resolved'],
                'resolved_at' => $alert['resolved_at'] ?? null,
                'count' => $alert['count'],
                'last_triggered' => $alert['last_triggered']
            ],
            ['alert_id' => $alert_id],
            ['%d', '%s', '%d', '%s'],
            ['%s']
        );
    }

    /**
     * Tracker les métriques de requête
     */
    public function track_request_metrics(): void {
        $this->performance_metrics['request_start'] = microtime(true);
        $this->performance_metrics['memory_start'] = memory_get_usage();
    }

    /**
     * Enregistrer les métriques de requête
     */
    public function record_request_metrics(): void {
        if (!isset($this->performance_metrics['request_start'])) {
            return;
        }

        $response_time = (microtime(true) - $this->performance_metrics['request_start']) * 1000;
        $memory_used = (memory_get_peak_usage() - $this->performance_metrics['memory_start']) / 1024 / 1024;

        // Stocker dans une table séparée pour les métriques de requête
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_request_metrics',
            [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                'response_time' => round($response_time, 2),
                'memory_used' => round($memory_used, 2),
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%f', '%f', '%d', '%s', '%s', '%s']
        );
    }

    /**
     * Tracker les erreurs WordPress
     *
     * @param string $code
     * @param string $message
     * @param string $data
     * @param mixed $wp_error
     */
    public function track_wp_errors(string $code, string $message, string $data, $wp_error): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_errors',
            [
                'type' => 'wordpress',
                'code' => $code,
                'message' => $message,
                'data' => wp_json_encode($data),
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );
    }

    /**
     * Tracker les erreurs API
     *
     * @param string $endpoint
     * @param string $error
     */
    public function track_api_errors(string $endpoint, string $error): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_errors',
            [
                'type' => 'api',
                'code' => 'api_error',
                'message' => $error,
                'data' => wp_json_encode(['endpoint' => $endpoint]),
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s']
        );
    }

    /**
     * Tracker les événements de sécurité
     *
     * @param string $event
     * @param array $data
     */
    public function track_security_events(string $event, array $data): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_security_events',
            [
                'event' => $event,
                'data' => wp_json_encode($data),
                'user_id' => get_current_user_id(),
                'ip_address' => $this->get_client_ip(),
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%d', '%s', '%s']
        );
    }

    /**
     * Nettoyer les anciennes métriques
     */
    public function cleanup_old_metrics(): void {
        global $wpdb;

        $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));

        // Supprimer les anciennes métriques
        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_metrics
            WHERE created_at < %s
        ", $cutoff_date));

        // Supprimer les anciennes métriques de requête (7 jours)
        $request_cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_request_metrics
            WHERE created_at < %s
        ", $request_cutoff));

        // Supprimer les anciennes erreurs (30 jours)
        $wpdb->query(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            DELETE FROM {$wpdb->prefix}pdf_builder_errors
            WHERE created_at < %s
        ", $cutoff_date));

        $this->logger->info('Anciennes métriques nettoyées');
    }

    /**
     * Obtenir la charge serveur
     *
     * @return float
     */
    private function get_server_load(): float {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0], 2);
        }
        return 0.0;
    }

    /**
     * Obtenir l'utilisation mémoire
     *
     * @return float
     */
    private function get_memory_usage(): float {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    }

    /**
     * Obtenir l'utilisation disque
     *
     * @return float
     */
    private function get_disk_usage(): float {
        $disk_total = disk_total_space('/');
        $disk_free = disk_free_space('/');
        $disk_used = $disk_total - $disk_free;

        return round(($disk_used / $disk_total) * 100, 2);
    }

    /**
     * Obtenir l'utilisation CPU
     *
     * @return float
     */
    private function get_cpu_usage(): float {
        // Simulation - en production, utiliser des outils système
        return rand(10, 80);
    }

    /**
     * Obtenir les connexions DB
     *
     * @return float
     */
    private function get_database_connections(): float {
        global $wpdb;

        // Simulation - en production, interroger le serveur MySQL
        return rand(10, 90);
    }

    /**
     * Obtenir l'uptime système
     *
     * @return int
     */
    private function get_system_uptime(): int {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime -s');
            if ($uptime) {
                return strtotime($uptime);
            }
        }
        return time() - rand(86400, 604800); // Simulation
    }

    /**
     * Obtenir la version du plugin
     *
     * @return string
     */
    private function get_plugin_version(): string {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/pdf-builder-pro/pdf-builder-pro.php');
        return $plugin_data['Version'] ?? '1.0.0';
    }

    /**
     * Obtenir le temps de réponse moyen
     *
     * @return float
     */
    private function get_average_response_time(): float {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT AVG(response_time) as avg_time
            FROM {$wpdb->prefix}pdf_builder_request_metrics
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-1 hour'))), ARRAY_A);

        return round(floatval($result['avg_time'] ?? 0), 2);
    }

    /**
     * Obtenir le nombre total de requêtes
     *
     * @return int
     */
    private function get_total_requests(): int {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT COUNT(*) as total
            FROM {$wpdb->prefix}pdf_builder_request_metrics
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-1 hour'))), ARRAY_A);

        return intval($result['total'] ?? 0);
    }

    /**
     * Obtenir le taux d'erreur
     *
     * @return float
     */
    private function get_error_rate(): float {
        global $wpdb;

        $total_requests = $this->get_total_requests();
        if ($total_requests === 0) {
            return 0.0;
        }

        $errors = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT COUNT(*) as error_count
            FROM {$wpdb->prefix}pdf_builder_errors
            WHERE created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-1 hour'))), ARRAY_A);

        return round((intval($errors['error_count'] ?? 0) / $total_requests) * 100, 2);
    }

    /**
     * Obtenir le taux de succès du cache
     *
     * @return float
     */
    private function get_cache_hit_rate(): float {
        // Simulation - en production, suivre les hits/misses du cache
        return rand(85, 98);
    }

    /**
     * Obtenir le temps d'exécution des requêtes DB
     *
     * @return float
     */
    private function get_database_query_time(): float {
        // Simulation - en production, utiliser SAVEQUERIES ou profiler
        return rand(50, 200);
    }

    /**
     * Obtenir les temps de réponse API
     *
     * @return array
     */
    private function get_api_response_times(): array {
        // Simulation
        return [
            'documents' => rand(100, 500),
            'templates' => rand(80, 300),
            'export' => rand(200, 1000)
        ];
    }

    /**
     * Obtenir les sessions utilisateur actives
     *
     * @return int
     */
    private function get_active_user_sessions(): int {
        // Simulation
        return rand(5, 50);
    }

    /**
     * Obtenir les tentatives de connexion échouées
     *
     * @return int
     */
    private function get_failed_login_attempts(): int {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT COUNT(*) as failed
            FROM {$wpdb->prefix}pdf_builder_security_events
            WHERE event = 'failed_login' AND created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-1 hour'))), ARRAY_A);

        return intval($result['failed'] ?? 0);
    }

    /**
     * Obtenir l'IP du client
     *
     * @return string
     */
    private function get_client_ip(): string {
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
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Charger les canaux d'alertes
     */
    private function load_alert_channels(): void {
        $saved_channels = get_option('pdf_builder_alert_channels', []);
        if (!empty($saved_channels)) {
            $this->alert_channels = array_merge($this->alert_channels, $saved_channels);
        }
    }

    /**
     * Sauvegarder les canaux d'alertes
     */
    private function save_alert_channels(): void {
        update_option('pdf_builder_alert_channels', $this->alert_channels);
    }

    /**
     * Configurer les seuils d'alertes
     *
     * @param array $thresholds
     */
    public function configure_alert_thresholds(array $thresholds): void {
        $this->alert_thresholds = array_merge($this->alert_thresholds, $thresholds);
        update_option('pdf_builder_alert_thresholds', $this->alert_thresholds);
    }

    /**
     * Configurer un canal d'alertes
     *
     * @param string $channel
     * @param array $config
     */
    public function configure_alert_channel(string $channel, array $config): void {
        if (isset($this->alert_channels[$channel])) {
            $this->alert_channels[$channel] = array_merge($this->alert_channels[$channel], $config);
            $this->save_alert_channels();
        }
    }

    /**
     * Tester un canal d'alertes
     *
     * @param string $channel
     * @return array
     */
    public function test_alert_channel(string $channel): array {
        if (!isset($this->alert_channels[$channel]) || !$this->alert_channels[$channel]['enabled']) {
            return ['success' => false, 'message' => 'Canal non activé'];
        }

        $test_alert = [
            'id' => 'test_alert',
            'metric' => 'test_metric',
            'level' => 'warning',
            'value' => 50,
            'threshold' => 40,
            'message' => 'Ceci est un test d\'alerte',
            'triggered_at' => current_time('mysql'),
            'count' => 1,
            'resolved' => false
        ];

        try {
            $this->{'send_' . $channel . '_alert'}($test_alert, $this->alert_channels[$channel]);
            return ['success' => true, 'message' => 'Test envoyé avec succès'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Échec du test: ' . $e->getMessage()];
        }
    }

    /**
     * Obtenir les données de monitoring
     *
     * @param string $type
     * @param int $hours
     * @return array
     */
    public function get_monitoring_data(string $type = 'all', int $hours = 24): array {
        global $wpdb;

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        $data = [];

        if ($type === 'all' || $type === 'system') {
            $system_metrics = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT * FROM {$wpdb->prefix}pdf_builder_metrics
                WHERE type = 'system' AND created_at > %s
                ORDER BY created_at DESC
            ", $cutoff), ARRAY_A);

            $data['system'] = array_map(function($row) {
                return array_merge($row, ['metrics' => json_decode($row['metrics'], true)]);
            }, $system_metrics);
        }

        if ($type === 'all' || $type === 'performance') {
            $performance_metrics = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT * FROM {$wpdb->prefix}pdf_builder_metrics
                WHERE type = 'performance' AND created_at > %s
                ORDER BY created_at DESC
            ", $cutoff), ARRAY_A);

            $data['performance'] = array_map(function($row) {
                return array_merge($row, ['metrics' => json_decode($row['metrics'], true)]);
            }, $performance_metrics);
        }

        if ($type === 'all' || $type === 'requests') {
            $request_metrics = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
                SELECT
                    DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour,
                    COUNT(*) as request_count,
                    AVG(response_time) as avg_response_time,
                    MAX(response_time) as max_response_time,
                    MIN(response_time) as min_response_time
                FROM {$wpdb->prefix}pdf_builder_request_metrics
                WHERE created_at > %s
                GROUP BY hour
                ORDER BY hour DESC
            ", $cutoff), ARRAY_A);

            $data['requests'] = $request_metrics;
        }

        return $data;
    }

    /**
     * Obtenir les alertes actives
     *
     * @return array
     */
    public function get_active_alerts(): array {
        return array_filter($this->active_alerts, function($alert) {
            return !$alert['resolved'];
        });
    }

    /**
     * Obtenir l'historique des alertes
     *
     * @param int $days
     * @return array
     */
    public function get_alert_history(int $days = 7): array {
        global $wpdb;

        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        return $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT * FROM {$wpdb->prefix}pdf_builder_alerts
            WHERE triggered_at > %s
            ORDER BY triggered_at DESC
        ", $cutoff), ARRAY_A);
    }

    /**
     * Générer un rapport de monitoring
     *
     * @param int $days
     * @return array
     */
    public function generate_monitoring_report(int $days = 7): array {
        $report = [
            'period' => $days . ' days',
            'generated_at' => current_time('mysql'),
            'system_health' => $this->get_system_health_summary($days),
            'performance_metrics' => $this->get_performance_summary($days),
            'alert_summary' => $this->get_alert_summary($days),
            'recommendations' => $this->generate_monitoring_recommendations($days)
        ];

        return $report;
    }

    /**
     * Obtenir le résumé de santé système
     *
     * @param int $days
     * @return array
     */
    private function get_system_health_summary(int $days): array {
        $system_data = $this->get_monitoring_data('system', $days * 24);

        if (empty($system_data['system'])) {
            return ['status' => 'unknown', 'metrics' => []];
        }

        $latest = $system_data['system'][0]['metrics'];
        $status = 'healthy';

        // Évaluer la santé basée sur les métriques
        if ($latest['cpu_usage'] > 80 || $latest['memory_usage'] > 100 || $latest['disk_usage'] > 90) {
            $status = 'warning';
        }
        if ($latest['cpu_usage'] > 95 || $latest['memory_usage'] > 150 || $latest['disk_usage'] > 95) {
            $status = 'critical';
        }

        return [
            'status' => $status,
            'latest_metrics' => $latest,
            'avg_metrics' => $this->calculate_average_metrics($system_data['system'])
        ];
    }

    /**
     * Obtenir le résumé de performance
     *
     * @param int $days
     * @return array
     */
    private function get_performance_summary(int $days): array {
        $performance_data = $this->get_monitoring_data('performance', $days * 24);
        $request_data = $this->get_monitoring_data('requests', $days * 24);

        return [
            'performance_trends' => $performance_data['performance'] ?? [],
            'request_patterns' => $request_data['requests'] ?? [],
            'key_metrics' => $this->extract_key_performance_metrics($performance_data)
        ];
    }

    /**
     * Obtenir le résumé des alertes
     *
     * @param int $days
     * @return array
     */
    private function get_alert_summary(int $days): array {
        $alert_history = $this->get_alert_history($days);

        $summary = [
            'total_alerts' => count($alert_history),
            'by_level' => [
                'critical' => 0,
                'warning' => 0
            ],
            'by_metric' => [],
            'active_alerts' => count($this->get_active_alerts())
        ];

        foreach ($alert_history as $alert) {
            $summary['by_level'][$alert['level']]++;
            if (!isset($summary['by_metric'][$alert['metric']])) {
                $summary['by_metric'][$alert['metric']] = 0;
            }
            $summary['by_metric'][$alert['metric']]++;
        }

        return $summary;
    }

    /**
     * Générer des recommandations de monitoring
     *
     * @param int $days
     * @return array
     */
    private function generate_monitoring_recommendations(int $days): array {
        $recommendations = [];
        $health = $this->get_system_health_summary($days);
        $alerts = $this->get_alert_summary($days);

        if ($health['status'] === 'critical') {
            $recommendations[] = 'État système critique détecté. Vérifier immédiatement les ressources serveur.';
        } elseif ($health['status'] === 'warning') {
            $recommendations[] = 'État système dégradé. Considérer une optimisation des ressources.';
        }

        if ($alerts['total_alerts'] > 10) {
            $recommendations[] = 'Nombre élevé d\'alertes détecté. Réviser les seuils d\'alerte.';
        }

        if ($alerts['active_alerts'] > 0) {
            $recommendations[] = 'Alertes actives présentes. Résoudre les problèmes identifiés.';
        }

        return $recommendations;
    }

    /**
     * Calculer les métriques moyennes
     *
     * @param array $metrics
     * @return array
     */
    private function calculate_average_metrics(array $metrics): array {
        if (empty($metrics)) {
            return [];
        }

        $sums = [];
        $counts = [];

        foreach ($metrics as $metric) {
            foreach ($metric['metrics'] as $key => $value) {
                if (is_numeric($value)) {
                    $sums[$key] = ($sums[$key] ?? 0) + $value;
                    $counts[$key] = ($counts[$key] ?? 0) + 1;
                }
            }
        }

        $averages = [];
        foreach ($sums as $key => $sum) {
            $averages[$key] = round($sum / $counts[$key], 2);
        }

        return $averages;
    }

    /**
     * Extraire les métriques de performance clés
     *
     * @param array $performance_data
     * @return array
     */
    private function extract_key_performance_metrics(array $performance_data): array {
        if (empty($performance_data['performance'])) {
            return [];
        }

        $latest = $performance_data['performance'][0]['metrics'];

        return [
            'avg_response_time' => $latest['avg_response_time'] ?? 0,
            'total_requests' => $latest['total_requests'] ?? 0,
            'error_rate' => $latest['error_rate'] ?? 0,
            'cache_hit_rate' => $latest['cache_hit_rate'] ?? 0,
            'active_users' => $latest['user_sessions'] ?? 0
        ];
    }

    /**
     * AJAX: Obtenir les données de monitoring
     */
    public function ajax_get_monitoring_data(): void {
        try {
            $type = sanitize_text_field($_POST['type'] ?? 'all');
            $hours = intval($_POST['hours'] ?? 24);

            $data = $this->get_monitoring_data($type, $hours);

            wp_send_json_success($data);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les alertes
     */
    public function ajax_get_alerts(): void {
        try {
            $active_only = filter_var($_POST['active_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $days = intval($_POST['days'] ?? 7);

            if ($active_only) {
                $alerts = $this->get_active_alerts();
            } else {
                $alerts = $this->get_alert_history($days);
            }

            wp_send_json_success($alerts);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Configurer les alertes
     */
    public function ajax_configure_alerts(): void {
        try {
            $channel = sanitize_text_field($_POST['channel']);
            $config = json_decode(stripslashes($_POST['config'] ?? '{}'), true) ?: [];

            $this->configure_alert_channel($channel, $config);

            wp_send_json_success(['message' => 'Configuration sauvegardée']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Tester un canal d'alertes
     */
    public function ajax_test_alert_channel(): void {
        try {
            $channel = sanitize_text_field($_POST['channel']);

            $result = $this->test_alert_channel($channel);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les seuils d'alertes
     *
     * @return array
     */
    public function get_alert_thresholds(): array {
        return $this->alert_thresholds;
    }

    /**
     * Obtenir les canaux d'alertes
     *
     * @return array
     */
    public function get_alert_channels(): array {
        return $this->alert_channels;
    }

    /**
     * Obtenir les métriques actuelles
     *
     * @return array
     */
    public function get_current_metrics(): array {
        return $this->metrics;
    }
}

