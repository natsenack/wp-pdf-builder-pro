<?php
/**
 * PDF Builder Pro - Admin Core (Refactored)
 * Classe principale réduite qui orchestre les modules
 */

class PDF_Builder_Admin {

    /**
     * Instance singleton
     */
    private static $instance = null;

    /**
     * Instance de la classe principale
     */
    private $main;

    /**
     * Gestionnaires de modules
     */
    private $template_manager;
    private $pdf_generator;
    private $woocommerce_integration;
    private $settings_manager;
    private $diagnostic_manager;

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance($main_instance = null) {
        if (null === self::$instance) {
            self::$instance = new self($main_instance);
        }
        return self::$instance;
    }

    /**
     * Constructeur privé pour singleton
     */
    private function __construct($main_instance) {
        $this->main = $main_instance;

        // Initialiser les modules
        $this->init_modules();

        // Initialiser les hooks principaux
        $this->init_hooks();
    }

    /**
     * Initialiser les modules
     */
    private function init_modules() {
        // Inclure les classes de modules
        require_once plugin_dir_path(__FILE__) . 'managers/class-pdf-builder-template-manager.php';
        require_once plugin_dir_path(__FILE__) . 'managers/class-pdf-builder-pdf-generator.php';
        require_once plugin_dir_path(__FILE__) . 'managers/class-pdf-builder-woocommerce-integration.php';
        require_once plugin_dir_path(__FILE__) . 'managers/class-pdf-builder-settings-manager.php';
        require_once plugin_dir_path(__FILE__) . 'managers/class-pdf-builder-diagnostic-manager.php';

        // Instancier les modules
        $this->template_manager = new PDF_Builder_Template_Manager($this->main);
        $this->pdf_generator = new PDF_Builder_PDF_Generator($this->main);
        $this->woocommerce_integration = new PDF_Builder_WooCommerce_Integration($this->main);
        $this->settings_manager = new PDF_Builder_Settings_Manager($this->main);
        $this->diagnostic_manager = new PDF_Builder_Diagnostic_Manager($this->main);
    }

    /**
     * Initialiser les hooks principaux
     */
    private function init_hooks() {
        // Menu d'administration
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Scripts et styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // AJAX handlers principaux
        add_action('wp_ajax_pdf_builder_check_database', [$this->diagnostic_manager, 'ajax_check_database']);
    }

    /**
     * Vérifier les permissions d'administration
     */
    private function check_admin_permissions() {
        // Si le mode debug est activé, pas de vérification
        if (defined('PDF_BUILDER_DEBUG_MODE') && PDF_BUILDER_DEBUG_MODE) {
            return;
        }

        if (!is_user_logged_in() || !current_user_can('read')) {
            wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Vérifier si l'utilisateur a accès basé sur les rôles autorisés
        if (!$this->user_has_pdf_access()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }
    }

    /**
     * Vérifier si l'utilisateur actuel a accès au PDF Builder basé sur les rôles autorisés
     */
    private function user_has_pdf_access() {
        // Les administrateurs ont toujours accès
        if (current_user_can('administrator')) {
            return true;
        }

        $user_id = get_current_user_id();

        // Vérifier le cache (valide pour 5 minutes)
        $cache_key = 'pdf_builder_user_access_' . $user_id;
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result === 'allowed';
        }

        // Récupérer les rôles autorisés depuis les options
        $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator']);

        // S'assurer que c'est un tableau
        if (!is_array($allowed_roles)) {
            $allowed_roles = ['administrator'];
        }

        // Vérifier si l'utilisateur a un des rôles autorisés
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        $has_access = false;

        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Mettre en cache le résultat (5 minutes)
        set_transient($cache_key, $has_access ? 'allowed' : 'denied', 5 * MINUTE_IN_SECONDS);

        return $has_access;
    }

