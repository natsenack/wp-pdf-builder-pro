<?php

namespace PDF_Builder\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
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
        // Les clés de licence sont maintenant dans des lignes séparées
        $this->license_key = pdf_builder_get_option('pdf_builder_license_key', '');
        $this->license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
        $this->license_data = pdf_builder_get_option('pdf_builder_license_data', []);

        add_action('admin_init', array($this, 'check_license_status'));
    }

    /**
     * Vérifier si l'utilisateur a une licence premium active
     * Inclut les licences réelles, les clés de test, et les licences précédemment activées
     */
    public function isPremium()
    {
        // Vérifier d'abord la licence réelle active
        if ($this->license_status === 'active') {
            return true;
        }

        // Vérifier si l'utilisateur a déjà eu une licence premium activée (même si expirée)
        // Cela permet de garder l'accès premium même après suppression de la clé de test
        $has_been_premium = pdf_builder_get_option('pdf_builder_has_been_premium', false);
        if ($has_been_premium) {
            return true;
        }

        // Vérifier les clés de test (maintenant dans des lignes séparées)
        $test_key = pdf_builder_get_option('pdf_builder_license_test_key', '');
        if (!empty($test_key)) {
            // Vérifier si la clé de test n'est pas expirée
            $test_expires = pdf_builder_get_option('pdf_builder_license_test_key_expires', '');
            if (!empty($test_expires)) {
                $expires_date = strtotime($test_expires);
                if ($expires_date && $expires_date > time()) {
                    // Marquer que l'utilisateur a été premium
                    pdf_builder_update_option('pdf_builder_has_been_premium', true);
                    return true;
                }
            } else {
                // Si pas de date d'expiration, considérer comme valide
                pdf_builder_update_option('pdf_builder_has_been_premium', true);
                return true;
            }
        }

        return false;
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
            pdf_builder_update_option('pdf_builder_license_key', $license_key);
            pdf_builder_update_option('pdf_builder_license_status', 'active');
            pdf_builder_update_option('pdf_builder_license_data', $result['data']);
            // Marquer que l'utilisateur a été premium
            pdf_builder_update_option('pdf_builder_has_been_premium', true);

            $this->license_key = $license_key;
            $this->license_status = 'active';
            $this->license_data = $result['data'];

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

    /**
     * Validation de la licence (simulation)
     * À remplacer par un appel à votre serveur de licences
     */
    private function validateLicense($license_key)
    {
        // Simulation de validation - REMPLACER PAR VOTRE LOGIQUE RÉELLE
        $valid_keys = [
            'PDF-PRO-DEMO-2025' => [
                'tier' => 'professional',
                'expires' => strtotime('+1 year'),
                'features' => ['all']
            ]
        ];

        if (isset($valid_keys[$license_key])) {
            return [
                'success' => true,
                'data' => $valid_keys[$license_key]
            ];
        }

        // Simulation d'appel API (à implémenter)
        /*
        $api_url = 'https://api.pdfbuilderpro.com/validate-license';
        $response = wp_remote_post($api_url, [
            'body' => [
                'license_key' => $license_key,
                'site_url' => get_site_url(),
                'plugin_version' => PDF_BUILDER_VERSION
            ]
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'message' => 'Erreur de connexion au serveur'];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
        */

        return ['success' => false, 'message' => 'Clé de licence invalide'];
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





