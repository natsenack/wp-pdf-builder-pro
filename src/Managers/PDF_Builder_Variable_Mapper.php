<?php

/**
 * PDF Builder Pro - Variable Mapper
 *
 * Maps dynamic variables to WooCommerce order data
 *
 * @package PDF_Builder_Pro
 * @version 1.0
 * @since   5.4
 */

namespace PDF_Builder\Managers;

// Importer les classes nécessaires
use DateTime;
use WC_DateTime;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class PDFBuilderVariableMapper
 *
 * Handles mapping of dynamic variables to WooCommerce order data
 */
class PDFBuilderVariableMapper
{
    /**
     * WooCommerce order object
     *
     * @var WC_Order
     */
    private $order;

    /**
     * Constructor
     *
     * @param WC_Order $order WooCommerce order object
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get all available variables with their values
     *
     * @return array Array of variable => value mappings
     */
    public function getAllVariables()
    {
        if (!$this->order) {
            return array_merge(
                $this->getOrderVariables(),
                $this->getCustomerVariables(),
                $this->getAddressVariables(),
                $this->getFinancialVariables(),
                $this->getPaymentVariables(),
                $this->getProductVariables(),
                $this->getCompanyVariables()
            );
        }

        return array_merge(
            $this->getOrderVariables(),
            $this->getCustomerVariables(),
            $this->getAddressVariables(),
            $this->getFinancialVariables(),
            $this->getPaymentVariables(),
            $this->getProductVariables(),
            $this->getCompanyVariables()
        );
    }

    /**
     * Get order-related variables
     *
     * @return array
     */
    private function getOrderVariables()
    {
        if (!$this->order) {
            return array(
                'order_number' => '',
                'order_date' => '',
                'order_date_time' => '',
                'order_date_modified' => '',
                'order_total' => '',
                'order_status' => '',
                'currency' => ''
            );
        }

        return array(
            'order_number' => $this->order->get_order_number(),
            'order_date' => $this->formatDate($this->order->get_date_created()),
            'order_date_time' => $this->formatDatetime($this->order->get_date_created()),
            'order_date_modified' => $this->formatDate($this->order->get_date_modified()),
            'order_total' => $this->formatCurrency($this->order->get_total()),
            'order_status' => $this->getOrderStatusLabel($this->order->get_status()),
            'currency' => $this->order->get_currency()
        );
    }

    /**
     * Get customer-related variables
     *
     * @return array
     */
    private function getCustomerVariables()
    {
        if (!$this->order) {
            return array(
                'customer_name' => '',
                'customer_first_name' => '',
                'customer_last_name' => '',
                'customer_email' => '',
                'customer_phone' => '',
                'customer_note' => ''
            );
        }

        return array(
            'customer_name' => $this->order->get_formatted_billing_full_name(),
            'customer_first_name' => $this->order->get_billing_first_name(),
            'customer_last_name' => $this->order->get_billing_last_name(),
            'customer_email' => $this->order->get_billing_email(),
            'customer_phone' => $this->order->get_billing_phone(),
            'customer_note' => $this->order->get_customer_note()
        );
    }

    /**
     * Get address-related variables
     *
     * @return array
     */
    private function getAddressVariables()
    {
        if (!$this->order) {
            return array(
                'billing_address' => '',
                'shipping_address' => '',
                'billing_first_name' => '',
                'billing_last_name' => '',
                'billing_company' => '',
                'billing_address_1' => '',
                'billing_address_2' => '',
                'billing_city' => '',
                'billing_postcode' => '',
                'billing_country' => '',
                'billing_state' => ''
            );
        }

        return array(
            'billing_address' => $this->order->get_formatted_billing_address(),
            'shipping_address' => $this->order->get_formatted_shipping_address(),
            'billing_first_name' => $this->order->get_billing_first_name(),
            'billing_last_name' => $this->order->get_billing_last_name(),
            'billing_company' => $this->order->get_billing_company(),
            'billing_address_1' => $this->order->get_billing_address_1(),
            'billing_address_2' => $this->order->get_billing_address_2(),
            'billing_city' => $this->order->get_billing_city(),
            'billing_postcode' => $this->order->get_billing_postcode(),
            'billing_country' => $this->getCountryName($this->order->get_billing_country()),
            'billing_state' => $this->order->get_billing_state()
        );
    }

