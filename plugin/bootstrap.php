<?php

/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

// ============================================================================
// ✅ CACHE DÉSACTIVÉ - Force désactiver le cache pour la cohérence des données
// ============================================================================
add_action('plugins_loaded', function() {
    $settings = get_option('pdf_builder_settings', []);
    $settings['cache_enabled'] = false;  // Force désactivé
    $settings['cache_ttl'] = 0;           // Pas de TTL
    update_option('pdf_builder_settings', $settings);
}, 1);

// ============================================================================
// ENDPOINTS AJAX POUR RÉGÉNÉRATION DES POSITIONS
// ============================================================================

error_log('🚀 PDF BUILDER BOOTSTRAP LOADED - Registering hooks...');

// Hook global pour intercepter TOUTES les requêtes AJAX
add_action('wp_ajax_nopriv_pdf_builder_auto_save_template', function() {
    error_log('GLOBAL AJAX INTERCEPT: wp_ajax_nopriv_pdf_builder_auto_save_template called');
});
add_action('wp_ajax_pdf_builder_auto_save_template', function() {
    error_log('GLOBAL AJAX INTERCEPT: wp_ajax_pdf_builder_auto_save_template called - BEFORE ANY OTHER HOOKS');
    // Ne pas traiter ici, laisser les autres hooks gérer
}, 1); // Priorité 1 pour être exécuté en premier

add_action('wp_ajax_pdf_builder_save_template', function() {
    error_log('GLOBAL AJAX INTERCEPT: wp_ajax_pdf_builder_save_template called - BEFORE ANY OTHER HOOKS');
    // Ne pas traiter ici, laisser les autres hooks gérer
}, 1); // Priorité 1 pour être exécuté en premier

// Hook pour intercepter TOUTES les actions AJAX sans distinction
add_action('admin_init', function() {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'pdf_builder_auto_save_template') {
        error_log('ADMIN_INIT INTERCEPT: pdf_builder_auto_save_template detected in admin_init');
    }
});

// Hook très tôt pour intercepter
add_action('init', function() {
    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'pdf_builder_auto_save_template') {
        error_log('INIT INTERCEPT: pdf_builder_auto_save_template detected in init hook');
    }
}, 1);

add_action('wp_ajax_pdf_builder_regenerate_positions', function () {
    error_log('🔍 REGENERATE POSITIONS HOOK CALLED - This proves AJAX routing works');
    error_log('🔍 REGENERATE POSITIONS HOOK CALLED - This proves AJAX routing works');

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $default_positions = [
        'customer_info' => ['x' => 20, 'y' => 20, 'width' => 250, 'height' => 40],
        'company_logo' => ['x' => 550, 'y' => 20, 'width' => 40, 'height' => 40],
        'company_info' => ['x' => 600, 'y' => 20, 'width' => 150, 'height' => 40],
        'document_type' => ['x' => 20, 'y' => 70, 'width' => 150, 'height' => 30],
        'order_number' => ['x' => 200, 'y' => 70, 'width' => 200, 'height' => 30],
        'product_table' => ['x' => 20, 'y' => 120, 'width' => 730, 'height' => 200],
        'line' => ['x' => 20, 'y' => 330, 'width' => 730, 'height' => 1],
        'dynamic-text' => ['x' => 20, 'y' => 350, 'width' => 300, 'height' => 50],
        'mentions' => ['x' => 20, 'y' => 420, 'width' => 730, 'height' => 50],
    ];
// Récupérer tous les templates
    $templates = $wpdb->get_results("SELECT id, template_data FROM $table_templates", ARRAY_A);
    $fixed_count = 0;
    $elements_fixed = 0;
    foreach ($templates as $template) {
        $template_data = json_decode($template['template_data'], true);
        if (is_array($template_data)) {
            $elements = $template_data['elements'] ?? [];
            if (!empty($elements)) {
                $updated_elements = [];
                $position_count = [];
                foreach ($elements as $element) {
                    $type = $element['type'] ?? 'text';
                    $count = $position_count[$type] ?? 0;
                    $position_count[$type] = $count + 1;
                    if (isset($default_positions[$type])) {
                        $pos = $default_positions[$type];
                        $element['x'] = $pos['x'];
                        $element['y'] = $pos['y'] + ($count * 50);
                        $element['width'] = $pos['width'];
                        $element['height'] = $pos['height'];
                    } else {
                        $element['x'] = 20 + ($count * 20);
                        $element['y'] = 20 + ($count * 30);
                        $element['width'] = 200;
                        $element['height'] = 40;
                    }

                    $updated_elements[] = $element;
                    $elements_fixed++;
                }

                $template_data['elements'] = $updated_elements;
                $json_data = wp_json_encode($template_data);
                $wpdb->update(
                    $table_templates,
                    ['template_data' => $json_data],
                    ['id' => $template['id']],
                    ['%s'],
                    ['%d']
                );
                $fixed_count++;
            }
        }
    }

    wp_send_json_success([
        'message' => "Positions régénérées avec succès",
        'count' => $fixed_count,
        'elements_fixed' => $elements_fixed
    ]);
});
add_action('wp_ajax_pdf_builder_preview_positions', function () {

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $default_positions = [
        'customer_info' => ['x' => 20, 'y' => 20, 'width' => 250, 'height' => 40],
        'company_logo' => ['x' => 550, 'y' => 20, 'width' => 40, 'height' => 40],
        'company_info' => ['x' => 600, 'y' => 20, 'width' => 150, 'height' => 40],
        'document_type' => ['x' => 20, 'y' => 70, 'width' => 150, 'height' => 30],
        'order_number' => ['x' => 200, 'y' => 70, 'width' => 200, 'height' => 30],
        'product_table' => ['x' => 20, 'y' => 120, 'width' => 730, 'height' => 200],
        'line' => ['x' => 20, 'y' => 330, 'width' => 730, 'height' => 1],
        'dynamic-text' => ['x' => 20, 'y' => 350, 'width' => 300, 'height' => 50],
        'mentions' => ['x' => 20, 'y' => 420, 'width' => 730, 'height' => 50],
    ];
// Récupérer tous les templates
    $templates = $wpdb->get_results("SELECT id, name, template_data FROM $table_templates", ARRAY_A);
    $preview_data = [];
    foreach ($templates as $template) {
        $template_data = json_decode($template['template_data'], true);
        if (is_array($template_data)) {
            $elements = $template_data['elements'] ?? [];
            if (!empty($elements)) {
                $updated_elements = [];
                $position_count = [];
                foreach ($elements as $element) {
                    $type = $element['type'] ?? 'text';
                    $count = $position_count[$type] ?? 0;
                    $position_count[$type] = $count + 1;
                    if (isset($default_positions[$type])) {
                        $pos = $default_positions[$type];
                        $element['x'] = $pos['x'];
                        $element['y'] = $pos['y'] + ($count * 50);
                        $element['width'] = $pos['width'];
                        $element['height'] = $pos['height'];
                    } else {
                        $element['x'] = 20 + ($count * 20);
                        $element['y'] = 20 + ($count * 30);
                        $element['width'] = 200;
                        $element['height'] = 40;
                    }

                    $updated_elements[] = $element;
                }

                $preview_data[] = [
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'elements' => $updated_elements
                ];
            }
        }
    }

    wp_send_json_success(['templates' => $preview_data]);
});
// Initialiser les variables $_SERVER manquantes pour éviter les Undefined array key errors
// Cela corrige les erreurs strict PHP 8.1+ quand wp-config.php accède à des clés HTTP_* inexistantes
if (!isset($_SERVER['HTTP_B701CD7'])) {
    $_SERVER['HTTP_B701CD7'] = '';
}

