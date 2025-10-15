<?php
/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Définir la constante du répertoire du plugin si elle n'existe pas
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Fonction pour charger le core du plugin
function pdf_builder_load_core() {
    static $loaded = false;
    if ($loaded) return;

    // IMPORTANT: Ne charger QUE la nouvelle classe pour éviter les conflits
    // L'ancienne classe PDF_Builder_Admin_Old est complètement désactivée

    // Charger la classe principale PDF_Builder_Core
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php';
    }

    // Charger UNIQUEMENT la nouvelle classe d'administration
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/classes/class-pdf-builder-admin.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/class-pdf-builder-admin.php';
    }

    // NE PAS charger l'ancienne classe class-pdf-builder-admin.php

    // Charger les managers essentiels en premier pour éviter les dépendances circulaires
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Cache_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Cache_Manager.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Logger.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Logger.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php';
    }

    // Charger les managers canvas
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Elements_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Elements_Manager.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Interactions_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Canvas_Interactions_Manager.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Drag_Drop_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Drag_Drop_Manager.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Resize_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Resize_Manager.php';
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

    // Charger la configuration si pas déjà faite
    if (!function_exists('pdf_builder_should_load')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/config.php';
    }

    // Charger le core maintenant que c'est nécessaire
    pdf_builder_load_core();

    // Vérification plus robuste que la classe est chargée
    if (!class_exists('PDF_Builder_Core')) {
        // Essayer de charger manuellement si la fonction n'a pas fonctionné
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php';
        }
    }

    if (class_exists('PDF_Builder_Core') && method_exists('PDF_Builder_Core', 'getInstance')) {
        $core = PDF_Builder_Core::getInstance();
        $core->init();

        // Initialiser les paramètres par défaut du canvas
        pdf_builder_init_canvas_defaults();

        // Charger le générateur PDF
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/pdf-generator.php')) {
            require_once PDF_BUILDER_PLUGIN_DIR . 'includes/pdf-generator.php';
        }

        // Enregistrer l'action AJAX dès que possible
        error_log('PDF Builder Bootstrap: AJAX action registered in bootstrap');
        
        // Enregistrer les actions AJAX pour WooCommerce immédiatement
        add_action('wp_ajax_pdf_builder_generate_order_pdf', 'pdf_builder_ajax_generate_order_pdf_fallback', 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', 'pdf_builder_ajax_save_order_canvas_fallback', 1);
        add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce', 1);
        add_action('wp_ajax_pdf_builder_validate_preview', 'pdf_builder_ajax_validate_preview');
        add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
        add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
        add_action('wp_ajax_pdf_builder_unified_preview', 'pdf_builder_ajax_unified_preview_fallback', 1);

        // Initialiser l'interface d'administration
        if (is_admin() && class_exists('PDF_Builder_Admin')) {
            PDF_Builder_Admin::getInstance($core);
        }

        // L'API Manager sera initialisé automatiquement par le Core
        // Les routes seront enregistrées sur le hook rest_api_init
        // Mais pour les requêtes REST, nous devons nous assurer qu'elles sont disponibles
        if (defined('REST_REQUEST') && REST_REQUEST) {
            if (class_exists('PDF_Builder_API_Manager')) {
                $api_manager = PDF_Builder_API_Manager::getInstance();
                if (!$api_manager->is_initialized()) {
                    $api_manager->init();
                }
            }
        }
    }

    // Menu handled by PDF_Builder_Admin class - DÉPLACÉ dans pdf-builder-pro.php
    // add_action('admin_menu', 'pdf_builder_register_admin_menu_simple');

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
        if (class_exists('PDF_Builder_Core')) {
            try {
                PDF_Builder_Core::getInstance()->init();
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
            $core = PDF_Builder_Core::getInstance();
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
            $core = PDF_Builder_Core::getInstance();
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
            $core = PDF_Builder_Core::getInstance();
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
            $core = PDF_Builder_Core::getInstance();
            global $pdf_builder_core;
            $pdf_builder_core = $core;
            $core->render_settings_page();
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
            'pdf-builder-settings',
            'pdf_builder_settings_page_callback'
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
    error_log('PDF BUILDER - Fallback AJAX handler called for generate_order_pdf');
    
    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder_Core')) {
        return;
    }
    
    $core = PDF_Builder_Core::getInstance();
    $admin = PDF_Builder_Admin::getInstance();
    $woocommerce_integration = $admin ? $admin->get_woocommerce_integration() : null;
    
    if ($woocommerce_integration && method_exists($woocommerce_integration, 'ajax_generate_order_pdf')) {
        $woocommerce_integration->ajax_generate_order_pdf();
    } else {
        wp_send_json_error('WooCommerce integration not available');
    }
}

