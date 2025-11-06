<?php

namespace PDF_Builder\Core;

/**
 * PDF Builder Core
 * Classe principale du plugin PDF Builder Pro
 *
 * @method static void pdf_builder_log(string $message, int $level = 2, array $context = [])
 */

class PDF_Builder_Core
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Flag pour éviter l'ajout multiple du menu
     */
    private static $menu_added = false;

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
    private function __construct()
    {
        $this->loadDependencies();
    }

    /**
     * Charger les dépendances nécessaires
     */
    private function loadDependencies()
    {
        // Charger les managers essentiels
        $managers = array(
            'PDF_Builder_Cache_Manager.php',
            'PDF_Builder_Drag_Drop_Manager.php',
            'PDF_Builder_Feature_Manager.php',
            'PDF_Builder_License_Manager.php',
            'PDF_Builder_Logger.php',
            'PDF_Builder_PDF_Generator.php',
            'PDF_Builder_Resize_Manager.php',
            'PDF_Builder_Settings_Manager.php',
            'PDF_Builder_Status_Manager.php',
            'PDF_Builder_Template_Manager.php',
            'PDF_Builder_Variable_Mapper.php',
            'PDF_Builder_WooCommerce_Integration.php'
        );

        foreach ($managers as $manager) {
            $manager_path = PDF_BUILDER_PLUGIN_DIR . 'src/Managers/' . $manager;
            if (file_exists($manager_path)) {
                require_once $manager_path;
            }
        }

        // Charger les classes Core essentielles
        $core_classes = array(
            'PDF_Builder_Security_Validator.php'
        );

        foreach ($core_classes as $core_class) {
            $core_path = PDF_BUILDER_PLUGIN_DIR . 'src/Core/' . $core_class;
            if (file_exists($core_path)) {
                require_once $core_path;
            }
        }

        // Charger la classe d'administration
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        }

        // Charger le contrôleur PDF
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php';
        }

        // Charger le handler AJAX d'image de prévisualisation
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php';
        }
    }

    /**
     * Obtenir l'instance unique de la classe (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser le plugin (appelé depuis bootstrap)
     */
    public function init()
    {
        // Enregistrer le menu admin au hook admin_menu (pas pendant plugins_loaded)
        // Cela évite que les traductions soient appelées trop tôt
        add_action('admin_menu', [$this, 'register_admin_menu']);

        // Initialiser les dossiers au hook WordPress 'init' (plus tardif)
        add_action('init', [$this, 'initialize_directories']);
    }

    /**
     * Initialiser les dossiers nécessaires pour le plugin
     */
    public function initialize_directories()
    {
        // Obtenir le répertoire d'upload WordPress
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];

        // Dossiers à créer
        $directories = array(
            $base_dir . '/pdf-builder',
            $base_dir . '/pdf-builder/templates',
            $base_dir . '/pdf-builder/previews',
            $base_dir . '/pdf-builder/orders',
            $base_dir . '/pdf-builder/temp',
            $base_dir . '/pdf-builder/cache',
            $base_dir . '/pdf-builder/logs'
        );

        // Créer chaque dossier s'il n'existe pas
        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                wp_mkdir_p($directory);

                // Créer un fichier .htaccess pour sécuriser les dossiers
                $htaccess_path = $directory . '/.htaccess';
                if (!file_exists($htaccess_path)) {
                    $htaccess_content = "# Sécuriser l'accès aux fichiers PDF Builder\n<FilesMatch \"\\.(php|php3|php4|php5|phtml)$\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n";
                    file_put_contents($htaccess_path, $htaccess_content);
                }

                // Créer un fichier index.php vide pour éviter le listage
                $index_path = $directory . '/index.php';
                if (!file_exists($index_path)) {
                    file_put_contents($index_path, '<?php // Silence is golden');
                }
            }
        }
    }

    /**
     * Enregistrer le menu admin - appelé uniquement sur admin_menu (DÉSACTIVÉ - géré par PDF_Builder_Admin)
     */
    public function register_admin_menu()
    {
        // Menus désactivés - gérés par PDF_Builder_Admin qui a le vrai contenu
        return;

        if (self::$menu_added) {
            return;
        }
        self::$menu_added = true;

        try {
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
                'pdf-builder-pro',
                __('Settings', 'pdf-builder-pro'),
                __('Settings', 'pdf-builder-pro'),
                'manage_options',
                'pdf-builder-settings',
                array($this, 'settings_page')
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * Enregistrer les paramètres
     */
    public function register_settings()
    {
        \register_setting('pdf_builder_options', 'pdf_builder_settings');

        \add_settings_section(
            'pdf_builder_main',
            __('Main Settings', 'pdf-builder-pro'),
            array($this, 'settings_section_callback'),
            'pdf_builder_settings'
        );
    }

    /**
     * Page d'administration principale
     */
    public function admin_page()
    {
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
    public function templates_page()
    {
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
    public function template_editor_page()
    {
        include plugin_dir_path(__FILE__) . '../template-editor.php';
    }

    /**
     * Page des paramètres
     */
    public function settings_page()
    {
        ?>
        <div class="wrap">
            <h1><?php _e('PDF Builder Settings', 'pdf-builder-pro'); ?></h1>
            <form method="post" action="options.php">
                <?php
                \settings_fields('pdf_builder_options');
                \do_settings_sections('pdf_builder_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Callback de la section des paramètres
     */
    public function settings_section_callback()
    {
        echo '<p>' . __('Configure PDF Builder Pro settings.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Activation du plugin
     */
    public function activate()
    {
        // Créer les tables de base de données si nécessaire
        $this->create_database_tables();

        // Définir les options par défaut
        add_option('pdf_builder_version', $this->version);

        if (function_exists('pdf_builder_log')) {
            call_user_func('pdf_builder_log', 'PDF Builder Pro activated', 1);
        }
    }

    /**
     * Désactivation du plugin
     */
    public function deactivate()
    {
        // Nettoyer si nécessaire
        if (function_exists('pdf_builder_log')) {
            call_user_func('pdf_builder_log', 'PDF Builder Pro deactivated', 1);
        }
    }

    /**
     * Créer les tables de base de données
     */
    private function create_database_tables()
    {
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

        // Table pour les canvas personnalisés par commande
        $table_order_canvases = $wpdb->prefix . 'pdf_builder_order_canvases';
        $sql_order_canvases = "CREATE TABLE $table_order_canvases (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            canvas_data longtext NOT NULL,
            template_id mediumint(9) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_id (order_id),
            KEY template_id (template_id)
        ) $charset_collate;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        \dbDelta($sql_templates);
        \dbDelta($sql_order_canvases);
    }

    /**
     * Notice pour version PHP trop ancienne
     */
    public function php_version_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php _e('PDF Builder Pro requires PHP 7.4 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Notice pour version WordPress trop ancienne
     */
    public function wp_version_notice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php _e('PDF Builder Pro requires WordPress 5.0 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Initialisation de l'interface d'administration
     */
    private function init_admin()
    {
        // Vérifier si WordPress est disponible
        if (!function_exists('add_action')) {
            return;
        }

        // Inclure et instancier la classe d'administration
        if (!class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
            include_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        }

        if (class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
            $this->admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance($this);
        }
    }

    /**
     * Obtenir la version du plugin
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Génère un PDF pour une commande WooCommerce (délégation à l'admin)
     *
     * @param  int $order_id    ID de la commande
     * @param  int $template_id ID du template (0 pour auto-détection)
     * @return mixed URL du PDF généré ou erreur
     */
    public function generate_order_pdf($order_id, $template_id = 0)
    {
        if ($this->admin && method_exists($this->admin, 'generate_order_pdf')) {
            return $this->admin->generate_order_pdf($order_id, $template_id);
        } else {
            return new \WP_Error('admin_not_initialized', 'Interface d\'administration non initialisée');
        }
    }

    /**
     * Action AJAX pour sauvegarder les paramètres
     */
    public function ajax_save_settings()
    {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit;
        }

        // Traiter les paramètres comme dans le code original
        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'email_notifications_enabled' => isset($_POST['email_notifications_enabled']),
            'notification_events' => isset($_POST['notification_events']) ? array_map(
                function ($event) {
                    return sanitize_text_field($event);
                },
                $_POST['notification_events']
            ) : [],
            // Paramètres Canvas - anciens
            'canvas_element_borders_enabled' => isset($_POST['canvas_element_borders_enabled']),
            'canvas_border_width' => isset($_POST['canvas_border_width']) ? floatval($_POST['canvas_border_width']) : 1,
            'canvas_border_color' => isset($_POST['canvas_border_color']) ? sanitize_text_field($_POST['canvas_border_color']) : '#007cba',
            'canvas_border_spacing' => isset($_POST['canvas_border_spacing']) ? intval($_POST['canvas_border_spacing']) : 2,
            'canvas_resize_handles_enabled' => isset($_POST['canvas_resize_handles_enabled']),
            'canvas_handle_size' => isset($_POST['canvas_handle_size']) ? intval($_POST['canvas_handle_size']) : 8,
            'canvas_handle_color' => isset($_POST['canvas_handle_color']) ? sanitize_text_field($_POST['canvas_handle_color']) : '#007cba',
            'canvas_handle_hover_color' => isset($_POST['canvas_handle_hover_color']) ? sanitize_text_field($_POST['canvas_handle_hover_color']) : '#005a87',
            // Paramètres Canvas - nouveaux sous-onglets
            'default_canvas_width' => isset($_POST['default_canvas_width']) ? intval($_POST['default_canvas_width']) : 794,
            'default_canvas_height' => isset($_POST['default_canvas_height']) ? intval($_POST['default_canvas_height']) : 1123,
            'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? sanitize_text_field($_POST['default_canvas_unit']) : 'px',
            'canvas_background_color' => isset($_POST['canvas_background_color']) ? sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'container_background_color' => isset($_POST['container_background_color']) ? sanitize_text_field($_POST['container_background_color']) : '#f8f9fa',
            'container_show_transparency' => isset($_POST['container_show_transparency']),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => isset($_POST['margin_top']) ? intval($_POST['margin_top']) : 28,
            'margin_right' => isset($_POST['margin_right']) ? intval($_POST['margin_right']) : 28,
            'margin_bottom' => isset($_POST['margin_bottom']) ? intval($_POST['margin_bottom']) : 28,
            'margin_left' => isset($_POST['margin_left']) ? intval($_POST['margin_left']) : 28,
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => isset($_POST['grid_size']) ? intval($_POST['grid_size']) : 10,
            'grid_color' => isset($_POST['grid_color']) ? sanitize_text_field($_POST['grid_color']) : '#e0e0e0',
            'grid_opacity' => isset($_POST['grid_opacity']) ? intval($_POST['grid_opacity']) : 30,
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_to_margins' => isset($_POST['snap_to_margins']),
            'snap_tolerance' => isset($_POST['snap_tolerance']) ? intval($_POST['snap_tolerance']) : 5,
            'show_guides' => isset($_POST['show_guides']),
            'lock_guides' => isset($_POST['lock_guides']),
            'default_zoom' => isset($_POST['default_zoom']) ? sanitize_text_field($_POST['default_zoom']) : '100',
            'zoom_step' => isset($_POST['zoom_step']) ? intval($_POST['zoom_step']) : 25,
            'min_zoom' => isset($_POST['min_zoom']) ? intval($_POST['min_zoom']) : 10,
            'max_zoom' => isset($_POST['max_zoom']) ? intval($_POST['max_zoom']) : 500,
            'pan_with_mouse' => isset($_POST['pan_with_mouse']),
            'smooth_zoom' => isset($_POST['smooth_zoom']),
            'show_zoom_indicator' => isset($_POST['show_zoom_indicator']),
            'zoom_with_wheel' => isset($_POST['zoom_with_wheel']),
            'zoom_to_selection' => isset($_POST['zoom_to_selection']),
            'show_resize_handles' => isset($_POST['show_resize_handles']),
            'handle_size' => isset($_POST['handle_size']) ? intval($_POST['handle_size']) : 8,
            'handle_color' => isset($_POST['handle_color']) ? sanitize_text_field($_POST['handle_color']) : '#007cba',
            'enable_rotation' => isset($_POST['enable_rotation']),
            'rotation_step' => isset($_POST['rotation_step']) ? intval($_POST['rotation_step']) : 15,
            'rotation_snap' => isset($_POST['rotation_snap']),
            'multi_select' => isset($_POST['multi_select']),
            'select_all_shortcut' => isset($_POST['select_all_shortcut']),
            'show_selection_bounds' => isset($_POST['show_selection_bounds']),
            'copy_paste_enabled' => isset($_POST['copy_paste_enabled']),
            'duplicate_on_drag' => isset($_POST['duplicate_on_drag']),
            'export_quality' => isset($_POST['export_quality']) ? sanitize_text_field($_POST['export_quality']) : 'print',
            'export_format' => isset($_POST['export_format']) ? sanitize_text_field($_POST['export_format']) : 'pdf',
            'compress_images' => isset($_POST['compress_images']),
            'image_quality' => isset($_POST['image_quality']) ? intval($_POST['image_quality']) : 85,
            'max_image_size' => isset($_POST['max_image_size']) ? intval($_POST['max_image_size']) : 2048,
            'include_metadata' => isset($_POST['include_metadata']),
            'pdf_author' => isset($_POST['pdf_author']) ? sanitize_text_field($_POST['pdf_author']) : get_bloginfo('name'),
            'pdf_subject' => isset($_POST['pdf_subject']) ? sanitize_text_field($_POST['pdf_subject']) : '',
            'auto_crop' => isset($_POST['auto_crop']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => isset($_POST['max_fps']) ? intval($_POST['max_fps']) : 60,
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => isset($_POST['auto_save_interval']) ? intval($_POST['auto_save_interval']) : 30,
            'auto_save_versions' => isset($_POST['auto_save_versions']) ? intval($_POST['auto_save_versions']) : 10,
            'undo_levels' => isset($_POST['undo_levels']) ? intval($_POST['undo_levels']) : 50,
            'redo_levels' => isset($_POST['redo_levels']) ? intval($_POST['redo_levels']) : 50,
            'enable_keyboard_shortcuts' => isset($_POST['enable_keyboard_shortcuts']),
            'debug_mode' => isset($_POST['debug_mode']),
            'show_fps' => isset($_POST['show_fps']),
            'email_notifications' => isset($_POST['email_notifications']),
            'admin_email' => sanitize_email($_POST['admin_email'] ?? ''),
            'notification_log_level' => sanitize_text_field($_POST['notification_log_level'] ?? 'error')
        ];

        // Sauvegarder les paramètres
        update_option('pdf_builder_settings', $settings);

        // Traiter les informations entreprise spécifiques
        if (isset($_POST['company_vat'])) {
            update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat']));
        }
        if (isset($_POST['company_rcs'])) {
            update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs']));
        }
        if (isset($_POST['company_siret'])) {
            update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret']));
        }
        if (isset($_POST['company_phone'])) {
            update_option('pdf_builder_company_phone', sanitize_text_field($_POST['company_phone']));
        }

        // Traiter les rôles autorisés
        if (isset($_POST['pdf_builder_allowed_roles'])) {
            $allowed_roles = array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']);
            if (empty($allowed_roles)) {
                $allowed_roles = ['administrator'];
            }
            update_option('pdf_builder_allowed_roles', $allowed_roles);
        }

        // Traiter les mappings template par statut de commande
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            $template_mappings = [];
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                $template_id = intval($template_id);
                if ($template_id > 0) {
                    $template_mappings[sanitize_text_field($status)] = $template_id;
                }
            }
            update_option('pdf_builder_order_status_templates', $template_mappings);
        }

        // Retourner le succès
        wp_send_json_success(
            array(
            'message' => __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
            'spacing' => $settings['canvas_border_spacing']
            )
        );
        exit;
    }

    /**
     * Action AJAX pour récupérer les paramètres
     */
    public function ajax_get_settings()
    {
        // Vérifier le nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'pdf_builder_settings')) {
            wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit;
        }

        // Récupérer les paramètres depuis la base de données
        $settings = get_option('pdf_builder_settings', []);

        // Retourner les paramètres
        wp_send_json_success($settings);
        exit;
    }



    /**
     * Phase 3.4.3 - Optimisation des balises script
     * Ajoute des attributs de performance aux scripts
     *
     * @param string $tag Balise script générée
     * @param string $handle Handle du script
     * @param string $src URL du script
     * @return string Balise script optimisée
     */
    public function optimize_script_tags($tag, $handle, $src)
    {
        // Scripts critiques qui ne doivent pas être différés
        $critical_scripts = array(
            'jquery',
            'jquery-core',
            'pdf-builder-script-loader'
        );

        // Scripts à différer (chargement asynchrone)
        $defer_scripts = array(
            'pdf-builder-lazy-loader'
        );

        // Scripts à charger de manière asynchrone
        $async_scripts = array(
            'pdf-builder-admin-core' // Le script principal peut être asynchrone
        );

        if (in_array($handle, $critical_scripts)) {
            // Scripts critiques : ajouter preload et integrity si disponible
            $preload_link = '<link rel="preload" href="' . esc_url($src) . '" as="script">';
            $tag = $preload_link . $tag;
        } elseif (in_array($handle, $defer_scripts)) {
            // Scripts différés : ajouter defer
            $tag = str_replace('<script ', '<script defer ', $tag);
        } elseif (in_array($handle, $async_scripts)) {
            // Scripts asynchrones : ajouter async
            $tag = str_replace('<script ', '<script async ', $tag);
        }

        return $tag;
    }

    /**
     * Phase 3.4.3 - Optimisation des balises style
     * Ajoute des attributs de performance aux styles
     *
     * @param string $tag Balise style générée
     * @param string $handle Handle du style
     * @param string $href URL du style
     * @return string Balise style optimisée
     */
    public function optimize_style_tags($tag, $handle, $href)
    {
        // Styles critiques à précharger
        $critical_styles = array(
            'pdf-builder-admin-core'
        );

        if (in_array($handle, $critical_styles)) {
            // Ajouter preload pour les styles critiques
            $preload_link = '<link rel="preload" href="' . esc_url($href) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
            $noscript_fallback = '<noscript>' . $tag . '</noscript>';

            // Créer un lien preload qui se transforme en stylesheet
            $optimized_tag = '<link rel="preload" href="' . esc_url($href) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . $noscript_fallback;

            return $optimized_tag;
        }

        return $tag;
    }

    /**
     * Page de l'éditeur React
     */
    public function render_react_editor_page()
    {
        // Récupérer le template_id depuis l'URL si présent
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : null;

        // Vérifier si c'est un template builtin
        $builtin_template = isset($_GET['builtin_template']) ? sanitize_text_field($_GET['builtin_template']) : null;
        $transient_key = isset($_GET['transient_key']) ? sanitize_text_field($_GET['transient_key']) : null;

        $template_data = null;
        if ($builtin_template && $transient_key) {
            $template_data = get_transient($transient_key);
            if ($template_data) {
                // Marquer comme template builtin pour l'interface
                $template_data['is_builtin'] = true;
                $template_data['builtin_id'] = $builtin_template;
            }
        }

        ?>
        <div class="wrap">
            <h1><?php _e('PDF Builder - React Editor', 'pdf-builder-pro'); ?></h1>
            <?php if ($builtin_template): ?>
                <p><?php printf(__('Editing builtin template: %s', 'pdf-builder-pro'), $builtin_template); ?></p>
            <?php elseif ($template_id): ?>
                <p><?php printf(__('Editing template #%d', 'pdf-builder-pro'), $template_id); ?></p>
            <?php else: ?>
                <p><?php _e('Create a new PDF template', 'pdf-builder-pro'); ?></p>
            <?php endif; ?>

            <div id="pdf-builder-react-editor-container">
                <!-- Le contenu React sera chargé ici -->
                <p><?php _e('Loading PDF Builder React Editor...', 'pdf-builder-pro'); ?></p>
            </div>
        </div>

        <script type="text/javascript">
            // Passer les données à React
            window.pdfBuilderData = {
                templateId: <?php echo $template_id ? $template_id : 'null'; ?>,
                builtinTemplate: <?php echo $builtin_template ? json_encode($builtin_template) : 'null'; ?>,
                templateData: <?php echo $template_data ? json_encode($template_data) : 'null'; ?>,
                isEditing: <?php echo ($template_id || $template_data) ? 'true' : 'false'; ?>,
                isBuiltin: <?php echo $builtin_template ? 'true' : 'false'; ?>,
                ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo wp_create_nonce('pdf_builder_nonce'); ?>'
            };

            // Debug: Afficher les données passées à React
            console.log('🔍 [PDF BUILDER] Données passées à React:', window.pdfBuilderData);
            if (window.pdfBuilderData.templateData) {
                console.log('📊 [PDF BUILDER] Template data elements:', window.pdfBuilderData.templateData.elements);
            }

            // Pour les templates builtin, injecter les données directement dans l'éditeur React
            <?php if ($template_data && $builtin_template): ?>
            window.pdfBuilderBuiltinData = <?php echo json_encode($template_data); ?>;
            console.log('🏗️ [PDF BUILDER] Données builtin injectées:', window.pdfBuilderBuiltinData);

            // Injecter les données dans l'éditeur React après son chargement
            let injectionAttempts = 0;
            const maxAttempts = 50; // 5 secondes max

            function tryInjectBuiltinData() {
                injectionAttempts++;
                console.log(`� [PDF BUILDER] Tentative d'injection ${injectionAttempts}/${maxAttempts}`);

                // Essayer différentes méthodes d'accès à l'éditeur
                const possibleEditors = [
                    window.pdfBuilderEditor,
                    window.pdfCanvasEditor,
                    window.pdfEditorPreview?.canvasEditor,
                    // Chercher dans le DOM
                    document.querySelector('[data-react-pdf-builder]')?.__reactInternalInstance,
                ];

                for (const editor of possibleEditors) {
                    if (editor && typeof editor.dispatch === 'function') {
                        console.log('🚀 [PDF BUILDER] Éditeur trouvé, injection des données builtin...');
                        try {
                            editor.dispatch({
                                type: 'LOAD_TEMPLATE',
                                payload: {
                                    id: 'builtin_' + window.pdfBuilderData.builtinTemplate,
                                    name: window.pdfBuilderBuiltinData.name || 'Template Builtin',
                                    elements: window.pdfBuilderBuiltinData.elements || [],
                                    canvas: {
                                        width: window.pdfBuilderBuiltinData.canvasWidth || 794,
                                        height: window.pdfBuilderBuiltinData.canvasHeight || 1123
                                    }
                                }
                            });
                            console.log('✅ [PDF BUILDER] Données builtin injectées avec succès');
                            return true;
                        } catch (error) {
                            console.error('❌ [PDF BUILDER] Erreur lors du dispatch:', error);
                        }
                    }
                }

                // Si on n'a pas trouvé d'éditeur, essayer de déclencher un événement personnalisé
                if (window.dispatchEvent) {
                    try {
                        const event = new CustomEvent('pdfBuilderLoadBuiltinTemplate', {
                            detail: window.pdfBuilderBuiltinData
                        });
                        window.dispatchEvent(event);
                        console.log('📡 [PDF BUILDER] Événement personnalisé envoyé');
                    } catch (e) {
                        console.error('❌ [PDF BUILDER] Erreur envoi événement:', e);
                    }
                }

                // Réessayer si on n'a pas dépassé le nombre max de tentatives
                if (injectionAttempts < maxAttempts) {
                    setTimeout(tryInjectBuiltinData, 100);
                } else {
                    console.error('❌ [PDF BUILDER] Échec de l\'injection après', maxAttempts, 'tentatives');
                }
            }

            // Démarrer l'injection après un court délai
            setTimeout(tryInjectBuiltinData, 500);
            <?php endif; ?>
        </script>
        <?php
    }
}

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}


