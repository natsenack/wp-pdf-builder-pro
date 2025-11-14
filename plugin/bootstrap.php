<?php

/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

// Log pour déboguer le chargement
error_log('PDF Builder: bootstrap.php loaded, is_admin: ' . (is_admin() ? 'true' : 'false') . ', doing_ajax: ' . (wp_doing_ajax() ? 'true' : 'false') . ', action: ' . (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'none'));

// ============================================================================
// ✅ BLOQUER LES NOTIFICATIONS WORDPRESS AVANT TOUT
// ============================================================================
// Cette approche très tôt supprime les notifications AVANT qu'elles ne s'affichent
if (is_admin()) {
    // Vérifier que nous sommes sur une page PDF Builder
    $pdf_builder_pages = [
        'pdf-builder-pro',
        'pdf-builder-templates',
        'pdf-builder-react-editor',
        'pdf-builder-settings',
        'pdf-builder-developer',
        'pdf-builder-predefined-templates'
    ];
    
    $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    
    if (in_array($current_page, $pdf_builder_pages)) {
        // Utiliser un hook TRÈS TARDIF pour supprimer les notifications
        // Cela permet au PDF Builder de s'initialiser d'abord
        add_action('admin_init', function() {
            // Supprimer les actions admin_notices sauf celles du PDF Builder
            global $wp_filter;
            
            if (isset($wp_filter['admin_notices'])) {
                foreach ($wp_filter['admin_notices']->callbacks as $priority => $callbacks) {
                    foreach ($callbacks as $hook_name => $data) {
                        $callback = $data['function'];
                        
                        // Vérifier si c'est du PDF Builder
                        $is_pdf_builder = false;
                        if (is_array($callback) && is_object($callback[0])) {
                            $class = get_class($callback[0]);
                            if (strpos($class, 'PDF_Builder') !== false) {
                                $is_pdf_builder = true;
                            }
                        }
                        
                        // Supprimer si ce n'est pas du PDF Builder
                        if (!$is_pdf_builder) {
                            remove_action('admin_notices', $callback, $priority);
                        }
                    }
                }
            }
            
            // Même chose pour all_admin_notices
            if (isset($wp_filter['all_admin_notices'])) {
                foreach ($wp_filter['all_admin_notices']->callbacks as $priority => $callbacks) {
                    foreach ($callbacks as $hook_name => $data) {
                        $callback = $data['function'];
                        
                        $is_pdf_builder = false;
                        if (is_array($callback) && is_object($callback[0])) {
                            $class = get_class($callback[0]);
                            if (strpos($class, 'PDF_Builder') !== false) {
                                $is_pdf_builder = true;
                            }
                        }
                        
                        if (!$is_pdf_builder) {
                            remove_action('all_admin_notices', $callback, $priority);
                        }
                    }
                }
            }
        }, 999);
        
        // Output buffering comme filet de secours pour les notifications qui passent quand même
        add_action('admin_head', function() {
            ob_start(function($buffer) {
                // Supprimer les notifications qui ne viennent pas du PDF Builder
                // Mais GARDER les notifications du PDF Builder
                $buffer = preg_replace_callback('/<div[^>]*id="setting-error-[^"]*"[^>]*>.*?<\/div>/is', function($matches) {
                    // Vérifier si c'est du PDF Builder
                    if (strpos($matches[0], 'pdf-builder') !== false || strpos($matches[0], 'pdf_builder') !== false) {
                        return $matches[0]; // Garder
                    }
                    return ''; // Supprimer
                }, $buffer);
                
                // Supprimer les notifications non-PDF-Builder
                $buffer = preg_replace_callback('/<div[^>]*class="[^"]*(notice|error|updated|update-nag|wp-notice)[^"]*"[^>]*>.*?<\/div>/is', function($matches) {
                    // Vérifier si c'est du PDF Builder
                    if (strpos($matches[0], 'pdf-builder') !== false || strpos($matches[0], 'pdf_builder') !== false) {
                        return $matches[0]; // Garder
                    }
                    return ''; // Supprimer
                }, $buffer);
                
                return $buffer;
            });
        }, 999);
    }
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
// HOOKS AJAX ESSENTIELS
// ============================================================================

// Intercepter les requêtes AJAX pour les templates
add_action('wp_ajax_pdf_builder_save_template', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    pdf_builder_register_essential_ajax_hooks();
}, 1);

