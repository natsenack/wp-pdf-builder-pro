<?php
namespace WP_PDF_Builder_Pro\Data;

/**
 * Interface DataProviderInterface
 * Définit le contrat pour tous les fournisseurs de données d'aperçu
 */
interface DataProviderInterface {

    /**
     * Récupère la valeur d'une variable dynamique
     *
     * @param string $variable Le nom de la variable (ex: 'customer_name')
     * @return string La valeur de la variable ou la variable elle-même si non trouvée
     */
    public function getVariableValue(string $variable): string;

    /**
     * Indique si le provider utilise des données fictives ou réelles
     *
     * @return bool true si données fictives, false si données réelles
     */
    public function isSampleData(): bool;

    /**
     * Récupère le contexte d'utilisation (canvas, metabox, etc.)
     *
     * @return string Le contexte d'utilisation
     */
    public function getContext(): string;

    /**
     * Valide et sanitise les données selon le contexte
     *
     * @param array $data Les données à valider
     * @return array Les données validées et sanitizées
     */
    public function validateAndSanitizeData(array $data): array;

    /**
     * Valide et sanitise une valeur individuelle selon le type
     *
     * @param mixed $value La valeur à valider
     * @param string $type Le type de données attendu
     * @return mixed La valeur validée et sanitizée
     */
    public function validateAndSanitizeValue($value, string $type = 'string');
}