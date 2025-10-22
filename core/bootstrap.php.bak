<?php
/**
 * PDF Builder Pro - Bootstrap
 * Chargement différé des fonctionnalités du plugin
 */

if (!defined("ABSPATH")) {
    exit("Accès direct interdit");
}

// Charger les constantes
require_once __DIR__ . "/constants.php";

// Charger l'autoloader
require_once __DIR__ . "/autoloader.php";

/**
 * Fonction principale de chargement du plugin
 */
function pdf_builder_bootstrap() {
    static $loaded = false;
    if ($loaded) {
        return;
    }

    // Initialiser l autoloader
    PDF_Builder_Autoloader::init(PDF_BUILDER_PLUGIN_DIR);

    // Charger les classes de sécurité (Phase 5.8)
    pdf_builder_load_security_classes();

    // Configurer les headers de sécurité
    pdf_builder_setup_security_headers();

    // Charger la configuration
    if (file_exists(PDF_BUILDER_CONFIG_DIR . "config.php")) {
        require_once PDF_BUILDER_CONFIG_DIR . "config.php";
    }

    // Initialiser le core du plugin
    if (class_exists("PDF_Builder\\Core\\PDF_Builder_Core")) {
        PDF_Builder\Core\PDF_Builder_Core::init();
    }

    // Initialiser l administration
    if (class_exists("PDF_Builder\\Admin\\PDF_Builder_Admin")) {
        PDF_Builder\Admin\PDF_Builder_Admin::init();
    }

    $loaded = true;
}

/**
 * Charge les classes de sécurité
 */
function pdf_builder_load_security_classes() {
    $security_files = [
        __DIR__ . '/../src/Core/PDF_Builder_Security_Validator.php',
        __DIR__ . '/../src/Core/PDF_Builder_Path_Validator.php',
        __DIR__ . '/../src/Core/PDF_Builder_Rate_Limiter.php'
    ];

    foreach ($security_files as $file) {
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

/**
 * Configure les headers de sécurité
 */
function pdf_builder_setup_security_headers() {
    // Content Security Policy pour les pages admin PDF Builder
    add_action('send_headers', function() {
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'pdf-builder') === 0) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https: blob:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self';");
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: SAMEORIGIN");
            header("X-XSS-Protection: 1; mode=block");
            header("Referrer-Policy: strict-origin-when-cross-origin");
        }
    });

    // Headers de sécurité pour toutes les requêtes AJAX PDF Builder
    add_action('admin_init', function() {
        if (wp_doing_ajax() && isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') === 0) {
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: DENY");
            header("X-XSS-Protection: 1; mode=block");
        }
    });
}

/**
 * Fonction de chargement différé
 */
function pdf_builder_load_core() {
    pdf_builder_bootstrap();
}

/**
 * Fonction de vérification si le plugin doit se charger
 */
function pdf_builder_should_load() {
    // Conditions de chargement du plugin
    return true;
}