add_action('wp_ajax_pdf_builder_load_template', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    pdf_builder_register_essential_ajax_hooks();
}, 1);

add_action('wp_ajax_pdf_builder_auto_save_template', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }
    pdf_builder_register_essential_ajax_hooks();
}, 1);

// Test AJAX simple pour l'intégration du cache
add_action('wp_ajax_pdf_builder_simple_test', function() {
    error_log('PDF Builder: pdf_builder_simple_test action called');
    try {
        // Vérifier que WordPress est chargé
        if (!function_exists('wp_send_json_success')) {
            error_log('PDF Builder: wp_send_json_success not available');
            throw new Exception('wp_send_json_success function not available');
        }

        error_log('PDF Builder: Sending success response');
        wp_send_json_success('<p>✅ Test AJAX simplifié réussi !</p>');
    } catch (Exception $e) {
        error_log('PDF Builder Simple Test Error: ' . $e->getMessage());
        wp_send_json_error('<p>❌ Erreur lors du test AJAX: ' . esc_html($e->getMessage()) . '</p>');
    }
});
add_action('wp_ajax_nopriv_pdf_builder_simple_test', function() {
    error_log('PDF Builder: pdf_builder_simple_test nopriv action called');
    try {
        // Vérifier que WordPress est chargé
        if (!function_exists('wp_send_json_success')) {
            error_log('PDF Builder: wp_send_json_success not available (nopriv)');
            throw new Exception('wp_send_json_success function not available');
        }

        error_log('PDF Builder: Sending success response (nopriv)');
        wp_send_json_success('<p>✅ Test AJAX simplifié réussi !</p>');
    } catch (Exception $e) {
        error_log('PDF Builder Simple Test Error: ' . $e->getMessage());
        wp_send_json_error('<p>❌ Erreur lors du test AJAX: ' . esc_html($e->getMessage()) . '</p>');
    }
});

