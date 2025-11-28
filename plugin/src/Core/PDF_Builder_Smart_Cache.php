<?php
/**
 * Système de Cache Intelligent pour PDF Builder Pro
 *
 * Cache avancé avec TTL, compression, et gestion automatique
 * des performances et de l'espace disque.
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du système de cache intelligent
 */
class PDF_Builder_Smart_Cache {

    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Configuration du cache
     */
    private $config = array();

    /**
     * Métriques du cache
     */
    private $metrics = array();

    /**
     * Répertoire de cache
     */
    private $cache_dir = '';

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_config();
        $this->init_cache_dir();
        $this->load_metrics();
        $this->register_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser la configuration
     */
    private function init_config() {
        $this->config = array(
            'enabled' => get_option('pdf_builder_cache_enabled', '1') === '1',
            'ttl' => intval(get_option('pdf_builder_cache_ttl', 3600)), // 1 heure par défaut
            'compression' => get_option('pdf_builder_cache_compression', '0') === '1',
            'auto_cleanup' => get_option('pdf_builder_cache_auto_cleanup', '1') === '1',
            'max_size' => intval(get_option('pdf_builder_cache_max_size', 100)), // MB
            'compression_level' => 6, // Niveau de compression gzip
        );
    }

    /**
     * Initialiser le répertoire de cache
     */
    private function init_cache_dir() {
        $upload_dir = wp_upload_dir();
        $this->cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';

        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }

