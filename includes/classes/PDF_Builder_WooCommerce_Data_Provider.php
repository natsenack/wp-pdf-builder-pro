<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - WooCommerce Data Provider
 * Fournit les données WooCommerce pour les éléments du canvas
 */



class PDF_Builder_WooCommerce_Data_Provider {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Données de test pour l'aperçu
     */
    private $test_data = null;

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_test_data();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les données de test
     */
    private function init_test_data() {
        $this->test_data = array(
            'invoice_number' => 'INV-001',
            'invoice_date' => current_time('Y-m-d'),
            'order_number' => '#1234',
            'order_date' => current_time('Y-m-d H:i'),
            'billing_address' => "John Doe\n123 Main Street\nSpringfield, IL 62701\nUnited States",
            'shipping_address' => "John Doe\n456 Shipping Avenue\nSpringfield, IL 62702\nUnited States",
            'customer_name' => 'John Doe',
            'customer_email' => 'john.doe@example.com',
            'payment_method' => 'Carte de crédit (Stripe)',
            'order_status' => 'Traitée',
            'subtotal' => '$45.00',
            'discount' => '-$5.00',
            'shipping' => '$5.00',
            'taxes' => '$2.25',
            'total' => '$47.25',
            'refund' => '$0.00',
            'fees' => '$1.50',
            'quote_number' => 'QUO-001',
            'quote_date' => current_time('Y-m-d'),
            'quote_validity' => '30 jours',
            'quote_notes' => 'Conditions spéciales : paiement à 30 jours.',
            'products_table' => array(
                array(
                    'name' => 'Produit Exemple 1',
                    'quantity' => 1,
                    'price' => '$10.00',
                    'total' => '$10.00'
                ),
                array(
                    'name' => 'Produit Exemple 2',
                    'quantity' => 2,
                    'price' => '$15.00',
                    'total' => '$30.00'
                ),
                array(
                    'name' => 'Produit Exemple 3',
                    'quantity' => 1,
                    'price' => '$5.00',
                    'total' => '$5.00'
                )
            )
        );
    }

    /**
     * Obtenir les données pour un élément WooCommerce
     *
     * @param string $element_type Type de l'élément
     * @param int|null $order_id ID de la commande (null pour données de test)
     * @return mixed Données formatées pour l'élément
     */
    public function get_element_data($element_type, $order_id = null) {
        // Si pas d'order_id, retourner les données de test
        if ($order_id === null) {
            return $this->get_test_data($element_type);
        }

        // Récupérer les vraies données WooCommerce
        return $this->get_woocommerce_data($element_type, $order_id);
    }

    /**
     * Obtenir les données de test
     */
    private function get_test_data($element_type) {
        switch ($element_type) {
            case 'woocommerce-invoice-number':
                return $this->test_data['invoice_number'];
            case 'woocommerce-invoice-date':
                return $this->test_data['invoice_date'];
            case 'woocommerce-order-number':
                return $this->test_data['order_number'];
            case 'woocommerce-order-date':
                return $this->test_data['order_date'];
            case 'woocommerce-billing-address':
                return $this->test_data['billing_address'];
            case 'woocommerce-shipping-address':
                return $this->test_data['shipping_address'];
            case 'woocommerce-customer-name':
                return $this->test_data['customer_name'];
            case 'woocommerce-customer-email':
                return $this->test_data['customer_email'];
            case 'woocommerce-payment-method':
                return $this->test_data['payment_method'];
            case 'woocommerce-order-status':
                return $this->test_data['order_status'];
            case 'woocommerce-products-table':
                return $this->format_products_table($this->test_data['products_table']);
            case 'woocommerce-subtotal':
                return $this->test_data['subtotal'];
            case 'woocommerce-discount':
                return $this->test_data['discount'];
            case 'woocommerce-shipping':
                return $this->test_data['shipping'];
            case 'woocommerce-taxes':
                return $this->test_data['taxes'];
            case 'woocommerce-total':
                return $this->test_data['total'];
            case 'woocommerce-refund':
                return $this->test_data['refund'];
            case 'woocommerce-fees':
                return $this->test_data['fees'];
            case 'woocommerce-quote-number':
                return $this->test_data['quote_number'];
            case 'woocommerce-quote-date':
                return $this->test_data['quote_date'];
            case 'woocommerce-quote-validity':
                return $this->test_data['quote_validity'];
            case 'woocommerce-quote-notes':
                return $this->test_data['quote_notes'];
            default:
                return '';
        }
    }

