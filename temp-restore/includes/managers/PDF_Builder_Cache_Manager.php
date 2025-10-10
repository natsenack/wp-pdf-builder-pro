<?php
/**
 * Gestionnaire de Cache Multi-Niveau - PDF Builder Pro
 *
 * Architecture de cache avancée :
 * - Cache objet (Redis/Memcached)
 * - Cache fichier
 * - Cache transient WordPress
 * - Cache mémoire (statique)
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Cache
 */
class PDF_Builder_Cache_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Cache_Manager
     */
    private static $instance = null;

    /**
     * Cache mémoire (statique)
     * @var array
     */
    private static $memory_cache = [];

    /**
     * Cache Redis/Memcached
     * @var mixed
     */
    private $object_cache = null;

    /**
     * Cache fichier
     * @var string
     */
    private $file_cache_dir;

    /**
     * Configuration du cache
     * @var array
     */
    private $config = [];

    /**
     * Métriques de performance
     * @var array
     */
    private $metrics = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_config();
        $this->init_object_cache();
        $this->init_file_cache();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Cache_Manager
     */
    public static function getInstance(): PDF_Builder_Cache_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation de la configuration
     *
     * @return void
     */
    private function init_config(): void {
        $this->config = [
            'enabled' => function_exists('get_option') ? get_option('pdf_builder_cache_enabled', true) : true,
            'ttl' => function_exists('get_option') ? get_option('pdf_builder_cache_ttl', PDF_BUILDER_CACHE_TTL) : PDF_BUILDER_CACHE_TTL,
            'object_cache_enabled' => $this->is_object_cache_available(),
            'file_cache_enabled' => true,
            'memory_cache_enabled' => true,
            'compression_enabled' => true,
            'max_memory_items' => 1000,
            'cleanup_probability' => 0.01 // 1% de chance de nettoyage
        ];
    }

    /**
     * Initialisation du cache objet
     *
     * @return void
     */
    private function init_object_cache(): void {
        if (!$this->config['object_cache_enabled']) {
            return;
        }

        // Redis
        if (class_exists('Redis')) {
            try {
                $this->object_cache = new Redis();
                $this->object_cache->connect('127.0.0.1', 6379);
                $this->object_cache->select(1); // Base de données dédiée
            } catch (Exception $e) {
                $this->object_cache = null;
            }
        }

        // Memcached
        if (!$this->object_cache && class_exists('Memcached')) {
            try {
                $this->object_cache = new Memcached();
                $this->object_cache->addServer('127.0.0.1', 11211);
            } catch (Exception $e) {
                $this->object_cache = null;
            }
        }
    }

    /**
     * Initialisation du cache fichier
     *
     * @return void
     */
    private function init_file_cache(): void {
        if (function_exists('wp_upload_dir')) {
            $upload_dir = wp_upload_dir();
            $this->file_cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';
        } else {
            // Fallback pour les tests hors WordPress
            $this->file_cache_dir = sys_get_temp_dir() . '/pdf-builder-cache/';
        }

        if (!file_exists($this->file_cache_dir)) {
            if (function_exists('wp_mkdir_p')) {
                wp_mkdir_p($this->file_cache_dir);
            } else {
                mkdir($this->file_cache_dir, 0755, true);
            }
        }

        // Créer le fichier .htaccess pour la sécurité (uniquement si WordPress est chargé)
        if (function_exists('wp_upload_dir')) {
            $htaccess_file = $this->file_cache_dir . '.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, "Deny from all\n");
            }
        }
    }

    /**
     * Vérifier si le cache objet est disponible
     *
     * @return bool
     */
    private function is_object_cache_available(): bool {
        return function_exists('wp_cache_get') && wp_using_ext_object_cache();
    }

    /**
     * Obtenir une valeur du cache
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key) {
        if (!$this->config['enabled']) {
            return null;
        }

        $cache_key = $this->get_cache_key($key);

        // Cache mémoire (le plus rapide)
        if ($this->config['memory_cache_enabled'] && isset(self::$memory_cache[$cache_key])) {
            $this->metrics['hits']++;
            return $this->unserialize_data(self::$memory_cache[$cache_key]);
        }

        // Cache objet
        if ($this->object_cache) {
            $data = $this->get_from_object_cache($cache_key);
            if ($data !== false) {
                $this->metrics['hits']++;
                // Stocker en mémoire pour les accès futurs
                if ($this->config['memory_cache_enabled']) {
                    self::$memory_cache[$cache_key] = $data;
                }
                return $this->unserialize_data($data);
            }
        }

        // Cache transient WordPress
        $data = get_transient($cache_key);
        if ($data !== false) {
            $this->metrics['hits']++;
            if ($this->config['memory_cache_enabled']) {
                self::$memory_cache[$cache_key] = $data;
            }
            return $this->unserialize_data($data);
        }

        // Cache fichier
        if ($this->config['file_cache_enabled']) {
            $data = $this->get_from_file_cache($cache_key);
            if ($data !== false) {
                $this->metrics['hits']++;
                if ($this->config['memory_cache_enabled']) {
                    self::$memory_cache[$cache_key] = $data;
                }
                return $this->unserialize_data($data);
            }
        }

        $this->metrics['misses']++;
        return null;
    }

    /**
     * Définir une valeur dans le cache
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool {
        if (!$this->config['enabled']) {
            return false;
        }

        $cache_key = $this->get_cache_key($key);
        $ttl = $ttl ?? $this->config['ttl'];
        $serialized_data = $this->serialize_data($value);

        $this->metrics['sets']++;

        // Cache mémoire
        if ($this->config['memory_cache_enabled']) {
            self::$memory_cache[$cache_key] = $serialized_data;
            $this->cleanup_memory_cache();
        }

        // Cache objet
        if ($this->object_cache) {
            $this->set_in_object_cache($cache_key, $serialized_data, $ttl);
        }

        // Cache transient WordPress
        set_transient($cache_key, $serialized_data, $ttl);

        // Cache fichier
        if ($this->config['file_cache_enabled']) {
            $this->set_in_file_cache($cache_key, $serialized_data, $ttl);
        }

        return true;
    }

    /**
     * Supprimer une valeur du cache
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool {
        if (!$this->config['enabled']) {
            return false;
        }

        $cache_key = $this->get_cache_key($key);

        $this->metrics['deletes']++;

        // Cache mémoire
        if ($this->config['memory_cache_enabled'] && isset(self::$memory_cache[$cache_key])) {
            unset(self::$memory_cache[$cache_key]);
        }

        // Cache objet
        if ($this->object_cache) {
            $this->delete_from_object_cache($cache_key);
        }

        // Cache transient
        delete_transient($cache_key);

        // Cache fichier
        if ($this->config['file_cache_enabled']) {
            $this->delete_from_file_cache($cache_key);
        }

        return true;
    }

    /**
     * Vider tout le cache
     *
     * @return bool
     */
    public function flush(): bool {
        if (!$this->config['enabled']) {
            return false;
        }

        // Cache mémoire
        self::$memory_cache = [];

        // Cache objet
        if ($this->object_cache) {
            $this->flush_object_cache();
        }

        // Cache transient (suppression par pattern)
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");

        // Cache fichier
        if ($this->config['file_cache_enabled']) {
            $this->flush_file_cache();
        }

        return true;
    }

    /**
     * Nettoyer le cache expiré
     *
     * @return void
     */
    public function cleanup_expired(): void {
        // Nettoyer le cache mémoire si nécessaire
        if ($this->config['memory_cache_enabled'] && mt_rand(1, 100) <= ($this->config['cleanup_probability'] * 100)) {
            $this->cleanup_memory_cache();
        }

        // Nettoyer le cache fichier
        if ($this->config['file_cache_enabled']) {
            $this->cleanup_file_cache();
        }
    }

    /**
     * Obtenir les statistiques du cache
     *
     * @return array
     */
    public function get_stats(): array {
        $total_requests = $this->metrics['hits'] + $this->metrics['misses'];
        $hit_rate = $total_requests > 0 ? ($this->metrics['hits'] / $total_requests) * 100 : 0;

        return [
            'enabled' => $this->config['enabled'],
            'hits' => $this->metrics['hits'],
            'misses' => $this->metrics['misses'],
            'sets' => $this->metrics['sets'],
            'deletes' => $this->metrics['deletes'],
            'hit_rate' => round($hit_rate, 2),
            'memory_items' => count(self::$memory_cache),
            'object_cache_available' => $this->object_cache !== null,
            'file_cache_available' => $this->config['file_cache_enabled']
        ];
    }

    /**
     * Générer une clé de cache
     *
     * @param string $key
     * @return string
     */
    private function get_cache_key(string $key): string {
        return 'pdf_builder_' . md5($key);
    }

    /**
     * Sérialiser les données
     *
     * @param mixed $data
     * @return string
     */
    private function serialize_data($data): string {
        if ($this->config['compression_enabled'] && function_exists('gzcompress')) {
            return gzcompress(serialize($data));
        }
        return serialize($data);
    }

    /**
     * Désérialiser les données
     *
     * @param string $data
     * @return mixed
     */
    private function unserialize_data(string $data) {
        if ($this->config['compression_enabled'] && function_exists('gzuncompress')) {
            $data = gzuncompress($data);
        }
        return unserialize($data);
    }

    /**
     * Obtenir du cache objet
     *
     * @param string $key
     * @return mixed
     */
    private function get_from_object_cache(string $key) {
        if ($this->object_cache instanceof Redis) {
            return $this->object_cache->get($key);
        } elseif ($this->object_cache instanceof Memcached) {
            return $this->object_cache->get($key);
        }
        return wp_cache_get($key, 'pdf_builder');
    }

    /**
     * Définir dans le cache objet
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return void
     */
    private function set_in_object_cache(string $key, $value, int $ttl): void {
        if ($this->object_cache instanceof Redis) {
            $this->object_cache->setex($key, $ttl, $value);
        } elseif ($this->object_cache instanceof Memcached) {
            $this->object_cache->set($key, $value, $ttl);
        } else {
            wp_cache_set($key, $value, 'pdf_builder', $ttl);
        }
    }

    /**
     * Supprimer du cache objet
     *
     * @param string $key
     * @return void
     */
    private function delete_from_object_cache(string $key): void {
        if ($this->object_cache instanceof Redis) {
            $this->object_cache->del($key);
        } elseif ($this->object_cache instanceof Memcached) {
            $this->object_cache->delete($key);
        } else {
            wp_cache_delete($key, 'pdf_builder');
        }
    }

    /**
     * Vider le cache objet
     *
     * @return void
     */
    private function flush_object_cache(): void {
        if ($this->object_cache instanceof Redis) {
            $this->object_cache->flushdb();
        } elseif ($this->object_cache instanceof Memcached) {
            $this->object_cache->flush();
        } else {
            wp_cache_flush();
        }
    }

    /**
     * Obtenir du cache fichier
     *
     * @param string $key
     * @return mixed
     */
    private function get_from_file_cache(string $key) {
        $file_path = $this->file_cache_dir . $key . '.cache';

        if (!file_exists($file_path)) {
            return false;
        }

        $data = file_get_contents($file_path);
        if ($data === false) {
            return false;
        }

        $cache_data = unserialize($data);
        if (!$cache_data || !isset($cache_data['expires']) || !isset($cache_data['data'])) {
            return false;
        }

        if (time() > $cache_data['expires']) {
            unlink($file_path);
            return false;
        }

        return $cache_data['data'];
    }

    /**
     * Définir dans le cache fichier
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return void
     */
    private function set_in_file_cache(string $key, $value, int $ttl): void {
        $file_path = $this->file_cache_dir . $key . '.cache';

        $cache_data = [
            'data' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        file_put_contents($file_path, serialize($cache_data));
    }

    /**
     * Supprimer du cache fichier
     *
     * @param string $key
     * @return void
     */
    private function delete_from_file_cache(string $key): void {
        $file_path = $this->file_cache_dir . $key . '.cache';
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    /**
     * Vider le cache fichier
     *
     * @return void
     */
    private function flush_file_cache(): void {
        $files = glob($this->file_cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Nettoyer le cache fichier
     *
     * @return void
     */
    private function cleanup_file_cache(): void {
        $files = glob($this->file_cache_dir . '*.cache');
        foreach ($files as $file) {
            $data = file_get_contents($file);
            if ($data !== false) {
                $cache_data = unserialize($data);
                if ($cache_data && isset($cache_data['expires']) && time() > $cache_data['expires']) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Nettoyer le cache mémoire
     *
     * @return void
     */
    private function cleanup_memory_cache(): void {
        if (count(self::$memory_cache) > $this->config['max_memory_items']) {
            // Garder seulement les éléments les plus récents
            arsort(self::$memory_cache);
            self::$memory_cache = array_slice(self::$memory_cache, 0, $this->config['max_memory_items'], true);
        }
    }

    /**
     * Initialisation du gestionnaire
     *
     * @return void
     */
    public function init(): void {
        // Actions d'initialisation supplémentaires si nécessaire
    }
}

