<?php
/**
 * PDF Builder Pro - Gestion des licences et activation
 * Contrôle l'activation et la validité des licences
 */

class PDF_Builder_License_Manager
{
    private static $instance = null;

    // Statuts de licence
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const STATUS_INVALID = 'invalid';
    const STATUS_REVOKED = 'revoked';

    // Types de licence
    const TYPE_PERSONAL = 'personal';
    const TYPE_BUSINESS = 'business';
    const TYPE_ENTERPRISE = 'enterprise';
    const TYPE_DEVELOPER = 'developer';

    // Limites par type de licence
    const LIMITS = [
        self::TYPE_PERSONAL => [
            'pdfs_per_month' => 100,
            'templates' => 5,
            'users' => 1,
            'storage_gb' => 1,
            'api_calls_per_hour' => 100,
            'support_level' => 'basic'
        ],
        self::TYPE_BUSINESS => [
            'pdfs_per_month' => 1000,
            'templates' => 25,
            'users' => 10,
            'storage_gb' => 10,
            'api_calls_per_hour' => 1000,
            'support_level' => 'priority'
        ],
        self::TYPE_ENTERPRISE => [
            'pdfs_per_month' => -1, // Illimité
            'templates' => -1,
            'users' => -1,
            'storage_gb' => 100,
            'api_calls_per_hour' => -1,
            'support_level' => 'premium'
        ],
        self::TYPE_DEVELOPER => [
            'pdfs_per_month' => -1,
            'templates' => -1,
            'users' => 1,
            'storage_gb' => 50,
            'api_calls_per_hour' => -1,
            'support_level' => 'developer'
        ]
    ];

    // Clé de stockage
    const OPTION_LICENSE_KEY = 'pdf_builder_license_key';
    const OPTION_LICENSE_DATA = 'pdf_builder_license_data';
    const OPTION_ACTIVATION_STATUS = 'pdf_builder_activation_status';

    // URL de l'API de licences
    const API_URL = 'https://api.pdfbuilderpro.com/v1/licenses';
    const API_TIMEOUT = 30;

