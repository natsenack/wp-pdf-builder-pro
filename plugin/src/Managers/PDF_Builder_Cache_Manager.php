<?php

namespace WP_PDF_Builder_Pro\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Cache Manager
 * Gestionnaire de cache pour le plugin PDF Builder Pro
 */



class PdfBuilderCacheManager
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Préfixe pour les clés de cache
     */
    private $cache_prefix = 'pdf_builder_';

    /**
     * Durée de vie du cache (en secondes)
     */
    private $cache_expiration = 3600; // 1 heure

    /**
     * Cache activé ou désactivé
     */
    private $cache_enabled = true;

    /**
     * Constructeur privé
     */
    private function __construct()
    {
        // Utiliser les paramètres de configuration du plugin
        $this->loadCacheSettings();

        // Nettoyer le cache automatiquement
        add_action('wp_scheduled_delete', array($this, 'cleanup_expired_cache'));
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
     * Générer une clé de cache
     */
    private function generateKey($key)
    {
        return $this->cache_prefix . md5($key);
    }

    /**
     * Définir une valeur en cache
     */
    public function set($key, $value, $expiration = null)
    {
        // ✅ CACHE DÉSACTIVÉ - ne rien mettre en cache
        return false;
    }

    /**
     * Obtenir une valeur du cache
     */
    public function get($key, $default = null)
    {
        // ✅ CACHE DÉSACTIVÉ - toujours retourner la valeur par défaut
        return $default;
    }

    /**
     * Vérifier si une clé existe en cache
     */
    public function exists($key)
    {
        // Vérifier si le cache est activé
        if (!$this->isEnabled()) {
            return false;
        }

        $cache_key = $this->generate_key($key);
        return false !== get_transient($cache_key);
    }

    /**
     * Alias pour exists() - Vérifier si une clé existe en cache
     */
    public function has($key)
    {
        return $this->exists($key);
    }

    /**
     * Supprimer une valeur du cache
     */
    public function delete($key)
    {
        // ✅ CACHE DÉSACTIVÉ - rien à supprimer
        return false;
    }

    /**
     * Vider tout le cache du plugin
     */
    public function flush()
    {
        // ✅ CACHE DÉSACTIVÉ - rien à vider
        return 0;
    }

    /**
     * Nettoyer le cache expiré
     */
    public function cleanupExpiredCache()
    {
        // Vérifier si le cache est activé
        if (!$this->isEnabled()) {
            return;
        }

        // WordPress gère automatiquement la suppression des transients expirés
        // Cette méthode peut être utilisée pour un nettoyage manuel si nécessaire
        if (function_exists('pdf_builder_log')) {
            pdf_builder_log("Expired cache cleanup completed", 2);
        }
    }

    /**
     * Obtenir les statistiques du cache
     */
    public function getStats()
    {
        global $wpdb;

        $pattern = $this->cache_prefix . '%';
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );

        return array(
            'total_entries' => intval($count),
            'cache_prefix' => $this->cache_prefix,
            'expiration' => $this->cache_expiration
        );
    }

    /**
     * Précharger des données en cache
     */
    public function preload($keys)
    {
        // Vérifier si le cache est activé
        if (!$this->isEnabled()) {
            return;
        }

        foreach ($keys as $key) {
            if (!$this->exists($key)) {
                // Ici, vous pouvez implémenter la logique pour charger les données
                // Par exemple, charger des templates, des configurations, etc.
                if (function_exists('pdf_builder_log')) {
                    pdf_builder_log("Preloading cache for: $key", 3);
                }
            }
        }
    }

    /**
     * Charger les paramètres de cache depuis la configuration du plugin
     */
    private function loadCacheSettings()
    {
        $settings = get_option('pdf_builder_settings', []);

        // Vérifier si le cache est activé
        $this->cache_enabled = !empty($settings['cache_enabled']);

        // Utiliser la TTL configurée ou la valeur par défaut
        $this->cache_expiration = intval($settings['cache_ttl'] ?? $this->cache_expiration);
    }
    /**
     * Vérifier si le cache est activé
     */
    public function isEnabled()
    {
        // ✅ CACHE TOUJOURS DÉSACTIVÉ
        return false;
    }
}

// Fonctions globales pour le cache
function pdf_builder_cache_set($key, $value, $expiration = null)
{
    return PDF_Builder_Cache_Manager::getInstance()->set($key, $value, $expiration);
}

function pdf_builder_cache_get($key, $default = null)
{
    return PDF_Builder_Cache_Manager::getInstance()->get($key, $default);
}

function pdf_builder_cache_exists($key)
{
    return PDF_Builder_Cache_Manager::getInstance()->exists($key);
}

function pdf_builder_cache_delete($key)
{
    return PDF_Builder_Cache_Manager::getInstance()->delete($key);
}

function pdf_builder_cache_flush()
{
    return PDF_Builder_Cache_Manager::getInstance()->flush();
}
