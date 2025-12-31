<?php

/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

// Empêcher l'accès direct (sauf pour les tests)
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

// Définir les constantes essentielles si elles ne sont pas déjà définies
if (!defined('PDF_BUILDER_PLUGIN_FILE')) {
    define('PDF_BUILDER_PLUGIN_FILE', __FILE__);
}
if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
    define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');
}

// ============================================================================
// CHARGEMENT DES MODULES DE BOOTSTRAP
// ============================================================================

// Charger les modules de bootstrap dans l'ordre approprié
$bootstrap_modules = [
    'emergency-loader.php',      // Fonctions d'urgence et vérifications de classe
    'deferred-initialization.php', // Initialisation différée et alias de classe
    'ajax-loader.php',           // Chargement des handlers AJAX
    'canvas-defaults.php',       // Paramètres par défaut du canvas
    'admin-styles.php',          // Styles et ressources admin
    'security-audit.php',        // Audit de sécurité et hardening
    'input-validation.php',      // Validation d'entrée renforcée
    'security-logging.php',      // Logs de sécurité et monitoring
    // 'asset-compression.php',     // Compression et optimisation des assets - DÉSACTIVÉ
    'ajax-actions.php',          // Actions AJAX pour les paramètres
    'task-scheduler.php'         // Planificateur de tâches
];

foreach ($bootstrap_modules as $module) {
    $module_path = PDF_BUILDER_PLUGIN_DIR . 'src/bootstrap/' . $module;
    if (file_exists($module_path)) {
        require_once $module_path;
    }
}

// ============================================================================
// FONCTIONS DE CHARGEMENT DU CORE
// ============================================================================

// Fonction pour charger le core du plugin
function pdf_builder_load_core()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }

    // Charger le autoloader pour le nouveau système PSR-4 - DISABLED
    // if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
    //     require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    // }

    // Charger la configuration si pas déjà faite
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'config/config.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'config/config.php';
    }

    // Charger le core maintenant que l'autoloader est prêt
    pdf_builder_load_new_classes();

    // Charger manuellement le Thumbnail Manager pour s'assurer qu'il est disponible
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php';
    }

    // CHARGER LE HANDLER DE TEST DE LICENCE (toujours chargé pour permettre l'activation/désactivation)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php';
        // Initialiser le handler toujours (pas seulement si le mode test est activé)
        if (class_exists('PDF_Builder\\License\\LicenseTestHandler')) {
            $license_test_handler = \PDF_Builder\License\LicenseTestHandler::getInstance();
            $license_test_handler->init();
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

    // INITIALISER LE VALIDATEUR DE SÉCURITÉ APRÈS LE CHARGEMENT DE WORDPRESS
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator')) {
        \PDF_Builder\Core\PDF_Builder_Security_Validator::get_instance()->init();
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE CANVAS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php';
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE SAUVEGARDE/RESTAURATION
    // Nécessaire pour l'onglet système même si la sauvegarde automatique n'est pas activée
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php';
        // Initialiser l'instance
        \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
    }

    // INITIALISER LE GESTIONNAIRE DE TUTORIELS - SUPPRIMÉ

    // ENREGISTRER LES HANDLERS AJAX POUR LE CANVAS
    if (class_exists('PDF_Builder\\Admin\\Canvas_AJAX_Handler')) {
        \PDF_Builder\Admin\Canvas_AJAX_Handler::register_hooks();
    }

    // CHARGER LE GESTIONNAIRE DE NOTIFICATIONS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php';
        // Initialiser l'instance
        PDF_Builder_Notification_Manager::get_instance();
    }

    // CHARGER LES STYLES ET SCRIPTS DES NOTIFICATIONS - DESACTIVE TEMPORAIREMENT
    /*
    add_action('admin_enqueue_scripts', function() {
        // Charger le CSS des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'assets/css/notifications.css')) {
            wp_enqueue_style(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'assets/css/notifications.css',
                array(),
                PDF_BUILDER_VERSION . '-' . time(),
                'all'
            );
        }

        // Charger le JavaScript des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'assets/js/notifications.js')) {
            wp_enqueue_script(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/notifications.js',
                array('jquery'),
                PDF_BUILDER_VERSION . '-' . time(),
                true
            );
        }
    });
    */

    $loaded = true;
}

