<?php

namespace PDF_Builder\Generators;

use WC_Order;

/**
 * Extrait les données d'une commande WooCommerce
 * et les formate pour usage dans les templates PDF
 */
class OrderDataExtractor
{
    private WC_Order $order;
    private array $extracted_data = [];

    public function __construct(WC_Order $order)
    {
        $this->order = $order;
        $this->extract_all_data();
    }

    /**
     * Extrait tous les données de la commande
     */
    private function extract_all_data(): void
    {
        // Données client
        $this->extracted_data['customer'] = $this->extract_customer_data();
        
        // Données commande
        $this->extracted_data['order'] = $this->extract_order_data();
        
        // Produits
        $this->extracted_data['products'] = $this->extract_products_data();
        
        // Frais supplémentaires
        $this->extracted_data['fees'] = $this->extract_fees_data();
        
        // Adresses
        $this->extracted_data['billing'] = $this->extract_billing_address();
        $this->extracted_data['shipping'] = $this->extract_shipping_address();
        
        // Totaux
        $this->extracted_data['totals'] = $this->extract_totals();
    }

    /**
     * Extrait les données client
     */
    private function extract_customer_data(): array
    {
        return [
            'id' => $this->order->get_customer_id(),
            'first_name' => $this->order->get_billing_first_name(),
            'last_name' => $this->order->get_billing_last_name(),
            'email' => $this->order->get_billing_email(),
            'phone' => $this->order->get_billing_phone(),
            'full_name' => trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name()),
        ];
    }

    /**
     * Extrait les données de commande
     */
    private function extract_order_data(): array
    {
        return [
            'id' => $this->order->get_id(),
            'order_number' => $this->order->get_order_number(),
            'date' => $this->order->get_date_created()->format('Y-m-d H:i:s'),
            'date_formatted' => wc_format_datetime($this->order->get_date_created(), get_option('date_format')),
            'status' => $this->order->get_status(),
            'status_label' => wc_get_order_status_name($this->order->get_status()),
            'payment_method' => $this->order->get_payment_method_title(),
            'transaction_id' => $this->order->get_transaction_id(),
            'shipping_method' => $this->order->get_shipping_method(),
            'currency' => $this->order->get_currency(),
            'notes' => $this->order->get_customer_note(),
        ];
    }

    /**
     * Extrait les données des produits
     */
    private function extract_products_data(): array
    {
        $products = [];
        
        foreach ($this->order->get_items() as $item) {
            $product = $item->get_product();
            
            if (!$product) {
                continue;
            }
            
            $unit_price = $item->get_quantity() > 0 ? $item->get_total() / $item->get_quantity() : 0;
            
            // Récupérer l'image du produit en base64 pour PDF
            $image_id = $product->get_image_id();
            $image_base64 = '';
            if ($image_id) {
                $image_path = get_attached_file($image_id);
                if ($image_path && file_exists($image_path)) {
                    $image_data = file_get_contents($image_path);
                    $image_type = wp_check_filetype($image_path);
                    $image_base64 = 'data:' . $image_type['type'] . ';base64,' . base64_encode($image_data);
                }
            }
            
            // Récupérer la description
            $description = $product->get_short_description() ?: $product->get_description();
            
            $products[] = [
                'id' => $product->get_id(),
                'sku' => $product->get_sku(),
                'name' => $item->get_name(),
                'description' => wp_strip_all_tags($description),
                'image' => $image_base64,
                'quantity' => $item->get_quantity(),
                'price' => wc_price($unit_price),
                'price_raw' => (float) $unit_price,
                'total' => wc_price($item->get_total()),
                'total_raw' => (float) $item->get_total(),
                'subtotal' => wc_price($item->get_subtotal()),
                'subtotal_raw' => (float) $item->get_subtotal(),
                'tax' => wc_price($item->get_total_tax()),
                'tax_raw' => (float) $item->get_total_tax(),
                'variation_data' => $this->extract_variation_data($item),
            ];
        }
        
        return $products;
    }

    /**
     * Extrait les frais supplémentaires (fees)
     */
    private function extract_fees_data(): array
    {
        $fees = [];
        
        foreach ($this->order->get_fees() as $fee_id => $fee) {
            $fees[] = [
                'id' => $fee_id,
                'name' => $fee->get_name(),
                'amount' => wc_price($fee->get_amount()),
                'amount_raw' => (float) $fee->get_amount(),
                'total' => wc_price($fee->get_total()),
                'total_raw' => (float) $fee->get_total(),
                'tax' => wc_price($fee->get_total_tax()),
                'tax_raw' => (float) $fee->get_total_tax(),
            ];
        }
        
        return $fees;
    }

    /**
     * Extrait les données de variation de produit
     */
    private function extract_variation_data($item): array
    {
        $meta_data = $item->get_meta_data();
        $variations = [];
        
        if (!empty($meta_data)) {
            foreach ($meta_data as $meta) {
                if (strpos($meta->key, 'pa_') === 0) {
                    $variations[$meta->key] = $meta->value;
                }
            }
        }
        
        return $variations;
    }

    /**
     * Extrait l'adresse de facturation
     */
    private function extract_billing_address(): array
    {
        return [
            'first_name' => $this->order->get_billing_first_name(),
            'last_name' => $this->order->get_billing_last_name(),
            'company' => $this->order->get_billing_company(),
            'address_1' => $this->order->get_billing_address_1(),
            'address_2' => $this->order->get_billing_address_2(),
            'city' => $this->order->get_billing_city(),
            'state' => $this->order->get_billing_state(),
            'postcode' => $this->order->get_billing_postcode(),
            'country' => $this->order->get_billing_country(),
            'full_address' => $this->format_address($this->get_billing_address_array()),
        ];
    }

    /**
     * Extrait l'adresse de livraison
     */
    private function extract_shipping_address(): array
    {
        return [
            'first_name' => $this->order->get_shipping_first_name(),
            'last_name' => $this->order->get_shipping_last_name(),
            'company' => $this->order->get_shipping_company(),
            'address_1' => $this->order->get_shipping_address_1(),
            'address_2' => $this->order->get_shipping_address_2(),
            'city' => $this->order->get_shipping_city(),
            'state' => $this->order->get_shipping_state(),
            'postcode' => $this->order->get_shipping_postcode(),
            'country' => $this->order->get_shipping_country(),
            'full_address' => $this->format_address($this->get_shipping_address_array()),
        ];
    }

    /**
     * Extrait les totaux
     */
    private function extract_totals(): array
    {
        return [
            'subtotal' => wc_price($this->order->get_subtotal()),
            'subtotal_raw' => $this->order->get_subtotal(),
            'shipping' => wc_price($this->order->get_shipping_total()),
            'shipping_raw' => $this->order->get_shipping_total(),
            'tax' => wc_price($this->order->get_total_tax()),
            'tax_raw' => $this->order->get_total_tax(),
            'discount' => wc_price($this->order->get_discount_total()),
            'discount_raw' => $this->order->get_discount_total(),
            'total' => wc_price($this->order->get_total()),
            'total_raw' => $this->order->get_total(),
        ];
    }

    /**
     * Récupère un tableau adresse de facturation
     */
    private function get_billing_address_array(): array
    {
        return [
            'first_name' => $this->order->get_billing_first_name(),
            'last_name' => $this->order->get_billing_last_name(),
            'company' => $this->order->get_billing_company(),
            'address_1' => $this->order->get_billing_address_1(),
            'address_2' => $this->order->get_billing_address_2(),
            'city' => $this->order->get_billing_city(),
            'state' => $this->order->get_billing_state(),
            'postcode' => $this->order->get_billing_postcode(),
            'country' => $this->order->get_billing_country(),
        ];
    }

    /**
     * Récupère un tableau adresse de livraison
     */
    private function get_shipping_address_array(): array
    {
        return [
            'first_name' => $this->order->get_shipping_first_name(),
            'last_name' => $this->order->get_shipping_last_name(),
            'company' => $this->order->get_shipping_company(),
            'address_1' => $this->order->get_shipping_address_1(),
            'address_2' => $this->order->get_shipping_address_2(),
            'city' => $this->order->get_shipping_city(),
            'state' => $this->order->get_shipping_state(),
            'postcode' => $this->order->get_shipping_postcode(),
            'country' => $this->order->get_shipping_country(),
        ];
    }

    /**
     * Formate une adresse
     */
    private function format_address(array $address): string
    {
        $lines = [];
        
        // NE PAS inclure le nom (first_name/last_name) car il est affiché séparément dans customer_info
        // Correction : le nom apparaissait en double dans l'aperçu HTML
        
        // Ligne 1 : Company (si présente, sur sa propre ligne)
        if (!empty($address['company'])) {
            $lines[] = $address['company'];
        }
        
        // Ligne 2 : Adresse complète sur une seule ligne (rue, code postal, ville)
        $address_parts = [];
        if (!empty($address['address_1'])) {
            $address_parts[] = $address['address_1'];
        }
        if (!empty($address['address_2'])) {
            $address_parts[] = $address['address_2'];
        }
        if (!empty($address['postcode']) || !empty($address['city'])) {
            $address_parts[] = trim($address['postcode'] . ' ' . $address['city']);
        }
        if (!empty($address['state'])) {
            $address_parts[] = $address['state'];
        }
        
        if (!empty($address_parts)) {
            $lines[] = implode(', ', $address_parts);
        }
        
        // Ligne 3 : Pays sur une ligne séparée
        if (!empty($address['country'])) {
            $lines[] = WC()->countries->countries[$address['country']] ?? $address['country'];
        }
        
        return implode("\n", array_filter($lines));
    }

    /**
     * Retourne toutes les données extraites
     */
    public function get_all_data(): array
    {
        return $this->extracted_data;
    }

    /**
     * Retourne les données customer
     */
    public function get_customer(): array
    {
        return $this->extracted_data['customer'];
    }

    /**
     * Retourne les données order
     */
    public function get_order(): array
    {
        return $this->extracted_data['order'];
    }

    /**
     * Retourne les produits
     */
    public function get_products(): array
    {
        return $this->extracted_data['products'];
    }

    /**
     * Retourne l'adresse de facturation
     */
    public function get_billing(): array
    {
        return $this->extracted_data['billing'];
    }

    /**
     * Retourne l'adresse de livraison
     */
    public function get_shipping(): array
    {
        return $this->extracted_data['shipping'];
    }

    /**
     * Retourne les totaux
     */
    public function get_totals(): array
    {
        return $this->extracted_data['totals'];
    }

    /**
     * Remplace les placeholders dans un texte par les vraies données
     * Exemple: "Client: {customer.full_name}" -> "Client: John Doe"
     */
    public function replace_placeholders(string $text): string
    {
        preg_match_all('/\{([^}]+)\}/', $text, $matches);
        
        if (empty($matches[1])) {
            return $text;
        }
        
        foreach ($matches[1] as $placeholder) {
            $value = $this->get_value_by_path($placeholder);
            
            if ($value !== null) {
                $text = str_replace('{' . $placeholder . '}', $value, $text);
            }
        }
        
        return $text;
    }

    /**
     * Récupère une valeur avec une clé pointée
     * Exemple: "customer.full_name" -> $extracted_data['customer']['full_name']
     */
    private function get_value_by_path(string $path)
    {
        $keys = explode('.', $path);
        $value = $this->extracted_data;
        
        foreach ($keys as $key) {
            if (is_array($value) && isset($value[$key])) {
                $value = $value[$key];
            } else {
                return null;
            }
        }
        
        return $value;
    }
}
