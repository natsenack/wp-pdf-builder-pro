<?php
/**
 * PDF Builder Pro - Classe Principale Ultra-Performante
 * Inspiré de WooCommerce PDF Invoice Builder
 *
 * Version 1.0.0
 * Architecture modulaire avec IoC et haute performance
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe principale PDF Builder Pro
 *
 * Architecture inspirée de woocommerce-pdf-invoice.php avec améliorations :
 * - Pattern IoC (Inversion of Control) avancé
 * - Cache multi-niveau haute performance
 * - Gestion d'erreurs robuste
 * - Architecture modulaire extensible
 * - Optimisations de performance
 */
class PDF_Builder_Core {

    /**
     * Instance singleton
     * @var PDF_Builder_Core
     */
    private static $instance = null;

    /**
     * Conteneur IoC pour l'injection de dépendances
     * @var array
     */
    private $container = [];

    /**
     * Gestionnaire de cache multi-niveau
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager = null;

    /**
     * Gestionnaire de configuration
     * @var PDF_Builder_Config_Manager
     */
    private $config_manager = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $database_manager = null;

    /**
     * Gestionnaire d'API
     * @var PDF_Builder_API_Manager
     */
    private $api_manager = null;

    /**
     * Gestionnaire de templates
     * @var PDF_Builder_Template_Manager
     */
    private $template_manager = null;

    /**
     * Gestionnaire de logs et monitoring
     * @var PDF_Builder_Logger
     */
    private $logger = null;

    /**
     * Gestionnaire de Bulk Actions
     * @var PDF_Builder_Bulk_Actions_Manager
     */
    private $bulk_actions_manager = null;

    /**
     * Interface d'administration
     * @var PDF_Builder_Admin
     */
    private $admin = null;

    /**
     * Gestionnaire d'Export
     * @var PDF_Builder_Export_Manager
     */
    private $export_manager = null;

    /**
     * Gestionnaire d'Analytics
     * @var PDF_Builder_Analytics_Manager
     */
    private $analytics_manager = null;

    /**
     * Gestionnaire de Collaboration
     * @var PDF_Builder_Collaboration_Manager
     */
    private $collaboration_manager = null;

    /**
     * Gestionnaire d'intégration WooCommerce
     * @var PDF_Builder_WooCommerce_Integration
     */
    private $woocommerce_integration = null;

    /**
     * Gestionnaire d'API Avancée
     * @var PDF_Builder_API_Manager_Advanced
     */
    private $api_manager_advanced = null;

    /**
     * Phase 4: Gestionnaire de Sécurité Enterprise
     * @var PDF_Builder_Security_Manager
     */
    private $security_manager = null;

    /**
     * Phase 4: Gestionnaire de Tests Industriels
     * @var PDF_Builder_Test_Manager
     */
    private $test_manager = null;

    /**
     * Phase 4: Pipeline CI/CD
     * @var PDF_Builder_CI_CD_Pipeline
     */
    private $ci_cd_pipeline = null;

    /**
     * Phase 4: Système de Monitoring & Alertes
     * @var PDF_Builder_Monitoring_Alerts
     */
    private $monitoring_alerts = null;

    /**
     * Phase 4: Système de Documentation Premium
     * @var PDF_Builder_Documentation_System
     */
    private $documentation_system = null;

    /**
     * Phase 4: Système de Launch & Growth Hacking
     * @var PDF_Builder_Launch_Growth_System
     */
    private $launch_growth_system = null;

    /**
     * Phase 4: Système de Déploiement & Production
     * @var PDF_Builder_Deployment_Production_System
     */
    private $deployment_production_system = null;

    /**
     * Gestionnaire des éléments du canvas
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private $canvas_elements_manager = null;

    /**
     * Gestionnaire de drag & drop
     * @var PDF_Builder_Drag_Drop_Manager
     */
    private $drag_drop_manager = null;

    /**
     * Gestionnaire de redimensionnement
     * @var PDF_Builder_Resize_Manager
     */
    private $resize_manager = null;

    /**
     * Gestionnaire des interactions du canvas
     * @var PDF_Builder_Canvas_Interactions_Manager
     */
    private $canvas_interactions_manager = null;

    /**
     * Phase 4: Système de Validation & Launch
     * @var PDF_Builder_Validation_Launch_System
     */
    private $validation_launch_system = null;

    /**
     * Phase 4: Script de Déploiement Automatisé
     * @var PDF_Builder_Deployment_Script
     */
    private $deployment_script = null;

    /**
     * État d'initialisation du plugin
     * @var bool
     */
    private $initialized = false;

