<?php
/**
 * PDF Builder Pro Configuration
 * Configuration et constantes du plugin
 */



// === CONSTANTES DE BASE ===

// Version du plugin
if (!defined('PDF_BUILDER_PRO_VERSION')) {
    define('PDF_BUILDER_PRO_VERSION', '1.0.2');
}

// Répertoire du plugin
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__) . '../');
}

// URL du plugin
if (!defined('PDF_BUILDER_PLUGIN_URL')) {
    define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(__FILE__) . '../');
}

// Répertoire des assets
if (!defined('PDF_BUILDER_PRO_ASSETS_DIR')) {
    define('PDF_BUILDER_PRO_ASSETS_DIR', PDF_BUILDER_PLUGIN_DIR . 'assets/');
}

// URL des assets
if (!defined('PDF_BUILDER_PRO_ASSETS_URL')) {
    define('PDF_BUILDER_PRO_ASSETS_URL', PDF_BUILDER_PLUGIN_URL . 'assets/');
}

// Chemin absolu des assets (pour file_exists)
if (!defined('PDF_BUILDER_PRO_ASSETS_PATH')) {
    define('PDF_BUILDER_PRO_ASSETS_PATH', PDF_BUILDER_PLUGIN_DIR . 'assets/');
}

// Répertoire des uploads
if (!defined('PDF_BUILDER_PRO_UPLOADS_DIR')) {
    // Utiliser une approche paresseuse pour éviter les erreurs si wp_upload_dir n'est pas disponible
    if (function_exists('wp_upload_dir')) {
        define('PDF_BUILDER_PRO_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/pdf-builder-pro/');
    } else {
        // Fallback temporaire - sera redéfini plus tard
        define('PDF_BUILDER_PRO_UPLOADS_DIR', ABSPATH . 'wp-content/uploads/pdf-builder-pro/');
    }
}

// URL des uploads
if (!defined('PDF_BUILDER_PRO_UPLOADS_URL')) {
    // Utiliser une approche paresseuse pour éviter les erreurs
    if (function_exists('wp_upload_dir')) {
        define('PDF_BUILDER_PRO_UPLOADS_URL', wp_upload_dir()['baseurl'] . '/pdf-builder-pro/');
    } else {
        // Fallback temporaire - sera redéfini plus tard
        define('PDF_BUILDER_PRO_UPLOADS_URL', site_url('/wp-content/uploads/pdf-builder-pro/'));
    }
}

// Répertoire du cache
if (!defined('PDF_BUILDER_PRO_CACHE_DIR')) {
    define('PDF_BUILDER_PRO_CACHE_DIR', PDF_BUILDER_PRO_UPLOADS_DIR . 'cache/');
}

// Répertoire des logs
if (!defined('PDF_BUILDER_PRO_LOGS_DIR')) {
    define('PDF_BUILDER_PRO_LOGS_DIR', PDF_BUILDER_PRO_UPLOADS_DIR . 'logs/');
}

// === CONSTANTES DE CONFIGURATION ===

// Préfixe pour les options
if (!defined('PDF_BUILDER_OPTION_PREFIX')) {
    define('PDF_BUILDER_OPTION_PREFIX', 'pdf_builder_');
}

// Préfixe pour les transients
if (!defined('PDF_BUILDER_TRANSIENT_PREFIX')) {
    define('PDF_BUILDER_TRANSIENT_PREFIX', 'pdf_builder_');
}

// Préfixe pour les actions AJAX
if (!defined('PDF_BUILDER_AJAX_PREFIX')) {
    define('PDF_BUILDER_AJAX_PREFIX', 'pdf_builder_');
}

// === CONSTANTES DE SÉCURITÉ ===

// Clé de sécurité pour les nonces
if (!defined('PDF_BUILDER_NONCE_KEY')) {
    define('PDF_BUILDER_NONCE_KEY', 'pdf_builder_nonce_key');
}

// Action de sécurité pour les nonces
if (!defined('PDF_BUILDER_NONCE_ACTION')) {
    define('PDF_BUILDER_NONCE_ACTION', 'pdf_builder_nonce_action');
}

// === CONSTANTES DE PERFORMANCE ===

// Durée de vie du cache (en secondes)
if (!defined('PDF_BUILDER_CACHE_LIFETIME')) {
    define('PDF_BUILDER_CACHE_LIFETIME', 3600); // 1 heure
}

