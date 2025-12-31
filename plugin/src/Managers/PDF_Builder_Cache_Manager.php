<?php

/**
 * PDF Builder Pro - Cache Manager Centralisé
 * Système unifié de gestion du cache pour tous les composants du plugin
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

namespace PDF_Builder\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Classe principale de gestion du cache
 */
class PDF_Builder_Cache_Manager
{
    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;

    /**
     * Configuration du cache
     */
    private $config = [];

    /**
     * Préfixes pour les différents types de cache
     */
    private const PREFIXES = [
        'transient' => 'pdf_builder_cache_',
        'ajax' => 'pdf_builder_ajax_',
        'asset' => 'pdf_builder_asset_',
        'image' => 'pdf_builder_image_',
        'preview' => 'pdf_builder_preview_',
        'rate_limit' => 'pdf_builder_rate_limit_'
    ];

    /**
     * Types de cache disponibles
     */
    private const CACHE_TYPES = [
        'transient', // Cache WordPress standard
        'object',    // Cache d'objets (WP_Object_Cache)
        'file',      // Cache fichier
        'memory'     // Cache en mémoire PHP
    ];

    /**
     * Statistiques du cache
     */
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
        'clears' => 0
    ];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->loadConfiguration();
        $this->initializeCache();
        $this->setupCleanupHooks();
    }

    /**
     * Obtenir l'instance unique (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Charger la configuration du cache
     */
    private function loadConfiguration()
    {
        // Récupérer les paramètres de cache depuis la base de données
        $settings = get_option('pdf_builder_settings', []);

        $this->config = [
            // Cache général
            'enabled' => $settings['pdf_builder_cache_enabled'] ?? true,
            'default_ttl' => intval($settings['pdf_builder_cache_ttl'] ?? 3600), // 1 heure par défaut
            'max_size' => intval($settings['pdf_builder_cache_max_size'] ?? 100), // 100MB par défaut
            'compression' => $settings['pdf_builder_cache_compression'] ?? true,

            // Cache transients
            'transient_enabled' => $settings['pdf_builder_cache_transient_enabled'] ?? true,
            'transient_prefix' => $settings['pdf_builder_cache_transient_prefix'] ?? self::PREFIXES['transient'],

            // Cache d'assets
            'asset_cache_enabled' => $settings['pdf_builder_asset_cache_enabled'] ?? true,
            'asset_compression' => $settings['pdf_builder_asset_compression'] ?? true,
            'asset_minify' => $settings['pdf_builder_asset_minify'] ?? true,

            // Cache AJAX
            'ajax_cache_enabled' => $settings['pdf_builder_ajax_cache_enabled'] ?? true,
            'ajax_cache_ttl' => intval($settings['pdf_builder_ajax_cache_ttl'] ?? 300), // 5 minutes

            // Cache d'images
            'image_cache_enabled' => $settings['pdf_builder_image_cache_enabled'] ?? true,
            'image_max_memory' => intval($settings['pdf_builder_image_max_memory'] ?? 256), // MB

            // Cache des aperçus
            'preview_cache_enabled' => $settings['pdf_builder_preview_cache_enabled'] ?? true,
            'preview_cache_max_items' => intval($settings['pdf_builder_preview_cache_max_items'] ?? 50),

            // Nettoyage automatique
            'auto_cleanup' => $settings['pdf_builder_cache_auto_cleanup'] ?? true,
            'cleanup_interval' => intval($settings['pdf_builder_cache_cleanup_interval'] ?? 86400), // 24h

            // Debug
            'debug_mode' => $settings['pdf_builder_cache_debug'] ?? false,
            'stats_enabled' => $settings['pdf_builder_cache_stats'] ?? true
        ];

        // S'assurer que les valeurs sont dans les bonnes plages
        $this->validateConfiguration();
    }

    /**
     * Valider la configuration
     */
    private function validateConfiguration()
    {
        // TTL minimum 60 secondes, maximum 30 jours
        $this->config['default_ttl'] = max(60, min(2592000, $this->config['default_ttl']));

        // Taille maximum 10MB minimum, 1GB maximum
        $this->config['max_size'] = max(10, min(1024, $this->config['max_size']));

        // Mémoire image 32MB minimum, 1GB maximum
        $this->config['image_max_memory'] = max(32, min(1024, $this->config['image_max_memory']));
    }

    /**
     * Initialiser le cache
     */
    private function initializeCache()
    {
        if (!$this->config['enabled']) {
            return;
        }

        // Créer le répertoire de cache si nécessaire
        $this->ensureCacheDirectory();

        // Nettoyer les anciens caches au démarrage si activé
        if ($this->config['auto_cleanup']) {
            $this->scheduleCleanup();
        }

        // Log d'initialisation
        if ($this->config['debug_mode']) {
            error_log('[PDF Builder Cache] Cache Manager initialized with config: ' . json_encode($this->config));
        }
    }

    /**
     * S'assurer que le répertoire de cache existe
     */
    private function ensureCacheDirectory()
    {
        $cache_dir = $this->getCacheDirectory();
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);

            // Créer un fichier .htaccess pour la sécurité
            $htaccess = $cache_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
        }
    }

    /**
     * Obtenir le répertoire de cache
     */
    public function getCacheDirectory()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/pdf-builder-cache/';
    }

    /**
     * Configurer les hooks de nettoyage
     */
    private function setupCleanupHooks()
    {
        // Hook de nettoyage quotidien
        if (!wp_next_scheduled('pdf_builder_cache_cleanup')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cache_cleanup');
        }

        add_action('pdf_builder_cache_cleanup', [$this, 'cleanupExpiredCache']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'ajaxClearCache']);
        add_action('wp_ajax_pdf_builder_cache_stats', [$this, 'ajaxGetCacheStats']);
        add_action('wp_ajax_pdf_builder_get_ajax_cache', [$this, 'ajaxGetAjaxCache']);
        add_action('wp_ajax_pdf_builder_set_ajax_cache', [$this, 'ajaxSetAjaxCache']);
        add_action('wp_ajax_pdf_builder_cache_status', [$this, 'ajaxGetCacheStatus']);
        add_action('wp_ajax_nopriv_pdf_builder_cache_status', [$this, 'ajaxGetCacheStatus']); // Accessible sans login
    }

    /**
     * Programmer le nettoyage automatique
     */
    private function scheduleCleanup()
    {
        if (!wp_next_scheduled('pdf_builder_daily_cache_cleanup')) {
            wp_schedule_event(time() + $this->config['cleanup_interval'], 'daily', 'pdf_builder_daily_cache_cleanup');
        }
    }

    /* ========================================
       MÉTHODES PUBLIQUES DE CACHE
       ======================================== */

    /**
     * Définir une valeur en cache
     *
     * @param string $key Clé du cache
     * @param mixed $value Valeur à stocker
     * @param string $type Type de cache ('transient', 'ajax', 'asset', etc.)
     * @param int $ttl Durée de vie en secondes (optionnel)
     * @return bool Succès
     */
    public function set($key, $value, $type = 'transient', $ttl = null)
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $ttl = $ttl ?? $this->config['default_ttl'];
        $prefixed_key = $this->getPrefixedKey($key, $type);

        $success = false;

        switch ($type) {
            case 'transient':
                $success = $this->setTransientCache($prefixed_key, $value, $ttl);
                break;
            case 'object':
                $success = $this->setObjectCache($prefixed_key, $value, $ttl);
                break;
            case 'file':
                $success = $this->setFileCache($prefixed_key, $value, $ttl);
                break;
            case 'memory':
                $success = $this->setMemoryCache($prefixed_key, $value, $ttl);
                break;
            default:
                $success = $this->setTransientCache($prefixed_key, $value, $ttl);
        }

        if ($success) {
            $this->stats['sets']++;
            if ($this->config['debug_mode']) {
                error_log("[PDF Builder Cache] SET {$type}:{$key} (TTL: {$ttl}s)");
            }
        }

        return $success;
    }

    /**
     * Récupérer une valeur du cache
     *
     * @param string $key Clé du cache
     * @param string $type Type de cache
     * @return mixed Valeur ou false si non trouvée
     */
    public function get($key, $type = 'transient')
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $prefixed_key = $this->getPrefixedKey($key, $type);
        $value = false;

        switch ($type) {
            case 'transient':
                $value = $this->getTransientCache($prefixed_key);
                break;
            case 'object':
                $value = $this->getObjectCache($prefixed_key);
                break;
            case 'file':
                $value = $this->getFileCache($prefixed_key);
                break;
            case 'memory':
                $value = $this->getMemoryCache($prefixed_key);
                break;
        }

        if ($value !== false) {
            $this->stats['hits']++;
            if ($this->config['debug_mode']) {
                error_log("[PDF Builder Cache] HIT {$type}:{$key}");
            }
        } else {
            $this->stats['misses']++;
            if ($this->config['debug_mode']) {
                error_log("[PDF Builder Cache] MISS {$type}:{$key}");
            }
        }

        return $value;
    }

    /**
     * Supprimer une valeur du cache
     *
     * @param string $key Clé du cache
     * @param string $type Type de cache
     * @return bool Succès
     */
    public function delete($key, $type = 'transient')
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $prefixed_key = $this->getPrefixedKey($key, $type);
        $success = false;

        switch ($type) {
            case 'transient':
                $success = $this->deleteTransientCache($prefixed_key);
                break;
            case 'object':
                $success = $this->deleteObjectCache($prefixed_key);
                break;
            case 'file':
                $success = $this->deleteFileCache($prefixed_key);
                break;
            case 'memory':
                $success = $this->deleteMemoryCache($prefixed_key);
                break;
        }

        if ($success) {
            $this->stats['deletes']++;
            if ($this->config['debug_mode']) {
                error_log("[PDF Builder Cache] DELETE {$type}:{$key}");
            }
        }

        return $success;
    }

    /**
     * Vider tout le cache ou un type spécifique
     *
     * @param string $type Type de cache à vider (optionnel)
     * @return bool Succès
     */
    public function clear($type = null)
    {
        if (!$this->config['enabled']) {
            return false;
        }

        if ($type) {
            return $this->clearCacheByType($type);
        }

        // Vider tous les types de cache
        $success = true;
        foreach (self::CACHE_TYPES as $cache_type) {
            if (!$this->clearCacheByType($cache_type)) {
                $success = false;
            }
        }

        $this->stats['clears']++;

        if ($this->config['debug_mode']) {
            error_log("[PDF Builder Cache] CLEAR ALL");
        }

        return $success;
    }

    /**
     * Vérifier si une clé existe en cache
     *
     * @param string $key Clé du cache
     * @param string $type Type de cache
     * @return bool Existe
     */
    public function exists($key, $type = 'transient')
    {
        return $this->get($key, $type) !== false;
    }

    /**
     * Obtenir les statistiques du cache
     */
    public function getStats()
    {
        return array_merge($this->stats, [
            'config' => $this->config,
            'memory_usage' => $this->getMemoryUsage(),
            'cache_size' => $this->getCacheSize(),
            'uptime' => time() - $this->getStartTime()
        ]);
    }

    /* ========================================
       MÉTHODES SPÉCIALISÉES PAR TYPE
       ======================================== */

    /**
     * Cache AJAX spécialisé
     */
    public function setAjaxCache($action, $data, $result, $ttl = null)
    {
        if (!$this->config['ajax_cache_enabled']) {
            return false;
        }

        $key = $this->generateAjaxKey($action, $data);
        $ttl = $ttl ?? $this->config['ajax_cache_ttl'];

        return $this->set($key, $result, 'transient', $ttl);
    }

    public function getAjaxCache($action, $data)
    {
        if (!$this->config['ajax_cache_enabled']) {
            return false;
        }

        $key = $this->generateAjaxKey($action, $data);
        return $this->get($key, 'transient');
    }

    /**
     * Cache d'assets spécialisé
     */
    public function setAssetCache($filename, $content, $type = 'css', $ttl = null)
    {
        if (!$this->config['asset_cache_enabled']) {
            return false;
        }

        $key = $this->generateAssetKey($filename, $type);
        $ttl = $ttl ?? $this->config['default_ttl'];

        // Compression et minification si activées
        if ($this->config['asset_compression']) {
            $content = $this->compressAssetContent($content, $type);
        }

        if ($this->config['asset_minify']) {
            $content = $this->minifyAssetContent($content, $type);
        }

        return $this->set($key, $content, 'file', $ttl);
    }

    public function getAssetCache($filename, $type = 'css')
    {
        if (!$this->config['asset_cache_enabled']) {
            return false;
        }

        $key = $this->generateAssetKey($filename, $type);
        return $this->get($key, 'file');
    }

    /**
     * Vérifier si un asset est en cache
     */
    public function hasAssetCache($filename, $type = 'css')
    {
        if (!$this->config['asset_cache_enabled']) {
            return false;
        }

        $key = $this->generateAssetKey($filename, $type);
        return $this->exists($key, 'file');
    }

    /**
     * Supprimer un asset du cache
     */
    public function deleteAssetCache($filename, $type = 'css')
    {
        if (!$this->config['asset_cache_enabled']) {
            return false;
        }

        $key = $this->generateAssetKey($filename, $type);
        return $this->delete($key, 'file');
    }

    /**
     * Optimiser et mettre en cache un asset
     */
    public function optimizeAndCacheAsset($filename, $content, $type = 'css')
    {
        if (!$this->config['asset_cache_enabled']) {
            return $content; // Retourner le contenu original
        }

        // Vérifier si déjà en cache
        $cached = $this->getAssetCache($filename, $type);
        if ($cached !== false) {
            return $cached;
        }

        // Optimiser et mettre en cache
        $optimized = $this->optimizeAsset($content, $type);
        $this->setAssetCache($filename, $optimized, $type);

        return $optimized;
    }

    /**
     * Cache d'images spécialisé
     */
    public function setImageCache($url, $image_data, $metadata = [])
    {
        if (!$this->config['image_cache_enabled']) {
            return false;
        }

        $key = $this->generateImageKey($url);
        $data = [
            'image_data' => $image_data,
            'metadata' => $metadata,
            'cached_at' => time()
        ];

        return $this->set($key, $data, 'file', $this->config['default_ttl']);
    }

    public function getImageCache($url)
    {
        if (!$this->config['image_cache_enabled']) {
            return false;
        }

        $key = $this->generateImageKey($url);
        return $this->get($key, 'file');
    }

    /* ========================================
       IMPLÉMENTATIONS PAR TYPE DE CACHE
       ======================================== */

    private function setTransientCache($key, $value, $ttl)
    {
        return set_transient($key, $value, $ttl);
    }

    private function getTransientCache($key)
    {
        return get_transient($key);
    }

    private function deleteTransientCache($key)
    {
        return delete_transient($key);
    }

    private function setObjectCache($key, $value, $ttl)
    {
        return wp_cache_set($key, $value, 'pdf_builder', $ttl);
    }

    private function getObjectCache($key)
    {
        return wp_cache_get($key, 'pdf_builder');
    }

    private function deleteObjectCache($key)
    {
        return wp_cache_delete($key, 'pdf_builder');
    }

    private function setFileCache($key, $value, $ttl)
    {
        $filename = $this->getCacheDirectory() . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'compressed' => $this->config['compression']
        ];

        if ($this->config['compression']) {
            $data['value'] = gzcompress(serialize($value));
        } else {
            $data['value'] = serialize($value);
        }

        return file_put_contents($filename, serialize($data)) !== false;
    }

    private function getFileCache($key)
    {
        $filename = $this->getCacheDirectory() . md5($key) . '.cache';

        if (!file_exists($filename)) {
            return false;
        }

        $data = unserialize(file_get_contents($filename));

        // Vérifier l'expiration
        if ($data['expires'] < time()) {
            unlink($filename);
            return false;
        }

        // Décompresser si nécessaire
        if ($data['compressed']) {
            return unserialize(gzuncompress($data['value']));
        }

        return $data['value'];
    }

    private function deleteFileCache($key)
    {
        $filename = $this->getCacheDirectory() . md5($key) . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    private function setMemoryCache($key, $value, $ttl)
    {
        // Cache en mémoire PHP (statique)
        static $memory_cache = [];

        $memory_cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        return true;
    }

    private function getMemoryCache($key)
    {
        static $memory_cache = [];

        if (!isset($memory_cache[$key])) {
            return false;
        }

        $data = $memory_cache[$key];

        if ($data['expires'] < time()) {
            unset($memory_cache[$key]);
            return false;
        }

        return $data['value'];
    }

    private function deleteMemoryCache($key)
    {
        static $memory_cache = [];
        unset($memory_cache[$key]);
        return true;
    }

    /* ========================================
       UTILITAIRES
       ======================================== */

    private function getPrefixedKey($key, $type)
    {
        return self::PREFIXES[$type] . $key;
    }

    private function generateAjaxKey($action, $data)
    {
        return 'ajax_' . $action . '_' . md5(serialize($data));
    }

    private function generateAssetKey($filename, $type)
    {
        return 'asset_' . $type . '_' . md5($filename);
    }

    private function generateImageKey($url)
    {
        return 'image_' . md5($url);
    }

    private function compressAssetContent($content, $type)
    {
        // Compression GZIP pour les assets
        if (function_exists('gzcompress')) {
            return gzcompress($content, 6); // Niveau de compression 6
        }

        return $content;
    }

    private function minifyAssetContent($content, $type)
    {
        switch ($type) {
            case 'css':
                return $this->minifyCss($content);
            case 'js':
                return $this->minifyJs($content);
            case 'html':
                return $this->minifyHtml($content);
            default:
                return $content;
        }
    }

    private function optimizeAsset($content, $type)
    {
        // Appliquer compression et minification selon la configuration
        $optimized = $content;

        if ($this->config['asset_minify']) {
            $optimized = $this->minifyAssetContent($optimized, $type);
        }

        if ($this->config['asset_compression']) {
            $optimized = $this->compressAssetContent($optimized, $type);
        }

        return $optimized;
    }

    private function minifyCss($css)
    {
        // Minification CSS basique
        $css = preg_replace('/\/\*[^*]*\*+([^\/*][^*]*\*+)*\//', '', $css); // Commentaires
        $css = preg_replace('/\s+/', ' ', $css); // Espaces multiples
        $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css); // Espaces autour des caractères spéciaux
        $css = str_replace(';}', '}', $css); // Point-virgule avant accolade fermante

        return trim($css);
    }

    private function minifyJs($js)
    {
        // Minification JS basique
        $js = preg_replace('/\/\*.*?\*\//s', '', $js); // Commentaires multilignes
        $js = preg_replace('/\/\/.*$/m', '', $js); // Commentaires unilignes
        $js = preg_replace('/\s+/', ' ', $js); // Espaces multiples
        $js = preg_replace('/\s*([{}:;,=+\-*\/%&|!<>?()\[\]])\s*/', '$1', $js); // Espaces autour des opérateurs

        return trim($js);
    }

    private function minifyHtml($html)
    {
        // Minification HTML basique
        $html = preg_replace('/<!--.*?-->/s', '', $html); // Commentaires HTML
        $html = preg_replace('/>\s+</', '><', $html); // Espaces entre les balises
        $html = preg_replace('/\s+/', ' ', $html); // Espaces multiples

        return trim($html);
    }

    private function clearCacheByType($type)
    {
        switch ($type) {
            case 'transient':
                return $this->clearTransientCache();
            case 'object':
                return $this->clearObjectCache();
            case 'file':
                return $this->clearFileCache();
            case 'memory':
                return $this->clearMemoryCache();
        }
        return false;
    }

    private function clearTransientCache()
    {
        global $wpdb;

        // Supprimer tous les transients du plugin
        foreach (self::PREFIXES as $prefix) {
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $prefix . '%'
            ));
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_' . $prefix . '%'
            ));
        }

        return true;
    }

    private function clearObjectCache()
    {
        return wp_cache_flush();
    }

    private function clearFileCache()
    {
        $cache_dir = $this->getCacheDirectory();
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
        return true;
    }

    private function clearMemoryCache()
    {
        // Impossible de vider complètement le cache mémoire statique
        // On peut seulement marquer comme expiré
        return true;
    }

    private function getMemoryUsage()
    {
        $cache_dir = $this->getCacheDirectory();
        $size = 0;

        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*.cache');
            foreach ($files as $file) {
                $size += filesize($file);
            }
        }

        return $size;
    }

    private function getCacheSize()
    {
        // Compter le nombre d'éléments en cache
        global $wpdb;
        $count = 0;

        foreach (self::PREFIXES as $prefix) {
            $transient_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $prefix . '%'
            ));
            $count += intval($transient_count);
        }

        // Ajouter les fichiers cache
        $cache_dir = $this->getCacheDirectory();
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*.cache');
            $count += count($files);
        }

        return $count;
    }

    private function getStartTime()
    {
        static $start_time = null;
        if ($start_time === null) {
            $start_time = time();
        }
        return $start_time;
    }

    /* ========================================
       NETTOYAGE AUTOMATIQUE
       ======================================== */

    public function cleanupExpiredCache()
    {
        if (!$this->config['auto_cleanup']) {
            return;
        }

        // Nettoyer les fichiers expirés
        $this->cleanupExpiredFiles();

        // Nettoyer les transients expirés (WordPress le fait automatiquement)
        // Mais on peut forcer un nettoyage des transients du plugin

        if ($this->config['debug_mode']) {
            error_log('[PDF Builder Cache] Automatic cleanup completed');
        }
    }

    private function cleanupExpiredFiles()
    {
        $cache_dir = $this->getCacheDirectory();

        if (!is_dir($cache_dir)) {
            return;
        }

        $files = glob($cache_dir . '*.cache');
        $now = time();
        $deleted = 0;

        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < $now) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0 && $this->config['debug_mode']) {
            error_log("[PDF Builder Cache] Cleaned up {$deleted} expired cache files");
        }
    }

    /* ========================================
       ACTIONS AJAX
       ======================================== */

    public function ajaxClearCache()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $type = isset($_POST['cache_type']) ? sanitize_text_field($_POST['cache_type']) : null;

        $result = $this->clear($type);

        if ($result) {
            wp_send_json_success([
                'message' => 'Cache vidé avec succès',
                'type' => $type ?: 'all'
            ]);
        } else {
            wp_send_json_error('Erreur lors du vidage du cache');
        }
    }

    public function ajaxGetCacheStats()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        wp_send_json_success([
            'stats' => $this->getStats(),
            'config' => $this->config
        ]);
    }

    /**
     * AJAX - Récupérer du cache AJAX
     */
    public function ajaxGetAjaxCache()
    {
        if (!isset($_POST['cache_key'])) {
            wp_send_json_error('Clé de cache manquante');
        }

        $cache_key = sanitize_text_field($_POST['cache_key']);
        $cached_data = $this->getAjaxCache('generic', ['key' => $cache_key]);

        if ($cached_data !== false) {
            wp_send_json_success([
                'cached' => true,
                'value' => $cached_data
            ]);
        } else {
            wp_send_json_success([
                'cached' => false
            ]);
        }
    }

    /**
     * AJAX - Sauvegarder en cache AJAX
     */
    public function ajaxSetAjaxCache()
    {
        if (!isset($_POST['cache_key']) || !isset($_POST['value'])) {
            wp_send_json_error('Paramètres manquants');
        }

        $cache_key = sanitize_text_field($_POST['cache_key']);
        $value = json_decode(stripslashes($_POST['value']), true);
        $ttl = isset($_POST['ttl']) && !empty($_POST['ttl']) ? intval($_POST['ttl']) : null;

        $success = $this->setAjaxCache('generic', ['key' => $cache_key], $value, $ttl);

        if ($success) {
            wp_send_json_success(['message' => 'Données mises en cache']);
        } else {
            wp_send_json_error('Erreur lors de la mise en cache');
        }
    }

    /**
     * AJAX - Vérifier le statut du cache
     */
    public function ajaxGetCacheStatus()
    {
        wp_send_json_success([
            'cache_enabled' => $this->config['enabled'],
            'ajax_cache_enabled' => $this->config['ajax_cache_enabled'],
            'asset_cache_enabled' => $this->config['asset_cache_enabled'],
            'image_cache_enabled' => $this->config['image_cache_enabled'],
            'preview_cache_enabled' => $this->config['preview_cache_enabled']
        ]);
    }

    /* ========================================
       MÉTHODES STATIQUES UTILITAIRES
       ======================================== */

    /**
     * Raccourci pour obtenir l'instance
     */
    public static function instance()
    {
        return self::getInstance();
    }

    /**
     * Raccourci pour définir en cache
     */
    public static function setCache($key, $value, $type = 'transient', $ttl = null)
    {
        return self::instance()->set($key, $value, $type, $ttl);
    }

    /**
     * Raccourci pour récupérer du cache
     */
    public static function getCache($key, $type = 'transient')
    {
        return self::instance()->get($key, $type);
    }

    /**
     * Raccourci pour supprimer du cache
     */
    public static function deleteCache($key, $type = 'transient')
    {
        return self::instance()->delete($key, $type);
    }

    /**
     * Raccourci pour vider le cache
     */
    public static function clearCache($type = null)
    {
        return self::instance()->clear($type);
    }
}

// Initialiser le cache manager au chargement du plugin
add_action('plugins_loaded', function() {
    PDF_Builder_Cache_Manager::getInstance();
});
