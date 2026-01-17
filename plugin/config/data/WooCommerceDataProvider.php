<?php

namespace PDF_Builder\Data;

use PDF_Builder\Interfaces\DataProviderInterface;

/**
 * Classe WooCommerceDataProvider
 * Fournit des données réelles depuis WooCommerce pour l'aperçu en mode metabox
 */
class WooCommerceDataProvider implements DataProviderInterface
{
    /** @var \WC_Order Commande WooCommerce */
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
    public function __construct(?int $orderId = null, string $context = 'metabox')
    {
        $this->context = $context;
        if ($orderId && function_exists('wc_get_order')) {
            $this->order = wc_get_order($orderId);
        }
    }

    /**
     * Définit la commande WooCommerce
     *
     * @param object $order Commande WooCommerce ou objet compatible
     */
    public function setOrder(object $order): void
    {
        $this->order = $order;
        $this->cachedValues = []; // Reset cache
    }

    /**
     * {@inheritDoc}
     */
    public function getVariableValue(string $variable): string
    {
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
    private function retrieveVariableValue(string $variable): string
    {
        try {
            switch ($variable) {
                // Informations commande
                case 'order_number':
                    return $this->order->getOrderNumber();
                case 'order_date':
                    return wp_date(get_option('date_format'), strtotime($this->order->getDateCreated()));
                case 'order_time':
                    return wp_date(get_option('time_format'), strtotime($this->order->getDateCreated()));
                case 'order_status':
                    return function_exists('wc_get_order_status_name') ? wc_get_order_status_name($this->order->getStatus()) : $this->order->getStatus();
                case 'order_total':
                    return $this->formatPrice($this->order->getTotal());
                case 'order_subtotal':
                    return $this->formatPrice($this->order->getSubtotal());
                case 'order_tax':
                    return $this->formatPrice($this->order->getTotalTax());
                case 'order_shipping':
                    return $this->formatPrice($this->order->getShippingTotal());
                case 'order_discount':
                    return $this->formatPrice($this->order->getDiscountTotal());
                // Informations client
                case 'customer_name':
                    return $this->order->getFormattedBillingFullName();
                case 'customer_firstname':
                case 'billing_first_name':
                    return $this->order->getBillingFirstName();
                case 'customer_lastname':
                case 'billing_last_name':
                    return $this->order->getBillingLastName();
                case 'customer_email':
                case 'billing_email':
                    return $this->order->getBillingEmail();
                case 'customer_phone':
                case 'billing_phone':
                    return $this->order->getBillingPhone();
                // Adresse de facturation
                case 'customer_address':
                case 'billing_address_1':
                    return $this->order->getBillingAddress1();
                case 'billing_address_2':
                    return $this->order->getBillingAddress2();
                case 'customer_city':
                case 'billing_city':
                    return $this->order->getBillingCity();
                case 'customer_postcode':
                case 'billing_postcode':
                    return $this->order->getBillingPostcode();
                case 'customer_country':
                case 'billing_country':
                    $countries = get_option('woocommerce_countries', []);
                    if (!empty($countries) && isset($countries[$this->order->getBillingCountry()])) {
                        return $countries[$this->order->getBillingCountry()];
                    }
                    return $this->order->getBillingCountry();
                case 'billing_state':
                    return $this->order->getBillingState();
                case 'billing_company':
                    return $this->order->getBillingCompany();
                // Adresse de livraison
                case 'shipping_first_name':
                    return $this->order->getShippingFirstName();
                case 'shipping_last_name':
                    return $this->order->getShippingLastName();
                case 'shipping_address_1':
                    return $this->order->getShippingAddress1();
                case 'shipping_address_2':
                    return $this->order->getShippingAddress2();
                case 'shipping_city':
                    return $this->order->getShippingCity();
                case 'shipping_postcode':
                    return $this->order->getShippingPostcode();
                case 'shipping_country':
                    $countries = get_option('woocommerce_countries', []);
                    if (!empty($countries) && isset($countries[$this->order->getShippingCountry()])) {
                        return $countries[$this->order->getShippingCountry()];
                    }
                    return $this->order->getShippingCountry();
                case 'shipping_state':
                    return $this->order->getShippingState();
                case 'shipping_company':
                    return $this->order->getShippingCompany();
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
                    $countries = get_option('woocommerce_countries', []);
                    if (!empty($countries) && isset($countries[$country])) {
                        return $countries[$country];
                    }
                    return $country;
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
    private function handleProductVariables(string $variable): string
    {
        // Variables générales sur les produits
        if ($variable === 'order_items_count') {
            return (string) $this->order->getItemCount();
        }

        if ($variable === 'products_total') {
            return $this->formatPrice($this->order->getSubtotal());
        }

        // Variables spécifiques à un produit (product_1_name, product_2_price, etc.)
        if (preg_match('/^product_(\d+)_(name|quantity|price|total)$/', $variable, $matches)) {
            $productIndex = (int) $matches[1] - 1; // Les indices commencent à 1 dans les templates
            $field = $matches[2];
            $items = $this->order->getItems();
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
     * @param object $item Article de commande
     * @param string $field Champ demandé
     * @return string Valeur du champ
     */
    private function getProductFieldValue(object $item, string $field): string
    {
        switch ($field) {
            case 'name':
                return $item->getName();
            case 'quantity':
                return (string) $item->getQuantity();
            case 'price':
                // Utiliser getSubtotal() pour obtenir le prix avant remises appliquées à l'article
                $quantity = $item->getQuantity();
                if ($quantity > 0) {
                    $unitPrice = $item->getSubtotal() / $quantity;
                    return $this->formatPrice($unitPrice);
                }
                return $this->formatPrice(0);
            case 'total':
                return $this->formatPrice($item->getTotal());
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
    private function handleSpecialVariables(string $variable): string
    {
        switch ($variable) {
            case 'current_date':
                return wp_date(get_option('date_format'));
            case 'current_time':
                return wp_date(get_option('time_format'));
            case 'current_datetime':
                return wp_date(get_option('date_format') . ' ' . get_option('time_format'));
            case 'customer_full_name':
                return trim($this->order->getBillingFirstName() . ' ' . $this->order->getBillingLastName());
            case 'company_full_address':
                $address = get_option('woocommerce_store_address', '');
                $city = get_option('woocommerce_store_city', '');
                $postcode = get_option('woocommerce_store_postcode', '');
                $country = get_option('woocommerce_default_country', '');
                $parts = array_filter([$address, $city, $postcode, $country]);
                return implode(', ', $parts);
            case 'payment_method':
                return $this->order->getPaymentMethodTitle();
            case 'order_notes':
                return $this->order->getCustomerNote();
            case 'has_discount':
                return $this->order->getDiscountTotal() > 0 ? 'true' : 'false';
            case 'is_paid':
                return $this->order->isPaid() ? 'true' : 'false';
            case 'currency_symbol':
                return $this->getCurrencySymbol();
            case 'currency_code':
                return $this->getCurrencyCode();
            case 'tax_rate':
                // Calcul du taux de TVA moyen (simplifié)
                $taxes = $this->order->getTaxTotals();
                if (!empty($taxes)) {
                    $totalTax = 0;
                    $totalBase = 0;
                    foreach ($taxes as $tax) {
                        $totalTax += $tax->amount;
                        // Note: Cette approximation peut être améliorée
                    }
                    if ($this->order->getSubtotal() > 0) {
                        $rate = ($totalTax / $this->order->getSubtotal()) * 100;
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
    private function formatPrice(float $price): string
    {
        if (function_exists('wc_price')) {
            return wc_price($price, ['currency' => $this->order->getCurrency()]);
        }
        return '$' . number_format($price, 2);
    }

    /**
     * Génère un placeholder pour les données manquantes
     *
     * @param string $variable Nom de la variable manquante
     * @return string Placeholder informatif
     */
    private function getMissingDataPlaceholder(string $variable): string
    {
        $placeholders = [
            // Informations commande
            'order_number' => '[Numéro de commande]',
            'order_date' => '[Date de commande]',
            'order_time' => '[Heure de commande]',
            'order_status' => '[Statut de commande]',
            'order_total' => '[Montant total]',
            'order_subtotal' => '[Sous-total]',
            'order_tax' => '[Montant TVA]',
            'order_shipping' => '[Frais de port]',
            'order_discount' => '[Remise]',

            // Informations client
            'customer_name' => '[Nom du client]',
            'customer_firstname' => '[Prénom]',
            'customer_lastname' => '[Nom]',
            'customer_email' => '[Email client]',
            'customer_phone' => '[Téléphone client]',
            'customer_address' => '[Adresse client]',
            'customer_city' => '[Ville client]',
            'customer_postcode' => '[Code postal client]',
            'customer_country' => '[Pays client]',

            // Informations entreprise
            'company_name' => '[Nom entreprise]',
            'company_address' => '[Adresse entreprise]',
            'company_city' => '[Ville entreprise]',
            'company_postcode' => '[CP entreprise]',
            'company_country' => '[Pays entreprise]',
            'company_phone' => '[Tél entreprise]',
            'company_email' => '[Email entreprise]',
            'company_website' => '[Site web]',
            'company_siret' => '[SIRET]',
            'company_tva' => '[N° TVA]',

            // Produits
            'products_total' => '[Total produits]',
            'tax_rate' => '[Taux TVA]',
            'tax_amount' => '[Montant TVA]',
            'shipping_cost' => '[Frais de port]',
            'grand_total' => '[Total général]',

            // Paiement
            'payment_method' => '[Mode de paiement]',
            'payment_date' => '[Date de paiement]',

            // Notes
            'order_notes' => '[Notes commande]',
            'customer_notes' => '[Notes client]'
        ];

        return $placeholders[$variable] ?? "[{$variable}]";
    }

    /**
     * {@inheritDoc}
     */
    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->getAllVariables());
    }

    /**
     * {@inheritDoc}
     */
    public function getAllVariables(): array
    {
        return [
            'order_number', 'order_date', 'order_time', 'order_status', 'order_total',
            'order_subtotal', 'order_tax', 'order_shipping', 'order_discount',
            // Informations client
            'customer_name', 'customer_firstname', 'customer_lastname', 'customer_email',
            'customer_phone', 'customer_address', 'customer_city', 'customer_postcode',
            'customer_country', 'billing_first_name', 'billing_last_name', 'billing_email',
            'billing_phone', 'billing_address_1', 'billing_address_2', 'billing_city',
            'billing_postcode', 'billing_country', 'shipping_first_name', 'shipping_last_name',
            'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode',
            'shipping_country',
            // Informations entreprise
            'company_name', 'company_address', 'company_city', 'company_postcode',
            'company_country', 'company_phone', 'company_email', 'company_website',
            'company_siret', 'company_tva',
            // Produits
            'products_total', 'tax_rate', 'tax_amount', 'shipping_cost', 'grand_total',
            // Informations de paiement
            'payment_method', 'payment_date',
            // Notes et commentaires
            'order_notes', 'customer_notes'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isSampleData(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     */
    public function validateAndSanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $type = $this->guessDataType($key);
            $sanitized[$key] = $this->validateAndSanitizeValue($value, $type);
        }
        return $sanitized;
    }

    /**
     * Valide et sanitise une valeur individuelle selon le type
     *
     * @param mixed $value La valeur à valider
     * @param string $type Le type de données attendu
     * @return mixed La valeur validée et sanitizée
     */
    private function validateAndSanitizeValue($value, string $type = 'string')
    {
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
    private function sanitizeEmail($value): string
    {
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
    private function sanitizeUrl($value): string
    {
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
    private function sanitizeHtml($value): string
    {
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
    private function sanitizeText($value): string
    {
        if (function_exists('sanitize_text_field')) {
            return sanitize_text_field($value);
        }
        // Fallback PHP
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Détermine le type de données d'après la clé
     *
     * @param string $key Clé de la donnée
     * @return string Type de données
     */
    private function guessDataType(string $key): string
    {
        if (strpos($key, 'email') !== false) {
            return 'email';
        }
        if (strpos($key, 'url') !== false || strpos($key, 'website') !== false) {
            return 'url';
        }
        if (strpos($key, 'price') !== false || strpos($key, 'total') !== false || strpos($key, 'tax') !== false) {
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
     * Récupère le symbole de la devise de la commande
     *
     * @return string Symbole de la devise
     */
    private function getCurrencySymbol(): string
    {
        return \get_woocommerce_currency_symbol($this->order->getCurrency());
    }

    /**
     * Récupère le code de la devise de la commande
     *
     * @return string Code de la devise
     */
    private function getCurrencyCode(): string
    {
        return \get_woocommerce_currency($this->order->getCurrency());
    }
}
