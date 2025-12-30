<?php
/**
 * PDF Builder Pro - AJAX Loader Module
 * Chargement et initialisation des handlers AJAX
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// CHARGER LES HANDLERS AJAX
// ============================================================================

// Inclure et initialiser les handlers AJAX
$ajax_handlers_path = PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/Ajax_Handlers.php';
if (file_exists($ajax_handlers_path)) {
    require_once $ajax_handlers_path;
}

// Charger les autres handlers AJAX pour le dispatcher unifié
$preview_ajax_path = PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Preview_Ajax.php';
if (file_exists($preview_ajax_path)) {
    require_once $preview_ajax_path;
}

$templates_ajax_path = PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/PDF_Builder_Templates_Ajax.php';
if (file_exists($templates_ajax_path)) {
    require_once $templates_ajax_path;
}

// PHASE 1: Charger le dispatcher AJAX unifié
if (file_exists(PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/Ajax_Dispatcher.php')) {
    require_once PDF_BUILDER_PLUGIN_DIR . 'src/AJAX/Ajax_Dispatcher.php';
}