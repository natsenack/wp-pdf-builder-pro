<?php
/**
 * PDF Builder Pro - Main Settings Logic (MINIMAL VERSION)
 * Core settings processing with NO JavaScript - clean and simple
 * Updated: 2025-11-18 20:10:00
 */

// Logs
error_log('PDF Builder: MINIMAL settings-main.php loaded - NO JavaScript');

// Security checks
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
    wp_die(__('You do not have permission to access this page.', 'pdf-builder-pro'));
}

// Load dependencies
require_once dirname(__FILE__) . '/settings-styles.php';

// Minimal data loading - NO JavaScript variables
$minimal_mode = true;
$notices = [];

// Basic settings loading for compatibility
$settings = get_option('pdf_builder_settings', []);
$company_phone_manual = get_option('pdf_builder_company_phone_manual', '');
$company_siret = get_option('pdf_builder_company_siret', '');
$company_vat = get_option('pdf_builder_company_vat', '');
$company_rcs = get_option('pdf_builder_company_rcs', '');
$company_capital = get_option('pdf_builder_company_capital', '');

// Basic form processing - NO AJAX
if (isset($_POST['submit']) && isset($_POST['_wpnonce'])) {
    if (wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_settings')) {
        // Basic field processing
        update_option('pdf_builder_company_phone_manual', sanitize_text_field($_POST['company_phone_manual'] ?? ''));
        update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret'] ?? ''));
        update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat'] ?? ''));
        update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs'] ?? ''));
        update_option('pdf_builder_company_capital', sanitize_text_field($_POST['company_capital'] ?? ''));

        $notices[] = '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
}

// Clean HTML structure - NO JavaScript
?>
<div class="wrap">
    <div class="pdf-builder-header">
        <h1><?php _e('⚙️ PDF Builder Pro Settings (MINIMAL MODE)', 'pdf-builder-pro'); ?></h1>
        <p><strong>Note:</strong> This is a minimal, JavaScript-free version for maximum performance.</p>
    </div>

    <?php foreach ($notices as $notice) {
        echo $notice;
    } ?>

    <!-- Simple Tabs (NO JavaScript) -->
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="?page=pdf-builder-settings&tab=general" class="nav-tab">General</a>
        <a href="?page=pdf-builder-settings&tab=licence" class="nav-tab">Licence</a>
        <a href="?page=pdf-builder-settings&tab=systeme" class="nav-tab">Systeme</a>
        <a href="?page=pdf-builder-settings&tab=acces" class="nav-tab">Accès</a>
        <a href="?page=pdf-builder-settings&tab=securite" class="nav-tab">Sécurité</a>
        <a href="?page=pdf-builder-settings&tab=pdf" class="nav-tab">PDF</a>
        <a href="?page=pdf-builder-settings&tab=contenu" class="nav-tab">Contenu</a>
        <a href="?page=pdf-builder-settings&tab=templates" class="nav-tab">Templates</a>
        <a href="?page=pdf-builder-settings&tab=developpeur" class="nav-tab">Développeur</a>
    </div>

    <!-- Tab Content (Static, no switching) -->
    <div id="general" class="tab-content">
        <?php require_once 'settings-general.php'; ?>
    </div>

    <!-- No modals, no floating buttons, no complex JavaScript -->
</div>
