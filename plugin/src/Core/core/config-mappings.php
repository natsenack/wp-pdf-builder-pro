<?php
/**
 * PDF Builder Configuration Mappings
 *
 * Centralise toutes les configurations et paramètres par défaut
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Config_Mappings {

    // ==========================================
    // CONFIGURATIONS GÉNÉRALES
    // ==========================================

    private static $general_config = [
        'plugin_version' => '1.0.0',
        'plugin_name' => 'PDF Builder Pro',
        'plugin_slug' => 'pdf-builder-pro',
        'plugin_file' => 'pdf-builder-pro.php',
        'min_wp_version' => '5.0',
        'min_php_version' => '7.2',
        'text_domain' => 'pdf-builder-pro',
        'db_version' => '1.0',
        'api_version' => 'v1'
    ];

    // ==========================================
    // CHEMINS ET URLS
    // ==========================================

    private static $paths_config = [
        'plugin_dir' => '',
        'plugin_url' => '',
        'assets_dir' => 'assets/',
        'assets_url' => 'assets/',
        'css_dir' => 'assets/css/',
        'css_url' => 'assets/css/',
        'js_dir' => 'assets/js/',
        'js_url' => 'assets/js/',
        'images_dir' => 'assets/images/',
        'images_url' => 'assets/images/',
        'templates_dir' => 'resources/templates/',
        'templates_url' => 'resources/templates/',
        'languages_dir' => 'resources/languages/',
        'languages_url' => 'resources/languages/',
        'uploads_dir' => 'pdf-builder-uploads/',
        'cache_dir' => 'pdf-builder-cache/',
        'temp_dir' => 'pdf-builder-temp/'
    ];

    // ==========================================
    // CONFIGURATIONS DE BASE DE DONNÉES
    // ==========================================

    private static $database_config = [
        'tables' => [
            'templates' => 'pdf_builder_templates',
            'elements' => 'pdf_builder_elements',
            'settings' => 'pdf_builder_settings',
            'logs' => 'pdf_builder_logs',
            'cache' => 'pdf_builder_cache'
        ],

        'table_prefix' => 'pdf_builder_',

        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci',

        'indexes' => [
            'templates' => ['user_id', 'created_at', 'updated_at'],
            'elements' => ['template_id', 'type', 'layer'],
            'settings' => ['user_id', 'option_name'],
            'logs' => ['level', 'created_at'],
            'cache' => ['key', 'expires_at']
        ]
    ];

    // ==========================================
    // CONFIGURATIONS AJAX
    // ==========================================

    private static $ajax_config = [
        'actions' => [
            'save_template' => 'pdf_builder_save_template',
            'load_template' => 'pdf_builder_load_template',
            'delete_template' => 'pdf_builder_delete_template',
            'export_pdf' => 'pdf_builder_export_pdf',
            'save_settings' => 'pdf_builder_save_settings',
            'load_settings' => 'pdf_builder_load_settings',
            'upload_image' => 'pdf_builder_upload_image',
            'get_fonts' => 'pdf_builder_get_fonts',
            'validate_data' => 'pdf_builder_validate_data',
            'get_preview' => 'pdf_builder_get_preview'
        ],

        'nonces' => [
            'save_template' => 'pdf_builder_save_template_nonce',
            'load_template' => 'pdf_builder_load_template_nonce',
            'delete_template' => 'pdf_builder_delete_template_nonce',
            'export_pdf' => 'pdf_builder_export_pdf_nonce',
            'save_settings' => 'pdf_builder_save_settings_nonce',
            'upload_image' => 'pdf_builder_upload_image_nonce',
            'ajax_request' => 'pdf_builder_ajax_nonce'
        ],

        'timeouts' => [
            'default' => 30,
            'upload' => 60,
            'export' => 120,
            'preview' => 15
        ]
    ];

    // ==========================================
    // CONFIGURATIONS DE CACHE
    // ==========================================

    private static $cache_config = [
        'enabled' => true,
        'ttl' => [
            'template' => 3600,     // 1 heure
            'settings' => 1800,     // 30 minutes
            'fonts' => 86400,       // 24 heures
            'preview' => 300,       // 5 minutes
            'export' => 7200        // 2 heures
        ],

        'drivers' => [
            'file' => 'PDF_Builder_Cache_File',
            'database' => 'PDF_Builder_Cache_Database',
            'redis' => 'PDF_Builder_Cache_Redis',
            'memcached' => 'PDF_Builder_Cache_Memcached'
        ],

        'default_driver' => 'file',

        'file_cache' => [
            'path' => WP_CONTENT_DIR . '/cache/pdf-builder/',
            'max_size' => '100M',
            'cleanup_interval' => 3600
        ]
    ];

    // ==========================================
    // CONFIGURATIONS DE SÉCURITÉ
    // ==========================================

    private static $security_config = [
        'encryption' => [
            'enabled' => true,
            'method' => 'AES-256-CBC',
            'key_length' => 32
        ],

        'rate_limiting' => [
            'enabled' => true,
            'max_requests' => 100,
            'time_window' => 60, // secondes
            'block_duration' => 900 // 15 minutes
        ],

        'input_validation' => [
            'sanitize_all' => true,
            'max_input_length' => 10000,
            'allowed_tags' => ['p', 'br', 'strong', 'em', 'u', 'a', 'span'],
            'allowed_protocols' => ['http', 'https', 'ftp']
        ],

        'file_upload' => [
            'max_size' => '5M',
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'pdf'],
            'scan_for_viruses' => true,
            'quarantine_suspicious' => true
        ]
    ];

    // ==========================================
    // CONFIGURATIONS DE PERFORMANCE
    // ==========================================

    private static $performance_config = [
        'lazy_loading' => [
            'enabled' => true,
            'threshold' => 50,
            'batch_size' => 10
        ],

        'compression' => [
            'enabled' => true,
            'level' => 6,
            'types' => ['gzip', 'deflate']
        ],

        'minification' => [
            'css' => true,
            'js' => true,
            'html' => false
        ],

        'cdn' => [
            'enabled' => false,
            'url' => '',
            'assets' => ['css', 'js', 'images']
        ],

        'memory_limit' => '256M',
        'max_execution_time' => 120,
        'memory_cleanup_interval' => 300000 // millisecondes
    ];

    // ==========================================
    // CONFIGURATIONS DE LOGGING
    // ==========================================

    private static $logging_config = [
        'enabled' => true,
        'level' => 'warning', // debug, info, notice, warning, error, critical, alert, emergency

        'handlers' => [
            'file' => [
                'enabled' => true,
                'path' => WP_CONTENT_DIR . '/logs/pdf-builder/',
                'max_files' => 30,
                'max_size' => '10M'
            ],

            'database' => [
                'enabled' => false,
                'table' => 'pdf_builder_logs',
                'max_entries' => 10000
            ],

            'email' => [
                'enabled' => false,
                'recipients' => [],
                'levels' => ['error', 'critical', 'alert', 'emergency']
            ]
        ],

        'format' => '[{timestamp}] {level}: {message} {context}',
        'date_format' => 'Y-m-d H:i:s'
    ];

    // ==========================================
    // CONFIGURATIONS DE MISE À JOUR
    // ==========================================

    private static $update_config = [
        'check_updates' => true,
        'update_url' => 'https://api.pdfbuilderpro.com/updates',
        'update_interval' => 43200, // 12 heures

        'auto_update' => [
            'minor' => true,
            'major' => false,
            'security' => true
        ],

        'backup_before_update' => true,
        'rollback_enabled' => true,
        'max_rollback_versions' => 5
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS
    // ==========================================

    /**
     * Obtenir la configuration générale
     */
    public static function get_general_config() {
        return self::$general_config;
    }

    /**
     * Obtenir une valeur de configuration générale
     */
    public static function get_general_config_value($key) {
        return self::$general_config[$key] ?? null;
    }

    /**
     * Obtenir les chemins et URLs
     */
    public static function get_paths_config() {
        return self::$paths_config;
    }

    /**
     * Obtenir un chemin ou URL spécifique (avec résolution des chemins absolus)
     */
    public static function get_path($key) {
        $path = self::$paths_config[$key] ?? null;

        if (!$path) {
            return null;
        }

        // Résoudre les chemins absolus
        switch ($key) {
            case 'plugin_dir':
                return plugin_dir_path(dirname(__FILE__) . '/../');
            case 'plugin_url':
                return plugin_dir_url(dirname(__FILE__) . '/../');
            default:
                return $path;
        }
    }

    /**
     * Obtenir la configuration de base de données
     */
    public static function get_database_config() {
        return self::$database_config;
    }

    /**
     * Obtenir le nom d'une table
     */
    public static function get_table_name($table) {
        return self::$database_config['tables'][$table] ?? null;
    }

    /**
     * Obtenir la configuration AJAX
     */
    public static function get_ajax_config() {
        return self::$ajax_config;
    }

    /**
     * Obtenir une action AJAX
     */
    public static function get_ajax_action($action) {
        return self::$ajax_config['actions'][$action] ?? null;
    }

    /**
     * Obtenir un nonce AJAX
     */
    public static function get_ajax_nonce($action) {
        return self::$ajax_config['nonces'][$action] ?? null;
    }

    /**
     * Obtenir la configuration de cache
     */
    public static function get_cache_config() {
        return self::$cache_config;
    }

    /**
     * Obtenir le TTL de cache pour un type
     */
    public static function get_cache_ttl($type) {
        return self::$cache_config['ttl'][$type] ?? self::$cache_config['ttl']['default'] ?? 3600;
    }

    /**
     * Obtenir la configuration de sécurité
     */
    public static function get_security_config() {
        return self::$security_config;
    }

    /**
     * Obtenir la configuration de performance
     */
    public static function get_performance_config() {
        return self::$performance_config;
    }

    /**
     * Obtenir la configuration de logging
     */
    public static function get_logging_config() {
        return self::$logging_config;
    }

    /**
     * Obtenir la configuration de mise à jour
     */
    public static function get_update_config() {
        return self::$update_config;
    }

    /**
     * Vérifier si une fonctionnalité est activée
     */
    public static function is_feature_enabled($feature, $config_type = 'general') {
        $config = null;

        switch ($config_type) {
            case 'cache':
                $config = self::$cache_config;
                break;
            case 'security':
                $config = self::$security_config;
                break;
            case 'performance':
                $config = self::$performance_config;
                break;
            case 'logging':
                $config = self::$logging_config;
                break;
            case 'update':
                $config = self::$update_config;
                break;
            default:
                return false;
        }

        return isset($config[$feature]['enabled']) ? $config[$feature]['enabled'] : false;
    }

    /**
     * Obtenir une valeur de configuration imbriquée
     */
    public static function get_nested_config_value($path, $config_type = 'general') {
        $config = null;

        switch ($config_type) {
            case 'general':
                $config = self::$general_config;
                break;
            case 'paths':
                $config = self::$paths_config;
                break;
            case 'database':
                $config = self::$database_config;
                break;
            case 'ajax':
                $config = self::$ajax_config;
                break;
            case 'cache':
                $config = self::$cache_config;
                break;
            case 'security':
                $config = self::$security_config;
                break;
            case 'performance':
                $config = self::$performance_config;
                break;
            case 'logging':
                $config = self::$logging_config;
                break;
            case 'update':
                $config = self::$update_config;
                break;
        }

        if (!$config) {
            return null;
        }

        $keys = explode('.', $path);
        $value = $config;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Générer un tableau de configuration pour l'export
     */
    public static function export_config() {
        return [
            'general' => self::$general_config,
            'paths' => self::$paths_config,
            'database' => self::$database_config,
            'ajax' => self::$ajax_config,
            'cache' => self::$cache_config,
            'security' => self::$security_config,
            'performance' => self::$performance_config,
            'logging' => self::$logging_config,
            'update' => self::$update_config
        ];
    }

    /**
     * Valider une configuration
     */
    public static function validate_config($config, $type = 'general') {
        $errors = [];

        switch ($type) {
            case 'database':
                if (empty($config['tables'])) {
                    $errors[] = 'Database tables configuration is missing';
                }
                break;

            case 'ajax':
                if (empty($config['actions'])) {
                    $errors[] = 'AJAX actions configuration is missing';
                }
                if (empty($config['nonces'])) {
                    $errors[] = 'AJAX nonces configuration is missing';
                }
                break;

            case 'cache':
                if (!isset($config['enabled'])) {
                    $errors[] = 'Cache enabled flag is missing';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