// Fonction pour charger le core du plugin
function pdf_builder_load_core()
{

    static $loaded = false;
    if ($loaded) {
        return;
    }

    // Charger le autoloader pour le nouveau système PSR-4
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    }

    // Charger les constantes
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/constants.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/constants.php';
    }

    // Charger le logger en premier (nécessaire pour PDF_Builder_Core)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Logger.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Logger.php';
    }

    // HOTFIX: Charger le correctif pour les notifications avant PDF_Builder_Core
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'hotfix-notifications.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'hotfix-notifications.php';
    }

    // Charger la classe principale PDF_Builder_Core depuis src/
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php';
        error_log('PDF Builder: PDF_Builder_Core.php loaded, class exists: ' . (class_exists('PDF_Builder\\Core\\PdfBuilderCore') ? 'yes' : 'no'));
    } else {
        error_log('PDF Builder: PDF_Builder_Core.php file not found');
    }

    // Charger les managers essentiels depuis src/Managers/ AVANT PdfBuilderAdmin
    $managers = array(
        'PDF_Builder_Cache_Manager.php',
        'PDF_Builder_Canvas_Manager.php',
        'PDF_Builder_Drag_Drop_Manager.php',
        'PDF_Builder_Feature_Manager.php',
        'PDF_Builder_License_Manager.php',
        'PDF_Builder_Logger.php',
        'PDF_Builder_Notification_Manager.php',
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

    // Charger la classe d'administration depuis src/ APRÈS les managers
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
    }

    // Charger le handler AJAX pour les paramètres Canvas
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Canvas_AJAX_Handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/Canvas_AJAX_Handler.php';
    }

    // Charger le gestionnaire de modèles prédéfinis
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'plugin/templates/admin/predefined-templates-manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'plugin/templates/admin/predefined-templates-manager.php';
    }

    // Charger le contrôleur PDF
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php';
    }

    // Charger le handler AJAX d'image de prévisualisation (Phase 3.0)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/preview-image-handler.php';
    }



    // Charger le handler AJAX pour générer les styles des éléments
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/element-styles-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/element-styles-handler.php';
    }

    // Charger l'injecteur de styles pour le canvas (inline)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/canvas-style-injector-inline.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/canvas-style-injector-inline.php';
    }

    // Charger le handler AJAX pour rendre le template en HTML
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/render-template-html.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/render-template-html.php';
    }

    // Charger le handler AJAX pour les templates
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Templates_Ajax.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Templates_Ajax.php';
    }

    $loaded = true;
}

