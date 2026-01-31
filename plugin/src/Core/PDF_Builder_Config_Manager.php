<?php
/**
 * PDF Builder Pro - Gestionnaire de configuration
 * Centralise la gestion de tous les paramètres et options du plugin
 */

class PDF_Builder_Global_Config_Manager {
    private static $instance = null;
    private $config = [];
    private $defaults = [];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Différer l'initialisation jusqu'à ce que WordPress soit prêt
        add_action('init', function() {
            $this->init_defaults();
            $this->load_config();
            $this->init_hooks();
        }, 1);
    }

    private function init_hooks() {
        // AJAX pour la gestion de la configuration
        add_action('wp_ajax_pdf_builder_save_config', [$this, 'save_config_ajax']);
        add_action('wp_ajax_pdf_builder_reset_config', [$this, 'reset_config_ajax']);
        add_action('wp_ajax_pdf_builder_export_config', [$this, 'export_config_ajax']);
        add_action('wp_ajax_pdf_builder_import_config', [$this, 'import_config_ajax']);

        // Validation des paramètres
        add_filter('pdf_builder_validate_config', [$this, 'validate_config']);
    }

    /**
     * Initialise les valeurs par défaut de la configuration
     */
    private function init_defaults() {
        $this->defaults = [
            // Paramètres généraux
            'company_name' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '', // Sera défini plus tard si WordPress est disponible
            'company_website' => '', // Sera défini plus tard si WordPress est disponible

            // Paramètres de performance
            'cache_enabled' => true,
            'cache_ttl' => 3600, // 1 heure
            'cache_cleanup_interval' => 86400, // 24 heures
            'memory_limit' => 256, // MB
            'max_execution_time' => 30, // secondes

            // Paramètres de sécurité
            'enable_nonce_check' => true,
            'nonce_lifetime' => 86400, // 24 heures
            'rate_limiting_enabled' => true,
            'max_requests_per_minute' => 60,
            'enable_ip_filtering' => false,
            'allowed_ips' => [],
            'enable_cors' => false,
            'cors_origins' => [],

            // Paramètres de logging
            'logging_enabled' => true,
            'log_level' => 'WARNING', // DEBUG, INFO, WARNING, ERROR, CRITICAL
            'log_retention_days' => 30,
            'log_max_file_size' => 10, // MB
            'log_rotation_enabled' => true,

            // Paramètres d'interface
            'default_template_width' => 210, // mm (A4)
            'default_template_height' => 297, // mm (A4)
            'default_dpi' => 300,
            'max_upload_size' => 10, // MB
            'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
            'enable_drag_drop' => true,
            'enable_keyboard_shortcuts' => true,

            // Paramètres avancés
            'debug_mode' => false, // Sera défini plus tard si WP_DEBUG est disponible
            'performance_monitoring' => true,
            'error_reporting' => true,
            'maintenance_mode' => false,
            'maintenance_message' => 'Le générateur PDF est temporairement indisponible pour maintenance.',

            // Paramètres de base de données
            'db_optimization_enabled' => true,
            'db_query_cache_enabled' => true,
            'db_connection_pooling' => false,

            // Paramètres d'internationalisation
            'default_language' => 'fr_FR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i:s',
            'timezone' => 'Europe/Paris', // Valeur par défaut sûre

            // Notification options removed

            // Paramètres de licence
            'license_key' => '',
            'license_status' => 'inactive',
            'license_expiry' => null,
            'auto_update_enabled' => true
        ];

        // Définir les valeurs qui dépendent de WordPress si disponibles
        if (function_exists('get_option')) {
            $this->defaults['company_email'] = get_option('admin_email', '');
            // notification_email setting removed
        }

        if (function_exists('get_site_url')) {
            $this->defaults['company_website'] = get_site_url();
        }

        if (function_exists('wp_timezone_string')) {
            $this->defaults['timezone'] = wp_timezone_string();
        }

        if (defined('WP_DEBUG')) {
            $this->defaults['debug_mode'] = WP_DEBUG;
        }
    }

    /**
     * Charge la configuration depuis la base de données
     */
    private function load_config() {
        $saved_config = pdf_builder_get_option('pdf_builder_config', []);

        // Fusionner avec les valeurs par défaut
        $this->config = array_merge($this->defaults, $saved_config);

        // Valider la configuration chargée (seulement si WordPress est chargé)
        if (function_exists('apply_filters')) {
            $this->config = apply_filters('pdf_builder_validate_config', $this->config);
        }
    }

    /**
     * Sauvegarde la configuration
     */
    public function save_config($new_config) {
        // Valider la configuration
        $validated_config = $this->validate_config($new_config);

        // Fusionner avec la configuration existante
        $this->config = array_merge($this->config, $validated_config);

        // Sauvegarder en base
        pdf_builder_update_option('pdf_builder_config', $this->config);

        // Logger la modification
                'user_id' => get_current_user_id(),
                'changes' => array_keys($validated_config)
            ]);
        }

        // Déclencher un hook pour les autres composants (seulement si WordPress est chargé)
        if (function_exists('do_action')) {
            do_action('pdf_builder_config_updated', $this->config, $validated_config);
        }

        return true;
    }

    /**
     * Réinitialise la configuration aux valeurs par défaut
     */
    public function reset_config() {
        $this->config = $this->defaults;
        pdf_builder_update_option('pdf_builder_config', $this->config);

        // Logger la réinitialisation
                'user_id' => get_current_user_id()
            ]);
        }

        // Déclencher un hook (seulement si WordPress est chargé)
        if (function_exists('do_action')) {
            do_action('pdf_builder_config_reset', $this->config);
        }

        return true;
    }

    /**
     * Obtient une valeur de configuration
     */
    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * Définit une valeur de configuration
     */
    public function set($key, $value) {
        $this->config[$key] = $value;
        $this->save_config([$key => $value]);
    }

    /**
     * Obtient toute la configuration
     */
    public function get_all() {
        return $this->config;
    }

    /**
     * Valide la configuration
     */
    public function validate_config($config) {
        $validated = [];

        foreach ($config as $key => $value) {
            $validated[$key] = $this->validate_config_value($key, $value);
        }

        return $validated;
    }

    /**
     * Valide une valeur de configuration spécifique
     */
    private function validate_config_value($key, $value) {
        switch ($key) {
            // Booléens
            case 'cache_enabled':
            case 'enable_nonce_check':
            case 'rate_limiting_enabled':
            case 'enable_ip_filtering':
            case 'enable_cors':
            case 'logging_enabled':
            case 'log_rotation_enabled':
            case 'auto_backup_enabled':
            case 'backup_compression':
            case 'enable_drag_drop':
            case 'enable_keyboard_shortcuts':
            case 'debug_mode':
            case 'performance_monitoring':
            case 'error_reporting':
            case 'maintenance_mode':
            case 'db_optimization_enabled':
            case 'db_query_cache_enabled':
            case 'db_connection_pooling':
            // Notification options removed - ignore these cases
            case 'auto_update_enabled':
                return (bool) $value;

            // Entiers
            case 'cache_ttl':
            case 'cache_cleanup_interval':
            case 'memory_limit':
            case 'max_execution_time':
            case 'max_requests_per_minute':
            case 'log_retention_days':
            case 'log_max_file_size':
            case 'backup_interval':
            case 'backup_retention_count':
            case 'default_template_width':
            case 'default_template_height':
            case 'default_dpi':
            case 'max_upload_size':
                return max(0, intval($value));

            // Chaînes avec validation
            case 'company_email':
                return is_email($value) ? sanitize_email($value) : $this->defaults[$key];

            case 'company_website':
                return esc_url_raw($value);

            case 'log_level':
                $valid_levels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];
                return in_array(strtoupper($value), $valid_levels) ? strtoupper($value) : $this->defaults[$key];

            case 'default_language':
                return sanitize_key($value);

            case 'license_key':
                return sanitize_key($value);

            // Tableaux
            case 'allowed_ips':
            case 'cors_origins':
                if (!is_array($value)) {
                    return [];
                }
                return array_map('sanitize_text_field', $value);

            case 'allowed_file_types':
                if (!is_array($value)) {
                    return $this->defaults[$key];
                }
                return array_map('strtolower', array_map('sanitize_key', $value));

            // Chaînes simples
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Exporte la configuration
     */
    public function export_config() {
        $export_data = [
            'version' => PDF_BUILDER_VERSION,
            'export_date' => current_time('mysql'),
            'config' => $this->config
        ];

        return wp_json_encode($export_data, JSON_PRETTY_PRINT);
    }

    /**
     * Importe une configuration
     */
    public function import_config($json_data) {
        $import_data = json_decode($json_data, true);

        if (!$import_data || !isset($import_data['config'])) {
            throw new Exception('Données d\'import invalides');
        }

        // Valider et sauvegarder
        $this->save_config($import_data['config']);

        return true;
    }

    /**
     * AJAX - Sauvegarde la configuration
     */
    public function save_config_ajax() {
        try {
            // Valider la requête
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $config_data = json_decode(stripslashes($_POST['config'] ?? '{}'), true);

            if (!$config_data) {
                wp_send_json_error(['message' => 'Données de configuration invalides']);
                return;
            }

            $this->save_config($config_data);

            wp_send_json_success(['message' => 'Configuration sauvegardée avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Réinitialise la configuration
     */
    public function reset_config_ajax() {
        try {
            // Valider la requête
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $this->reset_config();

            wp_send_json_success(['message' => 'Configuration réinitialisée avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exporte la configuration
     */
    public function export_config_ajax() {
        try {
            // Valider la requête
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $config_json = $this->export_config();

            wp_send_json_success([
                'message' => 'Configuration exportée avec succès',
                'config' => $config_json
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'export: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Importe la configuration
     */
    public function import_config_ajax() {
        try {
            // Valider la requête
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Permissions insuffisantes']);
                return;
            }

            $config_json = stripslashes($_POST['config_json'] ?? '');

            if (empty($config_json)) {
                wp_send_json_error(['message' => 'Données d\'import manquantes']);
                return;
            }

            $this->import_config($config_json);

            wp_send_json_success(['message' => 'Configuration importée avec succès']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors de l\'import: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtient les informations système pour le diagnostic
     */
    public function get_system_info() {
        global $wpdb;

        return [
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $wpdb->db_version(),
            'plugin_version' => PDF_BUILDER_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'active_theme' => wp_get_theme()->get('Name'),
            'active_plugins' => count(get_option('active_plugins')),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'cache_enabled' => $this->get('cache_enabled'),
            'logging_enabled' => $this->get('logging_enabled')
        ];
    }

    /**
     * Vérifie la santé de la configuration
     */
    public function health_check() {
        $issues = [];

        // Vérifier les permissions des dossiers
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';

        if (!is_writable($pdf_dir)) {
            $issues[] = 'Le dossier PDF n\'est pas accessible en écriture';
        }

        // Vérifier la mémoire
        $memory_limit = $this->get('memory_limit');
        if ($memory_limit > 512) {
            $issues[] = 'Limite mémoire élevée détectée - risque de problèmes de performance';
        }

        // Vérifier les paramètres de sécurité
        if (!$this->get('enable_nonce_check')) {
            $issues[] = 'Vérification des nonce désactivée - risque de sécurité';
        }

        // Vérifier la configuration de cache
        if ($this->get('cache_enabled') && $this->get('cache_ttl') < 300) {
            $issues[] = 'TTL de cache très court - impact sur les performances';
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'issues' => $issues,
            'timestamp' => current_time('timestamp')
        ];
    }
}

function pdf_builder_config($key = null, $default = null) {
    $config = PDF_Builder_Global_Config_Manager::get_instance();
    return $key ? $config->get($key, $default) : $config->get_all();
}

function pdf_builder_set_config($key, $value) {
    PDF_Builder_Global_Config_Manager::get_instance()->set($key, $value);
}

function pdf_builder_save_config($config) {
    return PDF_Builder_Global_Config_Manager::get_instance()->save_config($config);
}

function pdf_builder_get_system_info() {
    return PDF_Builder_Global_Config_Manager::get_instance()->get_system_info();
}

function pdf_builder_health_check() {
    return PDF_Builder_Global_Config_Manager::get_instance()->health_check();
}

// Initialisation différée - l'instance sera créée quand nécessaire
// PDF_Builder_Global_Config_Manager::get_instance();