// Initialiser les variables $_SERVER manquantes pour éviter les erreurs PHP 8.1+
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
    }

    // Charger les managers essentiels depuis src/Managers/
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
        'PDF_Builder_Security_Validator.php',
        'PDF_Builder_MU_Plugin_Blocker.php'
    );
    foreach ($core_classes as $core_class) {
        $core_path = PDF_BUILDER_PLUGIN_DIR . 'src/Core/' . $core_class;
        if (file_exists($core_path)) {
            require_once $core_path;
        }
    }

    // Charger la classe d'administration depuis src/
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
    // Protection globale contre les chargements multiples
    static $bootstrap_loaded = false;
    if ($bootstrap_loaded || (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED)) {
        return;
    }
    $bootstrap_loaded = true;

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
    pdf_builder_load_new_classes();

    // CHARGER LE TEST D'INTÉGRATION DU CACHE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Cache/cache-integration-test.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Cache/cache-integration-test.php';
    }

    // CHARGER LE HANDLER DE TEST DE LICENCE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php';
        // Instancier le handler pour enregistrer les hooks AJAX
        if (class_exists('PDF_Builder_Pro\\License\\LicenseTestHandler')) {
            new \PDF_Builder_Pro\License\LicenseTestHandler();
        }
    }

    // CHARGER LE HANDLER D'EXPIRATION DE LICENCE
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-expiration-handler.php';
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

    // CHARGER LES HOOKS AJAX ESSENTIELS TOUJOURS, MÊME EN MODE FALLBACK
    pdf_builder_register_essential_ajax_hooks();

    // INSTANCIER L'API PREVIEW POUR LES ROUTES REST (Étape 1.4)
    if (class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI')) {
        new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();
    }

    // Vérification que les classes essentielles sont chargées
    if (class_exists('PDF_Builder\\Core\\PdfBuilderCore')) {
        $core = \PDF_Builder\Core\PdfBuilderCore::getInstance();
        if (method_exists($core, 'init')) {
            $core->init();
        }

        // Initialiser l'interface d'administration dans l'admin OU lors d'AJAX pour nos actions
        $is_admin_or_pdf_ajax = is_admin() || (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') !== false);

        if ($is_admin_or_pdf_ajax && class_exists('PDF_Builder\\Admin\\PdfBuilderAdmin')) {
            try {
                $admin = \PDF_Builder\Admin\PdfBuilderAdmin::getInstance($core);
            } catch (Exception $e) {
                // Fallback en cas d'erreur
                add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
            }
        } elseif (wp_doing_ajax()) {
            // Ne rien faire pour les appels AJAX non-PDF
        } else {
            // Fallback: enregistrer un menu simple si la classe principale n'est pas disponible
            add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
        }
    } else {
        // Fallback: enregistrer un menu simple si le core n'est pas disponible
        add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');
    }

    // Marquer comme chargé globalement
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

// Fonction simple pour enregistrer le menu admin
function pdf_builder_register_admin_menu_simple()
{
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
    // Charger les classes nécessaires pour les handlers AJAX
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Template_Manager.php';
    }

    // PDF_Builder_Admin.php déjà chargé plus haut

    // Créer une instance du template manager pour les handlers AJAX
    $template_manager = null;
    if (class_exists('PDF_Builder_Pro\\Managers\\PdfBuilderTemplateManager')) {
        $template_manager = new PDF_Builder_Pro\Managers\PdfBuilderTemplateManager();
    }

    // Enregistrer les hooks AJAX essentiels
    add_action('wp_ajax_pdf_builder_save_template', function() use ($template_manager) {
        if ($template_manager && method_exists($template_manager, 'ajaxSaveTemplateV3')) {
            $template_manager->ajaxSaveTemplateV3();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_save_template();
        }
    });

    add_action('wp_ajax_pdf_builder_load_template', function() use ($template_manager) {
        if ($template_manager && method_exists($template_manager, 'ajaxLoadTemplate')) {
            $template_manager->ajaxLoadTemplate();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_load_template();
        }
    });

    add_action('wp_ajax_pdf_builder_auto_save_template', function() use ($template_manager) {
        if ($template_manager && method_exists($template_manager, 'ajax_auto_save_template')) {
            $template_manager->ajax_auto_save_template();
        } else {
            // Fallback handler
            pdf_builder_fallback_ajax_auto_save_template();
        }
    });
}

// Fonction de chargement différé (maintenant vide car les hooks sont enregistrés au bootstrap)
function pdf_builder_load_core_when_needed()
{
    // Les hooks essentiels sont déjà enregistrés dans pdf_builder_load_bootstrap()
}

// Handlers AJAX de fallback
function pdf_builder_fallback_ajax_save_template()
{
    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    // Récupérer les données
    $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

    if (empty($template_data) || !$template_id) {
        wp_send_json_error('Données manquantes');
        return;
    }

    // Décoder le JSON pour vérifier les données
    $decoded_data = json_decode($template_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error('Données JSON invalides');
        return;
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

    if ($result !== false) {
        wp_send_json_success(['message' => 'Template sauvegardé avec succès']);
    } else {
        wp_send_json_error('Erreur lors de la sauvegarde');
    }
}

function pdf_builder_fallback_ajax_load_template()
{
    // Vérifications de base
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissions insuffisantes');
        return;
    }

    $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

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
        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Erreur de décodage JSON');
            return;
        }

        wp_send_json_success([
            'template' => $template_data,
            'id' => $template['id'],
            'name' => $template['name']
        ]);
    } else {
        wp_send_json_error('Template non trouvé');
    }
}

function pdf_builder_fallback_ajax_auto_save_template()
{
    // Même logique que save_template mais pour l'auto-save
    pdf_builder_fallback_ajax_save_template();
}

// Chargement différé du core
function pdf_builder_load_core_on_demand()
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
                return;
            }
        }
    }
}