    /**
     * Get financial variables
     *
     * @return array
     */
    private function getFinancialVariables()
    {
        if (!$this->order) {
            return array(
                'subtotal' => '',
                'tax_amount' => '',
                'shipping_amount' => '',
                'discount_amount' => '',
                'total_excl_tax' => ''
            );
        }

        return array(
            'subtotal' => $this->formatCurrency($this->calculateSubtotalWithFees()),
            'tax_amount' => $this->formatCurrency($this->order->get_total_tax()),
            'shipping_amount' => $this->formatCurrency($this->order->get_shipping_total()),
            'discount_amount' => $this->formatCurrency($this->order->get_discount_total()),
            'total_excl_tax' => $this->formatCurrency($this->order->get_total() - $this->order->get_total_tax())
        );
    }

    /**
     * Calculate subtotal including fees (treats fees as products)
     *
     * @return float
     */
    private function calculateSubtotalWithFees()
    {
        if (!$this->order) {
            return 0;
        }

        $subtotal = $this->order->get_subtotal();

        // Add fees to subtotal (treating them as products)
        foreach ($this->order->get_fees() as $fee) {
            $subtotal += $fee->get_total();
        }

        return $subtotal;
    }

    /**
     * Get payment-related variables
     *
     * @return array
     */
    private function getPaymentVariables()
    {
        if (!$this->order) {
            return array(
                'payment_method' => '',
                'payment_method_code' => '',
                'transaction_id' => ''
            );
        }

        return array(
            'payment_method' => $this->order->get_payment_method_title(),
            'payment_method_code' => $this->order->get_payment_method(),
            'transaction_id' => $this->order->get_transaction_id()
        );
    }

    /**
     * Get product-related variables
     *
     * @return array
     */
    private function getProductVariables()
    {
        if (!$this->order) {
            return array(
                'product_name' => '',
                'product_qty' => '',
                'product_price' => '',
                'product_total' => '',
                'product_sku' => '',
                'products_list' => ''
            );
        }

        $items = $this->order->get_items();
        $fees = $this->order->get_fees();

        // Combiner les produits et les frais
        $all_items = array_merge($items, $fees);
        $first_item = reset($items); // Garder le premier produit pour les variables individuelles

        $products_list = array();
        foreach ($all_items as $item) {
            // Traiter tous les types d'items (produits et frais)
            if (method_exists($item, 'get_name') && method_exists($item, 'get_total')) {
                $name = $item->get_name();
                $total = $item->get_total();

                // Pour les produits, récupérer la quantité, pour les frais utiliser 1
                $quantity = method_exists($item, 'get_quantity') ? $item->get_quantity() : 1;

                $products_list[] = sprintf(
                    '%s (x%d) - %s',
                    $name,
                    $quantity,
                    $this->formatCurrency($total)
                );
            }
        }

        return array(
            'product_name' => $first_item ? $first_item->get_name() : '',
            'product_qty' => $first_item ? $first_item->get_quantity() : '',
            'product_price' => $first_item && $first_item->get_product() ? $this->formatCurrency($first_item->get_product()->get_price()) : '',
            'product_total' => $first_item ? $this->formatCurrency($first_item->get_total()) : '',
            'product_sku' => $first_item && $first_item->get_product() ? $first_item->get_product()->get_sku() : '',
            'products_list' => implode("\n", $products_list)
        );
    }

    /**
     * Get company-related variables
     *
     * @return array
     */
    private function getCompanyVariables()
    {
        // Get company info from WooCommerce settings
        $company_name = get_option('woocommerce_store_name', '');
        $company_address = get_option('woocommerce_store_address', '');
        $company_city = get_option('woocommerce_store_city', '');
        $company_postcode = get_option('woocommerce_store_postcode', '');
        $company_country = get_option('woocommerce_default_country', '');

        $full_address = array_filter(
            array(
            $company_address,
            $company_city,
            $company_postcode,
            $this->getCountryName($company_country)
            )
        );

        return array(
            'company_name' => $company_name,
            'company_address' => implode(', ', $full_address),
            'company_phone' => get_option('woocommerce_store_phone', ''),
            'company_email' => get_option('admin_email', '')
        );
    }

