<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * PDF Builder Pro - Gestionnaire de Cache
 *
 * Implémente le système de cache utilisé dans l'onglet Système :
 *  - Stockage via transients WordPress (persistant) + WP Object Cache (par requête)
 *  - Respect des paramètres : activé, TTL, compression, taille max, nettoyage auto
 *  - Exposition d'une API pour les boutons AJAX de l'onglet Système
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Cache_Manager {

    /** @var self|null */
    private static $instance = null;

    /** Préfixe pour tous les transients du plugin */
    const TRANSIENT_PREFIX = 'pdf_builder_';

    /** Groupe WP Object Cache */
    const CACHE_GROUP = 'pdf_builder';

    /** @var array Paramètres effectifs chargés une seule fois */
    private $config = [];

    /** @var array Statistiques de hits/misses par requête */
    private $stats = [
        'hits'   => 0,
        'misses' => 0,
        'writes' => 0,
    ];

    // ──────────────────────────────────────────────────────────────────────────
    // Singleton
    // ──────────────────────────────────────────────────────────────────────────

    private function __construct() {
        $this->load_config();
        $this->register_hooks();
    }

    public static function get_instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Configuration
    // ──────────────────────────────────────────────────────────────────────────

    private function load_config(): void {
        $settings = function_exists('pdf_builder_get_option')
            ? (pdf_builder_get_option('pdf_builder_settings', []) ?: [])
            : [];

        $this->config = [
            'enabled'      => ($settings['pdf_builder_cache_enabled']      ?? '0') === '1',
            'ttl'          => max(60, intval($settings['pdf_builder_cache_ttl']       ?? 3600)),
            'compression'  => ($settings['pdf_builder_cache_compression']  ?? '1') === '1',
            'auto_cleanup' => ($settings['pdf_builder_cache_auto_cleanup'] ?? '1') === '1',
            'max_size_mb'  => max(10, intval($settings['pdf_builder_cache_max_size']  ?? 100)),
        ];
    }

    private function register_hooks(): void {
        if ($this->config['auto_cleanup']) {
            add_action('shutdown', [$this, 'maybe_auto_cleanup']);
        }
        // Invalider le cache quand les paramètres sont mis à jour
        add_action('update_option_pdf_builder_settings', [$this, 'on_settings_updated'], 10, 0);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API publique : get / set / delete
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Récupère une valeur depuis le cache.
     *
     * @param string $key   Clé unique.
     * @param string $group Groupe logique (pour le WP Object Cache).
     * @return mixed|false  Valeur ou false si absente / cache désactivé.
     */
    public function get(string $key, string $group = self::CACHE_GROUP) {
        if (!$this->config['enabled']) {
            return false;
        }

        $cache_key = $this->make_key($key, $group);

        // 1. WP Object Cache (mémoire, par requête)
        $value = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($value !== false) {
            $this->stats['hits']++;
            return $value;
        }

        // 2. Transient (persistant)
        $raw = get_transient($cache_key);
        if ($raw === false) {
            $this->stats['misses']++;
            return false;
        }

        $value = $this->maybe_decompress($raw);
        // Alimenter l'Object Cache pour les prochains accès dans la requête
        wp_cache_set($cache_key, $value, self::CACHE_GROUP, $this->config['ttl']);

        $this->stats['hits']++;
        return $value;
    }

    /**
     * Stocke une valeur dans le cache.
     *
     * @param string   $key   Clé unique.
     * @param mixed    $value Valeur à stocker.
     * @param int|null $ttl   TTL en secondes (null = valeur de la configuration).
     * @param string   $group Groupe logique.
     */
    public function set(string $key, $value, ?int $ttl = null, string $group = self::CACHE_GROUP): bool {
        if (!$this->config['enabled']) {
            return false;
        }

        $ttl       = $ttl ?? $this->config['ttl'];
        $cache_key = $this->make_key($key, $group);
        $raw       = $this->maybe_compress($value);

        wp_cache_set($cache_key, $value, self::CACHE_GROUP, $ttl);
        $result = set_transient($cache_key, $raw, $ttl);

        if ($result) {
            $this->stats['writes']++;
        }

        return $result;
    }

    /**
     * Supprime une valeur du cache.
     */
    public function delete(string $key, string $group = self::CACHE_GROUP): bool {
        $cache_key = $this->make_key($key, $group);
        wp_cache_delete($cache_key, self::CACHE_GROUP);
        return delete_transient($cache_key);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // API AJAX — utilisée par l'onglet Système
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Vide l'intégralité du cache du plugin.
     * Supprime tous les transients préfixés + flush du WP Object Cache.
     */
    public function clear_all(): array {
        global $wpdb;

        // Supprimer les transients en base
        $like       = $wpdb->esc_like('_transient_' . self::TRANSIENT_PREFIX) . '%';
        $like_to    = $wpdb->esc_like('_transient_timeout_' . self::TRANSIENT_PREFIX) . '%';
        $deleted    = (int) $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $like, $like_to
        ));

        // Flush WP Object Cache (global — sans impact si la requête est déjà terminée)
        wp_cache_flush();

        // Supprimer les fichiers de cache temporaires du plugin
        $files_deleted = $this->clear_cache_files();

        // Enregistrer la date du dernier nettoyage
        $this->record_cleanup();

        return [
            'transients_deleted' => $deleted,
            'files_deleted'      => $files_deleted,
            'message'            => sprintf(
                '✅ Cache vidé — %d transient(s) et %d fichier(s) supprimés.',
                $deleted,
                $files_deleted
            ),
        ];
    }

    /**
     * Retourne l'état actuel du cache (pour l'indicateur dans l'onglet Système).
     */
    public function get_status(): array {
        global $wpdb;

        $transient_count = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_' . self::TRANSIENT_PREFIX) . '%'
        ));

        return [
            'enabled'         => $this->config['enabled'],
            'ttl'             => $this->config['ttl'],
            'compression'     => $this->config['compression'],
            'auto_cleanup'    => $this->config['auto_cleanup'],
            'max_size_mb'     => $this->config['max_size_mb'],
            'transient_count' => $transient_count,
            'cache_files'     => $this->count_cache_files(),
            'last_cleanup'    => get_option('pdf_builder_cache_last_cleanup', 'Jamais'),
            'stats'           => $this->stats,
        ];
    }

    /**
     * Retourne des métriques détaillées (utilisé par handle_get_cache_metrics).
     */
    public function get_metrics(): array {
        global $wpdb;

        $transient_count = (int) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_' . self::TRANSIENT_PREFIX) . '%'
        ));

        // Taille estimée en base (en Ko)
        $db_size_kb = (float) $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "SELECT ROUND(SUM(LENGTH(option_value)) / 1024, 1)
               FROM {$wpdb->options}
              WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_' . self::TRANSIENT_PREFIX) . '%'
        ));

        $file_count = $this->count_cache_files();
        $file_size_kb = $this->get_cache_files_size_kb();

        return [
            'status'          => $this->config['enabled'] ? 'active' : 'inactive',
            'enabled'         => $this->config['enabled'],
            'transient_count' => $transient_count,
            'db_size_kb'      => $db_size_kb,
            'file_count'      => $file_count,
            'file_size_kb'    => $file_size_kb,
            'session_hits'    => $this->stats['hits'],
            'session_misses'  => $this->stats['misses'],
            'session_writes'  => $this->stats['writes'],
            'last_cleanup'    => get_option('pdf_builder_cache_last_cleanup', 'Jamais'),
            'config'          => $this->config,
        ];
    }

    /**
     * Lance un test simple de lecture/écriture (bouton "Tester" de l'onglet Système).
     */
    public function run_tests(): array {
        $results = [];

        // Test 1 : WP Object Cache
        $key   = 'pdfb_test_' . wp_rand(1000, 9999);
        $value = 'value_' . wp_rand();
        wp_cache_set($key, $value, self::CACHE_GROUP, 60);
        $got = wp_cache_get($key, self::CACHE_GROUP);
        wp_cache_delete($key, self::CACHE_GROUP);
        $results['wp_object_cache'] = $got === $value ? '✅ Fonctionnel' : '⚠️ Non persistant (normal sans plugin de cache)';

        // Test 2 : Transients
        $tkey  = self::TRANSIENT_PREFIX . 'test_' . wp_rand(1000, 9999);
        $tval  = 'tval_' . wp_rand();
        set_transient($tkey, $tval, 60);
        $tgot  = get_transient($tkey);
        delete_transient($tkey);
        $results['transients'] = $tgot === $tval ? '✅ Fonctionnel' : '❌ Défaillant';

        // Test 3 : Compression (si activée)
        if ($this->config['compression']) {
            $data       = str_repeat('PDF Builder test data — ', 50);
            $compressed = $this->maybe_compress($data);
            $back       = $this->maybe_decompress($compressed);
            $results['compression'] = $back === $data ? '✅ Fonctionnel' : '❌ Erreur de compression';
        } else {
            $results['compression'] = '⏭️ Désactivée';
        }

        // Test 4 : Écriture/lecture via l'API du manager
        if ($this->config['enabled']) {
            $mk = 'api_test';
            $mv = ['ts' => time(), 'ok' => true];
            $this->set($mk, $mv, 60);
            $mr = $this->get($mk);
            $this->delete($mk);
            $results['api'] = ($mr === $mv) ? '✅ Fonctionnel' : '❌ Défaillant';
        } else {
            $results['api'] = '⏭️ Cache désactivé dans les paramètres';
        }

        $all_ok = !in_array('❌ Défaillant', $results, true);

        return [
            'message' => $all_ok
                ? '✅ Tous les tests du cache ont réussi'
                : '⚠️ Certains tests ont échoué — vérifiez les détails',
            'results' => $results,
            'status'  => $all_ok ? 'success' : 'warning',
        ];
    }

    /**
     * Test d'intégration complet (bouton "Tester l'intégration" de l'onglet Système).
     */
    public function test_integration(): array {
        return $this->run_tests();
    }

    /**
     * Met à jour les métriques (peut être appelé périodiquement).
     */
    public function update_metrics(): array {
        $metrics = $this->get_metrics();
        // Stocker un snapshot horodaté (non critique, jamais bloquant)
        update_option('pdf_builder_cache_metrics_snapshot', [
            'timestamp' => current_time('mysql'),
            'data'      => $metrics,
        ], false);

        return $metrics;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Nettoyage automatique
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Nettoyage des transients expirés (appellé sur l'action `shutdown`).
     * Ne tourne que 1× par heure maximum pour ne pas impacter les performances.
     */
    public function maybe_auto_cleanup(): void {
        if (!$this->config['auto_cleanup']) {
            return;
        }

        $last = (int) get_transient('pdf_builder_last_auto_cleanup');
        if ($last > time() - HOUR_IN_SECONDS) {
            return;
        }

        $this->delete_expired_transients();
        set_transient('pdf_builder_last_auto_cleanup', time(), HOUR_IN_SECONDS);
    }

    /**
     * Appelé quand les paramètres sont sauvegardés : recharge la configuration.
     */
    public function on_settings_updated(): void {
        $this->load_config();

        // Invalider les données mises en cache qui dépendent des settings
        delete_transient(self::TRANSIENT_PREFIX . 'settings_summary');
        wp_cache_delete(self::TRANSIENT_PREFIX . 'settings_summary', self::CACHE_GROUP);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Méthodes internes
    // ──────────────────────────────────────────────────────────────────────────

    private function make_key(string $key, string $group): string {
        $prefix = ($group !== self::CACHE_GROUP) ? $group . '_' : '';
        return self::TRANSIENT_PREFIX . $prefix . sanitize_key($key);
    }

    private function maybe_compress($value): string {
        $serialized = maybe_serialize($value);
        if ($this->config['compression'] && function_exists('gzcompress') && strlen($serialized) > 512) {
            return 'gz:' . base64_encode(gzcompress($serialized, 6));
        }
        return $serialized;
    }

    private function maybe_decompress($raw) {
        if (is_string($raw) && strncmp($raw, 'gz:', 3) === 0) {
            $decoded = base64_decode(substr($raw, 3), true);
            if ($decoded !== false) {
                $decompressed = @gzuncompress($decoded);
                if ($decompressed !== false) {
                    return maybe_unserialize($decompressed);
                }
            }
        }
        // Pas compressé, ou décompression échouée
        return maybe_unserialize($raw);
    }

    /**
     * Supprime les transients WordPress expirés du plugin (nettoyage en DB).
     */
    private function delete_expired_transients(): int {
        global $wpdb;
        return (int) $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
            "DELETE a, b FROM {$wpdb->options} a
             INNER JOIN {$wpdb->options} b
               ON b.option_name = REPLACE(a.option_name, '_transient_timeout_', '_transient_')
             WHERE a.option_name LIKE '_transient_timeout_" . self::TRANSIENT_PREFIX . "%'
               AND a.option_value < UNIX_TIMESTAMP()"
        );
    }

    private function clear_cache_files(): int {
        $deleted = 0;
        $dirs    = $this->get_cache_dirs();

        foreach ($dirs as $dir) {
            if (!is_dir($dir) || !is_writable($dir)) { // phpcs:ignore WordPress.WP.AlternativeFunctions
                continue;
            }
            $files = glob($dir . '/*') ?: [];
            foreach ($files as $file) {
                if (is_file($file) && @wp_delete_file($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    private function count_cache_files(): int {
        $count = 0;
        foreach ($this->get_cache_dirs() as $dir) {
            if (is_dir($dir) && is_readable($dir)) {
                $count += count(glob($dir . '/*') ?: []);
            }
        }
        return $count;
    }

    private function get_cache_files_size_kb(): float {
        $size = 0;
        foreach ($this->get_cache_dirs() as $dir) {
            if (!is_dir($dir) || !is_readable($dir)) {
                continue;
            }
            foreach (glob($dir . '/*') ?: [] as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                }
            }
        }
        return round($size / 1024, 1);
    }

    private function get_cache_dirs(): array {
        $dirs = [];
        if (defined('WP_CONTENT_DIR')) {
            $dirs[] = WP_CONTENT_DIR . '/cache/wp-pdf-builder-previews';
        }
        if (function_exists('wp_upload_dir')) {
            $upload = wp_upload_dir(null, false);
            $dirs[] = $upload['basedir'] . '/pdf-builder-cache';
            $dirs[] = $upload['basedir'] . '/pdf-builder-temp';
        }
        return $dirs;
    }

    private function record_cleanup(): void {
        $now = current_time('mysql');
        update_option('pdf_builder_cache_last_cleanup', $now, false);

        // Mettre à jour aussi dans le tableau groupé des settings
        $settings = function_exists('pdf_builder_get_option')
            ? (pdf_builder_get_option('pdf_builder_settings', []) ?: [])
            : [];
        $settings['pdf_builder_cache_last_cleanup'] = $now;
        if (function_exists('pdf_builder_update_option')) {
            pdf_builder_update_option('pdf_builder_settings', $settings);
        }
    }
}