// Initialiser les paramètres par défaut du canvas
function pdf_builder_init_canvas_defaults()
{
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

    foreach ($defaults as $option => $default_value) {
        if (get_option($option) === false) {
            add_option($option, $default_value);
        }
    }
}

// AJAX handler pour obtenir un nonce frais
function pdf_builder_ajax_get_fresh_nonce()
{
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }

    $nonce = wp_create_nonce('pdf_builder_nonce');
    wp_send_json_success(array(
        'nonce' => $nonce,
        'timestamp' => time()
    ));
}

// AJAX handler pour récupérer un template par ID
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

    // Récupérer le template depuis la table personnalisée
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);

    // Si le template n'est pas trouvé dans la table personnalisée, chercher dans wp_posts
    if (!$template) {
        $post = get_post($template_id);
        if ($post && $post->post_type === 'pdf_template') {
            $template_data_raw = get_post_meta($post->ID, '_pdf_template_data', true);
            if (!empty($template_data_raw)) {
                $template = array(
                    'id' => $post->ID,
                    'name' => $post->post_title,
                    'template_data' => $template_data_raw,
                    'created_at' => $post->post_date,
                    'updated_at' => $post->post_modified
                );
            }
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

    if (is_array($template_data)) {
        if (isset($template_data['elements'])) {
            $elements = $template_data['elements'];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } elseif (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
            $elements = $template_data['pages'][0]['elements'] ?? [];
            $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
        } else {
            $elements = $template_data;
            $canvas = null;
        }
    } else {
        wp_send_json_error(__('Format de données du template invalide.', 'pdf-builder-pro'));
        return;
    }

    // Traiter les éléments
    if (is_string($elements)) {
        $unescaped_elements = stripslashes($elements);
        $decoded_elements = json_decode($unescaped_elements, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $elements = $decoded_elements;
        } else {
            $elements = [];
        }
    } elseif (!is_array($elements)) {
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

    // Vérifier que elements est défini
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

        // Gérer les positions
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

        // Gérer les dimensions
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

        // Copier les autres propriétés de style
        $style_properties = ['fontSize', 'fontWeight', 'color', 'textAlign', 'verticalAlign',
            'backgroundColor', 'borderColor', 'borderWidth', 'borderStyle', 'rotation', 'opacity'];

        if (isset($element['style']) && is_array($element['style'])) {
            foreach ($style_properties as $prop) {
                if (isset($element['style'][$prop])) {
                    $transformed_element[$prop] = $element['style'][$prop];
                }
            }
        } else {
            foreach ($style_properties as $prop) {
                if (isset($element[$prop])) {
                    $transformed_element[$prop] = $element[$prop];
                }
            }
        }

        // Pour les éléments text, utiliser content comme text
        if (isset($element['type']) && $element['type'] === 'text' && isset($element['content'])) {
            $transformed_element['text'] = $element['content'];
        }

        // Copier d'autres propriétés utiles
        $copy_properties = ['visible', 'locked', 'zIndex', 'name', 'src', 'logoUrl', 'defaultSrc',
            'alignment', 'borderRadius'];
        foreach ($copy_properties as $prop) {
            if (isset($element[$prop])) {
                $transformed_element[$prop] = $element[$prop];
            }
        }

        // Propriétés par défaut
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

    // Enrichir les logos company_logo avec src si absent
    foreach ($elements as &$el) {
        if (isset($el['type']) && $el['type'] === 'company_logo') {
            if (empty($el['src']) && empty($el['logoUrl'])) {
                $custom_logo_id = get_theme_mod('custom_logo');
                if ($custom_logo_id) {
                    $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                    if ($logo_url) {
                        $el['src'] = $logo_url;
                    }
                } else {
                    $site_logo_id = get_option('site_logo');
                    if ($site_logo_id) {
                        $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
                        if ($logo_url) {
                            $el['src'] = $logo_url;
                        }
                    }
                }
            }
        }
    }
    unset($el);

    $cache_data = array(
        'id' => $template['id'],
        'name' => $template['name'],
        'elements' => $elements,
        'canvas' => $canvas
    );
    wp_send_json_success($cache_data);
}