// Fonction pour charger les nouvelles classes WP_PDF_Builder_Pro
function pdf_builder_load_new_classes()
{

    static $new_classes_loaded = false;
    if ($new_classes_loaded) {
        return;
    }

    // Charger les interfaces et classes de données
    $data_classes = [
        'data/DataProviderInterface.php',
        'data/SampleDataProvider.php',
        'data/WooCommerceDataProvider.php'
    ];
    foreach ($data_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les générateurs
    $generator_classes = [
        'generators/BaseGenerator.php',
        'generators/PDFGenerator.php',
        'generators/GeneratorManager.php'
    ];
    foreach ($generator_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les éléments et contrats
    $element_classes = [
        'elements/ElementContracts.php'
    ];
    foreach ($element_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger le core et conventions
    $core_classes = [
        'core/Conventions.php'
    ];
    foreach ($core_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger l'API
    $api_classes = [
        'api/PreviewImageAPI.php',
        'api/MediaDiagnosticAPI.php',
        'api/MediaLibraryFixAPI.php'
    ];
    foreach ($api_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les analytics
    $analytics_classes = [
        'analytics/AnalyticsInterface.php'
    ];
    foreach ($analytics_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les états
    $state_classes = [
        'states/PreviewStateManager.php'
    ];
    foreach ($state_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    $new_classes_loaded = true;
}

// Fonction principale de chargement du bootstrap
function pdf_builder_load_bootstrap()
{
    // Protection globale contre les chargements multiples - plus robuste
    static $bootstrap_loaded = false;
    if ($bootstrap_loaded || (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED)) {
        return;
    }
    $bootstrap_loaded = true;

    error_log('PDF Builder: Bootstrap function called - starting load');

    // CHARGER L'AUTOLOADER POUR LES NOUVELLES CLASSES (WP_PDF_Builder_Pro)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    }

    // Charger la configuration si pas déjà faite
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'config/config.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'config/config.php';
    }

    // Charger le core maintenant que l'autoloader est prêt
    pdf_builder_load_core();
// CHARGER LES NOUVELLES CLASSES WP_PDF_Builder_Pro
    pdf_builder_load_new_classes();
// CHARGER LE TEST D'INTÉGRATION DU CACHE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Cache/cache-integration-test.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Cache/cache-integration-test.php';
    }

    // CHARGER LE HANDLER DE TEST DE LICENCE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php';
    }

    // CHARGER LE HANDLER D'EXPIRATION DE LICENCE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php';
        \PDFBuilderPro\License\License_Expiration_Handler::init();
    }

    // CHARGER LE GESTIONNAIRE DES LIMITES DE SÉCURITÉ
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Security/Security_Limits_Handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Security/Security_Limits_Handler.php';
    }

    // CHARGER LE GESTIONNAIRE DE RATE LIMITING
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Security/Rate_Limiter.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Security/Rate_Limiter.php';
    }

    // CHARGER LE GESTIONNAIRE DES RÔLES ET PERMISSIONS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Security/Role_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Security/Role_Manager.php';
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE CANVAS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php';
        \WP_PDF_Builder_Pro\Canvas\Canvas_Manager::get_instance();
    }

    // ENREGISTRER LES HANDLERS AJAX POUR LE CANVAS
    if (class_exists('WP_PDF_Builder_Pro\\Admin\\Canvas_AJAX_Handler')) {
        \WP_PDF_Builder_Pro\Admin\Canvas_AJAX_Handler::register_hooks();
    }

    // INITIALISER LE GESTIONNAIRE DE NOTIFICATIONS
    if (class_exists('PDF_Builder_Notification_Manager')) {
        PDF_Builder_Notification_Manager::get_instance();
    }

    // INITIALISER LES HOOKS WOOCOMMERCE (Phase 1.6.1)
    if (class_exists('PDF_Builder\\Cache\\WooCommerceCache')) {
        \PDF_Builder\Cache\WooCommerceCache::setupAutoInvalidation();
    }

    // NOTE: PreviewImageAPI est instanciée dans pdf_builder_handle_preview_ajax()
    // dans pdf-builder-pro.php, pas ici, pour éviter les conflits

    // CHARGER LES HOOKS AJAX ESSENTIELS TOUJOURS, MÊME EN MODE FALLBACK
    // Cela garantit que les fonctionnalités de sauvegarde/chargement fonctionnent
    pdf_builder_register_essential_ajax_hooks();

    // Vérification que les classes essentielles sont chargées
    if (class_exists('PDF_Builder\\Core\\PdfBuilderCore')) {
        error_log('PDF Builder: PdfBuilderCore class exists');
        $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
        if (method_exists($core, 'init')) {
            $core->init();
            error_log('PDF Builder: Core initialized');
        }

        // DEBUG: Vérifier si la classe existe avant de l'utiliser
        $class_exists = class_exists('PDF_Builder\\Admin\\PdfBuilderAdmin');
        error_log('PDF Builder: Class PDF_Builder\Admin\PdfBuilderAdmin exists: ' . ($class_exists ? 'YES' : 'NO'));

        if (!$class_exists) {
            // Essayer de charger manuellement la classe
            $admin_file = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
            error_log('PDF Builder: Trying to load admin file: ' . $admin_file);
            if (file_exists($admin_file)) {
                error_log('PDF Builder: Admin file exists, requiring it...');
                require_once $admin_file;
                $class_exists_after = class_exists('PDF_Builder\\Admin\\PdfBuilderAdmin');
                error_log('PDF Builder: Class exists after manual load: ' . ($class_exists_after ? 'YES' : 'NO'));
            } else {
                error_log('PDF Builder: Admin file does not exist: ' . $admin_file);
            }
        }

        // Initialiser l'interface d'administration dans l'admin OU lors d'AJAX pour nos actions
        $is_admin_or_pdf_ajax = is_admin() || (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') !== false);

        error_log('PDF Builder: Checking admin initialization - is_admin: ' . (is_admin() ? 'true' : 'false') . ', is_ajax: ' . (wp_doing_ajax() ? 'true' : 'false') . ', pdf_ajax_allowed: ' . ($is_admin_or_pdf_ajax ? 'true' : 'false'));

        if ($is_admin_or_pdf_ajax && class_exists('PDF_Builder\\Admin\\PdfBuilderAdmin')) {
            error_log('PDF Builder: PdfBuilderAdmin class exists, creating instance');
            try {
                $admin = \PDF_Builder\Admin\PdfBuilderAdmin::getInstance($core);
                error_log('PDF Builder: Admin class loaded successfully');
            } catch (Exception $e) {
                error_log('PDF Builder: Error creating PdfBuilderAdmin instance: ' . $e->getMessage());
                // Fallback en cas d'erreur
                add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
            }
        } elseif (wp_doing_ajax()) {
            error_log('PDF Builder: Skipping admin load during non-PDF AJAX call');
        } else {
            // Fallback: enregistrer un menu simple si la classe principale n'est pas disponible
            error_log('PDF Builder: Using fallback admin menu - class not found or not in admin');
            add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
        }
    } else {
        error_log('PDF Builder: PdfBuilderCore class does not exist');
    }

    // Marquer comme chargé globalement
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

// Fonction simple pour enregistrer le menu admin
function pdf_builder_register_admin_menu_simple()
{
    error_log('PDF Builder: Registering simple admin menu');

    add_menu_page(
        'PDF Builder Pro',
        'PDF Builder',
        'read',
        'pdf-builder-pro',
        'pdf_builder_admin_page_simple',
        'dashicons-pdf',
        30
    );
    add_submenu_page(
        'pdf-builder-pro',
        __('Templates', 'pdf-builder-pro'),
        __('Templates', 'pdf-builder-pro'),
        'read',
        'pdf-builder-templates',
        'pdf_builder_templates_page_simple'
    );
}

// Callbacks simples
function pdf_builder_admin_page_simple()
{

    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>PDF Builder Pro</h1><p>Page principale en cours de développement.</p></div>';
}

function pdf_builder_templates_page_simple()
{

    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>Templates</h1><p>Page templates en cours de développement.</p></div>';
}

// Fonction pour enregistrer les hooks AJAX essentiels
function pdf_builder_register_essential_ajax_hooks()
{
    error_log('PDF Builder: Registering essential AJAX hooks...');

    // Charger les classes nécessaires pour les handlers AJAX
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php';
        error_log('PDF Builder: Template Manager loaded');
    }

    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
        error_log('PDF Builder: Admin class loaded');
    }

    // Créer une instance du template manager pour les handlers AJAX
    $template_manager = null;
    if (class_exists('PDF_Builder_Pro\\Managers\\PdfBuilderTemplateManager')) {
        $template_manager = new PDF_Builder_Pro\Managers\PdfBuilderTemplateManager();
        error_log('PDF Builder: Template Manager instance created');
    } else {
        error_log('PDF Builder: Template Manager class not found, using fallback handlers');
    }

    // Enregistrer les hooks AJAX essentiels
    add_action('wp_ajax_pdf_builder_save_template', function() use ($template_manager) {
        error_log('PDF Builder: AJAX save_template hook triggered');
        if ($template_manager && method_exists($template_manager, 'ajaxSaveTemplateV3')) {
            $template_manager->ajaxSaveTemplateV3();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_save_template();
        }
    });

    add_action('wp_ajax_pdf_builder_load_template', function() use ($template_manager) {
        error_log('PDF Builder: AJAX load_template hook triggered');
        if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
            $template_manager->ajaxLoadTemplate();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_load_template();
        }
    });

    add_action('wp_ajax_pdf_builder_auto_save_template', function() use ($template_manager) {
        error_log('PDF Builder: AJAX auto_save_template hook triggered');
        if ($template_manager && method_exists($template_manager, 'ajax_auto_save_template')) {
            $template_manager->ajax_auto_save_template();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_auto_save_template();
        }
    });

    error_log('PDF Builder: Essential AJAX hooks registered successfully');
}

// Fonction de chargement différé (maintenant vide car les hooks sont enregistrés au bootstrap)
function pdf_builder_load_core_when_needed()
{
    // Les hooks essentiels sont déjà enregistrés dans pdf_builder_load_bootstrap()
    // Cette fonction est gardée pour compatibilité
}

// Handlers AJAX de fallback
function pdf_builder_fallback_ajax_save_template()
{
    error_log('PDF Builder: Using fallback save template handler');

    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Récupérer les données
    $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

    error_log('🔍 [PHP SAVE] RECEIVED - Template ID: ' . $template_id);
    error_log('🔍 [PHP SAVE] RECEIVED - Raw template_data length: ' . strlen($template_data));
    error_log('🔍 [PHP SAVE] RECEIVED - First 500 chars: ' . substr($template_data, 0, 500));

    if (empty($template_data) || !$template_id) {
        wp_send_json_error('Données manquantes');
        return;
    }

    // Décoder le JSON pour vérifier les données
    $decoded_data = json_decode($template_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('🔍 [PHP SAVE] JSON decode error: ' . json_last_error_msg());
        wp_send_json_error('Données JSON invalides');
        return;
    }

    error_log('🔍 [PHP SAVE] Decoded data structure: ' . print_r(array_keys($decoded_data), true));

    // Log détaillé des éléments order_number
    if (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
        error_log('🔍 [PHP SAVE] Elements array found with ' . count($decoded_data['elements']) . ' elements');

        $order_elements = array_filter($decoded_data['elements'], function($el) {
            return isset($el['type']) && $el['type'] === 'order_number';
        });
        error_log('🔍 [PHP SAVE] Order number elements count: ' . count($order_elements));
        foreach ($order_elements as $index => $element) {
            error_log('🔍 [PHP SAVE] Order element ' . $index . ': id=' .
                (isset($element['id']) ? $element['id'] : 'NO_ID') .
                ', contentAlign=' . (isset($element['contentAlign']) ? $element['contentAlign'] : 'NOT_SET') .
                ', labelPosition=' . (isset($element['labelPosition']) ? $element['labelPosition'] : 'NOT_SET') .
                ', all_keys=' . implode(',', array_keys($element)));
        }
    } else {
        error_log('🔍 [PHP SAVE] No elements array found in decoded data');
    }

    // Sauvegarder dans la base de données
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';

    $result = $wpdb->update(
        $table,
        ['template_data' => $template_data, 'updated_at' => current_time('mysql')],
        ['id' => $template_id],
        ['%s', '%s'],
        ['%d']
    );

    error_log('🔍 [PHP SAVE] Database update result: ' . ($result !== false ? 'SUCCESS' : 'FAILED'));

    if ($result !== false) {
        wp_send_json_success(['message' => 'Template sauvegardé avec succès']);
    } else {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }
}

