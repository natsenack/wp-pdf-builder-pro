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
