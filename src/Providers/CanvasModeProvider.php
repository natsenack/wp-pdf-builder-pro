<?php
/**
 * Provider de données pour le mode Canvas (éditeur)
 *
 * Fournit des données fictives cohérentes pour l'aperçu
 * dans l'éditeur Canvas.
 *
 * @package PDF_Builder_Pro
 * @subpackage Providers
 */

namespace PDF_Builder_Pro\Providers;

use PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Classe CanvasModeProvider
 *
 * Implémente DataProviderInterface pour fournir des données fictives
 * cohérentes utilisées dans l'éditeur Canvas pour l'aperçu.
 */
class CanvasModeProvider implements DataProviderInterface
{
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
            'template_id' => $context['template_id'] ?? 'template_001',
            'mode' => 'canvas',
            'timestamp' => time(),
            'version' => '1.0.0',
            'locale' => 'fr_FR'
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
        $cacheKey = 'customer_data_' . md5(serialize($context));

        if ($this->getCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $customerData = [
            'first_name' => 'Marie',
            'last_name' => 'Dubois',
            'full_name' => 'Marie Dubois',
            'email' => 'marie.dubois@email.com',
            'phone' => '+33 6 12 34 56 78',
            'company' => 'Entreprise Dubois SARL',
            'address' => [
                'street' => '15 Rue de la Paix',
                'city' => 'Paris',
                'postcode' => '75001',
                'country' => 'France',
                'formatted' => '15 Rue de la Paix, 75001 Paris, France'
            ],
            'customer_id' => 'CUST_2024_001',
            'registration_date' => '2024-01-15',
            'loyalty_points' => 1250,
            'total_orders' => 8,
            'vat_number' => 'FR12345678901'
        ];

        $this->cacheData($cacheKey, $customerData, $this->cacheTtl);
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
        $cacheKey = 'order_data_' . md5(serialize($context));

        if ($this->getCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $orderData = [
            'order_number' => 'CMD-2024-0456',
            'order_id' => 'WC_0456',
            'order_date' => '2024-10-22',
            'order_time' => '14:30:00',
            'order_status' => 'processing',
            'payment_method' => 'Carte bancaire',
            'shipping_method' => 'Colissimo Express',
            'currency' => 'EUR',
            'subtotal' => 299.99,
            'tax_amount' => 59.99,
            'shipping_cost' => 9.99,
            'discount_amount' => 15.00,
            'total' => 354.97,
            'items' => [
                [
                    'name' => 'Ordinateur Portable Pro',
                    'sku' => 'LAPTOP-PRO-15',
                    'quantity' => 1,
                    'unit_price' => 299.99,
                    'total' => 299.99,
                    'description' => 'Ordinateur portable professionnel 15 pouces'
                ]
            ],
            'shipping_address' => [
                'first_name' => 'Marie',
                'last_name' => 'Dubois',
                'company' => 'Entreprise Dubois SARL',
                'street' => '15 Rue de la Paix',
                'city' => 'Paris',
                'postcode' => '75001',
                'country' => 'France',
                'formatted' => 'Marie Dubois\nEntreprise Dubois SARL\n15 Rue de la Paix\n75001 Paris\nFrance'
            ],
            'billing_address' => [
                'first_name' => 'Marie',
                'last_name' => 'Dubois',
                'company' => 'Entreprise Dubois SARL',
                'street' => '15 Rue de la Paix',
                'city' => 'Paris',
                'postcode' => '75001',
                'country' => 'France',
                'formatted' => 'Marie Dubois\nEntreprise Dubois SARL\n15 Rue de la Paix\n75001 Paris\nFrance'
            ],
            'notes' => 'Livraison en journée, merci de contacter avant livraison.',
            'tracking_number' => 'FR1234567890'
        ];

        $this->cacheData($cacheKey, $orderData, $this->cacheTtl);
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
        $cacheKey = 'company_data_' . md5(serialize($context));

        if ($this->getCachedData($cacheKey)) {
            return $this->getCachedData($cacheKey);
        }

        $companyData = [
            'name' => 'Votre Société SARL',
            'legal_name' => 'Votre Société Société à Responsabilité Limitée',
            'siret' => '123 456 789 01234',
            'vat_number' => 'FR12345678901',
            'email' => 'contact@votresociete.com',
            'phone' => '+33 1 42 86 75 39',
            'website' => 'www.votresociete.com',
            'address' => [
                'street' => '25 Boulevard Haussmann',
                'city' => 'Paris',
                'postcode' => '75009',
                'country' => 'France',
                'formatted' => '25 Boulevard Haussmann, 75009 Paris, France'
            ],
            'bank_details' => [
                'bank_name' => 'BNP Paribas',
                'iban' => 'FR14 2004 1010 0505 0001 3M02 606',
                'bic' => 'BNPAFRPP'
            ],
            'logo_url' => '/wp-content/uploads/2024/10/company-logo.png',
            'ceo_name' => 'Jean Martin',
            'registration_number' => 'RCS Paris 123 456 789'
        ];

        $this->cacheData($cacheKey, $companyData, $this->cacheTtl);
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

        $now = new \DateTime();
        $systemData = [
            'current_date' => $now->format('d/m/Y'),
            'current_time' => $now->format('H:i:s'),
            'current_datetime' => $now->format('d/m/Y H:i:s'),
            'current_year' => $now->format('Y'),
            'current_month' => $now->format('m'),
            'current_day' => $now->format('d'),
            'page_number' => 1,
            'total_pages' => 1,
            'document_type' => 'invoice',
            'template_version' => '1.0.0',
            'generated_by' => 'PDF Builder Pro v2.0',
            'generation_timestamp' => $now->getTimestamp(),
            'locale' => 'fr_FR',
            'timezone' => 'Europe/Paris'
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
            if (!isset($allData[$key])) {
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
        $mockData = [];

        // Génère des données pour chaque clé demandée
        foreach ($templateKeys as $key) {
            switch ($key) {
                case 'customer_name':
                    $mockData[$key] = 'Marie Dubois';
                    break;
                case 'customer_email':
                    $mockData[$key] = 'marie.dubois@email.com';
                    break;
                case 'order_number':
                    $mockData[$key] = 'CMD-2024-0456';
                    break;
                case 'order_date':
                    $mockData[$key] = '22/10/2024';
                    break;
                case 'order_total':
                    $mockData[$key] = '354,97 €';
                    break;
                case 'company_name':
                    $mockData[$key] = 'Votre Société SARL';
                    break;
                case 'company_email':
                    $mockData[$key] = 'contact@votresociete.com';
                    break;
                case 'current_date':
                    $mockData[$key] = date('d/m/Y');
                    break;
                case 'product_name':
                    $mockData[$key] = 'Ordinateur Portable Pro';
                    break;
                case 'product_quantity':
                    $mockData[$key] = '1';
                    break;
                case 'product_price':
                    $mockData[$key] = '299,99 €';
                    break;
                default:
                    $mockData[$key] = '[Donnée fictive: ' . $key . ']';
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
                $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            } elseif (is_array($value)) {
                // Récursivement nettoyer les tableaux
                $sanitized[$key] = $this->sanitizeData($value);
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
}