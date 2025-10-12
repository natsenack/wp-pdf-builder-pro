<?php
/**
 * PDF Builder Cache Manager
 * Gestionnaire de cache pour le plugin PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

class PDF_Builder_Cache_Manager {

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
     * Constructeur privé
     */
    private function __construct() {
        $this->cache_expiration = defined('PDF_BUILDER_CACHE_EXPIRATION') ? PDF_BUILDER_CACHE_EXPIRATION : $this->cache_expiration;

        // Nettoyer le cache automatiquement
        add_action('wp_scheduled_delete', array($this, 'cleanup_expired_cache'));
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Générer une clé de cache
     */
    private function generate_key($key) {
        return $this->cache_prefix . md5($key);
    }

    /**
     * Définir une valeur en cache
     */
    public function set($key, $value, $expiration = null) {
        if (null === $expiration) {
            $expiration = $this->cache_expiration;
        }

        $cache_key = $this->generate_key($key);

        // Utiliser le cache WordPress transient
        set_transient($cache_key, $value, $expiration);

        // Logger l'action
        pdf_builder_log("Cache set: $key", 3, array('expiration' => $expiration));
    }

    /**
     * Obtenir une valeur du cache
     */
    public function get($key, $default = null) {
        $cache_key = $this->generate_key($key);
        $value = get_transient($cache_key);

        if (false === $value) {
            return $default;
        }

        return $value;
    }

    /**
     * Vérifier si une clé existe en cache
     */
    public function exists($key) {
        $cache_key = $this->generate_key($key);
        return false !== get_transient($cache_key);
    }

    /**
     * Supprimer une valeur du cache
     */
    public function delete($key) {
        $cache_key = $this->generate_key($key);
        $deleted = delete_transient($cache_key);

        if ($deleted) {
            pdf_builder_log("Cache deleted: $key", 2);
        }

        return $deleted;
    }

    /**
     * Vider tout le cache du plugin
     */
    public function flush() {
        global $wpdb;

        $pattern = $this->cache_prefix . '%';
        $deleted = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );

        pdf_builder_log("Cache flushed: $deleted entries deleted", 1);

        return $deleted;
    }

    /**
     * Nettoyer le cache expiré
     */
    public function cleanup_expired_cache() {
        // WordPress gère automatiquement la suppression des transients expirés
        // Cette méthode peut être utilisée pour un nettoyage manuel si nécessaire
        pdf_builder_log("Expired cache cleanup completed", 2);
    }

    /**
     * Obtenir les statistiques du cache
     */
    public function get_stats() {
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
    public function preload($keys) {
        foreach ($keys as $key) {
            if (!$this->exists($key)) {
                // Ici, vous pouvez implémenter la logique pour charger les données
                // Par exemple, charger des templates, des configurations, etc.
                pdf_builder_log("Preloading cache for: $key", 3);
            }
        }
    }
}

// Fonctions globales pour le cache
function pdf_builder_cache_set($key, $value, $expiration = null) {
    return PDF_Builder_Cache_Manager::getInstance()->set($key, $value, $expiration);
}

function pdf_builder_cache_get($key, $default = null) {
    return PDF_Builder_Cache_Manager::getInstance()->get($key, $default);
}

function pdf_builder_cache_exists($key) {
    return PDF_Builder_Cache_Manager::getInstance()->exists($key);
}

function pdf_builder_cache_delete($key) {
    return PDF_Builder_Cache_Manager::getInstance()->delete($key);
}

function pdf_builder_cache_flush() {
    return PDF_Builder_Cache_Manager::getInstance()->flush();
}

