<?php

namespace PDF_Builder\Data;

use PDF_Builder\Interfaces\DataProviderInterface;

/**
 * Classe EditorDataProvider
 * Fournit les données réelles du template pour l'aperçu en mode éditeur
 * Utilisé pour afficher exactement ce que l'utilisateur a créé
 */
class EditorDataProvider implements DataProviderInterface
{
    /** @var array Données du template */
    private $templateData;
    /** @var string Contexte d'utilisation */
    private $context;

    /**
     * Constructeur
     *
     * @param array $templateData Données du template
     * @param string $context Contexte d'utilisation (editor, preview, etc.)
     */
    public function __construct(array $templateData = [], string $context = 'editor')
    {
        $this->templateData = $templateData;
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     * Récupère une valeur de données - retourne le template tel quel
     */
    public function get($key, $default = null): mixed
    {
        // Les variables dynamiques dans les templates ne sont pas remplacées
        // On retourne la clé elle-même ou une chaîne vide
        // Les éléments de template sont rendus avec leurs contenus originaux
        return isset($this->templateData[$key]) ? $this->templateData[$key] : $default;
    }

    /**
     * {@inheritDoc}
     * Récupère toutes les données
     */
    public function getAll(): array
    {
        return $this->templateData;
    }

    /**
     * {@inheritDoc}
     * Vérifie si une clé existe
     */
    public function has($key): bool
    {
        return isset($this->templateData[$key]);
    }

    /**
     * Définit une valeur de données
     */
    public function set($key, $value): void
    {
        $this->templateData[$key] = $value;
    }

    /**
     * {@inheritDoc}
     * Récupère les données formatées pour le rendu
     */
    public function getFormattedData(): array
    {
        return $this->templateData;
    }

    /**
     * {@inheritDoc}
     * Récupère le contexte
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * {@inheritDoc}
     * Remplace les variables dans un texte - pour EditorDataProvider,
     * on ne remplace que les variables qui existent dans les données du template
     */
    public function replaceVariables($text): string
    {
        if (empty($text) || !is_string($text)) {
            return $text;
        }

        $result = $text;

        // Remplacer les variables de format {VAR_NAME} avec les données réelles
        foreach ($this->templateData as $key => $value) {
            $pattern = '{' . strtoupper($key) . '}';
            if (stripos($result, $pattern) !== false) {
                $result = str_replace($pattern, (string)$value, $result);
            }
        }

        return $result;
    }

    /**
     * Récupère les informations client (du template)
     */
    public function getCustomerInfo(): array
    {
        return [
            'name' => $this->get('customer_name', ''),
            'email' => $this->get('customer_email', ''),
            'phone' => $this->get('customer_phone', ''),
            'address' => $this->get('customer_address', ''),
        ];
    }

    /**
     * Récupère les informations de commande (du template)
     */
    public function getOrderInfo(): array
    {
        return [
            'number' => $this->get('order_number', ''),
            'date' => $this->get('order_date', gmdate('d/m/Y')),
            'status' => $this->get('order_status', ''),
            'total' => $this->get('order_total', ''),
        ];
    }

    /**
     * Récupère les informations d'entreprise (du template)
     */
    public function getCompanyInfo(): array
    {
        return [
            'name' => $this->get('company_name', ''),
            'address' => $this->get('company_address', ''),
            'phone' => $this->get('company_phone', ''),
            'email' => $this->get('company_email', ''),
        ];
    }

    /**
     * Récupère les produits (du template)
     */
    public function getProducts(): array
    {
        $products = [];
        // Chercher tous les produits dans les données du template
        foreach ($this->templateData as $key => $value) {
            if (strpos($key, 'product_') === 0) {
                $products[$key] = $value;
            }
        }
        return $products;
    }

    /**
     * {@inheritDoc}
     * Récupère la valeur d'une variable - retourne la valeur exacte du template
     */
    public function getVariableValue(string $variable): string
    {
        if (isset($this->templateData[$variable])) {
            return (string)$this->templateData[$variable];
        }
        return '';
    }

    /**
     * {@inheritDoc}
     * Vérifie si une variable est disponible dans le template
     */
    public function hasVariable(string $variable): bool
    {
        return isset($this->templateData[$variable]);
    }

    /**
     * {@inheritDoc}
     * Retourne la liste de toutes les variables disponibles
     */
    public function getAllVariables(): array
    {
        return array_keys($this->templateData);
    }

    /**
     * {@inheritDoc}
     * Indique que ce ne sont pas des données fictives - c'est les vraies données du template
     */
    public function isSampleData(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * Valide et sanitise un tableau de données
     */
    public function validateAndSanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            // Accepter les types basiques
            if (is_string($value)) {
                $sanitized[$key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->validateAndSanitizeData($value);
            } else {
                $sanitized[$key] = (string)$value;
            }
        }
        return $sanitized;
    }
}