function pdf_builder_fallback_ajax_load_template()
{
    error_log('PDF Builder: Using fallback load template handler');

    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    error_log('🔍 [PHP LOAD] Template ID to load: ' . $template_id);

    if (!$template_id) {
        wp_send_json_error('ID de template manquant');
        return;
    }

    // Charger depuis la base de données
    global $wpdb;
    $table = $wpdb->prefix . 'pdf_builder_templates';

    $template = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id),
        ARRAY_A
    );

    if ($template) {
        error_log('🔍 [PHP LOAD] Raw template_data from DB length: ' . strlen($template['template_data']));
        error_log('🔍 [PHP LOAD] First 200 chars of raw data: ' . substr($template['template_data'], 0, 200));

        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('🔍 [PHP LOAD] JSON decode error: ' . json_last_error_msg());
            wp_send_json_error('Erreur de décodage JSON');
            return;
        }

        error_log('🔍 [PHP LOAD] Decoded data structure: ' . print_r(array_keys($template_data), true));

        // Log détaillé des éléments order_number chargés
        if (isset($template_data['elements']) && is_array($template_data['elements'])) {
            error_log('🔍 [PHP LOAD] Elements array found with ' . count($template_data['elements']) . ' elements');

            $order_elements = array_filter($template_data['elements'], function($el) {
                return isset($el['type']) && $el['type'] === 'order_number';
            });
            error_log('🔍 [PHP LOAD] Order number elements loaded: ' . count($order_elements));
            foreach ($order_elements as $index => $element) {
                error_log('🔍 [PHP LOAD] Order element ' . $index . ': id=' .
                    (isset($element['id']) ? $element['id'] : 'NO_ID') .
                    ', contentAlign=' . (isset($element['contentAlign']) ? $element['contentAlign'] : 'NOT_SET') .
                    ', labelPosition=' . (isset($element['labelPosition']) ? $element['labelPosition'] : 'NOT_SET') .
                    ', all_keys=' . implode(',', array_keys($element)));
            }
        } else {
            error_log('🔍 [PHP LOAD] No elements array found in loaded data');
        }

        wp_send_json_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);
    } else {
        error_log('🔍 [PHP LOAD] Template not found in database');
        wp_send_json_error('Template non trouvé');
    }
}

function pdf_builder_fallback_ajax_auto_save_template()
{
    error_log('PDF Builder: Using fallback auto-save template handler');

    // Même logique que save_template mais pour l'auto-save
    pdf_builder_fallback_ajax_save_template();
}
{

    static $core_loaded = false;
    if ($core_loaded) {
        return;
    }

    // Détection ultra-rapide
    $load_core = false;
    if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') === 0) {
        $load_core = true;
    } elseif (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') === 0) {
        $load_core = true;
    } elseif (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action'])) {
    // Charger pour les appels AJAX du PDF Builder
        $pdf_builder_ajax_actions = [
            'pdf_builder_save_template',
            'pdf_builder_load_template',
            'pdf_builder_auto_save_template',
            'pdf_builder_flush_rest_cache'
        ];
        if (in_array($_REQUEST['action'], $pdf_builder_ajax_actions)) {
            $load_core = true;
        }
    }

    if ($load_core) {
        pdf_builder_load_core();
        if (class_exists('PDF_Builder\Core\PdfBuilderCore')) {
            try {
                \PDF_Builder\Core\PdfBuilderCore::getInstance()->init();
                $core_loaded = true;
            } catch (Exception $e) {
    // Ne pas utiliser wp_die() car cela peut causer une erreur 500 en AJAX
                // wp_die('Plugin initialization failed: ' . esc_html($e->getMessage()));
                return; // Sortir sans charger le core
            }
        }
    }
}

// Enregistrer le menu admin au bon moment
/*
function pdf_builder_register_admin_menu() {
    pdf_builder_ensure_admin_menu();
}
*/

// Fallback direct pour le menu admin - seulement si on est dans l'admin
/*
function pdf_builder_ensure_admin_menu() {
    // Ne rien faire si on n'est pas dans l'admin
    if (!is_admin()) {
        return;
    }

    // Définir les callbacks d'abord
    if (!function_exists('pdf_builder_main_page_callback')) {
        // Fonction callback pour la page principale
        function pdf_builder_main_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_main_page();
        }

        // Fonction callback pour la page templates
        function pdf_builder_templates_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_templates_page();
        }

        // Fonction callback pour la page settings
        function pdf_builder_settings_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_settings_page();
        }

        // Fonction callback pour la page modèles prédéfinis
        function pdf_builder_predefined_templates_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            // Le gestionnaire est auto-instancié, on appelle juste sa méthode de rendu
            if (class_exists('PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager')) {
                $manager = new \PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager();
                $manager->renderAdminPage();
            } else {
                echo '<div class="wrap"><h1>Erreur</h1><p>Le gestionnaire de modèles prédéfinis n\'est pas '
                    . 'disponible.</p></div>';
            }
        }

        // Fonction callback pour la page React Editor
        function pdf_builder_react_editor_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_react_editor_page();
        }
    }

    global $menu;
    $menu_exists = false;

    // Vérifier que $menu est défini et est un tableau
    if (!isset($menu) || !is_array($menu)) {
        $menu = array();
    }

    foreach ($menu as $item) {
        if (isset($item[2]) && $item[2] === 'pdf-builder-main') {
            $menu_exists = true;
            break;
        }
    }

    if (!$menu_exists) {
        add_menu_page(
            'PDF Builder Pro',
            'PDF Builder',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-main',
            'pdf_builder_main_page_callback',
            'dashicons-pdf',
            30
        );

        add_submenu_page(
            'pdf-builder-main',
            'Templates',
            'Templates',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-templates',
            'pdf_builder_templates_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Settings',
            'Settings',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-settings',
            'pdf_builder_settings_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            '📝 Modèles Prédéfinis',
            '📝 Modèles Prédéfinis',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-predefined-templates',
            'pdf_builder_predefined_templates_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            'React Editor',
            'React Editor',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-core-editor',
            'pdf_builder_react_editor_page_callback'
        );
    }
}

/**
 * Initialiser les paramètres par défaut du canvas
 */
function pdf_builder_init_canvas_defaults()
{

    // Paramètres par défaut du canvas
    $defaults = [
        'canvas_element_borders_enabled' => true,
        'canvas_border_width' => 1,
        'canvas_border_color' => '#007cba',
        'canvas_border_spacing' => 2,
        'canvas_resize_handles_enabled' => true,
        'canvas_handle_size' => 8,
        'canvas_handle_color' => '#007cba',
        'canvas_handle_hover_color' => '#ffffff'
    ];
// Initialiser chaque paramètre seulement s'il n'existe pas déjà
    foreach ($defaults as $option => $default_value) {
        if (get_option($option) === false) {
            add_option($option, $default_value);
        }
    }
}

/**
 * Fonctions de fallback AJAX pour s'assurer que les actions sont toujours disponibles
 */


/**
 * AJAX handler pour obtenir un nonce frais
 */
function pdf_builder_ajax_get_fresh_nonce()
{

    // Vérifier les permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }

    // Générer un nouveau nonce pour la génération PDF
    $nonce = wp_create_nonce('pdf_builder_nonce');
// Retourner le nonce
    wp_send_json_success(array(
        'nonce' => $nonce,
        'timestamp' => time()
    ));
}

