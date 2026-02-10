<?php

/**
 * PDF Builder Pro - Preview Data AJAX Handler
 * 
 * Récupère les données réelles WooCommerce pour l'aperçu des templates.
 * S'inspire de OrderValueRetriever du plugin concurrent
 * 
 * @package PDF_Builder
 * @subpackage AJAX
 * @since 1.0.0
 */

namespace PDF_Builder\AJAX;

use Exception;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

class PreviewDataAjax
{
    public function __construct()
    {
        // Action AJAX pour récupérer les données d'une commande pour l'aperçu
        \add_action('wp_ajax_pdf_builder_get_order_data_for_preview', array($this, 'getOrderDataForPreview'));
    }

    /**
     * Récupère toutes les données réelles d'une commande WooCommerce
     * 
     * Inspiré de OrderValueRetriever du plugin concurrent
     * Retourne les données dans le format RealOrderData
     * 
     * POST params:
     * - nonce: Nonce de sécurité
     * - orderId: ID de la commande WC
     * 
     * @return void JSON response avec RealOrderData
     */
    public function getOrderDataForPreview()
    {
        try {
            // 1. Vérifier les permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error([
                    'message' => 'Permissions insuffisantes',
                    'code' => 403
                ]);
            }

            // 2. Lire les données JSON du body si présentes
            $json_body = json_decode(file_get_contents('php://input'), true);
            
            // 3. Vérifier le nonce (priorité: GET, puis JSON body, puis POST)
            $nonce = isset($_GET['nonce']) 
                ? \sanitize_text_field($_GET['nonce']) 
                : (isset($json_body['nonce']) 
                    ? \sanitize_text_field($json_body['nonce']) 
                    : (isset($_POST['nonce']) ? \sanitize_text_field($_POST['nonce']) : ''));
                    
            if (!\pdf_builder_verify_nonce($nonce, 'pdf_builder_preview')) {
                \wp_send_json_error([
                    'message' => 'Nonce invalide',
                    'code' => 403
                ]);
            }

            // 4. Récupérer et valider l'ID commande (GET priority, then JSON, then POST)
            $order_id = isset($_GET['orderId']) 
                ? \intval($_GET['orderId']) 
                : (isset($json_body['orderId']) 
                    ? \intval($json_body['orderId']) 
                    : (isset($_POST['orderId']) ? \intval($_POST['orderId']) : 0));
                    
            if (empty($order_id)) {
                \wp_send_json_error([
                    'message' => 'Paramètre orderId manquant ou invalide',
                    'code' => 400
                ]);
            }

            // 4. Récupérer l'objet commande WooCommerce
            $order = \wc_get_order($order_id);
            if (!$order || !is_a($order, 'WC_Order')) {
                \wp_send_json_error([
                    'message' => "Commande WooCommerce ID {$order_id} non trouvée",
                    'code' => 404
                ]);
            }

            // 5. Construire les données réelles (RealOrderData)
            $real_order_data = $this->buildRealOrderData($order);

            // 6. Retourner les données
            \wp_send_json_success($real_order_data);

        } catch (Exception $e) {
            \wp_send_json_error([
                'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage(),
                'code' => 500,
                'exception' => $e->getMessage()
            ]);
        }
    }

    /**
     * Construit la structure RealOrderData depuis une commande WC
     * 
     * @param \WC_Order $order L'objet commande WooCommerce
     * @return array Les données réelles formatées
     */
    private function buildRealOrderData(\WC_Order $order): array
    {
        // Récupérer les produits commandés
        $products = array();
        foreach ($order->get_items('line_item') as $item) {
            $product = $item->get_product();
            if ($product) {
                // Récupérer l'image du produit
                $product_image_url = '';
                if ($product->get_image_id()) {
                    $product_image_url = wp_get_attachment_url($product->get_image_id());
                } else {
                    $product_image_url = wc_placeholder_img_src('woocommerce_gallery');
                }

                $products[] = array(
                    'name'     => $item->get_name(),
                    'sku'      => $product->get_sku() ?: 'N/A',
                    'quantity' => $item->get_quantity(),
                    'price'    => floatval($item->get_total() / $item->get_quantity()),
                    'total'    => floatval($item->get_total()),
                    'image'    => $product_image_url // NOUVEAU: URL de l'image produit
                );
            }
        }

        // Récupérer informations de transport
        $shipping_cost = floatval($order->get_shipping_total());
        
        // Récupérer informations de taxes
        $tax_cost = floatval($order->get_total_tax());
        
        // Récupérer les frais
        $fees = array();
        foreach ($order->get_fees() as $fee) {
            $fees[] = array(
                'name'  => $fee->get_name(),
                'total' => floatval($fee->get_total())
            );
        }

        // Récupérer les totaux
        $subtotal = floatval($order->get_subtotal());
        $total = floatval($order->get_total());
        $total_fees = array_sum(array_column($fees, 'total'));
        
        // Calculer la remise: subtotal + shipping + tax + fees - total = remise
        // (Formule WooCommerce standard)
        $discount = $subtotal + $shipping_cost + $tax_cost + $total_fees - $total;
        $discount = max(0, $discount); // Remise toujours positive
        
        // Récupérer les infos compte (order status, payment method)
        $payment_method = $order->get_payment_method_title();
        $transaction_id = $order->get_transaction_id();

        // Récupérer les infos client (adresse facturation)
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name = $order->get_billing_last_name();
        $billing_company = $order->get_billing_company();
        $billing_address_1 = $order->get_billing_address_1();
        $billing_address_2 = $order->get_billing_address_2();
        $billing_city = $order->get_billing_city();
        $billing_postcode = $order->get_billing_postcode();
        $billing_country = $order->get_billing_country();
        $billing_email = $order->get_billing_email();
        $billing_phone = $order->get_billing_phone();

        // Formater l'adresse complète
        $customer_address = $this->formatAddress([
            'address_1' => $billing_address_1,
            'address_2' => $billing_address_2,
            'city'      => $billing_city,
            'postcode'  => $billing_postcode,
            'country'   => $billing_country
        ]);

        // Récupérer les infos société (depuis les options WordPress)
        $company_name = \get_option('pdf_builder_company_name', 'Ma Société');
        $company_address = \get_option('pdf_builder_company_address', '');
        $company_phone = \get_option('pdf_builder_company_phone', '');
        $company_email = \get_option('pdf_builder_company_email', '');
        $company_website = \get_option('pdf_builder_company_website', '');
        $company_tax_id = \get_option('pdf_builder_company_tax_id', '');
        $company_registration_number = \get_option('pdf_builder_company_registration_number', '');

        // Construire et retourner RealOrderData
        return array(
            // Informations de base commande
            'orderId'               => strval($order->get_id()),
            'orderNumber'           => $order->get_order_number(),
            'orderDate'             => $order->get_date_created()->format('Y-m-d H:i:s'),
            'orderStatus'           => $order->get_status(),
            
            // Informations client
            'customerName'          => trim("{$billing_first_name} {$billing_last_name}"),
            'customerFirstName'     => $billing_first_name,
            'customerLastName'      => $billing_last_name,
            'customerCompany'       => $billing_company,
            'customerEmail'         => $billing_email,
            'customerPhone'         => $billing_phone,
            'customerAddress'       => $customer_address,
            'customerAddressLine1'  => $billing_address_1,
            'customerAddressLine2'  => $billing_address_2,
            'customerCity'          => $billing_city,
            'customerPostcode'      => $billing_postcode,
            'customerCountry'       => $billing_country,
            
            // Produits
            'products'              => $products,
            'productCount'          => count($products),
            
            // Totaux
            'subtotal'              => $subtotal,
            'shippingCost'          => $shipping_cost,
            'taxCost'               => $tax_cost,
            'taxRate'               => !empty($subtotal) ? ($tax_cost / $subtotal * 100) : 0,
            'discount'              => $discount,
            'total'                 => $total,
            'fees'                  => $fees,
            'totalFees'             => $total_fees,
            
            // Paiement
            'paymentMethod'         => $payment_method,
            'transactionId'         => $transaction_id ?: 'N/A',
            
            // Infos société
            'companyName'           => $company_name,
            'companyAddress'        => $company_address,
            'companyPhone'          => $company_phone,
            'companyEmail'          => $company_email,
            'companyWebsite'        => $company_website,
            'companyTaxId'          => $company_tax_id,
            'companyRegistrationNumber' => $company_registration_number,
            
            // Métadonnées
            'timestamp'             => \current_time('timestamp'),
            'isTest'                => false  // Indique que ce sont des vraies données
        );
    }

    /**
     * Formate une adresse à partir de ses composants
     * 
     * @param array $address_parts Les parties de l'adresse
     * @return string L'adresse formatée
     */
    private function formatAddress(array $address_parts): string
    {
        $address_lines = array();

        if (!empty($address_parts['address_1'])) {
            $address_lines[] = $address_parts['address_1'];
        }

        if (!empty($address_parts['address_2'])) {
            $address_lines[] = $address_parts['address_2'];
        }

        $city_line = array();
        if (!empty($address_parts['postcode'])) {
            $city_line[] = $address_parts['postcode'];
        }
        if (!empty($address_parts['city'])) {
            $city_line[] = $address_parts['city'];
        }
        if (!empty($city_line)) {
            $address_lines[] = implode(' ', $city_line);
        }

        if (!empty($address_parts['country'])) {
            $address_lines[] = $address_parts['country'];
        }

        return implode("\n", $address_lines);
    }
}

// Initialiser la classe si ce n'est pas déjà fait
if (!class_exists('PreviewDataAjax')) {
    new PreviewDataAjax();
}
