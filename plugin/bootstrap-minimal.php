<?php
/**
 * PDF Builder Pro - Bootstrap Minimal pour Tests
 * Version simplifiée pour diagnostiquer les problèmes de chargement
 */

// Empêcher l'accès direct
if (!defined('ABSPATH') && !defined('PHPUNIT_RUNNING')) {
    exit('Accès direct interdit');
}

/**
 * Fonction principale de chargement du bootstrap (version minimale)
 */
function pdf_builder_load_bootstrap() {
    // Protection contre les chargements multiples
    if (defined('PDF_BUILDER_BOOTSTRAP_LOADED') && PDF_BUILDER_BOOTSTRAP_LOADED) {
        return;
    }

    // CHARGER L'AUTOLOADER (priorité absolue)
    if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php')) {
        require_once PDF_BUILDER_PLUGIN_DIR . 'core/autoloader.php';
    }

    // CHARGER LES CLASSES WP_PDF_Builder_Pro MINIMALES
    pdf_builder_load_new_classes_minimal();

    // Marquer comme chargé
    define('PDF_BUILDER_BOOTSTRAP_LOADED', true);
}

/**
 * Fonction pour charger les classes essentielles seulement
 */
function pdf_builder_load_new_classes_minimal() {
    static $loaded = false;
    if ($loaded) return;

    // Classes essentielles pour les tests
    $essential_classes = [
        'data/DataProviderInterface.php',
        'data/SampleDataProvider.php',
        'generators/BaseGenerator.php',
    ];

    foreach ($essential_classes as $class_file) {
        $file_path = PDF_BUILDER_PLUGIN_DIR . $class_file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    $loaded = true;
}

// Fonction de compatibilité (si elle n'existe pas)
if (!function_exists('pdf_builder_load_core')) {
    function pdf_builder_load_core() {
        // Version vide pour compatibilité
    }
}

// Fonction de compatibilité (si elle n'existe pas)
if (!function_exists('pdf_builder_load_new_classes')) {
    function pdf_builder_load_new_classes() {
        // Version vide pour compatibilité
    }
}