/**
 * AJAX handler pour récupérer un template par ID
 */
/**
 * Charge un template PDF Builder via AJAX
 *
 * Endpoint: /wp-admin/admin-ajax.php?action=pdf_builder_get_template
 * Méthode: GET
 *
 * Paramètres:
 * - template_id (int): ID du template à charger
 * - nonce (string): Token de sécurité WordPress
 *
 * Réponse: JSON {success: bool, data: {id, name, elements, canvas, ...}}
 *
 * @since 1.0.0
 * @uses PDF_Builder_Cache_Manager Pour cacher les templates fréquemment utilisés
 */
function pdf_builder_ajax_get_template()
{

    // Vérifier le nonce de sécurité
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions utilisateur
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    // Valider et récupérer l'ID du template
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
    if (!$template_id || $template_id < 1) {
        wp_send_json_error(__('ID du template manquant ou invalide.', 'pdf-builder-pro'));
        return;
    }

    // ✅ ÉTAPE 1: Cache DÉSACTIVÉ - toujours charger les données fraîches
    // Performance: toujours charger depuis la DB pour garantir la cohérence des données
    // Les données custom comme contentAlign et labelPosition nécessitent toujours les données fraîches
    
    // ✅ ÉTAPE 2: Récupérer le template depuis la table personnalisée
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
// 🔍 DEBUG: Log what we got from DB
    error_log('🔍 [GET TEMPLATE] Template from DB: ID=' . $template_id . ', Data size: '
        . (isset($template['template_data']) ? strlen($template['template_data']) : 'NULL'));
    if ($template && isset($template['template_data'])) {
        error_log('🔍 [GET TEMPLATE] First 200 chars: ' . substr($template['template_data'], 0, 200));
    }

    // Si le template n'est pas trouvé dans la table personnalisée, chercher dans wp_posts
    if (!$template) {
        $post = get_post($template_id);
        if ($post && $post->post_type === 'pdf_template') {
        // Récupérer les métadonnées du template
            $template_data_raw = get_post_meta($post->ID, '_pdf_template_data', true);
            if (!empty($template_data_raw)) {
        // Créer un objet template compatible avec le format attendu
                $template = array(
                    'id' => $post->ID,
                    'name' => $post->post_title,
                    'template_data' => $template_data_raw,
                    'created_at' => $post->post_date,
                    'updated_at' => $post->post_modified
                );
            } else {
            }
        } else {
        }
    }

    if (!$template) {
        wp_send_json_error(__('Template non trouvé.', 'pdf-builder-pro'));
        return;
    }

    // Décoder les données JSON du template
    $template_data = json_decode($template['template_data'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(__('Erreur lors du décodage des données du template.', 'pdf-builder-pro'));
        return;
    }

    // Gérer les différents formats de données
    $elements = [];
    $canvas = null;
// Vérifier les différents formats
    if (is_array($template_data)) {
        if (isset($template_data['elements'])) {
        // Nouveau format : {"elements": [...], "canvas": {...}}
            $elements = $template_data['elements'];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } elseif (
            isset($template_data['pages']) && is_array($template_data['pages'])
            && !empty($template_data['pages'])
        ) {
        // Format avec pages : {"pages": [{"elements": [...]}], "canvas": {...}}
            $elements = $template_data['pages'][0]['elements'] ?? [];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } else {
        // Ancien format : directement un tableau d'éléments
            $elements = $template_data;
            $canvas = null;
        }
    } else {
        wp_send_json_error(__('Format de données du template invalide.', 'pdf-builder-pro'));
        return;
    }

    // Traiter les éléments (même logique pour les deux formats)
    if (is_string($elements)) {
// D'abord supprimer les slashes d'échappement, puis décoder
        $unescaped_elements = stripslashes($elements);
        $decoded_elements = json_decode($unescaped_elements, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $elements = $decoded_elements;
        } else {
            $elements = [];
        }
    } elseif (!is_array($elements)) {
    // Si ce n'est ni un array ni une string, initialiser comme array vide

        $elements = [];
    }

    // Traiter le canvas si présent
    if ($canvas !== null) {
        if (is_string($canvas)) {
            $unescaped_canvas = stripslashes($canvas);
            $decoded_canvas = json_decode($unescaped_canvas, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $canvas = $decoded_canvas;
            } else {
                $canvas = null;
            }
        } elseif (!is_array($canvas) && !is_null($canvas)) {
            $canvas = null;
        }
    }

    // Vérifier que elements est défini (peut être un array vide pour un nouveau template)
    if (!isset($elements)) {
        wp_send_json_error(__('Données du template incomplètes.', 'pdf-builder-pro'));
        return;
    }

    // Transformer les éléments dans le format attendu par React
    $transformed_elements = [];
    foreach ($elements as $element) {
        $transformed_element = [];
    // Copier les propriétés de base
        if (isset($element['id'])) {
            $transformed_element['id'] = $element['id'];
        }
        if (isset($element['type'])) {
            $transformed_element['type'] = $element['type'];
        }
        if (isset($element['content'])) {
            $transformed_element['content'] = $element['content'];
        }

        // Gérer les positions - deux formats possibles
        // Format imbriqué: position.x ou format plat: x
        if (isset($element['position']['x'])) {
            $transformed_element['x'] = (int)$element['position']['x'];
        } elseif (isset($element['x'])) {
            $transformed_element['x'] = (int)$element['x'];
        }

        if (isset($element['position']['y'])) {
            $transformed_element['y'] = (int)$element['position']['y'];
        } elseif (isset($element['y'])) {
            $transformed_element['y'] = (int)$element['y'];
        }

        // Gérer les dimensions - deux formats possibles
        // Format imbriqué: size.width ou format plat: width
        if (isset($element['size']['width'])) {
            $transformed_element['width'] = (int)$element['size']['width'];
        } elseif (isset($element['width'])) {
            $transformed_element['width'] = (int)$element['width'];
        }

        if (isset($element['size']['height'])) {
            $transformed_element['height'] = (int)$element['size']['height'];
        } elseif (isset($element['height'])) {
            $transformed_element['height'] = (int)$element['height'];
        }

        // Copier les autres propriétés de style directement
        $style_properties = ['fontSize', 'fontWeight', 'color', 'textAlign', 'verticalAlign',
            'backgroundColor', 'borderColor', 'borderWidth', 'borderStyle', 'rotation', 'opacity'];
    // Format imbriqué: style.fontSize ou format plat: fontSize
        if (isset($element['style']) && is_array($element['style'])) {
            foreach ($style_properties as $prop) {
                if (isset($element['style'][$prop])) {
                    if (in_array($prop, ['fontSize', 'borderWidth', 'rotation', 'opacity'])) {
                        $transformed_element[$prop] = is_numeric($element['style'][$prop])
                            ? (int)$element['style'][$prop] : $element['style'][$prop];
                    } else {
                            $transformed_element[$prop] = $element['style'][$prop];
                    }
                }
            }
        } else {
        // Format plat
            foreach ($style_properties as $prop) {
                if (isset($element[$prop])) {
                    if (in_array($prop, ['fontSize', 'borderWidth', 'rotation', 'opacity'])) {
                        $transformed_element[$prop] = is_numeric($element[$prop])
                            ? (int)$element[$prop] : $element[$prop];
                    } else {
                        $transformed_element[$prop] = $element[$prop];
                    }
                }
            }
        }

        // Pour les éléments text, utiliser content comme text
        if (isset($element['type']) && $element['type'] === 'text' && isset($element['content'])) {
            $transformed_element['text'] = $element['content'];
        }

        // Copier d'autres propriétés utiles si présentes
        $copy_properties = ['visible', 'locked', 'zIndex', 'name', 'src', 'logoUrl', 'defaultSrc',
            'alignment', 'borderRadius'];
        foreach ($copy_properties as $prop) {
            if (isset($element[$prop])) {
                $transformed_element[$prop] = $element[$prop];
            }
        }

        // Propriétés par défaut pour tous les éléments (seulement si non défini)
        if (!isset($transformed_element['x'])) {
            $transformed_element['x'] = 0;
        }
        if (!isset($transformed_element['y'])) {
            $transformed_element['y'] = 0;
        }
        if (!isset($transformed_element['width'])) {
            $transformed_element['width'] = 100;
        }
        if (!isset($transformed_element['height'])) {
            $transformed_element['height'] = 50;
        }
        if (!isset($transformed_element['visible'])) {
            $transformed_element['visible'] = true;
        }
        if (!isset($transformed_element['locked'])) {
            $transformed_element['locked'] = false;
        }

        $transformed_elements[] = $transformed_element;
    }

    $elements = $transformed_elements;
// 🏷️ Enrichir les logos company_logo avec src si absent
    error_log('🔍 [GET TEMPLATE] Starting logo enrichment for ' . count($elements) . ' elements');
    foreach ($elements as &$el) {
        if (isset($el['type']) && $el['type'] === 'company_logo') {
            error_log('🔍 [GET TEMPLATE] Found company_logo element: src='
                . (isset($el['src']) ? $el['src'] : 'NULL') . ', logoUrl='
                . (isset($el['logoUrl']) ? $el['logoUrl'] : 'NULL'));
        // Si src est vide ou absent, chercher le logo WordPress
            if (empty($el['src']) && empty($el['logoUrl'])) {
                error_log('🔍 [GET TEMPLATE] Logo is empty, trying to enrich...');
// Essayer d'obtenir le logo du site WordPress
                $custom_logo_id = get_theme_mod('custom_logo');
                error_log('🔍 [GET TEMPLATE] custom_logo theme_mod = '
                    . ($custom_logo_id ? $custom_logo_id : 'NULL'));
                if ($custom_logo_id) {
                    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                    error_log('🔍 [GET TEMPLATE] wp_get_attachment_image_url returned: '
                        . ($logo_url ? $logo_url : 'NULL'));
                    if ($logo_url) {
                        $el['src'] = $logo_url;
                        error_log('✅ [GET TEMPLATE] Logo enrichi avec WordPress site logo: ' . $logo_url);
                    }
                } else {
                // Sinon chercher le logo dans les options WordPress
                    $site_logo_id = get_option('site_logo');
                    error_log('🔍 [GET TEMPLATE] site_logo option = '
                        . ($site_logo_id ? $site_logo_id : 'NULL'));
                    if ($site_logo_id) {
                        $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
                        error_log('🔍 [GET TEMPLATE] wp_get_attachment_image_url returned: '
                            . ($logo_url ? $logo_url : 'NULL'));
                        if ($logo_url) {
                                    $el['src'] = $logo_url;
                                    error_log('✅ [GET TEMPLATE] Logo enrichi avec site_logo: ' . $logo_url);
                        }
                    } else {
                        error_log('⚠️ [GET TEMPLATE] No site_logo found in WordPress options');
                        $test_logo_url = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAAAyCAYAAACsbzlmAAAAQUlEQVR4nO3XMQEAMAgEsNCdw98JXDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwM+NhdAJRq3M4hAXZAAAAAElFTkSuQmCC';
                        $el['src'] = $test_logo_url;
                        error_log('⚠️ [GET TEMPLATE] Using fallback test logo: ' . $test_logo_url);
                    }
                }
            } else {
                error_log('ℹ️ [GET TEMPLATE] Logo already has src/logoUrl, skipping enrichment');
            }
        }
    }
    unset($el);
// 🔍 DEBUG: Log what we're returning
    error_log('✅ [GET TEMPLATE] Returning: elements=' . count($elements)
        . ', canvas=' . (isset($canvas) ? 'YES' : 'NO'));
    if (count($elements) > 0) {
        error_log('✅ [GET TEMPLATE] First element: ' . json_encode($elements[0]));
        // Log company_logo specifically
        foreach ($elements as $idx => $el) {
            if (isset($el['type']) && $el['type'] === 'company_logo') {
                error_log('✅ [GET TEMPLATE] company_logo[' . $idx . '] returned: src=' . (isset($el['src']) ? $el['src'] : 'MISSING'));
            }
        }
    }

    // ✅ ÉTAPE 3: Cache DISABLED for now - always fresh from DB
    // Uncomment below once flash issue is fully resolved
    // set_transient($cache_key, $cache_data, 3600);

    $cache_data = array(
        'id' => $template['id'],
        'name' => $template['name'],
        'elements' => $elements,
        'canvas' => $canvas,
        'created_at' => $template['created_at'],
        'updated_at' => $template['updated_at']
    );
    wp_send_json_success($cache_data);
}



/**
 * Fonction utilitaire pour corriger/régénérer les éléments avec des positions correctes
 */
function pdf_builder_regenerate_element_positions($elements)
{

    $default_positions = [
        'customer_info' => ['x' => 20, 'y' => 20, 'width' => 250, 'height' => 40],
        'company_logo' => ['x' => 550, 'y' => 20, 'width' => 40, 'height' => 40],
        'company_info' => ['x' => 600, 'y' => 20, 'width' => 150, 'height' => 40],
        'document_type' => ['x' => 20, 'y' => 70, 'width' => 150, 'height' => 30],
        'order_number' => ['x' => 200, 'y' => 70, 'width' => 200, 'height' => 30],
        'product_table' => ['x' => 20, 'y' => 120, 'width' => 730, 'height' => 200],
        'line' => ['x' => 20, 'y' => 330, 'width' => 730, 'height' => 1],
        'dynamic-text' => ['x' => 20, 'y' => 350, 'width' => 300, 'height' => 50],
        'mentions' => ['x' => 20, 'y' => 420, 'width' => 730, 'height' => 50],
    ];
    $updated = [];
    $y_offset = 0;
    $position_count = [];
    foreach ($elements as $element) {
        $type = $element['type'] ?? 'text';
        $count = $position_count[$type] ?? 0;
        $position_count[$type] = $count + 1;
    // Utiliser les positions par défaut si disponibles
        if (isset($default_positions[$type])) {
            $pos = $default_positions[$type];
            $element['x'] = $pos['x'];
            $element['y'] = $pos['y'] + ($count * 50);
// Décalage pour les doublons
            $element['width'] = $pos['width'];
            $element['height'] = $pos['height'];
        } else {
        // Générer une position par défaut
            $element['x'] = 20 + ($count * 20);
            $element['y'] = 20 + ($count * 30);
            $element['width'] = 200;
            $element['height'] = 40;
        }

        $updated[] = $element;
    }

    return $updated;
}

/**
 * AJAX endpoint pour régénérer les positions des éléments (debug/fix)
 */
function pdf_builder_ajax_regenerate_positions()
{

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Récupérer tous les templates
    $templates = $wpdb->get_results("SELECT id, template_data FROM $table_templates", ARRAY_A);
    $fixed_count = 0;
    foreach ($templates as $template) {
        $template_data = json_decode($template['template_data'], true);
        if (is_array($template_data)) {
            $elements = $template_data['elements'] ?? [];
            if (!empty($elements)) {
                // Régénérer les positions
                $fixed_elements = pdf_builder_regenerate_element_positions($elements);
                // Mettre à jour
                $template_data['elements'] = $fixed_elements;
                $json_data = wp_json_encode($template_data);
                $wpdb->update(
                    $table_templates,
                    ['template_data' => $json_data],
                    ['id' => $template['id']],
                    ['%s'],
                    ['%d']
                );
                $fixed_count++;
                error_log("DEBUG: Fixed template ID {$template['id']}");
            }
        }
    }

    wp_send_json_success([
        'message' => "Positions régénérées pour $fixed_count templates",
        'count' => $fixed_count
    ]);
}

add_action('wp_ajax_pdf_builder_regenerate_positions', 'pdf_builder_ajax_regenerate_positions');

/**
 * Sauvegarde un template PDF Builder via AJAX
 *
 * Endpoint: /wp-admin/admin-ajax.php?action=pdf_builder_save_template
 * Méthode: POST
 * Type données: FormData
 *
 * Paramètres POST:
 * - template_id (int): ID du template (0 = nouveau)
 * - template_name (string): Nom du template
 * - elements (JSON): Array des éléments du canvas
 * - canvas (JSON): Objet configuration du canvas (zoom, pan, etc)
 * - nonce (string): Token de sécurité WordPress
 *
 * Réponse: JSON {success: bool, data: {id, name, timestamp, elementCount, message}}
 *
 * Sécurité:
 * - ✅ Nonce verification (CSRF protection)
 * - ✅ Permission check (current_user_can)
 * - ✅ wp_unslash & sanitization
 * - ✅ JSON validation & error handling
 *
 * Performance:
 * - ✅ Cache invalidation after save
 * - ✅ Logging de tous les évenements
 * - ✅ Early returns sur erreurs
 *
 * @since 1.0.0
 * @uses PDF_Builder_Canvas_Save_Logger Pour traçabilité complète
 * @uses wp_json_encode Pour sérialisation sécurisée
 */
function pdf_builder_ajax_save_template()
{

    // Initialiser le logger pour traçabilité complète
    if (!class_exists('PDF_Builder_Canvas_Save_Logger')) {
        require_once plugin_dir_path(__FILE__) . 'src/Managers/PDF_Builder_Canvas_Save_Logger.php';
    }
    $logger = PDF_Builder_Canvas_Save_Logger::get_instance();
// Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    // Récupérer les données
    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
    $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
// Logger le début
    $logger->log_save_start($template_id, $template_name);
// Les données elements et canvas arrivent comme JSON strings depuis React
    $elements_raw = isset($_POST['elements']) ? wp_unslash($_POST['elements']) : '[]';
    $canvas_raw = isset($_POST['canvas']) ? wp_unslash($_POST['canvas']) : '{}';
// Décoder les JSON strings
    $elements = json_decode($elements_raw, true);
    if ($elements === null) {
        $elements = [];
    }

    $canvas = json_decode($canvas_raw, true);
    if ($canvas === null) {
        $canvas = [];
    }

    // Logger les données reçues
    $logger->log_elements_received($elements, count($elements));
    $logger->log_canvas_properties($canvas);
// Valider les données
    $is_valid = $logger->log_validation($elements, $canvas);
    if (!$is_valid) {
        $logger->log_save_error('Validation failed');
        wp_send_json_error(__('Données invalides.', 'pdf-builder-pro'));
        return;
    }

    if (empty($template_name)) {
        $logger->log_save_error('Template name is empty');
        wp_send_json_error(__('Nom du template requis.', 'pdf-builder-pro'));
        return;
    }

    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        pdf_builder_load_core_when_needed();
    }

    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
// Préparer les données du template à stocker
    $template_data = [
        'elements' => $elements,  // Array décodé
        'canvas' => $canvas       // Array décodé
    ];
    $json_data = wp_json_encode($template_data);
    if ($json_data === false) {
        $logger->log_save_error('JSON encoding failed');
        wp_send_json_error(__('Erreur lors de l\'encodage des données JSON.', 'pdf-builder-pro'));
        return;
    }

    if ($template_id > 0) {
// Mettre à jour un template existant
        $result = $wpdb->update($table_templates, [
                'name' => $template_name,
                'template_data' => $json_data,
                'updated_at' => current_time('mysql')
            ], ['id' => $template_id], ['%s', '%s', '%s'], ['%d']);
        if ($result === false) {
            $logger->log_save_error('Update failed');
            wp_send_json_error(__('Erreur lors de la mise à jour du template.', 'pdf-builder-pro'));
            return;
        }
    } else {
    // Créer un nouveau template
        $result = $wpdb->insert($table_templates, [
                'name' => $template_name,
                'template_data' => $json_data,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ], ['%s', '%s', '%s', '%s']);
        if ($result === false) {
            $logger->log_save_error('Insert failed');
            wp_send_json_error(__('Erreur lors de la création du template.', 'pdf-builder-pro'));
            return;
        }

        $template_id = $wpdb->insert_id;
    }

    // Logger le succès
    $logger->log_save_success($template_id, count($elements));
// ✅ Invalider le cache pour ce template
    delete_transient('pdf_builder_template_' . $template_id);
// Retourner le succès
    wp_send_json_success([
        'id' => $template_id,
        'name' => $template_name,
        'timestamp' => current_time('U'),
        'elementCount' => count($elements),
        'message' => __('Template enregistré avec succès.', 'pdf-builder-pro')
    ]);
}

