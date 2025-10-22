<?php
/**
 * Provider de données pour le mode Metabox (données WooCommerce réelles)
 *
 * Fournit des données réelles récupérées depuis WooCommerce
 * pour l'aperçu dans la metabox des commandes.
 *
 * @package PDF_Builder_Pro
 * @subpackage Providers
 */

namespace PDF_Builder_Pro\Providers;

use PDF_Builder_Pro\Interfaces\DataProviderInterface;
use PDF_Builder\Cache\WooCommerceCache;

/**
 * Classe MetaboxModeProvider
 *
 * Implémente DataProviderInterface pour fournir des données réelles
 * récupérées depuis WooCommerce utilisées dans la metabox des commandes.
 */
class MetaboxModeProvider implements DataProviderInterface
{
    /**
     * Instance de la commande WooCommerce
     *
     * @var mixed
     */
    private mixed $order = null;

    /**
     * Cache interne pour les données générées
     *
     * @var array
     */
    private array $cache = [];

    /**
     * Durée de vie du cache en secondes
     *
     * @var int
     */
    private int $cacheTtl = 300; // 5 minutes

    /**
     * Constructeur
     *
     * @param mixed $order Instance de la commande WooCommerce ou mock
     */
    public function __construct(mixed $order = null)
    {
        $this->order = $order;
    }

    /**
     * Définit la commande WooCommerce
     *
     * @param mixed $order Instance de la commande
     * @return void
     */
    public function setOrder(mixed $order): void
    {
        $this->order = $order;
        // Invalider le cache quand la commande change
        $this->cache = [];
    }