    /**
     * Format currency according to WooCommerce settings
     *
     * @param  float $amount Amount to format
     * @return string Formatted currency
     */
    private function formatCurrency($amount)
    {
        return wc_price($amount, array('currency' => $this->order->get_currency()));
    }

    /**
     * Format date according to WordPress settings
     *
     * @param  WC_DateTime|DateTime|string $date Date to format
     * @return string Formatted date
     */
    private function formatDate($date)
    {
        if (is_a($date, 'WC_DateTime')) {
            return $date->date_i18n(get_option('date_format'));
        }
        if ($date instanceof DateTime) {
            return date_i18n(get_option('date_format'), $date->getTimestamp());
        }
        return date_i18n(get_option('date_format'), strtotime($date));
    }

    /**
     * Format datetime according to WordPress settings
     *
     * @param  WC_DateTime|DateTime|string $datetime DateTime to format
     * @return string Formatted datetime
     */
    private function formatDatetime($date)
    {
        if (is_a($date, 'WC_DateTime')) {
            return $date->date_i18n(get_option('date_format') . ' ' . get_option('time_format'));
        }
        if ($date instanceof DateTime) {
            return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $date->getTimestamp());
        }
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
    }

    /**
     * Get order status label
     *
     * @param  string $status Order status
     * @return string Status label
     */
    private function getOrderStatusLabel($status)
    {
        $statuses = wc_get_order_statuses();
        return isset($statuses[$status]) ? $statuses[$status] : $status;
    }

    /**
     * Get country name from country code
     *
     * @param  string $country_code Country code
     * @return string Country name
     */
    private function getCountryName($country_code)
    {
        if (!function_exists('WC') || !$country_code || !WC()->countries) {
            return $country_code;
        }

        $countries = WC()->countries->get_countries();
        return isset($countries[$country_code]) ? $countries[$country_code] : $country_code;
    }

    /**
     * Replace variables in text
     *
     * @param  string $text Text containing variables
     * @return string Text with variables replaced
     */
    public function replaceVariables($text)
    {
        $variables = $this->getAllVariables();

        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }

        return $text;
    }

    /**
     * Get fallback values for missing data
     *
     * @return array Array of fallback values
     */
    public static function getFallbacks()
    {
        return array(
            'order_number' => 'N/A',
            'order_date' => date_i18n(get_option('date_format')),
            'order_date_time' => date_i18n(get_option('date_format') . ' ' . get_option('time_format')),
            'order_date_modified' => date_i18n(get_option('date_format')),
            'order_total' => wc_price(0),
            'order_status' => __('Unknown', 'pdf-builder-pro'),
            'currency' => get_woocommerce_currency(),
            'customer_name' => __('Customer', 'pdf-builder-pro'),
            'customer_first_name' => '',
            'customer_last_name' => '',
            'customer_email' => '',
            'customer_phone' => '',
            'customer_note' => '',
            'billing_address' => '',
            'shipping_address' => '',
            'billing_first_name' => '',
            'billing_last_name' => '',
            'billing_company' => '',
            'billing_address_1' => '',
            'billing_address_2' => '',
            'billing_city' => '',
            'billing_postcode' => '',
            'billing_country' => '',
            'billing_state' => '',
            'subtotal' => wc_price(0),
            'tax_amount' => wc_price(0),
            'shipping_amount' => wc_price(0),
            'discount_amount' => wc_price(0),
            'total_excl_tax' => wc_price(0),
            'payment_method' => '',
            'payment_method_code' => '',
            'transaction_id' => '',
            'product_name' => '',
            'product_qty' => '',
            'product_price' => '',
            'product_total' => '',
            'product_sku' => '',
            'products_list' => '',
            'company_name' => get_option('woocommerce_store_name', ''),
            'company_address' => '',
            'company_phone' => '',
            'company_email' => get_option('admin_email', '')
        );
    }
}
