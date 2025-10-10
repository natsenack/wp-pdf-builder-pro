<?php
/**
 * Gestionnaire de Configuration Avancé - PDF Builder Pro
 *
 * Gestion centralisée de la configuration avec :
 * - Validation des paramètres
 * - Cache des configurations
 * - Migration automatique
 * - Environnements multiples
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Gestionnaire de Configuration
 */
class PDF_Builder_Config_Manager {

    /**
     * Instance singleton
     * @var PDF_Builder_Config_Manager
     */
    private static $instance = null;

    /**
     * Configuration en cache
     * @var array
     */
    private $config_cache = [];

    /**
     * Schéma de validation
     * @var array
     */
    private $validation_schema = [];

    /**
     * Valeurs par défaut
     * @var array
     */
    private $defaults = [];

    /**
     * Gestionnaire de cache
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_defaults();
        $this->init_validation_schema();
        $this->cache_manager = PDF_Builder_Cache_Manager::getInstance();
        $this->logger = PDF_Builder_Logger::getInstance();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Config_Manager
     */
    public static function getInstance(): PDF_Builder_Config_Manager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation des valeurs par défaut
     *
     * @return void
     */
    private function init_defaults(): void {
        $this->defaults = [
            // Général
            'version' => PDF_BUILDER_VERSION,
            'debug_mode' => false,
            'environment' => 'production',

            // Cache
            'cache_enabled' => true,
            'cache_ttl' => PDF_BUILDER_CACHE_TTL,
            'cache_compression' => true,

            // Performance
            'max_execution_time' => PDF_BUILDER_MAX_EXECUTION_TIME,
            'memory_limit' => PDF_BUILDER_MEMORY_LIMIT,
            'max_concurrent_processes' => 5,

            // PDF
            'pdf_quality' => 'high',
            'default_format' => 'A4',
            'default_orientation' => 'portrait',
            'pdf_compression' => true,
            'embed_fonts' => true,

            // Templates
            'max_template_size' => 50 * 1024 * 1024, // 50MB
            'allowed_template_types' => ['pdf', 'docx', 'html', 'facture', 'devis', 'bon_commande', 'bon_livraison', 'reçu'],
            'template_cache_enabled' => true,

            // Sécurité
            'nonce_lifetime' => 86400, // 24 heures
            'rate_limiting_enabled' => true,
            'max_requests_per_minute' => 60,
            'ip_whitelist' => [],
            'ip_blacklist' => [],

            // Base de données
            'db_optimization_enabled' => true,
            'db_cleanup_interval' => 86400, // 24 heures
            'max_db_connections' => 10,

            // API
            'api_enabled' => true,
            'api_rate_limiting' => true,
            'api_auth_required' => true,
            'api_cors_enabled' => false,

            // Logging
            'log_level' => 'warning',
            'log_max_files' => 30,
            'log_max_size' => 10 * 1024 * 1024, // 10MB
            'log_retention_days' => 90,

            // Interface utilisateur
            'ui_theme' => 'default',
            'ui_language' => function_exists('get_locale') ? get_locale() : 'en_US',
            'ui_compact_mode' => false,
            'ui_animations_enabled' => true,

            // Notifications
            'email_notifications_enabled' => true,
            'admin_email' => function_exists('get_option') ? get_option('admin_email') : '',
            'notification_events' => [
                'template_created',
                'template_updated',
                'document_generated',
                'error_occurred'
            ],

            // Intégrations
            'woocommerce_integration' => true,
            'gravity_forms_integration' => false,
            'acf_integration' => false,

            // Avancé
            'custom_css_enabled' => false,
            'custom_js_enabled' => false,
            'webhooks_enabled' => false,
            'bulk_operations_enabled' => true
        ];
    }

