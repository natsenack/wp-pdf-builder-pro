<?php
namespace WP_PDF_Builder_Pro\Data;

use WC_Order;
use WC_Order_Item_Product;
use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Classe WooCommerceDataProvider
 * Fournit des données réelles depuis WooCommerce pour l'aperçu en mode metabox
 */
class WooCommerceDataProvider implements DataProviderInterface {

    /** @var WC_Order Commande WooCommerce */
    private $order;

    /** @var string Contexte d'utilisation */
    private $context;

    /** @var array Cache des valeurs récupérées */
    private $cachedValues = [];

    /**
     * Constructeur
     *
     * @param int|null $orderId ID de la commande WooCommerce
     * @param string $context Contexte d'utilisation (canvas, metabox, etc.)
     */
    public function __construct(?int $orderId = null, string $context = 'metabox') {
        $this->context = $context;

        if ($orderId) {
            $this->order = wc_get_order($orderId);
        }
    }

    /**
     * Définit la commande WooCommerce
     *
     * @param object $order Commande WooCommerce ou objet compatible
     */
    public function setOrder(object $order): void {
        $this->order = $order;
        $this->cachedValues = []; // Reset cache
    }

    /**
     * {@inheritDoc}
     */
    public function getVariableValue(string $variable): string {
        // Vérifier le cache d'abord
        if (isset($this->cachedValues[$variable])) {
            return $this->cachedValues[$variable];
        }

        if (!$this->order) {
            return $this->getMissingDataPlaceholder($variable);
        }

        // Récupérer la valeur selon la variable demandée
        $value = $this->retrieveVariableValue($variable);

        // Mettre en cache
        $this->cachedValues[$variable] = $value;

        return $value;
    }

    /**
     * Récupère la valeur d'une variable depuis WooCommerce
     *
     * @param string $variable Nom de la variable
     * @return string Valeur de la variable
     */
    private function retrieveVariableValue(string $variable): string {
        try {
            switch ($variable) {
                // Informations commande
                case 'order_number':
                    return $this->order->get_order_number();

                case 'order_date':
                    return wp_date(get_option('date_format'), strtotime($this->order->get_date_created()));

                case 'order_time':
                    return wp_date(get_option('time_format'), strtotime($this->order->get_date_created()));

                case 'order_status':
                    return wc_get_order_status_name($this->order->get_status());

                case 'order_total':
                    return $this->formatPrice($this->order->get_total());

                case 'order_subtotal':
                    return $this->formatPrice($this->order->get_subtotal());

                case 'order_tax':
                    return $this->formatPrice($this->order->get_total_tax());

                case 'order_shipping':
                    return $this->formatPrice($this->order->get_shipping_total());

                case 'order_discount':
                    return $this->formatPrice($this->order->get_discount_total());

                // Informations client
                case 'customer_name':
                    return $this->order->get_formatted_billing_full_name();

                case 'customer_firstname':
                case 'billing_first_name':
                    return $this->order->get_billing_first_name();

                case 'customer_lastname':
                case 'billing_last_name':
                    return $this->order->get_billing_last_name();

                case 'customer_email':
                case 'billing_email':
                    return $this->order->get_billing_email();

                case 'customer_phone':
                case 'billing_phone':
                    return $this->order->get_billing_phone();

                // Adresse de facturation
                case 'customer_address':
                case 'billing_address_1':
                    return $this->order->get_billing_address_1();

                case 'billing_address_2':
                    return $this->order->get_billing_address_2();

                case 'customer_city':
                case 'billing_city':
                    return $this->order->get_billing_city();

                case 'customer_postcode':
                case 'billing_postcode':
                    return $this->order->get_billing_postcode();

                case 'customer_country':
                case 'billing_country':
                    return WC()->countries->countries[$this->order->get_billing_country()] ?? $this->order->get_billing_country();

                case 'billing_state':
                    return $this->order->get_billing_state();

                case 'billing_company':
                    return $this->order->get_billing_company();

                // Adresse de livraison
                case 'shipping_first_name':
                    return $this->order->get_shipping_first_name();

                case 'shipping_last_name':
                    return $this->order->get_shipping_last_name();

                case 'shipping_address_1':
                    return $this->order->get_shipping_address_1();

                case 'shipping_address_2':
                    return $this->order->get_shipping_address_2();

                case 'shipping_city':
                    return $this->order->get_shipping_city();

                case 'shipping_postcode':
                    return $this->order->get_shipping_postcode();

                case 'shipping_country':
                    return WC()->countries->countries[$this->order->get_shipping_country()] ?? $this->order->get_shipping_country();

                case 'shipping_state':
                    return $this->order->get_shipping_state();

                case 'shipping_company':
                    return $this->order->get_shipping_company();

                // Informations entreprise (depuis paramètres WooCommerce)
                case 'company_name':
                    return get_option('woocommerce_store_name', get_bloginfo('name'));

                case 'company_address':
                    return get_option('woocommerce_store_address', '');

                case 'company_city':
                    return get_option('woocommerce_store_city', '');

                case 'company_postcode':
                    return get_option('woocommerce_store_postcode', '');

                case 'company_country':
                    $country = get_option('woocommerce_default_country', '');
                    return WC()->countries->countries[$country] ?? $country;

                case 'company_phone':
                    return get_option('woocommerce_store_phone', '');

                case 'company_email':
                    return get_option('woocommerce_email_from_address', get_bloginfo('admin_email'));

                // Produits
                default:
                    return $this->handleProductVariables($variable);
            }
        } catch (\Exception $e) {
            return $this->getMissingDataPlaceholder($variable);
        }
    }

