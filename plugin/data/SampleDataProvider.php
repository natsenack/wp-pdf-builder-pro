<?php
namespace WP_PDF_Builder_Pro\Data;

/**
 * Classe SampleDataProvider
 * Fournit des données fictives cohérentes pour l'aperçu en mode éditeur
 */
class SampleDataProvider implements DataProviderInterface {

    /** @var array Données fictives de base */
    private $sampleData;

    /** @var string Contexte d'utilisation */
    private $context;

    /**
     * Constructeur
     *
     * @param string $context Contexte d'utilisation (canvas, metabox, etc.)
     */
    public function __construct(string $context = 'canvas') {
        $this->context = $context;
        $this->initializeSampleData();
    }

    /**
     * Initialise les données fictives cohérentes
     */
    private function initializeSampleData(): void {
        $this->sampleData = [
            // Informations client
            'customer_name' => 'Jean Dupont',
            'customer_firstname' => 'Jean',
            'customer_lastname' => 'Dupont',
            'customer_email' => 'jean.dupont@email.com',
            'customer_phone' => '+33 6 12 34 56 78',
            'customer_address' => '15 rue de la Paix',
            'customer_city' => 'Paris',
            'customer_postcode' => '75001',
            'customer_country' => 'France',

            // Informations commande
            'order_number' => 'SAMPLE-001',
            'order_date' => date('d/m/Y'),
            'order_time' => date('H:i'),
            'order_status' => 'En cours',
            'order_total' => '€127.50',
            'order_subtotal' => '€120.00',
            'order_tax' => '€7.50',
            'order_shipping' => '€5.00',
            'order_discount' => '€0.00',

            // Informations entreprise (paramètres fictifs)
            'company_name' => 'Ma Boutique en Ligne',
            'company_address' => '123 Avenue des Commerçants',
            'company_city' => 'Lyon',
            'company_postcode' => '69000',
            'company_country' => 'France',
            'company_phone' => '+33 4 12 34 56 78',
            'company_email' => 'contact@ma-boutique.com',
            'company_website' => 'www.ma-boutique.com',
            'company_siret' => '123 456 789 01234',
            'company_tva' => 'FR 12 345 678 901',

            // Produits (3 produits avec calculs cohérents)
            'product_1_name' => 'T-shirt Premium Bio',
            'product_1_quantity' => '2',
            'product_1_price' => '€25.00',
            'product_1_total' => '€50.00',

            'product_2_name' => 'Jean Slim Fit',
            'product_2_quantity' => '1',
            'product_2_price' => '€45.00',
            'product_2_total' => '€45.00',

            'product_3_name' => 'Casquette Ajustable',
            'product_3_quantity' => '1',
            'product_3_price' => '€25.00',
            'product_3_total' => '€25.00',

            // Totaux calculés
            'products_total' => '€120.00',
            'tax_rate' => '6.25%',
            'tax_amount' => '€7.50',
            'shipping_cost' => '€5.00',
            'grand_total' => '€127.50',

            // Informations de paiement
            'payment_method' => 'Carte bancaire',
            'payment_date' => date('d/m/Y H:i'),

            // Notes et commentaires
            'order_notes' => 'Livraison express demandée',
            'customer_notes' => 'Merci pour votre commande !',

            // Variables conditionnelles
            'has_discount' => 'false',
            'is_paid' => 'true',
            'is_shipped' => 'false',

            // Variables de formatage
            'currency_symbol' => '€',
            'currency_code' => 'EUR',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',

            // Variables WooCommerce spécifiques
            'billing_first_name' => 'Jean',
            'billing_last_name' => 'Dupont',
            'billing_company' => '',
            'billing_address_1' => '15 rue de la Paix',
            'billing_address_2' => '',
            'billing_city' => 'Paris',
            'billing_state' => 'Île-de-France',
            'billing_postcode' => '75001',
            'billing_country' => 'FR',
            'billing_email' => 'jean.dupont@email.com',
            'billing_phone' => '+33 6 12 34 56 78',

            'shipping_first_name' => 'Jean',
            'shipping_last_name' => 'Dupont',
            'shipping_company' => '',
            'shipping_address_1' => '15 rue de la Paix',
            'shipping_address_2' => '',
            'shipping_city' => 'Paris',
            'shipping_state' => 'Île-de-France',
            'shipping_postcode' => '75001',
            'shipping_country' => 'FR',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getVariableValue(string $variable): string {
        // Recherche directe dans les données
        if (isset($this->sampleData[$variable])) {
            return $this->sampleData[$variable];
        }

        // Recherche avec préfixes communs
        $prefixedVars = [
            'order_' . $variable,
            'customer_' . $variable,
            'company_' . $variable,
            'product_' . $variable,
            'billing_' . $variable,
            'shipping_' . $variable,
        ];

        foreach ($prefixedVars as $prefixedVar) {
            if (isset($this->sampleData[$prefixedVar])) {
                return $this->sampleData[$prefixedVar];
            }
        }

        // Gestion des variables spéciales
        return $this->handleSpecialVariables($variable);
    }

    /**
     * Gère les variables spéciales qui nécessitent un calcul
     *
     * @param string $variable Nom de la variable
     * @return string Valeur de la variable
     */
    private function handleSpecialVariables(string $variable): string {
        switch ($variable) {
            case 'current_date':
                return date('d/m/Y');

            case 'current_time':
                return date('H:i');

            case 'current_datetime':
                return date('d/m/Y H:i');

            case 'order_items_count':
                return '3'; // Nombre d'articles dans notre exemple

            case 'order_weight':
                return '1.2 kg'; // Poids fictif

            case 'customer_full_name':
                return $this->sampleData['customer_firstname'] . ' ' . $this->sampleData['customer_lastname'];

            case 'company_full_address':
                return $this->sampleData['company_address'] . ', ' .
                       $this->sampleData['company_postcode'] . ' ' .
                       $this->sampleData['company_city'] . ', ' .
                       $this->sampleData['company_country'];

            default:
                // Retourne la variable elle-même si non trouvée (pour debug)
                return '{{' . $variable . '}}';
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isSampleData(): bool {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): string {
        return $this->context;
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
     * Retourne toutes les données fictives disponibles
     *
     * @return array Données fictives complètes
     */
    public function getAllSampleData(): array {
        return $this->sampleData;
    }

    /**
     * Met à jour une donnée fictive (pour tests ou personnalisation)
     *
     * @param string $key Clé de la donnée
     * @param mixed $value Nouvelle valeur
     */
    public function setSampleData(string $key, $value): void {
        $this->sampleData[$key] = $this->validateAndSanitizeData($value);
    }
}