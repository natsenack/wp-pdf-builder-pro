<?php

namespace PDF_Builder\Core;

// Déclarations des fonctions WordPress pour l'IDE
if (!function_exists('did_action')) {
    function did_action($tag) { return 0; }
}
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($path) { return mkdir($path, 0755, true); }
}
if (!function_exists('add_menu_page')) {
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function = '', $icon_url = '', $position = null) { return ''; }
}
if (!function_exists('add_submenu_page')) {
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '') { return ''; }
}
if (!function_exists('add_settings_section')) {
    function add_settings_section($id, $title, $callback, $page) { return true; }
}
if (!function_exists('_e')) {
    function _e($text, $domain = 'default') { echo $text; } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
if (!function_exists('wp_die')) {
    function wp_die($message = '', $title = '', $args = []) { die($message); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('date_i18n')) {
    function date_i18n($format, $timestamp = false) { return date($format, $timestamp ?: time()); }
}
if (!function_exists('get_option')) {
    function get_option($option, $default = false) { return $default; }
}
if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) { return true; }
}
if (!function_exists('wp_script_add_data')) {
    function wp_script_add_data($handle, $key, $value) { return true; }
}
if (!function_exists('add_option')) {
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') { return true; }
}
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}
if (!function_exists('dbDelta')) {
    function dbDelta($queries = '', $execute = true) { return []; }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') { return 'PDF Builder Pro'; }
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($email) { return filter_var($email, FILTER_SANITIZE_EMAIL); }
}
if (!function_exists('__')) {
    function __($text, $domain = 'default') { return $text; }
}
if (!function_exists('printf')) {
    function printf($format, ...$args) { echo sprintf($format, ...$args); } // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * PDF Builder Core
 * Classe principale du plugin PDF Builder Pro
 *
 * @method static void pdf_builder_log(string $message, int $level = 2, array $context = [])
 */

class PdfBuilderCore
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
            // 'PDF_Builder_Drag_Drop_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_Feature_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_Feature_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_License_Manager.php', // Chargé dans bootstrap.php
            // 'PDF_Builder_PDF_Generator.php', // PDF generation system removed
            'PDF_Builder_Resize_Manager.php',
            'PDF_Builder_Settings_Manager.php',
            'PDF_Builder_Template_Manager.php'
        );

        // Managers dépendants de WooCommerce - chargés seulement si WooCommerce est disponible
        $woocommerce_managers = array(
            'PDF_Builder_Status_Manager.php',
            'PDF_Builder_Variable_Mapper.php',
            'PDF_Builder_WooCommerce_Integration.php'
        );

        foreach ($managers as $manager) {
            $manager_path = PDF_BUILDER_PLUGIN_DIR . 'src/Managers/' . $manager;
            if (file_exists($manager_path)) {
                require_once $manager_path;
            }
        }

        // Charger les managers WooCommerce seulement si WooCommerce est actif
        if (function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active()) {
            foreach ($woocommerce_managers as $manager) {
                $manager_path = PDF_BUILDER_PLUGIN_DIR . 'src/Managers/' . $manager;
                if (file_exists($manager_path)) {
                    require_once $manager_path;
                }
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
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php') && !class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        }

        // PDF generation system removed
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
        // Menu admin is registered in PDF_Builder_Admin.php
        // Initialiser les dossiers au hook WordPress 'init' (plus tardif)
        add_action('init', [$this, 'initialize_directories']);

        // Notification system removed - no initialization required
    }

    /**
     * Initialiser les dossiers nécessaires pour le plugin
     */
    public function initializeDirectories()
    {
        try {
            // Obtenir le répertoire d'upload WordPress
            $upload_dir = \wp_upload_dir();
            if (isset($upload_dir['error']) && $upload_dir['error']) {
                
                return;
            }

            $base_dir = $upload_dir['basedir'];
            if (empty($base_dir) || !is_writable($base_dir)) {
                
                return;
            }

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
        } catch (\Exception $e) {
            
        }
    }

    /**
     * Alias pour initializeDirectories() - compatibilité
     */
    public function initialize_directories()
    {
        return $this->initializeDirectories();
    }

    /**
     * Enregistrer les paramètres
     */
    public function registerSettings()
    {
        // REMOVED: register_setting déplacé vers SettingsManager.php pour éviter les conflits
        // \register_setting('pdf_builder_options', 'pdf_builder_settings');

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
    public function adminPage()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
            <p><?php esc_html_e('Welcome to PDF Builder Pro - Professional PDF creation made easy.', 'pdf-builder-pro'); ?></p>

            <div class="pdfb-pdf-builder-dashboard">
                <div class="pdfb-pdf-builder-card">
                    <h3><?php esc_html_e('Quick Start', 'pdf-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Create your first PDF template in minutes.', 'pdf-builder-pro'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-templates')); ?>" class="button button-primary">
                        <?php esc_html_e('Create Template', 'pdf-builder-pro'); ?>
                    </a>
                </div>

                <div class="pdfb-pdf-builder-card">
                    <h3><?php esc_html_e('Documentation', 'pdf-builder-pro'); ?></h3>
                    <p><?php esc_html_e('Learn how to use all features.', 'pdf-builder-pro'); ?></p>
                    <a href="#" class="button"><?php esc_html_e('View Docs', 'pdf-builder-pro'); ?></a>
                </div>
            </div>
        </div>


        <?php
    }

    /**
     * Page des templates
     */
    public function templatesPage()
    {
        // Vérifier les permissions - utiliser manage_options comme capacité principale
        if (!\current_user_can('manage_options')) {
            wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Inclure la page templates comme dans PDF_Builder_Admin
        $templates_page_path = \plugin_dir_path(__FILE__) . '../resources/templates/admin/templates-page.php';
        if (file_exists($templates_page_path)) {
            include $templates_page_path;
        } else {
            // Fallback basique si le fichier n'existe pas
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('PDF Templates', 'pdf-builder-pro'); ?></h1>
                <p><?php esc_html_e('Manage your PDF templates.', 'pdf-builder-pro'); ?></p>

                <div id="pdf-builder-templates-container">
                    <!-- Le contenu React sera chargé ici -->
                    <p><?php esc_html_e('Loading PDF Builder...', 'pdf-builder-pro'); ?></p>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Page de l'éditeur de template
     */
    public function templateEditorPage()
    {
        include \plugin_dir_path(__FILE__) . '../template-editor.php';
    }

    /**
     * Page de l'éditeur React
     */
    public function render_react_editor_page()
    {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('PDF Builder React Editor', 'pdf-builder-pro'); ?></h1>
            <p><?php esc_html_e('Advanced PDF template editor with React interface.', 'pdf-builder-pro'); ?></p>

            <div id="pdf-builder-react-editor-container">
                <!-- Le contenu React sera chargé ici -->
                <p><?php esc_html_e('Loading React Editor...', 'pdf-builder-pro'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Page des documents récents
     */
    public function render_documents_page()
    {
        global $wpdb;

        // Récupérer les logs récents depuis la base de données
        $table_name = $wpdb->prefix . 'pdf_builder_logs';
        $recent_logs = $wpdb->get_results(
            "SELECT * FROM {$table_name}
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             ORDER BY created_at DESC
             LIMIT 50"
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('📄 Documents Récents', 'pdf-builder-pro'); ?></h1>
            <p><?php esc_html_e('Consultez les logs de génération PDF récents.', 'pdf-builder-pro'); ?></p>

            <?php if (empty($recent_logs)): ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('Aucun log récent trouvé. Commencez par créer et générer des PDF avec vos templates.', 'pdf-builder-pro'); ?></p>
                </div>
            <?php else: ?>
                <div class="pdfb-recent-documents-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Date', 'pdf-builder-pro'); ?></th>
                                <th><?php esc_html_e('Message', 'pdf-builder-pro'); ?></th>
                                <th><?php esc_html_e('Actions', 'pdf-builder-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at))); ?></td>
                                    <td><?php echo esc_html($log->log_message); ?></td>
                                    <td>
                                        <button class="button button-small"
                                                onclick="alert('<?php echo esc_js(__('Fonctionnalité de téléchargement à implémenter', 'pdf-builder-pro')); ?>')">
                                            📄 <?php esc_html_e('Détails', 'pdf-builder-pro'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>


            <?php endif; ?>

            <div class="recent-documents-info" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #007cba;">
                <h3><?php esc_html_e('ℹ️ Informations', 'pdf-builder-pro'); ?></h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><?php esc_html_e('Cette page affiche les logs de génération PDF des 30 derniers jours.', 'pdf-builder-pro'); ?></li>
                    <li><?php esc_html_e('La fonctionnalité complète de téléchargement sera bientôt disponible.', 'pdf-builder-pro'); ?></li>
                    <li><?php esc_html_e('Pour consulter vos PDF générés, vérifiez le dossier de téléchargement de votre navigateur.', 'pdf-builder-pro'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Page des paramètres
     */
    public function settingsPage()
    {
        // Inclure le template principal des paramètres avec onglets
        $template_path = PDF_BUILDER_PLUGIN_DIR . 'resources/templates/admin/settings-page.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback si le template n'existe pas
            echo '<div class="wrap">';
            echo '<h1>' . esc_html__('Paramètres PDF Builder Pro', 'pdf-builder-pro') . '</h1>';
            echo '<p>' . esc_html__('Template de paramètres introuvable.', 'pdf-builder-pro') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Charge seulement l'API globale de l'éditeur React (sans l'interface complète)
     * Utile pour les pages qui ont besoin de communiquer avec l'éditeur
     */
    private function enqueueReactGlobalAPI()
    {
        // Charger React et ReactDOM en premier
        wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.production.min.js', [], '18.0.0', true);
        wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', ['react'], '18.0.0', true);

        // Charger seulement l'API globale de PDF Builder React - seulement si le fichier existe
        $react_script_path = PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-react-wrapper.min.js';
        if (file_exists($react_script_path)) {
            $react_script_url = PDF_BUILDER_PLUGIN_URL . 'assets/js/pdf-builder-react-wrapper.min.js';
            $cache_bust = time();
            $version_param = $this->version . '-' . $cache_bust;
            wp_enqueue_script('pdf-builder-react-api-only', $react_script_url, ['react', 'react-dom'], $version_param, true);
            wp_script_add_data('pdf-builder-react-api-only', 'type', 'text/javascript');
        }
    }

    /**
     * Activation du plugin
     */
    public function activate()
    {
        // Créer la table personnalisée wp_pdf_builder_settings
        if (!class_exists('PDF_Builder\Database\Settings_Table_Manager')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'src/Database/Settings_Table_Manager.php';
        }
        \PDF_Builder\Database\Settings_Table_Manager::create_table();

        // Créer les tables de base de données si nécessaire
        $this->createDatabaseTables();

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
    private function createDatabaseTables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table pour les templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $sql_templates = "CREATE TABLE $table_templates (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            template_data longtext NOT NULL,
            user_id bigint(20) unsigned NOT NULL DEFAULT 0,
            is_default tinyint(1) NOT NULL DEFAULT 0,
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
        dbDelta($sql_templates);
        dbDelta($sql_order_canvases);
    }

    /**
     * Notice pour version PHP trop ancienne
     */
    public function phpVersionNotice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('PDF Builder Pro requires PHP 7.4 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Notice pour version WordPress trop ancienne
     */
    public function wpVersionNotice()
    {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('PDF Builder Pro requires WordPress 5.0 or higher.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php
    }

    /**
     * Initialisation de l'interface d'administration
     */
    private function initAdmin()
    {
        // Vérifier si WordPress est disponible
        if (!function_exists('add_action')) {
            return;
        }

        // Inclure et instancier la classe d'administration
        if (!class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
            include_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        }

        if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
            // Utiliser réflexion pour éviter les erreurs de compilation
            try {
                $reflection = new \ReflectionClass('PDF_Builder\Admin\PdfBuilderAdminNew');
                if ($reflection->hasMethod('getInstance')) {
                    $method = $reflection->getMethod('getInstance');
                    $this->admin = $method->invoke(null, $this);
                }
            } catch (\Exception $e) {
                // Classe existe mais méthode getInstance n'est pas disponible
                $this->admin = null;
            }
        }
    }

    /**
     * Obtenir la version du plugin
     */
    public function getVersion()
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
    public function generateOrderPdf($order_id, $template_id = 0)
    {
        if ($this->admin && method_exists($this->admin, 'generate_order_pdf')) {
            return $this->admin->generateOrderPdf($order_id, $template_id);
        } else {
            return new \WP_Error('admin_not_initialized', 'Interface d\'administration non initialisée');
        }
    }

    /**
     * Action AJAX pour sauvegarder les paramètres
     */
    public function ajaxSaveSettings()
    {
        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !\pdf_builder_verify_nonce($_POST['nonce'], 'pdf_builder_settings')) {
            \wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit;
        }

        // Traiter les paramètres comme dans le code original
        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => \intval($_POST['cache_ttl'] ?? 3600),
            'max_execution_time' => \intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => \sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            'pdf_quality' => \sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => \sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => \sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'log_level' => \sanitize_text_field($_POST['log_level'] ?? 'info'),
            'max_template_size' => \intval($_POST['max_template_size'] ?? 52428800),
            // Notification settings removed
            // Paramètres Canvas - anciens
            'canvas_element_borders_enabled' => isset($_POST['canvas_element_borders_enabled']),
            'canvas_border_width' => isset($_POST['canvas_border_width']) ? \floatval($_POST['canvas_border_width']) : 1,
            'canvas_border_color' => isset($_POST['canvas_border_color']) ? \sanitize_text_field($_POST['canvas_border_color']) : '#007cba',
            'canvas_border_spacing' => isset($_POST['canvas_border_spacing']) ? \intval($_POST['canvas_border_spacing']) : 2,
            'canvas_resize_handles_enabled' => isset($_POST['canvas_resize_handles_enabled']),
            'canvas_handle_size' => isset($_POST['canvas_handle_size']) ? \intval($_POST['canvas_handle_size']) : 8,
            'canvas_handle_color' => isset($_POST['canvas_handle_color']) ? \sanitize_text_field($_POST['canvas_handle_color']) : '#007cba',
            'canvas_handle_hover_color' => isset($_POST['canvas_handle_hover_color']) ? \sanitize_text_field($_POST['canvas_handle_hover_color']) : '#005a87',
            // Paramètres Canvas - nouveaux sous-onglets
            'default_canvas_width' => isset($_POST['default_canvas_width']) ? \intval($_POST['default_canvas_width']) : 794,
            'default_canvas_height' => isset($_POST['default_canvas_height']) ? \intval($_POST['default_canvas_height']) : 1123,
            'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? \sanitize_text_field($_POST['default_canvas_unit']) : 'px',
            'canvas_background_color' => isset($_POST['canvas_background_color']) ? \sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'container_background_color' => isset($_POST['container_background_color']) ? \sanitize_text_field($_POST['container_background_color']) : '#f8f9fa',
            'container_show_transparency' => isset($_POST['container_show_transparency']),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => isset($_POST['margin_top']) ? \intval($_POST['margin_top']) : 28,
            'margin_right' => isset($_POST['margin_right']) ? \intval($_POST['margin_right']) : 28,
            'margin_bottom' => isset($_POST['margin_bottom']) ? \intval($_POST['margin_bottom']) : 28,
            'margin_left' => isset($_POST['margin_left']) ? \intval($_POST['margin_left']) : 28,
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => isset($_POST['grid_size']) ? \intval($_POST['grid_size']) : 10,
            'grid_color' => isset($_POST['grid_color']) ? \sanitize_text_field($_POST['grid_color']) : '#e0e0e0',
            'grid_opacity' => isset($_POST['grid_opacity']) ? \intval($_POST['grid_opacity']) : 30,
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_to_margins' => isset($_POST['snap_to_margins']),
            'snap_tolerance' => isset($_POST['snap_tolerance']) ? \intval($_POST['snap_tolerance']) : 5,
            'show_guides' => isset($_POST['show_guides']),
            'lock_guides' => isset($_POST['lock_guides']),
            'default_zoom' => isset($_POST['default_zoom']) ? \sanitize_text_field($_POST['default_zoom']) : '100',
            'zoom_step' => isset($_POST['zoom_step']) ? \intval($_POST['zoom_step']) : 25,
            'min_zoom' => isset($_POST['min_zoom']) ? \intval($_POST['min_zoom']) : 10,
            'max_zoom' => isset($_POST['max_zoom']) ? \intval($_POST['max_zoom']) : 500,
            'pan_with_mouse' => isset($_POST['pan_with_mouse']),
            'smooth_zoom' => isset($_POST['smooth_zoom']),
            'show_zoom_indicator' => isset($_POST['show_zoom_indicator']),
            'zoom_with_wheel' => isset($_POST['zoom_with_wheel']),
            'zoom_to_selection' => isset($_POST['zoom_to_selection']),
            'show_resize_handles' => isset($_POST['show_resize_handles']),
            'handle_size' => isset($_POST['handle_size']) ? \intval($_POST['handle_size']) : 8,
            'handle_color' => isset($_POST['handle_color']) ? \sanitize_text_field($_POST['handle_color']) : '#007cba',
            'enable_rotation' => isset($_POST['enable_rotation']),
            'rotation_step' => isset($_POST['rotation_step']) ? \intval($_POST['rotation_step']) : 15,
            'rotation_snap' => isset($_POST['rotation_snap']),
            'multi_select' => isset($_POST['multi_select']),
            'select_all_shortcut' => isset($_POST['select_all_shortcut']),
            'show_selection_bounds' => isset($_POST['show_selection_bounds']),
            'copy_paste_enabled' => isset($_POST['copy_paste_enabled']),
            'duplicate_on_drag' => isset($_POST['duplicate_on_drag']),
            'export_quality' => isset($_POST['export_quality']) ? \sanitize_text_field($_POST['export_quality']) : 'print',
            'export_format' => isset($_POST['export_format']) ? \sanitize_text_field($_POST['export_format']) : 'pdf',
            'compress_images' => isset($_POST['compress_images']),
            'image_quality' => isset($_POST['image_quality']) ? \intval($_POST['image_quality']) : 85,
            'max_image_size' => isset($_POST['max_image_size']) ? \intval($_POST['max_image_size']) : 2048,
            'include_metadata' => isset($_POST['include_metadata']),
            'pdf_author' => isset($_POST['pdf_author']) ? \sanitize_text_field($_POST['pdf_author']) : get_bloginfo('name'),
            'pdf_subject' => isset($_POST['pdf_subject']) ? \sanitize_text_field($_POST['pdf_subject']) : '',
            'auto_crop' => isset($_POST['auto_crop']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => isset($_POST['max_fps']) ? \intval($_POST['max_fps']) : 60,
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => isset($_POST['auto_save_interval']) ? \intval($_POST['auto_save_interval']) : 5,
            'auto_save_versions' => isset($_POST['auto_save_versions']) ? \intval($_POST['auto_save_versions']) : 10,
            'undo_levels' => isset($_POST['undo_levels']) ? \intval($_POST['undo_levels']) : 50,
            'redo_levels' => isset($_POST['redo_levels']) ? \intval($_POST['redo_levels']) : 50,
            'enable_keyboard_shortcuts' => isset($_POST['enable_keyboard_shortcuts']),
            'debug_mode' => isset($_POST['debug_mode']),
            'show_fps' => isset($_POST['show_fps']),
            // 'email_notifications' removed
            'admin_email' => sanitize_email($_POST['admin_email'] ?? '')
        ];

        // Sauvegarder les paramètres
        pdf_builder_update_option('pdf_builder_settings', $settings);

        // Traiter les paramètres de sauvegarde automatique
        if (isset($_POST['pdf_builder_backup_frequency'])) {
            pdf_builder_update_option('pdf_builder_backup_frequency', \sanitize_text_field($_POST['pdf_builder_backup_frequency']));
        }

        // Traiter les informations entreprise spécifiques
        if (isset($_POST['company_vat'])) {
            pdf_builder_update_option('pdf_builder_company_vat', \sanitize_text_field($_POST['company_vat']));
        }
        if (isset($_POST['company_rcs'])) {
            pdf_builder_update_option('pdf_builder_company_rcs', \sanitize_text_field($_POST['company_rcs']));
        }
        if (isset($_POST['company_siret'])) {
            pdf_builder_update_option('pdf_builder_company_siret', \sanitize_text_field($_POST['company_siret']));
        }
        if (isset($_POST['company_phone'])) {
            pdf_builder_update_option('pdf_builder_company_phone', \sanitize_text_field($_POST['company_phone']));
        }

        // Traiter les rôles autorisés
        if (isset($_POST['pdf_builder_allowed_roles'])) {
            $allowed_roles = array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']);
            if (empty($allowed_roles)) {
                $allowed_roles = ['administrator'];
            }
            pdf_builder_update_option('pdf_builder_allowed_roles', $allowed_roles);
        }

        // Traiter les mappings template par statut de commande
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            $template_mappings = [];
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                $template_id = \intval($template_id);
                if ($template_id > 0) {
                    $template_mappings[\sanitize_text_field($status)] = $template_id;
                }
            }
            pdf_builder_update_option('pdf_builder_settings', array_merge(pdf_builder_get_option('pdf_builder_settings', array()), ['pdf_builder_order_status_templates' => $template_mappings]));
        }

        // Retourner le succès
        \wp_send_json_success(
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
    public function ajaxGetSettings()
    {
        // Vérifier le nonce
        if (!isset($_GET['nonce']) || !\pdf_builder_verify_nonce($_GET['nonce'], 'pdf_builder_settings')) {
            \wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit;
        }

        // Récupérer les paramètres depuis la base de données
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Retourner les paramètres
        \wp_send_json_success($settings);
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
    public function optimizeScriptTags($tag, $handle, $src)
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
    public function optimizeStyleTags($tag, $handle, $href)
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
    public function renderReactEditorPage()
    {
        // Récupérer le template_id depuis l'URL si présent
        $template_id = isset($_GET['template_id']) ? \intval($_GET['template_id']) : null;
        $transient_key = isset($_GET['transient_key']) ? \sanitize_text_field($_GET['transient_key']) : null;

        $template_data = null;

        ?>
        <div class="wrap">
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
                <h1 style="margin: 0; color: #856404;">� Éditeur Modèles Prédéfinis</h1>
                <p style="margin: 4px 0 0 0; font-size: 14px; color: #666;">
                    Éditeur spécialisé pour la création et modification de modèles prédéfinis
                </p>
            </div>

            <h1><?php esc_html_e('PDF Builder - React Editor', 'pdf-builder-pro'); ?></h1>
            <?php if ($template_id) : ?>
                <?php /* translators: %d: template ID number */ ?>
                <p><?php printf(esc_html__('Editing template #%d', 'pdf-builder-pro'), intval($template_id)); ?></p>
            <?php else : ?>
                <p><?php esc_html_e('Create a new PDF template', 'pdf-builder-pro'); ?></p>
            <?php endif; ?>

            <div id="pdf-builder-react-editor-container">
                <!-- Le contenu React sera chargé ici -->
                <p><?php esc_html_e('Loading PDF Builder React Editor...', 'pdf-builder-pro'); ?></p>
            </div>
        </div>

        <script type="text/javascript">
            // Passer les données à React de manière sécurisée
            try {
                window.pdfBuilderData = {
                    templateId: <?php echo $template_id ? intval($template_id) : 'null'; ?>,
                    templateData: <?php echo $template_data ? wp_json_encode($template_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null'; ?>,
                    isEditing: <?php echo ($template_id || $template_data) ? 'true' : 'false'; ?>,
                    ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    nonce: '<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>'
                };
            } catch (e) {
                if (window.pdfBuilderDebugSettings?.javascript) {
                    // 
                }
                window.pdfBuilderData = {
                    templateId: null,
                    templateData: null,
                    isEditing: false,
                    ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    nonce: '<?php echo esc_attr(wp_create_nonce('pdf_builder_ajax')); ?>'
                };
            }
        </script>
        <?php
    }
}

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}








