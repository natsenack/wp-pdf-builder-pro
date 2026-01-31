<?php
/**
 * PDF Builder Pro - Gestion des intégrations externes
 * Connecte le plugin avec des services externes (Google Drive, Dropbox, etc.)
 */

class PDF_Builder_Integration_Manager {
    private static $instance = null;

    // Services supportés
    const SERVICE_GOOGLE_DRIVE = 'google_drive';
    const SERVICE_DROPBOX = 'dropbox';
    const SERVICE_ONEDRIVE = 'onedrive';
    const SERVICE_AWS_S3 = 'aws_s3';
    const SERVICE_SLACK = 'slack';
    const SERVICE_WEBHOOK = 'webhook';
    const SERVICE_ZAPIER = 'zapier';
    const SERVICE_MAILCHIMP = 'mailchimp';
    const SERVICE_HUBSPOT = 'hubspot';
    const SERVICE_SALESFORCE = 'salesforce';

    // Statuts d'intégration
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_ERROR = 'error';
    const STATUS_PENDING = 'pending';

    // Configuration des services
    const SERVICES_CONFIG = [
        self::SERVICE_GOOGLE_DRIVE => [
            'name' => 'Google Drive',
            'description' => 'Sauvegarder et synchroniser les PDFs avec Google Drive',
            'auth_type' => 'oauth2',
            'scopes' => ['https://www.googleapis.com/auth/drive.file'],
            'client_id_required' => true,
            'client_secret_required' => true,
            'redirect_uri_required' => true
        ],
        self::SERVICE_DROPBOX => [
            'name' => 'Dropbox',
            'description' => 'Sauvegarder les PDFs dans Dropbox',
            'auth_type' => 'oauth2',
            'scopes' => ['files.content.write', 'files.content.read'],
            'app_key_required' => true,
            'app_secret_required' => true
        ],
        self::SERVICE_ONEDRIVE => [
            'name' => 'OneDrive',
            'description' => 'Sauvegarder les PDFs dans OneDrive',
            'auth_type' => 'oauth2',
            'scopes' => ['Files.ReadWrite.All'],
            'client_id_required' => true,
            'client_secret_required' => true
        ],
        self::SERVICE_AWS_S3 => [
            'name' => 'Amazon S3',
            'description' => 'Stockage cloud scalable pour les PDFs',
            'auth_type' => 'api_key',
            'access_key_required' => true,
            'secret_key_required' => true,
            'region_required' => true,
            'bucket_required' => true
        ],
        self::SERVICE_SLACK => [
            'name' => 'Slack',
            'description' => 'Intégrations Slack et Webhook',
            'auth_type' => 'oauth2',
            'scopes' => ['chat:write', 'files:write'],
            'bot_token_required' => true,
            'channel_required' => true
        ],
        self::SERVICE_WEBHOOK => [
            'name' => 'Webhooks',
            'description' => 'Intégrations personnalisées via webhooks',
            'auth_type' => 'none',
            'url_required' => true,
            'secret_required' => false
        ],
        self::SERVICE_ZAPIER => [
            'name' => 'Zapier',
            'description' => 'Automatisations avec Zapier',
            'auth_type' => 'webhook',
            'webhook_url_required' => true
        ],
        self::SERVICE_MAILCHIMP => [
            'name' => 'Mailchimp',
            'description' => 'Intégration avec les listes Mailchimp',
            'auth_type' => 'api_key',
            'api_key_required' => true,
            'server_prefix_required' => true
        ],
        self::SERVICE_HUBSPOT => [
            'name' => 'HubSpot',
            'description' => 'Synchronisation avec HubSpot CRM',
            'auth_type' => 'oauth2',
            'scopes' => ['contacts', 'files'],
            'client_id_required' => true,
            'client_secret_required' => true
        ],
        self::SERVICE_SALESFORCE => [
            'name' => 'Salesforce',
            'description' => 'Intégration avec Salesforce CRM',
            'auth_type' => 'oauth2',
            'scopes' => ['api', 'refresh_token'],
            'client_id_required' => true,
            'client_secret_required' => true,
            'instance_url_required' => true
        ]
    ];