    /**
     * Ajouter le menu d'administration
     */
    public function add_admin_menu() {
        add_menu_page(
            'PDF Builder Pro',
            'PDF Builder',
            'read',
            'pdf-builder-pro',
            [$this, 'admin_page'],
            'dashicons-pdf',
            30
        );

        // Sous-menus
        add_submenu_page(
            'pdf-builder-pro',
            __('Templates', 'pdf-builder-pro'),
            __('Templates', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-templates',
            [$this->template_manager, 'templates_page']
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Paramètres', 'pdf-builder-pro'),
            __('Paramètres', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-settings',
            [$this->settings_manager, 'settings_page']
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Rendu Canvas', 'pdf-builder-pro'),
            __('Rendu Canvas', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-canvas-render',
            [$this->settings_manager, 'canvas_render_settings_page']
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Diagnostic', 'pdf-builder-pro'),
            __('Diagnostic', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-diagnostic',
            [$this->diagnostic_manager, 'diagnostic_page']
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Test TCPDF', 'pdf-builder-pro'),
            __('Test TCPDF', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-test-tcpdf',
            [$this->diagnostic_manager, 'test_tcpdf_page']
        );

        // Éditeur de template (React/TypeScript)
        add_submenu_page(
            'pdf-builder-pro',
            __('Éditeur de Template', 'pdf-builder-pro'),
            __('Éditeur', 'pdf-builder-pro'),
            'read',
            'pdf-builder-editor',
            [$this, 'template_editor_page']
        );
    }

    /**
     * Page d'accueil de l'administration
     */
    public function admin_page() {
        $this->check_admin_permissions();
        include plugin_dir_path(dirname(__FILE__)) . '../admin-page.php';
    }

    /**
     * Page éditeur de template (React/TypeScript)
     */
    public function template_editor_page() {
        $this->check_admin_permissions();
        include plugin_dir_path(dirname(__FILE__)) . '../template-editor.php';
    }

    /**
     * Charger les scripts et styles d'administration
     */
    public function enqueue_admin_scripts($hook) {
        // Scripts pour toutes les pages du plugin
        if (strpos($hook, 'pdf-builder') !== false) {
            wp_enqueue_script('jquery');

            // Scripts spécifiques selon la page
            if ($hook === 'pdf-builder_page_pdf-builder-editor') {
                // Scripts pour l'éditeur React/TypeScript
                $this->enqueue_editor_scripts();
            } elseif ($hook === 'toplevel_page_pdf-builder-pro' || strpos($hook, 'pdf-builder') !== false) {
                // Scripts pour les pages d'administration
                $this->enqueue_admin_page_scripts();
            }
        }
    }

    /**
     * Scripts pour l'éditeur
     */
    private function enqueue_editor_scripts() {
        // Scripts React/TypeScript pour l'éditeur
        wp_enqueue_script(
            'pdf-builder-editor',
            plugin_dir_url(dirname(__FILE__)) . '../assets/js/editor.js',
            ['wp-element', 'wp-api-fetch'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'pdf-builder-editor',
            plugin_dir_url(dirname(__FILE__)) . '../assets/css/editor.css',
            [],
            '1.0.0'
        );

        // Localiser les scripts
        wp_localize_script('pdf-builder-editor', 'pdfBuilderEditor', [
            'nonce' => wp_create_nonce('pdf_builder_nonce'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'strings' => [
                'save' => __('Sauvegarder', 'pdf-builder-pro'),
                'preview' => __('Aperçu', 'pdf-builder-pro'),
                'download' => __('Télécharger', 'pdf-builder-pro'),
            ]
        ]);
    }

    /**
     * Scripts pour les pages d'administration
     */
    private function enqueue_admin_page_scripts() {
        wp_enqueue_script(
            'pdf-builder-admin',
            plugin_dir_url(dirname(__FILE__)) . '../assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'pdf-builder-admin',
            plugin_dir_url(dirname(__FILE__)) . '../assets/css/admin.css',
            [],
            '1.0.0'
        );

        // Localiser pour AJAX
        wp_localize_script('pdf-builder-admin', 'pdfBuilderAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_maintenance'),
        ]);
    }

    /**
     * Méthodes publiques pour accéder aux modules (pour compatibilité)
     */
    public function get_template_manager() {
        return $this->template_manager;
    }

    public function get_pdf_generator() {
        return $this->pdf_generator;
    }

    public function get_woocommerce_integration() {
        return $this->woocommerce_integration;
    }

    public function get_settings_manager() {
        return $this->settings_manager;
    }

    public function get_diagnostic_manager() {
        return $this->diagnostic_manager;
    }
}