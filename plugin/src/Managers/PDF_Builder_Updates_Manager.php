<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags

namespace PDF_Builder\Managers;

if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * PDF_Builder_Updates_Manager
 * Gestionnaire de mises à jour automatiques via EDD Software Licensing.
 * Générique : réutilisable pour n'importe quel plugin vendu sur un store EDD.
 *
 * Usage pour un autre plugin :
 *   $updater = new PDF_Builder_Updates_Manager([
 *       'store_url'      => 'https://hub.threeaxe.fr',
 *       'item_id'        => 42,
 *       'item_name'      => 'My Other Plugin',
 *       'plugin_slug'    => 'my-other-plugin',
 *       'plugin_file'    => MY_OTHER_PLUGIN_FILE,
 *       'version'        => MY_OTHER_PLUGIN_VERSION,
 *       'license_key'    => get_option('my_other_plugin_license_key', ''),
 *       'plugin_url'     => MY_OTHER_PLUGIN_URL,
 *   ]);
 *   $updater->init();
 *
 * @since 1.0.2.0
 */
class PDF_Builder_Updates_Manager {

    /**
     * Cache duration (12 hours)
     */
    const CACHE_TIMEOUT = 43200;

    /** @var string URL du store EDD */
    private $store_url;

    /** @var int ID du produit sur EDD */
    private $item_id;

    /** @var string Nom du produit sur EDD */
    private $item_name;

    /** @var string Slug WordPress du plugin */
    private $plugin_slug;

    /** @var string Chemin absolu du fichier principal du plugin */
    private $plugin_file;

    /** @var string Version installée */
    private $current_version;

    /** @var string URL de base du plugin (pour icônes/bannières) */
    private $plugin_url;

    /** @var string Clé de cache transient (auto-générée depuis le slug) */
    private $transient_key;

    /** @var callable|null Callback pour récupérer la licence à la volée */
    private $license_callback;

    /**
     * Constructor
     *
     * @param array $config {
     *   @type string        $store_url        URL du store EDD (ex: 'https://hub.threeaxe.fr')
     *   @type int           $item_id          ID du téléchargement EDD
     *   @type string        $item_name        Nom du produit EDD
     *   @type string        $plugin_slug      Slug WordPress du plugin
     *   @type string        $plugin_file      Chemin absolu vers le fichier principal du plugin
     *   @type string        $version          Version actuellement installée
     *   @type string        $plugin_url       URL de base du plugin (pour assets)
     *   @type string        $license_key      Clé de licence (optionnelle)
     *   @type callable      $license_callback Callback qui retourne la clé de licence dynamiquement
     * }
     */
    public function __construct( array $config = [] ) {
        $this->store_url       = rtrim( $config['store_url']   ?? 'https://hub.threeaxe.fr', '/' );
        $this->item_id         = (int) ( $config['item_id']    ?? 19 );
        $this->item_name       = $config['item_name']          ?? 'PDF Builder Pro';
        $this->plugin_slug     = $config['plugin_slug']        ?? 'pdf-builder-pro';
        $this->plugin_file     = $config['plugin_file']        ?? PDF_BUILDER_PLUGIN_FILE;
        $this->plugin_url      = rtrim( $config['plugin_url']  ?? PDF_BUILDER_PLUGIN_URL, '/' ) . '/';
        $this->current_version = $config['version']            ?? ( defined('PDF_BUILDER_PRO_VERSION') ? PDF_BUILDER_PRO_VERSION : '1.0.1.0' );
        $this->license_callback = $config['license_callback']  ?? null;

        // Clé de cache dérivée du slug pour éviter les collisions entre plugins
        $this->transient_key = sanitize_key( $this->plugin_slug ) . '_edd_update_check';

        // Clé de licence statique passée directement (alternative au callback)
        if ( isset( $config['license_key'] ) ) {
            $static_key = $config['license_key'];
            $this->license_callback = function() use ( $static_key ) { return $static_key; };
        }
    }

    /**
     * Compatibilité : accès aux anciens noms de constantes via propriétés
     * Permet à l'ancien code (bootstrap.php) d'utiliser ::UPDATE_TRANSIENT_KEY sans erreur.
     */
    public function get_transient_key() {
        return $this->transient_key;
    }

    /**
     * Initialize update hooks
     */
    public function init() {
        // Injecte la réponse de mise à jour dans le transient WordPress
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_for_updates' ], 10, 1 ); // phpcs:ignore PluginCheck.CodeAnalysis.AutoUpdates.PluginUpdaterDetected
        add_filter( 'pre_set_transient_update_plugins',      [ $this, 'check_for_updates' ], 10, 1 ); // phpcs:ignore PluginCheck.CodeAnalysis.AutoUpdates.PluginUpdaterDetected

        // Fournit les informations du plugin quand WordPress les demande
        add_filter( 'plugins_api', [ $this, 'plugins_api_handler' ], 10, 3 );
    }