function pdf_builder_ajax_unified_preview_fallback() {
    error_log('PDF BUILDER - Fallback AJAX handler called for unified_preview');

    try {
        // Charger la classe WooCommerce integration si nécessaire
        if (!class_exists('PDF_Builder_WooCommerce_Integration')) {
            $integration_file = plugin_dir_path(__FILE__) . 'includes/classes/managers/class-pdf-builder-woocommerce-integration.php';
            if (file_exists($integration_file)) {
                error_log('PDF BUILDER - Fallback: Loading WooCommerce integration file');
                require_once $integration_file;
            } else {
                error_log('PDF BUILDER - Fallback: WooCommerce integration file not found: ' . $integration_file);
                wp_send_json_error('Fichier d\'intégration WooCommerce non trouvé');
                return;
            }
        }

        if (!class_exists('PDF_Builder_WooCommerce_Integration')) {
            error_log('PDF BUILDER - Fallback: PDF_Builder_WooCommerce_Integration class not available');
            wp_send_json_error('Classe d\'intégration WooCommerce non disponible');
            return;
        }

        error_log('PDF BUILDER - Fallback: Creating WooCommerce integration instance');
        $integration = new PDF_Builder_WooCommerce_Integration();

        if (!method_exists($integration, 'ajax_unified_preview')) {
            error_log('PDF BUILDER - Fallback: ajax_unified_preview method not found');
            wp_send_json_error('Méthode ajax_unified_preview non trouvée');
            return;
        }

        error_log('PDF BUILDER - Fallback: Calling ajax_unified_preview method');
        $integration->ajax_unified_preview();

    } catch (Exception $e) {
        error_log('PDF BUILDER - Fallback: Exception: ' . $e->getMessage());
        error_log('PDF BUILDER - Fallback: Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

function pdf_builder_ajax_save_order_canvas_fallback() {
    error_log('PDF BUILDER - Fallback AJAX handler called for save_order_canvas');
    
    // Charger le core si nécessaire
    if (!class_exists('PDF_Builder_Core')) {
        return;
    }
    
    $core = PDF_Builder_Core::getInstance();
    $admin = PDF_Builder_Admin::getInstance();
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

    // Générer un nouveau nonce
    $nonce = wp_create_nonce('pdf_builder_order_actions');

    // Retourner le nonce
    wp_send_json_success(array(
        'nonce' => $nonce,
        'timestamp' => time()
    ));
}

/**
 * AJAX handler pour valider l'aperçu côté serveur
 */
function pdf_builder_ajax_validate_preview() {
    // Vérifier les permissions
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
        return;
    }

    // Vérifier le nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    try {
        // DEBUG: Log complet des données reçues AVANT tout traitement
        error_log('PDF Builder Validation - RAW $_POST[elements]: ' . print_r($_POST['elements'], true));
        error_log('PDF Builder Validation - RAW strlen($_POST[elements]): ' . strlen($_POST['elements']));

        // Sauvegarder immédiatement les données brutes
        $raw_debug_file = __DIR__ . '/debug_raw_post_elements.txt';
        file_put_contents($raw_debug_file, "=== RAW POST ELEMENTS " . date('Y-m-d H:i:s') . " ===\n");
        file_put_contents($raw_debug_file, "Type: " . gettype($_POST['elements']) . "\n", FILE_APPEND);
        file_put_contents($raw_debug_file, "Length: " . strlen($_POST['elements']) . "\n", FILE_APPEND);
        file_put_contents($raw_debug_file, "Content:\n" . $_POST['elements'] . "\n", FILE_APPEND);
        file_put_contents($raw_debug_file, "=== END RAW ===\n\n", FILE_APPEND);

        // Récupérer les données JSON
        $json_data = isset($_POST['elements']) ? wp_unslash($_POST['elements']) : '';
        if (empty($json_data)) {
            wp_send_json_error('No elements data provided');
            return;
        }

        // DEBUG: Log complet des données reçues
        error_log('PDF Builder Validation - Raw JSON data length: ' . strlen($json_data));
        error_log('PDF Builder Validation - First 500 chars: ' . substr($json_data, 0, 500));
        error_log('PDF Builder Validation - Last 500 chars: ' . substr($json_data, -500));

        // DEBUG: Vérifier tous les headers et données POST
        error_log('PDF Builder Validation - All POST data: ' . print_r($_POST, true));
        error_log('PDF Builder Validation - Content-Type: ' . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set'));
        error_log('PDF Builder Validation - Request method: ' . $_SERVER['REQUEST_METHOD']);

        // Vérifier si les données sont URL-encoded (peuvent arriver ainsi via FormData)
        if (strpos($json_data, '%') !== false) {
            $original_length = strlen($json_data);
            $json_data = urldecode($json_data);
            error_log('PDF Builder Validation - Data was URL-encoded, original length: ' . $original_length . ', decoded length: ' . strlen($json_data));
        }

        // DEBUG: Sauvegarder les données pour analyse détaillée
        $debug_file = __DIR__ . '/debug_received_json.txt';
        file_put_contents($debug_file, "=== DEBUG SESSION " . date('Y-m-d H:i:s') . " ===\n");
        file_put_contents($debug_file, "Raw length: " . strlen($json_data) . "\n", FILE_APPEND);
        file_put_contents($debug_file, "First 1000 chars:\n" . substr($json_data, 0, 1000) . "\n\n", FILE_APPEND);
        file_put_contents($debug_file, "Last 1000 chars:\n" . substr($json_data, -1000) . "\n\n", FILE_APPEND);
        file_put_contents($debug_file, "Full content:\n" . $json_data . "\n\n", FILE_APPEND);
        file_put_contents($debug_file, "=== END DEBUG ===\n\n", FILE_APPEND);

        // Vérifier si les données semblent être du JSON valide
        $json_data_trimmed = trim($json_data);
        if (empty($json_data_trimmed)) {
            wp_send_json_error('Empty JSON data after trimming');
            return;
        }

        // Vérifier les premiers et derniers caractères
        $first_char = substr($json_data_trimmed, 0, 1);
        $last_char = substr($json_data_trimmed, -1);
        error_log('PDF Builder Validation - First char: ' . $first_char . ', Last char: ' . $last_char);

        if ($first_char !== '[') {
            error_log('PDF Builder Validation - ERROR: JSON does not start with [ - this is not an array!');
            file_put_contents($debug_file, "ERROR: Does not start with [\n", FILE_APPEND);
            wp_send_json_error('JSON data must start with [ (array)');
            return;
        }

        if ($last_char !== ']') {
            error_log('PDF Builder Validation - ERROR: JSON does not end with ]!');
            file_put_contents($debug_file, "ERROR: Does not end with ]\n", FILE_APPEND);
            wp_send_json_error('JSON data must end with ] (array)');
            return;
        }

        // Essayer de décoder le JSON
        $elements = json_decode($json_data, true);

        // DEBUG: Log de l'erreur JSON si elle existe
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = json_last_error_msg();
            $error_code = json_last_error();
            error_log('PDF Builder Validation - JSON decode error: ' . $error_msg . ' (code: ' . $error_code . ')');
            error_log('PDF Builder Validation - JSON data that failed (first 2000 chars): ' . substr($json_data, 0, 2000));

            // Sauvegarder les données problématiques pour analyse détaillée
            $failed_file = __DIR__ . '/debug_failed_json.txt';
            file_put_contents($failed_file, "=== FAILED JSON SESSION " . date('Y-m-d H:i:s') . " ===\n");
            file_put_contents($failed_file, "Error: " . $error_msg . " (code: " . $error_code . ")\n", FILE_APPEND);
            file_put_contents($failed_file, "Data length: " . strlen($json_data) . "\n", FILE_APPEND);
            file_put_contents($failed_file, "First 2000 chars:\n" . substr($json_data, 0, 2000) . "\n\n", FILE_APPEND);
            file_put_contents($failed_file, "Last 2000 chars:\n" . substr($json_data, -2000) . "\n\n", FILE_APPEND);
            file_put_contents($failed_file, "Full content:\n" . $json_data . "\n\n", FILE_APPEND);
            file_put_contents($failed_file, "=== END FAILED JSON ===\n\n", FILE_APPEND);

            wp_send_json_error('Invalid JSON data: ' . $error_msg);
            return;
        }

        // DEBUG: Log du succès du décodage
        error_log('PDF Builder Validation - JSON decoded successfully, elements count: ' . count($elements));

        // Validation basique des éléments
        if (!is_array($elements)) {
            wp_send_json_error('Elements must be an array');
            return;
        }

        // Compter les éléments valides
        $valid_count = 0;
        foreach ($elements as $element) {
            if (is_array($element) && isset($element['type']) && isset($element['id'])) {
                $valid_count++;
            }
        }

        // Retourner le succès avec les informations de validation
        wp_send_json_success(array(
            'success' => true,
            'elements_count' => count($elements),
            'valid_elements' => $valid_count,
            'width' => 595, // A4 width in points
            'height' => 842, // A4 height in points
            'server_validated' => true,
            'message' => 'Aperçu validé côté serveur avec succès'
        ));

    } catch (Exception $e) {
        wp_send_json_error('Erreur lors de la validation: ' . $e->getMessage());
    }
}

// Enregistrer les actions AJAX
add_action('wp_ajax_pdf_builder_validate_preview', 'pdf_builder_ajax_validate_preview');
add_action('wp_ajax_nopriv_pdf_builder_validate_preview', 'pdf_builder_ajax_validate_preview');
add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
add_action('wp_ajax_nopriv_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce');
add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
add_action('wp_ajax_nopriv_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback');
add_action('wp_ajax_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
add_action('wp_ajax_nopriv_pdf_builder_save_settings', 'pdf_builder_ajax_save_settings_fallback');
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
        'notification_events' => isset($_POST['notification_events']) ? array_map('sanitize_text_field', $_POST['notification_events']) : [],
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
    wp_send_json_success(array(
        'message' => __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
        'spacing' => $settings['canvas_border_spacing'] ?? 2
    ));
    exit;
}

