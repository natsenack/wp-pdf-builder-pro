<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Core
 * Classe principale du plugin PDF Builder Pro
 */

class PDF_Builder_Core {

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

        // Forcer l'ajout du menu sur les pages admin
        if (is_admin()) {
            $this->add_admin_menu();
        }

        pdf_builder_debug('PDF Builder Pro initialized', 1, 'core');
    }

    /**
     * Initialisation de l'administration
     */
    public function admin_init() {
        error_log('PDF BUILDER - admin_init called - is_admin: ' . (is_admin() ? 'yes' : 'no') . ' - DOING_AJAX: ' . (defined('DOING_AJAX') && DOING_AJAX ? 'yes' : 'no'));

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
        // Charger les scripts sur toutes les pages du plugin
        if (($hook && strpos($hook, 'pdf-builder') !== false) ||
            (isset($_GET['page']) && $_GET['page'] && strpos($_GET['page'], 'pdf-builder') !== false) ||
            (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] && strpos($_SERVER['REQUEST_URI'], 'pdf-builder-editor') !== false)) {

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
        if (self::$menu_added) return;
        self::$menu_added = true;

        try {
            error_log('PDF BUILDER - add_admin_menu called - start');

            add_menu_page(
                __('PDF Builder Pro', 'pdf-builder-pro'),
                __('PDF Builder', 'pdf-builder-pro'),
                '',
                'pdf-builder-pro',
                array($this, 'admin_page'),
                'dashicons-pdf',
                30
            );

            add_submenu_page(
                'pdf-builder-pro',
                __('Templates', 'pdf-builder-pro'),
                __('Templates', 'pdf-builder-pro'),
                '',
                'pdf-builder-templates',
                array($this, 'templates_page')
            );

            // Template Editor page is now handled by PDF_Builder_Admin class
            // add_submenu_page(
            //     null, // Hidden page
            //     __('Template Editor', 'pdf-builder-pro'),
            //     __('Template Editor', 'pdf-builder-pro'),
            //     'manage_options',
            //     'pdf-builder-editor',
            //     array($this, 'template_editor_page')
            // );

            add_submenu_page(
                'pdf-builder-pro',
                __('Settings', 'pdf-builder-pro'),
                __('Settings', 'pdf-builder-pro'),
                '',
                'pdf-builder-settings',
                array($this, 'settings_page')
            );

            // Page de test pour le débogage
            error_log('PDF BUILDER - Adding test templates submenu');
            add_menu_page(
                __('🔧 Test Templates', 'pdf-builder-pro'),
                __('🔧 Test Templates', 'pdf-builder-pro'),
                '',
                'pdf-builder-test',
                array($this, 'test_template_selection_page'),
                'dashicons-admin-tools',
                31
            );
            error_log('PDF BUILDER - Test templates submenu added');
            error_log('PDF BUILDER - add_admin_menu called - end');
        } catch (Exception $e) {
            error_log('PDF BUILDER - ERROR in add_admin_menu: ' . $e->getMessage());
        }
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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_templates);
        dbDelta($sql_order_canvases);

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

    /**
     * Génère un PDF pour une commande WooCommerce (délégation à l'admin)
     *
     * @param int $order_id ID de la commande
     * @param int $template_id ID du template (0 pour auto-détection)
     * @return string|WP_Error URL du PDF généré ou erreur
     */
    public function generate_order_pdf($order_id, $template_id = 0) {
        if ($this->admin && method_exists($this->admin, 'generate_order_pdf')) {
            return $this->admin->generate_order_pdf($order_id, $template_id);
        } else {
            return new WP_Error('admin_not_initialized', 'Interface d\'administration non initialisée');
        }
    }

    /**
     * Page de test pour la sélection de templates
     */
    public function test_template_selection_page() {
        error_log('PDF BUILDER - test_template_selection_page called');
        // Simuler une commande WooCommerce
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 9275;
        $order = wc_get_order($order_id);

        if (!$order) {
            echo '<div class="wrap"><h1>❌ Commande #' . $order_id . ' non trouvée</h1></div>';
            return;
        }

        $order_status = $order->get_status();

        echo '<div class="wrap">';
        echo '<h1>🧪 Test de sélection de template PDF Builder</h1>';
        echo '<p><strong>Commande #' . $order_id . '</strong> - Statut: <strong>' . $order_status . '</strong></p>';

        // Connexion à la base de données
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Vérifier s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;
        $mapped_template = null;

        echo '<h2>🔍 Étape 1: Mapping spécifique</h2>';
        echo '<p>Clé recherchée: <code>' . $status_key . '</code></p>';
        echo '<p>Mappings disponibles: <pre>' . print_r($status_templates, true) . '</pre></p>';

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $mapped_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);
            echo '<p style="color: green;">✅ Template mappé trouvé: ' . $mapped_template['name'] . ' (ID: ' . $mapped_template['id'] . ')</p>';
        } else {
            echo '<p style="color: orange;">⚠️ Aucun mapping spécifique trouvé</p>';
        }

        // Si pas de mapping spécifique, utiliser la logique de détection automatique
        $template_id = null;
        if ($mapped_template) {
            $template_id = $mapped_template['id'];
            echo '<p style="color: green;">🎯 Template sélectionné: ' . $mapped_template['name'] . ' (ID: ' . $template_id . ')</p>';
        } else {
            echo '<h2>🔍 Étape 2: Détection automatique</h2>';

            // Logique de détection automatique basée sur le statut
            $keywords = [];
            switch ($order_status) {
                case 'pending':
                    $keywords = ['devis', 'quote', 'estimation'];
                    break;
                case 'processing':
                case 'on-hold':
                    $keywords = ['facture', 'invoice', 'commande'];
                    break;
                case 'completed':
                    $keywords = ['facture', 'invoice', 'reçu', 'receipt'];
                    break;
                case 'cancelled':
                case 'refunded':
                    $keywords = ['avoir', 'credit', 'refund'];
                    break;
                case 'failed':
                    $keywords = ['erreur', 'failed', 'échoué'];
                    break;
                default:
                    $keywords = ['facture', 'invoice'];
                    break;
            }

            echo '<p>Mots-clés pour le statut \'' . $order_status . '\': <code>' . implode(', ', $keywords) . '</code></p>';

            if (!empty($keywords)) {
                // Chercher un template par défaut dont le nom contient un mot-clé
                $placeholders = str_repeat('%s,', count($keywords) - 1) . '%s';
                $sql = $wpdb->prepare(
                    "SELECT id, name FROM $table_templates WHERE is_default = 1 AND (" .
                    implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE LOWER(%s)')) .
                    ") LIMIT 1",
                    array_map(function($keyword) { return '%' . $keyword . '%'; }, $keywords)
                );

                echo '<p>Requête SQL: <code>' . $sql . '</code></p>';

                $keyword_template = $wpdb->get_row($sql, ARRAY_A);

                if ($keyword_template) {
                    $template_id = $keyword_template['id'];
                    echo '<p style="color: green;">✅ Template trouvé par mots-clés: ' . $keyword_template['name'] . ' (ID: ' . $template_id . ')</p>';
                } else {
                    echo '<p style="color: orange;">⚠️ Aucun template trouvé par mots-clés</p>';
                }
            }

            // Si aucun template spécifique trouvé, prendre n'importe quel template par défaut
            if (!$template_id) {
                $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
                if ($default_template) {
                    $template_id = $default_template['id'];
                    echo '<p style="color: blue;">🔄 Template par défaut utilisé: ' . $default_template['name'] . ' (ID: ' . $template_id . ')</p>';
                } else {
                    echo '<p style="color: orange;">⚠️ Aucun template par défaut trouvé</p>';
                }
            }

            // Si toujours pas de template, prendre le premier template disponible
            if (!$template_id) {
                $any_template = $wpdb->get_row("SELECT id, name FROM $table_templates ORDER BY id LIMIT 1", ARRAY_A);
                if ($any_template) {
                    $template_id = $any_template['id'];
                    echo '<p style="color: orange;">🔄 Premier template disponible: ' . $any_template['name'] . ' (ID: ' . $template_id . ')</p>';
                } else {
                    echo '<p style="color: red;">❌ Aucun template trouvé dans la base de données</p>';
                }
            }
        }

        echo '<h2>📊 Résultat final</h2>';
        if ($template_id) {
            echo '<p style="color: green; font-size: 18px; font-weight: bold;">✅ Template sélectionné: ID ' . $template_id . '</p>';

            // Afficher les détails du template
            $template_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            echo '<h3>Détails du template:</h3>';
            echo '<ul>';
            echo '<li><strong>Nom:</strong> ' . $template_details['name'] . '</li>';
            echo '<li><strong>Par défaut:</strong> ' . ($template_details['is_default'] ? 'Oui' : 'Non') . '</li>';
            echo '<li><strong>Créé:</strong> ' . $template_details['created_at'] . '</li>';
            echo '<li><strong>Modifié:</strong> ' . $template_details['updated_at'] . '</li>';
            echo '</ul>';

            // Tester la décodage des données JSON
            $template_data = json_decode($template_details['template_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $elements_count = isset($template_data['elements']) ? count($template_data['elements']) : 0;
                echo '<p style="color: green;">✅ Données JSON valides - ' . $elements_count . ' éléments trouvés</p>';

                if ($elements_count > 0) {
                    echo '<h4>Éléments du template:</h4>';
                    echo '<ul>';
                    foreach ($template_data['elements'] as $element) {
                        $type = isset($element['type']) ? $element['type'] : 'unknown';
                        echo '<li>Type: ' . $type . '</li>';
                    }
                    echo '</ul>';
                }
            } else {
                echo '<p style="color: red;">❌ Erreur JSON: ' . json_last_error_msg() . '</p>';
            }

        } else {
            echo '<p style="color: red; font-size: 18px; font-weight: bold;">❌ Aucun template sélectionné</p>';
        }

        echo '<hr>';
        echo '<p><a href="?page=pdf-builder-test-templates&order_id=9275" class="button">Tester commande 9275</a> | ';
        echo '<a href="?page=pdf-builder-test-templates&order_id=9276" class="button">Tester commande 9276</a></p>';
        echo '</div>';
    }
}