// Taille maximale du cache (en Mo)
if (!defined('PDF_BUILDER_MAX_CACHE_SIZE')) {
    define('PDF_BUILDER_MAX_CACHE_SIZE', 100); // 100 Mo
}

// Nombre maximum de fichiers en cache
if (!defined('PDF_BUILDER_MAX_CACHE_FILES')) {
    define('PDF_BUILDER_MAX_CACHE_FILES', 1000);
}

// === CONSTANTES DE LOGGING ===

// Niveau de log par défaut (1 = erreurs, 2 = avertissements, 3 = info, 4 = debug)
if (!defined('PDF_BUILDER_DEFAULT_LOG_LEVEL')) {
    define('PDF_BUILDER_DEFAULT_LOG_LEVEL', 2);
}

// Taille maximale du fichier de log (en octets)
if (!defined('PDF_BUILDER_MAX_LOG_SIZE')) {
    define('PDF_BUILDER_MAX_LOG_SIZE', 10485760); // 10 Mo
}

// Nombre de fichiers de log à conserver
if (!defined('PDF_BUILDER_MAX_LOG_FILES')) {
    define('PDF_BUILDER_MAX_LOG_FILES', 5);
}

// === CONSTANTES DE PDF ===

// Format de papier par défaut
if (!defined('PDF_BUILDER_DEFAULT_PAPER_SIZE')) {
    define('PDF_BUILDER_DEFAULT_PAPER_SIZE', 'A4');
}

// Orientation par défaut
if (!defined('PDF_BUILDER_DEFAULT_ORIENTATION')) {
    define('PDF_BUILDER_DEFAULT_ORIENTATION', 'portrait');
}

// Marge par défaut (en mm)
if (!defined('PDF_BUILDER_DEFAULT_MARGIN')) {
    define('PDF_BUILDER_DEFAULT_MARGIN', 10);
}

// Qualité d'image par défaut (DPI)
if (!defined('PDF_BUILDER_DEFAULT_DPI')) {
    define('PDF_BUILDER_DEFAULT_DPI', 150);
}

// === CONSTANTES DE BASE DE DONNÉES ===

// Préfixe des tables
if (!defined('PDF_BUILDER_DB_PREFIX')) {
    global $wpdb;
    define('PDF_BUILDER_DB_PREFIX', (isset($wpdb) && $wpdb) ? $wpdb->prefix . 'pdf_builder_' : 'wp_pdf_builder_');
}

// Nom de la table des templates
if (!defined('PDF_BUILDER_TEMPLATES_TABLE')) {
    define('PDF_BUILDER_TEMPLATES_TABLE', PDF_BUILDER_DB_PREFIX . 'templates');
}

// Nom de la table des PDFs générés
if (!defined('PDF_BUILDER_PDFS_TABLE')) {
    define('PDF_BUILDER_PDFS_TABLE', PDF_BUILDER_DB_PREFIX . 'pdfs');
}

// === CONSTANTES DE CAPACITÉS ===

// Capacité requise pour accéder à l'administration
if (!defined('PDF_BUILDER_ADMIN_CAPABILITY')) {
    define('PDF_BUILDER_ADMIN_CAPABILITY', 'manage_options');
}

// Capacité requise pour créer des templates
if (!defined('PDF_BUILDER_CREATE_CAPABILITY')) {
    define('PDF_BUILDER_CREATE_CAPABILITY', 'edit_posts');
}

// Capacité requise pour générer des PDFs
if (!defined('PDF_BUILDER_GENERATE_CAPABILITY')) {
    define('PDF_BUILDER_GENERATE_CAPABILITY', 'read');
}

// === CONSTANTES DE RÉSEAU ===

// Timeout pour les requêtes HTTP (en secondes)
if (!defined('PDF_BUILDER_HTTP_TIMEOUT')) {
    define('PDF_BUILDER_HTTP_TIMEOUT', 30);
}

// Nombre maximum de redirections
if (!defined('PDF_BUILDER_MAX_REDIRECTS')) {
    define('PDF_BUILDER_MAX_REDIRECTS', 5);
}

// === CONSTANTES DE LOCALISATION ===

// Domaine de texte pour les traductions
if (!defined('PDF_BUILDER_TEXT_DOMAIN')) {
    define('PDF_BUILDER_TEXT_DOMAIN', 'pdf-builder-pro');
}

