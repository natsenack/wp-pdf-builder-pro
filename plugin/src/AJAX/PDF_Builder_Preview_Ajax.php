<?php

/**
 * PDF Builder Pro - Aperçu AJAX Handler
 * Phase 1: Système d'aperçu côté serveur inspiré de WooCommerce PDF Invoice Builder
 */

namespace WP_PDF_Builder_Pro\AJAX;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PdfBuilderPreviewAjax
{
    public function __construct()
    {
        add_action('wp_ajax_pdf_builder_generate_preview', array($this, 'generate_preview'));
        add_action('wp_ajax_pdf_builder_get_preview_data', array($this, 'get_preview_data'));
    }

    /**
     * Génère l'aperçu PDF côté serveur
     */
    public function generatePreview()
    {
        try {
// Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_die('Forbidden');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_preview_nonce')) {
                wp_send_json_error('Invalid nonce');
            }

            // Récupération des données
            $template_data = json_decode(stripslashes($_POST['template_data'] ?? '{}'), true);
            $preview_type = sanitize_text_field($_POST['preview_type'] ?? 'sample');
            $order_id = intval($_POST['order_id'] ?? 0);
            if (empty($template_data)) {
                wp_send_json_error('Données du template manquantes');
            }

            // Création du générateur d'aperçu
            require_once plugin_dir_path(__FILE__) . 'Managers/PDF_Builder_Preview_Generator.php';
            $generator = new PDF_Builder_Preview_Generator($template_data, $preview_type, $order_id);
            $preview_url = $generator->generate_preview();
            wp_send_json_success(array(
                'preview_url' => $preview_url,
                'cache_key' => $generator->get_cache_key()
            ));
        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la génération de l\'aperçu: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les données d'aperçu (commandes disponibles, etc.)
     */
    public function getPreviewData()
    {
        try {
// Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_die('Forbidden');
            }

            // Vérification du nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_preview_nonce')) {
                wp_send_json_error('Invalid nonce');
            }

            $data = array(
                'sample_orders' => $this->get_sample_orders(),
                'recent_orders' => $this->get_recent_orders(),
                'company_info' => $this->get_company_info()
            );
            wp_send_json_success($data);
        } catch (Exception $e) {
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
                'total' => $order->get_formatted_order_total(),
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
new PDF_Builder_Preview_Ajax();