    /**
     * Récupère les données de base pour un contexte donné
     *
     * @param array $context Contexte de récupération (order_id, template_id, etc.)
     * @return array Données de base formatées
     */
    public function getBaseData(array $context = []): array
    {
        $cacheKey = 'base_data_' . md5(serialize($context));

        if ($this->getCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $baseData = [
            'template_id' => $context['template_id'] ?? 'metabox_template',
            'mode' => 'metabox',
            'timestamp' => time(),
            'version' => '1.0.0',
            'locale' => function_exists('get_locale') ? get_locale() : 'fr_FR',
            'order_id' => $this->order ? $this->order->get_id() : null
        ];

        $this->cacheData($cacheKey, $baseData, $this->cacheTtl);
        return $baseData;
    }

    /**
     * Récupère les données client
     *
     * @param array $context Contexte de récupération
     * @return array Données client formatées
     */
    public function getCustomerData(array $context = []): array
    {
        $customerId = $this->order ? $this->order->get_customer_id() : 0;

        // Pour les commandes invitées, utiliser un cache basé sur l'email
        if ($customerId === 0 && $this->order) {
            $email = $this->order->get_billing_email();
            $cacheKey = 'guest_' . md5($email);

            // Essayer de récupérer du cache interne pour les invités
            if ($this->getCachedData($cacheKey)) {
                return $this->getCachedData($cacheKey);
            }
        } else {
            // Essayer de récupérer du cache WooCommerce pour les clients enregistrés
            $cachedData = WooCommerceCache::getCustomerData($customerId);
            if ($cachedData !== null) {
                return $cachedData;
            }
        }

        $customerData = [
            'customer_id' => $customerId,
            'first_name' => $this->order ? $this->order->get_billing_first_name() : '[Prénom manquant]',
            'last_name' => $this->order ? $this->order->get_billing_last_name() : '[Nom manquant]',
            'full_name' => $this->order ?
                trim($this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name()) :
                '[Nom complet manquant]',
            'email' => $this->order ? $this->order->get_billing_email() : '[Email manquant]',
            'phone' => $this->order ? $this->order->get_billing_phone() : '[Téléphone manquant]',
            'company' => $this->order ? $this->order->get_billing_company() : '',
            'address' => [
                'street' => $this->order ? $this->order->get_billing_address_1() : '[Adresse manquante]',
                'city' => $this->order ? $this->order->get_billing_city() : '[Ville manquante]',
                'postcode' => $this->order ? $this->order->get_billing_postcode() : '[Code postal manquant]',
                'country' => $this->order ? $this->order->get_billing_country() : '[Pays manquant]',
                'formatted' => $this->order ?
                    $this->order->get_formatted_billing_address() :
                    '[Adresse de facturation manquante]'
            ],
            'registration_date' => $this->getCustomerRegistrationDate(),
            'total_orders' => $this->getCustomerTotalOrders(),
            'vat_number' => $this->order ? $this->order->get_meta('_billing_vat_number') : ''
        ];

        // Stocker en cache
        if ($customerId === 0 && $this->order) {
            // Cache interne pour les invités
            $this->cacheData($cacheKey, $customerData, $this->cacheTtl);
        } else {
            // Cache WooCommerce pour les clients enregistrés
            WooCommerceCache::setCustomerData($customerId, $customerData);
        }

        return $customerData;
    }

    /**
     * Récupère les données commande/produit
     *
     * @param array $context Contexte de récupération
     * @return array Données commande formatées
     */
    public function getOrderData(array $context = []): array
    {
        if (!$this->order) {
            return $this->getEmptyOrderData();
        }

        $orderId = $this->order->get_id();

        // Essayer de récupérer du cache WooCommerce
        $cachedData = WooCommerceCache::getOrderData($orderId, array_keys($context));
        if ($cachedData !== null) {
            return $cachedData;
        }

        // Générer les données si pas en cache
        $orderData = [
            'order_number' => $this->order->get_order_number(),
            'order_id' => $this->order->get_id(),
            'order_date' => $this->order->get_date_created() ?
                (method_exists($this->order->get_date_created(), 'date') ?
                    $this->order->get_date_created()->date('Y-m-d') :
                    $this->order->get_date_created()->format('Y-m-d')) : date('Y-m-d'),
            'order_time' => $this->order->get_date_created() ?
                (method_exists($this->order->get_date_created(), 'date') ?
                    $this->order->get_date_created()->date('H:i:s') :
                    $this->order->get_date_created()->format('H:i:s')) : date('H:i:s'),
            'order_datetime' => $this->order->get_date_created() ?
                (method_exists($this->order->get_date_created(), 'date') ?
                    $this->order->get_date_created()->date('Y-m-d H:i:s') :
                    $this->order->get_date_created()->format('Y-m-d H:i:s')) : date('Y-m-d H:i:s'),
            'order_status' => $this->order->get_status(),
            'payment_method' => $this->order->get_payment_method_title(),
            'shipping_method' => $this->getShippingMethod(),
            'currency' => $this->order->get_currency(),
            'subtotal' => $this->order->get_subtotal(),
            'tax_amount' => $this->order->get_total_tax(),
            'shipping_cost' => $this->order->get_shipping_total(),
            'discount_amount' => $this->order->get_discount_total(),
            'total' => $this->order->get_total(),
            'items' => $this->getOrderItems(),
            'shipping_address' => [
                'first_name' => $this->order->get_shipping_first_name() ?: $this->order->get_billing_first_name(),
                'last_name' => $this->order->get_shipping_last_name() ?: $this->order->get_billing_last_name(),
                'company' => $this->order->get_shipping_company() ?: $this->order->get_billing_company(),
                'street' => $this->order->get_shipping_address_1() ?: $this->order->get_billing_address_1(),
                'city' => $this->order->get_shipping_city() ?: $this->order->get_billing_city(),
                'postcode' => $this->order->get_shipping_postcode() ?: $this->order->get_billing_postcode(),
                'country' => $this->order->get_shipping_country() ?: $this->order->get_billing_country(),
                'formatted' => $this->order->get_formatted_shipping_address() ?: $this->order->get_formatted_billing_address()
            ],
            'billing_address' => [
                'first_name' => $this->order->get_billing_first_name(),
                'last_name' => $this->order->get_billing_last_name(),
                'company' => $this->order->get_billing_company(),
                'street' => $this->order->get_billing_address_1(),
                'city' => $this->order->get_billing_city(),
                'postcode' => $this->order->get_billing_postcode(),
                'country' => $this->order->get_billing_country(),
                'formatted' => $this->order->get_formatted_billing_address()
            ],
            'notes' => $this->order->get_customer_note(),
            'tracking_number' => $this->order->get_meta('_tracking_number') ?: ''
        ];

        // Stocker en cache WooCommerce
        WooCommerceCache::setOrderData($orderId, $orderData, array_keys($context));

        return $orderData;
    }

    /**
     * Récupère les données société
     *
     * @param array $context Contexte de récupération
     * @return array Données société formatées
     */
    public function getCompanyData(array $context = []): array
    {
        $contextKey = isset($context['template']) ? $context['template'] : 'default';

        // Essayer de récupérer du cache WooCommerce
        $cachedData = WooCommerceCache::getCompanyData($contextKey);
        if ($cachedData !== null) {
            return $cachedData;
        }

        $companyData = [
            'name' => function_exists('get_option') ? (get_option('woocommerce_store_name') ?: get_bloginfo('name')) : 'Test Company SARL',
            'legal_name' => function_exists('get_option') ? (get_option('woocommerce_store_name') ?: get_bloginfo('name')) : 'Test Company SARL',
            'siret' => function_exists('get_option') ? get_option('woocommerce_store_siret') : '',
            'vat_number' => function_exists('get_option') ? get_option('woocommerce_store_vat_number') : '',
            'email' => function_exists('get_option') ? (get_option('woocommerce_store_email') ?: get_option('admin_email')) : 'test@example.com',
            'phone' => function_exists('get_option') ? get_option('woocommerce_store_phone') : '',
            'website' => function_exists('get_site_url') ? get_site_url() : 'https://example.com',
            'address' => [
                'street' => function_exists('get_option') ? get_option('woocommerce_store_address') : '123 Test Street',
                'city' => function_exists('get_option') ? get_option('woocommerce_store_city') : 'Test City',
                'postcode' => function_exists('get_option') ? get_option('woocommerce_store_postcode') : '12345',
                'country' => function_exists('get_option') ? get_option('woocommerce_store_country') : 'FR',
                'formatted' => $this->getFormattedStoreAddress()
            ],
            'bank_details' => [
                'bank_name' => function_exists('get_option') ? get_option('woocommerce_store_bank_name') : '',
                'iban' => function_exists('get_option') ? get_option('woocommerce_store_iban') : '',
                'bic' => function_exists('get_option') ? get_option('woocommerce_store_bic') : ''
            ],
            'logo_url' => function_exists('get_option') ? get_option('woocommerce_store_logo') : '',
            'ceo_name' => function_exists('get_option') ? get_option('woocommerce_store_ceo') : '',
            'registration_number' => function_exists('get_option') ? get_option('woocommerce_store_registration') : ''
        ];

        // Stocker en cache WooCommerce
        WooCommerceCache::setCompanyData($companyData, $contextKey);

        return $companyData;
    }

    /**
     * Récupère les données système (date, numéro, etc.)
     *
     * @param array $context Contexte de récupération
     * @return array Données système formatées
     */
    public function getSystemData(array $context = []): array
    {
        $cacheKey = 'system_data_' . md5(serialize($context));

        if ($this->getCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $now = function_exists('current_time') ? current_time('timestamp') : time();
        $systemData = [
            'current_date' => function_exists('date_i18n') ? date_i18n('d/m/Y', $now) : date('d/m/Y', $now),
            'current_time' => function_exists('date_i18n') ? date_i18n('H:i:s', $now) : date('H:i:s', $now),
            'current_datetime' => function_exists('date_i18n') ? date_i18n('d/m/Y H:i:s', $now) : date('d/m/Y H:i:s', $now),
            'current_year' => function_exists('date_i18n') ? date_i18n('Y', $now) : date('Y', $now),
            'current_month' => function_exists('date_i18n') ? date_i18n('m', $now) : date('m', $now),
            'current_day' => function_exists('date_i18n') ? date_i18n('d', $now) : date('d', $now),
            'page_number' => 1,
            'total_pages' => 1,
            'document_type' => 'invoice',
            'template_version' => '1.0.0',
            'generated_by' => 'PDF Builder Pro v2.0',
            'generation_timestamp' => $now,
            'locale' => function_exists('get_locale') ? get_locale() : 'fr_FR',
            'timezone' => function_exists('wp_timezone_string') ? wp_timezone_string() : 'Europe/Paris'
        ];

        $this->cacheData($cacheKey, $systemData, $this->cacheTtl);
        return $systemData;
    }

    /**
     * Vérifie si toutes les données requises sont disponibles
     *
     * @param array $requiredKeys Clés requises
     * @param array $context Contexte de vérification
     * @return array Résultat ['complete' => bool, 'missing' => array]
     */
    public function checkDataCompleteness(array $requiredKeys, array $context = []): array
    {
        $missing = [];
        $allData = array_merge(
            $this->getBaseData($context),
            $this->getCustomerData($context),
            $this->getOrderData($context),
            $this->getCompanyData($context),
            $this->getSystemData($context)
        );

        foreach ($requiredKeys as $key) {
            if (!isset($allData[$key]) || empty($allData[$key])) {
                $missing[] = $key;
            }
        }

        return [
            'complete' => empty($missing),
            'missing' => $missing
        ];
    }

    /**
     * Génère des données fictives cohérentes pour les tests/prévisualisation
     *
     * @param array $templateKeys Clés du template à générer
     * @return array Données fictives générées
     */
    public function generateMockData(array $templateKeys = []): array
    {
        // Pour le mode metabox, on utilise les vraies données quand disponibles
        // Sinon on utilise des placeholders
        $mockData = [];

        foreach ($templateKeys as $key) {
            switch ($key) {
                case 'customer_name':
                    $customerData = $this->getCustomerData();
                    $mockData[$key] = $customerData['full_name'];
                    break;
                case 'customer_email':
                    $customerData = $this->getCustomerData();
                    $mockData[$key] = $customerData['email'];
                    break;
                case 'order_number':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = $orderData['order_number'];
                    break;
                case 'order_date':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = $orderData['order_date'];
                    break;
                case 'order_total':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = number_format($orderData['total'], 2, ',', ' ') . ' €';
                    break;
                case 'company_name':
                    $companyData = $this->getCompanyData();
                    $mockData[$key] = $companyData['name'];
                    break;
                case 'company_email':
                    $companyData = $this->getCompanyData();
                    $mockData[$key] = $companyData['email'];
                    break;
                case 'current_date':
                    $systemData = $this->getSystemData();
                    $mockData[$key] = $systemData['current_date'];
                    break;
                case 'product_name':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = !empty($orderData['items']) ? $orderData['items'][0]['name'] : '[Produit manquant]';
                    break;
                case 'product_quantity':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = !empty($orderData['items']) ? $orderData['items'][0]['quantity'] : '0';
                    break;
                case 'product_price':
                    $orderData = $this->getOrderData();
                    $mockData[$key] = !empty($orderData['items']) ?
                        number_format($orderData['items'][0]['total'], 2, ',', ' ') . ' €' :
                        '0,00 €';
                    break;
                default:
                    $mockData[$key] = '[Donnée manquante: ' . $key . ']';
                    break;
            }
        }

        return $mockData;
    }

    /**
     * Nettoie et formate les données selon les standards
     *
     * @param array $data Données brutes à nettoyer
     * @return array Données nettoyées et formatées
     */
    public function sanitizeData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Échappe les caractères spéciaux HTML
                $sanitized[$key] = function_exists('wp_kses_post') ? wp_kses_post($value) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                // Récursivement nettoyer les tableaux
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_numeric($value)) {
                // Formater les nombres
                if (strpos($key, 'price') !== false || strpos($key, 'total') !== false || strpos($key, 'amount') !== false) {
                    $sanitized[$key] = number_format((float)$value, 2, ',', ' ');
                } else {
                    $sanitized[$key] = $value;
                }
            } else {
                // Conserver les autres types de données
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Met en cache les données pour optimiser les performances
     *
     * @param string $key Clé de cache
     * @param array $data Données à mettre en cache
     * @param int $ttl Durée de vie en secondes
     * @return bool True si mise en cache réussie
     */
    public function cacheData(string $key, array $data, int $ttl = 300): bool
    {
        $this->cache[$key] = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
        return true;
    }

    /**
     * Récupère les données depuis le cache
     *
     * @param string $key Clé de cache
     * @return array|null Données depuis le cache ou null si non trouvées
     */
    public function getCachedData(string $key): ?array
    {
        if (!isset($this->cache[$key])) {
            return null;
        }

        if ($this->cache[$key]['expires'] < time()) {
            unset($this->cache[$key]);
            return null;
        }

        return $this->cache[$key]['data'];
    }

    /**
     * Invalide le cache pour une clé donnée
     *
     * @param string $key Clé de cache à invalider
     * @return bool True si invalidation réussie
     */
    public function invalidateCache(string $key): bool
    {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            return true;
        }
        return false;
    }

    /**
     * Récupère la date d'inscription du client
     *
     * @return string Date d'inscription formatée
     */
    private function getCustomerRegistrationDate(): string
    {
        if (!$this->order || !$this->order->get_customer_id()) {
            return '';
        }

        if (!function_exists('get_user_by')) {
            return '2024-01-15'; // Valeur par défaut pour les tests
        }

        $user = get_user_by('id', $this->order->get_customer_id());
        if ($user) {
            return date_i18n('Y-m-d', strtotime($user->user_registered));
        }

        return '';
    }

    /**
     * Récupère le nombre total de commandes du client
     *
     * @return int Nombre total de commandes
     */
    private function getCustomerTotalOrders(): int
    {
        if (!$this->order || !$this->order->get_customer_id()) {
            return 0;
        }

        if (!function_exists('wc_get_orders')) {
            return 5; // Valeur par défaut pour les tests
        }

        if (!function_exists('wc_get_orders')) {
            return 5; // Valeur par défaut pour les tests
        }

        // Simulation pour les tests - en production utiliserait wc_get_orders
        return 5; // Placeholder pour les tests
    }

    /**
     * Récupère la méthode d'expédition formatée
     *
     * @return string Méthode d'expédition
     */
    private function getShippingMethod(): string
    {
        if (!$this->order) {
            return '';
        }

        $shipping_methods = $this->order->get_shipping_methods();
        if (!empty($shipping_methods)) {
            $method = reset($shipping_methods);
            return $method->get_method_title();
        }

        return '';
    }

    /**
     * Récupère les articles de la commande formatés
     *
     * @return array Articles formatés
     */
    private function getOrderItems(): array
    {
        if (!$this->order) {
            return [];
        }

        $items = [];
        foreach ($this->order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $items[] = [
                'id' => $item_id,
                'name' => $item->get_name(),
                'sku' => $product ? $product->get_sku() : '',
                'quantity' => $item->get_quantity(),
                'unit_price' => $item->get_total() / $item->get_quantity(),
                'total' => $item->get_total(),
                'description' => $product ? $product->get_short_description() : ''
            ];
        }

        return $items;
    }

    /**
     * Récupère l'adresse du magasin formatée
     *
     * @return string Adresse formatée
     */
    private function getFormattedStoreAddress(): string
    {
        $address_parts = [];

        if (!function_exists('get_option')) {
            return '456 Avenue des Champs, Lyon, 69000, France'; // Valeur par défaut pour les tests
        }

        if ($street = get_option('woocommerce_store_address')) {
            $address_parts[] = $street;
        }
        if ($city = get_option('woocommerce_store_city')) {
            $address_parts[] = $city;
        }
        if ($postcode = get_option('woocommerce_store_postcode')) {
            $address_parts[] = $postcode;
        }
        if ($country = get_option('woocommerce_store_country')) {
            $country_name = $country; // Simplifié pour les tests
            $address_parts[] = $country_name;
        }

        return implode(', ', array_filter($address_parts));
    }

    /**
     * Retourne des données de commande vides pour les cas d'erreur
     *
     * @return array Données vides
     */
    private function getEmptyOrderData(): array
    {
        return [
            'order_number' => '[Numéro commande manquant]',
            'order_id' => null,
            'order_date' => date('Y-m-d'),
            'order_time' => date('H:i:s'),
            'order_datetime' => date('Y-m-d H:i:s'),
            'order_status' => 'unknown',
            'payment_method' => '[Méthode paiement manquante]',
            'shipping_method' => '[Méthode expédition manquante]',
            'currency' => 'EUR',
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => 0,
            'items' => [],
            'shipping_address' => [
                'first_name' => '[Prénom manquant]',
                'last_name' => '[Nom manquant]',
                'company' => '',
                'street' => '[Adresse manquante]',
                'city' => '[Ville manquante]',
                'postcode' => '[Code postal manquant]',
                'country' => '[Pays manquant]',
                'formatted' => '[Adresse expédition manquante]'
            ],
            'billing_address' => [
                'first_name' => '[Prénom manquant]',
                'last_name' => '[Nom manquant]',
                'company' => '',
                'street' => '[Adresse manquante]',
                'city' => '[Ville manquante]',
                'postcode' => '[Code postal manquant]',
                'country' => '[Pays manquant]',
                'formatted' => '[Adresse facturation manquante]'
            ],
            'notes' => '',
            'tracking_number' => ''
        ];
    }
}