/**
 * Enregistrer les hooks AJAX de fallback de manière sécurisée
 */
function pdf_builder_register_fallback_hooks()
{

    // Vérifier que WordPress est chargé
    if (!function_exists('add_action')) {
        return;
    }

    // Actions AJAX fallback
    add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
    add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
    
    // Fallback pour la sauvegarde de template si la classe admin n'est pas disponible
    if (!function_exists('pdf_builder_ajax_save_template_fallback')) {
        function pdf_builder_ajax_save_template_fallback() {
            try {
                error_log('PDF Builder: Fallback save handler called - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
                error_log('PDF Builder: Fallback - Action: ' . (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'none'));
                error_log('PDF Builder: Fallback - POST data keys: ' . implode(', ', array_keys($_POST)));
                
                // Vérifier les permissions
                if (!current_user_can('manage_options')) {
                    error_log('PDF Builder: Fallback - Insufficient permissions');
                    wp_send_json_error('Permissions insuffisantes');
                    return;
                }

                // Vérifier le nonce
                $nonce_valid = false;
                if (isset($_POST['nonce'])) {
                    $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                                  wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                                  wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
                }

                if (!$nonce_valid) {
                    error_log('PDF Builder: Fallback - Invalid nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'none'));
                    wp_send_json_error('Sécurité: Nonce invalide');
                    return;
                }

                error_log('PDF Builder: Fallback - Nonce valid, processing data');

                // Charger le Template Manager directement
                if (class_exists('PDF_Builder_Pro\Managers\PdfBuilderTemplateManager')) {
                    $manager = new \PDF_Builder_Pro\Managers\PdfBuilderTemplateManager();
                    
                    // Appeler la bonne méthode selon l'action
                    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
                    if ($action === 'pdf_builder_auto_save_template') {
                        $manager->ajax_auto_save_template();
                    } else {
                        $manager->ajaxSaveTemplateV3();
                    }
                } else {
                    error_log('PDF Builder: Fallback - TemplateManager class not found');
                    wp_send_json_error('Gestionnaire de templates non disponible');
                }
            } catch (\Throwable $e) {
                error_log('PDF Builder: Fallback save handler error: ' . $e->getMessage());
                wp_send_json_error('Erreur critique: ' . $e->getMessage());
            }
        }
        
        add_action('wp_ajax_pdf_builder_save_template', 'pdf_builder_ajax_save_template_fallback');
        add_action('wp_ajax_pdf_builder_pro_save_template', 'pdf_builder_ajax_save_template_fallback');
        add_action('wp_ajax_pdf_builder_auto_save_template', function () {
            error_log('� [FALLBACK HOOK] wp_ajax_pdf_builder_auto_save_template CALLED!');
            error_log('�🔍 [FALLBACK HOOK] Auto-save hook called! Action received');
            error_log('🔍 [FALLBACK HOOK] POST data: ' . print_r($_POST, true));
            
            // Appeler le vrai handler du template manager
            if (class_exists('PDF_Builder_Pro\Managers\PdfBuilderTemplateManager')) {
                error_log('🔍 [FALLBACK HOOK] PdfBuilderTemplateManager found, calling ajax_auto_save_template');
                $manager = new \PDF_Builder_Pro\Managers\PdfBuilderTemplateManager();
                $manager->ajax_auto_save_template();
            } else {
                error_log('🔍 [FALLBACK HOOK] PdfBuilderTemplateManager class not found');
                wp_send_json_error('Gestionnaire de templates non disponible');
            }
        });
    }
    
// Hooks pour save_template supprimés - gérés par PDF_Builder_Admin.php
// Hooks pour get_template supprimés - gérés par PDF_Builder_Admin.php
}

// Enregistrer les hooks seulement si WordPress est disponible
if (function_exists('add_action')) {
// Action cron pour la génération de previews de templates
    add_action('pdf_builder_generate_template_preview', 'pdf_builder_generate_template_preview_cron');
    error_log('📝 REGISTERING FALLBACK HOOKS...');
    pdf_builder_register_fallback_hooks();
}

// Enregistrer les scripts seulement si WordPress est disponible
if (function_exists('add_action')) {
    add_action('admin_enqueue_scripts', 'pdf_builder_enqueue_editor_scripts');
}

function pdf_builder_enqueue_editor_scripts($hook)
{

    // Charger wp_enqueue_media seulement sur les pages du PDF builder
    if (
        strpos($hook, 'pdf-builder') !== false
        || (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false)
    ) {
        // Diagnostic immédiat
        error_log('=== PDF BUILDER MEDIA DIAGNOSTIC ===');
        error_log('GD Library: ' . (extension_loaded('gd') ? 'YES' : 'NO'));
        error_log('ImageMagick: ' . (extension_loaded('imagick') ? 'YES' : 'NO'));
        
        $upload_dir = wp_upload_dir();
        error_log('Upload basedir: ' . $upload_dir['basedir']);
        error_log('Upload dir writable: ' . (is_writable($upload_dir['basedir']) ? 'YES' : 'NO'));
        
        // Lister les attachments
        $args = array(
            'post_type'      => 'attachment',
            'posts_per_page' => 10,
            'post_status'    => 'inherit'
        );
        $attachments = get_posts($args);
        error_log('Attachments in DB: ' . count($attachments));
        foreach ($attachments as $att) {
            $url = wp_get_attachment_url($att->ID);
            error_log('  ID: ' . $att->ID . ', Title: ' . $att->post_title . ', URL: ' . $url);
        }
        error_log('=== END DIAGNOSTIC ===');
        
        wp_enqueue_media();
    }
}

/**
 * Diagnostic pour la bibliothèque média WordPress
 */
function pdf_builder_check_media_library() {
    error_log('=== PDF BUILDER MEDIA DIAGNOSTIC ===');
    
    // Vérifier si GD est disponible
    $gd_info = extension_loaded('gd');
    error_log('GD Library: ' . ($gd_info ? 'YES' : 'NO'));
    
    // Vérifier ImageMagick
    $imagick = extension_loaded('imagick');
    error_log('ImageMagick: ' . ($imagick ? 'YES' : 'NO'));
    
    // Vérifier uploads writable
    $upload_dir = wp_upload_dir();
    error_log('Upload basedir: ' . $upload_dir['basedir']);
    error_log('Upload dir writable: ' . (is_writable($upload_dir['basedir']) ? 'YES' : 'NO'));
    
    // Lister les fichiers uploadés
    $files = glob($upload_dir['basedir'] . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    error_log('Uploaded files count: ' . count($files ?? array()));
    if (!empty($files)) {
        foreach (array_slice($files, 0, 5) as $file) {
            error_log('  - ' . basename($file));
        }
    }
    
    // Vérifier les attachments en DB
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => 10,
        'post_status'    => 'inherit'
    );
    $attachments = get_posts($args);
    error_log('Attachments in DB: ' . count($attachments));
    foreach ($attachments as $attachment) {
        error_log('  - ID: ' . $attachment->ID . ', Title: ' . $attachment->post_title . ', URL: ' . wp_get_attachment_url($attachment->ID));
    }
}

// Appeler aussi en admin et en AJAX
add_action('admin_enqueue_scripts', function() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false) {
        pdf_builder_check_media_library();
    }
}, 5);