    /**
     * Obtenir les vraies données WooCommerce
     */
    private function get_woocommerce_data($element_type, $order_id) {
        if (!function_exists('wc_get_order')) {
            return $this->get_test_data($element_type);
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return $this->get_test_data($element_type);
        }

        switch ($element_type) {
            case 'woocommerce-invoice-number':
                return $this->get_invoice_number($order);
            case 'woocommerce-invoice-date':
                return $this->get_invoice_date($order);
            case 'woocommerce-order-number':
                return '#' . $order->get_order_number();
            case 'woocommerce-order-date':
                return $order->get_date_created()->format('Y-m-d H:i');
            case 'woocommerce-billing-address':
                return $this->format_address($order, 'billing');
            case 'woocommerce-shipping-address':
                return $this->format_address($order, 'shipping');
            case 'woocommerce-customer-name':
                return $order->get_formatted_billing_full_name();
            case 'woocommerce-customer-email':
                return $order->get_billing_email();
            case 'woocommerce-payment-method':
                return $order->get_payment_method_title();
            case 'woocommerce-order-status':
                return wc_get_order_status_name($order->get_status());
            case 'woocommerce-products-table':
                return $this->format_products_table($this->get_order_items($order));
            case 'woocommerce-subtotal':
                return wc_price($order->get_subtotal());
            case 'woocommerce-discount':
                $discount = $order->get_discount_total();
                return $discount > 0 ? '-' . wc_price($discount) : '$0.00';
            case 'woocommerce-shipping':
                return wc_price($order->get_shipping_total());
            case 'woocommerce-taxes':
                return wc_price($order->get_total_tax());
            case 'woocommerce-total':
                return wc_price($order->get_total());
            case 'woocommerce-refund':
                $refunded = $order->get_total_refunded();
                return $refunded > 0 ? '-' . wc_price($refunded) : '$0.00';
            case 'woocommerce-fees':
                return wc_price($order->get_fee_total());
            case 'woocommerce-quote-number':
                return $this->get_quote_number($order);
            case 'woocommerce-quote-date':
                return $this->get_quote_date($order);
            case 'woocommerce-quote-validity':
                return $this->get_quote_validity($order);
            case 'woocommerce-quote-notes':
                return $this->get_quote_notes($order);
            default:
                return '';
        }
    }

    /**
     * Formater le tableau des produits
     */
    private function format_products_table($items) {
        if (!is_array($items)) {
            return '';
        }

        $output = '<table style="width: 100%; border-collapse: collapse; font-size: 12px;">';
        $output .= '<thead><tr style="border-bottom: 1px solid #000;">';
        $output .= '<th style="text-align: left; padding: 5px;">Produit</th>';
        $output .= '<th style="text-align: center; padding: 5px;">Qté</th>';
        $output .= '<th style="text-align: right; padding: 5px;">Prix</th>';
        $output .= '<th style="text-align: right; padding: 5px;">Total</th>';
        $output .= '</tr></thead><tbody>';

        foreach ($items as $item) {
            $name = isset($item['name']) ? $item['name'] : '';
            $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
            $price = isset($item['price']) ? $item['price'] : '$0.00';
            $total = isset($item['total']) ? $item['total'] : '$0.00';

            $output .= '<tr>';
            $output .= '<td style="padding: 5px;">' . esc_html($name) . '</td>';
            $output .= '<td style="text-align: center; padding: 5px;">' . esc_html($quantity) . '</td>';
            $output .= '<td style="text-align: right; padding: 5px;">' . esc_html($price) . '</td>';
            $output .= '<td style="text-align: right; padding: 5px;">' . esc_html($total) . '</td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table>';
        return $output;
    }

    /**
     * Obtenir les éléments de commande formatés
     */
    private function get_order_items($order) {
        $items = array();
        foreach ($order->get_items() as $item_id => $item) {
            $items[] = array(
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => wc_price($order->get_item_total($item, false, true)),
                'total' => wc_price($order->get_item_total($item, true, true))
            );
        }
        return $items;
    }

    /**
     * Formater une adresse
     */
    private function format_address($order, $type = 'billing') {
        $address = array();
        $method = "get_{$type}";

        $address[] = $order->{$method . '_first_name'}() . ' ' . $order->{$method . '_last_name'}();
        $address[] = $order->{$method . '_address_1'}();
        if ($order->{$method . '_address_2'}()) {
            $address[] = $order->{$method . '_address_2'}();
        }
        $address[] = $order->{$method . '_city'}() . ', ' . $order->{$method . '_state'}() . ' ' . $order->{$method . '_postcode'}();
        $address[] = $order->{$method . '_country'}();

        return implode("\n", array_filter($address));
    }

    /**
     * Méthodes pour les données spécifiques aux devis (peuvent être étendues)
     */
    private function get_invoice_number($order) {
        // Logique pour numéro de facture personnalisé
        return 'INV-' . $order->get_order_number();
    }

    private function get_invoice_date($order) {
        return $order->get_date_created()->format('Y-m-d');
    }

    private function get_quote_number($order) {
        return 'QUO-' . $order->get_order_number();
    }

    private function get_quote_date($order) {
        return $order->get_date_created()->format('Y-m-d');
    }

    private function get_quote_validity($order) {
        return '30 jours';
    }

    private function get_quote_notes($order) {
        return 'Conditions standard de paiement.';
    }

    /**
     * Obtenir toutes les données WooCommerce pour un template
     */
    public function get_template_data($order_id = null) {
        $data = array();

        $woocommerce_types = array(
            'woocommerce-invoice-number',
            'woocommerce-invoice-date',
            'woocommerce-order-number',
            'woocommerce-order-date',
            'woocommerce-billing-address',
            'woocommerce-shipping-address',
            'woocommerce-customer-name',
            'woocommerce-customer-email',
            'woocommerce-payment-method',
            'woocommerce-order-status',
            'woocommerce-products-table',
            'woocommerce-subtotal',
            'woocommerce-discount',
            'woocommerce-shipping',
            'woocommerce-taxes',
            'woocommerce-total',
            'woocommerce-refund',
            'woocommerce-fees',
            'woocommerce-quote-number',
            'woocommerce-quote-date',
            'woocommerce-quote-validity',
            'woocommerce-quote-notes'
        );

        foreach ($woocommerce_types as $type) {
            $data[$type] = $this->get_element_data($type, $order_id);
        }

        return $data;
    }
}