// Fonction pour charger les nouvelles classes PDF_Builder
function pdf_builder_load_new_classes()
{
    static $new_classes_loaded = false;
    if ($new_classes_loaded) {
        return;
    }

    // Charger les interfaces et classes de données
    $data_classes = [
        'src/Interfaces/DataProviderInterface.php',
        'config/data/SampleDataProvider.php',
        'config/data/WooCommerceDataProvider.php'
    ];
    foreach ($data_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les générateurs
    $generator_classes = [
        'src/Generators/BaseGenerator.php',
        'src/Generators/PDFGenerator.php',
        'src/Generators/GeneratorManager.php'
    ];
    foreach ($generator_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger les éléments et contrats
    $element_classes = [
        'src/Elements/ElementContracts.php'
    ];
    foreach ($element_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    // Charger le core et conventions
    $core_classes = [
        'src/Core/Conventions.php'
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

    // Charger les états
    $state_classes = [
        'config/states/PreviewStateManager.php'
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

    // CHARGER L'AUTOLOADER POUR LES NOUVELLES CLASSES (PDF_Builder)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/core/autoloader.php';
        if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
            \PDF_Builder\Core\PdfBuilderAutoloader::init(PDF_BUILDER_PLUGIN_DIR);
        }
    }

    // Charger la configuration si pas déjà faite
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'config/config.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'config/config.php';
    }

    // Charger le core maintenant que l'autoloader est prêt
    pdf_builder_load_core();
    pdf_builder_load_new_classes();

    // Charger manuellement le Thumbnail Manager pour s'assurer qu'il est disponible
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Thumbnail_Manager.php';
    }

    // CHARGER LE HANDLER DE TEST DE LICENCE (toujours chargé pour permettre l'activation/désactivation)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/License/license-test-handler.php';
        // Initialiser le handler toujours (pas seulement si le mode test est activé)
        if (class_exists('PDF_Builder\\License\\LicenseTestHandler')) {
            $license_test_handler = \PDF_Builder\License\LicenseTestHandler::getInstance();
            $license_test_handler->init();
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

    // INITIALISER LE VALIDATEUR DE SÉCURITÉ APRÈS LE CHARGEMENT DE WORDPRESS
    if (class_exists('PDF_Builder\\Core\\PDF_Builder_Security_Validator')) {
        \PDF_Builder\Core\PDF_Builder_Security_Validator::get_instance()->init();
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE CANVAS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Canvas/Canvas_Manager.php';
    }

    // CHARGER ET INITIALISER LE GESTIONNAIRE DE SAUVEGARDE/RESTAURATION
    // Nécessaire pour l'onglet système même si la sauvegarde automatique n'est pas activée
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Managers/PDF_Builder_Backup_Restore_Manager.php';
        // Initialiser l'instance
        \PDF_Builder\Managers\PdfBuilderBackupRestoreManager::getInstance();
    }

    // INITIALISER LE GESTIONNAIRE DE TUTORIELS - SUPPRIMÉ

    // ENREGISTRER LES HANDLERS AJAX POUR LE CANVAS
    if (class_exists('PDF_Builder\\Admin\\Canvas_AJAX_Handler')) {
        \PDF_Builder\Admin\Canvas_AJAX_Handler::register_hooks();
    }

    // CHARGER LE GESTIONNAIRE DE NOTIFICATIONS
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Notification_Manager.php';
        // Initialiser l'instance
        PDF_Builder_Notification_Manager::get_instance();
    }

    // CHARGER LES STYLES ET SCRIPTS DES NOTIFICATIONS - DESACTIVE TEMPORAIREMENT
    /*
    add_action('admin_enqueue_scripts', function() {
        // Charger le CSS des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'assets/css/notifications.css')) {
            wp_enqueue_style(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'assets/css/notifications.css',
                array(),
                PDF_BUILDER_VERSION . '-' . time(),
                'all'
            );
        }

        // Charger le JavaScript des notifications
        if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'assets/js/notifications.js')) {
            wp_enqueue_script(
                'pdf-builder-notifications',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/notifications.js',
                array('jquery'),
                PDF_BUILDER_VERSION . '-' . time(),
                true
            );
        }
    });
    */

    // INITIALISER L'ADMIN SI DANS L'INTERFACE D'ADMINISTRATION
    if (is_admin() && class_exists('PDF_Builder\\Admin\\PdfBuilderAdmin')) {
        \PDF_Builder\Admin\PdfBuilderAdmin::getInstance();
    }
}

// ============================================================================
// INITIALISATION DU PLUGIN
// ============================================================================

// Initialiser le chargement du bootstrap quand WordPress est prêt
if (function_exists('add_action')) {
    add_action('plugins_loaded', function() {
        pdf_builder_load_bootstrap();
    }, 5);
}

// ============================================================================
// FIN DU BOOTSTRAP
// ============================================================================
