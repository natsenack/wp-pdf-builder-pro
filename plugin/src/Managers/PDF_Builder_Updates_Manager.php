<?php

namespace PDF_Builder\Managers;

if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * PDF_Builder_Updates_Manager
 * Gère les mises à jour automatiques du plugin via EDD
 * Intègre WordPress avec hub.threeaxe.fr pour vérifier les versions disponibles
 *
 * @since 1.0.2.0
 */
class PDF_Builder_Updates_Manager {

    /**
     * Store URL on EDD
     */
    const EDD_STORE_URL = 'https://hub.threeaxe.fr';

    /**
     * Item ID for PDF Builder Pro in EDD
     */
    const EDD_ITEM_ID = 19;

    /**
     * Slug du plugin
     */
    const PLUGIN_SLUG = 'pdf-builder-pro';

    /**
     * Transient cache key
     */
    const UPDATE_TRANSIENT_KEY = 'pdf_builder_pro_update_check';

    /**
     * Cache duration (12 hours)
     */
    const CACHE_TIMEOUT = 43200;

    /**
     * Chemin du plugin
     */
    private $plugin_file;

    /**
     * Données de version
     */
    private $current_version;

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_file = PDF_BUILDER_PLUGIN_FILE;
        $this->current_version = defined('PDF_BUILDER_PRO_VERSION') ? PDF_BUILDER_PRO_VERSION : '1.0.1.0';
    }

    /**
     * Initialize update hooks
     * S'appuie sur le mécanisme natif de WordPress (wp_update_plugins)
     * qui appelle pre_set_site_transient_update_plugins automatiquement.
     * Aucun cron personnalisé nécessaire.
     */
    public function init() {
        // Fournit les informations du plugin quand WordPress les demande
        add_filter('plugins_api', [$this, 'plugins_api_handler'], 10, 3);

        // WordPress appelle ce filtre lors de chaque vérification native (wp_update_plugins)
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates'], 10, 1);
        add_filter('pre_set_transient_update_plugins', [$this, 'check_for_updates'], 10, 1);
    }

    /**
     * Handle plugins_api requests
     * Fournit les informations du plugin quand WordPress les demande
     */
    public function plugins_api_handler($result, $action, $args) {
        // On ne traite que les requêtes pour notre plugin
        if (isset($args->slug) && $args->slug !== self::PLUGIN_SLUG) {
            return $result;
        }

        if ($action === 'plugin_information' || $action === 'query_plugins') {
            $plugin_info = $this->get_plugin_info();
            if ($plugin_info) {
                return $plugin_info;
            }
        }

        return $result;
    }

    /**
     * Vérifier les mises à jour et retourner transient pour WordPress
     */
    public function check_for_updates($transient) {
        // Si ce n'est pas un objet, le créer
        if (!is_object($transient)) {
            $transient = new \stdClass();
            $transient->last_checked = time();
            $transient->response = [];
            $transient->no_update = [];
        }

        // Le cache est géré via transient WordPress dans get_remote_version() (12h)
        // WordPress contrôle la fréquence des appels via wp_update_plugins()

        // Récupérer la version disponible depuis EDD
        $remote_version = $this->get_remote_version();

        if ($remote_version && version_compare($remote_version['version'], $this->current_version, '>')) {
            // Il y a une mise à jour disponible
            $plugin_basename = plugin_basename($this->plugin_file);

            $transient->response[$plugin_basename] = (object)[
                'id'               => self::EDD_ITEM_ID,
                'slug'             => self::PLUGIN_SLUG,
                'plugin'           => $plugin_basename,
                'new_version'      => $remote_version['version'],
                'url'              => $remote_version['url'] ?? self::EDD_STORE_URL . '/product/pdf-builder-pro/',
                'package'          => $remote_version['package'] ?? '',
                'icons'            => [
                    '1x' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-icon.png',
                    '2x' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-icon-2x.png',
                ],
                'banners'          => [
                    'low'  => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-banner-772x250.png',
                    'high' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-banner-1544x500.png',
                ],
                'tested'           => $remote_version['tested'] ?? '6.9',
                'requires'         => $remote_version['requires'] ?? '5.0',
                'requires_php'     => $remote_version['requires_php'] ?? '7.4',
                'compatibility'    => [],
            ];

            // Ajouter changelog si disponible
            if (!empty($remote_version['changelog'])) {
                $transient->response[$plugin_basename]->sections = [
                    'changelog' => $remote_version['changelog'],
                ];
            }
        } else {
            // Pas de mise à jour disponible
            if ($remote_version) {
                $plugin_basename = plugin_basename($this->plugin_file);
                $transient->no_update[$plugin_basename] = (object)[
                    'id'          => self::EDD_ITEM_ID,
                    'slug'        => self::PLUGIN_SLUG,
                    'plugin'      => $plugin_basename,
                    'new_version' => $remote_version['version'] ?? $this->current_version,
                    'url'         => $remote_version['url'] ?? self::EDD_STORE_URL . '/product/pdf-builder-pro/',
                ];
            }
        }

        $transient->last_checked = time();
        return $transient;
    }

    /**
     * Forcer la vérification manuelle (vide le cache transient)
     * Peut être appelé depuis un bouton admin ou via WP-CLI
     */
    public function manually_check_updates() {
        delete_transient(self::UPDATE_TRANSIENT_KEY);
        $this->get_remote_version(true);
    }

    /**
     * Récupérer les infos du plugin (pour plugins_api)
     */
    public function get_plugin_info() {
        $remote = $this->get_remote_version();

        if (!$remote) {
            return false;
        }

        $sections = [];
        if (!empty($remote['changelog'])) {
            $sections['changelog'] = $remote['changelog'];
        }

        return (object)[
            'id'               => self::EDD_ITEM_ID,
            'slug'             => self::PLUGIN_SLUG,
            'name'             => 'PDF Builder Pro',
            'version'          => $remote['version'] ?? $this->current_version,
            'author'           => 'Natsenack',
            'author_url'       => 'https://github.com/natsenack',
            'homepage'         => self::EDD_STORE_URL . '/product/pdf-builder-pro/',
            'url'              => $remote['url'] ?? self::EDD_STORE_URL . '/product/pdf-builder-pro/',
            'download_url'     => $remote['package'] ?? '',
            'requires'         => $remote['requires'] ?? '5.0',
            'requires_php'     => $remote['requires_php'] ?? '7.4',
            'tested'           => $remote['tested'] ?? '6.9',
            'active_installs'  => 0,
            'rating'           => 5,
            'ratings'          => [5 => 100],
            'downloaded'       => 0,
            'last_updated'     => date('Y-m-d H:i:s', time()),
            'added'            => '2025-01-01',
            'banners'          => [
                'low'  => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-banner-772x250.png',
                'high' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-banner-1544x500.png',
            ],
            'icons'            => [
                '1x' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-icon.png',
                '2x' => PDF_BUILDER_PLUGIN_URL . 'assets/images/plugin-icon-2x.png',
            ],
            'sections'         => $sections,
            'contributors'     => ['natsenack'],
            'donate_link'      => self::EDD_STORE_URL,
        ];
    }

    /**
     * Récupérer la version distante depuis EDD
     * 
     * @param bool $force Force cache bypass
     * @return array|bool
     */
    public function get_remote_version($force = false) {
        // Vérifier le cache
        if (!$force) {
            $cached = get_transient(self::UPDATE_TRANSIENT_KEY);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Requête vers EDD pour récupérer les infos du produit
        $response = wp_remote_get(
            add_query_arg([
                'edd_action'           => 'get_version',
                'license'              => $this->get_license_key(),
                'item_id'              => self::EDD_ITEM_ID,
                'url'                  => home_url(),
                'wp_version'           => get_bloginfo('version'),
                'php_version'          => phpversion(),
                'plugin_version'       => $this->current_version,
            ], self::EDD_STORE_URL),
            [
                'timeout'     => 15,
                'sslverify'   => true,
                'user-agent'  => 'PDF-Builder-Pro/' . $this->current_version . '; ' . home_url(),
            ]
        );

        // Gestion des erreurs
        if (is_wp_error($response)) {
            do_action('pdf_builder_update_check_error', $response);
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $remote_version = json_decode($body, true);

        if (!is_array($remote_version) || empty($remote_version['version'])) {
            return false;
        }

        // Cacher le résultat
        set_transient(self::UPDATE_TRANSIENT_KEY, $remote_version, self::CACHE_TIMEOUT);

        return $remote_version;
    }

    /**
     * Récupérer la clé de licence
     * Retourne la clé si elle existe, sinon vide (les clients gratuits n'en ont pas)
     */
    private function get_license_key() {
        $license_manager = PDF_Builder_License_Manager::getInstance();
        
        // Vérifier que c'est un admin (sécurité)
        if (!current_user_can('manage_options')) {
            return '';
        }

        return $license_manager->getLicenseKeyForLinks();
    }

    /**
     * Obtenir la version actuelle du plugin
     */
    public function get_current_version() {
        return $this->current_version;
    }

    /**
     * Obtenir les infos du changelog local
     */
    public function get_changelog_from_file() {
        $changelog_file = PDF_BUILDER_PLUGIN_DIR . 'changelog.json';

        if (!file_exists($changelog_file)) {
            return null;
        }

        $changelog = json_decode(file_get_contents($changelog_file), true);
        return $changelog;
    }

    /**
     * Nettoyer les transients (utilisé lors de la désactivation)
     */
    public static function cleanup() {
        delete_transient(self::UPDATE_TRANSIENT_KEY);
        // Supprime l'ancien cron personnalisé s'il existait encore
        wp_clear_scheduled_hook('pdf_builder_check_updates');
    }
}
