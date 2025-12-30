<?php
/**
 * PDF Builder Pro - Admin Styles Module
 * Chargement des styles et ressources pour l'administration
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// CHARGER LE LOADER DES STYLES DE LA PAGE DE PARAMÈTRES
// ============================================================================

// Charge le CSS de settings au moment approprié (admin_print_styles)
if (is_admin()) {
    require_once __DIR__ . '/../../resources/templates/admin/settings-loader.php';
}