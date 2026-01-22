<?php

/**
 * PDF Builder Pro - Aperçu AJAX Handler
 * Phase 1: Système d'aperçu côté serveur inspiré de WooCommerce PDF Invoice Builder
 */

namespace PDF_Builder\AJAX;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Empêcher la redéclaration de classe
if (!isset($GLOBALS['pdf_builder_preview_ajax_loaded'])) {
    $GLOBALS['pdf_builder_preview_ajax_loaded'] = true;
    error_log('[PDF Preview AJAX] File loaded, about to define class');
} else {
    error_log('[PDF Preview AJAX] File already loaded, skipping');
    return;
}

error_log('[PDF Preview AJAX] Defining class');

// All preview generation has been removed

