<?php

namespace PDF_Builder\Interfaces;

/**
 * Interface DataProviderInterface
 * Définit le contrat pour les fournisseurs de données de template
 * Updated: 2025-11-30 00:41
 */
interface DataProviderInterface
{
    /**
     * Récupère la valeur d'une variable
     *
     * @param string $variable Nom de la variable
     * @return string Valeur de la variable ou valeur par défaut
     */
    public function getVariableValue(string $variable): string;

    /**
     * Vérifie si une variable est disponible
     *
     * @param string $variable Nom de la variable
     * @return bool True si la variable existe
     */
    public function hasVariable(string $variable): bool;

    /**
     * Retourne la liste de toutes les variables disponibles
     *
     * @return array Liste des noms de variables
     */
    public function getAllVariables(): array;

    /**
     * Indique si les données sont fictives (pour l'aperçu)
     *
     * @return bool True si données fictives
     */
    public function isSampleData(): bool;

    /**
     * Retourne le contexte d'utilisation
     *
     * @return string Contexte (canvas, metabox, etc.)
     */
    public function getContext(): string;

    /**
     * Valide et sanitise un tableau de données
     *
     * @param array $data Données à valider
     * @return array Données validées et sanitizées
     */
    public function validateAndSanitizeData(array $data): array;
}