    /**
     * Handle plugins_api requests
     * Fournit les informations du plugin quand WordPress les demande
     */
    public function plugins_api_handler($result, $action, $args) {
        // On ne traite QUE les requêtes d'information sur notre plugin spécifique.
        // query_plugins est géré par WordPress.org et ne doit pas être intercepté.
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $plugin_info = $this->get_plugin_info();
        if ($plugin_info) {
            return $plugin_info;
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

        // On n'utilise PAS notre cache transient ici :
        // WP ne déclenche ce filtre que quand son propre transient update_plugins expire (~12h),
        // donc forcer $force=true garantit des données EDD fraîches à chaque vérification WP.
        // Notre cache 12h restait périmé et empêchait de détecter les nouvelles versions.

        error_log('[PDF Builder] check_for_updates() appelé. Version locale: ' . $this->current_version);

        // Récupérer la version disponible depuis EDD (données fraîches, pas de cache)
        $remote_version = $this->get_remote_version(true);

        if (!$remote_version) {
            error_log('[PDF Builder] check_for_updates() : get_remote_version() a retourné false (échec EDD)');
        } else {
            error_log('[PDF Builder] check_for_updates() : version distante = ' . $remote_version['version'] . ' | package = ' . $remote_version['package']);
            $cmp = version_compare($remote_version['version'], $this->current_version, '>');
            error_log('[PDF Builder] check_for_updates() : update disponible = ' . ($cmp ? 'OUI' : 'NON'));
        }

        if ($remote_version && version_compare($remote_version['version'], $this->current_version, '>')) {
            // Il y a une mise à jour disponible
            $plugin_basename = plugin_basename($this->plugin_file);

            $transient->response[$plugin_basename] = (object)[
                'id'               => $this->item_id,
                'slug'             => $this->plugin_slug,
                'plugin'           => $plugin_basename,
                'new_version'      => $remote_version['version'],
                'url'              => $remote_version['url'] ?? $this->store_url . '/downloads/' . $this->plugin_slug . '/',
                'package'          => $remote_version['package'] ?? '',
                'icons'            => [
                    '1x' => $this->plugin_url . 'assets/images/plugin-icon.png',
                    '2x' => $this->plugin_url . 'assets/images/plugin-icon-2x.png',
                ],
                'banners'          => [
                    'low'  => $this->plugin_url . 'assets/images/plugin-banner-772x250.png',
                    'high' => $this->plugin_url . 'assets/images/plugin-banner-1544x500.png',
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
                    'id'          => $this->item_id,
                    'slug'        => $this->plugin_slug,
                    'plugin'      => $plugin_basename,
                    'new_version' => $remote_version['version'] ?? $this->current_version,
                    'url'         => $remote_version['url'] ?? $this->store_url . '/downloads/' . $this->plugin_slug . '/',
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
        delete_transient($this->transient_key);
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
            'id'               => $this->item_id,
            'slug'             => $this->plugin_slug,
            'name'             => $this->item_name,
            'version'          => $remote['version'] ?? $this->current_version,
            'author'           => 'Natsenack',
            'author_url'       => 'https://github.com/natsenack',
            'homepage'         => $remote['url'] ?? $this->store_url . '/downloads/' . $this->plugin_slug . '/',
            'url'              => $remote['url'] ?? $this->store_url . '/downloads/' . $this->plugin_slug . '/',
            'download_url'     => $remote['package'] ?? '',
            'requires'         => $remote['requires'] ?? '5.0',
            'requires_php'     => $remote['requires_php'] ?? '7.4',
            'tested'           => $remote['tested'] ?? '6.9',
            'active_installs'  => 0,
            'rating'           => 5,
            'ratings'          => [5 => 100],
            'downloaded'       => 0,
            'last_updated'     => gmdate('Y-m-d H:i:s', time()),
            'added'            => '2025-01-01',
            'banners'          => [
                'low'  => $this->plugin_url . 'assets/images/plugin-banner-772x250.png',
                'high' => $this->plugin_url . 'assets/images/plugin-banner-1544x500.png',
            ],
            'icons'            => [
                '1x' => $this->plugin_url . 'assets/images/plugin-icon.png',
                '2x' => $this->plugin_url . 'assets/images/plugin-icon-2x.png',
            ],
            'sections'         => $sections,
            'contributors'     => ['natsenack'],
            'donate_link'      => $this->store_url,
        ];
    }

    /**
     * Récupérer la version distante depuis EDD Software Licensing
     *
     * EDD SL retourne un objet JSON avec les clés :
     *   new_version, download_link, sections, url, name, slug, tested, requires, requires_php
     *
     * @param bool $force Force cache bypass
     * @return array|bool
     */
    public function get_remote_version($force = false) {
        // Vérifier le cache
        if (!$force) {
            $cached = get_transient($this->transient_key);
            if ($cached !== false) {
                error_log('[PDF Builder] get_remote_version() : cache HIT → version=' . ($cached['version'] ?? 'N/A'));
                return $cached;
            }
            error_log('[PDF Builder] get_remote_version() : cache MISS → appel EDD');
        } else {
            error_log('[PDF Builder] get_remote_version() : force=true → appel EDD sans cache');
        }

        // Requête vers EDD Software Licensing
        $api_url = add_query_arg([
            'edd_action'     => 'get_version',
            'license'        => $this->get_license_key(),
            'item_id'        => $this->item_id,
            'item_name'      => rawurlencode($this->item_name),
            'url'            => home_url(),
            'wp_version'     => get_bloginfo('version'),
            'php_version'    => phpversion(),
            'plugin_version' => $this->current_version,
        ], $this->store_url);

        // Log de l'URL (sans la clé de licence pour la sécurité)
        $log_url = add_query_arg([
            'edd_action'     => 'get_version',
            'item_id'        => $this->item_id,
            'item_name'      => $this->item_name,
            'plugin_version' => $this->current_version,
        ], $this->store_url);
        error_log('[PDF Builder] get_remote_version() : appel EDD → ' . $log_url);

        $response = wp_remote_get($api_url, [
            'timeout'    => 15,
            'sslverify'  => true,
            'user-agent' => 'PDF-Builder-Pro/' . $this->current_version . '; ' . home_url(),
        ]);

        if (is_wp_error($response)) {
            error_log('[PDF Builder] Update check HTTP error: ' . $response->get_error_message());
            do_action('pdf_builder_update_check_error', $response);
            return false;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body      = wp_remote_retrieve_body($response);

        error_log('[PDF Builder] get_remote_version() : réponse HTTP ' . $http_code . ' | body (' . strlen($body) . ' octets) : ' . substr($body, 0, 500));

        if ($http_code !== 200 || empty($body)) {
            error_log('[PDF Builder] Update check failed. HTTP ' . $http_code);
            return false;
        }

        // EDD SL peut retourner du JSON ou du PHP sérialisé selon la version
        $data = json_decode($body, true);
        if (!is_array($data)) {
            error_log('[PDF Builder] get_remote_version() : JSON decode failed, tentative maybe_unserialize()');
            $data = (array) maybe_unserialize($body);
        }
        error_log('[PDF Builder] get_remote_version() : clés reçues = ' . implode(', ', array_keys($data)));

        // EDD SL utilise "new_version" et "download_link"
        // On normalise vers notre format interne
        $version = $data['new_version'] ?? $data['version'] ?? null;
        if (empty($version)) {
            error_log('[PDF Builder] Update check: no version in response. Body: ' . substr($body, 0, 300));
            return false;
        }

        $normalized = [
            'version'      => $version,
            'package'      => $data['download_link'] ?? $data['package'] ?? '',
            'url'          => $data['url'] ?? $data['homepage'] ?? $this->store_url,
            'requires'     => $data['requires'] ?? '5.0',
            'requires_php' => $data['requires_php'] ?? '7.4',
            'tested'       => $data['tested'] ?? '6.9',
            'changelog'    => $data['sections']['changelog'] ?? ($data['changelog'] ?? ''),
        ];

        error_log('[PDF Builder] get_remote_version() : résultat normalisé → version=' . $normalized['version'] . ' | package=' . $normalized['package']);

        // Vérification santé : si EDD fournit un package mais qu'il retourne 404,
        // les règles de réécriture du serveur EDD sont probablement obsolètes.
        // Solution : Réglages → Permaliens → Enregistrer sur hub.threeaxe.fr
        if (!empty($normalized['package'])) {
            $head = wp_remote_head($normalized['package'], ['timeout' => 5, 'sslverify' => true]);
            $code = wp_remote_retrieve_response_code($head);
            if ($code === 404) {
                error_log('[PDF Builder] AVERTISSEMENT : EDD package_download retourne 404. Les permaliens de hub.threeaxe.fr sont probablement obsolètes. Aller sur Réglages → Permaliens → Enregistrer.');
            } else {
                error_log('[PDF Builder] get_remote_version() : package accessible (HTTP ' . $code . ')');
            }
        }

        // Mettre en cache 12h
        set_transient($this->transient_key, $normalized, self::CACHE_TIMEOUT);

        return $normalized;
    }

    /**
     * Récupérer la clé de licence
     * Utilise le callback si fourni, sinon fallback sur PDF_Builder_License_Manager
     */
    private function get_license_key() {
        if ( $this->license_callback ) {
            return call_user_func( $this->license_callback );
        }
        // Fallback par défaut : PDF Builder Pro License Manager
        if ( class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager') ) {
            return PDF_Builder_License_Manager::getInstance()->get_license_key();
        }
        return '';
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
    public function cleanup() {
        delete_transient($this->transient_key);
        // Supprime l'ancien cron personnalisé s'il existait encore
        wp_clear_scheduled_hook('pdf_builder_check_updates');
    }
}
