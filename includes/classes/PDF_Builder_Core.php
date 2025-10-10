<?php
/**
 * PDF Builder Core
 * Classe principale du plugin PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

class PDF_Builder_Core {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Version du plugin
     */
    private $version = '1.0.0';

    /**
     * Interface d'administration
     */
    private $admin = null;

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks WordPress
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        // Hooks d'activation/désactivation
        register_activation_hook(PDF_BUILDER_PLUGIN_DIR . 'pdf-builder-pro.php', array($this, 'activate'));
        register_deactivation_hook(PDF_BUILDER_PLUGIN_DIR . 'pdf-builder-pro.php', array($this, 'deactivate'));
    }

    /**
     * Charger les dépendances
     */
    private function load_dependencies() {
        // Les dépendances sont déjà chargées dans le fichier principal
        pdf_builder_debug('PDF Builder Core dependencies loaded', 2, 'core');
    }

    /**
     * Initialisation du plugin
     */
    public function init() {
        // Vérifier les dépendances
        $this->check_dependencies();

        // Initialiser les fonctionnalités
        $this->init_features();

        // Initialiser l'interface d'administration
        $this->init_admin();

        pdf_builder_debug('PDF Builder Pro initialized', 1, 'core');
    }

    /**
     * Initialisation de l'administration
     */
    public function admin_init() {
        // Ajouter les pages d'administration
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enregistrer les paramètres
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Vérifier les dépendances système
     */
    private function check_dependencies() {
        // Vérifier PHP
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }

        // Vérifier WordPress
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            add_action('admin_notices', array($this, 'wp_version_notice'));
            return;
        }

        pdf_builder_debug('System dependencies check passed', 2, 'core');
    }

    /**
     * Initialiser les fonctionnalités
     */
    private function init_features() {
        // Initialiser les managers canvas (récupérés depuis l'archive)
        $this->init_canvas_managers();

        // Ici seront initialisées les autres fonctionnalités du plugin
        // (gestionnaire de templates, générateur PDF, etc.)
        pdf_builder_debug('PDF Builder features initialized', 2, 'core');
    }

    /**
     * Initialiser les managers canvas
     */
    private function init_canvas_managers() {
        try {
            // Initialiser les managers dans le bon ordre (dépendances)
            if (class_exists('PDF_Builder_Canvas_Elements_Manager')) {
                $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();
                pdf_builder_debug('Canvas Elements Manager initialized', 2, 'core');
            }

            if (class_exists('PDF_Builder_Drag_Drop_Manager')) {
                $drag_manager = PDF_Builder_Drag_Drop_Manager::getInstance();
                pdf_builder_debug('Drag & Drop Manager initialized', 2, 'core');
            }

            if (class_exists('PDF_Builder_Resize_Manager')) {
                $resize_manager = PDF_Builder_Resize_Manager::getInstance();
                pdf_builder_debug('Resize Manager initialized', 2, 'core');
            }

            if (class_exists('PDF_Builder_Canvas_Interactions_Manager')) {
                $interactions_manager = PDF_Builder_Canvas_Interactions_Manager::getInstance();
                pdf_builder_debug('Canvas Interactions Manager initialized', 2, 'core');
            }

            pdf_builder_debug('All canvas managers initialized successfully', 1, 'core');

        } catch (Exception $e) {
            pdf_builder_log_error('Failed to initialize canvas managers: ' . $e->getMessage(), 'core');
        }
    }

    /**
     * Charger les scripts pour le frontend
     */
    public function enqueue_scripts() {
        // Scripts frontend si nécessaire
    }

    /**
     * Charger les scripts pour l'administration
     */
    public function admin_enqueue_scripts($hook) {
        // Debug logging
        error_log('PDF_Builder_Core Debug - Hook: ' . $hook);
        error_log('PDF_Builder_Core Debug - REQUEST_URI: ' . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));
        error_log('PDF_Builder_Core Debug - GET page: ' . (isset($_GET['page']) ? $_GET['page'] : 'not set'));

        // Charger les scripts sur toutes les pages du plugin
        if (strpos($hook, 'pdf-builder') !== false ||
            (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false) ||
            (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'pdf-builder-editor') !== false)) {

            error_log('PDF_Builder_Core Debug - Loading scripts');

            wp_enqueue_script(
                'pdf-builder-admin-core',
                PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js',
                array('jquery'),
                PDF_BUILDER_PRO_VERSION,
                true
            );            wp_enqueue_style(
                'pdf-builder-admin-core',
                PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css',
                array(),
                PDF_BUILDER_PRO_VERSION
            );

            // Localiser le script pour AJAX
            wp_localize_script('pdf-builder-admin-core', 'pdfBuilderAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_templates'),
                'strings' => array(
                    'loading' => __('Loading...', 'pdf-builder-pro'),
                    'error' => __('An error occurred', 'pdf-builder-pro'),
                    'success' => __('Success', 'pdf-builder-pro')
                )
            ));
        }
    }

    /**
     * Ajouter le menu d'administration
     */
    public function add_admin_menu() {
        add_menu_page(
            __('PDF Builder Pro', 'pdf-builder-pro'),
            __('PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro',
            array($this, 'admin_page'),
            'dashicons-pdf',
            30
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Templates', 'pdf-builder-pro'),
            __('Templates', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-templates',
            array($this, 'templates_page')
        );

        add_submenu_page(
            null, // Hidden page
            __('Template Editor', 'pdf-builder-pro'),
            __('Template Editor', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-editor',
            array($this, 'template_editor_page')
        );

        add_submenu_page(
            'pdf-builder-pro',
            __('Settings', 'pdf-builder-pro'),
            __('Settings', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Enregistrer les paramètres
     */
    public function register_settings() {
        register_setting('pdf_builder_options', 'pdf_builder_settings');

        add_settings_section(
            'pdf_builder_main',
            __('Main Settings', 'pdf-builder-pro'),
            array($this, 'settings_section_callback'),
            'pdf_builder_settings'
        );
    }

    /**
     * Page d'administration principale
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
            <p><?php _e('Welcome to PDF Builder Pro - Professional PDF creation made easy.', 'pdf-builder-pro'); ?></p>

            <div class="pdf-builder-dashboard">
                <div class="pdf-builder-card">
                    <h3><?php _e('Quick Start', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Create your first PDF template in minutes.', 'pdf-builder-pro'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button button-primary">
                        <?php _e('Create Template', 'pdf-builder-pro'); ?>
                    </a>
                </div>

                <div class="pdf-builder-card">
                    <h3><?php _e('Documentation', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Learn how to use all features.', 'pdf-builder-pro'); ?></p>
                    <a href="#" class="button"><?php _e('View Docs', 'pdf-builder-pro'); ?></a>
                </div>
            </div>
        </div>

        <style>
            .pdf-builder-dashboard {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            .pdf-builder-card {
                background: white;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .pdf-builder-card h3 {
                margin-top: 0;
                color: #23282d;
            }
        </style>
        <?php
    }

    /**
     * Page des templates
     */
    public function templates_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('PDF Templates', 'pdf-builder-pro'); ?></h1>
            <p><?php _e('Manage your PDF templates.', 'pdf-builder-pro'); ?></p>

            <div id="pdf-builder-templates-container">
                <!-- Le contenu React sera chargé ici -->
                <p><?php _e('Loading PDF Builder...', 'pdf-builder-pro'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Page de l'éditeur de template
     */
    public function template_editor_page() {
        include plugin_dir_path(__FILE__) . '../template-editor.php';
    }

    /**
     * Page des paramètres
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('PDF Builder Settings', 'pdf-builder-pro'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pdf_builder_options');
                do_settings_sections('pdf_builder_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Callback de la section des paramètres
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure PDF Builder Pro settings.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Activation du plugin
     */
    public function activate() {
        // Créer les tables de base de données si nécessaire
        $this->create_database_tables();

        // Définir les options par défaut
        add_option('pdf_builder_version', $this->version);

        pdf_builder_log('PDF Builder Pro activated', 1);
    }

    /**
     * Désactivation du plugin
     */
    public function deactivate() {
        // Nettoyer si nécessaire
        pdf_builder_log('PDF Builder Pro deactivated', 1);
    }

    /**
     * Créer les tables de base de données
     */
    private function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table pour les templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $sql_templates = "CREATE TABLE $table_templates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_templates);

        pdf_builder_debug('Database tables created', 2, 'core');
    }

    /**
     * Notice pour version PHP trop ancienne
     */
    public function php_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('PDF Builder Pro requires PHP 7.4 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Notice pour version WordPress trop ancienne
     */
    public function wp_version_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('PDF Builder Pro requires WordPress 5.0 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Initialisation de l'interface d'administration
     */
    private function init_admin() {
        // Vérifier si WordPress est disponible
        if (!function_exists('add_action')) {
            pdf_builder_debug('WordPress admin functions not available, skipping admin initialization', 2, 'core');
            return;
        }

        // Inclure et instancier la classe d'administration
        if (!class_exists('PDF_Builder_Admin')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/class-pdf-builder-admin.php';
        }

        if (class_exists('PDF_Builder_Admin')) {
            $this->admin = PDF_Builder_Admin::getInstance($this);
            pdf_builder_debug('PDF Builder Admin interface initialized', 1, 'core');
        } else {
            pdf_builder_debug('Failed to load PDF_Builder_Admin class', 1, 'core');
        }
    }

    /**
     * Obtenir la version du plugin
     */
    public function get_version() {
        return $this->version;
    }
}

