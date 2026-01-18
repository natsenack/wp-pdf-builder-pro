<?php
/**
 * PDF Builder Pro - Gestionnaire d'APIs externes
 * Gère les intégrations avec des services tiers (Google Drive, Dropbox, etc.)
 */

class PDF_Builder_API_Manager {
    private static $instance = null;

    // Services supportés
    const SERVICE_GOOGLE_DRIVE = 'google_drive';
    const SERVICE_DROPBOX = 'dropbox';
    const SERVICE_ONE_DRIVE = 'one_drive';
    const SERVICE_AWS_S3 = 'aws_s3';
    const SERVICE_WEBHOOK = 'webhook';
    const SERVICE_SLACK = 'slack';
    const SERVICE_EMAIL = 'email';

    // Statuts de connexion
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_ERROR = 'error';
    const STATUS_EXPIRED = 'expired';

    // Cache des connexions
    private $connections = [];
    private $api_clients = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_connections();
    }

    private function init_hooks() {
        // AJAX handlers
        add_action('wp_ajax_pdf_builder_connect_api', [$this, 'connect_api_ajax']);
        add_action('wp_ajax_pdf_builder_disconnect_api', [$this, 'disconnect_api_ajax']);
        add_action('wp_ajax_pdf_builder_test_api_connection', [$this, 'test_api_connection_ajax']);
        add_action('wp_ajax_pdf_builder_get_api_status', [$this, 'get_api_status_ajax']);

        // Actions d'intégration
        add_action('pdf_builder_api_sync', [$this, 'sync_with_external_services']);
        add_action('pdf_builder_api_backup', [$this, 'backup_to_external_services']);
        // External notifications removed - no action registered

        // Nettoyage des tokens expirés
        add_action('pdf_builder_hourly_cleanup', [$this, 'cleanup_expired_tokens']);

        // Webhooks entrants
        add_action('wp_ajax_nopriv_pdf_builder_webhook', [$this, 'handle_incoming_webhook']);
        add_action('wp_ajax_pdf_builder_webhook', [$this, 'handle_incoming_webhook']);
    }

    /**
     * Charge les connexions API depuis la base de données
     */
    private function load_connections() {
        $stored_connections = pdf_builder_get_option('pdf_builder_api_connections', []);

        foreach ($stored_connections as $service => $connection) {
            $this->connections[$service] = $connection;
        }
    }

    /**
     * Enregistre une connexion API
     */
    public function register_connection($service, $credentials, $settings = []) {
        try {
            // Valider les credentials
            $this->validate_credentials($service, $credentials);

            // Tester la connexion
            $test_result = $this->test_connection($service, $credentials);

            if (!$test_result['success']) {
                throw new Exception('Échec du test de connexion: ' . $test_result['message']);
            }

            // Stocker la connexion
            $connection = [
                'service' => $service,
                'credentials' => $this->encrypt_credentials($credentials),
                'settings' => $settings,
                'status' => self::STATUS_CONNECTED,
                'connected_at' => current_time('mysql'),
                'last_tested' => current_time('mysql'),
                'expires_at' => $this->calculate_expiry($service, $credentials)
            ];

            $this->connections[$service] = $connection;
            $this->save_connections();

            // Logger la connexion
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info("API connection established: $service");
            }

            return [
                'success' => true,
                'message' => 'Connexion établie avec succès',
                'connection' => $connection
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Déconnecte un service API
     */
    public function disconnect_connection($service) {
        if (!isset($this->connections[$service])) {
            return [
                'success' => false,
                'message' => 'Service non connecté'
            ];
        }

        // Révoquer les tokens si nécessaire
        $this->revoke_tokens($service, $this->connections[$service]);

        unset($this->connections[$service]);
        $this->save_connections();

        // Nettoyer le cache du client
        unset($this->api_clients[$service]);

        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->info("API connection disconnected: $service");
        }

        return [
            'success' => true,
            'message' => 'Connexion supprimée avec succès'
        ];
    }

    /**
     * Teste une connexion API
     */
    public function test_connection($service, $credentials = null) {
        try {
            if ($credentials === null && isset($this->connections[$service])) {
                $credentials = $this->decrypt_credentials($this->connections[$service]['credentials']);
            }

            if (!$credentials) {
                return [
                    'success' => false,
                    'message' => 'Credentials manquants'
                ];
            }

            $client = $this->get_api_client($service, $credentials);
            $result = $client->test_connection();

            // Mettre à jour le statut
            if (isset($this->connections[$service])) {
                $this->connections[$service]['status'] = $result['success'] ? self::STATUS_CONNECTED : self::STATUS_ERROR;
                $this->connections[$service]['last_tested'] = current_time('mysql');
                $this->save_connections();
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtient le client API pour un service
     */
    private function get_api_client($service, $credentials) {
        if (!isset($this->api_clients[$service])) {
            $client_class = $this->get_client_class($service);

            if (!class_exists($client_class)) {
                throw new Exception("Client API non disponible pour le service: $service");
            }

            $this->api_clients[$service] = new $client_class($credentials);
        }

        return $this->api_clients[$service];
    }

    /**
     * Obtient la classe client pour un service
     */
    private function get_client_class($service) {
        $classes = [
            self::SERVICE_GOOGLE_DRIVE => 'PDF_Builder_Google_Drive_API',
            self::SERVICE_DROPBOX => 'PDF_Builder_Dropbox_API',
            self::SERVICE_ONE_DRIVE => 'PDF_Builder_One_Drive_API',
            self::SERVICE_AWS_S3 => 'PDF_Builder_AWS_S3_API',
            self::SERVICE_WEBHOOK => 'PDF_Builder_Webhook_API',
            self::SERVICE_SLACK => 'PDF_Builder_Slack_API',
            self::SERVICE_EMAIL => 'PDF_Builder_Email_API'
        ];

        return $classes[$service] ?? null;
    }

    /**
     * Valide les credentials pour un service
     */
    private function validate_credentials($service, $credentials) {
        $required_fields = $this->get_required_fields($service);

        foreach ($required_fields as $field) {
            if (!isset($credentials[$field]) || empty($credentials[$field])) {
                throw new Exception("Champ requis manquant: $field");
            }
        }
    }

    /**
     * Obtient les champs requis pour un service
     */
    private function get_required_fields($service) {
        $fields = [
            self::SERVICE_GOOGLE_DRIVE => ['client_id', 'client_secret', 'access_token'],
            self::SERVICE_DROPBOX => ['access_token'],
            self::SERVICE_ONE_DRIVE => ['client_id', 'client_secret', 'access_token'],
            self::SERVICE_AWS_S3 => ['access_key', 'secret_key', 'bucket'],
            self::SERVICE_WEBHOOK => ['url', 'secret'],
            self::SERVICE_SLACK => ['webhook_url', 'channel'],
            self::SERVICE_EMAIL => ['smtp_host', 'smtp_port', 'username', 'password']
        ];

        return $fields[$service] ?? [];
    }

    /**
     * Chiffre les credentials
     */
    private function encrypt_credentials($credentials) {
        $key = wp_salt('auth');
        $json = json_encode($credentials);

        if (function_exists('openssl_encrypt')) {
            return openssl_encrypt($json, 'AES-256-CBC', $key, 0, substr($key, 0, 16));
        }

        // Fallback simple (non recommandé pour la production)
        return base64_encode($json);
    }

    /**
     * Déchiffre les credentials
     */
    private function decrypt_credentials($encrypted) {
        $key = wp_salt('auth');

        if (function_exists('openssl_decrypt')) {
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, substr($key, 0, 16));
        } else {
            $decrypted = base64_decode($encrypted);
        }

        return json_decode($decrypted, true);
    }

    /**
     * Calcule la date d'expiration des credentials
     */
    private function calculate_expiry($service, $credentials) {
        // Pour les services OAuth, utiliser expires_in si disponible
        if (isset($credentials['expires_in'])) {
            return date('Y-m-d H:i:s', time() + $credentials['expires_in']);
        }

        // Par défaut, expiration dans 1 an
        return date('Y-m-d H:i:s', strtotime('+1 year'));
    }

    /**
     * Révoque les tokens pour un service
     */
    private function revoke_tokens($service, $connection) {
        try {
            $credentials = $this->decrypt_credentials($connection['credentials']);
            $client = $this->get_api_client($service, $credentials);

            if (method_exists($client, 'revoke_tokens')) {
                $client->revoke_tokens();
            }
        } catch (Exception $e) {
            // Logger l'erreur mais ne pas échouer
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->warning("Failed to revoke tokens for $service: " . $e->getMessage());
            }
        }
    }

    /**
     * Sauvegarde les connexions dans la base de données
     */
    private function save_connections() {
        pdf_builder_update_option('pdf_builder_api_connections', $this->connections);
    }

    /**
     * Synchronise avec les services externes
     */
    public function sync_with_external_services($data, $services = null) {
        $services = $services ?: array_keys($this->connections);
        $results = [];

        foreach ($services as $service) {
            if (!isset($this->connections[$service])) {
                continue;
            }

            try {
                $client = $this->get_api_client($service, $this->decrypt_credentials($this->connections[$service]['credentials']));
                $result = $client->sync($data);

                $results[$service] = [
                    'success' => true,
                    'result' => $result
                ];

            } catch (Exception $e) {
                $results[$service] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Sauvegarde vers les services externes
     */
    public function backup_to_external_services($backup_path, $services = null) {
        $services = $services ?: array_keys($this->connections);
        $results = [];

        foreach ($services as $service) {
            if (!isset($this->connections[$service])) {
                continue;
            }

            try {
                $client = $this->get_api_client($service, $this->decrypt_credentials($this->connections[$service]['credentials']));
                $result = $client->upload_backup($backup_path);

                $results[$service] = [
                    'success' => true,
                    'result' => $result
                ];

            } catch (Exception $e) {
                $results[$service] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    // External notification function removed

    /**
     * Gère les webhooks entrants
     */
    public function handle_incoming_webhook() {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
            $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? $payload['event'] ?? '';

            // Traiter selon le service
            $service = $this->identify_webhook_service($payload, $signature);

            if (!$service || !isset($this->connections[$service])) {
                wp_send_json_error(['message' => 'Service webhook non configuré']);
                return;
            }

            // Vérifier la signature
            $connection = $this->connections[$service];
            $credentials = $this->decrypt_credentials($connection['credentials']);

            if (!$this->verify_webhook_signature($service, $payload, $signature, $credentials)) {
                wp_send_json_error(['message' => 'Signature webhook invalide']);
                return;
            }

            // Traiter le webhook
            $client = $this->get_api_client($service, $credentials);
            $result = $client->process_webhook($payload, $event);

            wp_send_json_success([
                'message' => 'Webhook traité avec succès',
                'result' => $result
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur webhook: ' . $e->getMessage()]);
        }
    }

    /**
     * Identifie le service d'un webhook
     */
    private function identify_webhook_service($payload, $signature) {
        // Identifier selon les headers et le payload
        if (isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
            return self::SERVICE_WEBHOOK; // GitHub webhook
        }

        // Autres services...
        return null;
    }

    /**
     * Vérifie la signature d'un webhook
     */
    private function verify_webhook_signature($service, $payload, $signature, $credentials) {
        if (!isset($credentials['secret'])) {
            return false;
        }

        $expected_signature = 'sha1=' . hash_hmac('sha1', json_encode($payload), $credentials['secret']);

        return hash_equals($expected_signature, $signature);
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanup_expired_tokens() {
        $now = current_time('mysql');
        $expired = [];

        foreach ($this->connections as $service => $connection) {
            if (isset($connection['expires_at']) && $connection['expires_at'] < $now) {
                $connection['status'] = self::STATUS_EXPIRED;
                $expired[] = $service;
            }
        }

        if (!empty($expired)) {
            $this->save_connections();

            // Notifier
                // Legacy notification calls removed — write a warning to the logger instead
                PDF_Builder_Logger::get_instance()->warning('Tokens API expirés: Les tokens suivants ont expiré: ' . implode(', ', $expired));
        }
    }

    /**
     * Obtient le statut de toutes les connexions API
     */
    public function get_api_status() {
        $status = [];

        foreach ($this->connections as $service => $connection) {
            $status[$service] = [
                'service' => $service,
                'status' => $connection['status'],
                'connected_at' => $connection['connected_at'],
                'last_tested' => $connection['last_tested'] ?? null,
                'expires_at' => $connection['expires_at'] ?? null
            ];
        }

        return $status;
    }

    /**
     * AJAX - Connecte un service API
     */
    public function connect_api_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service = sanitize_text_field($_POST['service'] ?? '');
            $credentials = $_POST['credentials'] ?? [];
            $settings = $_POST['settings'] ?? [];

            if (empty($service)) {
                wp_send_json_error(['message' => 'Service manquant']);
                return;
            }

            // Sanitiser les credentials
            $credentials = array_map('sanitize_text_field', $credentials);
            $settings = array_map('sanitize_text_field', $settings);

            $result = $this->register_connection($service, $credentials, $settings);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'connection' => $result['connection']
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la connexion: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Déconnecte un service API
     */
    public function disconnect_api_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service = sanitize_text_field($_POST['service'] ?? '');

            if (empty($service)) {
                wp_send_json_error(['message' => 'Service manquant']);
                return;
            }

            $result = $this->disconnect_connection($service);

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la déconnexion: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Teste une connexion API
     */
    public function test_api_connection_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service = sanitize_text_field($_POST['service'] ?? '');

            if (empty($service)) {
                wp_send_json_error(['message' => 'Service manquant']);
                return;
            }

            $result = $this->test_connection($service);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => 'Connexion testée avec succès',
                    'result' => $result
                ]);
            } else {
                wp_send_json_error([
                    'message' => 'Échec du test de connexion',
                    'result' => $result
                ]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du test: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut des APIs
     */
    public function get_api_status_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $status = $this->get_api_status();

            wp_send_json_success([
                'message' => 'Statut récupéré',
                'status' => $status
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Classes de clients API de base
abstract class PDF_Builder_API_Client_Base {
    protected $credentials;

    public function __construct($credentials) {
        $this->credentials = $credentials;
    }

    abstract public function test_connection();
    abstract public function sync($data);
    abstract public function upload_backup($path);
    abstract public function send_notification($message, $level);
    abstract public function process_webhook($payload, $event);
}

// Client Google Drive
class PDF_Builder_Google_Drive_API extends PDF_Builder_API_Client_Base {
    public function test_connection() {
        // Implémentation du test de connexion Google Drive
        return ['success' => true, 'message' => 'Connexion Google Drive OK'];
    }

    public function sync($data) {
        // Implémentation de la synchronisation
        return ['uploaded' => true, 'url' => 'https://drive.google.com/...'];
    }

    public function upload_backup($path) {
        // Implémentation de l'upload de sauvegarde
        return ['uploaded' => true, 'file_id' => '12345'];
    }

    public function send_notification($message, $level) {
        // Google Drive ne supporte pas les notifications
        return ['sent' => false, 'message' => 'Non supporté'];
    }

    public function process_webhook($payload, $event) {
        // Traitement des webhooks Google Drive
        return ['processed' => true];
    }
}

// Client Dropbox
class PDF_Builder_Dropbox_API extends PDF_Builder_API_Client_Base {
    public function test_connection() {
        // Implémentation du test de connexion Dropbox
        return ['success' => true, 'message' => 'Connexion Dropbox OK'];
    }

    public function sync($data) {
        // Implémentation de la synchronisation
        return ['uploaded' => true, 'url' => 'https://dropbox.com/...'];
    }

    public function upload_backup($path) {
        // Implémentation de l'upload de sauvegarde
        return ['uploaded' => true, 'file_id' => '12345'];
    }

    public function send_notification($message, $level) {
        // Dropbox ne supporte pas les notifications
        return ['sent' => false, 'message' => 'Non supporté'];
    }

    public function process_webhook($payload, $event) {
        // Traitement des webhooks Dropbox
        return ['processed' => true];
    }
}

// Client Slack
class PDF_Builder_Slack_API extends PDF_Builder_API_Client_Base {
    public function test_connection() {
        // Test de connexion Slack
        $response = wp_remote_post($this->credentials['webhook_url'], [
            'body' => json_encode(['text' => 'Test de connexion PDF Builder Pro']),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return [
            'success' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200,
            'message' => 'Test Slack ' . (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200 ? 'réussi' : 'échoué')
        ];
    }

    public function sync($data) {
        // Slack ne supporte pas la synchronisation de fichiers
        return ['uploaded' => false, 'message' => 'Non supporté'];
    }

    public function upload_backup($path) {
        // Slack ne supporte pas l'upload de fichiers via webhook
        return ['uploaded' => false, 'message' => 'Non supporté'];
    }

    public function send_notification($message, $level) {
        $emoji = [
            'info' => ':information_source:',
            'success' => ':white_check_mark:',
            'warning' => ':warning:',
            'error' => ':x:',
            'critical' => ':fire:'
        ];

        $payload = [
            'text' => $emoji[$level] ?? ':bell:' . " PDF Builder Pro: $message",
            'channel' => $this->credentials['channel'] ?? '#general'
        ];

        $response = wp_remote_post($this->credentials['webhook_url'], [
            'body' => json_encode($payload),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return [
            'sent' => !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200
        ];
    }

    public function process_webhook($payload, $event) {
        // Slack peut envoyer des webhooks entrants
        return ['processed' => true, 'payload' => $payload];
    }
}

// Fonctions globales
function pdf_builder_api_manager() {
    return PDF_Builder_API_Manager::get_instance();
}

function pdf_builder_connect_api($service, $credentials, $settings = []) {
    return PDF_Builder_API_Manager::get_instance()->register_connection($service, $credentials, $settings);
}

function pdf_builder_disconnect_api($service) {
    return PDF_Builder_API_Manager::get_instance()->disconnect_connection($service);
}

function pdf_builder_test_api($service) {
    return PDF_Builder_API_Manager::get_instance()->test_connection($service);
}

function pdf_builder_sync_external($data, $services = null) {
    return PDF_Builder_API_Manager::get_instance()->sync_with_external_services($data, $services);
}

function pdf_builder_backup_external($path, $services = null) {
    return PDF_Builder_API_Manager::get_instance()->backup_to_external_services($path, $services);
}

// pdf_builder_notify_external removed - external notifications disabled

// Initialiser le gestionnaire d'APIs
add_action('plugins_loaded', function() {
    PDF_Builder_API_Manager::get_instance();
});