// Chemin vers les fichiers de langue
if (!defined('PDF_BUILDER_LANGUAGES_DIR')) {
    define('PDF_BUILDER_LANGUAGES_DIR', PDF_BUILDER_PLUGIN_DIR . 'languages/');
}

// === CONFIGURATION AVANCÉE ===

// Activer le mode debug (true/false)
if (!defined('PDF_BUILDER_DEBUG_MODE')) {
    define('PDF_BUILDER_DEBUG_MODE', defined('WP_DEBUG') && WP_DEBUG);
}

// Activer la compression GZIP (true/false)
if (!defined('PDF_BUILDER_ENABLE_COMPRESSION')) {
    define('PDF_BUILDER_ENABLE_COMPRESSION', true);
}

// Activer la mise en cache (true/false)
if (!defined('PDF_BUILDER_ENABLE_CACHE')) {
    define('PDF_BUILDER_ENABLE_CACHE', true);
}

// Activer les logs (true/false)
if (!defined('PDF_BUILDER_ENABLE_LOGGING')) {
    define('PDF_BUILDER_ENABLE_LOGGING', true);
}

// === FONCTIONS UTILITAIRES DE CONFIGURATION ===

/**
 * Fonction de vérification ultra-rapide pour déterminer si le plugin doit se charger
 * @return bool
 */
function pdf_builder_should_load() {
    // Cache statique pour éviter les recalculs
    static $should_load = null;
    if ($should_load !== null) return $should_load;

    // Vérifications ultra-rapides
    if (isset($_GET['action']) && in_array($_GET['action'], ['activate', 'deactivate'], true)) {
        return $should_load = false;
    }

    // CHARGER LE BOOTSTRAP DÈS QU'ON EST DANS L'ADMIN pour afficher le menu
    if (is_admin()) {
        return $should_load = true;
    }

    // CHARGER LE BOOTSTRAP POUR LES REQUÊTES REST API du plugin
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($request_uri, '/wp-json/pdf-builder/') !== false) {
        return $should_load = true;
    }

    if (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') === 0) {
        return $should_load = true;
    }

    return $should_load = false;
}

/**
 * Obtenir une option du plugin avec préfixe
 */
function pdf_builder_get_option($key, $default = null) {
    return get_option(PDF_BUILDER_OPTION_PREFIX . $key, $default);
}

/**
 * Définir une option du plugin avec préfixe
 */
function pdf_builder_set_option($key, $value) {
    return update_option(PDF_BUILDER_OPTION_PREFIX . $key, $value);
}

/**
 * Supprimer une option du plugin
 */
function pdf_builder_delete_option($key) {
    return delete_option(PDF_BUILDER_OPTION_PREFIX . $key);
}

/**
 * Obtenir un transient avec préfixe
 */
function pdf_builder_get_transient($key) {
    return get_transient(PDF_BUILDER_TRANSIENT_PREFIX . $key);
}

/**
 * Définir un transient avec préfixe
 */
function pdf_builder_set_transient($key, $value, $expiration = null) {
    $expiration = $expiration ?: PDF_BUILDER_CACHE_LIFETIME;
    return set_transient(PDF_BUILDER_TRANSIENT_PREFIX . $key, $value, $expiration);
}

/**
 * Supprimer un transient
 */
function pdf_builder_delete_transient($key) {
    return delete_transient(PDF_BUILDER_TRANSIENT_PREFIX . $key);
}

/**
 * Vérifier si le mode debug est activé
 */
function pdf_builder_is_debug() {
    return PDF_BUILDER_DEBUG_MODE || pdf_builder_get_option('debug_mode', false);
}

/**
 * Obtenir le niveau de log actuel
 */
function pdf_builder_get_log_level() {
    return pdf_builder_get_option('log_level', PDF_BUILDER_DEFAULT_LOG_LEVEL);
}

/**
 * Vérifier si la mise en cache est activée
 */
function pdf_builder_cache_enabled() {
    return PDF_BUILDER_ENABLE_CACHE && pdf_builder_get_option('enable_cache', true);
}

/**
 * Vérifier si les logs sont activés
 */
function pdf_builder_logging_enabled() {
    return PDF_BUILDER_ENABLE_LOGGING && pdf_builder_get_option('enable_logging', true);
}


