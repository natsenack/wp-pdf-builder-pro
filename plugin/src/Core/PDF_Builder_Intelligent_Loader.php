<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * Chargeur Intelligent pour PDF Builder Pro
 *
 * Système de chargement optimisé qui gère le chargement conditionnel
 * des composants selon les besoins et l'état du système.
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du chargeur intelligent
 */
class PDF_Builder_Intelligent_Loader {

    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Composants chargés
     */
    private $loaded_components = array();

    /**
     * Dépendances des composants
     */
    private $component_dependencies = array();

    /**
     * État du système
     */
    private $system_state = array();

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_dependencies();
        $this->assess_system_state();
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
     * Initialiser les dépendances des composants
     */
    private function init_dependencies() {
        $this->component_dependencies = array(
            'security' => array(),
            'cache' => array('security'),
            'database' => array('security'),
            'logging' => array('security', 'database'),
            'api' => array('security', 'cache'),
            'analytics' => array('security', 'database', 'logging'),
            'reporting' => array('analytics', 'database'),
            'backup' => array('database', 'logging'),
            'updates' => array('security', 'api'),
            'ui' => array('security'),
        );
    }

    /**
     * Évaluer l'état du système
     */
    private function assess_system_state() {
        $this->system_state = array(
            'is_admin' => is_admin(),
            'is_ajax' => defined('DOING_AJAX') && DOING_AJAX,
            'is_cron' => defined('DOING_CRON') && DOING_CRON,
            'is_rest' => defined('REST_REQUEST') && REST_REQUEST,
            'user_can_manage' => current_user_can('manage_options'),
            'cache_enabled' => pdf_builder_get_option('pdf_builder_cache_enabled', '0') === '1',
            'logging_enabled' => pdf_builder_get_option('pdf_builder_enable_logging', '0') === '1',
            'analytics_enabled' => pdf_builder_get_option('pdf_builder_analytics_enabled', '0') === '1',
            'memory_limit_ok' => $this->check_memory_limit(),
            'php_version_ok' => version_compare(PHP_VERSION, '7.4', '>='),
        );
    }

    /**
     * Vérifier la limite de mémoire
     */
    private function check_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            $value = (int) $matches[1];
            $unit = strtolower($matches[2]);

            switch ($unit) {
                case 'g': $value *= 1024;
                case 'm': $value *= 1024;
                case 'k': $value *= 1024;
            }

            return $value >= 128 * 1024 * 1024; // 128MB minimum
        }
        return true; // Si on ne peut pas parser, assumer que c'est OK
    }

    /**
     * Enregistrer les hooks
     */
    private function register_hooks() {
        add_action('init', array($this, 'load_essential_components'), 1);
        add_action('wp_loaded', array($this, 'load_frontend_components'), 1);
        add_action('admin_init', array($this, 'load_admin_components'), 1);
        add_action('wp_ajax_pdf_builder_load_component', array($this, 'ajax_load_component'));
    }

    /**
     * Charger les composants essentiels
     */
    public function load_essential_components() {
        $components_to_load = array('security', 'database');

        if ($this->system_state['cache_enabled']) {
            $components_to_load[] = 'cache';
        }

        if ($this->system_state['logging_enabled']) {
            $components_to_load[] = 'logging';
        }

        $this->load_components($components_to_load);
    }

    /**
     * Charger les composants frontend
     */
    public function load_frontend_components() {
        if ($this->system_state['is_admin'] || $this->system_state['is_ajax']) {
            return;
        }

        $components_to_load = array('ui');

        if ($this->system_state['analytics_enabled']) {
            $components_to_load[] = 'analytics';
        }

        $this->load_components($components_to_load);
    }

    /**
     * Charger les composants admin
     */
    public function load_admin_components() {
        if (!$this->system_state['is_admin']) {
            return;
        }

        $components_to_load = array('api', 'reporting', 'backup', 'updates');

        $this->load_components($components_to_load);
    }

    /**
     * Charger des composants spécifiques
     */
    public function load_components($components) {
        foreach ($components as $component) {
            if (!$this->is_component_loaded($component)) {
                $this->load_component($component);
            }
        }
    }

    /**
     * Charger un composant spécifique
     */
    private function load_component($component) {
        // Vérifier les dépendances
        if (!$this->check_dependencies($component)) {
            return false;
        }

        // Charger le composant selon son type
        $loaded = false;
        switch ($component) {
            case 'security':
                $loaded = $this->load_security_component();
                break;
            case 'cache':
                $loaded = $this->load_cache_component();
                break;
            case 'database':
                $loaded = $this->load_database_component();
                break;
            case 'logging':
                $loaded = $this->load_logging_component();
                break;
            case 'api':
                $loaded = $this->load_api_component();
                break;
            case 'analytics':
                $loaded = $this->load_analytics_component();
                break;
            case 'reporting':
                $loaded = $this->load_reporting_component();
                break;
            case 'backup':
                $loaded = $this->load_backup_component();
                break;
            case 'updates':
                $loaded = $this->load_updates_component();
                break;
            case 'ui':
                $loaded = $this->load_ui_component();
                break;
        }

        if ($loaded) {
            $this->loaded_components[] = $component;
        }

        return $loaded;
    }

    /**
     * Vérifier si un composant est chargé
     */
    public function is_component_loaded($component) {
        return in_array($component, $this->loaded_components);
    }

    /**
     * Vérifier les dépendances d'un composant
     */
    private function check_dependencies($component) {
        if (!isset($this->component_dependencies[$component])) {
            return true;
        }

        foreach ($this->component_dependencies[$component] as $dependency) {
            if (!$this->is_component_loaded($dependency)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Méthodes de chargement des composants individuels
     */
    private function load_security_component() {
        return class_exists('PDF_Builder_Security_Validator');
    }

    private function load_cache_component() {
        return false; // Système de cache supprimé
    }

    private function load_database_component() {
        return class_exists('PDF_Builder_Database_Updater');
    }

    private function load_logging_component() {
        return class_exists('PDF_Builder_Core_Logger');
    }

    private function load_api_component() {
        return class_exists('PDF_Builder_API_Manager');
    }

    private function load_analytics_component() {
        return class_exists('PDF_Builder_Analytics_Manager');
    }

    private function load_reporting_component() {
        return class_exists('PDF_Builder_Reporting_System');
    }

    private function load_backup_component() {
        return class_exists('PDF_Builder_Backup_Recovery_System');
    }

    private function load_updates_component() {
        return class_exists('PDF_Builder_Update_Manager');
    }

    private function load_ui_component() {
        return class_exists('PDF_Builder_Theme_Customizer');
    }

    /**
     * Chargement AJAX d'un composant
     */
    public function ajax_load_component() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $component = sanitize_text_field($_POST['component'] ?? '');

        if (empty($component)) {
            wp_send_json_error('Composant non spécifié');
        }

        if ($this->load_component($component)) {
            wp_send_json_success(array(
                'message' => 'Composant chargé avec succès',
                'component' => $component
            ));
        } else {
            wp_send_json_error('Erreur lors du chargement du composant');
        }
    }

    /**
     * Obtenir l'état du chargeur
     */
    public function get_status() {
        return array(
            'loaded_components' => $this->loaded_components,
            'system_state' => $this->system_state,
            'total_components' => count($this->component_dependencies),
            'loaded_count' => count($this->loaded_components)
        );
    }
}




