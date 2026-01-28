<?php
/**
 * PDF Builder Pro - Gestionnaire de chargement optimisé
 * Remplace le système de chargement complexe par une approche plus propre
 */

// Déclarations conditionnelles des fonctions WordPress pour éviter les erreurs de linting
if (!function_exists('has_shortcode')) {
    function has_shortcode($content, $tag) { return false; }
}
if (!function_exists('has_block')) {
    function has_block($block_name, $post = null) { return false; }
}

class PDF_Builder_Loader {
    private static $instance = null;
    private $loaded_components = [];

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
        // Chargement différé intelligent
        add_action('plugins_loaded', [$this, 'load_early_components'], 1);
        add_action('init', [$this, 'load_on_demand'], 1);
        add_action('admin_init', [$this, 'load_admin_components'], 1);
    }

    /**
     * Charge les composants essentiels tôt
     */
    public function load_early_components() {
        $this->load_constants();
        // $this->load_core_autoloader();
        $this->load_security_components();
    }

    /**
     * Charge les composants à la demande
     */
    public function load_on_demand() {
        // Détecter si on a besoin du plugin complet
        if ($this->should_load_full_plugin()) {
            $this->load_full_plugin();
        } else {
            $this->load_minimal_components();
        }
    }

    /**
     * Charge les composants admin
     */
    public function load_admin_components() {
        if (!is_admin()) {
            return;
        }

        $this->load_component('admin_interface');
        $this->load_component('settings_manager');
    }

    /**
     * Détermine si le plugin complet doit être chargé
     */
    private function should_load_full_plugin() {
        // Pages admin PDF Builder
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') === 0) {
            return true;
        }

        // Requêtes AJAX PDF Builder
        if (wp_doing_ajax() && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') === 0) {
            return true;
        }

        // Shortcodes ou blocks PDF Builder sur la page
        if (!is_admin() && $this->has_pdf_builder_content()) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie si la page contient du contenu PDF Builder
     */
    private function has_pdf_builder_content() {
        global $post;
        if (!$post) {
            return false;
        }

        // Vérifier les shortcodes
        $has_shortcode = has_shortcode($post->post_content, 'pdf_builder');

        // Vérifier les blocks Gutenberg
        $has_block = has_block('pdf-builder/template', $post);

        return $has_shortcode || $has_block;
    }

    /**
     * Charge le plugin complet
     */
    private function load_full_plugin() {
        $this->load_component('core');
        $this->load_component('managers');
        $this->load_component('utilities');
        $this->load_component('ajax_handlers');
        $this->load_component('api_endpoints');
        $this->load_component('integrations');
    }

    /**
     * Charge les composants minimaux
     */
    private function load_minimal_components() {
        $this->load_component('minimal_core');
        $this->load_component('public_handlers');
    }

    /**
     * Charge un composant spécifique
     */
    private function load_component($component) {
        if (isset($this->loaded_components[$component])) {
            return; // Déjà chargé
        }

        $method = 'load_' . $component;
        if (method_exists($this, $method)) {
            $this->$method();
            $this->loaded_components[$component] = true;
        }
    }

    /**
     * Charge les constantes
     */
    private function load_constants() {
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)) . '/');
        }
        if (!defined('PDF_BUILDER_VERSION')) {
            define('PDF_BUILDER_VERSION', '1.0.1.0');
        }
    }

    /**
     * Charge l'autoloader du core
     */
    private function load_core_autoloader() {
        // $autoloader_path = PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
        // if (file_exists($autoloader_path)) {
        //     require_once $autoloader_path;
        //     if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
        //         \PDF_Builder\Core\PdfBuilderAutoloader::init(PDF_BUILDER_PLUGIN_DIR);
        //     }
        // }
    }

    /**
     * Charge les composants de sécurité
     */
    private function load_security_components() {
        $this->require_file('src/Core/core/security-manager.php');
        $this->require_file('src/Core/core/sanitizer.php');
    }

    /**
     * Charge le core complet
     */
    private function load_core() {
        $this->require_file('src/Core/PDF_Builder_Core.php');
        $this->require_file('src/Core/core/constants.php');
        $this->require_file('src/Core/TemplateDefaults.php');
    }

    /**
     * Charge les managers
     */
    private function load_managers() {
        $managers = [
            'PDF_Builder_Backup_Restore_Manager.php',
            // 'PDF_Builder_Drag_Drop_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_Feature_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_License_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_Logger.php', // Chargé dans bootstrap.php
            'PDF_Builder_PDF_Generator.php',
            'PDF_Builder_Resize_Manager.php',
            'PDF_Builder_Settings_Manager.php',
            'PDF_Builder_Template_Manager.php'
        ];

        // Managers dépendants de WooCommerce - chargés seulement si WooCommerce est disponible
        $woocommerce_managers = [
            'PDF_Builder_Status_Manager.php',
            'PDF_Builder_Variable_Mapper.php',
            'PDF_Builder_WooCommerce_Integration.php'
        ];

        foreach ($managers as $manager) {
            $this->require_file('src/Managers/' . $manager);
        }

        // Charger les managers WooCommerce seulement si WooCommerce est actif
        if (function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active()) {
            foreach ($woocommerce_managers as $manager) {
                $this->require_file('src/Managers/' . $manager);
            }
        }
    }

    /**
     * Charge les utilitaires
     */
    private function load_utilities() {
        $utilities = [
            'PDF_Builder_GDPR_Manager.php'
        ];

        // Charger l'Onboarding Manager seulement si WooCommerce est actif
        if (function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active()) {
            $utilities[] = 'PDF_Builder_Onboarding_Manager.php';
        }

        foreach ($utilities as $utility) {
            $this->require_file('src/utilities/' . $utility);
        }
    }

    /**
     * Charge les handlers AJAX
     */
    private function load_ajax_handlers() {
        $this->require_file('src/AJAX/Ajax_Handlers.php');
        $this->require_file('src/Admin/Canvas_AJAX_Handler.php');
    }

    /**
     * Charge les endpoints API
     */
    private function load_api_endpoints() {
        // Preview system moved to preview-system folder
        require_once dirname(__DIR__) . '/preview-system/index.php';
        $this->require_file('src/AJAX/preview-image-handler.php');
    }

    /**
     * Charge les intégrations
     */
    private function load_integrations() {
        // WooCommerce si actif - différer la vérification pour éviter les problèmes de chargement
        if (function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active()) {
            // Intégration WooCommerce - cache supprimé
        }
    }

    /**
     * Charge l'interface admin
     */
    private function load_admin_interface() {
        $this->require_file('src/Admin/PDF_Builder_Admin.php');
    }

    /**
     * Charge le gestionnaire de paramètres
     */
    private function load_settings_manager() {
        // Settings handlers are now managed by unified AJAX system
    }

    /**
     * Charge le core minimal
     */
    private function load_minimal_core() {
        // Charger seulement les composants essentiels pour le frontend
        $this->require_file('src/Core/core/constants.php');
    }

    /**
     * Charge les handlers publics
     */
    private function load_public_handlers() {
        // Handlers pour shortcodes, etc.
    }

    /**
     * Utilitaire pour charger un fichier en toute sécurité
     */
    private function require_file($relative_path) {
        $full_path = PDF_BUILDER_PLUGIN_DIR . $relative_path;
        if (file_exists($full_path)) {
            // Pour PDF_Builder_Admin.php, vérifier le namespace complet
            if (strpos($relative_path, 'PDF_Builder_Admin.php') !== false) {
                if (!class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
                    require_once $full_path;
                }
            } else {
                // Pour les autres fichiers, utiliser la logique existante
                if (!class_exists($this->get_class_name_from_path($relative_path))) {
                    require_once $full_path;
                }
            }
        }
    }

    /**
     * Extrait le nom de classe depuis le chemin du fichier
     */
    private function get_class_name_from_path($path) {
        // Logique simplifiée - à adapter selon vos besoins
        $filename = basename($path, '.php');
        return str_replace('_', '', ucwords($filename, '_'));
    }
}

// Initialiser le loader
PDF_Builder_Loader::get_instance();

