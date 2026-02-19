<?php

namespace PDF_Builder\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * PDF Builder License Manager
 * Gestion centralisée des licences freemium
 *
 * Version: 1.1.0
 */

if (!class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager')) {
class PDF_Builder_License_Manager
{
    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Données de licence
     */
    private $license_key = '';
    private $license_status = 'free';
    private $license_data = [];

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation
     */
    private function init()
    {
        $encrypted            = pdf_builder_get_option('pdf_builder_license_key', '');
        $this->license_key    = self::decrypt_key($encrypted);
        $this->license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        $this->license_data   = pdf_builder_get_option('pdf_builder_license_data', []);

        \add_action('admin_init', array($this, 'check_license_status'));
    }

    // ─── Chiffrement AES-256-CBC ───────────────────────────────────────────────

    /**
     * Chiffre la clé de licence avant stockage en DB.
     * Utilise AUTH_KEY + SECURE_AUTH_KEY (uniques à l'installation WordPress).
     */
    private static function encrypt_key(string $plain): string
    {
        if (empty($plain)) return '';
        $secret = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true); // 32 octets
        $iv     = random_bytes(16);
        $cipher = openssl_encrypt($plain, 'aes-256-cbc', $secret, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $cipher); // IV prépendu au ciphertext
    }

    /**
     * Déchiffre la clé de licence lue depuis la DB.
     * Gestion ascendante : si la valeur est en clair (migration), elle est retournée telle quelle.
     */
    private static function decrypt_key(string $encrypted): string
    {
        if (empty($encrypted)) return '';
        $data = base64_decode($encrypted, true);
        if ($data === false || strlen($data) < 17) {
            // Valeur en clair (ancienne clé non chiffrée — compatibilité ascendante)
            return $encrypted;
        }
        $secret = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);
        $iv     = substr($data, 0, 16);
        $cipher = substr($data, 16);
        $plain  = openssl_decrypt($cipher, 'aes-256-cbc', $secret, OPENSSL_RAW_DATA, $iv);
        return ($plain !== false) ? $plain : '';
    }

    /**
     * Retourne la clé en clair pour les URL EDD (admin uniquement).
     * Ne jamais exposer côté public ou dans les logs.
     */
    public function getLicenseKeyForLinks(): string
    {
        return current_user_can('manage_options') ? $this->license_key : '';
    }

    /**
     * Retourne l'ID interne EDD du record de licence (pour les URL Renouveler / Désabonner).
     */
    public function getLicenseId(): string
    {
        return (string)($this->license_data['edd_license_id'] ?? '');
    }

    /**
     * Vérifier si l'utilisateur a une licence premium active
     * Désactive les fonctions premium si la licence est gratuite
     */
    public function isPremium()
    {
        // Seules les licences actives donnent accès aux fonctions premium
        return $this->license_status === 'active';
    }

    /**
     * Alias pour isPremium() - pour compatibilité
     */
    public function is_premium()
    {
        return $this->isPremium();
    }

    /**
     * Obtenir le statut de la licence
     */
    public function getLicenseStatus()
    {
        return $this->license_status;
    }

    /**
     * Obtenir les données de licence
     */
    public function getLicenseData()
    {
        return $this->license_data;
    }

    /**
     * Activer une licence
     */
    public function activateLicense($license_key)
    {
        // Validation basique
        if (empty($license_key)) {
            return ['success' => false, 'message' => 'Clé de licence requise'];
        }

        // Validation de la licence (simulation - à remplacer par appel API réel)
        $result = $this->validateLicense($license_key);

        if ($result['success']) {
            // Chiffrement avant stockage en base de données
            $encrypted = self::encrypt_key($license_key);
            pdf_builder_update_option('pdf_builder_license_key', $encrypted);
            pdf_builder_update_option('pdf_builder_license_status', 'active');
            pdf_builder_update_option('pdf_builder_license_data', $result['data']);

            $this->license_key    = $license_key; // en clair en mémoire uniquement
            $this->license_status = 'active';
            $this->license_data   = $result['data'];

            return ['success' => true, 'message' => 'Licence activée avec succès !'];
        }

        return ['success' => false, 'message' => $result['message']];
    }

    /**
     * Désactiver la licence
     */
    public function deactivateLicense()
    {
        pdf_builder_delete_option('pdf_builder_license_key');
        pdf_builder_delete_option('pdf_builder_license_status');
        pdf_builder_delete_option('pdf_builder_license_data');

        $this->license_key = '';
        $this->license_status = 'free';
        $this->license_data = [];

        return ['success' => true, 'message' => 'Licence désactivée'];
    }

    /** URL de la boutique EDD (Easy Digital Downloads) */
    const EDD_STORE_URL = 'https://hub.threeaxe.fr';

    /** ID du produit WP PDF Builder Pro dans EDD (à ajuster selon l'ID réel) */
    const EDD_ITEM_ID = 19;

    /** Nom du produit dans EDD (slug affiché dans l'URL) */
    const EDD_ITEM_NAME = 'PDF Builder Pro';

    /**
     * Validation de la licence via l'API EDD Software Licensing
     */
    private function validateLicense($license_key)
    {
        // Appel à l'API EDD Software Licensing
        $response = wp_remote_post(
            self::EDD_STORE_URL,
            [
                'timeout'   => 15,
                'sslverify' => true,
                'body'      => [
                    'edd_action' => 'activate_license',
                    'license'    => $license_key,
                    'item_id'    => self::EDD_ITEM_ID,
                    'item_name'  => self::EDD_ITEM_NAME,
                    'url'        => get_site_url(),
                ],
            ]
        );

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Impossible de contacter le serveur de licences : ' . $response->get_error_message(),
            ];
        }

        $license_data = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($license_data)) {
            return ['success' => false, 'message' => 'Réponse invalide du serveur de licences'];
        }

        // EDD renvoie "license" => "valid" | "invalid" | "expired" | "disabled"
        //                        | "site_inactive" | "item_name_mismatch" | "no_activations_left"
        $edd_status = $license_data['license'] ?? 'invalid';

        if ($edd_status === 'valid') {
            $expires_ts = (!empty($license_data['expires']) && $license_data['expires'] !== 'lifetime')
                ? strtotime($license_data['expires'])
                : strtotime('+100 years');

            // Stocker la date d'expiration pour le cron
            pdf_builder_update_option('pdf_builder_license_expires', $license_data['expires'] ?? '');

            return [
                'success' => true,
                'data'    => [
                    'tier'           => 'professional',
                    'expires'        => $expires_ts,
                    'expires_raw'    => $license_data['expires'] ?? '',
                    'features'       => ['all'],
                    'customer'       => $license_data['customer_name'] ?? '',
                    'email'          => $license_data['customer_email'] ?? '',
                    'activations'    => $license_data['activations_left'] ?? 0,
                    'edd_status'     => 'valid',
                    // ID interne EDD du record licence (pour URL Renouveler / Désabonner)
                    'edd_license_id' => $license_data['ID'] ?? '',
                ],
            ];
        }

        $error_messages = [
            'expired'             => 'Votre licence a expiré. Renouvelez-la sur wp-pdf-builder.com.',
            'disabled'            => 'Cette licence a été désactivée.',
            'site_inactive'       => 'Cette licence n\'est pas activée pour ce site.',
            'item_name_mismatch'  => 'Cette clé ne correspond pas à ce plugin.',
            'no_activations_left' => 'Le nombre maximum d\'activations a été atteint.',
            'key_mismatch'        => 'Clé de licence invalide.',
            'invalid'             => 'Clé de licence invalide.',
        ];

        return [
            'success' => false,
            'message' => $error_messages[$edd_status] ?? 'Clé invalide (code EDD : ' . esc_html($edd_status) . ')',
        ];
    }

    /**
     * Vérification périodique du statut de la licence
     */
    public function checkLicenseStatus()
    {
        if (empty($this->license_key) || $this->license_status !== 'active') {
            return;
        }

        // Vérifier une fois par jour
        $last_check = pdf_builder_get_option('pdf_builder_license_last_check', 0);
        $now = time();

        if ($now - $last_check > 86400) {
            $result = $this->validateLicense($this->license_key);

            if (!$result['success']) {
                pdf_builder_update_option('pdf_builder_license_status', 'expired');
                $this->license_status = 'expired';
            }

            pdf_builder_update_option('pdf_builder_license_last_check', $now);
        }
    }

    /**
     * Obtenir les informations de licence formatées
     */
    public function getLicenseInfo()
    {
        return [
            'status' => $this->license_status,
            'is_premium' => $this->is_premium(),
            'tier' => isset($this->license_data['tier']) ? $this->license_data['tier'] : 'free',
            'expires' => isset($this->license_data['expires']) ? date('d/m/Y', $this->license_data['expires']) : null,
            'features' => isset($this->license_data['features']) ? $this->license_data['features'] : []
        ];
    }
}
}







