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

class PdfBuilderPreviewAjax
{
    public function __construct()
    {
        error_log('[PDF Preview AJAX] Constructor called, registering hooks');
        add_action('wp_ajax_pdf_builder_generate_preview', array($this, 'generatePreview'));
        add_action('wp_ajax_pdf_builder_get_preview_data', array($this, 'getPreviewData'));
        error_log('[PDF Preview AJAX] Hooks registered successfully');
    }

    /**
     * Génère l'aperçu PDF côté serveur
     */
    public function generatePreview()
    {
        error_log('[PDF Preview AJAX] Handler called');
        try {
            error_log('[PDF Preview AJAX] Starting permission check');
// Vérification des permissions
            if (!current_user_can('manage_options')) {
                error_log('[PDF Preview AJAX] Permission denied');
                wp_die('Forbidden');
            }

            error_log('[PDF Preview AJAX] Checking nonce');
// Vérification du nonce
            if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pdf_builder_order_actions')) {
                error_log('[PDF Preview AJAX] Invalid nonce: ' . ($_POST['_wpnonce'] ?? 'none'));
                wp_send_json_error('Invalid nonce');
            }

            error_log('[PDF Preview AJAX] Retrieving data');
// Récupération des données
            $template_data = json_decode(stripslashes($_POST['template_data'] ?? '{}'), true);
            $preview_type = sanitize_text_field($_POST['preview_type'] ?? 'sample');
            $order_id = intval($_POST['order_id'] ?? 0);
            error_log('[PDF Preview AJAX] Template data: ' . json_encode($template_data));
            error_log('[PDF Preview AJAX] Preview type: ' . $preview_type);
            if (empty($template_data)) {
                error_log('[PDF Preview AJAX] Template data empty');
                wp_send_json_error('Données du template manquantes');
            }

            // Création du générateur d'aperçu
            require_once plugin_dir_path(__FILE__) . 'Managers/PDF_Builder_Preview_Generator.php';
            $generator = new Managers\PDF_Builder_Preview_Generator($template_data, $preview_type, $order_id);
            $preview_url = $generator->generate_preview();
            error_log('[PDF Preview AJAX] Preview generated successfully: ' . $preview_url);
            wp_send_json_success(array(
                'image_url' => $preview_url,
                'cache_key' => $generator->get_cache_key()
            ));
        } catch (Exception $e) {
            error_log('[PDF Preview AJAX] Exception: ' . $e->getMessage());
            error_log('[PDF Preview AJAX] Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur lors de la génération de l\'aperçu: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les données d'aperçu (commandes disponibles, etc.)
     */
    public function getPreviewData()
    {
        error_log('[PDF Preview AJAX] getPreviewData called');
        try {
// Vérification des permissions
            if (!current_user_can('manage_options')) {
                error_log('[PDF Preview AJAX] getPreviewData: Permission denied');
                wp_die('Forbidden');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                error_log('[PDF Preview AJAX] getPreviewData: Invalid nonce');
                wp_send_json_error('Invalid nonce');
            }

            $data = array(
                'sample_orders' => $this->get_sample_orders(),
                'recent_orders' => $this->get_recent_orders(),
                'company_info' => $this->get_company_info()
            );
            error_log('[PDF Preview AJAX] getPreviewData: Success');
            wp_send_json_success($data);
        } catch (Exception $e) {
            error_log('[PDF Preview AJAX] getPreviewData: Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur lors de la récupération des données: ' . $e->getMessage());
        }
    }

    /**
     * Récupère des commandes d'exemple pour l'aperçu
     */
    private function getSampleOrders()
    {
        $sample_orders = array();
// Récupère les 5 dernières commandes complétées
        $orders = wc_get_orders(array(
            'limit' => 5,
            'status' => 'completed',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        foreach ($orders as $order) {
            $sample_orders[] = array(
                'id' => $order->get_id(),
                'number' => $order->get_order_number(),
                'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'total' => function_exists('wc_price') ? wc_price($order->get_total()) : number_format($order->get_total(), 2),
                'date' => $order->get_date_created()->format('d/m/Y')
            );
        }

        return $sample_orders;
    }

    /**
     * Récupère les commandes récentes
     */
    private function getRecentOrders()
    {
        return $this->get_sample_orders(); // Même logique pour l'instant
    }

    /**
     * Récupère les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return array(
            'name' => get_option('woocommerce_store_name', get_bloginfo('name')),
            'address' => get_option('woocommerce_store_address', ''),
            'city' => get_option('woocommerce_store_city', ''),
            'postcode' => get_option('woocommerce_store_postcode', ''),
            'country' => get_option('woocommerce_default_country', ''),
            'email' => get_option('woocommerce_email_from_address', get_option('admin_email')),
            'phone' => get_option('woocommerce_store_phone', '')
        );
    }
}

// Initialisation
if (!isset($GLOBALS['pdf_builder_preview_ajax_instantiated'])) {
    $GLOBALS['pdf_builder_preview_ajax_instantiated'] = true;
    error_log('[PDF Preview AJAX] About to instantiate class');
    new PdfBuilderPreviewAjax();
    error_log('[PDF Preview AJAX] Class instantiated successfully');
}

