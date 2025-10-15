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
        add_action('wp_ajax_pdf_builder_preview_order_pdf', 'pdf_builder_ajax_preview_order_pdf_fallback', 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', 'pdf_builder_ajax_save_order_canvas_fallback', 1);
        add_action('wp_ajax_pdf_builder_get_fresh_nonce', 'pdf_builder_ajax_get_fresh_nonce', 1);
        add_action('wp_ajax_pdf_builder_validate_preview', 'pdf_builder_ajax_validate_preview');
        add_action('wp_ajax_pdf_builder_get_settings', 'pdf_builder_ajax_get_settings_fallback', 1);

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

function pdf_builder_ajax_preview_order_pdf_fallback() {
    error_log('PDF BUILDER - Fallback AJAX handler called for preview_order_pdf');

    try {
        // Vérifications de sécurité de base
        if (!current_user_can('manage_woocommerce')) {
            error_log('PDF BUILDER - Fallback: Permissions insuffisantes');
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('PDF BUILDER - Fallback: Nonce invalide');
            wp_send_json_error('Sécurité: Nonce invalide');
            return;
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
        error_log('PDF BUILDER - Fallback: order_id=' . $order_id . ', template_id=' . ($template_id ?: 'null'));

        if (!$order_id) {
            error_log('PDF BUILDER - Fallback: ID commande manquant');
            wp_send_json_error('ID commande manquant');
            return;
        }

        // Charger les fichiers nécessaires directement
        $plugin_dir = plugin_dir_path(__FILE__);
        if (!class_exists('PDF_Builder_Pro_Generator')) {
            $generator_file = $plugin_dir . 'includes/pdf-generator.php';
            if (file_exists($generator_file)) {
                error_log('PDF BUILDER - Fallback: Loading generator file');
                require_once $generator_file;
            } else {
                error_log('PDF BUILDER - Fallback: Generator file not found: ' . $generator_file);
                wp_send_json_error('Fichier générateur non trouvé');
                return;
            }
        }

        if (!class_exists('PDF_Builder_Pro_Generator')) {
            error_log('PDF BUILDER - Fallback: PDF_Builder_Pro_Generator class still not available');
            wp_send_json_error('Classe générateur non disponible');
            return;
        }

        error_log('PDF BUILDER - Fallback: Creating generator instance');
        $generator = new PDF_Builder_Pro_Generator();

        error_log('PDF BUILDER - Fallback: Calling generate_simple_preview');
        $result = $generator->generate_simple_preview($order_id, $template_id);

        if (is_wp_error($result)) {
            error_log('PDF BUILDER - Fallback: Error from generate_simple_preview: ' . $result->get_error_message());
            wp_send_json_error($result->get_error_message());
        } else {
            error_log('PDF BUILDER - Fallback: Success, URL: ' . $result);
            wp_send_json_success(['url' => $result]);
        }

    } catch (Exception $e) {
        error_log('PDF BUILDER - Fallback: Exception: ' . $e->getMessage());
        error_log('PDF BUILDER - Fallback: Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}function pdf_builder_ajax_save_order_canvas_fallback() {
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

