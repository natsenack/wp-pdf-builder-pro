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
// ENDPOINTS AJAX POUR RÉGÉNÉRATION DES POSITIONS
// ============================================================================

add_action('wp_ajax_pdf_builder_regenerate_positions', function() {
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

add_action('wp_ajax_pdf_builder_preview_positions', function() {
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
function pdf_builder_load_core() {
    static $loaded = false;
    if ($loaded) return;

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

    // Charger les managers essentiels depuis src/Managers/ AVANT PDF_Builder_Admin
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
function pdf_builder_load_new_classes() {
    static $new_classes_loaded = false;
    if ($new_classes_loaded) return;

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
        'api/PreviewImageAPI.php'
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
function pdf_builder_load_bootstrap() {
    // Protection globale contre les chargements multiples
    if (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED) {
        return;
    }

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

    // Vérification que les classes essentielles sont chargées
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Core')) {
        $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
        if (method_exists($core, 'init')) {
            $core->init();
        }

        // Initialiser l'interface d'administration
        if (is_admin() && class_exists('PDF_Builder\\Admin\\PDF_Builder_Admin')) {
            $admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance($core);
        }
    }

    // Marquer comme chargé globalement
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

// Fonction simple pour enregistrer le menu admin
function pdf_builder_register_admin_menu_simple() {
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
function pdf_builder_admin_page_simple() {
    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>PDF Builder Pro</h1><p>Page principale en cours de développement.</p></div>';
}

function pdf_builder_templates_page_simple() {
    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>Templates</h1><p>Page templates en cours de développement.</p></div>';
}

// Inclusion différée de la classe principale
function pdf_builder_load_core_when_needed() {
    static $core_loaded = false;
    if ($core_loaded) return;

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

        
        if (class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
            try {
                \PDF_Builder\Core\PDF_Builder_Core::getInstance()->init();
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
            $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
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
            $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_templates_page();
        }

        // Fonction callback pour la page documents
        function pdf_builder_documents_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_documents_page();
        }

        // Fonction callback pour la page settings
        function pdf_builder_settings_page_callback() {
            if (!is_user_logged_in()) {
                wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
            }

            pdf_builder_load_core_when_needed();
            $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
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
                $manager->render_admin_page();
            } else {
                echo '<div class="wrap"><h1>Erreur</h1><p>Le gestionnaire de modèles prédéfinis n\'est pas disponible.</p></div>';
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
            'Documents',
            'Documents',
            'manage_options',  // Capacité WordPress (sera vérifiée par Role_Manager)
            'pdf-builder-documents',
            'pdf_builder_documents_page_callback'
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
function pdf_builder_init_canvas_defaults() {
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
function pdf_builder_ajax_generate_order_pdf_fallback() {
    
    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        return;
    }
    
    $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
    $admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance();
    $woocommerce_integration = $admin ? $admin->get_woocommerce_integration() : null;
    
    if ($woocommerce_integration && method_exists($woocommerce_integration, 'ajax_generate_order_pdf')) {
        $woocommerce_integration->ajax_generate_order_pdf();
    } else {
        wp_send_json_error('WooCommerce integration not available');
    }
}

function pdf_builder_ajax_save_order_canvas_fallback() {
    
    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        return;
    }
    
    $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
    $admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance();
    $woocommerce_integration = $admin ? $admin->get_woocommerce_integration() : null;
    
    if ($woocommerce_integration && method_exists($woocommerce_integration, 'ajax_save_order_canvas')) {
        $woocommerce_integration->ajax_save_order_canvas();
    } else {
        wp_send_json_error('WooCommerce integration not available');
    }
}

/**
 * AJAX handler pour obtenir un nonce frais
 */
function pdf_builder_ajax_get_fresh_nonce() {
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
function pdf_builder_ajax_get_template() {
    // Vérifier le nonce
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'pdf_builder_nonce')) {
        wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
        return;
    }

    // Vérifier les permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission refusée.', 'pdf-builder-pro'));
        return;
    }

    // Récupérer l'ID du template
    $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

    if (!$template_id) {
        wp_send_json_error(__('ID du template manquant.', 'pdf-builder-pro'));
        return;
    }

    // Récupérer le template depuis la table personnalisée
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
        ARRAY_A
    );

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
        } elseif (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
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

    // DEBUG: Log la structure brute des éléments
    error_log('DEBUG: Elements avant transformation - Count: ' . count($elements));
    if (!empty($elements)) {
        error_log('DEBUG: Premier élément: ' . json_encode($elements[0]));
    }

    // Transformer les éléments dans le format attendu par React
    $transformed_elements = [];
    foreach ($elements as $element) {
        $transformed_element = [];

        // Copier les propriétés de base
        if (isset($element['id'])) $transformed_element['id'] = $element['id'];
        if (isset($element['type'])) $transformed_element['type'] = $element['type'];
        if (isset($element['content'])) $transformed_element['content'] = $element['content'];

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
        $style_properties = ['fontSize', 'fontWeight', 'color', 'textAlign', 'verticalAlign', 'backgroundColor', 'borderColor', 'borderWidth', 'borderStyle', 'rotation', 'opacity'];
        
        // Format imbriqué: style.fontSize ou format plat: fontSize
        if (isset($element['style']) && is_array($element['style'])) {
            foreach ($style_properties as $prop) {
                if (isset($element['style'][$prop])) {
                    if (in_array($prop, ['fontSize', 'borderWidth', 'rotation', 'opacity'])) {
                        $transformed_element[$prop] = is_numeric($element['style'][$prop]) ? (int)$element['style'][$prop] : $element['style'][$prop];
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
                        $transformed_element[$prop] = is_numeric($element[$prop]) ? (int)$element[$prop] : $element[$prop];
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
        $copy_properties = ['visible', 'locked', 'zIndex', 'name'];
        foreach ($copy_properties as $prop) {
            if (isset($element[$prop])) {
                $transformed_element[$prop] = $element[$prop];
            }
        }

        // Propriétés par défaut pour tous les éléments (seulement si non défini)
        if (!isset($transformed_element['x'])) $transformed_element['x'] = 0;
        if (!isset($transformed_element['y'])) $transformed_element['y'] = 0;
        if (!isset($transformed_element['width'])) $transformed_element['width'] = 100;
        if (!isset($transformed_element['height'])) $transformed_element['height'] = 50;
        if (!isset($transformed_element['visible'])) $transformed_element['visible'] = true;
        if (!isset($transformed_element['locked'])) $transformed_element['locked'] = false;

        $transformed_elements[] = $transformed_element;
    }

    $elements = $transformed_elements;

    // DEBUG: Log les éléments transformés
    error_log('DEBUG: Elements après transformation - Count: ' . count($elements));
    if (!empty($elements)) {
        error_log('DEBUG: Premier élément transformé: ' . json_encode($elements[0]));
        error_log('DEBUG: Tous les éléments: ' . json_encode($elements));
    }

    wp_send_json_success(array(
        'id' => $template['id'],
        'name' => $template['name'],
        'elements' => $elements,
        'canvas' => $canvas,
        'created_at' => $template['created_at'],
        'updated_at' => $template['updated_at']
    ));
}



/**
 * Fonction utilitaire pour corriger/régénérer les éléments avec des positions correctes
 */
function pdf_builder_regenerate_element_positions($elements) {
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
            $element['y'] = $pos['y'] + ($count * 50); // Décalage pour les doublons
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
function pdf_builder_ajax_regenerate_positions() {
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
 * AJAX handler pour sauvegarder un template
 */
function pdf_builder_ajax_save_template() {
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
    $elements = isset($_POST['elements']) ? $_POST['elements'] : [];
    $canvas = isset($_POST['canvas']) ? $_POST['canvas'] : [];

    // DEBUG: Log what React sent
    error_log('DEBUG SAVE: Receiving from React:');
    error_log('DEBUG SAVE: Elements raw: ' . print_r($elements, true));
    if (is_string($elements)) {
        error_log('DEBUG SAVE: Elements is string, first 500 chars: ' . substr($elements, 0, 500));
    }

    if (empty($template_name)) {
        wp_send_json_error(__('Nom du template requis.', 'pdf-builder-pro'));
        return;
    }

    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        pdf_builder_load_core_when_needed();
    }

    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';

    // Préparer les données du template
    $template_data = [
        'elements' => $elements,
        'canvas' => $canvas
    ];

    $json_data = wp_json_encode($template_data);
    if ($json_data === false) {
        wp_send_json_error(__('Erreur lors de l\'encodage des données JSON.', 'pdf-builder-pro'));
        return;
    }

    // Debug: log what we're storing


    if ($template_id > 0) {
        // Mettre à jour un template existant
        $result = $wpdb->update(
            $table_templates,
            [
                'name' => $template_name,
                'template_data' => $json_data,
                'updated_at' => current_time('mysql')
            ],
            ['id' => $template_id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            wp_send_json_error(__('Erreur lors de la mise à jour du template.', 'pdf-builder-pro'));
            return;
        }
    } else {
        // Créer un nouveau template
        $result = $wpdb->insert(
            $table_templates,
            [
                'name' => $template_name,
                'template_data' => $json_data,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error(__('Erreur lors de la création du template.', 'pdf-builder-pro'));
            return;
        }

        $template_id = $wpdb->insert_id;
    }

    // Retourner le succès
    wp_send_json_success([
        'id' => $template_id,
        'name' => $template_name,
        'message' => $template_id > 0 ? __('Template mis à jour avec succès.', 'pdf-builder-pro') : __('Template créé avec succès.', 'pdf-builder-pro')
    ]);
}

/**
 * Enregistrer les hooks AJAX de fallback de manière sécurisée
 */
function pdf_builder_register_fallback_hooks() {
    // Vérifier que WordPress est chargé
    if (!function_exists('add_action')) {
        return;
    }

    // Actions AJAX fallback
    add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
    add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
    // Fallback handlers are now handled by PDF_Builder_Admin class
    // add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
    // add_action('wp_ajax_nopriv_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
    // add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
    // add_action('wp_ajax_nopriv_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
    add_action('wp_ajax_pdf_builder_save_template', 'pdf_builder_ajax_save_template');
    add_action('wp_ajax_nopriv_pdf_builder_save_template', 'pdf_builder_ajax_save_template');
    add_action('wp_ajax_pdf_builder_get_template', 'pdf_builder_ajax_get_template');
    add_action('wp_ajax_nopriv_pdf_builder_get_template', 'pdf_builder_ajax_get_template');
    // Duplicate registration removed (was registered twice before)
    // add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
    // add_action('wp_ajax_nopriv_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
}

// Enregistrer les hooks seulement si WordPress est disponible
if (function_exists('add_action')) {
    // Action cron pour la génération de previews de templates
    add_action('pdf_builder_generate_template_preview', 'pdf_builder_generate_template_preview_cron');

    pdf_builder_register_fallback_hooks();
}

// Fonction fallback pour récupérer les paramètres
function pdf_builder_ajax_get_settings_fallback() {
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

// Fonction fallback pour sauvegarder les paramètres
function pdf_builder_ajax_save_settings_fallback() {
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
        'notification_events' => isset($_POST['notification_events']) ? array_map(function($event) { return sanitize_text_field($event); }, $_POST['notification_events']) : [],
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
        'default_canvas_width' => isset($_POST['default_canvas_width']) ? intval($_POST['default_canvas_width']) : 210,
        'default_canvas_height' => isset($_POST['default_canvas_height']) ? intval($_POST['default_canvas_height']) : 297,
        'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? sanitize_text_field($_POST['default_canvas_unit']) : 'mm',
        'canvas_background_color' => isset($_POST['canvas_background_color']) ? sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
        'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
        'container_background_color' => isset($_POST['container_background_color']) ? sanitize_text_field($_POST['container_background_color']) : '#f8f9fa',
        'container_show_transparency' => isset($_POST['container_show_transparency']),
        'show_margins' => isset($_POST['show_margins']),
        'margin_top' => isset($_POST['margin_top']) ? intval($_POST['margin_top']) : 10,
        'margin_right' => isset($_POST['margin_right']) ? intval($_POST['margin_right']) : 10,
        'margin_bottom' => isset($_POST['margin_bottom']) ? intval($_POST['margin_bottom']) : 10,
        'margin_left' => isset($_POST['margin_left']) ? intval($_POST['margin_left']) : 10,
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
        $allowed_roles = array_map(function($role) { return sanitize_text_field($role); }, (array) $_POST['pdf_builder_allowed_roles']);
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
    wp_send_json_success(array(
        'message' => __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
        'spacing' => $settings['canvas_border_spacing']
    ));
    exit;
}

// Enregistrer les scripts seulement si WordPress est disponible
if (function_exists('add_action')) {
    add_action('admin_enqueue_scripts', 'pdf_builder_enqueue_editor_scripts');
}

function pdf_builder_enqueue_editor_scripts($hook) {
    // Charger wp_enqueue_media seulement sur les pages du PDF builder
    if (strpos($hook, 'pdf-builder') !== false || (isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') !== false)) {
        wp_enqueue_media();
    }
}

/**
 * Fonction cron pour générer les previews de templates de manière asynchrone
 */
function pdf_builder_generate_template_preview_cron($template_id, $template_file) {
    try {
        // Charger le Template Manager
        if (class_exists('PDF_Builder_Template_Manager')) {
            $template_manager = new PDF_Builder_Template_Manager();
            $template_manager->generate_template_preview($template_id, $template_file);
        }
    } catch (Exception $e) {

    }
}