    /**
     * Initialisation du schéma de validation
     *
     * @return void
     */
    private function init_validation_schema(): void {
        $this->validation_schema = [
            'version' => ['type' => 'string', 'required' => true],
            'debug_mode' => ['type' => 'boolean'],
            'environment' => ['type' => 'string', 'enum' => ['development', 'staging', 'production']],
            'cache_enabled' => ['type' => 'boolean'],
            'cache_ttl' => ['type' => 'integer', 'min' => 60, 'max' => 604800],
            'max_execution_time' => ['type' => 'integer', 'min' => 30, 'max' => 300],
            'memory_limit' => ['type' => 'string', 'pattern' => '/^\d+[KMG]$/'],
            'pdf_quality' => ['type' => 'string', 'enum' => ['low', 'medium', 'high', 'ultra']],
            'default_format' => ['type' => 'string', 'enum' => ['A3', 'A4', 'A5', 'Letter', 'Legal']],
            'default_orientation' => ['type' => 'string', 'enum' => ['portrait', 'landscape']],
            'max_template_size' => ['type' => 'integer', 'min' => 1024, 'max' => 100 * 1024 * 1024],
            'allowed_template_types' => ['type' => 'array'],
            'log_level' => ['type' => 'string', 'enum' => ['debug', 'info', 'warning', 'error']],
            'ui_theme' => ['type' => 'string'],
            'notification_events' => ['type' => 'array']
        ];
    }

