<?php
/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

// Définir les constantes essentielles du plugin
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('PDF_BUILDER_PLUGIN_URL')) {
    define('PDF_BUILDER_PLUGIN_URL', plugins_url('/', __FILE__));
}

if (!defined('PDF_BUILDER_PRO_ASSETS_URL')) {
    define('PDF_BUILDER_PRO_ASSETS_URL', PDF_BUILDER_PLUGIN_URL . 'assets/');
}

if (!defined('PDF_BUILDER_PRO_VERSION')) {
    define('PDF_BUILDER_PRO_VERSION', '2.0.1');
}

// Debug logging
$debug_log = plugin_dir_path(__FILE__) . 'debug.log';
file_put_contents($debug_log, "[" . date('Y-m-d H:i:s') . "] Bootstrap loaded. Version: " . PDF_BUILDER_PRO_VERSION . "\n", FILE_APPEND);
error_log("[PDF-BUILDER] Bootstrap.php loaded. Version: " . PDF_BUILDER_PRO_VERSION);

// Fonction pour charger le core du plugin
function pdf_builder_load_core() {
    static $loaded = false;
    if ($loaded) return;

    // Charger le autoloader pour le nouveau système PSR-4
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    }

    // Charger le logger en premier (nécessaire pour PDF_Builder_Core)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Logger.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Logger.php';
    }

    // Charger la classe principale PDF_Builder_Core depuis src/
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Core.php';
    }

    // Charger les managers essentiels depuis src/Managers/ AVANT PDF_Builder_Admin
    $managers = array(
        'PDF_Builder_Cache_Manager.php',
        'PDF_Builder_Canvas_Elements_Manager.php',
        'PDF_Builder_Canvas_Interactions_Manager.php',
        'PDF_Builder_Diagnostic_Manager.php',
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

    // Charger la classe d'administration depuis src/ APRÈS les managers
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
    }

    // Charger le contrôleur PDF
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Generator_Controller.php';
    }

    // Charger le contrôleur API d'aperçu (Phase 2.5.1)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Builder_Preview_API_Controller.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Controllers/PDF_Builder_Preview_API_Controller.php';
    }

    $loaded = true;
}