    /**
     * Gère les variables liées aux produits
     *
     * @param string $variable Nom de la variable
     * @return string Valeur de la variable
     */
    private function handleProductVariables(string $variable): string {
        // Variables générales sur les produits
        if ($variable === 'order_items_count') {
            return (string) $this->order->get_item_count();
        }

        if ($variable === 'products_total') {
            return $this->formatPrice($this->order->get_subtotal());
        }

        // Variables spécifiques à un produit (product_1_name, product_2_price, etc.)
        if (preg_match('/^product_(\d+)_(name|quantity|price|total)$/', $variable, $matches)) {
            $productIndex = (int) $matches[1] - 1; // Les indices commencent à 1 dans les templates
            $field = $matches[2];

            $items = $this->order->get_items();
            $itemKeys = array_keys($items);

            if (isset($itemKeys[$productIndex])) {
                $item = $items[$itemKeys[$productIndex]];
                return $this->getProductFieldValue($item, $field);
            }
        }

        // Variables spéciales
        return $this->handleSpecialVariables($variable);
    }

    /**
     * Récupère la valeur d'un champ pour un produit
     *
     * @param WC_Order_Item_Product $item Article de commande
     * @param string $field Champ demandé
     * @return string Valeur du champ
     */
    private function getProductFieldValue(WC_Order_Item_Product $item, string $field): string {
        switch ($field) {
            case 'name':
                return $item->get_name();

            case 'quantity':
                return (string) $item->get_quantity();

            case 'price':
                return $this->formatPrice($item->get_total() / $item->get_quantity());

            case 'total':
                return $this->formatPrice($item->get_total());

            default:
                return '';
        }
    }