    /**
     * Obtenir une valeur de configuration
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null) {
        // Vérifier le cache
        if (isset($this->config_cache[$key])) {
            return $this->config_cache[$key];
        }

        // Obtenir de la base de données (seulement si WordPress est chargé)
        $db_value = function_exists('get_option') ? get_option('pdf_builder_' . $key) : false;

        if ($db_value !== false) {
            $value = $this->validate_and_sanitize($key, $db_value);
        } else {
            $value = $this->defaults[$key] ?? $default;
        }

        // Mettre en cache
        $this->config_cache[$key] = $value;

        return $value;
    }

    /**
     * Définir une valeur de configuration
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function set(string $key, $value): bool {
        // Validation
        $validated_value = $this->validate_and_sanitize($key, $value);
        if ($validated_value === null) {
            $this->logger->warning('Invalid configuration value', [
                'key' => $key,
                'value' => $value
            ]);
            return false;
        }

        // Sauvegarder en base
        $result = update_option('pdf_builder_' . $key, $validated_value);

        if ($result) {
            // Mettre à jour le cache
            $this->config_cache[$key] = $validated_value;

            // Invalider le cache global
            $this->cache_manager->delete('config_' . $key);

            $this->logger->info('Configuration updated', [
                'key' => $key,
                'value' => $validated_value
            ]);
        }

        return $result;
    }

    /**
     * Définir plusieurs valeurs de configuration
     *
     * @param array $config
     * @return bool
     */
    public function set_multiple(array $config): bool {
        $success = true;

        foreach ($config as $key => $value) {
            if (!$this->set($key, $value)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Supprimer une configuration
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool {
        $result = delete_option('pdf_builder_' . $key);

        if ($result) {
            unset($this->config_cache[$key]);
            $this->cache_manager->delete('config_' . $key);

            $this->logger->info('Configuration deleted', ['key' => $key]);
        }

        return $result;
    }

    /**
     * Réinitialiser une configuration à sa valeur par défaut
     *
     * @param string $key
     * @return bool
     */
    public function reset(string $key): bool {
        if (!isset($this->defaults[$key])) {
            return false;
        }

        return $this->set($key, $this->defaults[$key]);
    }

    /**
     * Obtenir toutes les configurations
     *
     * @return array
     */
    public function get_all(): array {
        $config = [];

        foreach (array_keys($this->defaults) as $key) {
            $config[$key] = $this->get($key);
        }

        return $config;
    }

    /**
     * Exporter la configuration
     *
     * @return array
     */
    public function export(): array {
        return [
            'version' => PDF_BUILDER_VERSION,
            'exported_at' => current_time('mysql'),
            'config' => $this->get_all()
        ];
    }

    /**
     * Importer la configuration
     *
     * @param array $data
     * @return bool
     */
    public function import(array $data): bool {
        if (!isset($data['config']) || !is_array($data['config'])) {
            return false;
        }

        // Sauvegarder la configuration actuelle
        $backup = $this->export();

        try {
            $this->set_multiple($data['config']);
            $this->logger->info('Configuration imported successfully');
            return true;
        } catch (Exception $e) {
            // Restaurer la sauvegarde en cas d'erreur
            $this->set_multiple($backup['config']);
            $this->logger->error('Configuration import failed, backup restored', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Valider et nettoyer une valeur
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    private function validate_and_sanitize(string $key, $value) {
        if (!isset($this->validation_schema[$key])) {
            return $value; // Pas de validation définie
        }

        $schema = $this->validation_schema[$key];

        // Validation de type
        if (isset($schema['type'])) {
            $value = $this->validate_type($value, $schema['type']);
            if ($value === null) {
                return null;
            }
        }

        // Validation enum
        if (isset($schema['enum']) && !in_array($value, $schema['enum'])) {
            return null;
        }

        // Validation de plage
        if (isset($schema['min']) && $value < $schema['min']) {
            return null;
        }

        if (isset($schema['max']) && $value > $schema['max']) {
            return null;
        }

        // Validation de pattern
        if (isset($schema['pattern']) && !preg_match($schema['pattern'], $value)) {
            return null;
        }

        return $value;
    }

    /**
     * Valider le type d'une valeur
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function validate_type($value, string $type) {
        switch ($type) {
            case 'string':
                return is_string($value) ? sanitize_text_field($value) : null;
            case 'integer':
                return is_numeric($value) ? (int) $value : null;
            case 'boolean':
                return is_bool($value) ? $value : null;
            case 'array':
                return is_array($value) ? $value : null;
            case 'float':
                return is_numeric($value) ? (float) $value : null;
            default:
                return $value;
        }
    }

    /**
     * Migration automatique de la configuration
     *
     * @return void
     */
    public function migrate(): void {
        $current_version = $this->get('version', '1.0.0');

        if (version_compare($current_version, PDF_BUILDER_VERSION, '<')) {
            $this->perform_migration($current_version, PDF_BUILDER_VERSION);
            $this->set('version', PDF_BUILDER_VERSION);

            $this->logger->info('Configuration migrated', [
                'from' => $current_version,
                'to' => PDF_BUILDER_VERSION
            ]);
        }
    }

    /**
     * Effectuer la migration
     *
     * @param string $from_version
     * @param string $to_version
     * @return void
     */
    private function perform_migration(string $from_version, string $to_version): void {
        // Migration 1.0.0 -> 1.1.0
        if (version_compare($from_version, '1.1.0', '<')) {
            // Ajouter de nouvelles configurations par défaut
            $new_defaults = [
                'bulk_operations_enabled' => true,
                'webhooks_enabled' => false
            ];

            foreach ($new_defaults as $key => $value) {
                if ($this->get($key) === null) {
                    $this->set($key, $value);
                }
            }
        }

        // Autres migrations futures...
    }

    /**
     * Vérifier si une configuration existe
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return isset($this->defaults[$key]) || (function_exists('get_option') && get_option('pdf_builder_' . $key) !== false);
    }

    /**
     * Obtenir les valeurs par défaut
     *
     * @return array
     */
    public function get_defaults(): array {
        return $this->defaults;
    }

    /**
     * Vider le cache de configuration
     *
     * @return void
     */
    public function clear_cache(): void {
        $this->config_cache = [];
        $this->cache_manager->delete('config_*');
    }

    /**
     * Initialisation du gestionnaire
     *
     * @return void
     */
    public function init(): void {
        $this->migrate();
        $this->logger->info('Configuration manager initialized');
    }
}

