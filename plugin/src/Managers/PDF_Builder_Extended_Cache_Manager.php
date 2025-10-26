<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Cache Manager Étendu
 * Gestion multi-niveaux du cache avec optimisations de performance
 */

class PDF_Builder_Extended_Cache_Manager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Cache mémoire (transient)
     */
    private $memory_cache = [];

    /**
     * Cache fichier
     */
    private $file_cache_dir;

    /**
     * Cache base de données
     */
    private $db_cache_table = 'pdf_builder_cache';

    /**
     * Configuration du cache
     */
    private $cache_config = [
        'memory_ttl' => 300,      // 5 minutes
        'file_ttl' => 3600,       // 1 heure
        'db_ttl' => 86400,        // 24 heures
        'max_memory_items' => 100,
        'compression_enabled' => true,
        'auto_cleanup' => true
    ];

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initialize_cache();
    }

    /**
     * Initialiser le système de cache
     */
    private function initialize_cache()
    {
        // Initialiser le cache fichier
        $upload_dir = wp_upload_dir();
        $this->file_cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache-extended';
        if (!file_exists($this->file_cache_dir)) {
            wp_mkdir_p($this->file_cache_dir);
        }

        // Créer les sous-répertoires
        $subdirs = ['pdfs', 'html', 'assets', 'temp'];
        foreach ($subdirs as $subdir) {
            $dir = $this->file_cache_dir . '/' . $subdir;
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }

        // Initialiser la table DB si nécessaire
        $this->ensure_db_table();

        // Nettoyer automatiquement si activé
        if ($this->cache_config['auto_cleanup']) {
            $this->schedule_cleanup();
        }
    }

    /**
     * S'assurer que la table DB existe
     */
    private function ensure_db_table()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            cache_key varchar(255) NOT NULL,
            cache_value longtext NOT NULL,
            cache_type varchar(50) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime NOT NULL,
            PRIMARY KEY (cache_key),
            KEY cache_type (cache_type),
            KEY expires_at (expires_at)
        ) $charset_collate;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Stocker en cache (multi-niveaux)
     *
     * @param  string $key   Clé du
     *                       cache
     * @param  mixed  $value Valeur à
     *                       stocker
     * @param  string $type  Type de cache (pdf, html, asset, etc.)
     * @param  int    $ttl   Durée de vie
     *                       en secondes
     * @return bool Succès
     */
    public function set($key, $value, $type = 'general', $ttl = null)
    {
        if (!$ttl) {
            $ttl = $this->get_default_ttl($type);
        }

        $success = true;

        // Cache mémoire
        $this->set_memory_cache($key, $value, $ttl);

        // Cache fichier (pour données volumineuses)
        if ($this->should_use_file_cache($value)) {
            $file_success = $this->set_file_cache($key, $value, $type, $ttl);
            $success = $success && $file_success;
        }

        // Cache DB (pour données persistantes)
        $db_success = $this->set_db_cache($key, $value, $type, $ttl);
        $success = $success && $db_success;

        return $success;
    }

    /**
     * Récupérer du cache (multi-niveaux)
     *
     * @param  string $key  Clé du
     *                      cache
     * @param  string $type Type de cache
     * @return mixed Valeur ou false si non trouvée
     */
    public function get($key, $type = 'general')
    {
        // 1. Vérifier le cache mémoire (le plus rapide)
        $value = $this->get_memory_cache($key);
        if ($value !== false) {
            return $value;
        }

        // 2. Vérifier le cache fichier
        $value = $this->get_file_cache($key, $type);
        if ($value !== false) {
            // Remettre en mémoire pour accélérer les accès futurs
            $this->set_memory_cache($key, $value, $this->cache_config['memory_ttl']);
            return $value;
        }

        // 3. Vérifier le cache DB
        $value = $this->get_db_cache($key, $type);
        if ($value !== false) {
            // Remettre en mémoire et fichier si volumineux
            $this->set_memory_cache($key, $value, $this->cache_config['memory_ttl']);
            if ($this->should_use_file_cache($value)) {
                $this->set_file_cache($key, $value, $type, $this->cache_config['file_ttl']);
            }
            return $value;
        }

        return false;
    }

    /**
     * Vérifier si une clé existe en cache
     */
    public function exists($key, $type = 'general')
    {
        return $this->get($key, $type) !== false;
    }

    /**
     * Supprimer du cache
     */
    public function delete($key, $type = 'general')
    {
        $this->delete_memory_cache($key);
        $this->delete_file_cache($key, $type);
        $this->delete_db_cache($key, $type);
    }

    /**
     * Vider tout le cache ou par type
     */
    public function clear($type = null)
    {
        if ($type) {
            $this->clear_memory_cache_by_type($type);
            $this->clear_file_cache_by_type($type);
            $this->clear_db_cache_by_type($type);
        } else {
            $this->clear_all_memory_cache();
            $this->clear_all_file_cache();
            $this->clear_all_db_cache();
        }
    }

    /**
     * Cache mémoire
     */
    private function set_memory_cache($key, $value, $ttl)
    {
        $this->memory_cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        // Limiter la taille du cache mémoire
        if (count($this->memory_cache) > $this->cache_config['max_memory_items']) {
            $this->cleanup_memory_cache();
        }
    }

    private function get_memory_cache($key)
    {
        if (!isset($this->memory_cache[$key])) {
            return false;
        }

        $item = $this->memory_cache[$key];
        if (time() > $item['expires']) {
            unset($this->memory_cache[$key]);
            return false;
        }

        return $item['value'];
    }

    private function delete_memory_cache($key)
    {
        unset($this->memory_cache[$key]);
    }

    private function clear_all_memory_cache()
    {
        $this->memory_cache = [];
    }

    private function clear_memory_cache_by_type($type)
    {
        // Le cache mémoire ne différencie pas par type, on nettoie tout
        $this->clear_all_memory_cache();
    }

    private function cleanup_memory_cache()
    {
        $now = time();
        foreach ($this->memory_cache as $key => $item) {
            if ($now > $item['expires']) {
                unset($this->memory_cache[$key]);
            }
        }

        // Si toujours trop d'éléments, supprimer les plus anciens
        if (count($this->memory_cache) > $this->cache_config['max_memory_items']) {
            uasort(
                $this->memory_cache,
                function ($a, $b) {
                    return $a['expires'] <=> $b['expires'];
                }
            );

            $items_to_remove = count($this->memory_cache) - $this->cache_config['max_memory_items'];
            $this->memory_cache = array_slice($this->memory_cache, $items_to_remove, null, true);
        }
    }

    /**
     * Cache fichier
     */
    private function set_file_cache($key, $value, $type, $ttl)
    {
        $cache_file = $this->get_cache_file_path($key, $type);

        $data = [
            'key' => $key,
            'value' => $value,
            'expires' => time() + $ttl,
            'type' => $type
        ];

        $serialized = serialize($data);

        // Compression si activée
        if ($this->cache_config['compression_enabled']) {
            $serialized = gzcompress($serialized);
        }

        return file_put_contents($cache_file, $serialized) !== false;
    }

    private function get_file_cache($key, $type)
    {
        $cache_file = $this->get_cache_file_path($key, $type);

        if (!file_exists($cache_file)) {
            return false;
        }

        $data = file_get_contents($cache_file);
        if ($data === false) {
            return false;
        }

        // Décompression si nécessaire
        if ($this->cache_config['compression_enabled']) {
            $data = gzuncompress($data);
        }

        $unserialized = unserialize($data);
        if (!$unserialized || !isset($unserialized['expires'])) {
            return false;
        }

        if (time() > $unserialized['expires']) {
            unlink($cache_file);
            return false;
        }

        return $unserialized['value'];
    }

    private function delete_file_cache($key, $type)
    {
        $cache_file = $this->get_cache_file_path($key, $type);
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    }

    private function clear_all_file_cache()
    {
        $this->delete_directory_contents($this->file_cache_dir);
    }

    private function clear_file_cache_by_type($type)
    {
        $type_dir = $this->file_cache_dir . '/' . $type;
        if (file_exists($type_dir)) {
            $this->delete_directory_contents($type_dir);
        }
    }

    /**
     * Cache base de données
     */
    private function set_db_cache($key, $value, $type, $ttl)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;
        $expires_at = date('Y-m-d H:i:s', time() + $ttl);

        $data = [
            'cache_value' => maybe_serialize($value),
            'cache_type' => $type,
            'expires_at' => $expires_at
        ];

        $result = $wpdb->replace($table_name, array_merge(['cache_key' => $key], $data));

        return $result !== false;
    }

    private function get_db_cache($key, $type)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT cache_value, expires_at FROM $table_name
             WHERE cache_key = %s AND cache_type = %s AND expires_at > NOW()",
                $key,
                $type
            )
        );

        if (!$result) {
            return false;
        }

        return maybe_unserialize($result->cache_value);
    }

    private function delete_db_cache($key, $type)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;

        $wpdb->delete(
            $table_name,
            [
            'cache_key' => $key,
            'cache_type' => $type
            ]
        );
    }

    private function clear_all_db_cache()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;
        $wpdb->query("TRUNCATE TABLE $table_name");
    }

    private function clear_db_cache_by_type($type)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;
        $wpdb->delete($table_name, ['cache_type' => $type]);
    }

    /**
     * Utilitaires
     */
    private function get_cache_file_path($key, $type)
    {
        $subdir = $this->file_cache_dir . '/' . $type;
        if (!file_exists($subdir)) {
            wp_mkdir_p($subdir);
        }

        $safe_key = md5($key);
        return $subdir . '/' . $safe_key . '.cache';
    }

    private function should_use_file_cache($value)
    {
        // Utiliser le cache fichier pour les données volumineuses
        $serialized = serialize($value);
        return strlen($serialized) > 10000; // Plus de 10KB
    }

    private function get_default_ttl($type)
    {
        $ttls = [
            'pdf' => $this->cache_config['file_ttl'],
            'html' => $this->cache_config['memory_ttl'],
            'asset' => $this->cache_config['db_ttl'],
            'general' => $this->cache_config['memory_ttl']
        ];

        return $ttls[$type] ?? $this->cache_config['memory_ttl'];
    }

    private function delete_directory_contents($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->delete_directory_contents($file);
                rmdir($file);
            }
        }
    }

    /**
     * Nettoyer automatiquement les caches expirés
     */
    private function schedule_cleanup()
    {
        if (!wp_next_scheduled('pdf_builder_cache_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_cache_cleanup');
        }

        add_action('pdf_builder_cache_cleanup', [$this, 'cleanup_expired_cache']);
    }

    public function cleanup_expired_cache()
    {
        // Nettoyer DB cache
        global $wpdb;
        $table_name = $wpdb->prefix . $this->db_cache_table;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE expires_at < NOW()"
            )
        );

        // Nettoyer fichier cache
        $this->cleanup_expired_files();

        // Nettoyer mémoire cache
        $this->cleanup_memory_cache();
    }

    private function cleanup_expired_files()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->file_cache_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $data = file_get_contents($file->getPathname());
                if ($data !== false) {
                    if ($this->cache_config['compression_enabled']) {
                        $data = gzuncompress($data);
                    }

                    $unserialized = unserialize($data);
                    if ($unserialized && isset($unserialized['expires']) && time() > $unserialized['expires']) {
                        unlink($file->getPathname());
                    }
                }
            }
        }
    }

    /**
     * Obtenir les statistiques du cache
     */
    public function get_cache_stats()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . $this->db_cache_table;

        $stats = [
            'memory_items' => count($this->memory_cache),
            'file_cache_size' => $this->get_directory_size($this->file_cache_dir),
            'db_cache_count' => $wpdb->get_var("SELECT COUNT(*) FROM $table_name"),
            'config' => $this->cache_config
        ];

        return $stats;
    }

    private function get_directory_size($dir)
    {
        $size = 0;
        if (!file_exists($dir)) {
            return $size;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            $size += $file->getSize();
        }

        return $size;
    }

    /**
     * Optimiser les performances du cache
     */
    public function optimize_performance()
    {
        // Augmenter la taille du cache mémoire si utilisation élevée
        $memory_usage = count($this->memory_cache) / $this->cache_config['max_memory_items'];
        if ($memory_usage > 0.8) {
            $this->cache_config['max_memory_items'] = intval($this->cache_config['max_memory_items'] * 1.5);
        }

        // Activer/désactiver la compression selon les besoins
        $file_cache_size = $this->get_directory_size($this->file_cache_dir);
        if ($file_cache_size > 100 * 1024 * 1024) { // Plus de 100MB
            $this->cache_config['compression_enabled'] = true;
        }

        // Nettoyer automatiquement
        $this->cleanup_expired_cache();
    }
}
