<?php
namespace WP_PDF_Builder_Pro\Data;

use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Fournisseur de données fictives pour l'aperçu design (éditeur)
 * Génère des données cohérentes et réalistes pour prévisualisation
 */
class CanvasDataProvider implements DataProviderInterface {
    private $mock_data;

    public function __construct() {
        $this->initializeMockData();
    }

    private function initializeMockData(): void {
        $this->mock_data = [
            'order_number' => '#DEMO-12345',
            'customer_name' => 'Jean Dupont',
            'customer_first_name' => 'Jean',
            'customer_last_name' => 'Dupont',
            'customer_email' => 'jean.dupont@email-demo.com',
            'customer_phone' => '+33 1 23 45 67 89',
            'billing_address' => '123 Rue de la Paix',
            'billing_address_2' => 'Appartement 4B',
            'billing_city' => 'Paris',
            'billing_postcode' => '75001',
            'billing_country' => 'France',
            'billing_state' => 'Île-de-France',
            'shipping_address' => '123 Rue de la Paix',
            'shipping_address_2' => 'Appartement 4B',
            'shipping_city' => 'Paris',
            'shipping_postcode' => '75001',
            'shipping_country' => 'France',
            'shipping_state' => 'Île-de-France',
            'order_date' => date('d/m/Y'),
            'order_time' => date('H:i'),
            'order_total' => '99,99 €',
            'order_subtotal' => '89,99 €',
            'order_tax' => '10,00 €',
            'order_shipping' => '5,00 €',
            'order_discount' => '0,00 €',
            'order_status' => 'En attente',
            'company_name' => 'Votre Entreprise',
            'company_address' => '456 Avenue des Champs',
            'company_city' => 'Paris',
            'company_postcode' => '75008',
            'company_phone' => '+33 1 98 76 54 32',
            'company_email' => 'contact@votre-entreprise.com',
            'company_website' => 'www.votre-entreprise.com'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableValue(string $variable): string {
        return $this->mock_data[$variable] ?? $this->getFallbackValue($variable);
    }

    /**
     * Vérifie si une variable est disponible
     *
     * @param string $variable Nom de la variable
     * @return bool True si la variable existe
     */
    public function hasVariable(string $variable): bool {
        return array_key_exists($variable, $this->mock_data) ||
               array_key_exists($variable, $this->getAllVariables());
    }

    /**
     * Retourne la liste de toutes les variables disponibles
     *
     * @return array Liste des noms de variables
     */
    public function getAllVariables(): array {
        return array_keys($this->mock_data);
    }

    /**
     * Valeur de fallback pour les variables non définies
     */
    private function getFallbackValue(string $variable): string {
        // Variables spéciales avec logique
        switch ($variable) {
            case 'current_date':
                return date('d/m/Y');
            case 'current_time':
                return date('H:i');
            case 'page_number':
                return '1';
            case 'total_pages':
                return '1';
            default:
                return '<span style="color: red; font-weight: bold;">[Donnée manquante: ' . $variable . ']</span>';
        }
    }

    /**
     * Vérifie si une variable est supportée
     */
    private function isSupportedVariable(string $variable): bool {
        $supported = [
            'current_date',
            'current_time',
            'page_number',
            'total_pages'
        ];

        return in_array($variable, $supported);
    }

    /**
     * Met à jour une donnée mock (pour tests)
     */
    public function setMockData(string $key, string $value): void {
        $this->mock_data[$key] = $value;
    }

    /**
     * Réinitialise les données mock
     */
    public function resetMockData(): void {
        // Les données restent les mêmes pour cohérence
    }

    /**
     * Génère des données de produits fictives pour démonstration
     */
    public function getMockProducts(): array {
        return [
            [
                'name' => 'Produit Démo Premium',
                'quantity' => 2,
                'price' => '29,99 €',
                'total' => '59,98 €',
                'sku' => 'DEMO-PREM-001'
            ],
            [
                'name' => 'Service Consultation',
                'quantity' => 1,
                'price' => '30,00 €',
                'total' => '30,00 €',
                'sku' => 'DEMO-SERV-001'
            ]
        ];
    }

    /**
     * Génère des données de commande fictives complètes
     */
    public function getMockOrderData(): array {
        return array_merge($this->mock_data, [
            'products' => $this->getMockProducts(),
            'notes' => 'Commande de démonstration pour aperçu design'
        ]);
    }
}