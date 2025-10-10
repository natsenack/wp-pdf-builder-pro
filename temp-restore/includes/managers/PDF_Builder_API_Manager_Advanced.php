<?php
/**
 * Gestionnaire d'API REST Avancée - PDF Builder Pro
 *
 * API REST complète avec authentification, rate limiting, webhooks
 * Inspiré de PDFApi.php du plugin de référence
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire d'API REST Avancée
 */
class PDF_Builder_API_Manager_Advanced extends PDF_Builder_API_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_API_Manager_Advanced
     */
    private static $instance = null;

    /**
     * Gestionnaire de collaboration
     * @var PDF_Builder_Collaboration_Manager
     */
    private $collaboration_manager;

    /**
     * Gestionnaire d'export
     * @var PDF_Builder_Export_Manager
     */
    private $export_manager;

    /**
     * Gestionnaire d'analytics
     * @var PDF_Builder_Analytics_Manager
     */
    private $analytics_manager;

    /**
     * Webhooks enregistrés
     * @var array
     */
    private $webhooks = [];

    /**
     * Rate limiting
     * @var array
     */
    private $rate_limits = [];

    /**
     * Clés API
     * @var array
     */
    private $api_keys = [];

    /**
     * Constructeur privé
     */
    private function __construct() {
        parent::__construct();

        $core = PDF_Builder_Core::getInstance();
        $this->collaboration_manager = $core->get_collaboration_manager();
        $this->export_manager = $core->get_export_manager();
        $this->analytics_manager = $core->get_analytics_manager();

        $this->init_advanced_hooks();
        $this->load_api_keys();
        // Enregistrer les endpoints sur rest_api_init au lieu d'ici
        add_action('rest_api_init', [$this, 'register_advanced_endpoints']);
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_API_Manager_Advanced
     */
    public static function getInstance(): PDF_Builder_API_Manager_Advanced {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks avancés
     */
    private function init_advanced_hooks(): void {
        // Hooks pour les webhooks
        add_action('pdf_builder_document_generated', [$this, 'trigger_webhook'], 10, 2);
        add_action('pdf_builder_comment_added', [$this, 'trigger_webhook'], 10, 2);
        add_action('pdf_builder_workflow_status_changed', [$this, 'trigger_webhook'], 10, 3);

        // Hooks pour le rate limiting
        add_action('wp_ajax_nopriv_pdf_builder_api_request', [$this, 'check_rate_limit'], 1);
        add_action('wp_ajax_pdf_builder_api_request', [$this, 'check_rate_limit'], 1);

        // Nettoyage des logs API
        add_action('pdf_builder_cleanup_api_logs', [$this, 'cleanup_api_logs']);

        if (!wp_next_scheduled('pdf_builder_cleanup_api_logs')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cleanup_api_logs');
        }
    }

    /**
     * Enregistrer les endpoints avancés
     */
    private function register_advanced_endpoints(): void {
        // Endpoints de collaboration
        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/share', [
            'methods' => 'POST',
            'callback' => [$this, 'api_share_document'],
            'permission_callback' => [$this, 'check_api_permissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ]
            ]
        ]);

        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/comments', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'api_handle_comments'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/versions', [
            'methods' => 'GET',
            'callback' => [$this, 'api_get_versions'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/versions/(?P<version>\d+)/restore', [
            'methods' => 'POST',
            'callback' => [$this, 'api_restore_version'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/workflow', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'api_handle_workflow'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        // Endpoints d'export
        register_rest_route('pdf-builder/v2', '/documents/(?P<id>\d+)/export', [
            'methods' => 'POST',
            'callback' => [$this, 'api_export_document'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        // Endpoints d'analytics
        register_rest_route('pdf-builder/v2', '/analytics/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'api_get_analytics'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        register_rest_route('pdf-builder/v2', '/analytics/reports', [
            'methods' => 'GET',
            'callback' => [$this, 'api_get_reports'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        // Endpoints de webhooks
        register_rest_route('pdf-builder/v2', '/webhooks', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'api_handle_webhooks'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        register_rest_route('pdf-builder/v2', '/webhooks/(?P<id>\d+)', [
            'methods' => ['PUT', 'DELETE'],
            'callback' => [$this, 'api_manage_webhook'],
            'permission_callback' => [$this, 'check_api_permissions']
        ]);

        // Endpoints d'intégration
        register_rest_route('pdf-builder/v2', '/integrations/zapier', [
            'methods' => 'POST',
            'callback' => [$this, 'api_zapier_integration'],
            'permission_callback' => '__return_true' // Webhook public pour Zapier
        ]);

        register_rest_route('pdf-builder/v2', '/integrations/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'api_generic_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Vérifier les permissions API
     *
     * @param WP_REST_Request $request
     * @return bool
     */
    public function check_api_permissions(WP_REST_Request $request): bool {
        // Vérifier l'authentification API
        $api_key = $request->get_header('X-API-Key');
        $api_secret = $request->get_header('X-API-Secret');

        if (!$api_key || !$api_secret) {
            return false;
        }

        // Vérifier la clé API
        if (!$this->validate_api_key($api_key, $api_secret)) {
            return false;
        }

        // Vérifier le rate limiting
        $client_ip = $this->get_client_ip();
        if ($this->is_rate_limited($client_ip)) {
            return false;
        }

        return true;
    }

    /**
     * Charger les clés API
     */
    private function load_api_keys(): void {
        $keys = get_option('pdf_builder_api_keys', []);
        $this->api_keys = is_array($keys) ? $keys : [];
    }

    /**
     * Valider une clé API
     *
     * @param string $api_key
     * @param string $api_secret
     * @return bool
     */
    private function validate_api_key(string $api_key, string $api_secret): bool {
        if (!isset($this->api_keys[$api_key])) {
            return false;
        }

        $stored_secret = $this->api_keys[$api_key]['secret'] ?? '';
        $is_active = $this->api_keys[$api_key]['active'] ?? false;

        return $is_active && hash_equals($stored_secret, $api_secret);
    }

    /**
     * Générer une nouvelle clé API
     *
     * @param string $name
     * @param array $permissions
     * @return array
     */
    public function generate_api_key(string $name, array $permissions = []): array {
        $api_key = 'pdf_' . wp_generate_uuid4();
        $api_secret = wp_generate_uuid4();

        $this->api_keys[$api_key] = [
            'name' => $name,
            'secret' => $api_secret,
            'permissions' => $permissions,
            'active' => true,
            'created_at' => current_time('mysql'),
            'last_used' => null,
            'usage_count' => 0
        ];

        update_option('pdf_builder_api_keys', $this->api_keys);

        return [
            'api_key' => $api_key,
            'api_secret' => $api_secret,
            'permissions' => $permissions
        ];
    }

    /**
     * Vérifier le rate limiting
     */
    public function check_rate_limit(): void {
        $client_ip = $this->get_client_ip();

        if ($this->is_rate_limited($client_ip)) {
            wp_send_json_error([
                'message' => 'Rate limit exceeded',
                'retry_after' => 60
            ], 429);
        }

        $this->record_api_request($client_ip);
    }

    /**
     * Vérifier si un client est rate limité
     *
     * @param string $client_ip
     * @return bool
     */
    private function is_rate_limited(string $client_ip): bool {
        $key = "rate_limit_{$client_ip}";
        $requests = get_transient($key) ?: [];

        // Nettoyer les anciennes requêtes (plus de 60 secondes)
        $requests = array_filter($requests, function($timestamp) {
            return (time() - $timestamp) < 60;
        });

        // Limite: 100 requêtes par minute
        return count($requests) >= 100;
    }

    /**
     * Enregistrer une requête API
     *
     * @param string $client_ip
     */
    private function record_api_request(string $client_ip): void {
        $key = "rate_limit_{$client_ip}";
        $requests = get_transient($key) ?: [];
        $requests[] = time();

        // Garder seulement les 100 dernières requêtes
        $requests = array_slice($requests, -100);

        set_transient($key, $requests, 60);
    }

    /**
     * Obtenir l'IP du client
     *
     * @return string
     */
    private function get_client_ip(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
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
     * API: Partager un document
     */
    public function api_share_document(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');
        $user_id = $request->get_param('user_id');
        $permission = $request->get_param('permission');

        try {
            $this->collaboration_manager->share_document($document_id, $user_id, $permission, get_current_user_id());

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Document partagé avec succès'
            ], 200);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Gérer les commentaires
     */
    public function api_handle_comments(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');

        if ($request->get_method() === 'GET') {
            $comments = $this->collaboration_manager->get_comments($document_id, get_current_user_id());

            return new WP_REST_Response([
                'success' => true,
                'comments' => $comments
            ], 200);

        } elseif ($request->get_method() === 'POST') {
            $comment = $request->get_param('comment');
            $metadata = $request->get_param('metadata') ?: [];

            $comment_id = $this->collaboration_manager->add_comment($document_id, get_current_user_id(), $comment, $metadata);

            return new WP_REST_Response([
                'success' => true,
                'comment_id' => $comment_id,
                'message' => 'Commentaire ajouté'
            ], 201);
        }

        return new WP_REST_Response(['error' => 'Method not allowed'], 405);
    }

    /**
     * API: Obtenir les versions d'un document
     */
    public function api_get_versions(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');

        $versions = $this->collaboration_manager->get_version_history($document_id, get_current_user_id());

        return new WP_REST_Response([
            'success' => true,
            'versions' => $versions
        ], 200);
    }

    /**
     * API: Restaurer une version
     */
    public function api_restore_version(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');
        $version_number = $request->get_param('version');

        try {
            $this->collaboration_manager->restore_version($document_id, $version_number, get_current_user_id());

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Version restaurée avec succès'
            ], 200);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Gérer le workflow
     */
    public function api_handle_workflow(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');

        if ($request->get_method() === 'GET') {
            $history = $this->collaboration_manager->get_workflow_history($document_id, get_current_user_id());

            return new WP_REST_Response([
                'success' => true,
                'workflow_history' => $history
            ], 200);

        } elseif ($request->get_method() === 'POST') {
            $status = $request->get_param('status');
            $comment = $request->get_param('comment') ?: '';

            $this->collaboration_manager->update_workflow_status($document_id, $status, get_current_user_id(), $comment);

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Statut du workflow mis à jour'
            ], 200);
        }

        return new WP_REST_Response(['error' => 'Method not allowed'], 405);
    }

    /**
     * API: Exporter un document
     */
    public function api_export_document(WP_REST_Request $request): WP_REST_Response {
        $document_id = $request->get_param('id');
        $format = $request->get_param('format');
        $options = $request->get_param('options') ?: [];

        try {
            $result = $this->export_manager->export_document($document_id, $format, $options);

            return new WP_REST_Response([
                'success' => true,
                'export' => $result
            ], 200);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Obtenir les analytics
     */
    public function api_get_analytics(WP_REST_Request $request): WP_REST_Response {
        $period = $request->get_param('period') ?: 'month';

        $stats = $this->analytics_manager->get_detailed_stats($period);

        return new WP_REST_Response([
            'success' => true,
            'analytics' => $stats
        ], 200);
    }

    /**
     * API: Obtenir les rapports
     */
    public function api_get_reports(WP_REST_Request $request): WP_REST_Response {
        // Logique pour récupérer les rapports d'analytics
        // À implémenter selon les besoins

        return new WP_REST_Response([
            'success' => true,
            'reports' => []
        ], 200);
    }

    /**
     * API: Gérer les webhooks
     */
    public function api_handle_webhooks(WP_REST_Request $request): WP_REST_Response {
        if ($request->get_method() === 'GET') {
            return new WP_REST_Response([
                'success' => true,
                'webhooks' => $this->webhooks
            ], 200);

        } elseif ($request->get_method() === 'POST') {
            $url = $request->get_param('url');
            $events = $request->get_param('events') ?: [];
            $secret = $request->get_param('secret') ?: wp_generate_uuid4();

            $webhook_id = $this->register_webhook($url, $events, $secret);

            return new WP_REST_Response([
                'success' => true,
                'webhook_id' => $webhook_id,
                'secret' => $secret
            ], 201);
        }

        return new WP_REST_Response(['error' => 'Method not allowed'], 405);
    }

    /**
     * API: Gérer un webhook spécifique
     */
    public function api_manage_webhook(WP_REST_Request $request): WP_REST_Response {
        $webhook_id = $request->get_param('id');

        if ($request->get_method() === 'PUT') {
            $url = $request->get_param('url');
            $events = $request->get_param('events');
            $active = $request->get_param('active');

            $this->update_webhook($webhook_id, $url, $events, $active);

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Webhook mis à jour'
            ], 200);

        } elseif ($request->get_method() === 'DELETE') {
            $this->unregister_webhook($webhook_id);

            return new WP_REST_Response([
                'success' => true,
                'message' => 'Webhook supprimé'
            ], 200);
        }

        return new WP_REST_Response(['error' => 'Method not allowed'], 405);
    }

    /**
     * API: Intégration Zapier
     */
    public function api_zapier_integration(WP_REST_Request $request): WP_REST_Response {
        $event = $request->get_param('event');
        $data = $request->get_param('data');

        // Traiter l'événement Zapier
        do_action("pdf_builder_zapier_{$event}", $data);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Événement Zapier traité'
        ], 200);
    }

    /**
     * API: Webhook générique
     */
    public function api_generic_webhook(WP_REST_Request $request): WP_REST_Response {
        $event = $request->get_param('event');
        $data = $request->get_param('data');
        $signature = $request->get_header('X-Hub-Signature-256');

        // Vérifier la signature si nécessaire
        if ($signature) {
            // Logique de vérification de signature
        }

        // Traiter le webhook
        do_action("pdf_builder_webhook_{$event}", $data);

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Webhook traité'
        ], 200);
    }

    /**
     * Enregistrer un webhook
     *
     * @param string $url
     * @param array $events
     * @param string $secret
     * @return string
     */
    public function register_webhook(string $url, array $events, string $secret): string {
        $webhook_id = 'wh_' . wp_generate_uuid4();

        $this->webhooks[$webhook_id] = [
            'url' => $url,
            'events' => $events,
            'secret' => $secret,
            'active' => true,
            'created_at' => current_time('mysql'),
            'last_triggered' => null,
            'failure_count' => 0
        ];

        update_option('pdf_builder_webhooks', $this->webhooks);

        return $webhook_id;
    }

    /**
     * Mettre à jour un webhook
     *
     * @param string $webhook_id
     * @param string $url
     * @param array $events
     * @param bool $active
     */
    public function update_webhook(string $webhook_id, string $url, array $events, bool $active): void {
        if (!isset($this->webhooks[$webhook_id])) {
            throw new Exception('Webhook introuvable');
        }

        $this->webhooks[$webhook_id]['url'] = $url;
        $this->webhooks[$webhook_id]['events'] = $events;
        $this->webhooks[$webhook_id]['active'] = $active;

        update_option('pdf_builder_webhooks', $this->webhooks);
    }

    /**
     * Désenregistrer un webhook
     *
     * @param string $webhook_id
     */
    public function unregister_webhook(string $webhook_id): void {
        if (isset($this->webhooks[$webhook_id])) {
            unset($this->webhooks[$webhook_id]);
            update_option('pdf_builder_webhooks', $this->webhooks);
        }
    }

    /**
     * Déclencher un webhook
     *
     * @param string $event
     * @param mixed $data
     */
    public function trigger_webhook(string $event, $data = null): void {
        foreach ($this->webhooks as $webhook_id => &$webhook) {
            if (!$webhook['active'] || !in_array($event, $webhook['events'])) {
                continue;
            }

            $this->send_webhook_request($webhook, $event, $data);
        }
    }

    /**
     * Envoyer une requête webhook
     *
     * @param array $webhook
     * @param string $event
     * @param mixed $data
     */
    private function send_webhook_request(array &$webhook, string $event, $data): void {
        $payload = [
            'event' => $event,
            'timestamp' => current_time('timestamp'),
            'data' => $data
        ];

        $args = [
            'body' => wp_json_encode($payload),
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PDF Builder Pro Webhook',
                'X-Webhook-Signature' => $this->generate_webhook_signature($payload, $webhook['secret'])
            ],
            'timeout' => 30,
            'blocking' => false // Asynchrone
        ];

        $response = wp_remote_post($webhook['url'], $args);

        if (is_wp_error($response)) {
            $webhook['failure_count']++;
            $this->logger->error('Webhook delivery failed', [
                'webhook_id' => array_search($webhook, $this->webhooks),
                'url' => $webhook['url'],
                'error' => $response->get_error_message()
            ]);
        } else {
            $webhook['last_triggered'] = current_time('mysql');
            $webhook['failure_count'] = 0;
        }

        // Désactiver le webhook après 5 échecs consécutifs
        if ($webhook['failure_count'] >= 5) {
            $webhook['active'] = false;
            update_option('pdf_builder_webhooks', $this->webhooks);
        }
    }

    /**
     * Générer une signature de webhook
     *
     * @param array $payload
     * @param string $secret
     * @return string
     */
    private function generate_webhook_signature(array $payload, string $secret): string {
        $payload_json = wp_json_encode($payload);
        return 'sha256=' . hash_hmac('sha256', $payload_json, $secret);
    }

    /**
     * Nettoyer les logs API
     */
    public function cleanup_api_logs(): void {
        global $wpdb;

        // Supprimer les logs de plus de 30 jours
        $wpdb->query("
            DELETE FROM {$wpdb->prefix}pdf_builder_api_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");

        $this->logger->info('API logs cleaned up');
    }

    /**
     * Obtenir les statistiques API
     */
    public function get_api_stats(): array {
        global $wpdb;

        $stats = $wpdb->get_row("
            SELECT
                COUNT(*) as total_requests,
                COUNT(CASE WHEN response_code >= 400 THEN 1 END) as error_requests,
                AVG(response_time) as avg_response_time,
                COUNT(DISTINCT client_ip) as unique_clients
            FROM {$wpdb->prefix}pdf_builder_api_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");

        return [
            'total_requests' => intval($stats->total_requests ?? 0),
            'error_requests' => intval($stats->error_requests ?? 0),
            'success_rate' => $stats->total_requests > 0 ?
                round((1 - ($stats->error_requests / $stats->total_requests)) * 100, 2) : 0,
            'avg_response_time' => round(floatval($stats->avg_response_time ?? 0), 2),
            'unique_clients' => intval($stats->unique_clients ?? 0),
            'active_webhooks' => count(array_filter($this->webhooks, function($wh) {
                return $wh['active'];
            }))
        ];
    }
}