    // Cache
    private $license_data = null;
    private $activation_status = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init_hooks();
        $this->load_license_data();
    }

    private function init_hooks()
    {
        // Actions AJAX
        add_action('wp_ajax_pdf_builder_activate_license', [$this, 'activate_license_ajax']);
        add_action('wp_ajax_pdf_builder_deactivate_license', [$this, 'deactivate_license_ajax']);
        add_action('wp_ajax_pdf_builder_check_license_status', [$this, 'check_license_status_ajax']);
        add_action('wp_ajax_pdf_builder_get_license_info', [$this, 'get_license_info_ajax']);

        // Actions d'administration
        add_action('admin_init', [$this, 'check_license_validity']);
        add_action('admin_menu', [$this, 'add_license_menu']);
        add_action('admin_notices', [$this, 'display_license_notices']);

        // Actions programmées
        add_action('pdf_builder_daily_license_check', [$this, 'daily_license_check']);
        add_action('pdf_builder_license_expiring_soon', [$this, 'handle_license_expiring_soon']);

        // Filtres
        add_filter('pdf_builder_feature_enabled', [$this, 'check_feature_availability'], 10, 2);
        add_filter('pdf_builder_usage_limit_reached', [$this, 'check_usage_limits'], 10, 2);

        // Désactivation du plugin
        register_deactivation_hook(PDF_BUILDER_PLUGIN_FILE, [$this, 'deactivate_license_on_uninstall']);
    }

    /**
     * Charge les données de licence
     */
    private function load_license_data()
    {
        $this->license_data = get_option(self::OPTION_LICENSE_DATA, []);
        $this->activation_status = get_option(self::OPTION_ACTIVATION_STATUS, self::STATUS_INACTIVE);
    }

    /**
     * Ajoute le menu de gestion des licences
     */
    public function add_license_menu()
    {
        add_submenu_page(
            'pdf-builder-settings',
            pdf_builder_translate('Gestion des licences', 'license'),
            pdf_builder_translate('Licence', 'license'),
            'manage_options',
            'pdf-builder-license',
            [$this, 'render_license_page']
        );
    }

    /**
     * Rend la page de gestion des licences
     */
    public function render_license_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(pdf_builder_translate('Accès refusé', 'license'));
        }

        $license_key = get_option(self::OPTION_LICENSE_KEY, '');
        $license_data = $this->get_license_data();
        $status = $this->get_activation_status();
        $limits = $this->get_current_limits();
        $usage = $this->get_current_usage();

        include PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/license-management.php';
    }

    /**
     * Active une licence
     */
    public function activate_license($license_key)
    {
        try {
            // Validation de base
            if (empty($license_key)) {
                throw new Exception(pdf_builder_translate('Clé de licence requise', 'license'));
            }

            // Nettoyer la clé
            $license_key = trim($license_key);

            // Vérifier le format de la clé
            if (!$this->validate_license_format($license_key)) {
                throw new Exception(pdf_builder_translate('Format de clé de licence invalide', 'license'));
            }

            // Appeler l'API d'activation
            $response = $this->call_license_api(
                'activate', [
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'site_name' => get_bloginfo('name'),
                'wp_version' => get_bloginfo('version'),
                'plugin_version' => PDF_BUILDER_VERSION
                ]
            );

            if (!$response['success']) {
                throw new Exception($response['message'] ?? pdf_builder_translate('Erreur d\'activation', 'license'));
            }

            // Sauvegarder les données
            update_option(self::OPTION_LICENSE_KEY, $license_key);
            update_option(self::OPTION_LICENSE_DATA, $response['data']);
            update_option(self::OPTION_ACTIVATION_STATUS, self::STATUS_ACTIVE);

            // Mettre à jour le cache
            $this->license_data = $response['data'];
            $this->activation_status = self::STATUS_ACTIVE;

            // Logger l'activation
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info(
                    'License activated', [
                    'license_key' => $this->mask_license_key($license_key),
                    'license_type' => $response['data']['type'] ?? 'unknown'
                    ]
                );
            }

            // Programmer la vérification quotidienne
            if (!wp_next_scheduled('pdf_builder_daily_license_check')) {
                wp_schedule_event(time(), 'daily', 'pdf_builder_daily_license_check');
            }

            return [
                'success' => true,
                'message' => pdf_builder_translate('Licence activée avec succès', 'license'),
                'data' => $response['data']
            ];

        } catch (Exception $e) {
            // Logger l'erreur
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->error(
                    'License activation failed', [
                    'error' => $e->getMessage(),
                    'license_key' => $this->mask_license_key($license_key)
                    ]
                );
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Désactive une licence
     */
    public function deactivate_license()
    {
        try {
            $license_key = get_option(self::OPTION_LICENSE_KEY);

            if (empty($license_key)) {
                throw new Exception(pdf_builder_translate('Aucune licence active', 'license'));
            }

            // Appeler l'API de désactivation
            $response = $this->call_license_api(
                'deactivate', [
                'license_key' => $license_key,
                'site_url' => get_site_url()
                ]
            );

            // Supprimer les données locales même si l'API échoue
            delete_option(self::OPTION_LICENSE_KEY);
            delete_option(self::OPTION_LICENSE_DATA);
            update_option(self::OPTION_ACTIVATION_STATUS, self::STATUS_INACTIVE);

            // Mettre à jour le cache
            $this->license_data = [];
            $this->activation_status = self::STATUS_INACTIVE;

            // Désactiver les fonctionnalités premium
            $this->disable_premium_features();

            // Logger la désactivation
            if (class_exists('PDF_Builder_Logger')) {
                PDF_Builder_Logger::get_instance()->info(
                    'License deactivated', [
                    'license_key' => $this->mask_license_key($license_key)
                    ]
                );
            }

            // Supprimer la vérification programmée
            wp_clear_scheduled_hook('pdf_builder_daily_license_check');

            return [
                'success' => true,
                'message' => pdf_builder_translate('Licence désactivée avec succès', 'license')
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie le statut d'une licence
     */
    public function check_license_status()
    {
        try {
            $license_key = get_option(self::OPTION_LICENSE_KEY);

            if (empty($license_key)) {
                return [
                    'status' => self::STATUS_INACTIVE,
                    'message' => pdf_builder_translate('Aucune licence active', 'license')
                ];
            }

            // Appeler l'API de vérification
            $response = $this->call_license_api(
                'status', [
                'license_key' => $license_key,
                'site_url' => get_site_url()
                ]
            );

            if (!$response['success']) {
                $status = self::STATUS_INVALID;
                $message = $response['message'] ?? pdf_builder_translate('Licence invalide', 'license');
            } else {
                $status = $response['data']['status'] ?? self::STATUS_INVALID;
                $message = $response['data']['message'] ?? '';

                // Mettre à jour les données si elles ont changé
                if (isset($response['data']['license_data'])) {
                    update_option(self::OPTION_LICENSE_DATA, $response['data']['license_data']);
                    $this->license_data = $response['data']['license_data'];
                }
            }

            // Mettre à jour le statut
            update_option(self::OPTION_ACTIVATION_STATUS, $status);
            $this->activation_status = $status;

            return [
                'status' => $status,
                'message' => $message,
                'data' => $response['data'] ?? []
            ];

        } catch (Exception $e) {
            return [
                'status' => self::STATUS_INVALID,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtient les données de licence
     */
    public function get_license_data()
    {
        return $this->license_data ?: [];
    }

    /**
     * Obtient le statut d'activation
     */
    public function get_activation_status()
    {
        return $this->activation_status ?: self::STATUS_INACTIVE;
    }

    /**
     * Vérifie si la licence est active
     */
    public function is_license_active()
    {
        return $this->get_activation_status() === self::STATUS_ACTIVE;
    }

    /**
     * Vérifie si la licence est expirée
     */
    public function is_license_expired()
    {
        $data = $this->get_license_data();

        if (empty($data['expires_at'])) {
            return false;
        }

        return strtotime($data['expires_at']) < time();
    }

    /**
     * Obtient les limites actuelles
     */
    public function get_current_limits()
    {
        $data = $this->get_license_data();
        $license_type = $data['type'] ?? self::TYPE_PERSONAL;

        return self::LIMITS[$license_type] ?? self::LIMITS[self::TYPE_PERSONAL];
    }

    /**
     * Obtient l'utilisation actuelle
     */
    public function get_current_usage()
    {
        if (!class_exists('PDF_Builder_Analytics_Manager')) {
            return [];
        }

        $analytics = PDF_Builder_Analytics_Manager::get_instance();

        return [
            'pdfs_this_month' => $analytics->get_pdfs_generated_this_month(),
            'templates_used' => $analytics->get_templates_count(),
            'users_active' => $analytics->get_active_users_count(),
            'storage_used_gb' => $analytics->get_storage_used_gb(),
            'api_calls_today' => $analytics->get_api_calls_today()
        ];
    }

    /**
     * Vérifie la disponibilité d'une fonctionnalité
     */
    public function check_feature_availability($enabled, $feature)
    {
        if (!$this->is_license_active()) {
            // Fonctionnalités de base toujours disponibles
            $basic_features = ['view_pdfs', 'basic_templates'];

            if (in_array($feature, $basic_features)) {
                return true;
            }

            return false;
        }

        $limits = $this->get_current_limits();

        // Vérifications spécifiques par fonctionnalité
        switch ($feature) {
        case 'advanced_templates':
            return $limits['templates'] === -1 || $this->get_current_usage()['templates_used'] < $limits['templates'];

        case 'api_access':
            return $limits['api_calls_per_hour'] === -1 || $this->get_current_usage()['api_calls_today'] < $limits['api_calls_per_hour'];

        case 'multi_user':
            return $limits['users'] === -1 || $this->get_current_usage()['users_active'] < $limits['users'];

        case 'unlimited_storage':
            return $limits['storage_gb'] === -1 || $this->get_current_usage()['storage_used_gb'] < $limits['storage_gb'];

        default:
            return $enabled;
        }
    }

    /**
     * Vérifie les limites d'utilisation
     */
    public function check_usage_limits($limit_reached, $type)
    {
        if (!$this->is_license_active()) {
            return true; // Limite atteinte pour les licences inactives
        }

        $limits = $this->get_current_limits();
        $usage = $this->get_current_usage();

        switch ($type) {
        case 'pdfs_per_month':
            return $limits['pdfs_per_month'] !== -1 && $usage['pdfs_this_month'] >= $limits['pdfs_per_month'];

        case 'api_calls_per_hour':
            return $limits['api_calls_per_hour'] !== -1 && $usage['api_calls_today'] >= $limits['api_calls_per_hour'];

        case 'storage':
            return $limits['storage_gb'] !== -1 && $usage['storage_used_gb'] >= $limits['storage_gb'];

        default:
            return $limit_reached;
        }
    }

    /**
     * Valide le format d'une clé de licence
     */
    private function validate_license_format($license_key)
    {
        // Format attendu: XXXX-XXXX-XXXX-XXXX
        $pattern = '/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/';

        return preg_match($pattern, $license_key);
    }

    /**
     * Masque une clé de licence pour les logs
     */
    private function mask_license_key($license_key)
    {
        if (strlen($license_key) < 8) {
            return '****';
        }

        return substr($license_key, 0, 4) . '****' . substr($license_key, -4);
    }

    /**
     * Appelle l'API de licences
     */
    private function call_license_api($action, $data = [])
    {
        $url = self::API_URL . '/' . $action;

        $args = [
            'method' => 'POST',
            'timeout' => self::API_TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PDF Builder Pro/' . PDF_BUILDER_VERSION . '; ' . get_site_url()
            ],
            'body' => wp_json_encode($data),
            'sslverify' => true
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            throw new Exception(pdf_builder_translate('Erreur de connexion à l\'API', 'license') . ': ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(pdf_builder_translate('Réponse API invalide', 'license'));
        }

        return $data;
    }

    /**
     * Désactive les fonctionnalités premium
     */
    private function disable_premium_features()
    {
        // Supprimer les options premium
        delete_option('pdf_builder_premium_features_enabled');

        // Logger l'action
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->info('Premium features disabled due to license deactivation');
        }
    }

    /**
     * Vérifie la validité de la licence
     */
    public function check_license_validity()
    {
        if (!$this->is_license_active()) {
            return;
        }

        $status = $this->check_license_status();

        if ($status['status'] !== self::STATUS_ACTIVE) {
            update_option(self::OPTION_ACTIVATION_STATUS, $status['status']);
            $this->activation_status = $status['status'];

            // Désactiver les fonctionnalités si nécessaire
            if ($status['status'] === self::STATUS_EXPIRED || $status['status'] === self::STATUS_REVOKED) {
                $this->disable_premium_features();
            }
        }
    }

    /**
     * Vérification quotidienne de la licence
     */
    public function daily_license_check()
    {
        $this->check_license_validity();

        // Vérifier l'expiration prochaine
        $data = $this->get_license_data();

        if (!empty($data['expires_at'])) {
            $expires_at = strtotime($data['expires_at']);
            $days_until_expiry = floor(($expires_at - time()) / (60 * 60 * 24));

            if ($days_until_expiry <= 7 && $days_until_expiry > 0) {
                // Programmer un rappel d'expiration
                if (!wp_next_scheduled('pdf_builder_license_expiring_soon')) {
                    wp_schedule_single_event(time() + 3600, 'pdf_builder_license_expiring_soon');
                }
            }
        }
    }

    /**
     * Gère l'expiration prochaine de la licence
     */
    public function handle_license_expiring_soon()
    {
        $data = $this->get_license_data();
        $expires_at = $data['expires_at'] ?? '';
        $days_left = floor((strtotime($expires_at) - time()) / (60 * 60 * 24));

        $admin_email = get_option('admin_email');
        $subject = 'PDF Builder Pro - Licence expirant bientôt';
        $message = sprintf(
            "Votre licence PDF Builder Pro expire bientôt.\n\n" .
            "Date d'expiration: %s\n" .
            "Jours restants: %d\n\n" .
            "Veuillez renouveler votre licence pour continuer à bénéficier de toutes les fonctionnalités.",
            $expires_at,
            $days_left
        );

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Affiche les messages de licence
     */
    public function display_license_notices()
    {
        $status = $this->get_activation_status();

        switch ($status) {
        case self::STATUS_INACTIVE:
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p>' . sprintf(
                pdf_builder_translate('PDF Builder Pro nécessite une licence active. <a href="%s">Activer maintenant</a>', 'license'),
                admin_url('admin.php?page=pdf-builder-license')
            ) . '</p>';
            echo '</div>';
            break;

        case self::STATUS_EXPIRED:
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . sprintf(
                pdf_builder_translate('Votre licence PDF Builder Pro a expiré. <a href="%s">Renouveler maintenant</a>', 'license'),
                admin_url('admin.php?page=pdf-builder-license')
            ) . '</p>';
            echo '</div>';
            break;

        case self::STATUS_INVALID:
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . sprintf(
                pdf_builder_translate('Votre licence PDF Builder Pro est invalide. <a href="%s">Vérifier la licence</a>', 'license'),
                admin_url('admin.php?page=pdf-builder-license')
            ) . '</p>';
            echo '</div>';
            break;
        }

        // Message d'expiration prochaine
        if ($this->is_license_active()) {
            $data = $this->get_license_data();

            if (!empty($data['expires_at'])) {
                $days_left = floor((strtotime($data['expires_at']) - time()) / (60 * 60 * 24));

                if ($days_left <= 7 && $days_left > 0) {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p>' . sprintf(
                        pdf_builder_translate('Votre licence PDF Builder Pro expire dans %d jours. <a href="%s">Renouveler maintenant</a>', 'license'),
                        $days_left,
                        admin_url('admin.php?page=pdf-builder-license')
                    ) . '</p>';
                    echo '</div>';
                }
            }
        }
    }

    /**
     * Désactive la licence lors de la désinstallation
     */
    public function deactivate_license_on_uninstall()
    {
        $this->deactivate_license();
    }

    /**
     * AJAX - Active une licence
     */
    public function activate_license_ajax()
    {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $license_key = sanitize_text_field($_POST['license_key'] ?? '');

            $result = $this->activate_license($license_key);

            if ($result['success']) {
                wp_send_json_success(
                    [
                    'message' => $result['message'],
                    'data' => $result['data']
                    ]
                );
            } else {
                wp_send_json_error(['message' => $result['message']]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Désactive une licence
     */
    public function deactivate_license_ajax()
    {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $result = $this->deactivate_license();

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
     * AJAX - Vérifie le statut de la licence
     */
    public function check_license_status_ajax()
    {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $result = $this->check_license_status();

            wp_send_json_success(
                [
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => $result['data']
                ]
            );

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtient les informations de licence
     */
    public function get_license_info_ajax()
    {
        try {
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $license_data = $this->get_license_data();
            $status = $this->get_activation_status();
            $limits = $this->get_current_limits();
            $usage = $this->get_current_usage();

            wp_send_json_success(
                [
                'license_data' => $license_data,
                'status' => $status,
                'limits' => $limits,
                'usage' => $usage
                ]
            );

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}

// Fonctions globales
function pdf_builder_license_manager()
{
    return PDF_Builder_License_Manager::get_instance();
}

function pdf_builder_is_license_active()
{
    return PDF_Builder_License_Manager::get_instance()->is_license_active();
}

function pdf_builder_get_license_data()
{
    return PDF_Builder_License_Manager::get_instance()->get_license_data();
}

function pdf_builder_get_license_limits()
{
    return PDF_Builder_License_Manager::get_instance()->get_current_limits();
}

function pdf_builder_check_feature_enabled($feature)
{
    return apply_filters('pdf_builder_feature_enabled', true, $feature);
}

function pdf_builder_check_usage_limit($type)
{
    return apply_filters('pdf_builder_usage_limit_reached', false, $type);
}

// Vérifications de licence pour les actions critiques
function pdf_builder_require_license($feature = null)
{
    if (!pdf_builder_is_license_active()) {
        wp_die(pdf_builder_translate('Cette fonctionnalité nécessite une licence active', 'license'));
    }

    if ($feature && !pdf_builder_check_feature_enabled($feature)) {
        wp_die(pdf_builder_translate('Cette fonctionnalité n\'est pas disponible avec votre licence', 'license'));
    }
}

// Initialiser le système de gestion des licences
add_action(
    'plugins_loaded', function () {
        PDF_Builder_License_Manager::get_instance();
    }
);