// Fonction principale de chargement du bootstrap
function pdf_builder_load_bootstrap() {
    // Protection globale contre les chargements multiples
    if (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED) {
        return;
    }

    // Définir le répertoire du plugin si pas déjà fait
    if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
        define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    // Charger l'autoloader PSR-4
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    }

    // Charger la configuration si pas déjà faite
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'config/config.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'config/config.php';
    }

    // Charger le core maintenant que l'autoloader est prêt
    pdf_builder_load_core();

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

    add_submenu_page(
        'pdf-builder-pro',
        __('Éditeur Canvas', 'pdf-builder-pro'),
        __('🎨 Éditeur Canvas', 'pdf-builder-pro'),
        'read',
        'pdf-builder-editor',
        'pdf_builder_editor_page_simple'
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

function pdf_builder_editor_page_simple() {
    if (!is_user_logged_in()) {
        wp_die(__('Vous devez être connecté.', 'pdf-builder-pro'));
    }
    echo '<div class="wrap"><h1>Éditeur Canvas</h1><p>Éditeur en cours de développement.</p></div>';
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
    }

    if ($load_core) {
        pdf_builder_load_core();
        if (class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
            try {
                \PDF_Builder\Core\PDF_Builder_Core::getInstance()->init();
                $core_loaded = true;
            } catch (Exception $e) {
                // Gestion silencieuse des erreurs pour éviter les logs
                wp_die('Plugin initialization failed: ' . esc_html($e->getMessage()));
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
            'read',  // Changé pour permettre à tous les utilisateurs connectés
            'pdf-builder-main',
            'pdf_builder_main_page_callback',
            'dashicons-pdf',
            30
        );

        add_submenu_page(
            'pdf-builder-main',
            'Templates',
            'Templates',
            'read',  // Changé pour permettre à tous les utilisateurs connectés
            'pdf-builder-templates',
            'pdf_builder_templates_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Documents',
            'Documents',
            'read',  // Changé pour permettre à tous les utilisateurs connectés
            'pdf-builder-documents',
            'pdf_builder_documents_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            'Settings',
            'Settings',
            'read',  // Changé pour permettre à tous les utilisateurs connectés
            'pdf-builder-settings',
            'pdf_builder_settings_page_callback'
        );

        add_submenu_page(
            'pdf-builder-main',
            'React Editor',
            'React Editor',
            'read',  // Changé pour permettre à tous les utilisateurs connectés
            'pdf-builder-react-editor',
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

    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder\Core\PDF_Builder_Core')) {
        pdf_builder_load_core_when_needed();
    }

    $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();

    // Récupérer le template
    global $wpdb;
    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
    $template = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
        ARRAY_A
    );

    if (!$template) {
        wp_send_json_error(__('Template non trouvé.', 'pdf-builder-pro'));
        return;
    }

    // Décoder les données JSON du template
    $template_data = json_decode($template['template_data'], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('PDF Builder: Erreur JSON decode - ' . json_last_error_msg() . ' - Raw data: ' . substr($template['template_data'], 0, 500));
        wp_send_json_error(__('Erreur lors du décodage des données du template.', 'pdf-builder-pro'));
        return;
    }

    // Debug: Log des données décodées
    error_log('PDF Builder: Template data decoded: ' . print_r($template_data, true));

    // S'assurer que elements est un array (gérer les données stockées de manière incohérente)
    $elements = isset($template_data['elements']) ? $template_data['elements'] : [];
    error_log('PDF Builder: Elements before processing: ' . (is_string($elements) ? 'STRING: ' . substr($elements, 0, 200) : 'ARRAY/OTHER: ' . print_r($elements, true)));

    if (is_string($elements)) {
        // Si elements est une string JSON, la décoder
        $elements = json_decode($elements, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PDF Builder: Erreur JSON decode elements - ' . json_last_error_msg() . ' - Raw elements: ' . substr($elements, 0, 200));
            $elements = [];
        }
    }

    // Debug: Log des éléments finaux
    error_log('PDF Builder: Final elements: ' . print_r($elements, true));

    // S'assurer que canvas est un objet (gérer les données stockées de manière incohérente)
    $canvas = isset($template_data['canvas']) ? $template_data['canvas'] : null;
    if (is_string($canvas)) {
        // Si canvas est une string JSON, la décoder
        $canvas = json_decode($canvas, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PDF Builder: Erreur JSON decode canvas - ' . json_last_error_msg() . ' - Raw canvas: ' . substr($canvas, 0, 200));
            $canvas = null;
        }
    }

    // Vérifier que elements est défini (peut être un array vide pour un nouveau template)
    if (!isset($template_data['elements'])) {
        error_log('PDF Builder: Missing elements in template data: ' . print_r($template_data, true));
        wp_send_json_error(__('Données du template incomplètes.', 'pdf-builder-pro'));
        return;
    }

    // Retourner les données du template
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
    error_log('PDF Builder: Storing template data - Length: ' . strlen($json_data) . ' - First 500 chars: ' . substr($json_data, 0, 500));

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
 * Actions AJAX fallback
 */
add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
add_action('wp_ajax_nopriv_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
add_action('wp_ajax_nopriv_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
add_action('wp_ajax_pdf_builder_save_template', 'pdf_builder_ajax_save_template');
add_action('wp_ajax_nopriv_pdf_builder_save_template', 'pdf_builder_ajax_save_template');
add_action('wp_ajax_pdf_builder_get_template', 'pdf_builder_ajax_get_template');
add_action('wp_ajax_nopriv_pdf_builder_get_template', 'pdf_builder_ajax_get_template');
add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
add_action('wp_ajax_nopriv_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');

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

