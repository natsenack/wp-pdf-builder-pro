<?php

/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Éditeur React uniquement
 * REFACORISÉ : Architecture modulaire avec séparation des responsabilités
 */

namespace PDF_Builder\Admin;

// Importer les classes spécialisées
use PDF_Builder\Admin\Managers\SettingsManager;
use PDF_Builder\Admin\Handlers\AjaxHandler;
use PDF_Builder\Admin\Utils\Permissions;
use PDF_Builder\Admin\Utils\Validation;
use PDF_Builder\Admin\Utils\Helpers;
use PDF_Builder\Admin\Data\DataUtils;
use PDF_Builder\Admin\Utils\Utils;

/**
 * Classe principale d'administration du PDF Builder Pro
 * RESPONSABILITÉS : Orchestration des managers, interface principale
 */
class PdfBuilderAdmin
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Référence vers le core
     */
    private $core = null;

    /**
     * Managers spécialisés
     */
    private $template_manager = null;
    private $settings_manager = null;
    private $ajax_handler = null;

    /**
     * Intégration WooCommerce
     */
    private $woocommerce_integration = null;

    /**
     * Flag pour éviter l'ajout multiple du menu
     */
    private static $menu_added = false;

    /**
     * Manager de templates prédéfinis
     */
    private $predefined_templates_manager = null;

    /**
     * Renderers et processors
     */
    private $html_renderer = null;
    private $template_processor = null;
    private $data_utils = null;
    private $pdf_generator = null;
    private $utils = null;
    private $thumbnail_manager = null;
    private $script_loader = null;
    private $style_builder = null;
    private $table_renderer = null;
    private $react_transformer = null;
    private $filesystem_helper = null;
    private $maintenance_manager = null;
    private $logger_service = null;
    private $parameter_validator = null;

    /**
     * Handlers et providers spécialisés
     */
    private $maintenance_action_handler = null;
    private $dashboard_data_provider = null;
    private $pdf_html_generator = null;
    private $admin_page_renderer = null;

    /**
     * Obtenir l'instance unique de la classe (Singleton)
     */
    public static function getInstance($core = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($core);
        }
        return self::$instance;
    }

    /**
     * Constructeur privé
     */
    private function __construct($core = null)
    {
        $this->core = $core;

        // Charger manuellement les classes nécessaires si l'autoloader est désactivé
        if (!class_exists('PDF_Builder\Admin\Managers\SettingsManager')) {
            $settings_manager_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Managers/SettingsManager.php';
            if (file_exists($settings_manager_file)) {
                require_once $settings_manager_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Handlers\AjaxHandler')) {
            $ajax_handler_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Handlers/AjaxHandler.php';
            if (file_exists($ajax_handler_file)) {
                require_once $ajax_handler_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Renderers\HTMLRenderer')) {
            $html_renderer_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Renderers/HTMLRenderer.php';
            if (file_exists($html_renderer_file)) {
                require_once $html_renderer_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Processors\TemplateProcessor')) {
            $template_processor_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Processors/TemplateProcessor.php';
            if (file_exists($template_processor_file)) {
                require_once $template_processor_file;
            }
        }

        // Charger manuellement PDFGenerator et Utils si nécessaire
        if (!class_exists('PDF_Builder\Admin\Generators\PDFGenerator')) {
            $pdf_generator_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Generators/PDFGenerator.php';
            if (file_exists($pdf_generator_file)) {
                require_once $pdf_generator_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Utils\Utils')) {
            $utils_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Utils/Utils.php';
            if (file_exists($utils_file)) {
                require_once $utils_file;
            }
        }

        // Initialiser les managers spécialisés
        $this->settings_manager = new SettingsManager($this);
        $this->ajax_handler = new AjaxHandler($this);

        // Initialiser les nouveaux modules spécialisés
        $this->html_renderer = new \PDF_Builder\Admin\Renderers\HTMLRenderer($this);
        $this->template_processor = new \PDF_Builder\Admin\Processors\TemplateProcessor($this);

        // Charger manuellement la classe DataUtils si nécessaire
        if (!class_exists('PDF_Builder\Admin\Data\DataUtils')) {
            $data_utils_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Data/DataUtils.php';
            if (file_exists($data_utils_file)) {
                require_once $data_utils_file;
            }
        }

        $this->data_utils = new DataUtils($this);
        $this->pdf_generator = new \PDF_Builder\Admin\Generators\PDFGenerator($this);
        $this->utils = new Utils($this);

        // Initialiser l'intégration WooCommerce si disponible
        if (class_exists('PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration')) {
            $this->woocommerce_integration = new \PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration($this->core);
        }

        // Initialiser le manager de templates prédéfinis
        if (class_exists('PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager')) {
            $this->predefined_templates_manager = new \PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager();
        }

        // Charger manuellement le Thumbnail Manager si nécessaire
        if (!class_exists('PDF_Builder\Managers\PdfBuilderThumbnailManager')) {
            // Chemin absolu depuis ce fichier
            $plugin_root = dirname(dirname(dirname(__DIR__)));
            $thumbnail_manager_file = $plugin_root . '/src/Managers/PDF_Builder_Thumbnail_Manager.php';
            if (file_exists($thumbnail_manager_file)) {
                require_once $thumbnail_manager_file;
            }
        }

        // Initialiser le manager de thumbnails
        $this->thumbnail_manager = \PDF_Builder\Managers\PdfBuilderThumbnailManager::getInstance();

        // Initialiser les nouveaux services et loaders
        $this->script_loader = new \PDF_Builder\Admin\Loaders\AdminScriptLoader($this);
        $this->style_builder = new \PDF_Builder\Admin\Builders\StyleBuilder();
        $this->table_renderer = new \PDF_Builder\Admin\Renderers\TableRenderer();
        $this->react_transformer = new \PDF_Builder\Admin\Transformers\ReactDataTransformer();
        $this->filesystem_helper = new \PDF_Builder\Admin\Helpers\FileSystemHelper();
        $this->maintenance_manager = new \PDF_Builder\Admin\Managers\MaintenanceManager();
        $this->logger_service = new \PDF_Builder\Admin\Services\LoggerService();
        $this->parameter_validator = new \PDF_Builder\Admin\Validators\ParameterValidator();
        $this->maintenance_action_handler = new \PDF_Builder\Admin\Handlers\MaintenanceActionHandler();

        // Charger le DashboardDataProvider si nécessaire
        if (!class_exists('\PDF_Builder\Admin\Providers\DashboardDataProvider')) {
            require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'plugin/src/Admin/Providers/DashboardDataProvider.php';
        }

        $this->dashboard_data_provider = new \PDF_Builder\Admin\Providers\DashboardDataProvider();

        $this->initHooks();
    }

    /**
     * Log de debug conditionnel
     */
    public function debug_log($message)
    {
        if (get_option('pdf_builder_debug_javascript', '0') === '1') {
            error_log('[PDF Builder Admin] ' . $message);
        }
    }

    /**
     * Vérifie les permissions d'administration sans mise en cache
     */
    private function checkAdminPermissions()
    {
        // ✅ Vérifier la capacité pdf_builder_access (gérée par Role_Manager)
        if (current_user_can('pdf_builder_access')) {
            return true;
        }

        // ✅ Fallback: vérifier les rôles autorisés depuis les options (pour compatibilité)
        $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
        if (!is_array($allowed_roles)) {
            $allowed_roles = ['administrator', 'editor', 'shop_manager'];
        }

        $user = wp_get_current_user();
        $user_roles = $user ? $user->roles : [];

        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur peut créer un nouveau template (limitation freemium)
     *
     * @return bool
     */
    public static function can_create_template() {
        // Vérifier si utilisateur premium
        if (self::is_premium_user()) {
            return true; // Pas de limitation pour premium
        }

        // Compter templates existants de l'utilisateur
        $user_id = get_current_user_id();
        $templates_count = self::count_user_templates($user_id);

        // Limite : 1 template gratuit
        return $templates_count < 1;
    }

    /**
     * Compte le nombre de templates créés par un utilisateur
     *
     * @param int $user_id
     * @return int
     */
    public static function count_user_templates($user_id) {
        global $wpdb;
        
        // Compter depuis la table custom pdf_builder_templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        
        // Récupérer le nombre de templates pour cet utilisateur
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d AND is_default = 0",
            $user_id
        ));
        
        return (int)$count;
    }

    /**
     * Vérifie si l'utilisateur est premium
     *
     * @return bool
     */
    public static function is_premium_user() {
        // Vérifier le statut de la licence
        $license_status = get_option('pdf_builder_license_status', 'free');
        $license_key = get_option('pdf_builder_license_key', '');
        $test_key = get_option('pdf_builder_license_test_key', '');

        // Déboguer pour voir les valeurs
        // 

        // is_premium si vraie licence OU si clé de test existe
        $is_premium = ($license_status !== 'free' && $license_status !== 'expired') || (!empty($test_key));

        return $is_premium;
    }

    /**
     * Enregistre le custom post type pour les templates PDF
     */
    public function registerTemplatePostType()
    {
        register_post_type('pdf_template', [
            'labels' => [
                'name' => __('Templates PDF', 'pdf-builder-pro'),
                'singular_name' => __('Template PDF', 'pdf-builder-pro'),
                'add_new' => __('Nouveau Template', 'pdf-builder-pro'),
                'add_new_item' => __('Ajouter un Nouveau Template', 'pdf-builder-pro'),
                'edit_item' => __('Éditer le Template', 'pdf-builder-pro'),
                'new_item' => __('Nouveau Template', 'pdf-builder-pro'),
                'view_item' => __('Voir le Template', 'pdf-builder-pro'),
                'search_items' => __('Rechercher Templates', 'pdf-builder-pro'),
                'not_found' => __('Aucun template trouvé', 'pdf-builder-pro'),
                'not_found_in_trash' => __('Aucun template dans la corbeille', 'pdf-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Masqué du menu principal
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
        ]);
    }

    /**
     * Initialise les hooks WordPress
     */
    private function initHooks()
    {
        // 🔧 MIGRATION BASE DE DONNÉES - Gérée automatiquement par PDF_Builder_Migration_System
        // add_action('admin_init', [$this, 'run_database_migrations']);

        // 🔧 MISE À JOUR DES NOMS DE TEMPLATES (TEMPORAIRE)
        // Désactiver temporairement la mise à jour automatique des noms
        // add_action('admin_init', [$this, 'update_template_names']);

        // Enregistrer le custom post type pour les templates
        add_action('init', [$this, 'register_template_post_type']);

        // Hooks de base de l'admin (restent dans cette classe)
        add_action('admin_menu', [$this, 'addAdminMenu']);
        // Script loading is handled by AdminScriptLoader
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 20);

        // Inclure le gestionnaire de modèles prédéfinis
        include_once plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/predefined-templates-manager.php';

        // Instancier le gestionnaire de modèles prédéfinis
        // new PDF_Builder_Predefined_Templates_Manager();

        // Enregistrer les hooks AJAX du Template_Manager avant même son instantiation
        // Cela garantit que les handlers AJAX seront disponibles immédiatement
        // ✅ Note: Seulement pdf_builder_save_template - React utilise celui-ci

// REMOVED: Conflit avec bootstrap.php - le handler de bootstrap.php gère maintenant le chargement
// Hook pdf_builder_get_template déjà enregistré plus haut

        // Hooks WooCommerce - Délégation vers le manager
        if (class_exists('WooCommerce') && $this->woocommerce_integration !== null) {
            add_action('add_meta_boxes_shop_order', [$this->woocommerce_integration, 'addWoocommerceOrderMetaBox']);
            // Le hook HPOS peut ne pas exister dans toutes les versions, on l'enregistre seulement s'il existe
            if (has_action('add_meta_boxes_woocommerce_page_wc-orders') !== false || defined('WC_VERSION') && version_compare(WC_VERSION, '7.1', '>=')) {
                add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this->woocommerce_integration, 'addWoocommerceOrderMetaBox']);
            }
        }

        // Hook pour la compatibilité avec les anciens liens template_id
        add_action('admin_init', [$this, 'handle_legacy_template_links']);
    }

    /**
     * Enregistre le custom post type pour les templates PDF
     */
    public function register_template_post_type()
    {
        $labels = array(
            'name' => __('Templates PDF', 'pdf-builder-pro'),
            'singular_name' => __('Template PDF', 'pdf-builder-pro'),
            'menu_name' => __('Templates', 'pdf-builder-pro'),
            'add_new' => __('Ajouter', 'pdf-builder-pro'),
            'add_new_item' => __('Ajouter un template PDF', 'pdf-builder-pro'),
            'edit_item' => __('Modifier le template', 'pdf-builder-pro'),
            'new_item' => __('Nouveau template', 'pdf-builder-pro'),
            'view_item' => __('Voir le template', 'pdf-builder-pro'),
            'search_items' => __('Rechercher des templates', 'pdf-builder-pro'),
            'not_found' => __('Aucun template trouvé', 'pdf-builder-pro'),
            'not_found_in_trash' => __('Aucun template dans la corbeille', 'pdf-builder-pro'),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Masqué du menu principal
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'custom-fields'),
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        );

        register_post_type('pdf_template', $args);
    }

    /**
     * Compatibilité avec les anciens liens template_id - méthode vide (système supprimé)
     */
    public function handleLegacyTemplateLinks()
    {
        // Méthode vide - plus de redirection nécessaire car éditeur unique
    }

    /**
     * Alias pour handleLegacyTemplateLinks() - compatibilité
     */
    public function handle_legacy_template_links()
    {
        return $this->handleLegacyTemplateLinks();
    }

    /**
     * Ajoute le menu d'administration
     */
    public function addAdminMenu()
    {
        // Éviter l'ajout multiple du menu
        if (self::$menu_added) {
            return;
        }
        self::$menu_added = true;

        // Menu principal avec icône distinctive - position remontée
        add_menu_page(__('PDF Builder Pro - Gestionnaire de PDF', 'pdf-builder-pro'), __('PDF Builder', 'pdf-builder-pro'), 'pdf_builder_access', 'pdf-builder-pro', [$this, 'adminPage'], 'dashicons-pdf', 25);

        // Page d'accueil (sous-menu principal masqué)
        add_submenu_page(
            'pdf-builder-pro',
            __('Accueil - PDF Builder Pro', 'pdf-builder-pro'),
            __('🏠 Accueil', 'pdf-builder-pro'),
            'pdf_builder_access',
            'pdf-builder-pro', // Même slug que le menu principal
            [$this, 'adminPage']
        );

        // Éditeur React unique (accessible via lien direct, masqué du menu)
        add_submenu_page('pdf-builder-pro', __('Éditeur PDF', 'pdf-builder-pro'), __('🎨 Éditeur PDF', 'pdf-builder-pro'), 'pdf_builder_access', 'pdf-builder-react-editor', [$this, 'reactEditorPage']);

        // Masquer le menu de l'éditeur React globalement avec CSS
        add_action('admin_enqueue_scripts', function() {
            echo '<style>
                li a[href*="page=pdf-builder-react-editor"] {
                    display: none !important;
                }
                li a[href*="page=pdf-builder-react-editor"] + ul {
                    display: none !important;
                }
            </style>';
        });

        // Gestion des templates
        add_submenu_page('pdf-builder-pro', __('Templates PDF - PDF Builder Pro', 'pdf-builder-pro'), __('📋 Templates', 'pdf-builder-pro'), 'pdf_builder_access', 'pdf-builder-templates', [$this, 'templatesPage']);

        // Paramètres et configuration
        add_submenu_page('pdf-builder-pro', __('Paramètres - PDF Builder Pro', 'pdf-builder-pro'), __('⚙️ Paramètres', 'pdf-builder-pro'), 'pdf_builder_access', 'pdf-builder-settings', [$this, 'settings_page']);

    }



    /**
     * Page principale d'administration - Tableau de bord
     */
    public function adminPage()
    {
        if (!$this->checkAdminPermissions()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Utiliser le renderer pour afficher la page d'administration
        if ($this->admin_page_renderer) {
            echo $this->admin_page_renderer->renderAdminPage();
        } else {
            // Fallback si le renderer n'est pas disponible
            echo '<div class="wrap"><h1>PDF Builder Pro</h1><p>Erreur: Renderer non disponible.</p></div>';
        }
    }
}