    /**
     * Gère les variables spéciales
     *
     * @param string $variable Nom de la variable
     * @return string Valeur de la variable
     */
    private function handleSpecialVariables(string $variable): string {
        switch ($variable) {
            case 'current_date':
                return wp_date(get_option('date_format'));

            case 'current_time':
                return wp_date(get_option('time_format'));

            case 'current_datetime':
                return wp_date(get_option('date_format') . ' ' . get_option('time_format'));

            case 'customer_full_name':
                return trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name());

            case 'company_full_address':
                $address = get_option('woocommerce_store_address', '');
                $city = get_option('woocommerce_store_city', '');
                $postcode = get_option('woocommerce_store_postcode', '');
                $country = get_option('woocommerce_default_country', '');

                $parts = array_filter([$address, $city, $postcode, $country]);
                return implode(', ', $parts);

            case 'payment_method':
                return $this->order->get_payment_method_title();

            case 'order_notes':
                return $this->order->get_customer_note();

            case 'has_discount':
                return $this->order->get_discount_total() > 0 ? 'true' : 'false';

            case 'is_paid':
                return $this->order->is_paid() ? 'true' : 'false';

            case 'currency_symbol':
                return get_woocommerce_currency_symbol();

            case 'currency_code':
                return get_woocommerce_currency();

            case 'tax_rate':
                // Calcul du taux de TVA moyen (simplifié)
                $taxes = $this->order->get_tax_totals();
                if (!empty($taxes)) {
                    $totalTax = 0;
                    $totalBase = 0;
                    foreach ($taxes as $tax) {
                        $totalTax += $tax->amount;
                        // Note: Cette approximation peut être améliorée
                    }
                    if ($this->order->get_subtotal() > 0) {
                        $rate = ($totalTax / $this->order->get_subtotal()) * 100;
                        return number_format($rate, 2) . '%';
                    }
                }
                return '0%';

            default:
                return $this->getMissingDataPlaceholder($variable);
        }
    }

    /**
     * Formate un prix selon les paramètres WooCommerce
     *
     * @param float $price Prix à formater
     * @return string Prix formaté
     */
    private function formatPrice(float $price): string {
        return wc_price($price, ['currency' => $this->order->get_currency()]);
    }

    /**
     * Génère un placeholder pour les données manquantes
     *
     * @param string $variable Nom de la variable manquante
     * @return string Placeholder informatif
     */
    private function getMissingDataPlaceholder(string $variable): string {
        return '<span style="color: red; font-weight: bold;">[Donnée manquante: ' . $variable . ']</span>';
    }

    /**
     * {@inheritDoc}
     */
    public function isSampleData(): bool {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): string {
        return $this->context;
    }

    /**
     * Vérifie si une variable est disponible
     *
     * @param string $variable Nom de la variable
     * @return bool True si la variable existe
     */
    public function hasVariable(string $variable): bool {
        return array_key_exists($variable, $this->getAllVariables());
    }

    /**
     * Retourne la liste de toutes les variables disponibles
     *
     * @return array Liste des noms de variables
     */
    public function getAllVariables(): array {
        return [
            'order_number', 'order_date', 'order_status', 'order_total', 'order_subtotal',
            'order_tax', 'order_shipping', 'order_discount', 'customer_name', 'customer_email',
            'customer_phone', 'billing_first_name', 'billing_last_name', 'billing_company',
            'billing_address', 'billing_address_1', 'billing_address_2', 'billing_city',
            'billing_state', 'billing_postcode', 'billing_country', 'billing_email',
            'billing_phone', 'shipping_first_name', 'shipping_last_name', 'shipping_company',
            'shipping_address', 'shipping_address_1', 'shipping_address_2', 'shipping_city',
            'shipping_state', 'shipping_postcode', 'shipping_country', 'currency',
            'payment_method', 'transaction_id', 'order_notes', 'order_items'
        ];
    }

    /**
     * Valide et sanitise une valeur individuelle selon le type
     *
     * @param mixed $value La valeur à valider
     * @param string $type Le type de données attendu
     * @return mixed La valeur validée et sanitizée
     */
    public function validateAndSanitizeValue($value, string $type = 'string') {
        switch ($type) {
            case 'email':
                return $this->sanitizeEmail($value);

            case 'url':
                return $this->sanitizeUrl($value);

            case 'html':
                return $this->sanitizeHtml($value);

            case 'float':
                return floatval($value);

            case 'int':
                return intval($value);

            case 'string':
            default:
                return $this->sanitizeText($value);
        }
    }

    /**
     * Sanitise une adresse email
     *
     * @param mixed $value Valeur à sanitiser
     * @return string Email sanitizé ou chaîne vide
     */
    private function sanitizeEmail($value): string {
        if (function_exists('is_email') && function_exists('sanitize_email')) {
            return is_email($value) ? sanitize_email($value) : '';
        }
        // Fallback PHP
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
    }

    /**
     * Sanitise une URL
     *
     * @param mixed $value Valeur à sanitiser
     * @return string URL sanitizée
     */
    private function sanitizeUrl($value): string {
        if (function_exists('esc_url_raw')) {
            return esc_url_raw($value);
        }
        // Fallback PHP
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitise du HTML
     *
     * @param mixed $value Valeur à sanitiser
     * @return string HTML sanitizé
     */
    private function sanitizeHtml($value): string {
        if (function_exists('wp_kses_post')) {
            return wp_kses_post($value);
        }
        // Fallback PHP basique
        return strip_tags($value, '<p><br><strong><em><a><ul><ol><li>');
    }

    /**
     * Sanitise un texte
     *
     * @param mixed $value Valeur à sanitiser
     * @return string Texte sanitizé
     */
    private function sanitizeText($value): string {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($value);
        }
        // Fallback PHP
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * {@inheritDoc}
     */
    public function validateAndSanitizeData(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $type = $this->guessDataType($key);
            $sanitized[$key] = $this->validateAndSanitizeValue($value, $type);
        }
        return $sanitized;
    }

    /**
     * Détermine le type de données d'après la clé
     *
     * @param string $key Clé de la donnée
     * @return string Type de données
     */
    private function guessDataType(string $key): string {
        if (strpos($key, 'email') !== false) {
            return 'email';
        }
        if (strpos($key, 'url') !== false || strpos($key, 'website') !== false) {
            return 'url';
        }
        if (strpos($key, 'price') !== false || strpos($key, 'total') !== false ||
            strpos($key, 'tax') !== false || strpos($key, 'shipping') !== false ||
            strpos($key, 'discount') !== false) {
            return 'float';
        }
        if (strpos($key, 'quantity') !== false || strpos($key, 'count') !== false) {
            return 'int';
        }
        if (strpos($key, 'notes') !== false || strpos($key, 'description') !== false) {
            return 'html';
        }
        return 'string';
    }

    /**
     * Vérifie si une commande est disponible
     *
     * @return bool True si la commande existe
     */
    public function hasOrder(): bool {
        return $this->order instanceof WC_Order;
    }

    /**
     * Retourne l'ID de la commande actuelle
     *
     * @return int|null ID de la commande ou null
     */
    public function getOrderId(): ?int {
        return $this->order ? $this->order->get_id() : null;
    }
}