// Aussi en frontend pour le diagnostic
add_action('wp_enqueue_scripts', function() {
    if (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false) {
        pdf_builder_check_media_library();
    }
}, 5);

/**
 * Fonction cron pour générer les previews de templates de manière asynchrone
 */
function pdf_builder_generate_template_preview_cron($template_id, $template_file)
{

    try {
// Charger le Template Manager
        if (class_exists('PDF_Builder_Template_Manager')) {
            $template_manager = new PDF_Builder_Template_Manager();
            $template_manager->generate_template_preview($template_id, $template_file);
        }
    } catch (Exception $e) {
    }
}

// ============================================================================
// FILTRER LES NOTICES ADMIN - NE MONTRER QUE CELLES DU PLUGIN PDF BUILDER PRO
// ============================================================================

add_action('admin_notices', function() {
    // ✅ Masquer toutes les notices sauf celles du plugin PDF Builder Pro
    // sur les pages du plugin
    
    $current_screen = get_current_screen();
    if (!$current_screen) return;
    
    // Vérifier si c'est une page du plugin PDF Builder Pro
    $is_pdf_builder_page = (
        strpos($current_screen->base, 'pdf-builder') !== false ||
        strpos($current_screen->base, 'pdf_builder') !== false ||
        strpos($current_screen->id, 'pdf-builder') !== false ||
        strpos($current_screen->id, 'pdf_builder') !== false
    );
    
    // Si c'est une page PDF Builder Pro, masquer les notices des autres plugins
    if ($is_pdf_builder_page) {
        // Récupérer toutes les notices DOM
        $notices = array_filter(array(
            'notice',
            'notice-info',
            'notice-warning',
            'notice-error',
            'notice-success',
            'error',
            'updated'
        ));
        
        echo '<style>';
        echo 'div.notice:not(.pdf-builder-notice),';
        echo 'div.notice-info:not(.pdf-builder-notice),';
        echo 'div.notice-warning:not(.pdf-builder-notice),';
        echo 'div.notice-error:not(.pdf-builder-notice),';
        echo 'div.notice-success:not(.pdf-builder-notice),';
        echo 'div.error:not(.pdf-builder-notice),';
        echo 'div.updated:not(.pdf-builder-notice) {';
        echo '  display: none !important;';
        echo '}';
        echo '</style>';
    }
}, 1);  // Hook très tôt pour intercepter avant les autres notices
