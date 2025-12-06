<?php
/**
 * PDF Builder Pro - Système de cache intelligent
 * Optimise les performances en cachant les données fréquemment utilisées
 */

class PDF_Builder_Cache_Manager {
    private static $instance = null;
    private $cache_group = 'pdf_builder';
    private $default_ttl = 3600; // 1 heure

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Nettoyer le cache lors des mises à jour
        add_action('pdf_builder_settings_updated', [$this, 'flush_cache']);
        add_action('pdf_builder_template_saved', [$this, 'flush_template_cache']);
        add_action('pdf_builder_template_deleted', [$this, 'flush_template_cache']);

        // Nettoyer automatiquement le cache expiré
        if ($this->is_cache_cleanup_enabled()) {
            add_action('pdf_builder_hourly_cleanup', [$this, 'cleanup_expired_cache']);
        }
    }

    /**
     * Récupère une valeur du cache
     */
    public function get($key, $default = null) {
        if (!$this->is_cache_enabled()) {
            return $default;
        }

        $cached_value = wp_cache_get($key, $this->cache_group);
        if ($cached_value === false) {
            return $default;
        }

        return $cached_value;
    }

    /**
     * Stocke une valeur dans le cache
     */
    public function set($key, $value, $ttl = null) {
        if (!$this->is_cache_enabled()) {
            return false;
        }

        $ttl = $ttl ?: $this->get_cache_ttl();
        return wp_cache_set($key, $value, $this->cache_group, $ttl);
    }

    /**
     * Supprime une clé du cache
     */
    public function delete($key) {
        return wp_cache_delete($key, $this->cache_group);
    }

    /**
     * Vide tout le cache du plugin
     */
    public function flush_cache() {
        wp_cache_flush();
        $this->clear_transients();
        $this->clear_options_cache();

        do_action('pdf_builder_cache_flushed');
    }

    /**
     * Vide le cache des templates
     */
    public function flush_template_cache() {
        global $wpdb;

        // Supprimer les transients liés aux templates
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_template_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_template_%'");

        wp_cache_delete('pdf_builder_templates_list', $this->cache_group);
        wp_cache_delete('pdf_builder_template_count', $this->cache_group);
    }

    /**
     * Nettoie le cache expiré
     */
    public function cleanup_expired_cache() {
        global $wpdb;

        // Supprimer les transients expirés
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%' AND option_value < UNIX_TIMESTAMP()");

        // Optimiser les tables de cache si nécessaire
        if ($this->should_optimize_tables()) {
            $this->optimize_cache_tables();
        }
    }

    /**
     * Cache une liste de templates
     */
    public function get_templates_list($force_refresh = false) {
        $cache_key = 'pdf_builder_templates_list';

        if (!$force_refresh) {
            $cached = $this->get($cache_key);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Récupérer depuis la base de données
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_builder_templates';

        $templates = $wpdb->get_results(
            "SELECT id, name, user_id, is_default, created_at, updated_at FROM {$table} ORDER BY updated_at DESC",
            ARRAY_A
        );

        if ($templates) {
            $this->set($cache_key, $templates, $this->default_ttl);
        }

        return $templates ?: [];
    }

    /**
     * Cache un template individuel
     */
    public function get_template($template_id, $force_refresh = false) {
        $cache_key = 'pdf_builder_template_' . $template_id;

        if (!$force_refresh) {
            $cached = $this->get($cache_key);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Récupérer depuis la base de données
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $template_id),
            ARRAY_A
        );

        if ($template) {
            // Décoder les données JSON
            $template['template_data'] = json_decode($template['template_data'], true);
            $this->set($cache_key, $template, $this->default_ttl);
        }

        return $template;
    }

    /**
     * Cache les paramètres du plugin
     */
    public function get_settings($force_refresh = false) {
        $cache_key = 'pdf_builder_settings';

        if (!$force_refresh) {
            $cached = $this->get($cache_key);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Récupérer tous les paramètres du plugin
        global $wpdb;
        $settings = [];

        $options = $wpdb->get_results(
            $wpdb->prepare("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", 'pdf_builder_%'),
            ARRAY_A
        );

        foreach ($options as $option) {
            $key = str_replace('pdf_builder_', '', $option['option_name']);
            $settings[$key] = maybe_unserialize($option['option_value']);
        }

        $this->set($cache_key, $settings, $this->default_ttl);

        return $settings;
    }

    /**
     * Met à jour le cache des paramètres
     */
    public function update_settings_cache($new_settings) {
        $this->set('pdf_builder_settings', $new_settings, $this->default_ttl);
    }

    /**
     * Vérifie si le cache est activé
     */
    private function is_cache_enabled() {
        return get_option('pdf_builder_cache_enabled', '1') === '1';
    }

    /**
     * Récupère la durée de vie du cache
     */
    private function get_cache_ttl() {
        return intval(get_option('pdf_builder_cache_ttl', $this->default_ttl));
    }

    /**
     * Vérifie si le nettoyage automatique est activé
     */
    private function is_cache_cleanup_enabled() {
        return get_option('pdf_builder_cache_auto_cleanup', '1') === '1';
    }

    /**
     * Supprime les transients du plugin
     */
    private function clear_transients() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");
    }

    /**
     * Vide le cache des options WordPress
     */
    private function clear_options_cache() {
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    /**
     * Détermine si les tables doivent être optimisées
     */
    private function should_optimize_tables() {
        // Optimiser une fois par semaine
        $last_optimization = get_option('pdf_builder_last_cache_optimization', 0);
        return (current_time('timestamp') - $last_optimization) > WEEK_IN_SECONDS;
    }

    /**
     * Optimise les tables de cache
     */
    private function optimize_cache_tables() {
        global $wpdb;

        // Optimiser la table options pour les transients
        $wpdb->query("OPTIMIZE TABLE {$wpdb->options}");

        update_option('pdf_builder_last_cache_optimization', current_time('timestamp'));
    }

    /**
     * Méthodes utilitaires pour le cache
     */
    public function get_cache_stats() {
        global $wpdb;

        $stats = [
            'transients_count' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'"),
            'cache_enabled' => $this->is_cache_enabled(),
            'cache_ttl' => $this->get_cache_ttl(),
            'last_cleanup' => get_option('pdf_builder_last_cache_cleanup', 'Jamais'),
        ];

        return $stats;
    }

    /**
     * Force le rafraîchissement d'une clé spécifique
     */
    public function refresh_key($key) {
        $this->delete($key);
        return true;
    }
}

// Initialiser le gestionnaire de cache
add_action('plugins_loaded', function() {
    PDF_Builder_Cache_Manager::get_instance();
});