    // Cache des intégrations
    private $integrations_cache = [];
    private $connections_cache = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->load_integrations_cache();
    }

    private function init_hooks() {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_connect_service', [$this, 'connect_service_ajax']);
        add_action('wp_ajax_pdf_builder_disconnect_service', [$this, 'disconnect_service_ajax']);
        add_action('wp_ajax_pdf_builder_test_connection', [$this, 'test_connection_ajax']);
        add_action('wp_ajax_pdf_builder_get_integration_status', [$this, 'get_integration_status_ajax']);
        add_action('wp_ajax_pdf_builder_save_integration_settings', [$this, 'save_integration_settings_ajax']);

        // Actions d'administration
        add_action('admin_init', [$this, 'handle_oauth_callback']);
        add_action('admin_menu', [$this, 'add_integrations_menu']);
        add_action('admin_notices', [$this, 'display_integration_notices']);

        // Actions programmées
        add_action('pdf_builder_check_integration_health', [$this, 'check_integration_health']);
        add_action('pdf_builder_refresh_oauth_tokens', [$this, 'refresh_oauth_tokens']);

        // Filtres
        add_filter('pdf_builder_storage_backends', [$this, 'add_cloud_storage_backends']);
        // Notification channels filter removed - notification system deleted

        // Nettoyage
        add_action('pdf_builder_monthly_cleanup', [$this, 'cleanup_integration_data']);
    }

    /**
     * Charge le cache des intégrations
     */
    private function load_integrations_cache() {
        $this->integrations_cache = pdf_builder_get_option('pdf_builder_integrations', []);
        $this->connections_cache = pdf_builder_get_option('pdf_builder_integration_connections', []);
    }

    /**
     * Ajoute le menu des intégrations
     */
    public function add_integrations_menu() {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Intégrations externes', 'integration'),
            pdf_builder_translate('Intégrations', 'integration'),
            'manage_options',
            'pdf-builder-integrations',
            [$this, 'render_integrations_page']
        );
    }

    /**
     * Rend la page des intégrations
     */
    public function render_integrations_page() {
        if (!current_user_can('manage_options')) {
            wp_die(pdf_builder_translate('Accès refusé', 'integration'));
        }

        $services = $this->get_available_services();
        $connections = $this->get_connections_status();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/integrations-management.php';
    }

    /**
     * Obtient les services disponibles
     */
    public function get_available_services() {
        $services = [];

        foreach (self::SERVICES_CONFIG as $service_id => $config) {
            $services[$service_id] = [
                'id' => $service_id,
                'name' => $config['name'],
                'description' => $config['description'],
                'auth_type' => $config['auth_type'],
                'status' => $this->get_connection_status($service_id),
                'config' => $this->get_service_config($service_id),
                'last_sync' => $this->get_last_sync_time($service_id)
            ];
        }

        return $services;
    }

    /**
     * Obtient le statut des connexions
     */
    public function get_connections_status() {
        $connections = [];

        foreach (array_keys(self::SERVICES_CONFIG) as $service_id) {
            $connections[$service_id] = $this->get_connection_status($service_id);
        }

        return $connections;
    }

    /**
     * Obtient le statut d'une connexion
     */
    public function get_connection_status($service_id) {
        if (!isset($this->connections_cache[$service_id])) {
            return self::STATUS_DISCONNECTED;
        }

        $connection = $this->connections_cache[$service_id];

        // Vérifier si le token est expiré pour OAuth2
        if ($this->is_oauth_token_expired($service_id)) {
            return self::STATUS_ERROR;
        }

        return $connection['status'] ?? self::STATUS_DISCONNECTED;
    }

    /**
     * Obtient la configuration d'un service
     */
    public function get_service_config($service_id) {
        return $this->integrations_cache[$service_id] ?? [];
    }

    /**
     * Obtient la dernière synchronisation
     */
    public function get_last_sync_time($service_id) {
        $connection = $this->connections_cache[$service_id] ?? [];

        return $connection['last_sync'] ?? null;
    }

    /**
     * Connecte un service
     */
    public function connect_service($service_id, $config = []) {
        try {
            if (!isset(self::SERVICES_CONFIG[$service_id])) {
                throw new Exception(pdf_builder_translate('Service non supporté', 'integration'));
            }

            $service_config = self::SERVICES_CONFIG[$service_id];

            // Validation de la configuration
            $this->validate_service_config($service_id, $config);

            // Connexion selon le type d'authentification
            switch ($service_config['auth_type']) {
                case 'oauth2':
                    return $this->connect_oauth2_service($service_id, $config);

                case 'api_key':
                    return $this->connect_api_key_service($service_id, $config);

                case 'webhook':
                    return $this->connect_webhook_service($service_id, $config);

                case 'none':
                    return $this->connect_no_auth_service($service_id, $config);

                default:
                    throw new Exception(pdf_builder_translate('Type d\'authentification non supporté', 'integration'));
            }

        } catch (Exception $e) {
            // Logger l'erreur
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->error('Service connection failed', [
                    'service' => $service_id,
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Déconnecte un service
     */
    public function disconnect_service($service_id) {
        try {
            if (!isset($this->connections_cache[$service_id])) {
                throw new Exception(pdf_builder_translate('Service non connecté', 'integration'));
            }

            // Nettoyer les données de connexion
            unset($this->connections_cache[$service_id]);
            pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

            // Nettoyer la configuration
            unset($this->integrations_cache[$service_id]);
            pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

            // Logger la déconnexion
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info('Service disconnected', [
                    'service' => $service_id
                ]);
            }

            return [
                'success' => true,
                'message' => pdf_builder_translate('Service déconnecté avec succès', 'integration')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Teste une connexion
     */
    public function test_connection($service_id) {
        try {
            if ($this->get_connection_status($service_id) !== self::STATUS_CONNECTED) {
                throw new Exception(pdf_builder_translate('Service non connecté', 'integration'));
            }

            $config = $this->get_service_config($service_id);
            $connection = $this->connections_cache[$service_id];

            // Test selon le service
            switch ($service_id) {
                case self::SERVICE_GOOGLE_DRIVE:
                    return $this->test_google_drive_connection($config, $connection);

                case self::SERVICE_DROPBOX:
                    return $this->test_dropbox_connection($config, $connection);

                case self::SERVICE_SLACK:
                    return $this->test_slack_connection($config, $connection);

                case self::SERVICE_WEBHOOK:
                    return $this->test_webhook_connection($config);

                default:
                    // Test générique
                    return $this->test_generic_connection($service_id, $config, $connection);
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Connecte un service OAuth2
     */
    private function connect_oauth2_service($service_id, $config) {
        // Générer l'URL d'autorisation
        $auth_url = $this->generate_oauth2_auth_url($service_id, $config);

        // Stocker temporairement la configuration
        $temp_config = array_merge($config, [
            'service_id' => $service_id,
            'timestamp' => time()
        ]);

        pdf_builder_update_option('pdf_builder_oauth_temp_config', $temp_config);

        return [
            'success' => true,
            'auth_url' => $auth_url,
            'message' => pdf_builder_translate('Redirection vers l\'autorisation OAuth2', 'integration')
        ];
    }

    /**
     * Connecte un service avec clé API
     */
    private function connect_api_key_service($service_id, $config) {
        // Sauvegarder la configuration
        $this->integrations_cache[$service_id] = $config;
        pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

        // Marquer comme connecté
        $this->connections_cache[$service_id] = [
            'status' => self::STATUS_CONNECTED,
            'connected_at' => time(),
            'last_sync' => time()
        ];
        pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

        return [
            'success' => true,
            'message' => pdf_builder_translate('Service connecté avec succès', 'integration')
        ];
    }

    /**
     * Connecte un service webhook
     */
    private function connect_webhook_service($service_id, $config) {
        // Valider l'URL du webhook
        if (!filter_var($config['webhook_url'], FILTER_VALIDATE_URL)) {
            throw new Exception(pdf_builder_translate('URL du webhook invalide', 'integration'));
        }

        // Sauvegarder la configuration
        $this->integrations_cache[$service_id] = $config;
        pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

        // Marquer comme connecté
        $this->connections_cache[$service_id] = [
            'status' => self::STATUS_CONNECTED,
            'connected_at' => time(),
            'last_sync' => time()
        ];
        pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

        return [
            'success' => true,
            'message' => pdf_builder_translate('Webhook configuré avec succès', 'integration')
        ];
    }

    /**
     * Connecte un service sans authentification
     */
    private function connect_no_auth_service($service_id, $config) {
        // Sauvegarder la configuration
        $this->integrations_cache[$service_id] = $config;
        pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

        // Marquer comme connecté
        $this->connections_cache[$service_id] = [
            'status' => self::STATUS_CONNECTED,
            'connected_at' => time(),
            'last_sync' => time()
        ];
        pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

        return [
            'success' => true,
            'message' => pdf_builder_translate('Service configuré avec succès', 'integration')
        ];
    }

    /**
     * Génère l'URL d'autorisation OAuth2
     */
    private function generate_oauth2_auth_url($service_id, $config) {
        $base_urls = [
            self::SERVICE_GOOGLE_DRIVE => 'https://accounts.google.com/o/oauth2/v2/auth',
            self::SERVICE_DROPBOX => 'https://www.dropbox.com/oauth2/authorize',
            self::SERVICE_ONEDRIVE => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            self::SERVICE_SLACK => 'https://slack.com/oauth/v2/authorize',
            self::SERVICE_HUBSPOT => 'https://app.hubspot.com/oauth/authorize',
            self::SERVICE_SALESFORCE => 'https://login.salesforce.com/services/oauth2/authorize'
        ];

        if (!isset($base_urls[$service_id])) {
            throw new Exception(pdf_builder_translate('Service OAuth2 non supporté', 'integration'));
        }

        $service_config = self::SERVICES_CONFIG[$service_id];
        $redirect_uri = admin_url('admin.php?page=pdf-builder-integrations&oauth_callback=1&service=' . $service_id);

        $params = [
            'client_id' => $config['client_id'] ?? '',
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => implode(' ', $service_config['scopes']),
            'state' => wp_create_nonce('pdf_builder_oauth_' . $service_id)
        ];

        return $base_urls[$service_id] . '?' . http_build_query($params);
    }

    /**
     * Gère le callback OAuth2
     */
    public function handle_oauth_callback() {
        if (!isset($_GET['oauth_callback']) || !isset($_GET['service'])) {
            return;
        }

        $service_id = sanitize_key($_GET['service']);
        $code = sanitize_text_field($_GET['code'] ?? '');
        $state = sanitize_text_field($_GET['state'] ?? '');

        // Vérifier le nonce
        if (!wp_verify_nonce($state, 'pdf_builder_oauth_' . $service_id)) {
            wp_die(pdf_builder_translate('État OAuth invalide', 'integration'));
        }

        try {
            // Récupérer la configuration temporaire
            $temp_config = pdf_builder_get_option('pdf_builder_oauth_temp_config', []);

            if (empty($temp_config) || $temp_config['service_id'] !== $service_id) {
                throw new Exception(pdf_builder_translate('Configuration OAuth manquante', 'integration'));
            }

            // Échanger le code contre un token
            $token_data = $this->exchange_oauth_code($service_id, $code, $temp_config);

            // Sauvegarder la configuration et les tokens
            $this->integrations_cache[$service_id] = $temp_config;
            pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

            $this->connections_cache[$service_id] = [
                'status' => self::STATUS_CONNECTED,
                'connected_at' => time(),
                'last_sync' => time(),
                'tokens' => $token_data
            ];
            pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

            // Nettoyer la configuration temporaire
            delete_option('pdf_builder_oauth_temp_config');

            // Rediriger avec succès
            wp_redirect(admin_url('admin.php?page=pdf-builder-integrations&oauth_success=1&service=' . $service_id));
            exit;

        } catch (Exception $e) {
            // Logger l'erreur
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->error('OAuth callback failed', [
                    'service' => $service_id,
                    'error' => $e->getMessage()
                ]);
            }

            // Rediriger avec erreur
            wp_redirect(admin_url('admin.php?page=pdf-builder-integrations&oauth_error=1&service=' . $service_id));
            exit;
        }
    }

    /**
     * Échange un code OAuth contre un token
     */
    private function exchange_oauth_code($service_id, $code, $config) {
        $token_urls = [
            self::SERVICE_GOOGLE_DRIVE => 'https://oauth2.googleapis.com/token',
            self::SERVICE_DROPBOX => 'https://api.dropboxapi.com/oauth2/token',
            self::SERVICE_ONEDRIVE => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            self::SERVICE_SLACK => 'https://slack.com/api/oauth.v2.access',
            self::SERVICE_HUBSPOT => 'https://api.hubapi.com/oauth/v1/token',
            self::SERVICE_SALESFORCE => 'https://login.salesforce.com/services/oauth2/token'
        ];

        if (!isset($token_urls[$service_id])) {
            throw new Exception(pdf_builder_translate('Service OAuth2 non supporté', 'integration'));
        }

        $redirect_uri = admin_url('admin.php?page=pdf-builder-integrations&oauth_callback=1&service=' . $service_id);

        $post_data = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_uri
        ];

        $response = wp_remote_post($token_urls[$service_id], [
            'body' => $post_data,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur lors de l\'échange du code OAuth', 'integration'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['access_token'])) {
            throw new Exception(pdf_builder_translate('Token d\'accès manquant', 'integration'));
        }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => time() + ($data['expires_in'] ?? 3600),
            'token_type' => $data['token_type'] ?? 'Bearer'
        ];
    }

    /**
     * Vérifie si un token OAuth est expiré
     */
    private function is_oauth_token_expired($service_id) {
        $connection = $this->connections_cache[$service_id] ?? [];

        if (empty($connection['tokens']['expires_at'])) {
            return false;
        }

        return time() > $connection['tokens']['expires_at'];
    }

    /**
     * Rafraîchit les tokens OAuth
     */
    public function refresh_oauth_tokens() {
        foreach ($this->connections_cache as $service_id => $connection) {
            if ($this->is_oauth_token_expired($service_id) && !empty($connection['tokens']['refresh_token'])) {
                try {
                    $this->refresh_oauth_token($service_id);
                } catch (Exception $e) {
                    // Logger l'erreur mais continuer
                    if (class_exists('PDF_Builder_Logger')) {
                        PDF_Builder_Logger::get_instance()->error('Token refresh failed', [
                            'service' => $service_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Rafraîchit un token OAuth spécifique
     */
    private function refresh_oauth_token($service_id) {
        $connection = $this->connections_cache[$service_id];
        $config = $this->get_service_config($service_id);

        $refresh_urls = [
            self::SERVICE_GOOGLE_DRIVE => 'https://oauth2.googleapis.com/token',
            self::SERVICE_DROPBOX => 'https://api.dropboxapi.com/oauth2/token',
            self::SERVICE_ONEDRIVE => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            self::SERVICE_SLACK => 'https://slack.com/api/oauth.v2.access',
            self::SERVICE_HUBSPOT => 'https://api.hubapi.com/oauth/v1/token',
            self::SERVICE_SALESFORCE => 'https://login.salesforce.com/services/oauth2/token'
        ];

        if (!isset($refresh_urls[$service_id])) {
            throw new Exception(pdf_builder_translate('Service OAuth2 non supporté', 'integration'));
        }

        $post_data = [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'refresh_token' => $connection['tokens']['refresh_token'],
            'grant_type' => 'refresh_token'
        ];

        $response = wp_remote_post($refresh_urls[$service_id], [
            'body' => $post_data,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur lors du rafraîchissement du token', 'integration'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['access_token'])) {
            throw new Exception(pdf_builder_translate('Nouveau token d\'accès manquant', 'integration'));
        }

        // Mettre à jour les tokens
        $this->connections_cache[$service_id]['tokens'] = [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $connection['tokens']['refresh_token'],
            'expires_at' => time() + ($data['expires_in'] ?? 3600),
            'token_type' => $data['token_type'] ?? 'Bearer'
        ];

        pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);
    }

    /**
     * Valide la configuration d'un service
     */
    private function validate_service_config($service_id, $config) {
        $service_config = self::SERVICES_CONFIG[$service_id];

        $required_fields = [];
        foreach ($service_config as $key => $value) {
            if ($value === true && strpos($key, '_required') !== false) {
                $field_name = str_replace('_required', '', $key);
                $required_fields[] = $field_name;
            }
        }

        foreach ($required_fields as $field) {
            if (empty($config[$field])) {
                $field_names = [
                    'client_id' => pdf_builder_translate('ID Client', 'integration'),
                    'client_secret' => pdf_builder_translate('Secret Client', 'integration'),
                    'app_key' => pdf_builder_translate('Clé d\'application', 'integration'),
                    'app_secret' => pdf_builder_translate('Secret d\'application', 'integration'),
                    'access_key' => pdf_builder_translate('Clé d\'accès', 'integration'),
                    'secret_key' => pdf_builder_translate('Clé secrète', 'integration'),
                    'region' => pdf_builder_translate('Région', 'integration'),
                    'bucket' => pdf_builder_translate('Bucket', 'integration'),
                    'bot_token' => pdf_builder_translate('Token Bot', 'integration'),
                    'channel' => pdf_builder_translate('Canal', 'integration'),
                    'url' => pdf_builder_translate('URL', 'integration'),
                    'webhook_url' => pdf_builder_translate('URL Webhook', 'integration'),
                    'api_key' => pdf_builder_translate('Clé API', 'integration'),
                    'server_prefix' => pdf_builder_translate('Préfixe serveur', 'integration'),
                    'instance_url' => pdf_builder_translate('URL d\'instance', 'integration')
                ];

                $field_label = $field_names[$field] ?? $field;
                throw new Exception(sprintf(pdf_builder_translate('%s est requis', 'integration'), $field_label));
            }
        }
    }

    /**
     * Teste la connexion Google Drive
     */
    private function test_google_drive_connection($config, $connection) {
        $response = wp_remote_get('https://www.googleapis.com/drive/v3/files?pageSize=1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $connection['tokens']['access_token']
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur de connexion Google Drive', 'integration'));
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            throw new Exception(pdf_builder_translate('Token Google Drive invalide', 'integration'));
        }

        return [
            'success' => true,
            'message' => pdf_builder_translate('Connexion Google Drive réussie', 'integration')
        ];
    }

    /**
     * Teste la connexion Dropbox
     */
    private function test_dropbox_connection($config, $connection) {
        $response = wp_remote_post('https://api.dropboxapi.com/2/users/get_current_account', [
            'headers' => [
                'Authorization' => 'Bearer ' . $connection['tokens']['access_token'],
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur de connexion Dropbox', 'integration'));
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            throw new Exception(pdf_builder_translate('Token Dropbox invalide', 'integration'));
        }

        return [
            'success' => true,
            'message' => pdf_builder_translate('Connexion Dropbox réussie', 'integration')
        ];
    }

    /**
     * Teste la connexion Slack
     */
    private function test_slack_connection($config, $connection) {
        $response = wp_remote_post('https://slack.com/api/auth.test', [
            'headers' => [
                'Authorization' => 'Bearer ' . $connection['tokens']['access_token']
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur de connexion Slack', 'integration'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data['ok'])) {
            throw new Exception(pdf_builder_translate('Token Slack invalide', 'integration'));
        }

        return [
            'success' => true,
            'message' => pdf_builder_translate('Connexion Slack réussie', 'integration')
        ];
    }

    /**
     * Teste la connexion webhook
     */
    private function test_webhook_connection($config) {
        $response = wp_remote_post($config['webhook_url'], [
            'body' => wp_json_encode([
                'test' => true,
                'timestamp' => time()
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PDF Builder Pro Webhook Test'
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur de connexion webhook', 'integration'));
        }

        return [
            'success' => true,
            'message' => pdf_builder_translate('Connexion webhook réussie', 'integration')
        ];
    }

    /**
     * Test générique de connexion
     */
    private function test_generic_connection($service_id, $config, $connection) {
        // Test de base - vérifier que la configuration est présente
        if (empty($config)) {
            throw new Exception(pdf_builder_translate('Configuration manquante', 'integration'));
        }

        return [
            'success' => true,
            'message' => pdf_builder_translate('Connexion testée avec succès', 'integration')
        ];
    }

    /**
     * Vérifie la santé des intégrations
     */
    public function check_integration_health() {
        foreach ($this->connections_cache as $service_id => $connection) {
            if ($connection['status'] === self::STATUS_CONNECTED) {
                try {
                    $this->test_connection($service_id);
                } catch (Exception $e) {
                    // Marquer comme erreur
                    $this->connections_cache[$service_id]['status'] = self::STATUS_ERROR;
                    $this->connections_cache[$service_id]['last_error'] = $e->getMessage();
                    $this->connections_cache[$service_id]['error_time'] = time();

                    pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);

                    // Logger l'erreur
                    if (class_exists('PDF_Builder_Logger')) {
                        PDF_Builder_Logger::get_instance()->warning('Integration health check failed', [
                            'service' => $service_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Ajoute les backends de stockage cloud
     */
    public function add_cloud_storage_backends($backends) {
        $cloud_services = [
            self::SERVICE_GOOGLE_DRIVE => 'Google Drive',
            self::SERVICE_DROPBOX => 'Dropbox',
            self::SERVICE_ONEDRIVE => 'OneDrive',
            self::SERVICE_AWS_S3 => 'Amazon S3'
        ];

        foreach ($cloud_services as $service_id => $name) {
            if ($this->get_connection_status($service_id) === self::STATUS_CONNECTED) {
                $backends[$service_id] = [
                    'name' => $name,
                    'handler' => [$this, 'handle_cloud_storage_' . str_replace('_', '', $service_id)]
                ];
            }
        }

        return $backends;
    }

    // Notification channels handler removed — notification system deleted

    /**
     * Nettoie les données d'intégration
     */
    public function cleanup_integration_data() {
        // Supprimer les configurations temporaires expirées
        $temp_config = pdf_builder_get_option('pdf_builder_oauth_temp_config', []);

        if (!empty($temp_config['timestamp']) && (time() - $temp_config['timestamp']) > 3600) {
            delete_option('pdf_builder_oauth_temp_config');
        }

        // Nettoyer les erreurs anciennes
        foreach ($this->connections_cache as $service_id => $connection) {
            if (isset($connection['error_time']) && (time() - $connection['error_time']) > 86400) {
                unset($this->connections_cache[$service_id]['last_error']);
                unset($this->connections_cache[$service_id]['error_time']);
            }
        }

        pdf_builder_update_option('pdf_builder_integration_connections', $this->connections_cache);
    }

    /**
     * Affiche les notifications d'intégration
     */
    public function display_integration_notices() {
        // Succès OAuth
        if (isset($_GET['oauth_success']) && isset($_GET['service'])) {
            $service_name = self::SERVICES_CONFIG[$_GET['service']]['name'] ?? $_GET['service'];
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . sprintf(pdf_builder_translate('%s connecté avec succès !', 'integration'), $service_name) . '</p>';
            echo '</div>';
        }

        // Erreur OAuth
        if (isset($_GET['oauth_error']) && isset($_GET['service'])) {
            $service_name = self::SERVICES_CONFIG[$_GET['service']]['name'] ?? $_GET['service'];
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . sprintf(pdf_builder_translate('Erreur lors de la connexion à %s', 'integration'), $service_name) . '</p>';
            echo '</div>';
        }

        // Erreurs de connexion
        foreach ($this->connections_cache as $service_id => $connection) {
            if ($connection['status'] === self::STATUS_ERROR && isset($connection['last_error'])) {
                $service_name = self::SERVICES_CONFIG[$service_id]['name'] ?? $service_id;
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p>' . sprintf(
                    pdf_builder_translate('Erreur de connexion %s : %s', 'integration'),
                    $service_name,
                    $connection['last_error']
                ) . '</p>';
                echo '</div>';
            }
        }
    }

    /**
     * AJAX - Connecte un service
     */
    public function connect_service_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service_id = sanitize_key($_POST['service_id'] ?? '');
            $config = $_POST['config'] ?? [];

            if (empty($service_id)) {
                wp_send_json_error(['message' => 'ID de service manquant']);
                return;
            }

            // Nettoyer la configuration
            $config = array_map('sanitize_text_field', $config);

            $result = $this->connect_service($service_id, $config);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'auth_url' => $result['auth_url'] ?? null
                ]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Déconnecte un service
     */
    public function disconnect_service_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service_id = sanitize_key($_POST['service_id'] ?? '');

            if (empty($service_id)) {
                wp_send_json_error(['message' => 'ID de service manquant']);
                return;
            }

            $result = $this->disconnect_service($service_id);

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Teste une connexion
     */
    public function test_connection_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service_id = sanitize_key($_POST['service_id'] ?? '');

            if (empty($service_id)) {
                wp_send_json_error(['message' => 'ID de service manquant']);
                return;
            }

            $result = $this->test_connection($service_id);

            if ($result['success']) {
                wp_send_json_success(['message' => $result['message']]);
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient le statut d'intégration
     */
    public function get_integration_status_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $services = $this->get_available_services();
            $connections = $this->get_connections_status();

            wp_send_json_success([
                'services' => $services,
                'connections' => $connections
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Sauvegarde les paramètres d'intégration
     */
    public function save_integration_settings_ajax() {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $service_id = sanitize_key($_POST['service_id'] ?? '');
            $settings = $_POST['settings'] ?? [];

            if (empty($service_id)) {
                wp_send_json_error(['message' => 'ID de service manquant']);
                return;
            }

            // Sauvegarder les paramètres
            $this->integrations_cache[$service_id] = array_merge(
                $this->integrations_cache[$service_id] ?? [],
                $settings
            );
            pdf_builder_update_option('pdf_builder_integrations', $this->integrations_cache);

            wp_send_json_success([
                'message' => pdf_builder_translate('Paramètres sauvegardés avec succès', 'integration')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_integration_manager() {
    return PDF_Builder_Integration_Manager::get_instance();
}

function pdf_builder_connect_service($service_id, $config = []) {
    return PDF_Builder_Integration_Manager::get_instance()->connect_service($service_id, $config);
}

function pdf_builder_disconnect_service($service_id) {
    return PDF_Builder_Integration_Manager::get_instance()->disconnect_service($service_id);
}

function pdf_builder_test_connection($service_id) {
    return PDF_Builder_Integration_Manager::get_instance()->test_connection($service_id);
}

function pdf_builder_get_integration_status($service_id) {
    return PDF_Builder_Integration_Manager::get_instance()->get_connection_status($service_id);
}

function pdf_builder_get_integration_config($service_id) {
    return PDF_Builder_Integration_Manager::get_instance()->get_service_config($service_id);
}

function pdf_builder_get_available_integrations() {
    return PDF_Builder_Integration_Manager::get_instance()->get_available_services();
}

// Initialiser le système d'intégrations
add_action('plugins_loaded', function() {
    PDF_Builder_Integration_Manager::get_instance();
});




