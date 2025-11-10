<?php

namespace WP_PDF_Builder_Pro\Interfaces;

/**
 * Interface DataProviderInterface
 * Définit le contrat pour les fournisseurs de données de template
 * Updated: 2025-11-02 14:20
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
}