        // Créer les sous-répertoires
        $subdirs = array('templates', 'previews', 'settings', 'temp');
        foreach ($subdirs as $subdir) {
            $dir = $this->cache_dir . $subdir . '/';
            if (!file_exists($dir)) {
                wp_mkdir_p($dir);
            }
        }
    }

    /**
     * Charger les métriques
     */
    private function load_metrics() {
        $this->metrics = get_option('pdf_builder_cache_metrics', array(
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
            'size' => 0,
            'last_cleanup' => 0,
            'compression_savings' => 0,
        ));
    }

    /**
     * Enregistrer les hooks
     */
    private function register_hooks() {
        if ($this->config['auto_cleanup']) {
            add_action('pdf_builder_hourly_cleanup', array($this, 'cleanup_expired'));
        }

        add_action('wp_ajax_pdf_builder_clear_cache', array($this, 'ajax_clear_cache'));
    }

    /**
     * Obtenir une valeur du cache
     */
    public function get($key, $group = 'default') {
        if (!$this->config['enabled']) {
            return false;
        }

        $file = $this->get_cache_file($key, $group);

        if (!file_exists($file)) {
            $this->metrics['misses']++;
            $this->update_metrics();
            return false;
        }

        // Vérifier l'expiration
        if ($this->is_expired($file)) {
            unlink($file);
            $this->metrics['misses']++;
            $this->update_metrics();
            return false;
        }

        $data = $this->read_cache_file($file);
        if ($data === false) {
            $this->metrics['misses']++;
            $this->update_metrics();
            return false;
        }

        $this->metrics['hits']++;
        $this->update_metrics();

        return $data;
    }

    /**
     * Définir une valeur dans le cache
     */
    public function set($key, $data, $group = 'default', $ttl = null) {
        if (!$this->config['enabled']) {
            return false;
        }

        if ($ttl === null) {
            $ttl = $this->config['ttl'];
        }

        $file = $this->get_cache_file($key, $group);
        $result = $this->write_cache_file($file, $data, $ttl);

        if ($result) {
            $this->metrics['sets']++;
            $this->update_metrics();
        }

        return $result;
    }

    /**
     * Supprimer une valeur du cache
     */
    public function delete($key, $group = 'default') {
        $file = $this->get_cache_file($key, $group);

        if (file_exists($file) && unlink($file)) {
            $this->metrics['deletes']++;
            $this->update_metrics();
            return true;
        }

        return false;
    }

    /**
     * Vider tout le cache
     */
    public function clear_all() {
        $result = $this->clear_directory($this->cache_dir);
        $this->metrics['size'] = 0;
        $this->update_metrics();

        return array(
            'success' => $result,
            'message' => $result ? 'Cache vidé avec succès' : 'Erreur lors du vidage du cache'
        );
    }

    /**
     * Nettoyer les fichiers expirés
     */
    public function cleanup_expired() {
        $cleaned = 0;
        $files = glob($this->cache_dir . '**/*.cache');

        foreach ($files as $file) {
            if ($this->is_expired($file)) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }

        $this->metrics['last_cleanup'] = time();
        $this->update_metrics();

        return $cleaned;
    }

    /**
     * Obtenir l'état du cache
     */
    public function get_status() {
        return array(
            'enabled' => $this->config['enabled'],
            'size' => $this->get_cache_size(),
            'files_count' => $this->count_cache_files(),
            'compression_enabled' => $this->config['compression'],
            'auto_cleanup_enabled' => $this->config['auto_cleanup'],
        );
    }

    /**
     * Obtenir les métriques
     */
    public function get_metrics() {
        $this->metrics['current_size'] = $this->get_cache_size();
        $this->metrics['hit_ratio'] = $this->calculate_hit_ratio();

        return $this->metrics;
    }

    /**
     * Mettre à jour les métriques
     */
    public function update_metrics() {
        update_option('pdf_builder_cache_metrics', $this->metrics);
    }

    /**
     * Tester le système de cache
     */
    public function run_tests() {
        $results = array(
            'cache_available' => false,
            'transient_test' => false,
            'file_cache_test' => false,
            'compression_test' => false,
        );

        // Test 1: Disponibilité des fonctions de cache
        $results['cache_available'] = function_exists('wp_cache_flush');

        // Test 2: Test des transients
        $test_key = 'pdf_builder_cache_test_' . time();
        $test_value = 'test_value_' . mt_rand(1000, 9999);

        $set_result = set_transient($test_key, $test_value, 300);
        if ($set_result) {
            $get_result = get_transient($test_key);
            $results['transient_test'] = ($get_result === $test_value);
            delete_transient($test_key);
        }

        // Test 3: Test du cache fichier
        $test_data = array('test' => 'data', 'timestamp' => time());
        $results['file_cache_test'] = $this->set('test_key', $test_data, 'test', 300);

        if ($results['file_cache_test']) {
            $retrieved = $this->get('test_key', 'test');
            $results['file_cache_test'] = ($retrieved === $test_data);
            $this->delete('test_key', 'test');
        }

        // Test 4: Test de compression
        if ($this->config['compression']) {
            $large_data = str_repeat('test data for compression ', 1000);
            $results['compression_test'] = $this->set('compression_test', $large_data, 'test', 300);

            if ($results['compression_test']) {
                $this->delete('compression_test', 'test');
            }
        } else {
            $results['compression_test'] = true; // Non testé si compression désactivée
        }

        return array(
            'success' => !in_array(false, $results, true),
            'results' => $results,
            'message' => 'Tests du cache terminés'
        );
    }

    /**
     * Tester l'intégration du cache
     */
    public function test_integration() {
        return array(
            'cache_dir_writable' => is_writable($this->cache_dir),
            'cache_dir_exists' => file_exists($this->cache_dir),
            'subdirs_exist' => $this->check_subdirs(),
            'config_loaded' => !empty($this->config),
            'metrics_loaded' => !empty($this->metrics),
        );
    }

    /**
     * Méthodes privées utilitaires
     */
    private function get_cache_file($key, $group) {
        $hash = md5($key . $group);
        $subdir = substr($hash, 0, 2);
        $dir = $this->cache_dir . $group . '/' . $subdir . '/';

        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        return $dir . $hash . '.cache';
    }

    private function is_expired($file) {
        if (!file_exists($file)) {
            return true;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            return true;
        }

        $data = @unserialize($content);
        if ($data === false || !isset($data['expires'])) {
            return true;
        }

        return time() > $data['expires'];
    }

    private function read_cache_file($file) {
        $content = file_get_contents($file);
        if ($content === false) {
            return false;
        }

        $data = @unserialize($content);
        if ($data === false || !isset($data['data'])) {
            return false;
        }

        // Décompresser si nécessaire
        if (isset($data['compressed']) && $data['compressed']) {
            $data['data'] = gzuncompress($data['data']);
        }

        return $data['data'];
    }

    private function write_cache_file($file, $data, $ttl) {
        $cache_data = array(
            'data' => $data,
            'expires' => time() + $ttl,
            'compressed' => false,
        );

        // Compresser si activé et si les données sont assez grandes
        if ($this->config['compression'] && strlen(serialize($data)) > 1024) {
            $compressed = gzcompress(serialize($data), $this->config['compression_level']);
            if ($compressed !== false) {
                $cache_data['data'] = $compressed;
                $cache_data['compressed'] = true;
                $this->metrics['compression_savings'] += (strlen(serialize($data)) - strlen($compressed));
            }
        }

        $content = serialize($cache_data);
        return file_put_contents($file, $content, LOCK_EX) !== false;
    }

    private function clear_directory($dir) {
        if (!is_dir($dir)) {
            return true;
        }

        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clear_directory($file);
                rmdir($file);
            } else {
                unlink($file);
            }
        }

        return true;
    }

    private function get_cache_size() {
        $size = 0;
        $files = glob($this->cache_dir . '**/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            }
        }

        return $size;
    }

    private function count_cache_files() {
        $files = glob($this->cache_dir . '**/*.cache');
        return count($files);
    }

    private function calculate_hit_ratio() {
        $total = $this->metrics['hits'] + $this->metrics['misses'];
        return $total > 0 ? ($this->metrics['hits'] / $total) * 100 : 0;
    }

    private function check_subdirs() {
        $subdirs = array('templates', 'previews', 'settings', 'temp');
        foreach ($subdirs as $subdir) {
            if (!file_exists($this->cache_dir . $subdir)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Handler AJAX pour vider le cache
     */
    public function ajax_clear_cache() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $result = $this->clear_all();
        wp_send_json($result);
    }
}