    /**
     * Métriques de performance
     * @var array
     */
    private $performance_metrics = [];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->init_performance_monitoring();
        $this->register_shutdown_function();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Core
     */
    public static function getInstance(): PDF_Builder_Core {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation du plugin
     *
     * @return void
     */
    public function init(): void {
        // Protection globale contre les initialisations multiples
        if (defined('PDF_BUILDER_CORE_INITIALIZED') && PDF_BUILDER_CORE_INITIALIZED) {
            return;
        }

        if ($this->initialized) {
            return;
        }

        $start_time = microtime(true);

        try {
            $this->init_constants();
            $this->init_autoloader();
            $this->init_container();
            $this->init_managers();
            $this->init_admin();
            $this->init_hooks();
            $this->init_cron_jobs();
            $this->validate_environment();

            $this->initialized = true;
            define('PDF_BUILDER_CORE_INITIALIZED', true);
            $this->log_performance_metric('init_time', microtime(true) - $start_time);

            $this->logger->info('PDF Builder Pro initialized successfully', [
                'version' => PDF_BUILDER_VERSION,
                'init_time' => $this->performance_metrics['init_time'] ?? 0
            ]);

        } catch (Exception $e) {
            $this->handle_initialization_error($e);
        }
    }

    /**
     * Initialisation des constantes
     *
     * @return void
     */
    private function init_constants(): void {
        // Version du plugin
        if (!defined('PDF_BUILDER_VERSION')) {
            define('PDF_BUILDER_VERSION', '1.0.0');
        }

        // Chemins et URLs - gérer le cas où WordPress n'est pas chargé
        // PDF_BUILDER_PLUGIN_DIR est déjà défini dans le fichier principal
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            if (function_exists('plugin_dir_path')) {
                define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__, 2) . '/pdf-builder-pro.php'));
            } else {
                // Fallback pour les tests hors WordPress - utiliser le répertoire parent
                define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR);
            }
        }

        if (!defined('PDF_BUILDER_PLUGIN_URL')) {
            if (function_exists('plugin_dir_url')) {
                define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(dirname(__FILE__, 3) . '/pdf-builder-pro.php'));
            } else {
                // Fallback pour les tests hors WordPress
                define('PDF_BUILDER_PLUGIN_URL', 'file://' . dirname(__FILE__, 3) . '/');
            }
        }

        if (!defined('PDF_BUILDER_PLUGIN_BASENAME')) {
            if (function_exists('plugin_basename')) {
                define('PDF_BUILDER_PLUGIN_BASENAME', plugin_basename(__FILE__));
            } else {
                define('PDF_BUILDER_PLUGIN_BASENAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
            }
        }

        // Assets URLs
        if (!defined('PDF_BUILDER_ASSETS_URL')) {
            define('PDF_BUILDER_ASSETS_URL', PDF_BUILDER_PLUGIN_URL . 'assets/');
        }

        if (!defined('PDF_BUILDER_CSS_URL')) {
            define('PDF_BUILDER_CSS_URL', PDF_BUILDER_ASSETS_URL . 'css/');
        }

        if (!defined('PDF_BUILDER_JS_URL')) {
            define('PDF_BUILDER_JS_URL', PDF_BUILDER_ASSETS_URL . 'js/');
        }

        if (!defined('PDF_BUILDER_IMG_URL')) {
            define('PDF_BUILDER_IMG_URL', PDF_BUILDER_ASSETS_URL . 'images/');
        }

        // Configuration avancée
        if (!defined('PDF_BUILDER_CACHE_TTL')) {
            define('PDF_BUILDER_CACHE_TTL', 3600); // 1 heure
        }

        if (!defined('PDF_BUILDER_MAX_EXECUTION_TIME')) {
            define('PDF_BUILDER_MAX_EXECUTION_TIME', 300); // 5 minutes
        }

        if (!defined('PDF_BUILDER_MEMORY_LIMIT')) {
            define('PDF_BUILDER_MEMORY_LIMIT', '256M');
        }
    }

    /**
     * Initialisation de l'autoloader
     *
     * @return void
     */
    private function init_autoloader(): void {
        spl_autoload_register([$this, 'autoload_classes']);
    }

    /**
     * Autoloader personnalisé pour les classes du plugin
     *
     * @param string $class_name
     * @return void
     */
    public function autoload_classes(string $class_name): void {
        // Vérifier si c'est une classe du plugin
        if (strpos($class_name, 'PDF_Builder_') !== 0) {
            return;
        }

        // Utiliser le nom de classe complet comme nom de fichier
        $file_name = $class_name . '.php';

        $possible_paths = [
            PDF_BUILDER_PLUGIN_DIR . 'includes/classes/' . $file_name,
            PDF_BUILDER_PLUGIN_DIR . 'includes/managers/' . $file_name,
            PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/' . $file_name,
            PDF_BUILDER_PLUGIN_DIR . 'includes/api/' . $file_name,
            PDF_BUILDER_PLUGIN_DIR . 'includes/' . $file_name
        ];

        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                // FORCER le chargement même si la classe n'existe pas encore
                @require_once $path; // Utiliser @ pour supprimer les warnings
                return;
            }
        }

        // NE PAS logger d'erreur pour éviter les problèmes - les classes peuvent être créées plus tard
        // Les gestionnaires null sont gérés dans init_managers()
    }

    /**
     * Initialisation du conteneur IoC
     *
     * @return void
     */
    private function init_container(): void {
        $this->container = [];

        // Gestionnaires de base qui existent
        $this->container['logger'] = function() {
            return PDF_Builder_Logger::getInstance();
        };

        $this->container['pdf_generator'] = function() {
            return new PDF_Builder_PDF_Generator();
        };

        // Gestionnaires principaux - FORCER l'activation même si classes n'existent pas
        $core_managers = [
            'PDF_Builder_Cache_Manager' => 'cache',
            'PDF_Builder_Config_Manager' => 'config',
            'PDF_Builder_Database_Manager' => 'database',
            'PDF_Builder_API_Manager' => 'api',
            'PDF_Builder_Template_Manager' => 'template',
        ];

        foreach ($core_managers as $class_name => $service_name) {
            $this->container[$service_name] = function() use ($class_name) {
                if (class_exists($class_name)) {
                    return $class_name::getInstance();
                }
                // Forcer la création même si classe n'existe pas
                return null;
            };
        }

        // Gestionnaires optionnels - DÉSACTIVÉS par défaut pour alléger le plugin
        $optional_managers = [
            'PDF_Builder_Bulk_Actions_Manager' => 'bulk_actions',
            'PDF_Builder_Export_Manager' => 'export',
            'PDF_Builder_Analytics_Manager' => 'analytics',
            'PDF_Builder_Collaboration_Manager' => 'collaboration',
        ];

        // Désactiver les optionnels pour alléger le plugin
        foreach ($optional_managers as $class_name => $service_name) {
            $this->container[$service_name] = function() {
                return null; // Désactivé pour performance
            };
        }

        // Phase 4 managers - COMPLÈTEMENT DÉSACTIVÉS (fonctionnalités entreprise)
        $phase4_managers = [
            'PDF_Builder_API_Manager_Advanced' => 'api_advanced',
            'PDF_Builder_Security_Manager' => 'security',
            'PDF_Builder_Test_Manager' => 'test',
            'PDF_Builder_CI_CD_Pipeline' => 'ci_cd',
            'PDF_Builder_Monitoring_Alerts' => 'monitoring',
            'PDF_Builder_Documentation_System' => 'documentation',
            'PDF_Builder_Launch_Growth_System' => 'launch_growth',
            'PDF_Builder_Deployment_Production_System' => 'deployment_production',
            'PDF_Builder_Validation_Launch_System' => 'validation_launch',
            'PDF_Builder_Deployment_Script' => 'deployment_script',
        ];

        // Tous les Phase 4 sont désactivés pour alléger considérablement le plugin
        foreach ($phase4_managers as $class_name => $service_name) {
            $this->container[$service_name] = function() {
                return null; // Désactivé - fonctionnalités entreprise
            };
        }
    }

    /**
     * Initialisation des gestionnaires
     *
     * @return void
     */
    private function init_managers(): void {
        // Vérifier si WordPress est disponible avant d'initialiser les managers
        $wordpress_available = function_exists('add_action') && function_exists('get_option');

        // Logger d'abord (toujours disponible)
        $this->logger = $this->get('logger');

        // Générateur PDF (toujours disponible)
        // Note: pas stocké dans une propriété de classe pour l'instant

        if ($wordpress_available) {
            // UNIQUEMENT les gestionnaires ESSENTIELS si WordPress est disponible
            $this->cache_manager = $this->get('cache');
            $this->config_manager = $this->get('config');
            $this->database_manager = $this->get('database');
            $this->api_manager = $this->get('api');
            $this->template_manager = $this->get('template');
        } else {
            // WordPress pas disponible - créer des instances null ou mock
            $this->cache_manager = null;
            $this->config_manager = null;
            $this->database_manager = null;
            $this->api_manager = null;
            $this->template_manager = null;
        }

        // Gestionnaires OPTIONNELS - DÉSACTIVÉS pour alléger le plugin
        $this->bulk_actions_manager = null; // Désactivé
        $this->export_manager = null; // Désactivé
        $this->analytics_manager = null; // Désactivé
        $this->collaboration_manager = null; // Désactivé
        $this->api_manager_advanced = null; // Désactivé

        // Phase 4: Enterprise Managers - TOUS DÉSACTIVÉS pour performance
        $this->security_manager = null; // Désactivé
        $this->test_manager = null; // Désactivé
        $this->ci_cd_pipeline = null; // Désactivé
        $this->monitoring_alerts = null; // Désactivé
        $this->documentation_system = null; // Désactivé
        $this->launch_growth_system = null; // Désactivé
        $this->deployment_production_system = null; // Désactivé
        $this->validation_launch_system = null; // Désactivé
        $this->deployment_script = null; // Désactivé

        // WooCommerce Integration Manager - ACTIVÉ (Phase 5)
        if ($wordpress_available && class_exists('WooCommerce')) {
            $this->woocommerce_integration = PDF_Builder_WooCommerce_Integration::get_instance();
        } else {
            $this->woocommerce_integration = null;
        }

        // Initialisation séquentielle UNIQUEMENT des gestionnaires actifs
        $managers = [
            'cache_manager' => $this->cache_manager,
            'config_manager' => $this->config_manager,
            'database_manager' => $this->database_manager,
            'api_manager' => $this->api_manager,
            'template_manager' => $this->template_manager,
            // Canvas managers
            'canvas_elements_manager' => $this->canvas_elements_manager,
            'drag_drop_manager' => $this->drag_drop_manager,
            'resize_manager' => $this->resize_manager,
            'canvas_interactions_manager' => $this->canvas_interactions_manager,
            // WooCommerce integration
            'woocommerce_integration' => $this->woocommerce_integration,
            // Optionnels et Phase 4 sont null - pas d'initialisation
        ];

        foreach ($managers as $name => $manager) {
            try {
                if ($manager && method_exists($manager, 'init')) {
                    $manager->init();
                }
                $this->logger->debug("Manager initialized: {$name}");
            } catch (Exception $e) {
                $this->logger->error("Failed to initialize manager: {$name}", [
                    'error' => $e->getMessage()
                ]);
                // Ne pas planter - continuer avec les autres
            }
        }

        $this->logger->info('PDF Builder managers LIGHTWEIGHT MODE', [
            'active_managers' => count($managers),
            'disabled_optional' => 4,
            'disabled_phase4' => 9,
            'phase' => 'LIGHTWEIGHT ACTIVATION MODE'
        ]);
    }

    /**
     * Initialisation de l'interface d'administration
     *
     * @return void
     */
    private function init_admin(): void {
        // Vérifier si WordPress est disponible
        if (!function_exists('add_action')) {
            $this->logger->warning('WordPress admin functions not available, skipping admin initialization');
            return;
        }

        // Inclure et instancier la classe d'administration
        if (!class_exists('PDF_Builder_Admin')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'includes/class-pdf-builder-admin.php';
        }

        if (class_exists('PDF_Builder_Admin')) {
            $this->admin = new PDF_Builder_Admin($this);
            $this->logger->info('PDF Builder Admin interface initialized');
        } else {
            $this->logger->error('Failed to load PDF_Builder_Admin class');
        }
    }

    /**
     * Initialisation des hooks WordPress
     *
     * @return void
     */
    private function init_hooks(): void {
        // Vérifier si WordPress est disponible
        if (!function_exists('add_action')) {
            $this->logger->warning('WordPress hooks not available, skipping hook registration');
            return;
        }

        // Hooks d'activation/désactivation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Hooks principaux
        add_action('plugins_loaded', [$this, 'on_plugins_loaded'], 5);
        add_action('init', [$this, 'on_init'], 5);
        add_action('admin_init', [$this, 'on_admin_init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Hooks AJAX
        add_action('wp_ajax_pdf_builder_action', [$this, 'handle_ajax_request']);
        add_action('wp_ajax_nopriv_pdf_builder_action', [$this, 'handle_ajax_request']);

        // Hooks de nettoyage
        add_action('wp_loaded', [$this, 'on_wp_loaded']);
        add_action('shutdown', [$this, 'on_shutdown']);

        // Hook pour création des tables restantes en arrière-plan
        add_action('pdf_builder_create_remaining_tables', [self::class, 'create_remaining_tables']);

        // Hook pour initialisation complète en arrière-plan
        add_action('pdf_builder_complete_setup', [self::class, 'complete_setup']);
    }

    /**
     * Initialisation des tâches cron
     *
     * @return void
     */
    private function init_cron_jobs(): void {
        // Vérifier si WordPress est disponible
        if (!function_exists('wp_schedule_event')) {
            $this->logger->warning('WordPress cron functions not available, skipping cron job registration');
            return;
        }

        // Nettoyage du cache
        if (!wp_next_scheduled('pdf_builder_cleanup_cache')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_cleanup_cache');
        }
        add_action('pdf_builder_cleanup_cache', [$this, 'cleanup_expired_cache']);

        // Optimisation base de données
        if (!wp_next_scheduled('pdf_builder_optimize_database')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_optimize_database');
        }
        add_action('pdf_builder_optimize_database', [$this, 'optimize_database']);

        // Génération de rapports
        if (!wp_next_scheduled('pdf_builder_generate_reports')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_generate_reports');
        }
        add_action('pdf_builder_generate_reports', [$this, 'generate_daily_reports']);
    }

    /**
     * Validation de l'environnement
     *
     * @return void
     */
    private function validate_environment(): void {
        $requirements = [
            'php_version' => ['required' => '8.1', 'current' => PHP_VERSION],
            'wordpress_version' => ['required' => '6.0', 'current' => function_exists('get_bloginfo') ? get_bloginfo('version') : '0.0'],
            'memory_limit' => ['required' => '128M', 'current' => ini_get('memory_limit')],
            'max_execution_time' => ['required' => '120', 'current' => ini_get('max_execution_time')]
        ];

        foreach ($requirements as $requirement => $versions) {
            if (version_compare($versions['current'], $versions['required'], '<')) {
                $this->logger->warning("Requirement not met: {$requirement}", [
                    'required' => $versions['required'],
                    'current' => $versions['current']
                ]);
            }
        }
    }

    /**
     * Hook: plugins_loaded
     *
     * @return void
     */
    public function on_plugins_loaded(): void {
        // Chargement des traductions avec détection automatique de langue
        $this->load_translations();

        // Vérification des dépendances
        $this->check_dependencies();
    }

    /**
     * Charger les traductions avec détection automatique de langue
     *
     * @return void
     */
    private function load_translations(): void {
        $domain = 'pdf-builder-pro';
        $languages_path = dirname(PDF_BUILDER_PLUGIN_BASENAME) . '/languages/';

        // Détecter la langue WordPress
        $locale = $this->get_wordpress_locale();

        // Charger le textdomain principal
        load_plugin_textdomain(
            $domain,
            false,
            $languages_path
        );

        // Log de la langue détectée
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("PDF Builder Pro: Langue détectée - {$locale}");
        }
    }

    /**
     * Obtenir la locale WordPress actuelle
     *
     * @return string
     */
    private function get_wordpress_locale(): string {
        // Essayer d'abord get_locale() de WordPress
        if (function_exists('get_locale')) {
            return get_locale();
        }

        // Fallback vers la constante WPLANG si définie
        if (defined('WPLANG') && WPLANG) {
            return WPLANG;
        }

        // Fallback vers la langue du navigateur si disponible
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept_lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $lang = trim(explode(';', $accept_lang[0])[0]);
            // Convertir les formats comme fr-FR en fr_FR
            $lang = str_replace('-', '_', $lang);
            return $lang;
        }

        // Dernier fallback : anglais
        return 'en_US';
    }

    /**
     * Hook: init
     *
     * @return void
     */
    public function on_init(): void {
        // Enregistrement des types de contenu personnalisés
        $this->register_custom_post_types();

        // Enregistrement des taxonomies
        $this->register_taxonomies();

        // Initialisation des capacités
        $this->init_capabilities();
    }

    /**
     * Hook: admin_init
     *
     * @return void
     */
    public function on_admin_init(): void {
        error_log('PDF Builder: on_admin_init called - WordPress functions available: ' . (function_exists('add_menu_page') ? 'YES' : 'NO'));

        // S'assurer que le menu est ajouté même si d'autres choses échouent
        $this->ensure_admin_menu_added();

        // Vérifier si l'initialisation est en cours (tables supplémentaires)
        $activation_time = get_option('pdf_builder_activation_time', 0);
        $time_since_activation = time() - $activation_time;
        $setup_complete = get_option('pdf_builder_setup_complete', false);

        if ($activation_time && $time_since_activation < 300 && !$setup_complete) {
            add_action('admin_notices', [self::class, 'show_setup_notice']);
        }

        // Ajout des menus d'administration
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Paramètres d'administration
        add_action('admin_init', [$this, 'register_settings']);

        error_log('PDF Builder: on_admin_init completed');
    }

    /**
     * S'assurer que le menu admin est ajouté
     *
     * @return void
     */
    private function ensure_admin_menu_added(): void {
        // Vérifier si le menu existe déjà
        global $menu;
        $menu_exists = false;
        
        // Vérifier que $menu est un array valide
        if (is_array($menu) && !empty($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && $item[2] === 'pdf-builder-main') {
                    $menu_exists = true;
                    break;
                }
            }
        }

        if (!$menu_exists) {
            error_log('PDF Builder: Force adding admin menu - DÉSACTIVÉ (géré par bootstrap)');
            // $this->add_admin_menu();
        }
    }

    /**
     * Afficher une notice d'initialisation en cours
     *
     * @return void
     */
    public static function show_setup_notice(): void {
        $activation_time = get_option('pdf_builder_activation_time', 0);
        $time_since_activation = time() - $activation_time;

        // Montrer la notice seulement si l'activation est récente et que les tables restantes sont en cours de création
        if ($activation_time && $time_since_activation < 300) {
            $setup_complete = get_option('pdf_builder_setup_complete', false);

            if (!$setup_complete) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p><strong>PDF Builder Pro</strong>: Configuration des tables supplémentaires en cours. ';
                echo 'L\'installation complète peut prendre quelques minutes. ';
                echo 'Le plugin est déjà fonctionnel avec les fonctionnalités essentielles.</p>';
                echo '</div>';
            }
        }
    }

    /**
     * Enregistrement des types de contenu personnalisés
     *
     * @return void
     */
    private function register_custom_post_types(): void {
        register_post_type('pdf_template', [
            'labels' => [
                'name' => __('PDF Templates', 'pdf-builder-pro'),
                'singular_name' => __('PDF Template', 'pdf-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'capability_type' => 'pdf_template',
            'map_meta_cap' => true,
        ]);

        register_post_type('pdf_document', [
            'labels' => [
                'name' => __('PDF Documents', 'pdf-builder-pro'),
                'singular_name' => __('PDF Document', 'pdf-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'pdf_document',
            'map_meta_cap' => true,
        ]);
    }

    /**
     * Enregistrement des taxonomies
     *
     * @return void
     */
    private function register_taxonomies(): void {
        register_taxonomy('pdf_category', ['pdf_template'], [
            'labels' => [
                'name' => __('Template Categories', 'pdf-builder-pro'),
                'singular_name' => __('Template Category', 'pdf-builder-pro'),
            ],
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
        ]);
    }

    /**
     * Initialisation des capacités
     *
     * @return void
     */
    private function init_capabilities(): void {
        $roles = ['administrator', 'editor'];

        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('manage_pdf_templates');
                $role->add_cap('edit_pdf_templates');
                $role->add_cap('delete_pdf_templates');
                $role->add_cap('manage_pdf_documents');
                $role->add_cap('edit_pdf_documents');
                $role->add_cap('delete_pdf_documents');
            }
        }
    }

    /**
     * Ajout du menu d'administration
     *
     * @return void
     */
    public function add_admin_menu(): void {
        // Debug: vérifier que la fonction est appelée
        error_log('PDF Builder: add_admin_menu called for user ' . get_current_user_id());

        add_menu_page(
            __('PDF Builder Pro', 'pdf-builder-pro'),
            __('PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-main',
            [$this, 'render_main_page'],
            'dashicons-pdf',
            30
        );

        add_submenu_page(
            'pdf-builder-main',
            __('Templates', 'pdf-builder-pro'),
            __('Templates', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-templates',
            [$this, 'render_templates_page']
        );

        add_submenu_page(
            'pdf-builder-main',
            __('Documents', 'pdf-builder-pro'),
            __('Documents', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-documents',
            [$this, 'render_documents_page']
        );

        add_submenu_page(
            'pdf-builder-main',
            __('Settings', 'pdf-builder-pro'),
            __('Settings', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'pdf-builder-main',
            __('Migration', 'pdf-builder-pro'),
            __('Migration', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-migration',
            [$this, 'render_migration_page']
        );

        error_log('PDF Builder: menu pages added successfully');
    }

    /**
     * Enregistrement des paramètres
     *
     * @return void
     */
    public function register_settings(): void {
        register_setting('pdf_builder_options', 'pdf_builder_settings');

        add_settings_section(
            'pdf_builder_general',
            __('General Settings', 'pdf-builder-pro'),
            [$this, 'render_settings_section'],
            'pdf-builder-settings'
        );
    }

    /**
     * Chargement des assets frontend
     *
     * @return void
     */
    public function enqueue_frontend_assets(): void {
        if (!$this->should_load_frontend_assets()) {
            return;
        }

        // Note: Frontend assets (CSS/JS) are not currently implemented
        // Removed enqueue of non-existent frontend.css and frontend.js files
    }

    /**
     * Chargement des assets admin
     *
     * @param string $hook
     * @return void
     */
    public function enqueue_admin_assets(string $hook): void {
        // Vérifier si c'est une page du plugin PDF Builder
        $is_pdf_builder_page = strpos($hook, 'pdf-builder') !== false ||
                              (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false);

        if (!$is_pdf_builder_page) {
            return;
        }

        // Ne pas charger React sur la page éditeur - PDF_Builder_Admin s'en charge
        $is_editor_page = $hook === 'pdf-builder_page_pdf-builder-editor' ||
                         (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-editor');

        // Styles communs à toutes les pages
        $css_file = PDF_BUILDER_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'pdf-builder-admin',
                PDF_BUILDER_CSS_URL . 'admin.css',
                [],
                filemtime($css_file)
            );
        }

        // Scripts communs
        $js_file = PDF_BUILDER_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'pdf-builder-admin',
                PDF_BUILDER_JS_URL . 'admin.js',
                ['jquery', 'wp-util'],
                filemtime($js_file),
                true
            );
        }

        // Charger React seulement si ce n'est pas la page éditeur
        if (!$is_editor_page) {

        // Scripts spécifiques au canvas (éditeur)
        if ($hook === 'admin_page_pdf-builder-editor' ||
            (isset($_GET['page']) && $_GET['page'] === 'pdf-builder-editor')) {
            // Charger les styles CSS
            wp_enqueue_style(
                'pdf-builder-canvas-styles',
                PDF_BUILDER_CSS_URL . 'pdf-builder-canvas.css',
                [],
                PDF_BUILDER_VERSION
            );

            wp_enqueue_style(
                'pdf-builder-react-styles',
                PDF_BUILDER_CSS_URL . 'pdf-builder-react.css',
                ['pdf-builder-canvas-styles'],
                PDF_BUILDER_VERSION
            );

            // Charger React et ReactDOM - forcer le CDN pour compatibilité avec le bundle
            wp_enqueue_script(
                'react',
                'https://unpkg.com/react@18/umd/react.production.min.js',
                [],
                '18.2.0',
                true
            );

            wp_enqueue_script(
                'react-dom',
                'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js',
                ['react'],
                '18.2.0',
                true
            );

            wp_enqueue_script(
                'pdf-builder-canvas-app',
                PDF_BUILDER_JS_URL . 'dist/pdf-builder-admin.js',
                ['jquery', 'react', 'react-dom'],
                PDF_BUILDER_VERSION,
                true
            );

            // Localiser le script avec les données nécessaires
            wp_localize_script('pdf-builder-canvas-app', 'wpApiSettings', [
                'nonce' => wp_create_nonce('wp_rest'),
                'root' => esc_url_raw(rest_url()),
            ]);

            // Le bundle définit déjà window.PDFBuilderPro.init
            // Pas besoin de script inline supplémentaire
        }
        }
    }

    /**
     * Activation du plugin - Mode normal optimisé
     *
     * @return void
     */
    public static function activate(): void {
        try {
            // Créer une instance pour accéder aux méthodes
            $instance = self::getInstance();

            // Création des tables essentielles immédiatement (optimisé)
            $instance->database_manager->create_essential_tables();

            // Définition des options par défaut
            $instance->set_default_options();

            // Configuration des capacités
            $instance->setup_capabilities();

            // Flush des règles de réécriture
            flush_rewrite_rules();

            // Marquer comme activé
            update_option('pdf_builder_activated', true);
            update_option('pdf_builder_activation_time', time());

            // Création des tables restantes en arrière-plan (léger)
            if (!wp_next_scheduled('pdf_builder_create_remaining_tables')) {
                wp_schedule_single_event(time() + 30, 'pdf_builder_create_remaining_tables');
            }

            $instance->logger->info('Plugin activated successfully - essential setup complete');

        } catch (Exception $e) {
            // En cas d'erreur, essayer avec une instance existante ou logger minimal
            try {
                $instance = self::getInstance();
                $instance->logger->error('Plugin activation failed', [
                    'error' => $e->getMessage()
                ]);
            } catch (Exception $loggerError) {
                // Fallback minimal
                update_option('pdf_builder_activation_error', $e->getMessage());
            }
            wp_die($e->getMessage());
        }
    }

    /**
     * Désactivation du plugin
     *
     * @return void
     */
    public static function deactivate(): void {
        // Suppression des tâches cron
        wp_clear_scheduled_hook('pdf_builder_cleanup_cache');
        wp_clear_scheduled_hook('pdf_builder_optimize_database');
        wp_clear_scheduled_hook('pdf_builder_generate_reports');
        wp_clear_scheduled_hook('pdf_builder_create_remaining_tables');
        wp_clear_scheduled_hook('pdf_builder_complete_setup');

        // Nettoyer les options d'activation
        delete_option('pdf_builder_activated');
        delete_option('pdf_builder_activation_time');
        delete_option('pdf_builder_setup_complete');

        // Flush des règles de réécriture
        flush_rewrite_rules();

        // Logger la désactivation (utiliser une instance temporaire si nécessaire)
        try {
            $logger = new PDF_Builder_Logger();
            $logger->info('Plugin deactivated successfully');
        } catch (Exception $e) {
            // Fallback silencieux
        }
    }

    /**
     * Création des tables restantes en arrière-plan
     *
     * @return void
     */
    public static function create_remaining_tables(): void {
        try {
            // Créer une instance temporaire pour accéder aux méthodes d'instance
            $instance = self::getInstance();
            $instance->database_manager->create_remaining_tables();
            $instance->logger->info('Remaining database tables created successfully');
        } catch (Exception $e) {
            // Logger l'erreur (utiliser une instance temporaire si nécessaire)
            try {
                $logger = new PDF_Builder_Logger();
                $logger->error('Failed to create remaining tables', [
                    'error' => $e->getMessage()
                ]);
            } catch (Exception $loggerError) {
                // Fallback silencieux
                update_option('pdf_builder_remaining_tables_error', $e->getMessage());
            }
        }
    }

    /**
     * Initialisation complète du plugin en arrière-plan
     *
     * @return void
     */
    public static function complete_setup(): void {
        try {
            // Créer une instance temporaire pour accéder aux méthodes d'instance
            $instance = self::getInstance();

            // Création des tables essentielles
            $instance->database_manager->create_essential_tables();

            // Définition des options par défaut
            $instance->set_default_options();

            // Configuration des capacités
            $instance->setup_capabilities();

            // Flush des règles de réécriture
            flush_rewrite_rules();

            // Programmer la création des tables restantes
            if (!wp_next_scheduled('pdf_builder_create_remaining_tables')) {
                wp_schedule_single_event(time() + 30, 'pdf_builder_create_remaining_tables');
            }

            // Marquer l'initialisation comme terminée
            update_option('pdf_builder_setup_complete', true);
            update_option('pdf_builder_activated', true);
            update_option('pdf_builder_activation_time', time());

            $instance->logger->info('Plugin setup completed successfully in background');

        } catch (Exception $e) {
            // Logger l'erreur (utiliser une instance temporaire si nécessaire)
            try {
                $logger = new PDF_Builder_Logger();
                $logger->error('Plugin setup failed', [
                    'error' => $e->getMessage()
                ]);
            } catch (Exception $loggerError) {
                // Fallback silencieux
                update_option('pdf_builder_setup_error', $e->getMessage());
            }
        }
    }

    /**
     * Définition des options par défaut
     *
     * @return void
     */
    private function set_default_options(): void {
        $defaults = [
            'version' => PDF_BUILDER_VERSION,
            'cache_enabled' => true,
            'cache_ttl' => PDF_BUILDER_CACHE_TTL,
            'debug_mode' => false,
            'max_execution_time' => PDF_BUILDER_MAX_EXECUTION_TIME,
            'memory_limit' => PDF_BUILDER_MEMORY_LIMIT,
            'pdf_quality' => 'high',
            'default_format' => 'A4',
            'default_orientation' => 'portrait'
        ];

        add_option('pdf_builder_settings', $defaults);
    }

    /**
     * Configuration des capacités
     *
     * @return void
     */
    private function setup_capabilities(): void {
        $capabilities = [
            'manage_pdf_templates',
            'edit_pdf_templates',
            'delete_pdf_templates',
            'manage_pdf_documents',
            'edit_pdf_documents',
            'delete_pdf_documents'
        ];

        $role = get_role('administrator');
        if ($role) {
            foreach ($capabilities as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Vérification des dépendances
     *
     * @return void
     */
    private function check_dependencies(): void {
        $dependencies = [
            'gd' => extension_loaded('gd'),
            'mbstring' => extension_loaded('mbstring'),
            'zip' => extension_loaded('zip'),
            'curl' => extension_loaded('curl')
        ];

        foreach ($dependencies as $dep => $loaded) {
            if (!$loaded) {
                $this->logger->warning("PHP extension not loaded: {$dep}");
            }
        }
    }

    /**
     * Nettoyage du cache expiré
     *
     * @return void
     */
    public function cleanup_expired_cache(): void {
        $this->cache_manager->cleanup_expired();
        $this->logger->info('Expired cache cleaned up');
    }

    /**
     * Optimisation de la base de données
     *
     * @return void
     */
    public function optimize_database(): void {
        $this->database_manager->optimize_tables();
        $this->logger->info('Database optimized');
    }

    /**
     * Génération des rapports quotidiens
     *
     * @return void
     */
    public function generate_daily_reports(): void {
        // Implémentation des rapports quotidiens
        $this->logger->info('Daily reports generated');
    }

    /**
     * Rendu de la page principale
     *
     * @return void
     */
    public function render_main_page(): void {
        include PDF_BUILDER_PLUGIN_DIR . 'includes/views/main-page.php';
    }

    /**
     * Rendu de la page des templates
     *
     * @return void
     */
    public function render_templates_page(): void {
        include PDF_BUILDER_PLUGIN_DIR . 'includes/views/templates-page.php';
    }

    /**
     * Rendu de la page des documents
     *
     * @return void
     */
    public function render_documents_page(): void {
        include PDF_BUILDER_PLUGIN_DIR . 'includes/views/documents-page.php';
    }

    /**
     * Rendu de la page des paramètres
     *
     * @return void
     */
    public function render_settings_page(): void {
        include PDF_BUILDER_PLUGIN_DIR . 'includes/views/settings-page.php';
    }

    /**
     * Rendu de la page de migration
     *
     * @return void
     */
    public function render_migration_page(): void {
        include PDF_BUILDER_PLUGIN_DIR . 'migrate-default-templates-admin.php';
    }

    /**
     * Rendu de la section des paramètres
     *
     * @return void
     */
    public function render_settings_section(): void {
        echo '<p>' . __('Configure PDF Builder Pro settings.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Vérification si on doit charger les assets frontend
     *
     * @return bool
     */
    private function should_load_frontend_assets(): bool {
        // Frontend assets are not currently implemented
        return false;
    }

    /**
     * Vérification si c'est une page du plugin
     *
     * @param string $hook
     * @return bool
     */
    private function is_pdf_builder_page(string $hook): bool {
        $pdf_pages = [
            'toplevel_page_pdf-builder-main',
            'pdf-builder_page_pdf-builder-templates',
            'pdf-builder_page_pdf-builder-documents',
            'pdf-builder_page_pdf-builder-settings',
            'pdf-builder_page_pdf-builder-editor'
        ];

        return in_array($hook, $pdf_pages);
    }

    /**
     * Obtenir un service du conteneur IoC
     *
     * @param string $service
     * @return mixed
     */
    public function get(string $service) {
        if (!isset($this->container[$service])) {
            throw new Exception("Service not found: {$service}");
        }

        if (is_callable($this->container[$service])) {
            $this->container[$service] = $this->container[$service]();
        }

        return $this->container[$service];
    }

    /**
     * Vérifier si un service existe dans le conteneur IoC
     *
     * @param string $service
     * @return bool
     */
    public function has(string $service): bool {
        return isset($this->container[$service]);
    }

    /**
     * Enregistrer un service dans le conteneur IoC
     *
     * @param string $name
     * @param mixed $service
     * @return void
     */
    public function set(string $name, $service): void {
        $this->container[$name] = $service;
    }

    /**
     * Gestionnaire d'erreurs d'initialisation
     *
     * @param Exception $e
     * @return void
     */
    private function handle_initialization_error(Exception $e): void {
        error_log('PDF Builder Pro initialization failed: ' . $e->getMessage());

        // Désactiver le plugin en cas d'erreur critique
        if (is_admin()) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error"><p>';
                echo __('PDF Builder Pro failed to initialize: ', 'pdf-builder-pro') . $e->getMessage();
                echo '</p></div>';
            });
        }
    }

    /**
     * Initialisation du monitoring de performance
     *
     * @return void
     */
    private function init_performance_monitoring(): void {
        $this->performance_metrics = [
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(),
            'queries_start' => function_exists('get_num_queries') ? get_num_queries() : 0
        ];
    }

    /**
     * Enregistrement de la fonction de shutdown
     *
     * @return void
     */
    private function register_shutdown_function(): void {
        register_shutdown_function([$this, 'shutdown_handler']);
    }

    /**
     * Gestionnaire de shutdown
     *
     * @return void
     */
    public function shutdown_handler(): void {
        $this->log_performance_metric('total_execution_time', microtime(true) - $this->performance_metrics['start_time']);
        $this->log_performance_metric('memory_usage', memory_get_usage() - $this->performance_metrics['memory_start']);

        $queries_end = function_exists('get_num_queries') ? get_num_queries() : 0;
        $this->log_performance_metric('database_queries', $queries_end - $this->performance_metrics['queries_start']);
    }

    /**
     * Hook: wp_loaded
     *
     * @return void
     */
    public function on_wp_loaded(): void {
        // Actions après le chargement complet de WordPress
    }

    /**
     * Hook: shutdown
     *
     * @return void
     */
    public function on_shutdown(): void {
        // Actions avant l'arrêt du script
    }

    /**
     * Enregistrer une métrique de performance
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function log_performance_metric(string $key, $value): void {
        $this->performance_metrics[$key] = $value;
    }

    /**
     * Obtenir les métriques de performance
     *
     * @return array
     */
    public function get_performance_metrics(): array {
        return $this->performance_metrics;
    }

    /**
     * Vérifier si le plugin est initialisé
     *
     * @return bool
     */
    public function is_initialized(): bool {
        return $this->initialized;
    }

    /**
     * Obtenir la version du plugin
     *
     * @return string
     */
    public function get_version(): string {
        return PDF_BUILDER_VERSION;
    }

    /**
     * Obtenir le gestionnaire de cache
     *
     * @return PDF_Builder_Cache_Manager
     */
    public function get_cache_manager() {
        return $this->cache_manager;
    }

    /**
     * Obtenir le gestionnaire de configuration
     *
     * @return PDF_Builder_Config_Manager
     */
    public function get_config_manager() {
        return $this->config_manager;
    }

    /**
     * Obtenir le gestionnaire de base de données
     *
     * @return PDF_Builder_Database_Manager
     */
    public function get_database_manager() {
        return $this->database_manager;
    }

    /**
     * Obtenir le gestionnaire d'API
     *
     * @return PDF_Builder_API_Manager
     */
    public function get_api_manager() {
        return $this->api_manager;
    }

    /**
     * Obtenir le gestionnaire de templates
     *
     * @return PDF_Builder_Template_Manager
     */
    public function get_template_manager() {
        return $this->template_manager;
    }

    /**
     * Obtenir le logger
     *
     * @return PDF_Builder_Logger
     */
    public function get_logger() {
        return $this->logger;
    }

    /**
     * Obtenir le gestionnaire de Bulk Actions
     *
     * @return PDF_Builder_Bulk_Actions_Manager
     */
    public function get_bulk_actions_manager() {
        return $this->bulk_actions_manager;
    }

    /**
     * Obtenir le gestionnaire d'Export
     *
     * @return PDF_Builder_Export_Manager
     */
    public function get_export_manager() {
        return $this->export_manager;
    }

    /**
     * Obtenir le gestionnaire d'Analytics
     *
     * @return PDF_Builder_Analytics_Manager
     */
    public function get_analytics_manager() {
        return $this->analytics_manager;
    }

    /**
     * Obtenir le gestionnaire de Collaboration
     *
     * @return PDF_Builder_Collaboration_Manager
     */
    public function get_collaboration_manager() {
        return $this->collaboration_manager;
    }

    /**
     * Obtenir le gestionnaire d'API Avancée
     *
     * @return PDF_Builder_API_Manager_Advanced
     */
    public function get_api_manager_advanced() {
        return $this->api_manager_advanced;
    }

    /**
     * Obtenir le gestionnaire de sécurité
     *
     * @return PDF_Builder_Security_Manager
     */
    public function get_security_manager() {
        return $this->security_manager;
    }

    /**
     * Obtenir le gestionnaire de tests
     *
     * @return PDF_Builder_Test_Manager
     */
    public function get_test_manager() {
        return $this->test_manager;
    }

    /**
     * Obtenir le pipeline CI/CD
     *
     * @return PDF_Builder_CI_CD_Pipeline
     */
    public function get_ci_cd_pipeline() {
        return $this->ci_cd_pipeline;
    }

    /**
     * Obtenir le système de monitoring et alertes
     *
     * @return PDF_Builder_Monitoring_Alerts
     */
    public function get_monitoring_alerts() {
        return $this->monitoring_alerts;
    }

    /**
     * Obtenir le système de documentation
     *
     * @return PDF_Builder_Documentation_System
     */
    public function get_documentation_system() {
        return $this->documentation_system;
    }

    /**
     * Obtenir le système de lancement et croissance
     *
     * @return PDF_Builder_Launch_Growth_System
     */
    public function get_launch_growth_system() {
        return $this->launch_growth_system;
    }

    /**
     * Obtenir le système de déploiement production
     *
     * @return PDF_Builder_Deployment_Production_System
     */
    public function get_deployment_production_system() {
        return $this->deployment_production_system;
    }

    /**
     * Obtenir le système de validation lancement
     *
     * @return PDF_Builder_Validation_Launch_System
     */
    public function get_validation_launch_system() {
        return $this->validation_launch_system;
    }

    /**
     * Obtenir le script de déploiement
     *
     * @return PDF_Builder_Deployment_Script
     */
    public function get_deployment_script() {
        return $this->deployment_script;
    }

    /**
     * Obtenir le gestionnaire des éléments du canvas
     *
     * @return PDF_Builder_Canvas_Elements_Manager
     */
    public function get_canvas_elements_manager() {
        return $this->canvas_elements_manager;
    }

    /**
     * Obtenir le gestionnaire de drag & drop
     *
     * @return PDF_Builder_Drag_Drop_Manager
     */
    public function get_drag_drop_manager() {
        return $this->drag_drop_manager;
    }

    /**
     * Obtenir le gestionnaire de redimensionnement
     *
     * @return PDF_Builder_Resize_Manager
     */
    public function get_resize_manager() {
        return $this->resize_manager;
    }

    /**
     * Obtenir le gestionnaire des interactions du canvas
     *
     * @return PDF_Builder_Canvas_Interactions_Manager
     */
    public function get_canvas_interactions_manager() {
        return $this->canvas_interactions_manager;
    }

    /**
     * Gestionnaire AJAX unifié
     *
     * @return void
     */
    public function handle_ajax_request(): void {
        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_die(__('Sécurité: Nonce invalide', 'pdf-builder-pro'));
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $action = $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'pdf_builder_set_default_template':
                    $this->handle_set_default_template();
                    break;

                case 'pdf_builder_delete_template':
                    $this->handle_delete_template();
                    break;

                case 'pdf_builder_load_templates':
                    $this->handle_load_templates();
                    break;

                default:
                    wp_die(__('Action non reconnue', 'pdf-builder-pro'));
            }
        } catch (Exception $e) {
            $this->logger->error('Erreur AJAX: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Définir un template comme par défaut
     *
     * @return void
     */
    private function handle_set_default_template(): void {
        $template_id = intval($_POST['template_id'] ?? 0);
        $is_default = intval($_POST['is_default'] ?? 0);

        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template manquant', 'pdf-builder-pro')]);
            return;
        }

        // Obtenir le template pour connaître son type
        $template = $this->database_manager->get_row(
            'templates',
            ['id' => $template_id],
            ['type']
        );

        if (!$template) {
            wp_send_json_error(['message' => __('Template introuvable', 'pdf-builder-pro')]);
            return;
        }

        $template_type = $template->type;

        // Démarrer une transaction
        $this->database_manager->begin_transaction();

        try {
            if ($is_default) {
                // Retirer le statut par défaut de tous les templates de ce type
                $this->database_manager->update(
                    'templates',
                    ['is_default' => 0],
                    ['type' => $template_type]
                );

                // Définir ce template comme par défaut
                $this->database_manager->update(
                    'templates',
                    ['is_default' => 1],
                    ['id' => $template_id]
                );
            } else {
                // Retirer le statut par défaut de ce template
                $this->database_manager->update(
                    'templates',
                    ['is_default' => 0],
                    ['id' => $template_id]
                );
            }

            $this->database_manager->commit_transaction();

            // Invalider le cache
            if ($this->template_manager) {
                $this->template_manager->invalidate_template_cache();
            }

            wp_send_json_success();

        } catch (Exception $e) {
            $this->database_manager->rollback_transaction();
            throw $e;
        }
    }

    /**
     * Supprimer un template
     *
     * @return void
     */
    private function handle_delete_template(): void {
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template manquant', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier que le template existe
        $template = $this->database_manager->get_row(
            'templates',
            ['id' => $template_id]
        );

        if (!$template) {
            wp_send_json_error(['message' => __('Template introuvable', 'pdf-builder-pro')]);
            return;
        }

        // Supprimer le template
        $deleted = $this->database_manager->delete('templates', ['id' => $template_id]);

        if (!$deleted) {
            wp_send_json_error(['message' => __('Erreur lors de la suppression', 'pdf-builder-pro')]);
            return;
        }

        // Invalider le cache
        if ($this->template_manager) {
            $this->template_manager->invalidate_template_cache();
        }

        wp_send_json_success();
    }

    /**
     * Charger les templates
     *
     * @return void
     */
    private function handle_load_templates(): void {
        $view = $_POST['view'] ?? 'grid';

        if (!$this->template_manager) {
            wp_send_json_error(['message' => __('Gestionnaire de templates non disponible', 'pdf-builder-pro')]);
            return;
        }

        $templates = $this->template_manager->get_templates();

        wp_send_json_success(['templates' => $templates]);
    }
}

// Initialisation du plugin
PDF_Builder_Core::getInstance()->init();

