<?php
/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Définir la constante du répertoire du plugin si elle n'existe pas
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// Fonction pour charger le core du plugin
function pdf_builder_load_core() {
    static $loaded = false;
    if ($loaded) return;

    // IMPORTANT: Charger les utilitaires essentiels en premier
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Debug_Helper.php';
    }
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Logger.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/utilities/PDF_Builder_Logger.php';
    }

    // IMPORTANT: Ne charger QUE la nouvelle classe pour éviter les conflits
    // L'ancienne classe PDF_Builder_Admin_Old est complètement désactivée

    // Charger la classe principale PDF_Builder_Core
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/PDF_Builder_Core.php';
    }

    // Charger UNIQUEMENT la nouvelle classe d'administration
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/classes/class-pdf-builder-admin-new.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/classes/class-pdf-builder-admin-new.php';
    }

    // NE PAS charger l'ancienne classe class-pdf-builder-admin.php

    // Charger les managers essentiels en premier pour éviter les dépendances circulaires
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Cache_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'includes/managers/PDF_Builder_Cache_Manager.php';
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

        // Enregistrer l'action AJAX dès que possible
        add_action('wp_ajax_pdf_builder_preview', 'pdf_builder_handle_preview_ajax');
        add_action('wp_ajax_nopriv_pdf_builder_preview', 'pdf_builder_handle_preview_ajax');
        error_log('PDF Builder Bootstrap: AJAX action registered in bootstrap');

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
        'canvas_resize_zones_enabled' => true,
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

