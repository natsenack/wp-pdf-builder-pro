<?php
/**
 * PDF Builder Pro - Internationalization for Frontend
 *
 * Charge les traductions JavaScript pour le frontend React
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Charge les traductions pour le frontend
 */
function pdf_builder_pro_load_frontend_translations() {
    // Vérifier si nous sommes sur une page admin ou une page où le builder est utilisé
    if (!is_admin() && !wp_script_is('pdf-builder-admin', 'enqueued')) {
        return;
    }

    // Charger le domaine de traduction
    $domain = 'pdf-builder-pro';

    // Liste des clés de traduction pour le frontend
    $frontend_translations = array(
        // Interface générale
        'PDF Builder Pro' => __('PDF Builder Pro', $domain),
        'aperçu PDF' => __('aperçu PDF', $domain),
        'Aperçu PDF' => __('Aperçu PDF', $domain),
        'export PDF' => __('export PDF', $domain),
        'modifier' => __('modifier', $domain),
        'sauvegarder' => __('sauvegarder', $domain),
        'Templates' => __('Templates', $domain),
        'Paramètres' => __('Paramètres', $domain),

        // Accordéons
        'Order Info' => __('Order Info', $domain),
        'Customer' => __('Customer', $domain),
        'Order Totals' => __('Order Totals', $domain),
        'Company Info' => __('Company Info', $domain),
        'Shapes & Graphics' => __('Shapes & Graphics', $domain),

        // État des accordéons
        'Collapse' => __('Collapse', $domain),
        'Expand' => __('Expand', $domain),

        // Éléments Order Info
        'Order Number' => __('Order Number', $domain),
        'Order reference' => __('Order reference', $domain),
        'Order Date' => __('Order Date', $domain),
        'When order was placed' => __('When order was placed', $domain),
        'Invoice Number' => __('Invoice Number', $domain),
        'Auto-generated invoice ID' => __('Auto-generated invoice ID', $domain),
        'Invoice Date' => __('Invoice Date', $domain),
        'Invoice generation date' => __('Invoice generation date', $domain),
        'Billing Block' => __('Billing Block', $domain),
        'Complete billing info' => __('Complete billing info', $domain),

        // Éléments Customer
        'Customer Name' => __('Customer Name', $domain),
        'Full customer name' => __('Full customer name', $domain),
        'Billing Address' => __('Billing Address', $domain),
        'Complete billing address' => __('Complete billing address', $domain),
        'Shipping Address' => __('Shipping Address', $domain),
        'Delivery address' => __('Delivery address', $domain),
        'Customer Block' => __('Customer Block', $domain),
        'All customer details' => __('All customer details', $domain),

        // Éléments Order Totals
        'Subtotal' => __('Subtotal', $domain),
        'Before tax & shipping' => __('Before tax & shipping', $domain),
        'Tax' => __('Tax', $domain),
        'Tax amount' => __('Tax amount', $domain),
        'Shipping' => __('Shipping', $domain),
        'Delivery cost' => __('Delivery cost', $domain),
        'Total' => __('Total', $domain),
        'Final amount' => __('Final amount', $domain),
        'Products Table' => __('Products Table', $domain),
        'Detailed order items' => __('Detailed order items', $domain),

        // Éléments Company Info
        'Company Info' => __('Company Info', $domain),
        'Custom company text' => __('Custom company text', $domain),
        'Company Block' => __('Company Block', $domain),
        'Complete company details' => __('Complete company details', $domain),

        // Éléments Shapes & Graphics
        'Rectangle' => __('Rectangle', $domain),
        'Basic shape' => __('Basic shape', $domain),
        'Circle' => __('Circle', $domain),
        'Round shape' => __('Round shape', $domain),
        'Triangle' => __('Triangle', $domain),
        'Geometric shape' => __('Geometric shape', $domain),
        'Line' => __('Line', $domain),
        'Draw straight line' => __('Draw straight line', $domain),
        'Image' => __('Image', $domain),
        'Add image placeholder' => __('Add image placeholder', $domain),

        // Outils de la barre
        'Case à cocher' => __('Case à cocher', $domain),
        'Variables dynamiques' => __('Variables dynamiques', $domain),
        'Code-barres' => __('Code-barres', $domain),
        'Code QR' => __('Code QR', $domain),
        'Tableau' => __('Tableau', $domain),

        // États et messages
        'Initialisation du canvas...' => __('Initialisation du canvas...', $domain),
        'Mode dessin de ligne - Cliquez et tirez pour créer une ligne' => __('Mode dessin de ligne - Cliquez et tirez pour créer une ligne', $domain),

        // Tooltips et descriptions
        'Add order number field (displays #12345)' => __('Add order number field (displays #12345)', $domain),
        'Add order date field (displays order creation date)' => __('Add order date field (displays order creation date)', $domain),
        'Add invoice number field (auto-generated)' => __('Add invoice number field (auto-generated)', $domain),
        'Add invoice date field (current date)' => __('Add invoice date field (current date)', $domain),
        'Add complete billing information block' => __('Add complete billing information block', $domain),
        'Add customer full name' => __('Add customer full name', $domain),
        'Add complete billing address' => __('Add complete billing address', $domain),
        'Add complete shipping address' => __('Add complete shipping address', $domain),
        'Add complete customer information block' => __('Add complete customer information block', $domain),
        'Add order subtotal (before tax & shipping)' => __('Add order subtotal (before tax & shipping)', $domain),
        'Add tax amount' => __('Add tax amount', $domain),
        'Add shipping cost' => __('Add shipping cost', $domain),
        'Add final order total' => __('Add final order total', $domain),
        'Add detailed products table with quantities and prices' => __('Add detailed products table with quantities and prices', $domain),
        'Add custom company information text' => __('Add custom company information text', $domain),
        'Add complete company information block' => __('Add complete company information block', $domain),
        'Add rectangle shape' => __('Add rectangle shape', $domain),
        'Add circle shape' => __('Add circle shape', $domain),
        'Add triangle shape' => __('Add triangle shape', $domain),
        'Draw line (click and drag)' => __('Draw line (click and drag)', $domain),
        'Add image placeholder' => __('Add image placeholder', $domain),

        // Raccourcis clavier
        'Alt+1' => __('Alt+1', $domain),
        'Alt+2' => __('Alt+2', $domain),
        'Alt+3' => __('Alt+3', $domain),
        'Alt+4' => __('Alt+4', $domain),
        'Alt+5' => __('Alt+5', $domain),
        'Alt+6' => __('Alt+6', $domain),
        'Alt+7' => __('Alt+7', $domain),
        'Alt+8' => __('Alt+8', $domain),
        'Alt+9' => __('Alt+9', $domain),
        'Alt+Q' => __('Alt+Q', $domain),
        'Alt+W' => __('Alt+W', $domain),
        'Alt+E' => __('Alt+E', $domain),
        'Alt+R' => __('Alt+R', $domain),
        'Alt+T' => __('Alt+T', $domain),
        'Alt+C' => __('Alt+C', $domain),
        'Alt+B' => __('Alt+B', $domain),
        'Alt+S' => __('Alt+S', $domain),
        'Alt+O' => __('Alt+O', $domain),
        'Alt+G' => __('Alt+G', $domain),
        'Alt+L' => __('Alt+L', $domain),
        'Alt+I' => __('Alt+I', $domain),

        // États et messages supplémentaires
        'Désactiver le dessin de ligne' => __('Désactiver le dessin de ligne', $domain),
        'Cercle' => __('Cercle', $domain)
    );

    // Injecter les traductions dans JavaScript
    wp_localize_script('pdf-builder-admin', 'pdfBuilderTranslations', $frontend_translations);
}

/**
 * Modifier la fonction de traduction JavaScript pour utiliser WordPress
 */
function pdf_builder_pro_enqueue_frontend_i18n() {
    // Ajouter un script inline pour remplacer les traductions statiques
    $script = "
    (function() {
        if (typeof window.pdfBuilderTranslations !== 'undefined') {
            // Remplacer les traductions statiques par celles de WordPress
            window.PDFBuilderTranslations = window.pdfBuilderTranslations;
        }
    })();
    ";

    wp_add_inline_script('pdf-builder-admin', $script);
}

// Hooks
add_action('wp_enqueue_scripts', 'pdf_builder_pro_load_frontend_translations');
add_action('admin_enqueue_scripts', 'pdf_builder_pro_load_frontend_translations');
add_action('wp_enqueue_scripts', 'pdf_builder_pro_enqueue_frontend_i18n');
add_action('admin_enqueue_scripts', 'pdf_builder_pro_enqueue_frontend_i18n');